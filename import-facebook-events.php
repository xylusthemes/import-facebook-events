<?php
/**
 * Plugin Name:       Import Facebook Events
 * Plugin URI:        http://xylusthemes.com/plugins/import-facebook-events/
 * Description:       Import Facebook Events allows you to import Facebook ( facebook.com ) events into your WordPress site.
 * Version:           1.0.0
 * Author:            Xylus Themes
 * Author URI:        http://xylusthemes.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       import-facebook-events
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Import_Facebook_Events' ) ):

/**
* Main Import Facebook Events class
*/
class Import_Facebook_Events{
	
	/** Singleton *************************************************************/
	/**
	 * Import_Facebook_Events The one true Import_Facebook_Events.
	 */
	private static $instance;

    /**
     * Main Import Facebook Events Instance.
     * 
     * Insure that only one instance of Import_Facebook_Events exists in memory at any one time.
     * Also prevents needing to define globals all over the place.
     *
     * @since 1.0.0
     * @static object $instance
     * @uses Import_Facebook_Events::setup_constants() Setup the constants needed.
     * @uses Import_Facebook_Events::includes() Include the required files.
     * @uses Import_Facebook_Events::laod_textdomain() load the language files.
     * @see run_wp_event_aggregator()
     * @return object| WP Event Aggregator the one true WP Event Aggregator.
     */
	public static function instance() {
		if( ! isset( self::$instance ) && ! (self::$instance instanceof Import_Facebook_Events ) ) {
			self::$instance = new Import_Facebook_Events;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->admin = new Import_Facebook_Events_Admin();
			self::$instance->manage_import = new Import_Facebook_Events_Manage_Import();
			self::$instance->facebook = new Import_Facebook_Events_Facebook();

		}
		return self::$instance;	
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent Import_Facebook_Events from being loaded more than once.
	 *
	 * @since 1.0.0
	 * @see Import_Facebook_Events::instance()
	 * @see run_wp_event_aggregator()
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent Import_Facebook_Events from being cloned.
	 *
	 * @since 1.0.0
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'import-facebook-events' ), '1.0.0' ); }

	/**
	 * A dummy magic method to prevent Import_Facebook_Events from being unserialized.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'import-facebook-events' ), '1.0.0' ); }


	/**
	 * Setup plugins constants.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if( ! defined( 'IFE_VERSION' ) ){
			define( 'IFE_VERSION', '1.0.0' );
		}

		// Plugin folder Path.
		if( ! defined( 'IFE_PLUGIN_DIR' ) ){
			define( 'IFE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL.
		if( ! defined( 'IFE_PLUGIN_URL' ) ){
			define( 'IFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin root file.
		if( ! defined( 'IFE_PLUGIN_FILE' ) ){
			define( 'IFE_PLUGIN_FILE', __FILE__ );
		}

		// Options
		if( ! defined( 'IFE_OPTIONS' ) ){
			define( 'IFE_OPTIONS', 'ife_facebook_options' );
		}

		define( 'IFE_TEC_TAXONOMY', 'tribe_events_cat' );
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			define( 'IFE_TEC_POSTTYPE', Tribe__Events__Main::POSTTYPE );
		}else{
			define( 'IFE_TEC_POSTTYPE', 'tribe_events' );
		}

		if ( class_exists( 'Tribe__Events__Organizer' ) ) {
			define( 'IFE_TEC_ORGANIZER_POSTTYPE', Tribe__Events__Organizer::POSTTYPE );
		}else{
			define( 'IFE_TEC_ORGANIZER_POSTTYPE', 'tribe_organizer' );
		}

		if ( class_exists( 'Tribe__Events__Venue' ) ) {
			define( 'IFE_TEC_VENUE_POSTTYPE', Tribe__Events__Venue::POSTTYPE );
		}else{
			define( 'IFE_TEC_VENUE_POSTTYPE', 'tribe_venue' );
		}

		if ( defined( 'EM_POST_TYPE_EVENT' ) ) {
			define( 'IFE_EM_POSTTYPE', EM_POST_TYPE_EVENT );
		} else {
			define( 'IFE_EM_POSTTYPE', 'event' );
		}
		if ( defined( 'EM_TAXONOMY_CATEGORY' ) ) {
			define( 'IFE_EM_TAXONOMY',EM_TAXONOMY_CATEGORY );
		} else {
			define( 'IFE_EM_TAXONOMY','event-categories' );
		}
		if ( defined( 'EM_POST_TYPE_LOCATION' ) ) {
			define( 'IFE_LOCATION_POSTTYPE',EM_POST_TYPE_LOCATION );
		} else {
			define( 'IFE_LOCATION_POSTTYPE','location' );
		}

		// Pro plugin Buy now Link.
		if( ! defined( 'IFE_PLUGIN_BUY_NOW_URL' ) ){
			define( 'IFE_PLUGIN_BUY_NOW_URL', 'https://xylusthemes.com/plugins/import-facebook-events/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin' );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {

		require_once IFE_PLUGIN_DIR . 'includes/common-functions.php';
		require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-facebook.php';
		require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-admin.php';
		require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-manage-import.php';
	
	}

	/**
	 * Loads the plugin language files.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain(){

		load_plugin_textdomain(
			'import-facebook-events',
			false,
			IFE_PLUGIN_DIR . '/languages/'
		);
	
	}
	
}

endif; // End If class exists check.

/**
 * The main function for that returns Import_Facebook_Events
 *
 * The main function responsible for returning the one true Import_Facebook_Events
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $importfbevents = run_import_facebook_events(); ?>
 *
 * @since 1.0.0
 * @return object|Import_Facebook_Events The one true Import_Facebook_Events Instance.
 */
function run_import_facebook_events() {
	return Import_Facebook_Events::instance();
}

// Get Import_Facebook_Events Running.
global $importfbevents, $fb_errors, $fb_success_msg, $fb_warnings, $fb_info_msg;
$importfbevents = run_import_facebook_events();
$importfbevents->admin->check_requirements( plugin_basename( __FILE__ ) );
$fb_errors = $fb_warnings = $fb_success_msg = $fb_info_msg = array();
