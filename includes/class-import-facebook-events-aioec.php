<?php
/**
 * Class for Import Events into All in One Event Calendar
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
 * Class for Aioec plugin related functionalities
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_Aioec {

	/**
	 * All-in-one Event Calendar Event Taxonomy
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * All-in-one Event Calendar Event Posttype
	 *
	 * @var string
	 */
	protected $event_posttype;

	/**
	 * All-in-one Event Calendar Event Custom Table
	 *
	 * @var string
	 */
	protected $event_db_table;

	/**
	 * All-in-one Event Calendar Event Instance custom table
	 *
	 * @var string
	 */
	protected $event_instances_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		global $wpdb;
		$this->event_posttype        = 'ai1ec_event';
		$this->taxonomy              = 'events_categories';
		$this->event_db_table        = "{$wpdb->prefix}ai1ec_events";
		$this->event_instances_table = "{$wpdb->prefix}ai1ec_event_instances";

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
	 * Import event into TEC
	 *
	 * @since    1.0.0
	 * @param  array $centralize_array event array.
	 * @param  array $event_args event arguments.
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
		$timezone_name    = 'UTC';
		if ( isset( $centralize_array['timezone_name'] ) && ! empty( $centralize_array['timezone_name'] ) ) {
			$timezone_name = $centralize_array['timezone_name'];
		}

		$start_time = strtotime( $this->convert_datetime_to_local_datetime( $centralize_array['startime_utc'], $timezone_name ) );
		$end_time   = strtotime( $this->convert_datetime_to_local_datetime( $centralize_array['endtime_utc'], $timezone_name ) );
		$event_uri  = $centralize_array['url'];

		$eo_eventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
            'post_status'  => 'pending',
            'post_author'  => isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id()
		);
		if ( $is_exitsing_event ) {
			$eo_eventdata['ID'] = $is_exitsing_event;
		}

		if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
			$eo_eventdata['post_status'] = $event_args['event_status'];
		}

		if ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable( 'status' ) ) {
			$eo_eventdata['post_status'] = get_post_status( $is_exitsing_event );
		}

		$inserted_event_id = wp_insert_post( $eo_eventdata, true );

		if ( ! is_wp_error( $inserted_event_id ) ) {
			$inserted_event = get_post( $inserted_event_id );
			if ( empty( $inserted_event ) ) {
				return '';}

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

			$timezone      = isset( $centralize_array['timezone'] ) ? sanitize_text_field( $centralize_array['timezone'] ) : '';

			// Save Meta.
			update_post_meta( $inserted_event_id, 'ife_event_link', esc_url( $event_uri ) );
			update_post_meta( $inserted_event_id, 'ife_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, 'ife_event_timezone', $timezone );
			update_post_meta( $inserted_event_id, 'ife_event_timezone_name', $timezone_name );

			// Custom table Details.
			$event_array = array(
				'post_id' => $inserted_event_id,
				'start'   => $start_time,
				'end'     => $end_time,
			);

			$event_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$this->event_instances_table}` WHERE `post_id` = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $inserted_event_id )
				)
			); // cache ok, db call ok.
			if ( $event_count > 0 && is_numeric( $event_count ) ) {
				$where = array( 'post_id' => absint( $inserted_event_id ) );
				$wpdb->update( $this->event_instances_table, $event_array, $where ); // db call ok; no-cache ok.
			} else {
				$wpdb->insert( $this->event_instances_table, $event_array ); // db call ok; no-cache ok.
			}

			$venue         = isset( $centralize_array['location'] ) ? $centralize_array['location'] : '';
			$location_name = isset( $venue['name'] ) ? $venue['name'] : '';
			$address       = isset( $venue['full_address'] ) ? $venue['full_address'] : '';
			if ( empty( $address ) ) {
				$address = isset( $venue['address_1'] ) ? $venue['address_1'] : '';
			}
			$city     = isset( $venue['city'] ) ? $venue['city'] : '';
			$state    = isset( $venue['state'] ) ? $venue['state'] : '';
			$zip      = isset( $venue['zip'] ) ? $venue['zip'] : '';
			$lat      = isset( $venue['lat'] ) ? $venue['lat'] : '';
			$lon      = isset( $venue['long'] ) ? $venue['long'] : '';
			$country  = isset( $venue['country'] ) ? $venue['country'] : '';
			$show_map = 0;

			$show_coordinates = 0;
			if ( ! empty( $lat ) && ! empty( $lon ) ) {
				$show_map         = 1;
				$show_coordinates = 1;
			}
			$full_address = $address;
			if ( ! empty( $city ) ) {
				$full_address .= ', ' . $city;
			}
			if ( ! empty( $state ) ) {
				$full_address .= ', ' . $state;
			}
			if ( ! empty( $zip ) ) {
				$full_address .= ' ' . $zip;
			}

			$organizer = isset( $centralize_array['organizer'] ) ? $centralize_array['organizer'] : '';
			$org_name  = isset( $organizer['name'] ) ? $organizer['name'] : '';
			$org_phone = isset( $organizer['phone'] ) ? $organizer['phone'] : '';
			$org_email = isset( $organizer['email'] ) ? $organizer['email'] : '';
			$org_url   = isset( $organizer['url'] ) ? $organizer['url'] : '';

			$event_table_array = array(
				'post_id'          => $inserted_event_id,
				'start'            => $start_time,
				'end'              => $end_time,
				'timezone_name'    => $timezone_name,
				'allday'           => 0,
				'instant_event'    => 0,
				'venue'            => $location_name,
				'country'          => $country,
				'address'          => $full_address,
				'city'             => $city,
				'province'         => $state,
				'postal_code'      => $zip,
				'show_map'         => $show_map,
				'contact_name'     => $org_name,
				'contact_phone'    => $org_phone,
				'contact_email'    => $org_email,
				'contact_url'      => $org_url,
				'cost'             => '',
				'ticket_url'       => $event_uri,
				'ical_uid'         => $this->get_ical_uid_for_event( $inserted_event_id ),
				'show_coordinates' => $show_coordinates,
			);
			if ( ! empty( $lat ) ) {
				$event_table_array['latitude'] = $lat;
			}
			if ( ! empty( $lon ) ) {
				$event_table_array['longitude'] = $lon;
			}

			$event_format = array(
				'%d',  // post_id.
				'%d',  // start.
				'%d',  // end.
				'%s',  // timezone_name.
				'%d',  // allday.
				'%d',  // instant_event.
				'%s',  // venue.
				'%s',  // country.
				'%s',  // address.
				'%s',  // city.
				'%s',  // province.
				'%s',  // postal_code.
				'%d',  // show_map.
				'%s',  // contact_name.
				'%s',  // contact_phone.
				'%s',  // contact_email.
				'%s',  // contact_url.
				'%s',  // cost.
				'%s',  // ticket_url.
				'%s',  // ical_uid.
				'%d',  // show_coordinates.
			);
			if ( ! empty( $lat ) ) {
				$event_format[] = '%f';  // latitude.
			}
			if ( ! empty( $lon ) ) {
				$event_format[] = '%f';  // longitude.
			}

			$event_exist_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$this->event_db_table}` WHERE `post_id` = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $inserted_event_id )
				)
			); // cache ok, db call ok.
			if ( $event_exist_count > 0 && is_numeric( $event_exist_count ) ) {
				$where = array( 'post_id' => absint( $inserted_event_id ) );
				$wpdb->update( $this->event_db_table, $event_table_array, $where, $event_format ); // db call ok; no-cache ok.
			} else {
				$wpdb->insert( $this->event_db_table, $event_table_array, $event_format ); // db call ok; no-cache ok.
			}

			if ( $is_exitsing_event ) {
				do_action( 'ife_after_update_event_organizer_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id'     => $inserted_event_id,
				);
			} else {
				do_action( 'ife_after_create_event_organizer_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
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
	 * Get Uid for ai1ec event.
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @return string
	 */
	public function get_ical_uid_for_event( $event_id ) {
		$site_url = wp_parse_url( ai1ec_get_site_url() );
		$format   = 'ai1ec-%d@' . $site_url['host'];
		if ( isset( $site_url['path'] ) ) {
			$format .= $site_url['path'];
		}
		return sprintf( $format, $event_id );
	}

	/**
	 * Remove query string from URL.
	 *
	 * @since 1.0.0
	 * @param string $datetime DateTime.
	 * @param string $local_timezone Local Timezone.
	 * @return string
	 */
	public function convert_datetime_to_local_datetime( $datetime, $local_timezone ) {
		try {
			$datetime2 = new DateTime( date( 'Y-m-d H:i:s', $datetime ), new DateTimeZone( $local_timezone ) );
			$datetime2->setTimezone( new DateTimeZone( 'UTC' ) );
			return $datetime2->format( 'Y-m-d H:i:s' );
		} catch ( Exception $e ) {
			return $datetime;
		}
	}
}
