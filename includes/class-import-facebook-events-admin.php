<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Do nothing
		add_action( 'init', array( $this, 'register_scheduled_import_cpt' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
		add_action( 'admin_notices', array( $this, 'display_notices') );
	}

	/**
	 * Create the Admin menu and submenu and assign their links to global varibles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_menu_pages(){

		add_menu_page(
			esc_html__( 'Facebook Event Import', 'import-facebook-events' ),
			esc_html__( 'Facebook Import', 'import-facebook-events' ),
			'manage_options',
			'import_fb_events',
			array( $this, 'admin_page' ),
			'dashicons-calendar-alt',
			26
		);

	}

	/**
	 * Load Admin Scripts
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_scripts( $hook ) {

		$js_dir  = IFE_PLUGIN_URL . 'assets/js/';
		wp_register_script( 'import-facebook-events', $js_dir . 'import-facebook-events-admin.js', array('jquery' ), IFE_VERSION );
		wp_enqueue_script( 'import-facebook-events' );
		
	}

	/**
	 * Load Admin Styles.
	 *
	 * Enqueues the required admin styles.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_styles( $hook ) {

	  	$css_dir = IFE_PLUGIN_URL . 'assets/css/';
	 	wp_enqueue_style('import-facebook-events', $css_dir . 'import-facebook-events-admin.css', false, "" );
	}

	/**
	 * Load Admin page.
	 *
	 * @since 1.0
	 * @return void
	 */
	function admin_page() {
		
		?>
		<div class="wrap">
		    <h2><?php esc_html_e( 'Facebook Events Import', 'import-facebook-events' ); ?></h2>
		    <?php
		    // Set Default Tab to Import.
		    $tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'fbtec_import';
		    ?>
		    <div id="poststuff">
		        <div id="post-body" class="metabox-holder columns-2">

		            <div id="postbox-container-1" class="postbox-container">
		            	<?php require_once IFE_PLUGIN_DIR . '/templates/admin-sidebar.php'; ?>
		            </div>
		            <div id="postbox-container-2" class="postbox-container">

		                <h1 class="nav-tab-wrapper">

		                	<?php if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) { ?>
		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'fbtec_import' ) ); ?>" class="nav-tab <?php if ( $tab == 'fbtec_import' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'The Events Calendar import', 'import-facebook-events' ); ?>
		                    </a>
		                    <?php } ?>

		                    <?php if ( is_plugin_active( 'events-manager/events-manager.php' ) ) { ?>
		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'fbem_import' ) ); ?>" class="nav-tab <?php if ( $tab == 'fbem_import' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Events Manager import', 'import-facebook-events' ); ?>
		                    </a>
		                    <?php } ?>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'scheduled', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'scheduled' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Scheduled Imports', 'import-facebook-events' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'settings' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Settings', 'import-facebook-events' ); ?>
		                    </a>

		                </h1>

		                <div class="import-facebook-events-page">

		                	<?php
		                	if ( $tab == 'fbtec_import' ) {

		                		require_once IFE_PLUGIN_DIR . '/templates/import-tec-facebook-events.php';

		                	} elseif ( $tab == 'fbem_import' ) {
		                		
		                		require_once IFE_PLUGIN_DIR . '/templates/import-em-facebook-events.php';

		                	} elseif ( $tab == 'settings' ) {
		                		
		                		require_once IFE_PLUGIN_DIR . '/templates/settings.php';

		                	} elseif ( $tab == 'scheduled' ) {

		                		require_once IFE_PLUGIN_DIR . '/templates/scheduled-import-events.php';

		                	}
			                ?>
		                	<div style="clear: both"></div>
		                </div>

		        </div>
		        
		    </div>
		</div>
		<?php
	}


	/**
	 * Display notices in admin.
	 *
	 * @since    1.0.0
	 */
	public function display_notices() {
		global $fb_errors, $fb_success_msg, $fb_warnings, $fb_info_msg;
		
		if ( ! empty( $fb_errors ) ) {
			foreach ( $fb_errors as $error ) :
			    ?>
			    <div class="notice notice-error is-dismissible">
			        <p><?php echo $error; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $fb_success_msg ) ) {
			foreach ( $fb_success_msg as $success ) :
			    ?>
			    <div class="notice notice-success is-dismissible">
			        <p><?php echo $success; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $fb_warnings ) ) {
			foreach ( $fb_warnings as $warning ) :
			    ?>
			    <div class="notice notice-warning is-dismissible">
			        <p><?php echo $warning; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $fb_info_msg ) ) {
			foreach ( $fb_info_msg as $info ) :
			    ?>
			    <div class="notice notice-info is-dismissible">
			        <p><?php echo $info; ?></p>
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

		$cpt_labels = array(
			'name'               => _x( 'Facebook Scheduled Import', 'post type general name', 'import-facebook-events' ),
			'singular_name'      => _x( 'Facebook Scheduled Import', 'post type singular name', 'import-facebook-events' ),
			'menu_name'          => _x( 'Facebook Scheduled Imports', 'admin menu', 'import-facebook-events' ),
			'name_admin_bar'     => _x( 'Facebook Scheduled Import', 'add new on admin bar', 'import-facebook-events' ),
			'add_new'            => _x( 'Add New', 'book', 'import-facebook-events' ),
			'add_new_item'       => __( 'Add New Import', 'import-facebook-events' ),
			'new_item'           => __( 'New Import', 'import-facebook-events' ),
			'edit_item'          => __( 'Edit Import', 'import-facebook-events' ),
			'view_item'          => __( 'View Import', 'import-facebook-events' ),
			'all_items'          => __( 'All Facebook Scheduled Imports', 'import-facebook-events' ),
			'search_items'       => __( 'Search Scheduled Imports', 'import-facebook-events' ),
			'parent_item_colon'  => __( 'Parent Imports:', 'import-facebook-events' ),
			'not_found'          => __( 'No Imports found.', 'import-facebook-events' ),
			'not_found_in_trash' => __( 'No Imports found in Trash.', 'import-facebook-events' ),
		);

		$cpt_args = array(
			'labels'             => $cpt_labels,
	        'description'        => __( 'Facebook Scheduled Imports.', 'import-facebook-events' ),
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
			'menu_position'		=> 5,
		);

		register_post_type( 'fb_scheduled_imports', $cpt_args );
	}


	/**
	 * Check for dependencies to work this plugin and deactive plugin if requirements not met.
	 *
	 * @since    1.0.0
	 * @param string $plugin_basename Plugin basename.
	 */
	public function check_requirements( $plugin_basename ) {
		if ( ! $this->is_meets_requirements() ) {
			deactivate_plugins( $plugin_basename );
			add_action( 'admin_notices',array( $this, 'deactivate_notice' ) );
			return false;
		}
		return true;
	}
	/**
	 * Check meets dependencies requirements
	 *
	 * @since  1.0.0
	 * @return boolean true if met requirements.
	 */
	public function is_meets_requirements() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) || is_plugin_active( 'events-manager/events-manager.php' ) ) {
			$options = get_option( IFE_OPTIONS, array() );
			if( ( ! isset($options['facebook_app_id']) || $options['facebook_app_id'] == "" ) || (!isset( $options['facebook_app_secret'] ) || $options['facebook_app_secret'] == "" ) ){
				add_action( 'admin_notices', array( $this, 'facebook_credential_warning') );
			}
			return true;
		}
		return false;
	}

	/**
	 * Display an error message when the plugin deactivates itself.
	 */
	public function deactivate_notice() {
		?>
		<div class="error">
		    <p>
				<?php _e( 'Import Facebook Events requires <a href="https://wordpress.org/plugins/the-events-calendar/" target="_blank" >The Events Calendar</a> or <a href="https://wordpress.org/plugins/events-manager/" target="_blank" >Events Manager</a> to be installed and activated. Import Facebook Events has been deactivated itself.', 'import-facebook-events' ); ?>
		    </p>
		</div>
		<?php
	}

	/**
	 * Display an warning message if Facebook App ID or Facebook App Secret is not there.
	 */
	public function facebook_credential_warning() {
		?>
	    <div class="notice notice-warning is-dismissible">
	        <p><?php esc_html_e( 'Please insert Facebook App ID and App Secret in order to work facebook import.', 'import-facebook-events' ) ?></p>
	    </div>
	    <?php
	}

}
