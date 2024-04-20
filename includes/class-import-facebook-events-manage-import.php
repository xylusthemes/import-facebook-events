<?php
/**
 * Class for manane Imports submissions.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for import related functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_Manage_Import {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_success_messages' ) );
		add_action( 'admin_init', array( $this, 'handle_import_form_submit' ), 99 );
		add_action( 'admin_init', array( $this, 'handle_import_settings_submit' ), 99 );
		add_action( 'admin_init', array( $this, 'handle_gma_settings_submit' ), 99 );
		add_action( 'admin_init', array( $this, 'handle_listtable_oprations' ), 99 );
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_form_submit() {
		global $ife_errors;
		$event_data = array();

		if ( isset( $_POST['ife_action'] ) && 'ife_import_submit' === sanitize_text_field( wp_unslash( $_POST['ife_action'] ) ) && check_admin_referer( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ) ) { // input var okay.

			$event_origin = isset( $_POST['import_origin'] ) ? sanitize_text_field( wp_unslash( $_POST['import_origin'] ) ) : ''; // input var okay.
			if ( empty( $event_origin ) ) {
				$event_origin = 'facebook';
			}

			$event_data['import_into'] = isset( $_POST['event_plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['event_plugin'] ) ) : ''; // input var okay.
			if ( empty( $event_data['import_into'] ) ) {
				$ife_errors[] = esc_html__( 'Please provide Import into plugin for Event import.', 'import-facebook-events' );
				return;
			}
			$event_data['import_type']      = 'onetime';
			$event_data['import_frequency'] = '';
			$event_data['event_status']     = isset( $_POST['event_status'] ) ? sanitize_text_field( wp_unslash( $_POST['event_status'] ) ) : 'pending'; // input var okay.
			$event_data['event_cats']       = isset( $_POST['event_cats'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_cats'] ) ) : array(); // input var okay.

			$event_data['import_origin'] = 'facebook';
			$event_data['import_by']     = 'facebook_event_id';
			$event_data['page_username'] = '';
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $event_data['event_ids'] = isset( $_POST['facebook_event_ids'] ) ? array_map( 'trim', array_map( 'sanitize_text_field', explode( "\n", preg_replace( "/^\n+|^[\t\s]*\n+/m", '', wp_unslash( $_POST['facebook_event_ids'] ) ) ) ) ) : array(); // input var okay.
            $event_data['event_author']     = !empty( $_POST['event_author'] ) ? $_POST['event_author'] : get_current_user_id();
            
			if( 'ical' === $event_origin ){
				$this->handle_ical_import_form_submit( $event_data );
			} else {
				$this->handle_facebook_import_form_submit( $event_data );
			}
		}
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_settings_submit() {
		global $ife_errors, $ife_success_msg;
		if ( isset( $_POST['ife_action'] ) && 'ife_save_settings' === sanitize_text_field( wp_unslash( $_POST['ife_action'] ) ) && check_admin_referer( 'ife_setting_form_nonce_action', 'ife_setting_form_nonce' ) ) { // input var okay.

			$ife_options             = array();
			$ife_options['facebook'] = isset( $_POST['facebook'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['facebook'] ) ) : array(); // input var okay.

			$is_update = update_option( IFE_OPTIONS, $ife_options['facebook'] );
			if ( $is_update ) {
				$ife_success_msg[] = __( 'Import settings has been saved successfully.', 'import-facebook-events' );
			} else {
				$ife_errors[] = __( 'Something went wrong! please try again.', 'import-facebook-events' );
			}
		}
	}

	/**
	 * Process insert google maps api key for embed maps
	 *
	 * @since    1.0.0
	 */
	public function handle_gma_settings_submit() {
		global $ife_errors, $ife_success_msg;
		if ( isset( $_POST['ife_gma_action'] ) && 'ife_save_gma_settings' === sanitize_text_field( wp_unslash( $_POST['ife_gma_action'] ) ) && check_admin_referer( 'ife_gma_setting_form_nonce_action', 'ife_gma_setting_form_nonce' ) ) { // input var okay.
			$gma_option = array();
			$gma_option['ife_google_maps_api_key'] = isset( $_POST['ife_google_maps_api_key'] ) ? wp_unslash( $_POST['ife_google_maps_api_key'] ) : '';
			$gma_option['ife_google_geolocation_api_key'] = isset( $_POST['ife_google_geolocation_api_key'] ) ? wp_unslash( $_POST['ife_google_geolocation_api_key'] ) : '';
			$is_gm_update  = update_option( 'ife_google_maps_api_key', $gma_option['ife_google_maps_api_key'] );
			$is_ggl_update = update_option( 'ife_google_geolocation_api_key', $gma_option['ife_google_geolocation_api_key'] );
			if ( $is_gm_update || $is_ggl_update ) {
				$ife_success_msg[] = __( 'Google Maps API Key has been saved successfully.', 'import-facebook-events' );
			} else {
				$ife_errors[] = __( 'Something went wrong! please try again.', 'import-facebook-events' );
			}
		}
	}

	/**
	 * Delete scheduled import from list table.
	 *
	 * @since    1.0.0
	 */
	public function handle_listtable_oprations() {
		global $ife_success_msg;

		if ( isset( $_GET['ife_action'] ) && 'ife_history_delete' === sanitize_text_field( wp_unslash( $_GET['ife_action'] ) ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ife_delete_history_nonce' ) ) { // input var okay.
			$history_id  = isset( $_GET['history_id'] ) ? absint( $_GET['history_id'] ) : 0; // input var okay.
			$page        = 'facebook_import';
			$wp_redirect = admin_url( 'admin.php?page=' . $page );
			if ( $history_id > 0 ) {
				wp_delete_post( $history_id, true );
				$query_args = array(
					'imp_fb_msg' => 'history_del',
					'tab'        => 'history',
				);
				wp_safe_redirect( add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		if ( isset( $_GET['ife_action'] ) && 'ife_run_import' === sanitize_text_field( wp_unslash( $_GET['ife_action'] ) ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ife_run_import_nonce' ) ) { // input var okay.
			$import_id   = isset( $_GET['import_id'] ) ? absint( $_GET['import_id'] ) : 0; // input var okay.
			$page        = 'facebook_import';
			$wp_redirect = admin_url( 'admin.php?page=' . $page );
			if ( $import_id > 0 ) {
				do_action( 'xt_run_fb_scheduled_import', $import_id );
				$query_args = array(
					'imp_fb_msg' => 'import_success',
					'tab'        => 'scheduled',
				);
				wp_safe_redirect( add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		$is_bulk_delete = ( ( isset( $_GET['action'] ) && 'delete' === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) || ( isset( $_GET['action2'] ) && 'delete' === sanitize_text_field( wp_unslash( $_GET['action2'] ) ) ) ); // input var okay.

		if ( $is_bulk_delete && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-fb_import_histories' ) ) { // input var okay.
			$wp_redirect = admin_url();
			if ( isset( $_GET['_wp_http_referer'] ) ) {
				$wp_redirect = get_site_url() . urldecode( sanitize_text_field( wp_unslash( $_GET['_wp_http_referer'] ) ) ); // input var okay.
			}
			$delete_ids = isset( $_GET['import_history'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_GET['import_history'] ) ) : array(); // input var okay.
			if ( ! empty( $delete_ids ) ) {
				foreach ( $delete_ids as $delete_id ) {
					wp_delete_post( $delete_id, true );
				}
			}
			$query_args = array(
				'imp_fb_msg' => 'history_dels',
				'tab'        => 'history',
			);
			wp_safe_redirect( add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

		// Delete All History Data 
		if ( isset( $_GET['ife_action'] ) && esc_attr( $_GET['ife_action'] ) === 'ife_all_history_delete' && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'ife_delete_all_history_nonce' ) ) {
			$page        = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : 'facebook_import';
			$tab         = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'history';
			$wp_redirect = admin_url( 'admin.php?page=' . $page );

			$delete_ids  = get_posts( array( 'numberposts' => -1,'fields' => 'ids', 'post_type' => 'ife_import_history' ) );
			if ( ! empty( $delete_ids ) ) {
				foreach ( $delete_ids as $delete_id ) {
					wp_delete_post( $delete_id, true );
				}
			}		
			$query_args = array(
				'imp_fb_msg' => 'history_dels',
				'tab'     => $tab,
			);
			wp_redirect( add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

	}

	/**
	 * Handle Facebook import form submit.
	 *
	 * @since    1.0.0
	 * @param array $event_data Event Data.
	 */
	public function handle_facebook_import_form_submit( $event_data ) {
		global $ife_errors, $ife_success_msg, $ife_events;

		$fboptions           = ife_get_import_options( 'facebook' );
		$facebook_app_id     = isset( $fboptions['facebook_app_id'] ) ? $fboptions['facebook_app_id'] : '';
		$facebook_app_secret = isset( $fboptions['facebook_app_secret'] ) ? $fboptions['facebook_app_secret'] : '';
		if ( empty( $facebook_app_id ) || empty( $facebook_app_secret ) ) {
			$ife_errors[] = __( 'Please insert Facebook app ID and app Secret.', 'import-facebook-events' );
			return;
		}

		$import_events = $ife_events->facebook->import_events( $event_data );
		if ( $import_events && ! empty( $import_events ) ) {
			$ife_events->common->display_import_success_message( $import_events, $event_data );
		} else {
			if ( empty( $ife_errors ) ) {
				$ife_success_msg[] = esc_html__( 'Nothing to Import', 'import-facebook-events' ) . '<br>';
			}
			return false;
		}
	}

	/**
	 * Handle iCal import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_ical_import_form_submit( $event_data ){
		global $ife_errors, $ife_success_msg, $ife_events;

		$event_data['import_origin'] = 'ical';
		$event_data['import_by'] = 'ics_file';
		$event_data['ical_url'] = '';
		$event_data['start_date'] = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
		$event_data['end_date'] = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

		if( $event_data['import_by'] == 'ics_file' ){

			$file_ext = pathinfo( $_FILES['ics_file']['name'], PATHINFO_EXTENSION );
			$file_type = $_FILES['ics_file']['type'];

			if( $file_type != 'text/calendar' && $file_ext != 'ics' ){
				$ife_errors[] = esc_html__( 'Please upload .ics file', 'import-facebook-events');
				return;
			}

			$ics_content =  file_get_contents( $_FILES['ics_file']['tmp_name'] );
			$import_events = $ife_events->ical->import_events_from_ics_content( $event_data, $ics_content );

			if( $import_events && !empty( $import_events ) ){
				$ife_events->common->display_import_success_message( $import_events, $event_data );
			}else{
				if( empty( $ife_errors ) ){
					$ife_success_msg[] = esc_html__( 'Nothing to import.', 'import-facebook-events' );
				}
			}
		}
	}

	/**
	 * Setup Success Messages.
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages() {
		global $ife_success_msg;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['imp_fb_msg'] ) && ! empty( $_GET['imp_fb_msg'] ) ) { // input var okay.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			switch ( sanitize_text_field( wp_unslash( $_GET['imp_fb_msg'] ) ) ) { // input var okay.
				case 'import_del':
					$ife_success_msg[] = esc_html__( 'Scheduled import deleted successfully.', 'import-facebook-events' );
					break;

				case 'import_dels':
					$ife_success_msg[] = esc_html__( 'Scheduled imports are deleted successfully.', 'import-facebook-events' );
					break;

				case 'import_success':
					$ife_success_msg[] = esc_html__( 'Scheduled import has been run successfully.', 'import-facebook-events' );
					break;

				case 'history_del':
					$ife_success_msg[] = esc_html__( 'Import history deleted successfully.', 'import-facebook-events' );
					break;

				case 'history_dels':
					$ife_success_msg[] = esc_html__( 'Import histories are deleted successfully.', 'import-facebook-events' );
					break;

				default:
					$ife_success_msg[] = esc_html__( 'Scheduled imports are deleted successfully.', 'import-facebook-events' );
					break;
			}
		}
	}
}
