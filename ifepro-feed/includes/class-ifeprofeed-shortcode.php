<?php
/**
 * Import Facebook Events Live Feed Shortcode Handler.
 *
 * @package Import_Facebook_Events\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IFEPRO_Feed_Shortcode {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_shortcode( 'ifepro_live_feed', array( $this, 'render' ) );
	}

	/**
	 * Render the feed shortcode.
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'          => 0,
				'columns'     => null,
				'per_page'    => null,
				'layout'      => null,
				'time_filter' => null,
				'cache'       => null,
			),
			$atts,
			'ifepro_live_feed'
		);

		$feed_id = absint( $atts['id'] );
		if ( ! $feed_id ) {
			return $this->error( __( '[ifepro_live_feed] Missing required attribute: id', 'import-facebook-events' ) );
		}

		$feed_post = get_post( $feed_id );
		if ( ! $feed_post || IFEPRO_FEED_CPT !== $feed_post->post_type || 'publish' !== $feed_post->post_status ) {
			return $this->error( __( '[ifepro_live_feed] Feed not found or not published.', 'import-facebook-events' ) );
		}

		$meta = IFEPRO_Feed_API::instance()->get_feed_meta( $feed_id );

		if ( null !== $atts['columns'] )     $meta['columns']      = absint( $atts['columns'] );
		if ( null !== $atts['per_page'] )    $meta['per_page']     = absint( $atts['per_page'] );
		if ( null !== $atts['layout'] )      $meta['layout']       = sanitize_text_field( $atts['layout'] );
		if ( null !== $atts['time_filter'] ) $meta['time_filter']  = sanitize_text_field( $atts['time_filter'] );
		if ( null !== $atts['cache'] )       $meta['cache_duration'] = absint( $atts['cache'] );

		$events = IFEPRO_Feed_API::instance()->get_events( $feed_id );

		if ( is_wp_error( $events ) ) {
			return $this->error( $events->get_error_message() );
		}
		if ( empty( $events ) ) {
			return $this->no_events();
		}

		$this->enqueue_public_assets( $meta, $feed_id );

		ob_start();
		$this->render_feed( $feed_id, $events, $meta );
		return ob_get_clean();
	}

	// -------------------------------------------------------
	// Main render
	// -------------------------------------------------------

	private function render_feed( $feed_id, $events, $meta ) {
		$wrapper_id      = 'ifeprofeed-feed-' . $feed_id;
		$layout_class    = 'ifeprofeed-layout-' . sanitize_html_class( $meta['layout'] );
		$cols_class      = 'ifeprofeed-cols-' . absint( $meta['columns'] );
		$pagination_type = $meta['pagination_type'] ?? 'ajax';
		$per_page        = absint( $meta['per_page'] );
		$total_events    = count( $events );
		$total_pages     = ceil( $total_events / $per_page );

		if ( 'none' === $pagination_type ) {
			$page_events = array_slice( $events, 0, $per_page );
			$total_pages = 1;
		} else {
			$page_events = array_slice( $events, 0, $per_page );
		}
		?>
		<div id="<?php echo esc_attr( $wrapper_id ); ?>"
			class="ifeprofeed-feed-wrap <?php echo esc_attr( $layout_class . ' ' . $cols_class . ' ifeprofeed-pagination-' . $pagination_type ); ?>"
			data-feed-id="<?php echo esc_attr( $feed_id ); ?>"
			data-per-page="<?php echo esc_attr( $per_page ); ?>"
			data-current-page="1"
			data-total-pages="<?php echo esc_attr( $total_pages ); ?>"
			data-pagination-type="<?php echo esc_attr( $pagination_type ); ?>">

			<?php if ( ! empty( $meta['custom_css'] ) ) : ?>
			<style>#<?php echo esc_attr( $wrapper_id ); ?> { <?php echo wp_strip_all_tags( $meta['custom_css'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> }</style>
			<?php endif; ?>

			<div class="ifeprofeed-events-grid" data-events-container>
				<?php foreach ( $page_events as $event ) : ?>
					<?php $this->render_event_card( $event, $meta ); ?>
				<?php endforeach; ?>
			</div>

			<?php if ( $total_pages > 1 && 'none' !== $pagination_type ) : ?>
			<div class="ifeprofeed-pagination" data-pagination data-pagination-type="<?php echo esc_attr( $pagination_type ); ?>">
				<?php $this->render_pagination( 1, $total_pages, $total_events, $per_page, $pagination_type ); ?>
			</div>
			<?php endif; ?>

		</div>
		<?php
	}

	// -------------------------------------------------------
	// Single event card (dispatches to layout template)
	// -------------------------------------------------------

	public function render_event_card( $event, $meta ) {
		$layout   = sanitize_text_field( $meta['layout'] );
		$template = locate_template( 'ifeprofeed-feed/' . $layout . '.php' );
		if ( ! $template ) {
			$template = IFEPRO_FEED_DIR . 'templates/' . $layout . '.php';
		}
		$template = apply_filters( 'ifeprofeed_layout_template_path', $template, $layout, $meta, $event );
		if ( ! file_exists( $template ) ) {
			$template = IFEPRO_FEED_DIR . 'templates/card-grid.php';
		}
		if ( file_exists( $template ) ) {
			include $template;
		}
	}

	// -------------------------------------------------------
	// Pagination
	// -------------------------------------------------------

	public function render_pagination( $current_page, $total_pages, $total_events, $per_page, $pagination_type = 'ajax' ) {
		if ( $total_pages <= 1 ) return;

		$current_page = max( 1, min( $current_page, $total_pages ) );

		if ( 'load_more' === $pagination_type ) {
			$loaded = min( $current_page * $per_page, $total_events );
			?>
			<div class="ifeprofeed-load-more-wrap">
				<?php if ( $current_page < $total_pages ) : ?>
					<button class="ifeprofeed-btn ifeprofeed-btn--load-more"
						data-page="<?php echo esc_attr( $current_page + 1 ); ?>"
						data-loaded="<?php echo esc_attr( $loaded ); ?>"
						data-total="<?php echo esc_attr( $total_events ); ?>">
						<?php esc_html_e( 'Load More', 'import-facebook-events' ); ?>
					</button>
				<?php endif; ?>
			</div>
			<?php
			return;
		}

		if ( 'infinite_scroll' === $pagination_type ) {
			if ( $current_page >= $total_pages ) return;
			?>
			<div class="ifeprofeed-infinite-sentinel" data-page="<?php echo esc_attr( $current_page + 1 ); ?>" aria-hidden="true">
				<span class="ifeprofeed-loading-spinner"><?php esc_html_e( 'Loading...', 'import-facebook-events' ); ?></span>
			</div>
			<?php
			return;
		}

		// Numbered AJAX pagination
		?>
		<nav class="ifeprofeed-pagination-nav" aria-label="<?php esc_attr_e( 'Events pagination', 'import-facebook-events' ); ?>">
			<div class="ifeprofeed-pagination-buttons">
				<?php if ( $current_page > 1 ) : ?>
					<button class="ifeprofeed-btn ifeprofeed-btn--pagination" data-page="<?php echo esc_attr( $current_page - 1 ); ?>">
						<svg class="ifeprofeed-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
						<?php esc_html_e( 'Previous', 'import-facebook-events' ); ?>
					</button>
				<?php else : ?>
					<button class="ifeprofeed-btn ifeprofeed-btn--pagination" disabled aria-disabled="true">
						<svg class="ifeprofeed-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
						<?php esc_html_e( 'Previous', 'import-facebook-events' ); ?>
					</button>
				<?php endif; ?>

				<div class="ifeprofeed-pagination-pages">
					<?php
					$start_page = max( 1, $current_page - 2 );
					$end_page   = min( $total_pages, $current_page + 2 );
					if ( $start_page > 1 ) {
						echo '<button class="ifeprofeed-btn ifeprofeed-btn--page" data-page="1">1</button>';
						if ( $start_page > 2 ) echo '<span class="ifeprofeed-pagination-ellipsis">...</span>';
					}
					for ( $i = $start_page; $i <= $end_page; $i++ ) {
						$is_current = $i === $current_page;
						?>
						<button class="ifeprofeed-btn ifeprofeed-btn--page<?php echo $is_current ? ' is-active' : ''; ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?> data-page="<?php echo esc_attr( $i ); ?>">
							<?php echo esc_html( $i ); ?>
						</button>
						<?php
					}
					if ( $end_page < $total_pages ) {
						if ( $end_page < $total_pages - 1 ) echo '<span class="ifeprofeed-pagination-ellipsis">...</span>';
						echo '<button class="ifeprofeed-btn ifeprofeed-btn--page" data-page="' . esc_attr( $total_pages ) . '">' . esc_html( $total_pages ) . '</button>';
					}
					?>
				</div>

				<?php if ( $current_page < $total_pages ) : ?>
					<button class="ifeprofeed-btn ifeprofeed-btn--pagination" data-page="<?php echo esc_attr( $current_page + 1 ); ?>">
						<?php esc_html_e( 'Next', 'import-facebook-events' ); ?>
						<svg class="ifeprofeed-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
					</button>
				<?php else : ?>
					<button class="ifeprofeed-btn ifeprofeed-btn--pagination" disabled aria-disabled="true">
						<?php esc_html_e( 'Next', 'import-facebook-events' ); ?>
						<svg class="ifeprofeed-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59L10 18l6-6-6-6-1.41 1.41L13.17 12z"/></svg>
					</button>
				<?php endif; ?>
			</div>
		</nav>
		<?php
	}

	// -------------------------------------------------------
	// Enqueue assets
	// -------------------------------------------------------

	private function enqueue_public_assets( $meta, $feed_id = 0 ) {
		wp_enqueue_style(
			'ifeprofeed-public',
			IFEPRO_FEED_URL . 'assets/feed-public.css',
			array(),
			IFEPRO_FEED_VERSION
		);
		
		$deps = array();
		if ( isset( $meta['layout'] ) && 'masonry' === $meta['layout'] ) {
			wp_enqueue_script( 'masonry' );
			wp_enqueue_script( 'imagesloaded' );
			$deps = array( 'masonry', 'imagesloaded' );
		}
		
		wp_enqueue_script(
			'ifeprofeed-public',
			IFEPRO_FEED_URL . 'assets/feed-public.js',
			$deps,
			IFEPRO_FEED_VERSION,
			true
		);
		wp_localize_script( 'ifeprofeed-public', 'ifeproFeedData', array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'ajaxNonce'    => wp_create_nonce( 'ifeprofeed_pagination_' . $feed_id ),
			'feedId'       => $feed_id,
			'perPage'      => absint( $meta['per_page'] ),
			'ticket_style' => 'link',
		) );
	}

	// -------------------------------------------------------
	// Helpers
	// -------------------------------------------------------

	private function error( $msg ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<div class="ifeprofeed-feed-error" style="background:#fff3cd;border:1px solid #ffc107;padding:12px 16px;border-radius:4px;color:#856404;font-size:13px;">'
				. esc_html( $msg )
				. '</div>';
		}
		return '';
	}

	private function no_events() {
		return '<div class="ifeprofeed-feed-no-events"><p>'
			. esc_html__( 'No upcoming events found.', 'import-facebook-events' )
			. '</p></div>';
	}

	// -------------------------------------------------------
	// Static helpers used in templates
	// -------------------------------------------------------

	public static function format_date( $date_string, $timezone = '' ) {
		if ( ! $date_string ) return '';
		try {
			// phpcs:disable
			$tz = $timezone ? new DateTimeZone( $timezone ) : wp_timezone();
			// phpcs:enable
			$dt = new DateTime( $date_string, $tz );
			return $dt->format( get_option( 'date_format' ) . ' · ' . get_option( 'time_format' ) );
		} catch ( Exception $e ) {
			return $date_string;
		}
	}

	public static function format_price( $event ) {
		if ( ! empty( $event['is_free'] ) ) {
			return __( 'Free', 'import-facebook-events' );
		}
		$amount   = floatval( $event['min_price'] ?? 0 );
		$currency = $event['currency'] ?? '';
		if ( $amount > 0 ) {
			return ( $currency ? strtoupper( $currency ) . ' ' : '' ) . number_format( $amount, 2 );
		}
		return __( 'Free', 'import-facebook-events' );
	}

	public static function get_days_left_info( $date_string, $timezone = '' ) {
		if ( ! $date_string ) {
			return null;
		}
		try {
			// phpcs:disable
			$tz = $timezone ? new DateTimeZone( $timezone ) : wp_timezone();
			// phpcs:enable
			
			$now = new DateTime( 'now', $tz );
			$now->setTime( 0, 0, 0 );
			
			$event_date = new DateTime( $date_string, $tz );
			$event_date->setTime( 0, 0, 0 );
			
			$interval = $now->diff( $event_date );
			$days = (int) $interval->format( '%r%a' );
			
			if ( $days === 0 ) {
				return array(
					'text'  => __( 'Today', 'import-facebook-events' ),
					'class' => 'today',
				);
			} elseif ( $days === 1 ) {
				return array(
					'text'  => __( 'Tomorrow', 'import-facebook-events' ),
					'class' => 'tomorrow',
				);
			} elseif ( $days > 1 ) {
				return array(
					/* translators: %d: number of days left */
					'text'  => sprintf( _n( '%d Day Left', '%d Days Left', $days, 'import-facebook-events' ), $days ),
					'class' => 'future',
				);
			} else {
				$abs_days = abs( $days );
				return array(
					/* translators: %d: number of days ago */
					'text'  => sprintf( _n( '%d Day Ago', '%d Days Ago', $abs_days, 'import-facebook-events' ), $abs_days ),
					'class' => 'past',
				);
			}
		} catch ( Exception $e ) {
			return null;
		}
	}
}
