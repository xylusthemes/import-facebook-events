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
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Facebook_Events_Manage_Import {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup_success_messages' ) );
		add_action( 'admin_init', array( $this, 'handle_import_form_submit' ) , 99);
		add_action( 'admin_init', array( $this, 'handle_import_settings_submit' ), 99 );
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

		if ( isset( $_POST['ife_action'] ) && sanitize_text_field( $_POST['ife_action'] ) == 'ife_import_submit' &&  check_admin_referer( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ) ) {

			$event_origin = isset( $_POST['import_origin'] ) ? sanitize_text_field( $_POST['import_origin'] ) : '';
			if( empty( $event_origin ) ){
				$event_origin = 'facebook';
			}

			$event_data['import_into'] = isset( $_POST['event_plugin'] ) ? sanitize_text_field( $_POST['event_plugin']) : '';
			if( $event_data['import_into'] == '' ){
				$ife_errors[] = esc_html__( 'Please provide Import into plugin for Event import.', 'import-facebook-events' );
				return;
			}
			$event_data['import_type'] = 'onetime';
			$event_data['import_frequency'] = '';
			$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( $_POST['event_status']) : 'pending';
			$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? (array)$_POST['event_cats'] : array();
			$event_data['event_cats'] = array_map( 'sanitize_text_field', $event_data['event_cats'] );

			$this->handle_facebook_import_form_submit( $event_data );
		}
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_settings_submit() {
		global $ife_errors, $ife_success_msg;
		if ( isset( $_POST['ife_action'] ) && sanitize_text_field( $_POST['ife_action'] ) == 'ife_save_settings' &&  check_admin_referer( 'ife_setting_form_nonce_action', 'ife_setting_form_nonce' ) ) {
				
			$ife_options = array();
			$ife_options['facebook'] = isset( $_POST['facebook'] ) ? (array)$_POST['facebook'] : array();
			$ife_options['facebook'] = array_map( 'sanitize_text_field', $ife_options['facebook'] );
			
			$is_update = update_option( IFE_OPTIONS, $ife_options['facebook'] );
			if( $is_update ){
				$ife_success_msg[] = __( 'Import settings has been saved successfully.', 'import-facebook-events' );
			}else{
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
		if ( isset( $_GET['ife_action'] ) && sanitize_text_field( $_GET['ife_action'] ) == 'ife_simport_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'ife_delete_import_nonce') ) {
			$import_id = absint( $_GET['import_id'] );
			$page = 'facebook_import';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'scheduled';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $import_id > 0 ) {
				$post_type = get_post_type( $import_id );
				if ( $post_type == 'fb_scheduled_imports' ) {
					wp_delete_post( $import_id, true );
					$query_args = array( 'imp_fb_msg' => 'import_del', 'tab' => $tab );
        			wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
					exit;
				}
			}
		}

		if ( isset( $_GET['ife_action'] ) && sanitize_text_field( $_GET['ife_action'] ) == 'ife_history_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'ife_delete_history_nonce' ) ) {
			$history_id = absint( $_GET['history_id'] );
			$page = 'facebook_import';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'history';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $history_id > 0 ) {
				wp_delete_post( $history_id, true );
				$query_args = array( 'imp_fb_msg' => 'history_del', 'tab' => $tab );
        		wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		if ( isset( $_GET['ife_action'] ) && sanitize_text_field( $_GET['ife_action'] ) == 'ife_run_import' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'ife_run_import_nonce') ) {
			$import_id = absint( $_GET['import_id'] );
			$page = 'facebook_import';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'scheduled';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $import_id > 0 ) {
				do_action( 'xt_run_fb_scheduled_import', $import_id );
				$query_args = array( 'imp_fb_msg' => 'import_success', 'tab' => $tab );
        		wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		$is_bulk_delete = ( ( isset( $_GET['action'] ) && sanitize_text_field( $_GET['action'] ) == 'delete' ) || ( isset( $_GET['action2'] ) && sanitize_text_field( $_GET['action2'] ) == 'delete' ) );
		
		if ( $is_bulk_delete && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-fb_scheduled_imports') ) {
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'scheduled';
			$wp_redirect = get_site_url() . urldecode( sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ) );
        	$delete_ids = (array)$_REQUEST['fb_scheduled_import'];
        	$delete_ids = array_map( 'sanitize_text_field', $delete_ids );
        	if( !empty( $delete_ids ) ){
        		foreach ($delete_ids as $delete_id ) {
        			wp_delete_post( $delete_id, true );
        		}            		
        	}
        	$query_args = array( 'imp_fb_msg' => 'import_dels', 'tab' => $tab );
        	wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

		if ( $is_bulk_delete && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-fb_import_histories') ) {
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'history';
			$wp_redirect = get_site_url() . urldecode( sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ) );
        	$delete_ids = (array)$_REQUEST['import_history'];
        	$delete_ids = array_map( 'sanitize_text_field', $delete_ids );
        	if( !empty( $delete_ids ) ){
        		foreach ($delete_ids as $delete_id ) {
        			wp_delete_post( $delete_id, true );
        		}            		
        	}	
        	$query_args = array( 'imp_fb_msg' => 'history_dels', 'tab' => $tab );
        	wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}
	}

	/**
	 * Handle Facebook import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_facebook_import_form_submit( $event_data ){
		global $ife_errors, $ife_success_msg, $ife_events;

		$fboptions = ife_get_import_options( 'facebook' );
		$facebook_app_id = isset( $fboptions['facebook_app_id'] ) ? $fboptions['facebook_app_id'] : '';
		$facebook_app_secret = isset( $fboptions['facebook_app_secret'] ) ? $fboptions['facebook_app_secret'] : '';
		if ( $facebook_app_id == '' || $facebook_app_secret == '' ) {
			$ife_errors[] = __( 'Please insert Facebook app ID and app Secret.', 'import-facebook-events');
			return;
		}

		$event_data['import_origin'] = 'facebook';
		$event_data['import_by'] = 'facebook_event_id';

		$event_data['event_ids'] = isset( $_POST['facebook_event_ids'] ) ? array_map( 'trim', (array) explode( "\n", preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $_POST['facebook_event_ids'] ) ) ) : array();

		$event_data['page_username'] = '';

		$import_events = $ife_events->facebook->import_events( $event_data );
		if ( $import_events && ! empty( $import_events ) ) {
			$ife_events->common->display_import_success_message( $import_events, $event_data );
		}
	}

	/**
	 * Setup Success Messages.
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages() {
		global $ife_success_msg;
		if ( isset( $_GET['imp_fb_msg'] ) && trim( $_GET['imp_fb_msg'] ) != '' ) {
			switch ( sanitize_text_field( wp_unslash( $_GET['imp_fb_msg'] ) ) ) {
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
