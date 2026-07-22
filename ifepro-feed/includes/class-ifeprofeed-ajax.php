<?php
/**
 * Import Facebook Events Live Feed AJAX Handlers.
 *
 * @package Import_Facebook_Events\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IFEPRO_Feed_AJAX {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'wp_ajax_ifeprofeed_clear_cache',      array( $this, 'clear_cache' ) );
		add_action( 'wp_ajax_ifeprofeed_clear_hard_cache', array( $this, 'clear_hard_cache' ) );
		add_action( 'wp_ajax_ifeprofeed_preview_feed',     array( $this, 'preview_feed' ) );
		add_action( 'wp_ajax_ifeprofeed_live_preview',     array( $this, 'live_preview' ) );

		add_action( 'wp_ajax_nopriv_ifeprofeed_load_page', array( $this, 'load_paginated_page' ) );
		add_action( 'wp_ajax_ifeprofeed_load_page',        array( $this, 'load_paginated_page' ) );

		add_action( 'wp_ajax_ifeprofeed_bg_full_fetch',        array( $this, 'bg_full_fetch' ) );
		add_action( 'wp_ajax_nopriv_ifeprofeed_bg_full_fetch', array( $this, 'bg_full_fetch' ) );
	}

	/**
	 * Clear feed transient cache.
	 */

	public function clear_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'import-facebook-events' ) ) );
		}
		$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		$feed_id = absint( $_POST['feed_id'] ?? 0 );

		if ( ! wp_verify_nonce( $nonce, 'ifeprofeed_clear_cache_' . $feed_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'import-facebook-events' ) ) );
		}
		if ( ! $feed_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'import-facebook-events' ) ) );
		}

		IFEPRO_Feed_API::instance()->clear_cache( $feed_id );
		wp_send_json_success( array( 'message' => __( 'Cache cleared! Next page load will fetch fresh data from Facebook.', 'import-facebook-events' ) ) );
	}

	/**
	 * Clear hard cache (transients and image cache).
	 */

	public function clear_hard_cache() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'import-facebook-events' ) ) );
		}
		$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		$feed_id = absint( $_POST['feed_id'] ?? 0 );

		if ( ! wp_verify_nonce( $nonce, 'ifeprofeed_clear_hard_cache' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'import-facebook-events' ) ) );
		}
		if ( ! $feed_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'import-facebook-events' ) ) );
		}

		IFEPRO_Feed_DB::instance()->delete_all_images();
		IFEPRO_Feed_API::instance()->clear_cache( $feed_id );

		wp_remote_post(
			admin_url( 'admin-ajax.php' ),
			array(
				'timeout'   => 0.01,
				'blocking'  => false,
				'sslverify' => apply_filters( 'ifeprofeed_https_local_ssl_verify', false ),
				'body'      => array(
					'action'  => 'ifeprofeed_bg_full_fetch',
					'feed_id' => $feed_id,
					'nonce'   => md5( 'ifeprofeed_full_fetch_' . $feed_id . wp_salt() ),
				),
			)
		);

		wp_send_json_success( array( 'message' => __( 'Hard cache cleared! Fetching fresh HQ images in background — they will appear automatically.', 'import-facebook-events' ) ) );
	}

	/**
	 * Background handler: full event fetch after feed save.
	 */

	public function bg_full_fetch() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$feed_id = absint( $_POST['feed_id'] ?? 0 );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$nonce   = sanitize_text_field( $_POST['nonce'] ?? '' );
		$expected = md5( 'ifeprofeed_full_fetch_' . $feed_id . wp_salt() );

		if ( ! $feed_id || ! hash_equals( $expected, $nonce ) ) {
			wp_die();
		}

		ignore_user_abort( true );
		// phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		set_time_limit( 300 );

		$api  = IFEPRO_Feed_API::instance();
		$meta = $api->get_feed_meta( $feed_id );

		if ( ! in_array( $meta['source_type'] ?? '', array( 'page_id', 'group_id' ), true ) ) {
			$events = $api->get_events( $feed_id, true );
			
			if ( 'ical' === ( $meta['source_type'] ?? '' ) && ! is_wp_error( $events ) && ! empty( $events ) ) {
				$this->fetch_hq_batch_sync( $feed_id, $events );
			}
			
			wp_die();
		}

		$fetch_filter = 'group_id' === $meta['source_type'] ? 'ifeprofeed_fetch_group_events' : 'ifeprofeed_fetch_page_events';
		$duration     = absint( $meta['cache_duration'] ) * MINUTE_IN_SECONDS;

		// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
		$response = apply_filters( $fetch_filter, new WP_Error( 'ifeprofeed_pro_only', '' ), $meta, '', $api );

		if ( is_wp_error( $response ) ) {
			wp_die();
		}

		$page1_events = $api->dedup( $response['events'] ?? array() );
		$page1_events = $api->sort_events( $page1_events );

		$api->clear_cache( $feed_id );
		$api->save_page_cache( $feed_id, 1, $page1_events, $duration );
		update_post_meta( $feed_id, '_ifeprofeed_last_fetched', time() );

		$this->fetch_hq_batch_sync( $feed_id, $page1_events );

		$cursor     = $response['cursor'] ?? '';
		$has_more   = ! empty( $response['has_more'] ) && $cursor;
		$scrape_page = 2;
		$max_pages   = 20;

		while ( $has_more && $scrape_page <= $max_pages ) {
			// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
			$response = apply_filters( $fetch_filter, new WP_Error( 'ifeprofeed_pro_only', '' ), $meta, $cursor, $api );

			if ( is_wp_error( $response ) ) break;

			$page_events = $api->dedup( $response['events'] ?? array() );

			$api->save_page_cache( $feed_id, $scrape_page, $page_events, $duration );

			$this->fetch_hq_batch_sync( $feed_id, $page_events );

			$cursor   = $response['cursor'] ?? '';
			$has_more = ! empty( $response['has_more'] ) && $cursor;
			$scrape_page++;
		}

		delete_transient( 'ifeprofeed_lock_running_' . $feed_id );

		wp_die();
	}

	/**
	 * Fetch HQ images in batches.
	 */
	private function fetch_hq_batch_sync( $feed_id, $events ) {
		$db  = IFEPRO_Feed_DB::instance();
		$api = IFEPRO_Feed_API::instance();

		$pending = array();
		foreach ( $events as $event ) {
			$event_id = (string) ( $event['id'] ?? '' );
			if ( ! $event_id ) continue;
			if ( $db->get_image( $event_id ) ) continue; // Already in DB
			$pending[] = $event_id;
		}

		if ( empty( $pending ) ) return;

		$batches = array_chunk( array_unique( $pending ), 5 );

		foreach ( $batches as $batch ) {
			foreach ( $batch as $event_id ) {
				try {
					$data = $api->getEventById( $event_id );
					if ( ! empty( $data['cover_image'] ) ) {
						$db->save_image( $event_id, $data['cover_image'] );
						// Update the transient cache for this feed inline
						$this->update_event_image_in_transients( $feed_id, $event_id, $data['cover_image'] );
					}
				} catch ( \Exception $e ) {
					// Skip — will retry on next cache clear
				}
			}
			// Small pause between batches to be gentle on FB
			usleep( 500000 ); // 0.5 sec between batches
		}
	}

	/**
	 * Update image_url in the single transient for a feed after HQ fetch.
	 */
	private function update_event_image_in_transients( $feed_id, $event_id, $image_url ) {
		$key    = 'ifeprofeed_p_' . absint( $feed_id ) . '_all';
		$events = get_transient( $key );
		if ( ! is_array( $events ) ) return;

		$updated = false;
		foreach ( $events as &$ev ) {
			if ( (string) ( $ev['id'] ?? '' ) === (string) $event_id ) {
				$ev['image_url'] = $image_url;
				$updated = true;
			}
		}
		unset( $ev );

		if ( $updated ) {
			$timeout   = get_option( '_transient_timeout_' . $key );
			$remaining = $timeout ? max( 60, $timeout - time() ) : HOUR_IN_SECONDS;
			set_transient( $key, $events, $remaining );
		}
	}

	// -------------------------------------------------------
	// Preview feed (admin — count check only)
	// -------------------------------------------------------

	public function preview_feed() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'import-facebook-events' ) ) );
		}
		$nonce   = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
		$feed_id = absint( $_POST['feed_id'] ?? 0 );

		if ( ! wp_verify_nonce( $nonce, 'ifeprofeed_admin_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'import-facebook-events' ) ) );
		}

		$events = IFEPRO_Feed_API::instance()->get_events( $feed_id, true );
		if ( is_wp_error( $events ) ) {
			wp_send_json_error( array( 'message' => $events->get_error_message() ) );
		}

		wp_send_json_success( array(
			'total'   => count( $events ),
			'message' => sprintf(
				/* translators: %d: number of events */
				__( '%d events fetched successfully.', 'import-facebook-events' ),
				count( $events )
			),
		) );
	}

	// -------------------------------------------------------
	// Public paginated load
	// -------------------------------------------------------

	public function load_paginated_page() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$feed_id  = absint( $_POST['feed_id'] ?? 0 );
		$page     = absint( $_POST['page']     ?? 1 );
		$per_page = absint( $_POST['per_page'] ?? 12 );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( ! $feed_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid feed ID.', 'import-facebook-events' ) ) );
		}

		$feed_post = get_post( $feed_id );
		if ( ! $feed_post || IFEPRO_FEED_CPT !== $feed_post->post_type || 'publish' !== $feed_post->post_status ) {
			wp_send_json_error( array( 'message' => __( 'Feed not found.', 'import-facebook-events' ) ) );
		}

		$events = IFEPRO_Feed_API::instance()->get_events( $feed_id, false, $page );
		if ( is_wp_error( $events ) ) {
			wp_send_json_error( array( 'message' => $events->get_error_message() ) );
		}
		if ( empty( $events ) ) {
			wp_send_json_error( array( 'message' => __( 'No events found.', 'import-facebook-events' ) ) );
		}

		$meta         = IFEPRO_Feed_API::instance()->get_feed_meta( $feed_id );
		$total_events = count( $events );
		$total_pages  = (int) ceil( $total_events / $per_page );
		$page         = max( 1, min( $page, $total_pages ) );
		$page_events  = array_slice( $events, ( $page - 1 ) * $per_page, $per_page );

		ob_start();
		foreach ( $page_events as $event ) {
			IFEPRO_Feed_Shortcode::instance()->render_event_card( $event, $meta );
		}
		$events_html = ob_get_clean();

		ob_start();
		IFEPRO_Feed_Shortcode::instance()->render_pagination( $page, $total_pages, $total_events, $per_page, $meta['pagination_type'] ?? 'ajax' );
		$pagination_html = ob_get_clean();

		wp_send_json_success( array(
			'events_html'     => $events_html,
			'pagination_html' => $pagination_html,
			'current_page'    => $page,
			'total_pages'     => $total_pages,
			'total_events'    => $total_events,
		) );
	}

	// -------------------------------------------------------
	// Live preview (admin builder)
	// -------------------------------------------------------

	public function live_preview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'import-facebook-events' ) ) );
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$feed_id = absint( $_POST['feed_id'] ?? 0 );

		$time_filter = sanitize_text_field( wp_unslash( $_POST['_ifeprofeed_time_filter'] ?? 'all' ) );
		if ( 'past' === $time_filter ) {
			$time_filter = 'current_future';
		}

		$register_label = sanitize_text_field( $_POST['_ifeprofeed_register_label'] ?? __( 'View Event', 'import-facebook-events' ) );

		$cache_val = $_POST['_ifeprofeed_cache_duration'] ?? '1440';
		if ( 'custom' === $cache_val ) {
			$custom_hours  = max( 1, absint( $_POST['_ifeprofeed_cache_duration_custom'] ?? 5 ) );
			$cache_minutes = $custom_hours * 60;
		} else {
			$cache_minutes = absint( $cache_val ) ?: 1440;
		}

		$posted_meta = array(
			'source_type'     => sanitize_text_field( $_POST['_ifeprofeed_source_type'] ?? 'page_id' ),
			'page_id'         => sanitize_text_field( $_POST['_ifeprofeed_page_id']     ?? '' ),
			'group_id'        => sanitize_text_field( $_POST['_ifeprofeed_group_id']    ?? '' ),
			'event_ids'       => sanitize_text_field( $_POST['_ifeprofeed_event_ids']   ?? '' ),
			'ical_url'        => sanitize_text_field( $_POST['_ifeprofeed_ical_url']    ?? '' ),
			'time_filter'     => $time_filter,
			'cache_duration'  => $cache_minutes,
			'pagination_type' => sanitize_text_field( wp_unslash( $_POST['_ifeprofeed_pagination_type'] ?? 'ajax' ) ),
			'per_page'        => absint( $_POST['_ifeprofeed_per_page'] ?? 12 ),
			'layout'          => sanitize_text_field( wp_unslash( $_POST['_ifeprofeed_layout'] ?? 'card-grid' ) ),
			'columns'         => absint( $_POST['_ifeprofeed_columns'] ?? 3 ),
			'show_image'      => ! empty( $_POST['_ifeprofeed_show_image'] ),
			'show_date'       => ! empty( $_POST['_ifeprofeed_show_date'] ),
			'show_venue'      => ! empty( $_POST['_ifeprofeed_show_venue'] ),
			'show_organizer'  => ! empty( $_POST['_ifeprofeed_show_organizer'] ),
			'show_price'      => ! empty( $_POST['_ifeprofeed_show_price'] ),
			'show_category'   => false,
			'show_tags'       => false,
			'show_ticket_btn' => ! empty( $_POST['_ifeprofeed_show_ticket_btn'] ),
			'ticket_style'    => 'link',
			'free_label'      => __( 'Free', 'import-facebook-events' ),
			'paid_label'      => __( 'Paid', 'import-facebook-events' ),
			'register_label'  => $register_label,
			'hide_online'     => ! empty( $_POST['_ifeprofeed_hide_online'] ),
			'start_date'      => sanitize_text_field( wp_unslash( $_POST['_ifeprofeed_start_date'] ?? '' ) ),
			'end_date'        => sanitize_text_field( wp_unslash( $_POST['_ifeprofeed_end_date']   ?? '' ) ),
			'category_id'     => '',
			'tag_query'       => '',
			'tags_filter'     => '',
			'is_preview'      => true,
			'feed_id'         => $feed_id,
		);
		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		// Fallback: if source fields empty, pull from saved meta
		if ( $feed_id ) {
			$saved = IFEPRO_Feed_API::instance()->get_feed_meta( $feed_id );
			if ( 'page_id'    === $posted_meta['source_type'] && empty( $posted_meta['page_id'] ) )    $posted_meta['page_id']    = $saved['page_id']    ?? '';
			if ( 'group_id'   === $posted_meta['source_type'] && empty( $posted_meta['group_id'] ) )   $posted_meta['group_id']   = $saved['group_id']   ?? '';
			if ( 'event_ids'  === $posted_meta['source_type'] && empty( $posted_meta['event_ids'] ) )  $posted_meta['event_ids']  = $saved['event_ids']  ?? '';
			if ( 'ical_url'   === $posted_meta['source_type'] && empty( $posted_meta['ical_url'] ) )   $posted_meta['ical_url']   = $saved['ical_url']   ?? '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$is_full_preview = ! empty( $_POST['is_full_preview'] ) && 'true' === $_POST['is_full_preview'];

		$events = IFEPRO_Feed_API::instance()->fetch_preview_events( $posted_meta );

		if ( is_wp_error( $events ) ) {
			wp_send_json_error( array( 'message' => sprintf(
				/* translators: %s: error message */
				__( 'Could not load preview: %s. Please check your Source settings.', 'import-facebook-events' ),
				$events->get_error_message()
			) ) );
		}

		if ( empty( $events ) ) {
			wp_send_json_error( array( 'message' => __( 'No events found. Please verify your Source Data.', 'import-facebook-events' ) ) );
		}

		$per_page       = absint( $posted_meta['per_page'] );
		$preview_limit  = $is_full_preview ? max( 1, $per_page ) : min( 5, max( 1, $per_page ) );
		$preview_events = array_slice( $events, 0, $preview_limit );

		ob_start();
		?>
		<div class="ifeprofeed-feed-wrap ifeprofeed-layout-<?php echo esc_attr( $posted_meta['layout'] ); ?> ifeprofeed-cols-<?php echo esc_attr( $posted_meta['columns'] ); ?> ifeprofeed-preview-sample">
			<div class="ifeprofeed-events-grid">
				<?php foreach ( $preview_events as $event ) : ?>
					<?php IFEPRO_Feed_Shortcode::instance()->render_event_card( $event, $posted_meta ); ?>
				<?php endforeach; ?>
			</div>
			<div style="margin-top:15px;font-size:11px;color:#777;text-align:center;font-style:italic;">
				<?php esc_html_e( 'Note: Blurry/low-quality images may appear in Live Preview for new events to keep the editor fast. High-quality HD images load automatically on the front-end after saving.', 'import-facebook-events' ); ?>
			</div>
		</div>
		<?php
		$html    = ob_get_clean();
		$warning = IFEPRO_Feed_API::instance()->last_preview_warning;
		wp_send_json_success( array(
			'html'    => $html,
			'warning' => $warning,
		) );
	}
}
