<?php
/**
 * Plugin Name:       Import Social Events
 * Plugin URI:        http://xylusthemes.com/plugins/import-facebook-events/
 * Description:       Import Social Events allows you to import Facebook ( facebook.com ) events into your WordPress site.
 * Version:           1.7.9
 * Author:            Xylus Themes
 * Author URI:        http://xylusthemes.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       import-facebook-events
 * Domain Path:       /languages
 *
 * @package     Import_Facebook_Events
 * @author      Dharmesh Patel <dspatel44@gmail.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Import_Facebook_Events' ) ) :

	/**
	 * Main Import Facebook Events class
	 */
	class Import_Facebook_Events {

		/** Singleton *************************************************************/
		/**
		 * Import_Facebook_Events The one true Import_Facebook_Events.
		 *
		 * @var object Instance of Import_Facebook_Events
		 */
		private static $instance;
		public $common, $cpt, $facebook, $admin, $manage_import, $ife, $tec, $em, $eventon, $event_organizer, $aioec, $my_calendar, $ee4, $ical_parser, $ical, $fb_authorize, $common_pro, $facebook_pro, $cron, $ical_parser_aioec;

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
		 * @see run_import_facebook_events()
		 * @return object| Import Facebook Events the one true Import Facebook Events.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Import_Facebook_Events ) ) {
				self::$instance = new Import_Facebook_Events();
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
				add_action( 'plugins_loaded', array( self::$instance, 'load_authorize_class' ), 20 );
				add_action( 'wp_enqueue_scripts', array( self::$instance, 'ife_enqueue_style' ) );
				add_action( 'wp_enqueue_scripts', array( self::$instance, 'ife_enqueue_script' ) );
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( self::$instance, 'ife_setting_doc_links' ) );

				self::$instance->includes();
				self::$instance->common   = new Import_Facebook_Events_Common();
				self::$instance->cpt      = new Import_Facebook_Events_Cpt();
				self::$instance->facebook = new Import_Facebook_Events_Facebook();
				self::$instance->admin    = new Import_Facebook_Events_Admin();
				
				self::$instance->ical_parser 	   = new Import_Facebook_Events_Ical_Parser();
				self::$instance->ical_parser_aioec = new Import_Facebook_Events_Ical_Parser_AIOEC();
				self::$instance->ical 			   = new Import_Facebook_Events_Ical();
				if ( ife_is_pro() ) {
					self::$instance->manage_import = new Import_Facebook_Events_Pro_Manage_Import();
				} else {
					self::$instance->manage_import = new Import_Facebook_Events_Manage_Import();
				}
				self::$instance->ife             = new Import_Facebook_Events_IFE();
				self::$instance->tec             = new Import_Facebook_Events_TEC();
				self::$instance->em              = new Import_Facebook_Events_EM();
				self::$instance->eventon         = new Import_Facebook_Events_EventON();
				self::$instance->event_organizer = new Import_Facebook_Events_Event_Organizer();
				self::$instance->aioec           = new Import_Facebook_Events_Aioec();
				self::$instance->my_calendar     = new Import_Facebook_Events_My_Calendar();
				self::$instance->ee4             = new Import_Facebook_Events_EE4();
			}
			return self::$instance;
		}

		/** Magic Methods *********************************************************/

		/**
		 * A dummy constructor to prevent Import_Facebook_Events from being loaded more than once.
		 *
		 * @since 1.0.0
		 * @see Import_Facebook_Events::instance()
		 * @see run_import_facebook_events()
		 */
		private function __construct() {
			/* Do nothing here */ }

		/**
		 * A dummy magic method to prevent Import_Facebook_Events from being cloned.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'import-facebook-events' ), '1.7.9' ); }

		/**
		 * A dummy magic method to prevent Import_Facebook_Events from being unserialized.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'import-facebook-events' ), '1.7.9' ); }


		/**
		 * Setup plugins constants.
		 *
		 * @access private
		 * @since 1.0.0
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version.
			if ( ! defined( 'IFE_VERSION' ) ) {
				define( 'IFE_VERSION', '1.7.9' );
			}

			// Minimum Pro plugin version.
			if ( ! defined( 'IFE_MIN_PRO_VERSION' ) ) {
				define( 'IFE_MIN_PRO_VERSION', '1.7.5' );
			}

			// Plugin folder Path.
			if ( ! defined( 'IFE_PLUGIN_DIR' ) ) {
				define( 'IFE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin folder URL.
			if ( ! defined( 'IFE_PLUGIN_URL' ) ) {
				define( 'IFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin root file.
			if ( ! defined( 'IFE_PLUGIN_FILE' ) ) {
				define( 'IFE_PLUGIN_FILE', __FILE__ );
			}

			// Options.
			if ( ! defined( 'IFE_OPTIONS' ) ) {
				define( 'IFE_OPTIONS', 'ife_facebook_options' );
			}

			// Pro plugin Buy now Link.
			if ( ! defined( 'IFE_PLUGIN_BUY_NOW_URL' ) ) {
				define( 'IFE_PLUGIN_BUY_NOW_URL', 'http://xylusthemes.com/plugins/import-facebook-events/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin' );
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

			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-common.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-list-table.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-admin.php';
			if ( ife_is_pro() ) {
				require_once IFEPRO_PLUGIN_DIR . 'includes/class-import-facebook-events-manage-import.php';
			} else {
				require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-manage-import.php';
			}

			if( !class_exists( 'Kigkonsult\Icalcreator\Vcalendar' ) ){
				require_once IFE_PLUGIN_DIR . 'includes/lib/icalcreator/autoload.php';
			}
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-ical_parser.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-ical_parser_aioec.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-ical.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-cpt.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-facebook.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-ife.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-tec.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-em.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-eventon.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-event-organizer.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-aioec.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-my-calendar.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-ee4.php';
			require_once IFE_PLUGIN_DIR . 'includes/class-ife-plugin-deactivation.php';
			// Gutenberg Block.
			require_once IFE_PLUGIN_DIR . 'blocks/facebook-events/index.php';
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function load_textdomain() {

			load_plugin_textdomain(
				'import-facebook-events',
				false,
				basename( dirname( __FILE__ ) ) . '/languages'
			);

		}

		/**
		 * IFE setting And docs link add in plugin page.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function ife_setting_doc_links ( $links ) {
			$ife_setting_doc_link = array(
                'ife-event-setting' => sprintf(
                    '<a href="%s">%s</a>',
                    esc_url( admin_url( 'admin.php?page=facebook_import&tab=settings' ) ),
                    esc_html__( 'Setting', 'import-facebook-events' )
                ),
                'ife-event-docs' => sprintf(
                    '<a target="_blank" href="%s">%s</a>',
                    esc_url( 'https://docs.xylusthemes.com/docs/import-facebook-events/' ),
                    esc_html__( 'Docs', 'import-facebook-events' )
                ),
            );
            return array_merge( $links, $ife_setting_doc_link );
		}

		/**
		 * Loads the facebook authorize class
		 *
		 * @access public
		 * @since 1.5
		 * @return void
		 */
		public function load_authorize_class() {

			if ( ! class_exists( 'Import_Facebook_Events_Pro_FB_Authorize', false ) ) {
				include_once IFE_PLUGIN_DIR . 'includes/class-import-facebook-events-fb-authorize.php';
				global $ife_events;
				if ( class_exists( 'Import_Facebook_Events_FB_Authorize', false ) && ! empty( $ife_events ) ) {
					$ife_events->fb_authorize = new Import_Facebook_Events_FB_Authorize();
				}
			}
		}

		/**
		 * Enqueue style front-end
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function ife_enqueue_style() {

			$css_dir = IFE_PLUGIN_URL . 'assets/css/';
			wp_enqueue_style( 'font-awesome', $css_dir . 'font-awesome.min.css', false, IFE_VERSION );
			wp_enqueue_style( 'import-facebook-events-front', $css_dir . 'import-facebook-events.css', false, IFE_VERSION );
			wp_enqueue_style( 'import-facebook-events-front-style2', $css_dir . 'grid-style2.css', false, IFE_VERSION );
		}

		/**
		 * Enqueue script front-end
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function ife_enqueue_script() {

			// Enqueue script here.
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
 * Example: <?php $ife_events = run_import_facebook_events(); ?>
 *
 * @since 1.0.0
 * @return object|Import_Facebook_Events The one true Import_Facebook_Events Instance.
 */
function run_import_facebook_events() {
	return Import_Facebook_Events::instance();
}

/**
 * Get Import events setting options
 *
 * @since 1.0
 * @param string $type origin for requested options.
 * @return array
 */
function ife_get_import_options( $type = '' ) {
	$ife_options = get_option( IFE_OPTIONS );
	return $ife_options;
}

// Get Import_Facebook_Events Running.
global $ife_events, $ife_errors, $ife_success_msg, $ife_warnings, $ife_info_msg;
$ife_errors      = array();
$ife_warnings    = array();
$ife_success_msg = array();
$ife_info_msg    = array();
$ife_events      = run_import_facebook_events();

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0
 */
function ife_activate_import_facebook_events() {
	global $ife_events;
	$ife_events->cpt->register_event_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ife_activate_import_facebook_events' );
