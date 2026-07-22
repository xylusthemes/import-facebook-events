<?php
/**
 * Live Feed Module Entry Point
 *
 * @package Import_Facebook_Events\Feed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
if ( ! defined( 'IFEPRO_DIR' ) ) {
	define( 'IFEPRO_DIR', plugin_dir_path( __FILE__ ) . '../' );
}
if ( ! defined( 'IFEPRO_URL' ) ) {
	define( 'IFEPRO_URL', plugin_dir_url( __FILE__ ) . '../' );
}

define( 'IFEPRO_FEED_DIR', plugin_dir_path( __FILE__ ) );
define( 'IFEPRO_FEED_URL', plugin_dir_url( __FILE__ ) );
define( 'IFEPRO_FEED_VERSION', '1.0.0' );
define( 'IFEPRO_FEED_CPT', 'ifepro_live_feed' );

// Feed classes autoloader.
$ifeprofeed_classes = array(
	'IFEPRO_Feed_DB'         => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-db.php',
	'IFEPRO_Feed_CPT'        => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-cpt.php',
	'IFEPRO_Feed_API'        => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-api.php',
	'IFEPRO_Feed_Admin'      => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-admin.php',
	'IFEPRO_Feed_Shortcode'  => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-shortcode.php',
	'IFEPRO_Feed_Scheduler'  => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-scheduler.php',
	'IFEPRO_Feed_AJAX'       => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-ajax.php',
	'IFEPRO_Feed_Builder_UI' => IFEPRO_FEED_DIR . 'includes/class-ifeprofeed-builder-ui.php',
);

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
foreach ( $ifeprofeed_classes as $class => $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

// Initialize feed hooks and modules.
add_action( 'init', array( IFEPRO_Feed_CPT::instance(), 'register_cpt' ) );
add_action( 'init', array( IFEPRO_Feed_Shortcode::instance(), 'init' ) );
add_action( 'init', array( IFEPRO_Feed_AJAX::instance(), 'init' ) );
IFEPRO_Feed_API::instance();

// Database table initialization.
add_action( 'admin_init', array( IFEPRO_Feed_DB::instance(), 'maybe_create_table' ) );

// Schedule image cleanup cron.
add_action( 'init', array( IFEPRO_Feed_DB::instance(), 'schedule_cleanup' ) );
add_action( 'ifeprofeed_weekly_image_cleanup', array( IFEPRO_Feed_DB::instance(), 'run_weekly_cleanup' ) );

if ( is_admin() ) {
	add_action( 'init', array( IFEPRO_Feed_CPT::instance(), 'init_admin_hooks' ) );
	add_action( 'init', array( IFEPRO_Feed_Admin::instance(), 'init' ) );
	add_action( 'init', array( IFEPRO_Feed_Builder_UI::instance(), 'init' ) );
}

add_action( 'init', function () {
	if ( function_exists( 'as_schedule_recurring_action' ) ) {
		IFEPRO_Feed_Scheduler::instance()->init();
	}
}, 20 );
