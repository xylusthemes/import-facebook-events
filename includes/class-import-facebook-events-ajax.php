<?php
/**
 * Ajax functions class for WP Event aggregator.
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

class Import_Facebook_Events_Ajax {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_ife_load_paged_events',  array( $this, 'ife_load_paged_events_callback' ) );
        add_action( 'wp_ajax_nopriv_ife_load_paged_events',  array( $this, 'ife_load_paged_events_callback' ) );
	}

	public function ife_load_paged_events_callback() {
		check_ajax_referer( 'ife_ajax_pagi_nonce_action', 'nonce' );

		if ( empty( $_POST['atts'] ) || empty( $_POST['page'] ) ) {
			wp_send_json_error( 'Missing params' );
		}

		$atts = json_decode( wp_unslash( $_POST['atts'] ), true ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! is_array( $atts ) ) {
			$atts = array();
		}
		$sanitized_atts = array();
		foreach ( $atts as $key => $val ) {
			$sanitized_key = sanitize_key( $key );
			if ( is_array( $val ) ) {
				$sanitized_atts[ $sanitized_key ] = array_map( 'sanitize_text_field', $val );
			} else {
				$sanitized_atts[ $sanitized_key ] = sanitize_text_field( $val );
			}
		}
		$sanitized_atts['paged'] = absint( $_POST['page'] );
		$html                    = do_shortcode( '[facebook_events ' . http_build_query( $sanitized_atts, '', ' ' ) . ']' );

		wp_send_json_success( $html );
	}
}