<?php
/**
 * Class for iCal Imports.
 *
 * @link       http://xylusthemes.com/
 * @since      1.5
 *
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Facebook_Events_Ical {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.5
	 */
	public function __construct() {
		// init operations for iCal
	}

	/**
	 * import ical events by iCal URL
	 *
	 * @since  1.5
	 * @param  array $eventdata  import event data.
	 * @return array/boolean
	 */
	public function import_events( $event_data = array() ){

		global $ife_errors;
		$imported_events = array();

		$import_by = isset( $event_data['import_by'] ) ? esc_attr( $event_data['import_by'] ) : '';

		if( 'ical_url' != $import_by ){
			return;
		}

		if( $event_data['ical_url'] == '' ){
			$ife_errors[] = esc_html__( 'Please provide iCal URL.', 'import-facebook-events');
			return;
		}

		$ical_url = str_replace( 'webcal://', 'http://', $event_data['ical_url'] );
		$ics_content =  $this->get_remote_content( $ical_url );

		if( false == $ics_content ){
			return false;
		}

		if( $ics_content != "" ){

			$imported_events = $this->import_events_from_ics_content( $event_data, $ics_content );

		}
		return $imported_events;
	}


	/**
	 * import ical events using .ics file
	 *
	 * @since  1.5
	 * @param  array $eventdata  import event data.
	 * @param  array $ics_content  ics content data.
	 * @return array/boolean
	 */
	public function import_events_from_ics_content( $event_data = array(), $ics_content = '' ){
		global $ife_events, $ife_errors;

		error_reporting(0); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
		// Set time and memory limit.
		set_time_limit(0); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		$xt_memory_limit = (int)str_replace( 'M', '',ini_get('memory_limit' ) ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		if( $xt_memory_limit < 512 ){
			ini_set('memory_limit', '512M'); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		}

		$imported_events = array();
		if( empty( $ics_content ) ){
			return array();
		}

		if( ife_aioec_active() && post_type_exists( 'ai1ec_event' ) ){
			$imported_events = $ife_events->ical_parser_aioec->parse_import_events( $event_data, $ics_content );
			return $imported_events;
		}else{
			$imported_events = $ife_events->ical_parser->parse_import_events( $event_data, $ics_content );
			return $imported_events;
		}

	}

	/**
	 * load Content using wp_remote_get
	 *
	 * @param  string $ical_url
	 * @since    1.5
	 */
	protected function get_remote_content( $ical_url ) {

		global $wp_version, $ife_errors;
		$ical_url = str_replace( 'webcal://', 'http://', $ical_url );
		$timeout_in_seconds = 10;
		$response = null;

		$request_args = array(
			'timeout'     => $timeout_in_seconds,
			'sslverify'   => false,
			'method'      => 'GET',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
		);

		$response = wp_remote_get( $ical_url, $request_args );
		if ( is_wp_error( $response ) ) {
			$request_args['sslverify'] = true;
			$response = wp_remote_head( $ical_url, $request_args );
		}

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$ife_errors[] = esc_html__( 'Unable to retrieve content from the provided URL.', 'import-facebook-events');
			return false;
		}

		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		if ( $content_type !== false ) {
			if ( strpos( $content_type, 'text/calendar' ) === false && strpos( $content_type, 'application/calendar+xml' ) === false ) {
				$ife_errors[] = esc_html__( 'The provided URL does not contain iCal format data.', 'import-facebook-events' );
				return false;
			}
		}
		return $response['body'];
	}

}
