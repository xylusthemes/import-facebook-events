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

		$raw_urls = trim( $event_data['ical_url'] ?? '' );
		if( empty( $raw_urls ) ){
			$ife_errors[] = esc_html__( 'Please provide iCal URL.', 'import-facebook-events');
			return;
		}

		// Parse multiple iCal URLs.
		$urls = preg_split( '/[\r\n,]+/', $raw_urls );
		$urls = array_values( array_unique( array_filter( array_map( 'trim', $urls ) ) ) );

		if ( ! ife_is_pro() && count( $urls ) > 1 ) {
			$urls = array_slice( $urls, 0, 1 );
		}

		foreach ( $urls as $ical_url ) {
			// Convert webcal/webcals protocols to http/https
			$ical_url    = str_replace( array( 'webcal://', 'webcals://' ), array( 'http://', 'https://' ), $ical_url );
			$ics_content = $this->get_remote_content( $ical_url );

			if( false !== $ics_content && ! empty( $ics_content ) ){
				$events = $this->import_events_from_ics_content( $event_data, $ics_content );
				if( ! empty( $events ) && is_array( $events ) ){
					$imported_events = array_merge( $imported_events, $events );
				}
			}
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
		global $ife_errors;

		$ical_url = html_entity_decode( str_replace( array( 'webcal://', 'webcals://' ), array( 'http://', 'https://' ), trim( $ical_url ) ) );

		$ch = curl_init();
		curl_setopt_array( $ch, array(
			CURLOPT_URL            => $ical_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 30,
			CURLOPT_CONNECTTIMEOUT => 15,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_ENCODING       => '',
			CURLOPT_HTTPHEADER     => array(
				'Accept: text/calendar,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language: en-US,en;q=0.9',
				'Sec-Fetch-Dest: document',
				'Sec-Fetch-Mode: navigate',
				'Sec-Fetch-Site: none',
				'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
			),
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		) );

		$response = curl_exec( $ch );

		if ( curl_errno( $ch ) ) {
			$ife_errors[] = sprintf( esc_html__( 'cURL Error: %s', 'import-facebook-events' ), curl_error( $ch ) );
			if ( PHP_VERSION_ID < 80000 ) { @curl_close( $ch ); } // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
			return false;
		}

		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		if ( PHP_VERSION_ID < 80000 ) { @curl_close( $ch ); } // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close

		if ( $http_code !== 200 ) {
			$ife_errors[] = sprintf( esc_html__( 'Unable to retrieve content from URL (HTTP %d).', 'import-facebook-events' ), $http_code );
			return false;
		}

		return $response;
	}
}
