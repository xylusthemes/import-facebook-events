<?php
/**
 * Class for Import Events into Event Espresso 4
 *
 * @link       http://xylusthemes.com/
 * @since      1.3.0
 *
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EE4 functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/includes
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_EE4 {

	/**
	 * $taxonomy Event Taxonomy
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * $event_posttype Event Posttype
	 *
	 * @var string
	 */
	protected $event_posttype;

	/**
	 * $venue_posttype Venue Posttype
	 *
	 * @var string
	 */
	protected $venue_posttype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.0
	 */
	public function __construct() {

		$this->event_posttype = 'espresso_events';
		$this->taxonomy       = 'espresso_event_categories';
		$this->venue_posttype = 'espresso_venues';

	}


	/**
	 * Get Event Posttype
	 *
	 * @return string
	 */
	public function get_event_posttype() {
		return $this->event_posttype;
	}

	/**
	 * Get Taxonomy
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return $this->taxonomy;
	}

	/**
	 * Get Venue Posttype
	 *
	 * @return string
	 */
	public function get_venue_posttype() {
		return $this->venue_posttype;
	}

	/**
	 * Import event into EE4
	 *
	 * @since    1.3.0
	 * @param  array $centralize_array Centralize event array.
	 * @param  array $event_args Event Args array.
	 * @return array
	 */
	public function import_event( $centralize_array, $event_args ) {
		global $wpdb, $ife_events;

		if ( empty( $centralize_array ) || ! isset( $centralize_array['ID'] ) ) {
			return false;
		}

		$is_exitsing_event = $ife_events->common->get_event_by_event_id( $this->event_posttype, $centralize_array['ID'] );
		$options           = ife_get_import_options( $centralize_array['origin'] );

		if ( $is_exitsing_event ) {
			// Update event or not?
			$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
			$skip_trash    = isset( $options['skip_trash'] ) ? $options['skip_trash'] : 'no';
			$post_status   = get_post_status( $is_exitsing_event );
			if ( 'trash' == $post_status && $skip_trash == 'yes' ) {
				return array(
					'status' => 'skip_trash',
					'id'     => $is_exitsing_event,
				);
			}
			if ( 'yes' !== $update_events ) {
				return array(
					'status' => 'skipped',
					'id'     => $is_exitsing_event,
				);
			}
		}

		$origin_event_id  = $centralize_array['ID'];
		$post_title       = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$post_description = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$start_time       = $centralize_array['starttime_local'];
		$end_time         = $centralize_array['endtime_local'];
		$ticket_uri       = $centralize_array['url'];

		$emeventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
            'post_status'  => 'pending',
            'post_author'  => isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id()
		);
		if ( $is_exitsing_event ) {
			$emeventdata['ID'] = $is_exitsing_event;
		}
		if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
			$emeventdata['post_status'] = $event_args['event_status'];
		}

		if ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable( 'status' ) ) {
			$emeventdata['post_status'] = get_post_status( $is_exitsing_event );
			$event_args['event_status'] = get_post_status( $is_exitsing_event );
		}

		$inserted_event_id = wp_insert_post( $emeventdata, true );

		if ( ! is_wp_error( $inserted_event_id ) ) {
			$inserted_event = get_post( $inserted_event_id );
			if ( empty( $inserted_event ) ) {
				return false;}

			//Event ID
			update_post_meta( $inserted_event_id, 'ife_facebook_event_id', $centralize_array['ID'] );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				if ( ! ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable( 'category' ) ) ) {
					wp_set_object_terms( $inserted_event_id, $ife_cats, $this->taxonomy );
				}
			}

			// Assign Featured images.
			$event_image = $centralize_array['image_url'];
			if ( ! empty( $event_image ) ) {
				$ife_events->common->setup_featured_image_to_event( $inserted_event_id, $event_image );
			}else{
				$default_thumb  = isset( $options['ife_event_default_thumbnail'] ) ? $options['ife_event_default_thumbnail'] : '';
				if( !empty( $default_thumb ) ){
					set_post_thumbnail( $inserted_event_id, $default_thumb );
				}else{
					if ( $is_exitsing_event ) {
						delete_post_thumbnail( $inserted_event_id );
					}
				}
			}

			// Event Date & time Details.
			$event_start_date = gmdate( 'Y-m-d H:i:s', $start_time );
			$event_end_date   = gmdate( 'Y-m-d H:i:s', $end_time );

			$datetime_table   = $wpdb->prefix . 'esp_datetime';
			$event_meta_table = $wpdb->prefix . 'esp_event_meta';

			$datetime_data = array(
				'EVT_ID'        => $inserted_event_id,
				'DTT_EVT_start' => $event_start_date,
				'DTT_EVT_end'   => $event_end_date,
			);

			if ( $is_exitsing_event ) {
				$where     = array( 'EVT_ID' => $inserted_event_id );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$is_insert = $wpdb->update( $datetime_table, $datetime_data, $where ); // db call ok; no-cache ok.
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$is_insert = $wpdb->insert( $datetime_table, $datetime_data ); // db call ok;.
			}

			// Disable event registration.
			if ( ! $is_exitsing_event ) {
				$event_meta_data = array(
					'EVT_display_desc'            => 0,
					'EVT_display_ticket_selector' => 0,
					'EVT_visible_on'              => gmdate( 'Y-m-d H:i:s' ),
				);
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$event_meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT `EVTM_ID` FROM {$event_meta_table} WHERE EVT_ID = %d", $inserted_event_id ) ); // cache ok, db call ok.
				if ( ! empty( $event_meta_id ) && $event_meta_id > 0 ) {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->update(
						$event_meta_table,
						$event_meta_data,
						array(
							'EVTM_ID' => $event_meta_id,
							'EVT_ID'  => $inserted_event_id,
						)
					); // db call ok; no-cache ok.
				} else {
					$event_meta_data['EVT_ID'] = $inserted_event_id;
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->insert( $event_meta_table, $event_meta_data ); // db call ok;.
				}
			}

			/*
			 * Add Venue.
			 */
			$is_online = isset( $centralize_array['is_online'] )  ? $centralize_array['is_online'] : false;
			$venue_id = $this->add_ee4_venue( $centralize_array['location'], $inserted_event_id, $is_online );

			if ( ! empty( $venue_id ) && $venue_id > 0 ) {
				// Connect venue with Event.
				$event_venue_table = $wpdb->prefix . 'esp_event_venue';
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$result = $wpdb->get_col( $wpdb->prepare( "SELECT * FROM {$event_venue_table} WHERE EVT_ID = %d", $inserted_event_id ) ); // cache ok, db call ok.
				if ( count( $result ) > 0 ) {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->update( $event_venue_table, array( 'VNU_ID' => $venue_id ), array( 'EVT_ID' => $inserted_event_id ) ); // db call ok; no-cache ok.
				} else {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->insert(
						$event_venue_table,
						array(
							'EVT_ID' => $inserted_event_id,
							'VNU_ID' => $venue_id,
						)
					); // db call ok;.
				}
			}

			$timezone      = isset( $centralize_array['timezone'] ) ? sanitize_text_field( $centralize_array['timezone'] ) : '';
			$timezone_name = isset( $centralize_array['timezone_name'] ) ? sanitize_text_field( $centralize_array['timezone_name'] ) : '';

			// Save Event Data.
			update_post_meta( $inserted_event_id, 'ife_event_link', esc_url( $ticket_uri ) );
			update_post_meta( $inserted_event_id, 'ife_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, '_ife_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_ife_endtime_str', $end_time );
			update_post_meta( $inserted_event_id, 'start_ts', $start_time );
			update_post_meta( $inserted_event_id, 'end_ts', $end_time );
			update_post_meta( $inserted_event_id, 'ife_event_timezone', $timezone );
			update_post_meta( $inserted_event_id, 'ife_event_timezone_name', $timezone_name );

			if ( $is_exitsing_event ) {
				do_action( 'ife_after_update_ee4_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id'     => $inserted_event_id,
				);
			} else {
				do_action( 'ife_after_create_ee4_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'created',
					'id'     => $inserted_event_id,
				);
			}
		} else {
			return array(
				'status'  => 0,
				'message' => 'Something went wrong, please try again.',
			);
		}
	}

	/**
	 * Add Venue to EE4 Events
	 *
	 * @param array $venue_array Venue data array.
	 * @param int   $event_id Event id.
	 * @return int|bool venue ID on success or false on failure
	 */
	public function add_ee4_venue( $venue_array, $event_id, $is_online ) {
		global $wpdb;

		$venue_id = isset( $venue_array['ID'] ) ? $venue_array['ID'] : '';
		$venue_name = 'Online Event';
		if( $is_online == 1 ){
			$is_exitsing_venue = $this->get_ee4_venue_by_name( $venue_name );
			if ( $is_exitsing_venue ) {
				return $is_exitsing_venue;
			}
		}else{
			if ( empty( $venue_id ) ) {
				return false;
			}

			$is_exitsing_venue = $this->get_ee4_venue_by_id( $venue_id );
			if ( $is_exitsing_venue ) {
				return $is_exitsing_venue;
			}
		}

		// Venue Deatails.
		$address_1     = isset( $venue_array['address_1'] ) ? $venue_array['address_1'] : '';
		$address_2     = isset( $venue_array['address_2'] ) ? $venue_array['address_2'] : '';
		$venue_name    = isset( $venue_array['name'] ) ? sanitize_text_field( $venue_array['name'] ) : '';
		$venue_desc    = isset( $venue_array['description'] ) ? sanitize_text_field( $venue_array['description'] ) : '';
		$venue_address = isset( $venue_array['full_address'] ) ? sanitize_text_field( $venue_array['full_address'] ) : sanitize_text_field( $address_1 );
		$venue_city    = isset( $venue_array['city'] ) ? sanitize_text_field( $venue_array['city'] ) : '';
		$venue_state   = isset( $venue_array['state'] ) ? sanitize_text_field( $venue_array['state'] ) : '';
		$venue_country = isset( $venue_array['country'] ) ? sanitize_text_field( $venue_array['country'] ) : '';
		$venue_zipcode = isset( $venue_array['zip'] ) ? sanitize_text_field( $venue_array['zip'] ) : '';
		$venue_lat     = isset( $venue_array['lat'] ) ? sanitize_text_field( $venue_array['lat'] ) : '';
		$venue_lon     = isset( $venue_array['long'] ) ? sanitize_text_field( $venue_array['long'] ) : '';
		$venue_url     = isset( $venue_array['url'] ) ? esc_url( $venue_array['url'] ) : '';

		if( $is_online == 1 ){
			$venue_name = 'Online Event';
			$venue_id   = 'Online Event';
		}

		$venuedata = array(
			'post_title'   => $venue_name,
			'post_content' => $venue_desc,
			'post_type'    => $this->venue_posttype,
			'post_status'  => 'publish',
		);

		$ivenue_id = wp_insert_post( $venuedata, true );
		update_post_meta( $ivenue_id, 'ife_ee4_venue_id', $venue_id );

		// Get Country code.
		$cnt_iso       = '';
		$sta_id        = '';
		$country_table = $wpdb->prefix . 'esp_country';
		$state_table   = $wpdb->prefix . 'esp_state';
		if ( ! empty( $venue_country ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$cnt_country = $wpdb->get_row( $wpdb->prepare( "SELECT `CNT_ISO`,`CNT_active` FROM {$country_table} WHERE `CNT_name` = %s OR `CNT_ISO` = %s OR `CNT_ISO3` = %s", $venue_country, $venue_country, $venue_country ) ); // cache ok, db call ok.
			if ( ! empty( $cnt_country ) && isset( $cnt_country->CNT_ISO ) ) { // @codingStandardsIgnoreLine.
				$cnt_iso = $cnt_country->CNT_ISO; // @codingStandardsIgnoreLine.
				if ( 0 === $cnt_country->CNT_active ) { // @codingStandardsIgnoreLine.
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$active_con = $wpdb->update( $country_table, array( 'CNT_active' => 1 ), array( 'CNT_ISO' => $cnt_iso ) ); // db call ok; no-cache ok.
				}
			}
		}

		if ( ! empty( $venue_state ) && ! empty( $cnt_iso ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$sta_id = $wpdb->get_var( $wpdb->prepare( "SELECT `STA_ID` FROM {$state_table} WHERE `CNT_ISO` = %s AND (`STA_abbrev` = %s OR `STA_name` = %s)", $cnt_iso, $venue_state, $venue_state ) ); // cache ok, db call ok.
			if ( empty( $sta_id ) || is_null( $sta_id ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$inserted = $wpdb->insert(
					$state_table,
					array(
						'CNT_ISO'    => $cnt_iso,
						'STA_abbrev' => $venue_state,
						'STA_name'   => $venue_state,
					)
				); // db call ok;.
				if ( $inserted ) {
					$sta_id = $wpdb->insert_id;
				}
			}
		}

		// Add Venue Meta.
		$venue_data = array(
			'VNU_ID'              => $ivenue_id,
			'VNU_address'         => $address_1,
			'VNU_address2'        => $address_2,
			'VNU_city'            => $venue_city,
			'VNU_zip'             => $venue_zipcode,
			'VNU_url'             => $venue_url,
			'VNU_enable_for_gmap' => apply_filters( 'ife_ee4_venue_enable_for_map', 1 ),
		);
		if ( ! empty( $cnt_iso ) ) {
			$venue_data['CNT_ISO'] = $cnt_iso;
		}
		if ( ! empty( $sta_id ) ) {
			$venue_data['STA_ID'] = $sta_id;
		}

		$venue_table = $wpdb->prefix . 'esp_venue_meta';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->insert( $venue_table, $venue_data ); // db call ok;.

		return $ivenue_id;
	}

	/**
	 * Check for Existing EE4 Venue
	 *
	 * @since    1.0.0
	 * @param int $venue_id Venue id.
	 * @return int/boolean
	 */
	public function get_ee4_venue_by_id( $venue_id ) {
		if ( empty( $venue_id ) ) {
			return false;
		}

		$existing_venue = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->venue_posttype,
				'meta_key'         => 'ife_ee4_venue_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
				'meta_value'       => $venue_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Ignore.
				'suppress_filters' => false,
			)
		);

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return $existing_venue[0]->ID;
		}
		return false;
	}

	/**
	 * Check for Existing EE4 Venue
	 *
	 * @since    1.7.3
	 * @param int $venue_id Venue id.
	 * @return int/boolean
	 */
	public function get_ee4_venue_by_name( $venue_name ) {
		if ( empty( $venue_name ) ) {
			return false;
		}

		$existing_venue = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->venue_posttype,
				'meta_key'         => 'ife_ee4_venue_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
				'meta_value'       => $venue_name, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Ignore.
				'suppress_filters' => false,
			)
		);

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return $existing_venue[0]->ID;
		}
		return false;
	}

}
