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
		add_action( 'init', array( $this, 'register_session' ) );
		add_action( 'init', array( $this, 'handle_import_form_submit' ) , 99);
		add_action( 'init', array( $this, 'handle_import_settings_submit' ), 99 );		
		add_action( 'admin_notices', array( $this, 'display_session_success_message' ), 100 );
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_form_submit() {
		global $fb_errors;

		if ( isset( $_POST['ife_action'] ) && $_POST['ife_action'] == 'ife_import_submit' &&  check_admin_referer( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ) ) {
			
			if( !isset( $_POST['import_origin'] ) || empty( $_POST['import_origin'] ) ){
				$fb_errors[] = esc_html__( 'Something went wrong, Please try again.', 'import-facebook-events' );
				return;
			}
			$this->handle_facebook_import_form_submit( $_POST );
		}
	}

	/**
	 * Save Setting for facebook import.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_settings_submit() {
		global $fb_errors, $fb_success_msg;
		if ( isset( $_POST['ife_action'] ) && $_POST['ife_action'] == 'ife_save_settings' &&  check_admin_referer( 'ife_setting_form_nonce_action', 'ife_setting_form_nonce' ) ) {
				
			$ife_options = array();
			$ife_options = isset( $_POST['facebook'] ) ? $_POST['facebook'] : array();
			$is_update = update_option( IFE_OPTIONS, $ife_options );
			if( $is_update ){
				$fb_success_msg[] = __( 'Import settings has been saved successfully.', 'import-facebook-events' );
			}else{
				$fb_errors[] = __( 'Something went wrong! please try again.', 'import-facebook-events' );
			}
		}
	}

	/**
	 * Handle Facebook import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_facebook_import_form_submit(){
		global $fb_errors, $fb_success_msg, $importfbevents;
		
		$event_data = array();
		$event_data['import_by'] = isset( $_POST['facebook_import_by'] ) ? sanitize_text_field( $_POST['facebook_import_by']) : 'facebook_event_id';

		$event_data['event_ids'] = isset( $_POST['facebook_event_ids'] ) ? array_map( 'trim', (array) explode( "\n", preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $_POST['facebook_event_ids'] ) ) ) : array();

		$event_data['page_username'] = isset( $_POST['facebook_page_username'] ) ? sanitize_text_field( $_POST['facebook_page_username']) : '';
		$event_data['import_type'] = 'onetime';
		$event_data['import_frequency'] = isset( $_POST['import_frequency'] ) ? sanitize_text_field( $_POST['import_frequency']) : 'daily';
		$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( $_POST['event_status']) : 'pending';
		$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? $_POST['event_cats'] : array();
		$event_data['import_origin'] = isset( $_POST['import_origin'] ) ? $_POST['import_origin'] : '';

		$options = get_option( IFE_OPTIONS );
		$fb_app_id = isset( $options['facebook_app_id'] ) ? $options['facebook_app_id'] : '';
		$fb_app_secret = isset( $options['facebook_app_secret'] ) ? $options['facebook_app_secret'] : '';
		if( $fb_app_id == '' || $fb_app_secret == '' ){
			$fb_errors[] = __( 'Please insert Facebook app ID and app Secret.', 'import-facebook-events');
			return false;
		}

		$import_events = $importfbevents->facebook->import_events( $event_data );
		if( $import_events && !empty( $import_events ) ){
			$fb_success_msg[] = esc_html__( 'Events are imported successfully.', 'import-facebook-events' );
		}

	}

	/**
	 * Set Success message
	 *
	 * @since    1.0.0
	 */
	public function set_success_message_session( $message = '' ){
		$_SESSION['succ_message'] = $message;
	}

	/**
	 * Register Session
	 *
	 * @since    1.0.0
	 */
	public function register_session(){
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Set Success message
	 *
	 * @since    1.0.0
	 */
	public function display_session_success_message() {
		if ( isset( $_SESSION['succ_message'] ) && $_SESSION['succ_message'] != "" ) {
		?>
		    <div class="notice notice-success is-dismissible">
		        <p><?php esc_html_e( $_SESSION['succ_message'] ); ?></p>
		    </div>
	    <?php
		unset( $_SESSION['succ_message'] );
		}
	}

}
