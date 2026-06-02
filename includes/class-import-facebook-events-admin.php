<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_Admin {


	/**
	 * $adminpage_url
	 *
	 * @var string
	 */
	public $adminpage_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->adminpage_url = admin_url( 'admin.php?page=facebook_import' );

		add_action( 'init', array( $this, 'register_scheduled_import_cpt' ) );
		add_action( 'init', array( $this, 'register_history_cpt' ) );
		add_action( 'admin_notices', array( $this,'remove_default_notices' ), 1 );
		add_action( 'ife_display_all_notice', array( $this, 'display_notices' ) );
		add_action( 'admin_init', array( $this, 'ife_check_delete_pst_event_cron_status' ) );
		add_action( 'ife_delete_past_events_cron', array( $this, 'ife_delete_past_events' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ) );
		add_filter( 'submenu_file', array( $this, 'get_selected_tab_submenu_ife' ) );
		add_filter( 'parent_file', array( $this, 'get_selected_tab_parent_ife' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_action_ife_view_import_history', array( $this, 'ife_view_import_history_handler' ) );
		add_action( 'admin_init', array( $this, 'ife_wp_cron_check' ) );
		add_action( 'admin_menu', array( $this, 'ife_widget_free_page' ) ); 
	}

	function ife_widget_free_page() {
		if ( ! post_type_exists( 'ifepro_live_feed' ) && ! defined( 'IFEPRO_VERSION' ) ) {
			add_submenu_page(
				null,
				__( 'Facebook Widget', 'import-facebook-events' ),
				__( 'Facebook Widget', 'import-facebook-events' ),
				'manage_options',
				'ife_facebook_feed_upgrade',
				array( $this, 'ife_render_feed_upgrade_page' )
			);
		}
	}

	function ife_render_feed_upgrade_page() {
		$pro_url = 'https://xylusthemes.com/plugins/import-facebook-events';
		?>
		<style>
			.ife-upgrade-wrap { max-width: 900px; margin: 40px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
			.ife-upgrade-hero { background: linear-gradient(135deg, #f06342 0%, #e84f2a 50%, #3d64f4 100%); border-radius: 12px; padding: 48px 40px; color: #fff; text-align: center; position: relative; overflow: hidden; margin-bottom: 32px; }
			.ife-upgrade-hero::before { content:''; position:absolute; top:-60px; right:-60px; width:220px; height:220px; background:rgba(255,255,255,0.07); border-radius:50%; }
			.ife-upgrade-hero::after { content:''; position:absolute; bottom:-40px; left:-40px; width:160px; height:160px; background:rgba(255,255,255,0.05); border-radius:50%; }
			.ife-upgrade-hero h1 { font-size: 32px; font-weight: 800; margin: 0 0 12px; position:relative; z-index:1; }
			.ife-upgrade-hero p { font-size: 16px; opacity: 0.92; margin: 0 0 28px; position:relative; z-index:1; max-width: 560px; margin-left:auto; margin-right:auto; margin-bottom:28px;}
			.ife-upgrade-hero-btn { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: #f06342; font-size: 15px; font-weight: 700; padding: 14px 32px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 16px rgba(0,0,0,0.2); position:relative; z-index:1; transition: transform 0.2s; }
			.ife-upgrade-hero-btn:hover { transform: translateY(-2px); color: #e8411a; }
			.ife-pro-badge-large { display:inline-block; background:#4CAF50; color:#fff; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; letter-spacing:1px; text-transform:uppercase; margin-bottom:16px; }

			.ife-features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
			.ife-feature-card { background: #fff; border: 1px solid #e8e8e8; border-radius: 10px; padding: 24px 20px; text-align: center; transition: box-shadow 0.2s; }
			.ife-feature-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
			.ife-feature-icon { font-size: 32px; margin-bottom: 12px; display:block; }
			.ife-feature-card h3 { font-size: 14px; font-weight: 700; color: #1d2327; margin: 0 0 8px; }
			.ife-feature-card p { font-size: 12px; color: #666; margin: 0; line-height: 1.6; }

			.ife-compare-table { background:#fff; border:1px solid #e8e8e8; border-radius:10px; overflow:hidden; margin-bottom:32px; }
			.ife-compare-table table { width:100%; border-collapse:collapse; }
			.ife-compare-table th { padding:14px 20px; font-size:13px; font-weight:700; text-align:center; }
			.ife-compare-table th:first-child { text-align:left; background:#f8f9fa; }
			.ife-compare-table th.free-col { background:#f8f9fa; color:#888; }
			.ife-compare-table th.pro-col { background: linear-gradient(135deg, #f06342, #3d64f4); color:#fff; }
			.ife-compare-table td { padding:11px 20px; font-size:13px; border-top:1px solid #f0f0f0; text-align:center; }
			.ife-compare-table td:first-child { text-align:left; color:#444; font-weight:500; }
			.ife-compare-table tr:hover td { background:#fafafa; }
			.ife-check { color:#4CAF50; font-size:16px; font-weight:700; }
			.ife-cross { color:#ccc; font-size:16px; }

			.ife-bottom-cta { background:#f8f9fa; border:1px solid #e8e8e8; border-radius:10px; padding:32px; text-align:center; }
			.ife-bottom-cta h3 { font-size:20px; font-weight:700; color:#1d2327; margin:0 0 8px; }
			.ife-bottom-cta p { font-size:13px; color:#666; margin:0 0 20px; }

			@media (max-width: 782px) { .ife-features-grid { grid-template-columns: 1fr 1fr; } }
		</style>

		<div class="ife-upgrade-wrap">

			<div class="ife-upgrade-hero">
				<span class="ife-pro-badge-large"><?php esc_html_e( 'PRO Feature', 'import-facebook-events' ); ?></span>
				<h1 style="color:#fff;"><?php esc_html_e( 'Facebook Widget', 'import-facebook-events' ); ?></h1>
				<p style="color:#ddd;"><?php esc_html_e( 'Display Facebook events directly on your website. No manual importing, no authorization, no API token needed. Just paste a shortcode and go live!', 'import-facebook-events' ); ?></p>
				<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" class="ife-upgrade-hero-btn">
					✦ <?php esc_html_e( 'Upgrade to PRO', 'import-facebook-events' ); ?>
				</a>
			</div>

			<div class="ife-features-grid">
				<div class="ife-feature-card">
					<span class="ife-feature-icon">🚀</span>
					<h3><?php esc_html_e( 'Instant Live Feed', 'import-facebook-events' ); ?></h3>
					<p><?php esc_html_e( 'Show live events directly from any Facebook Page or specific Event IDs without manual synchronization.', 'import-facebook-events' ); ?></p>
				</div>
				<div class="ife-feature-card">
					<span class="ife-feature-icon">📸</span>
					<h3><?php esc_html_e( 'High-Quality Images', 'import-facebook-events' ); ?></h3>
					<p><?php esc_html_e( 'Automatically fetches and caches HD images from Facebook using background Action Scheduler.', 'import-facebook-events' ); ?></p>
				</div>
				<div class="ife-feature-card">
					<span class="ife-feature-icon">🔄</span>
					<h3><?php esc_html_e( 'AJAX Pagination', 'import-facebook-events' ); ?></h3>
					<p><?php esc_html_e( 'Smooth, instant page loading with Numbered, Load More, or Infinite Scroll pagination options.', 'import-facebook-events' ); ?></p>
				</div>
				<div class="ife-feature-card">
					<span class="ife-feature-icon">🎨</span>
					<h3><?php esc_html_e( '6 Layout Styles', 'import-facebook-events' ); ?></h3>
					<p><?php esc_html_e( 'Card Grid, List, Compact List, Timeline, Minimal Grid, and Masonry layouts.', 'import-facebook-events' ); ?></p>
				</div>
				<div class="ife-feature-card">
					<span class="ife-feature-icon">⚡</span>
					<h3><?php esc_html_e( 'Background Processing', 'import-facebook-events' ); ?></h3>
					<p><?php esc_html_e( 'Smart DB caching and delayed background fetching ensures your front-end stays lightning fast.', 'import-facebook-events' ); ?></p>
				</div>
				<div class="ife-feature-card">
					<span class="ife-feature-icon">🛠️</span>
					<h3><?php esc_html_e( 'Shortcode Builder', 'import-facebook-events' ); ?></h3>
					<p><?php esc_html_e( 'Live preview visual builder generates your feed shortcode instantly with customizable columns.', 'import-facebook-events' ); ?></p>
				</div>
			</div>

			<div class="ife-compare-table">
				<table>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Feature', 'import-facebook-events' ); ?></th>
							<th class="free-col"><?php esc_html_e( 'Free', 'import-facebook-events' ); ?></th>
							<th class="pro-col"><?php esc_html_e( 'PRO', 'import-facebook-events' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php esc_html_e( 'Display events via Live Widget (no manual import)', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Feed by Facebook Page ID or Username', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Feed by specific Facebook Event IDs', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'High-Quality Background Image Scraping', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Interactive Pagination (AJAX, Load More, Scroll)', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( '6 Display Layouts (Grid, List, Timeline & more)', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Filter by Date & Time (Past, Upcoming, Custom)', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Hide Online-Only / Virtual Events', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Smart Cache + Action Scheduler Auto Refresh', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Live Preview Shortcode Builder', 'import-facebook-events' ); ?></td>
							<td><span class="ife-cross">✕</span></td>
							<td><span class="ife-check">✔</span></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="ife-bottom-cta">
				<h3><?php esc_html_e( 'Ready to go live with Facebook Widget?', 'import-facebook-events' ); ?></h3>
				<p><?php esc_html_e( 'Upgrade to PRO and start displaying events on your website in minutes — no technical setup needed.', 'import-facebook-events' ); ?></p>
				<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" 
				style="display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#f06342,#3d64f4); color:#fff; font-size:14px; font-weight:700; padding:13px 30px; border-radius:8px; text-decoration:none; box-shadow:0 4px 16px rgba(240,99,66,0.35);">
					✦ <?php esc_html_e( 'Get PRO Now', 'import-facebook-events' ); ?>
				</a>
			</div>

		</div>
		<?php
	}

	/**
	 * Create the Admin menu and submenu and assign their links to global varibles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_menu_pages() {
		global $submenu;

		add_menu_page( __( 'Import Facebook Events', 'import-facebook-events' ), __( 'Facebook Import', 'import-facebook-events' ), 'manage_options', 'facebook_import', array( $this, 'admin_page' ), 'dashicons-calendar-alt', '30' );
		$submenu['facebook_import'][] = array( __( 'Dashboard', 'import-facebook-events' ), 'manage_options',  admin_url( 'admin.php?page=facebook_import&tab=dashboard' )  );
		if ( post_type_exists( 'ifepro_live_feed' ) || defined( 'IFEPRO_VERSION' ) ) {
			$submenu['facebook_import'][] = array(
				'<span style="display:flex; justify-content:space-between; align-items:center; width:100%;">' 
					. __( 'Facebook Widget', 'import-facebook-events' ) 
					. '<span style="background:#4CAF50; margin-left:6px; flex-shrink:0;height: 22px;border-radius: 3px;color: #FFF;font-size: 12px;line-height: 18px;font-weight: 600;display: inline-flex;padding: 0 4px;align-items: center;">NEW</span>'
				. '</span>',
				'manage_options',
				'edit.php?post_type=ifepro_live_feed'
			);
		} else {
			$submenu['facebook_import'][] = array(
				'<span style="display:flex; justify-content:space-between; align-items:center; width:100%;">' 
					. __( 'Facebook Widget', 'import-facebook-events' ) 
					. '<span style="background:#4CAF50; margin-left:6px; flex-shrink:0;height:22px;border-radius:3px;color:#FFF;font-size:12px;line-height:18px;font-weight:600;display:inline-flex;padding:0 4px;align-items:center;">NEW</span>'
				. '</span>',
				'manage_options',
				'admin.php?page=ife_facebook_feed_upgrade'
			);
		}
		$submenu['facebook_import'][] = array( __( 'Facebook Import', 'import-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=facebook_import&tab=facebook' ) );
		$submenu['facebook_import'][] = array( __( 'Facebook .ics Import', 'import-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=facebook_import&tab=ics' ) );
		$submenu['facebook_import'][] = array( __( 'Schedule Import', 'import-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=facebook_import&tab=scheduled' ) );
		$submenu['facebook_import'][] = array( __( 'Import History', 'import-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=facebook_import&tab=history' ) );
		$submenu['facebook_import'][] = array( __( 'Settings', 'import-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=facebook_import&tab=settings' ) );
		$submenu['facebook_import'][] = array( __( 'Shortcodes', 'import-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=facebook_import&tab=shortcodes' ));
		$submenu['facebook_import'][] = array( __( 'Wizard', 'import-facebook-events' ), 'manage_options', admin_url( 'admin.php?page=facebook_import&tab=ife_setup_wizard' ) );
		if( !ife_is_pro() ){
			$submenu['facebook_import'][] = array( '<li class="ife_upgrade_pro current">' . __( 'Upgrade to Pro', 'import-facebook-events' ) . '</li>', 'manage_options', esc_url( "https://xylusthemes.com/plugins/import-facebook-events/") );
		}
	}

	public function remove_default_notices() {
		// Remove default notices display.
		remove_action( 'admin_notices', 'wp_admin_notices' );
		remove_action( 'all_admin_notices', 'wp_admin_notices' );
	}

	/**
	 * Load Admin Scripts
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @param string $hook Page hook.
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ) {
		$js_dir = IFE_PLUGIN_URL . 'assets/js/';
		wp_register_script( 'import-facebook-events', $js_dir . 'import-facebook-events-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'wp-color-picker' ), IFE_VERSION, true );
		$params = array(
			'ajax_nonce' => wp_create_nonce( 'ife_admin_js_nonce' ),
		);
		wp_localize_script( 'import-facebook-events', 'ife_ajax', $params );
		wp_enqueue_script( 'import-facebook-events' );

		if( isset( $_GET['tab'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) == 'ife_setup_wizard' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_register_script( 'ife-wizard-js', $js_dir . 'wizard.js',  array( 'jquery', 'jquery-ui-core' ), IFE_VERSION, true  );
			wp_enqueue_script( 'ife-wizard-js' );
		}
	}
	
	/**
	 * Check if WP-Cron is enabled
	 *
	 * Checks if WP-Cron is enabled and if the current page is the scheduled imports page.
	 * If WP-Cron is disabled, it will display an error message.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function ife_wp_cron_check() {
		global $ife_errors;
		
		if ( ! is_admin() || empty($_GET['page']) || empty($_GET['tab']) || $_GET['page'] !== 'facebook_import' || $_GET['tab']  !== 'scheduled' ) {
			return;
		}

		if ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ) {
			$ife_errors[] = __(
				'<strong>Scheduled imports are paused.</strong> WP-Cron is currently disabled on your site, so Facebook scheduled imports will not run automatically. Please enable WP-Cron or set up a server cron job to keep imports running smoothly.',
				'import-facebook-events'
			);

		}
	}


	/**
	 * Load Admin Styles.
	 *
	 * Enqueues the required admin styles.
	 *
	 * @since 1.0
	 * @param string $hook Page hook.
	 * @return void
	 */
	public function enqueue_admin_styles( $hook ) {
		global $pagenow;
		$current_screen = get_current_screen();
		$css_dir = IFE_PLUGIN_URL . 'assets/css/';
		wp_enqueue_style( 'jquery-ui', $css_dir . 'jquery-ui.css', false, '1.12.0' );
		wp_enqueue_style( 'import-facebook-events-global', $css_dir . 'import-facebook-events-admin-global.css', false, IFE_VERSION );
		
		if( isset( $_GET['page'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) == 'facebook_import' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'toplevel_page_facebook_import' === $current_screen->id || 'widgets.php' === $pagenow || 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
				wp_enqueue_style( 'import-facebook-events', $css_dir . 'import-facebook-events-admin.css', false, IFE_VERSION );
				wp_enqueue_style( 'wp-color-picker' );

				if( isset( $_GET['tab'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) == 'ife_setup_wizard' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					wp_enqueue_style( 'ife-wizard-css', $css_dir . 'wizard.css', false, IFE_VERSION  );
				}
			}
		}
	}

	/**
	 * Load Admin page.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function admin_page() {
		global $ife_events;

			$active_tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) )  : 'facebook'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$gettab     = str_replace( 'by_', '', $active_tab );
			$gettab     = ucwords( str_replace( '_', ' & ', $gettab ) );
			if( $active_tab == 'support' ){
				$page_title = 'Support & Help';
			}elseif( $active_tab == 'facebook' ){
				$page_title = 'Facebook Import';
			}elseif( $active_tab == 'ics' ){
				$page_title = 'ICS Import';
			}elseif( $active_tab == 'scheduled' ){
				$page_title = 'Scheduled Import';
			}else{
				$page_title = $gettab;
			}

			if( $active_tab == 'ife_setup_wizard' ){
				require_once IFE_PLUGIN_DIR . '/templates/admin/ife-setup-wizard.php';
				exit();
			}

			$posts_header_result = $ife_events->common->ife_render_common_header( $page_title );

			if( $active_tab != 'dashboard' ){
				?>
					<div class="ife-container" style="margin-top: 60px;">
						<div class="ife-wrap" >
							<div id="poststuff">
								<div id="post-body" class="metabox-holder columns-2">
									<?php 
										do_action( 'ife_display_all_notice' );
									?>
									<div class="delete_notice"></div>
									<div id="postbox-container-2" class="postbox-container">
										<div class="ife-app">
											<div class="ife-tabs">
												<div class="tabs-scroller">
													<div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding">
														<div class="var-tabs__tab-wrap var-tabs--layout-horizontal">
															<a href="?page=facebook_import&tab=facebook" class="var-tab <?php echo $active_tab == 'facebook' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
																<span class="tab-label"><?php esc_attr_e( 'Import', 'import-facebook-events' ); ?></span>
															</a>
															<a href="?page=facebook_import&tab=ics" class="var-tab <?php echo $active_tab == 'ics' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
																<span class="tab-label"><?php esc_attr_e( 'ICS Import', 'import-facebook-events' ); ?></span>
															</a>
															<a href="?page=facebook_import&tab=scheduled" class="var-tab <?php echo ( $active_tab == 'scheduled' || $active_tab == 'scheduled' )  ? 'var-tab--active' : 'var-tab--inactive'; ?>">
																<span class="tab-label"><?php esc_attr_e( 'Schedule Import', 'import-facebook-events' ); if( !ife_is_pro() ){ echo '<div class="ife-pro-badge"> PRO </div>'; } ?></span>
															</a>
															<a href="?page=facebook_import&tab=history" class="var-tab <?php echo $active_tab == 'history' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
																<span class="tab-label"><?php esc_attr_e( 'History', 'import-facebook-events' ); ?></span>
															</a>
															<a href="?page=facebook_import&tab=settings" class="var-tab <?php echo $active_tab == 'settings' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
																<span class="tab-label"><?php esc_attr_e( 'Setting', 'import-facebook-events' ); ?></span>
															</a>
															<a href="?page=facebook_import&tab=shortcodes" class="var-tab <?php echo $active_tab == 'shortcodes' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
																<span class="tab-label"><?php esc_attr_e( 'Shortcodes', 'import-facebook-events' ); ?></span>
															</a>
															<a href="?page=facebook_import&tab=support" class="var-tab <?php echo $active_tab == 'support' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
																<span class="tab-label"><?php esc_attr_e( 'Support & Help', 'import-facebook-events' ); ?></span>
															</a>
														</div>
													</div>
												</div>
											</div>
										</div>

										<?php
											if ( 'facebook' === $active_tab ) {
												?>
												<form method="post" id="ife_facebook_form">
													<?php
														require_once IFE_PLUGIN_DIR . '/templates/admin/facebook-import-events.php';
													?>
													<div class="ife_element">
														<input type="hidden" name="import_origin" value="facebook" />
														<input type="hidden" name="ife_action" value="ife_import_submit" />
														<?php wp_nonce_field( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ); ?>
														<input type="submit" class="ife_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-facebook-events' ); ?>" />
													</div>
												</form>
												<?php
											} elseif ( 'ics' === $active_tab ) {
												?>
													<form method="post" enctype="multipart/form-data" id="ife_ics_form">
														<?php
															require_once IFE_PLUGIN_DIR . '/templates/admin/ical-import-events.php';
														?>
														<div class="ife_element">
															<input type="hidden" name="import_origin" value="ical" />
															<input type="hidden" name="ife_action" value="ife_import_submit" />
															<?php wp_nonce_field( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ); ?>
															<input type="submit" class="ife_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-facebook-events' ); ?>" />
														</div>
													</form>
												<?php
											} elseif ( 'settings' === $active_tab ) {
												require_once IFE_PLUGIN_DIR . '/templates/admin/import-facebook-events-settings.php';
											} elseif ( 'scheduled' === $active_tab ) {
												if ( ife_is_pro() ) {
													require_once IFEPRO_PLUGIN_DIR . '/templates/admin/scheduled-import-events.php';
												} else {
													?>
														<div class="ife-blur-filter" >
															<?php do_action( 'ife_render_pro_notice' ); ?>
														</div>
													<?php
												}
											} elseif ( 'history' === $active_tab ) {
												require_once IFE_PLUGIN_DIR . '/templates/admin/import-facebook-events-history.php';
											} elseif ( 'support' === $active_tab ) {
												require_once IFE_PLUGIN_DIR . '/templates/admin/import-facebook-events-support.php';
											}elseif ( 'shortcodes' === $active_tab ) {
												require_once IFE_PLUGIN_DIR . '/templates/admin/import-facebook-events-shortcode.php';
											}
										?>
									</div>
								</div>
								<br class="clear">
							</div>
						</div>
					</div>
				<?php
			}else{
				require_once IFE_PLUGIN_DIR . '/templates/admin/ife-dashboard.php';
			}
			$posts_footer_result = $ife_events->common->ife_render_common_footer();
	}


	/**
	 * Display notices in admin.
	 *
	 * @since    1.0.0
	 */
	public function display_notices() {
		global $ife_errors, $ife_success_msg, $ife_warnings, $ife_info_msg;

		if ( ! empty( $ife_errors ) ) {
			foreach ( $ife_errors as $error ) :
				?>
				<div class="notice notice-error ife_notice is-dismissible">
					<p><?php echo wp_kses_post( $error ); ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ife_success_msg ) ) {
			foreach ( $ife_success_msg as $success ) :
				?>
				<div class="notice notice-success ife_notice is-dismissible">
					<p><?php echo wp_kses_post( $success ); ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ife_warnings ) ) {
			foreach ( $ife_warnings as $warning ) :
				?>
				<div class="notice notice-warning ife_notice is-dismissible">
					<p><?php echo wp_kses_post( $warning ); ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ife_info_msg ) ) {
			foreach ( $ife_info_msg as $info ) :
				?>
				<div class="notice notice-info ife_notice is-dismissible">
					<p><?php echo wp_kses_post( $info ); ?></p>
				</div>
				<?php
			endforeach;
		}

	}

	/**
	 * Register custom post type for scheduled imports.
	 *
	 * @since    1.0.0
	 */
	public function register_scheduled_import_cpt() {
		$labels = array(
			'name'               => _x( 'Scheduled Import', 'post type general name', 'import-facebook-events' ),
			'singular_name'      => _x( 'Scheduled Import', 'post type singular name', 'import-facebook-events' ),
			'menu_name'          => _x( 'Scheduled Imports', 'admin menu', 'import-facebook-events' ),
			'name_admin_bar'     => _x( 'Scheduled Import', 'add new on admin bar', 'import-facebook-events' ),
			'add_new'            => _x( 'Add New', 'book', 'import-facebook-events' ),
			'add_new_item'       => __( 'Add New Import', 'import-facebook-events' ),
			'new_item'           => __( 'New Import', 'import-facebook-events' ),
			'edit_item'          => __( 'Edit Import', 'import-facebook-events' ),
			'view_item'          => __( 'View Import', 'import-facebook-events' ),
			'all_items'          => __( 'All Scheduled Imports', 'import-facebook-events' ),
			'search_items'       => __( 'Search Scheduled Imports', 'import-facebook-events' ),
			'parent_item_colon'  => __( 'Parent Imports:', 'import-facebook-events' ),
			'not_found'          => __( 'No Imports found.', 'import-facebook-events' ),
			'not_found_in_trash' => __( 'No Imports found in Trash.', 'import-facebook-events' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Scheduled Imports.', 'import-facebook-events' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => false,
			'can_export'         => false,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'menu_position'      => 5,
		);

		register_post_type( 'fb_scheduled_imports', $args );
	}

	/**
	 * Register custom post type for Save import history.
	 *
	 * @since    1.0.0
	 */
	public function register_history_cpt() {
		$labels = array(
			'name'               => _x( 'Import History', 'post type general name', 'import-facebook-events' ),
			'singular_name'      => _x( 'Import History', 'post type singular name', 'import-facebook-events' ),
			'menu_name'          => _x( 'Import History', 'admin menu', 'import-facebook-events' ),
			'name_admin_bar'     => _x( 'Import History', 'add new on admin bar', 'import-facebook-events' ),
			'add_new'            => _x( 'Add New', 'book', 'import-facebook-events' ),
			'add_new_item'       => __( 'Add New', 'import-facebook-events' ),
			'new_item'           => __( 'New History', 'import-facebook-events' ),
			'edit_item'          => __( 'Edit History', 'import-facebook-events' ),
			'view_item'          => __( 'View History', 'import-facebook-events' ),
			'all_items'          => __( 'All Import History', 'import-facebook-events' ),
			'search_items'       => __( 'Search History', 'import-facebook-events' ),
			'parent_item_colon'  => __( 'Parent History:', 'import-facebook-events' ),
			'not_found'          => __( 'No History found.', 'import-facebook-events' ),
			'not_found_in_trash' => __( 'No History found in Trash.', 'import-facebook-events' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Import History', 'import-facebook-events' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => false,
			'can_export'         => false,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'menu_position'      => 5,
		);

		register_post_type( 'ife_import_history', $args );
	}

	/**
	 * Get Plugin array
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_xyuls_themes_plugins() {
		return array(
			'wp-event-aggregator' => array( 'plugin_name' => esc_html__( 'WP Event Aggregator', 'import-facebook-events' ), 'description' => 'WP Event Aggregator: Easy way to import Facebook Events, Eventbrite events, MeetUp events into your WordPress Event Calendar.' ),
			'import-eventbrite-events' => array( 'plugin_name' => esc_html__( 'Import Eventbrite Events', 'import-facebook-events' ), 'description' => 'Import Eventbrite Events into WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
			'import-meetup-events' => array( 'plugin_name' => esc_html__( 'Import Meetup Events', 'import-facebook-events' ), 'description' => 'Import Meetup Events allows you to import Meetup (meetup.com) events into your WordPress site effortlessly.' ),
			'wp-bulk-delete' => array( 'plugin_name' => esc_html__( 'WP Bulk Delete', 'import-facebook-events' ), 'description' => 'Bulk delete and cleanup anything like posts, comments, users, meta fields, taxonomy terms. with powerful filter options.' ),
			'event-schema' => array( 'plugin_name' => esc_html__( 'Event Schema / Structured Data', 'import-facebook-events' ), 'description' => 'Automatically Google Event Rich Snippet Schema Generator. This plug-in generates complete JSON-LD based schema (structured data for Rich Snippet) for events.' ),
			'wp-smart-import' => array( 'plugin_name' => esc_html__( 'WP Smart Import : Import any XML File to WordPress', 'import-facebook-events' ), 'description' => 'The most powerful solution for importing any CSV files to WordPress. Create Posts and Pages any Custom Posttype with content from any CSV file.' ),
		);
	}

	/**
	 * Get Plugin Details.
	 *
	 * @since 1.1.0
	 * @param string $slug Slug of plugin on wp.org.
	 * @return array
	 */
	public function get_wporg_plugin( $slug ) {

		if ( empty( $slug ) ) {
			return false;
		}

		$transient_name = 'support_plugin_box' . $slug;
		$plugin_data    = get_transient( $transient_name );
		if ( false === $plugin_data ) {
			if ( ! function_exists( 'plugins_api' ) ) {
				include_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			}

			$plugin_data = plugins_api(
				'plugin_information',
				array(
					'slug'   => $slug,
					'is_ssl' => is_ssl(),
					'fields' => array(
						'banners'         => true,
						'active_installs' => true,
					),
				)
			);

			if ( ! is_wp_error( $plugin_data ) ) {
				set_transient( $transient_name, $plugin_data, 24 * HOUR_IN_SECONDS );
			} else {
				// If there was a bug on the Current Request just leave.
				return false;
			}
		}
		return $plugin_data;
	}

	/**
	 * Tab Submenu got selected.
	 *
	 * @since 1.7.1
	 * @return void
	 */
	public function get_selected_tab_submenu_ife( $submenu_file ){
		if( !empty( $_GET['page'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) == 'facebook_import' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$allowed_tabs = array( 'dashboard', 'facebook', 'ics', 'scheduled', 'history', 'settings', 'shortcodes', 'support' );
			$tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'facebook'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if( in_array( $tab, $allowed_tabs ) ){
				$submenu_file = admin_url( 'admin.php?page=facebook_import&tab='.$tab );
			}
		}
		global $post_type;
		if ( 'ifepro_live_feed' === $post_type ) {
			$submenu_file = 'edit.php?post_type=ifepro_live_feed';
		}
		return $submenu_file;
	}

	/**
	 * Set parent file for CPTs to keep menu open.
	 *
	 * @since 1.8.0
	 */
	public function get_selected_tab_parent_ife( $parent_file ){

		if ( ! empty( $_GET['page'] ) && 'ife_facebook_feed_upgrade' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return 'facebook_event';
		}

		global $post_type;
		if ( 'ifepro_live_feed' === $post_type ) {
			$parent_file = 'facebook_event';
		}
		return $parent_file;
	}

	/**
	 * Render imported Events in history Page.
	 *
	 * @return void
	 */
	public function ife_view_import_history_handler() {
		define( 'IFRAME_REQUEST', true );
		iframe_header();
		$history_id = isset( $_GET['history'] ) ? absint( $_GET['history'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $history_id > 0 ) {
			$imported_data = get_post_meta( $history_id, 'imported_data', true );
			if ( ! empty( $imported_data ) ) {
				?>
				<table class="widefat fixed striped">
				<thead>
					<tr>
						<th id="title" class="column-title column-primary"><?php esc_html_e( 'Event', 'import-facebook-events' ); ?></th>
						<th id="action" class="column-operation"><?php esc_html_e( 'Created/Updated', 'import-facebook-events' ); ?></th>
						<th id="action" class="column-date"><?php esc_html_e( 'Action', 'import-facebook-events' ); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php
					foreach ( $imported_data as $event ) {
						?>
						<tr>
							<td class="title column-title">
								<?php
								printf(
									'<a href="%1$s" target="_blank">%2$s</a>',
									esc_url( get_the_permalink( absint( $event['id'] ) ) ),
									esc_attr( get_the_title( absint( $event['id'] ) ) )
								);
								?>
							</td>
							<td class="title column-title">
								<?php echo esc_attr( ucfirst( $event['status'] ) ); ?>
							</td>
							<td class="title column-action">
								<?php
								printf(
									'<a href="%1$s" target="_blank">%2$s</a>',
									esc_url( get_edit_post_link( absint( $event['id'] ) ) ),
									esc_attr__( 'Edit', 'import-facebook-events' )
								);
								?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
								<?php
			} else {
				?>
				<div class="ife_no_import_events">
					<?php esc_html_e( 'No data found', 'import-facebook-events' ); ?>
				</div>
				<?php
			}
		} else {
			?>
			<div class="ife_no_import_events">
				<?php esc_html_e( 'No data found', 'import-facebook-events' ); ?>
			</div>
			<?php
		}
		?>
		<style>
			.ife_no_import_events{
				text-align: center;
				margin-top: 60px;
				font-size: 1.4em;
			}
		</style>
		<?php
		iframe_footer();
		exit;
	}

	/**
	 * Render Delete Past Event in the facebook_events post type
	 * @return void
	 */
	public function ife_delete_past_events() {

		$current_time = current_time('timestamp');
		$args         = array(
			'post_type'       => 'facebook_events',
			'posts_per_page'  => 100,
			'post_status'     => 'publish',
			'fields'          => 'ids',
			'meta_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				array(
					'key'     => 'end_ts',
					'value'   => current_time( 'timestamp' ) - ( 24 * 3600 ),
					'compare' => '<',      
					'type'    => 'NUMERIC',
				),
			),
		);
		$events = get_posts( $args );

		if ( empty( $events ) ) {
			return;
		}

		foreach ( $events as $event_id ) {
			wp_trash_post( $event_id );
		}
	}

	/**
	 * re-create if the past event cron is delete
	 */
	public function ife_check_delete_pst_event_cron_status(){

		$ife_options        = get_option( IFE_OPTIONS );
		$move_peit_ieevents = isset( $ife_options['move_peit'] ) ? $ife_options['move_peit'] : 'no';
		if ( $move_peit_ieevents == 'yes' ) {
			if ( !wp_next_scheduled( 'ife_delete_past_events_cron' ) ) {
				wp_schedule_event( time(), 'daily', 'ife_delete_past_events_cron' );
			}
		}else{
			if ( wp_next_scheduled( 'ife_delete_past_events_cron' ) ) {
				wp_clear_scheduled_hook( 'ife_delete_past_events_cron' );
			}
		}

	}
}
