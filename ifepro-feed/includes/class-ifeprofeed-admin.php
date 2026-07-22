<?php
/**
 * Import Facebook Events Live Feed Admin Meta Boxes.
 *
 * @package Import_Facebook_Events\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IFEPRO_Feed_Admin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_' . IFEPRO_FEED_CPT, array( $this, 'save_meta' ) );
		add_action( 'admin_post_ifeprofeed_clear_cache', array( $this, 'handle_clear_cache_row_action' ) );
		add_action( 'load-edit.php', array( $this, 'maybe_redirect_empty_feed_list' ) );
	}

	public function maybe_redirect_empty_feed_list() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-' . IFEPRO_FEED_CPT !== $screen->id ) return;
		$has_visited = get_option( 'ifeprofeed_has_visited_list', false );
		if ( ! $has_visited ) {
			update_option( 'ifeprofeed_has_visited_list', true );
			wp_safe_redirect( admin_url( 'post-new.php?post_type=' . IFEPRO_FEED_CPT ) );
			exit;
		}
	}

	/**
	 * Register feed meta boxes.
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'ifeprofeed_shortcode_box',
			__( 'Your Shortcode', 'import-facebook-events' ),
			array( $this, 'render_shortcode_box' ),
			IFEPRO_FEED_CPT, 'normal', 'high'
		);
		add_meta_box(
			'ifeprofeed_settings',
			__( 'Feed Settings', 'import-facebook-events' ),
			array( $this, 'render_meta_box' ),
			IFEPRO_FEED_CPT, 'normal', 'default'
		);
	}

	public function render_shortcode_box( $post ) {
		if ( ! $post->ID || 'auto-draft' === $post->post_status ) {
			echo '<p>' . esc_html__( 'Save the feed first to get your shortcode.', 'import-facebook-events' ) . '</p>';
			return;
		}
		$shortcode = '[ifepro_live_feed id="' . $post->ID . '"]';
		?>
		<p style="margin-bottom:6px;font-size:12px;color:#666;">
			<?php esc_html_e( 'Paste this shortcode into any page or post:', 'import-facebook-events' ); ?>
		</p>
		<div style="display:flex;gap:6px;align-items:center;">
			<input type="text" readonly
				value="<?php echo esc_attr( $shortcode ); ?>"
				id="ifeprofeed-shortcode-input"
				style="flex:1;font-family:monospace;font-size:13px;"
				onclick="this.select();"
			/>
			<button type="button" class="button" id="ifeprofeed-copy-shortcode-btn">
				<?php esc_html_e( 'Copy', 'import-facebook-events' ); ?>
			</button>
		</div>
		<p style="margin-top:8px;font-size:12px;color:#666;">
			<?php esc_html_e( 'Override options inline:', 'import-facebook-events' ); ?><br>
			<code style="font-size:11px;">[ifepro_live_feed id="<?php echo esc_html( $post->ID ); ?>" columns="2" per_page="6"]</code>
		</p>
		<?php
	}

	/**
	 * Render feed tabbed meta box.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'ifeprofeed_save_meta', 'ifeprofeed_nonce' );
		$meta = IFEPRO_Feed_API::instance()->get_feed_meta( $post->ID );
		?>
		<div class="ifeprofeed-builder-layout">
		<div class="ifeprofeed-builder-left">
		<div class="ifeprofeed-tabs">
			<ul class="ifeprofeed-tabs-nav">
				<li><a href="#ifeprofeed-tab-source" class="active"><?php esc_html_e( 'Source', 'import-facebook-events' ); ?></a></li>
				<li><a href="#ifeprofeed-tab-display"><?php esc_html_e( 'Display', 'import-facebook-events' ); ?></a></li>
				<li><a href="#ifeprofeed-tab-tickets"><?php esc_html_e( 'Buttons', 'import-facebook-events' ); ?></a></li>
				<li><a href="#ifeprofeed-tab-filters"><?php esc_html_e( 'Filters', 'import-facebook-events' ); ?></a></li>
				<li><a href="#ifeprofeed-tab-settings"><?php esc_html_e( 'Settings', 'import-facebook-events' ); ?></a></li>
			</ul>

			<?php // ===== TAB 1: SOURCE ===== ?>
			<div id="ifeprofeed-tab-source" class="ifeprofeed-tab-content active">
				<table class="form-table ifeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Source Type', 'import-facebook-events' ); ?></th>
						<td>
							<?php
							$sources = array(
								'page_id'   => __( 'Facebook Page ID/Slug', 'import-facebook-events' ),
								'group_id'  => __( 'Facebook Group URL/ID', 'import-facebook-events' ),
								'event_ids' => __( 'Specific Event IDs', 'import-facebook-events' ),
								'ical_url'  => __( 'iCal URL', 'import-facebook-events' ),
							);
							foreach ( $sources as $val => $label ) :
								$disabled = '';
								$label_suffix = '';
								$allowed_sources = apply_filters( 'ifeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) );
								if ( ! in_array( $val, $allowed_sources, true ) ) {
									$disabled = 'disabled';
									$label_suffix = ' <span class="ife-pro-badge">' . __( 'PRO', 'import-facebook-events' ) . '</span>';
								}
							?>
							<label style="margin-right:16px; <?php echo esc_attr( $disabled ? 'opacity:0.6; cursor:not-allowed;' : '' ); ?>">
								<input type="radio"
									name="_ifeprofeed_source_type"
									value="<?php echo esc_attr( $val ); ?>"
									<?php checked( $meta['source_type'], $val ); ?>
									<?php echo esc_attr( $disabled ); ?>
									class="ifeprofeed-source-type-radio"
								/>
								<?php echo esc_html( $label ) . wp_kses_post( $label_suffix ); ?>
							</label>
							<?php endforeach; ?>
						</td>
					</tr>

					<tr class="ifeprofeed-source-row ifeprofeed-source-page_id" <?php echo 'page_id' !== $meta['source_type'] ? 'style="display:none"' : ''; ?>>
						<th><label for="ifeprofeed_page_id"><?php esc_html_e( 'Facebook Page ID / Slug', 'import-facebook-events' ); ?></label></th>
						<td>
							<textarea id="ifeprofeed_page_id" name="_ifeprofeed_page_id" rows="3"
								class="large-text" placeholder="e.g. Fashionmantraexhibitions, 100064789211029" <?php disabled( ! in_array( 'page_id', apply_filters( 'ifeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) ), true ) ); ?>><?php echo esc_textarea( $meta['page_id'] ?? '' ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Enter Facebook Page IDs or slugs, separated by commas or newlines.', 'import-facebook-events' ); ?>
								<?php if ( ! in_array( 'page_id', apply_filters( 'ifeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) ), true ) ) : ?>
									<br><span style="color:#005AE0;font-weight:bold;">
										<?php
										/* translators: %s: link to pro version */
										echo wp_kses_post( sprintf( __( 'Available in %s version.', 'import-facebook-events' ), '<a href="https://xylusthemes.com/plugins/import-facebook-events/" target="_blank" style="color:#005AE0;text-decoration:underline;">Pro</a>' ) );
										?>
									</span>
								<?php endif; ?>
							</p>
						</td>
					</tr>

					<tr class="ifeprofeed-source-row ifeprofeed-source-group_id" <?php echo 'group_id' !== $meta['source_type'] ? 'style="display:none"' : ''; ?>>
						<th><label for="ifeprofeed_group_id"><?php esc_html_e( 'Facebook Group URL / ID', 'import-facebook-events' ); ?></label></th>
						<td>
							<textarea id="ifeprofeed_group_id" name="_ifeprofeed_group_id" rows="3"
								class="large-text" placeholder="e.g. 629555443733186, https://www.facebook.com/groups/anothergroup" <?php disabled( ! in_array( 'group_id', apply_filters( 'ifeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) ), true ) ); ?>><?php echo esc_textarea( $meta['group_id'] ?? '' ); ?></textarea>
							<p class="description">
								<?php esc_html_e( 'Enter URLs or numeric IDs of public Facebook Groups, separated by commas or newlines.', 'import-facebook-events' ); ?>
								<?php if ( ! in_array( 'group_id', apply_filters( 'ifeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) ), true ) ) : ?>
									<br><span style="color:#005AE0;font-weight:bold;">
										<?php
										/* translators: %s: link to pro version */
										echo wp_kses_post( sprintf( __( 'Available in %s version.', 'import-facebook-events' ), '<a href="https://xylusthemes.com/plugins/import-facebook-events/" target="_blank" style="color:#005AE0;text-decoration:underline;">Pro</a>' ) );
										?>
									</span>
								<?php endif; ?>
							</p>
						</td>
					</tr>

					<tr class="ifeprofeed-source-row ifeprofeed-source-event_ids" <?php echo 'event_ids' !== $meta['source_type'] ? 'style="display:none"' : ''; ?>>
						<th><label for="ifeprofeed_event_ids"><?php esc_html_e( 'Event IDs', 'import-facebook-events' ); ?></label></th>
						<td>
							<textarea id="ifeprofeed_event_ids" name="_ifeprofeed_event_ids" rows="3"
								class="large-text" placeholder="e.g. 3124795954373363, 1531644045138212"><?php echo esc_textarea( $meta['event_ids'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Enter Facebook Event IDs, separated by commas or newlines.', 'import-facebook-events' ); ?></p>
						</td>
					</tr>

					<tr class="ifeprofeed-source-row ifeprofeed-source-ical_url" <?php echo 'ical_url' !== $meta['source_type'] ? 'style="display:none"' : ''; ?>>
						<th><label for="ifeprofeed_ical_url"><?php esc_html_e( 'Facebook iCal URL', 'import-facebook-events' ); ?></label></th>
						<td>
							<textarea id="ifeprofeed_ical_url" name="_ifeprofeed_ical_url" rows="3"
								class="large-text" placeholder="e.g. https://www.facebook.com/events/ical/upcoming/?uid=...&key=..."><?php echo esc_textarea( $meta['ical_url'] ); ?></textarea>
							<p class="description">
								<?php if ( ! ife_is_pro() ) : ?>
									<?php esc_html_e( 'Enter Facebook iCal export URL.', 'import-facebook-events' ); ?> <a href="https://xylusthemes.com/plugins/import-facebook-events/" target="_blank" style="color:#005AE0;font-weight:bold;"><?php esc_html_e( 'Upgrade to Pro', 'import-facebook-events' ); ?></a> <?php esc_html_e( 'to import from multiple iCal URLs.', 'import-facebook-events' ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Enter Facebook iCal export URLs, separated by commas or newlines.', 'import-facebook-events' ); ?>
								<?php endif; ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<?php // ===== TAB 2: DISPLAY ===== ?>
			<div id="ifeprofeed-tab-display" class="ifeprofeed-tab-content">
				<table class="form-table ifeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Layout', 'import-facebook-events' ); ?></th>
						<td>
							<div class="ifeprofeed-layout-picker">
								<?php
								$layouts = array(
									'card-grid'    => array( 'label' => __( 'Card Grid', 'import-facebook-events' ), 'icon' => '⊞' ),
									'list'         => array( 'label' => __( 'List', 'import-facebook-events' ), 'icon' => '☰' ),
									'masonry'      => array( 'label' => __( 'Masonry', 'import-facebook-events' ), 'icon' => '⊟' ),
									'minimal-grid' => array( 'label' => __( 'Minimal Grid', 'import-facebook-events' ), 'icon' => '◫' ),
									'compact-list' => array( 'label' => __( 'Compact List', 'import-facebook-events' ), 'icon' => '☶' ),
									'timeline'     => array( 'label' => __( 'Timeline', 'import-facebook-events' ), 'icon' => '↧' ),
									'ticket-list'  => array( 'label' => __( 'Ticket', 'import-facebook-events' ), 'icon' => '🎟' ),
								);
								foreach ( $layouts as $val => $data ) :
									$allowed_layouts = apply_filters( 'ifeprofeed_allowed_layouts', array( 'card-grid', 'list' ) );
									$is_allowed = in_array( $val, $allowed_layouts, true );
									$class = $meta['layout'] === $val ? 'active' : '';
									if ( ! $is_allowed ) {
										$class .= ' ifeprofeed-layout-pro-only';
									}
								?>
								<label class="ifeprofeed-layout-option <?php echo esc_attr( $class ); ?>" style="<?php echo ! $is_allowed ? 'opacity:0.6; cursor:not-allowed; position:relative;' : ''; ?>">
									<input type="radio" name="_ifeprofeed_layout" value="<?php echo esc_attr( $val ); ?>" <?php checked( $meta['layout'], $val ); ?> <?php disabled( ! $is_allowed ); ?> />
									<span class="ifeprofeed-layout-icon"><?php echo esc_html( $data['icon'] ); ?></span>
									<span class="ifeprofeed-layout-label"><?php echo esc_html( $data['label'] ); ?></span>
									<?php if ( ! $is_allowed ) : ?>
										<span class="ife-pro-badge"><?php esc_html_e( 'PRO', 'import-facebook-events' ); ?></span>
									<?php endif; ?>
								</label>
								<?php endforeach; ?>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Columns', 'import-facebook-events' ); ?></th>
						<td>
							<?php $this->render_radio_row( '_ifeprofeed_columns', $meta['columns'], array( 1, 2, 3, 4 ) ); ?>
							<p class="description"><?php esc_html_e( 'Number of columns on desktop. Mobile is always 1 column.', 'import-facebook-events' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Show Fields', 'import-facebook-events' ); ?></th>
						<td>
							<?php
							$toggles = array(
								'_ifeprofeed_show_image'      => array( 'label' => __( 'Event Cover Image', 'import-facebook-events' ), 'default' => true ),
								'_ifeprofeed_show_date'       => array( 'label' => __( 'Date & Time', 'import-facebook-events' ), 'default' => true ),
								'_ifeprofeed_show_venue'      => array( 'label' => __( 'Venue / Location', 'import-facebook-events' ), 'default' => true ),
								'_ifeprofeed_show_organizer'  => array( 'label' => __( 'Organizer Name', 'import-facebook-events' ), 'default' => false ),
								'_ifeprofeed_show_ticket_btn' => array( 'label' => __( '"View Event" Button', 'import-facebook-events' ), 'default' => true ),
							);
							$meta_keys = array(
								'_ifeprofeed_show_image'      => $meta['show_image'],
								'_ifeprofeed_show_date'       => $meta['show_date'],
								'_ifeprofeed_show_venue'      => $meta['show_venue'],
								'_ifeprofeed_show_organizer'  => $meta['show_organizer'],
								'_ifeprofeed_show_ticket_btn' => $meta['show_ticket_btn'],
							);
							foreach ( $toggles as $key => $info ) :
								$checked = $meta_keys[ $key ] ?? $info['default'];
							?>
							<label style="display:block;margin-bottom:6px;">
								<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( $checked, true ); echo checked( $checked, '1', false ); ?> />
								<?php echo esc_html( $info['label'] ); ?>
							</label>
							<?php endforeach; ?>
						</td>
					</tr>
				</table>
			</div>

			<?php // ===== TAB 3: TICKETS ===== ?>
			<div id="ifeprofeed-tab-tickets" class="ifeprofeed-tab-content">
				<table class="form-table ifeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Button Type', 'import-facebook-events' ); ?></th>
						<td>
							<p class="description"><?php esc_html_e( 'Buttons link directly to the Facebook event page.', 'import-facebook-events' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><label for="ifeprofeed_register_label"><?php esc_html_e( 'Button Label', 'import-facebook-events' ); ?></label></th>
						<td>
							<input type="text" id="ifeprofeed_register_label" name="_ifeprofeed_register_label"
								value="<?php echo esc_attr( $meta['register_label'] ); ?>"
								class="regular-text" />
						</td>
					</tr>
				</table>
			</div>
			</div>

			<?php // ===== TAB 4: FILTERS ===== ?>
			<div id="ifeprofeed-tab-filters" class="ifeprofeed-tab-content">
				<table class="form-table ifeprofeed-form-table">
					<tr>
						<th><?php esc_html_e( 'Time Filter', 'import-facebook-events' ); ?></th>
						<td>
							<?php $this->render_select( '_ifeprofeed_time_filter', $meta['time_filter'], array(
								'today'            => __( 'Today', 'import-facebook-events' ),
								'upcoming_week'    => __( 'Upcoming Week', 'import-facebook-events' ),
								'upcoming_15_days' => __( 'Upcoming 15 Days', 'import-facebook-events' ),
								'upcoming_month'   => __( 'Upcoming Month', 'import-facebook-events' ),
								'current_future'   => __( 'All Upcoming / Current', 'import-facebook-events' ),
								'custom'           => __( 'Custom Date Range', 'import-facebook-events' ),
								'all'              => __( 'All (No Filter)', 'import-facebook-events' ),
							) ); ?>
						</td>
					</tr>
					<tr class="ifeprofeed-time-row ifeprofeed-time-custom" <?php echo 'custom' !== $meta['time_filter'] ? 'style="display:none"' : ''; ?>>
						<th><label for="ifeprofeed_start_date"><?php esc_html_e( 'Start Date', 'import-facebook-events' ); ?></label></th>
						<td>
							<input type="text" id="ifeprofeed_start_date" name="_ifeprofeed_start_date"
								value="<?php echo esc_attr( $meta['start_date'] ); ?>"
								class="regular-text ifeprofeed-datepicker" placeholder="YYYY-MM-DD" />
						</td>
					</tr>
					<tr class="ifeprofeed-time-row ifeprofeed-time-custom" <?php echo 'custom' !== $meta['time_filter'] ? 'style="display:none"' : ''; ?>>
						<th><label for="ifeprofeed_end_date"><?php esc_html_e( 'End Date', 'import-facebook-events' ); ?></label></th>
						<td>
							<input type="text" id="ifeprofeed_end_date" name="_ifeprofeed_end_date"
								value="<?php echo esc_attr( $meta['end_date'] ); ?>"
								class="regular-text ifeprofeed-datepicker" placeholder="YYYY-MM-DD" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Events Per Page', 'import-facebook-events' ); ?></th>
						<td>
							<?php $this->render_radio_row( '_ifeprofeed_per_page', $meta['per_page'], array( 6, 9, 10, 12, 20, 30, 40, 50 ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Pagination Type', 'import-facebook-events' ); ?></th>
						<td>
							<?php $this->render_select( '_ifeprofeed_pagination_type', $meta['pagination_type'], array(
								'ajax'            => __( 'Numbered Pagination (AJAX)', 'import-facebook-events' ),
								'load_more'       => __( 'Load More Button', 'import-facebook-events' ),
								'infinite_scroll' => __( 'Infinite Scroll', 'import-facebook-events' ),
								'none'            => __( 'No Pagination (Show All)', 'import-facebook-events' ),
							) ); ?>
						</td>
					</tr>
					<!-- <tr>
						<th><?php //esc_html_e( 'Online Events', 'import-facebook-events' ); ?></th>
						<td>
							<label>
								<input type="checkbox" name="_ifeprofeed_hide_online" value="1" <?php //checked( $meta['hide_online'], '1' ); ?> />
								<?php //esc_html_e( 'Hide online-only events', 'import-facebook-events' ); ?>
							</label>
						</td>
					</tr> -->
				</table>
			</div>

			<?php // ===== TAB 5: SETTINGS ===== ?>
			<div id="ifeprofeed-tab-settings" class="ifeprofeed-tab-content">
				<table class="form-table ifeprofeed-form-table">
					<tr>
					<th><?php esc_html_e( 'Cache Duration', 'import-facebook-events' ); ?></th>
					<td>
						<?php
						$presets      = array( 60 => __( '1 Hour', 'import-facebook-events' ), 360 => __( '6 Hours', 'import-facebook-events' ), 720 => __( '12 Hours', 'import-facebook-events' ), 1440 => __( '24 Hours', 'import-facebook-events' ) );
						$current_val  = absint( $meta['cache_duration'] ?: 1440 );
						$is_preset    = array_key_exists( $current_val, $presets );
						$is_custom    = ! $is_preset;
						$custom_hours = $is_custom ? round( $current_val / 60 ) : 5;
						?>
						<?php foreach ( $presets as $val => $label ) : ?>
						<label style="margin-right:12px;">
							<input type="radio" name="_ifeprofeed_cache_duration" value="<?php echo esc_attr( $val ); ?>"
								class="ifeprofeed-cache-preset"
								<?php checked( $is_preset && $current_val === $val ); ?> />
							<?php echo esc_html( $label ); ?>
						</label>
						<?php endforeach; ?>
						<label style="margin-right:12px;">
							<input type="radio" name="_ifeprofeed_cache_duration" value="custom"
								class="ifeprofeed-cache-preset"
								<?php checked( $is_custom ); ?> />
							<?php esc_html_e( 'Custom', 'import-facebook-events' ); ?>
						</label>
						<span class="ifeprofeed-cache-custom-wrap" <?php echo ! $is_custom ? 'style="display:none"' : ''; ?>>
							<input type="number" name="_ifeprofeed_cache_duration_custom" id="ifeprofeed_cache_custom"
								value="<?php echo esc_attr( $custom_hours ); ?>"
								min="1" step="1" class="small-text" placeholder="e.g. 5" />
							<span><?php esc_html_e( 'hours', 'import-facebook-events' ); ?></span>
						</span>
						<p class="description"><?php esc_html_e( 'Events are fetched from Facebook once per this interval.', 'import-facebook-events' ); ?></p>
					</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Auto-Refresh Cache', 'import-facebook-events' ); ?></th>
						<td>
							<?php $has_as = function_exists( 'as_schedule_recurring_action' ); ?>
							<label>
								<input type="checkbox" name="_ifeprofeed_auto_refresh" value="1"
									<?php checked( $meta['auto_refresh'], '1' ); ?>
									<?php disabled( ! $has_as ); ?> />
								<?php esc_html_e( 'Automatically refresh cache in background', 'import-facebook-events' ); ?>
								<strong><?php esc_html_e( '(uses Action Scheduler)', 'import-facebook-events' ); ?></strong>
							</label>
							<?php if ( ! $has_as ) : ?>
							<p class="description" style="color:#d63638;">
								<?php esc_html_e( 'Action Scheduler is not available. Please install it to use this feature.', 'import-facebook-events' ); ?>
							</p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Manual Cache Clear', 'import-facebook-events' ); ?></th>
						<td>
							<?php if ( $post->ID && 'auto-draft' !== $post->post_status ) : ?>
							<button type="button" class="button button-secondary"
								id="ifeprofeed-clear-cache-btn"
								data-feed-id="<?php echo esc_attr( $post->ID ); ?>"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'ifeprofeed_clear_cache_' . $post->ID ) ); ?>">
								<?php esc_html_e( 'Clear Cache Now', 'import-facebook-events' ); ?>
							</button>
							<span id="ifeprofeed-clear-cache-msg" style="margin-left:10px;display:none;"></span>
							<?php
							$cache_key   = 'ifeprofeed_' . $post->ID;
							$cached      = get_transient( $cache_key );
							$timeout_key = '_transient_timeout_' . $cache_key;
							$expires_at  = get_option( $timeout_key );
							if ( false !== $cached && $expires_at ) {
								$remaining = $expires_at - time();
								echo '<p class="description" style="margin-top:8px;">';
								if ( $remaining > 0 ) {
									/* translators: %d: minutes */
									echo '<span style="color:green;">&#9679; ' . esc_html( sprintf( __( 'Cache active — expires in %d minutes.', 'import-facebook-events' ), ceil( $remaining / 60 ) ) ) . '</span>';
								} else {
									echo '<span style="color:orange;">&#9679; ' . esc_html__( 'Cache expired — will refresh on next page load.', 'import-facebook-events' ) . '</span>';
								}
								echo '</p>';
							} else {
								echo '<p class="description" style="margin-top:8px;"><span style="color:#aaa;">&#9679; ' . esc_html__( 'No cache — will fetch on first page load.', 'import-facebook-events' ) . '</span></p>';
							}
							?>
							<?php else : ?>
							<p class="description"><?php esc_html_e( 'Save the feed first to manage cache.', 'import-facebook-events' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Hard Cache (HQ Images)', 'import-facebook-events' ); ?></th>
						<td>
							<?php if ( $post->ID && 'auto-draft' !== $post->post_status ) : ?>
							<button type="button" class="button button-secondary"
								id="ifeprofeed-clear-hard-cache-btn"
								data-feed-id="<?php echo esc_attr( $post->ID ); ?>"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'ifeprofeed_clear_hard_cache' ) ); ?>"
								style="color:#d63638;border-color:#d63638;">
								<?php esc_html_e( '🗑 Clear Hard Cache (Images)', 'import-facebook-events' ); ?>
							</button>
							<span id="ifeprofeed-clear-hard-cache-msg" style="margin-left:10px;display:none;"></span>
							<?php
							$image_count = IFEPRO_Feed_DB::instance()->get_image_count();
							echo '<p class="description" style="margin-top:8px;">';
							if ( $image_count > 0 ) {
								echo '<span style="color:#2271b1;">&#9679; ' . esc_html( sprintf(
									/* translators: %d: image count */
									__( '%d HQ images cached. Auto-cleans weekly.', 'import-facebook-events' ),
									$image_count
								) ) . '</span>';
							} else {
								echo '<span style="color:#aaa;">&#9679; ' . esc_html__( 'No HQ images cached yet.', 'import-facebook-events' ) . '</span>';
							}
							echo '</p>';
							?>
							<p class="description" style="margin-top:4px;font-style:italic;color:#666;">
								<?php esc_html_e( 'Clears all HQ event images. They will be re-fetched automatically on next page load.', 'import-facebook-events' ); ?>
							</p>
							<?php else : ?>
							<p class="description"><?php esc_html_e( 'Save the feed first.', 'import-facebook-events' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><label for="ifeprofeed_custom_css"><?php esc_html_e( 'Custom CSS', 'import-facebook-events' ); ?></label></th>
						<td>
							<textarea id="ifeprofeed_custom_css" name="_ifeprofeed_custom_css"
								rows="8" class="large-text code"
								placeholder="/* Add custom CSS for this feed only */"><?php echo esc_textarea( $meta['custom_css'] ); ?></textarea>
							<p class="description">
								<?php 
								/* translators: %d: Post ID */
								echo esc_html( sprintf( __( 'Scoped to #ifeprofeed-feed-%d. Will not affect other feeds.', 'import-facebook-events' ), $post->ID ) ); 
								?>
							</p>
						</td>
					</tr>
				</table>
			</div>

		</div><!-- .ifeprofeed-tabs -->
		</div><!-- .ifeprofeed-builder-left -->

		<div class="ifeprofeed-builder-right">
			<div class="ifeprofeed-preview-panel">
				<div class="ifeprofeed-preview-header">
					<h3><?php esc_html_e( 'Live Preview', 'import-facebook-events' ); ?></h3>
					<span class="ifeprofeed-preview-loading" style="display:none;"><?php esc_html_e( 'Updating...', 'import-facebook-events' ); ?></span>
				</div>
				<div class="ifeprofeed-preview-body">
					<div id="ifeprofeed-preview-container">
						<div class="ifeprofeed-preview-placeholder">
							<p><?php esc_html_e( 'Select a source and display options to see a live preview.', 'import-facebook-events' ); ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>

		</div><!-- .ifeprofeed-builder-layout -->
		<?php
	}

	/**
	 * Save feed meta box data.
	 */
	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['ifeprofeed_nonce'] ) ) return;
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ifeprofeed_nonce'] ) ), 'ifeprofeed_save_meta' ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$source_fields = array(
			'_ifeprofeed_source_type',
			'_ifeprofeed_page_id',
			'_ifeprofeed_group_id',
			'_ifeprofeed_event_ids',
			'_ifeprofeed_ical_url',
		);

		$old_source = array();
		foreach ( $source_fields as $sf ) {
			$old_source[ $sf ] = get_post_meta( $post_id, $sf, true );
		}

		$text_fields = array(
			'_ifeprofeed_source_type', '_ifeprofeed_page_id', '_ifeprofeed_group_id', '_ifeprofeed_event_ids', '_ifeprofeed_ical_url',
			'_ifeprofeed_time_filter', '_ifeprofeed_start_date', '_ifeprofeed_end_date',
			'_ifeprofeed_layout', '_ifeprofeed_pagination_type',
			'_ifeprofeed_register_label',
		);

		$int_fields = array( '_ifeprofeed_per_page', '_ifeprofeed_columns' );

		$checkbox_fields = array(
			'_ifeprofeed_show_image', '_ifeprofeed_show_date', '_ifeprofeed_show_venue',
			'_ifeprofeed_show_organizer', '_ifeprofeed_show_price',
			'_ifeprofeed_show_ticket_btn', '_ifeprofeed_hide_online', '_ifeprofeed_auto_refresh',
		);

		$textarea_fields = array( '_ifeprofeed_page_id', '_ifeprofeed_group_id', '_ifeprofeed_event_ids', '_ifeprofeed_ical_url' );

		foreach ( $text_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				if ( in_array( $field, $textarea_fields, true ) ) {
					$value = sanitize_textarea_field( wp_unslash( $_POST[ $field ] ) );
				} else {
					$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
				}
				if ( '_ifeprofeed_time_filter' === $field && 'past' === $value ) {
					$value = 'current_future';
				}
				if ( '_ifeprofeed_source_type' === $field ) {
					$allowed_sources = apply_filters( 'ifeprofeed_allowed_sources', array( 'event_ids', 'ical_url' ) );
					if ( ! in_array( $value, $allowed_sources, true ) ) {
						$value = 'event_ids';
					}
				}
				if ( '_ifeprofeed_layout' === $field ) {
					$allowed_layouts = apply_filters( 'ifeprofeed_allowed_layouts', array( 'card-grid', 'list' ) );
					if ( ! in_array( $value, $allowed_layouts, true ) ) {
						$value = 'card-grid';
					}
				}
				update_post_meta( $post_id, $field, $value );
			}
		}
		foreach ( $int_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta( $post_id, $field, absint( $_POST[ $field ] ) );
			}
		}
		foreach ( $checkbox_fields as $field ) {
			update_post_meta( $post_id, $field, isset( $_POST[ $field ] ) ? '1' : '0' );
		}

		if ( isset( $_POST['_ifeprofeed_custom_css'] ) ) {
			update_post_meta( $post_id, '_ifeprofeed_custom_css', wp_strip_all_tags( wp_unslash( $_POST['_ifeprofeed_custom_css'] ) ) );
		}

		if ( isset( $_POST['_ifeprofeed_cache_duration'] ) ) {
			$cache_val = sanitize_text_field( wp_unslash( $_POST['_ifeprofeed_cache_duration'] ) );
			if ( 'custom' === $cache_val && isset( $_POST['_ifeprofeed_cache_duration_custom'] ) ) {
				$custom_hours = max( 1, absint( $_POST['_ifeprofeed_cache_duration_custom'] ) );
				$cache_val    = $custom_hours * 60;
			} else {
				$cache_val    = absint( $cache_val ) ?: 1440;
			}
			update_post_meta( $post_id, '_ifeprofeed_cache_duration', $cache_val );
		}

		$source_changed = false;
		foreach ( $source_fields as $sf ) {
			$new_val = get_post_meta( $post_id, $sf, true );
			if ( trim( (string) $old_source[ $sf ] ) !== trim( (string) $new_val ) ) {
				$source_changed = true;
				break;
			}
		}

		set_transient( 'ifeprofeed_source_changed_' . $post_id, $source_changed ? '1' : '0', 30 );

		if ( $source_changed ) {
			IFEPRO_Feed_API::instance()->clear_cache( $post_id );
		}

		do_action( 'ifeprofeed_settings_saved', $post_id );
	}

	public function handle_clear_cache_row_action() {
		if ( ! current_user_can( 'manage_options' ) ) wp_die( esc_html__( 'Permission denied.', 'import-facebook-events' ) );
		$feed_id = absint( $_GET['feed_id'] ?? 0 );
		$nonce   = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ?? '' ) );
		if ( ! wp_verify_nonce( $nonce, 'ifeprofeed_clear_cache_' . $feed_id ) ) wp_die( esc_html__( 'Security check failed.', 'import-facebook-events' ) );
		IFEPRO_Feed_API::instance()->clear_cache( $feed_id );
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . IFEPRO_FEED_CPT . '&ifeprofeed_cache_cleared=1' ) );
		exit;
	}

	/**
	 * Helper rendering methods.
	 */

	private function render_select( $name, $current, $options ) {
		echo '<select name="' . esc_attr( $name ) . '">';
		foreach ( $options as $val => $label ) {
			echo '<option value="' . esc_attr( $val ) . '" ' . selected( $current, $val, false ) . '>';
			echo esc_html( $label );
			echo '</option>';
		}
		echo '</select>';
	}

	private function render_radio_row( $name, $current, $values, $suffix = '' ) {
		foreach ( $values as $val ) {
			echo '<label style="margin-right:12px;">';
			echo '<input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $val ) . '" ' . checked( (string) $current, (string) $val, false ) . ' /> ';
			echo esc_html( $val );
			if ( $suffix ) echo ' ' . esc_html( $suffix );
			echo '</label>';
		}
	}
}
