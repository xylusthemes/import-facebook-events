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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_action_ife_view_import_history', array( $this, 'ife_view_import_history_handler' ) );
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

		if( isset( $_GET['tab'] ) && $_GET['tab'] == 'ife_setup_wizard' ){
			wp_register_script( 'ife-wizard-js', $js_dir . 'wizard.js',  array( 'jquery', 'jquery-ui-core' ), IFE_VERSION  );
			wp_enqueue_script( 'ife-wizard-js' );
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
		if( isset( $_GET['page'] ) && $_GET['page'] == 'facebook_import' ){
			if ( 'toplevel_page_facebook_import' === $current_screen->id || 'widgets.php' === $pagenow || 'post.php' === $pagenow || 'post-new.php' === $pagenow ) {
				$css_dir = IFE_PLUGIN_URL . 'assets/css/';
				wp_enqueue_style( 'jquery-ui', $css_dir . 'jquery-ui.css', false, '1.12.0' );
				wp_enqueue_style( 'import-facebook-events', $css_dir . 'import-facebook-events-admin.css', false, IFE_VERSION );
				wp_enqueue_style( 'wp-color-picker' );

				if( isset( $_GET['tab'] ) && $_GET['tab'] == 'ife_setup_wizard' ){
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

			$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) )  : 'facebook';
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
			echo esc_attr_e( $posts_header_result );

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
			echo esc_attr_e( $posts_footer_result );
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
				<div class="notice notice-error is-dismissible">
					<p><?php echo wp_kses_post( $error ); ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ife_success_msg ) ) {
			foreach ( $ife_success_msg as $success ) :
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php echo wp_kses_post( $success ); ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ife_warnings ) ) {
			foreach ( $ife_warnings as $warning ) :
				?>
				<div class="notice notice-warning is-dismissible">
					<p><?php echo wp_kses_post( $warning ); ?></p>
				</div>
				<?php
			endforeach;
		}

		if ( ! empty( $ife_info_msg ) ) {
			foreach ( $ife_info_msg as $info ) :
				?>
				<div class="notice notice-info is-dismissible">
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
		if( !empty( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) == 'facebook_import' ){
			$allowed_tabs = array( 'dashboard', 'facebook', 'ics', 'scheduled', 'history', 'settings', 'shortcodes', 'support' );
			$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'facebook';
			if( in_array( $tab, $allowed_tabs ) ){
				$submenu_file = admin_url( 'admin.php?page=facebook_import&tab='.$tab );
			}
		}
		return $submenu_file;
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
			'meta_query'      => array(
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
