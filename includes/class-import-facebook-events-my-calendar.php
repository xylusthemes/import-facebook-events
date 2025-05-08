<?php
/**
 * Class for Import Events into Events Manager
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
 * Class for My Calendar functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/includes
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_My_Calendar {

	/**
	 * The Events Calendar Event Taxonomy
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * The Events Calendar Event Posttype
	 *
	 * @var string
	 */
	protected $event_posttype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->event_posttype = 'mc-events';
		$this->taxonomy       = 'mc-event-category';
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
	 * Get Event Taxonomy
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
		$event_uri        = $centralize_array['url'];

		$mc_eventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
            'post_status'  => 'pending',
            'post_author'  => isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id()
		);
		if ( $is_exitsing_event ) {
			$mc_eventdata['ID'] = $is_exitsing_event;
		}
		if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
			$mc_eventdata['post_status'] = $event_args['event_status'];
		}
		$event_approved = '0';
		if( $mc_eventdata['post_status'] == 'publish' ){
			$event_approved = '1';
		}
		if ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable('status') ) {
			$mc_eventdata['post_status'] = get_post_status( $is_exitsing_event );
			$event_args['event_status'] = get_post_status( $is_exitsing_event );
		}
		$inserted_event_id = wp_insert_post( $mc_eventdata, true );

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
			$timezone_name = isset( $centralize_array['timezone_name'] ) ? sanitize_text_field( $centralize_array['timezone_name'] ) : '';

			update_post_meta( $inserted_event_id, 'ife_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, 'ife_event_link', $centralize_array['url'] );
			update_post_meta( $inserted_event_id, 'ife_event_timezone', $timezone );
			update_post_meta( $inserted_event_id, 'ife_event_timezone_name', $timezone_name );

			// Setup Variables for insert into table.
			$begin   = gmdate( 'Y-m-d', $start_time );
			$end     = gmdate( 'Y-m-d', $end_time );
			$time    = gmdate( 'H:i:s', $start_time );
			$endtime = gmdate( 'H:i:s', $end_time );

			$event_author = $host = isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id();
			$event_category = 1;
			if ( ! empty( $ife_cats ) ) {
				$event_cat                    = $ife_cats[0];
				$my_calendar_categories_table = my_calendar_categories_table();
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$temp_event_cat = $wpdb->get_var( $wpdb->prepare( "SELECT `category_id` FROM {$my_calendar_categories_table} WHERE `category_term` = %d LIMIT 1", absint( $event_cat ) ) ); // cache ok, db call ok.
				if ( $temp_event_cat > 0 && is_numeric( $temp_event_cat ) && ! empty( $temp_event_cat ) ) {
					$event_category = $temp_event_cat;
				}
			}

			// Location Args for.
			$is_online = isset( $centralize_array['is_online'] )  ? $centralize_array['is_online'] : false;
			if( $is_online == true ){
				$centralize_array['location']['name'] = 'Online Event';
			}
			$venue = isset( $centralize_array['location'] ) ? $centralize_array['location'] : array();

			$event_label     = '';
			$event_street    = '';
			$event_street2   = '';
			$address         = '';
			$event_city      = '';
			$event_state     = '';
			$event_postcode  = '';
			$event_region    = '';
			$event_latitude  = '';
			$event_longitude = '';
			$event_country   = '';
			$event_url       = '';
			$event_phone     = '';
			$event_phone2    = '';
			$event_zoom      = '';
			$location_id     = 0;

			if ( ! empty( $venue ) ) {
				$event_label  = isset( $venue['name'] ) ? $venue['name'] : '';
				$event_street = isset( $venue['full_address'] ) ? $venue['full_address'] : '';
				if ( empty( $event_street ) && isset( $venue['address_1'] ) ) {
					$event_street = $venue['address_1'];
				}
				$event_street2   = isset( $venue['address_2'] ) ? $venue['address_2'] : '';
				$address         = isset( $venue['address_2'] ) ? $venue['address_2'] : '';
				$event_city      = isset( $venue['city'] ) ? $venue['city'] : '';
				$event_state     = isset( $venue['state'] ) ? $venue['state'] : '';
				$event_postcode  = isset( $venue['zip'] ) ? $venue['zip'] : '';
				$event_region    = isset( $venue['state'] ) ? $venue['state'] : '';
				$event_latitude  = isset( $venue['lat'] ) ? $venue['lat'] : 0.000000;
				$event_longitude = isset( $venue['long'] ) ? $venue['long'] : 0.000000;
				$event_country   = isset( $venue['country'] ) ? $venue['country'] : '';
				$event_url       = isset( $venue['url'] ) ? $venue['url'] : '';
				$event_phone     = '';
				$event_phone2    = '';
				$event_zoom      = 16;

				$location_data = array(
					'location_label'     => $event_label,
					'location_street'    => $event_street,
					'location_street2'   => $event_street2,
					'location_city'      => $event_city,
					'location_state'     => $event_state,
					'location_postcode'  => $event_postcode,
					'location_region'    => $event_region,
					'location_country'   => $event_country,
					'location_url'       => $event_url,
					'location_longitude' => $event_longitude,
					'location_latitude'  => $event_latitude,
					'location_zoom'      => $event_zoom,
					'location_phone'     => $event_phone,
					'location_phone2'    => $event_phone2,
					'location_access'    => '',
				);
				$add_loc       = array_map( 'mc_kses_post', $location_data );

				$loc_formats = array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%f',
					'%f',
					'%d',
					'%s',
					'%s',
					'%s',
				);

				$location_table = my_calendar_locations_table();
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$location_id = $wpdb->get_var( $wpdb->prepare( "SELECT `location_id` FROM {$location_table} WHERE `location_label` = %s", esc_sql( $event_label ) ) ); // cache ok, db call ok.
				if ( $location_id > 0 && is_numeric( $location_id ) && ! empty( $location_id ) ) {

					$where            = array( 'location_id' => (int) $location_id );
					$loc_where_format = array( '%d' );
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->update( my_calendar_locations_table(), $location_data, $where, $loc_formats, $loc_where_format ); // db call ok; no-cache ok.
				} else {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->insert( my_calendar_locations_table(), $location_data, $loc_formats ); // db call ok;.
					$location_id = $wpdb->insert_id;
				}
			}

			$event_data = array(
				// strings.
				'event_begin'        => $begin,
				'event_end'          => $end,
				'event_title'        => $inserted_event->post_title,
				'event_desc'         => $inserted_event->post_content,
				'event_short'        => '',
				'event_time'         => $time,
				'event_endtime'      => $endtime,
				'event_link'         => $event_uri,
				'event_label'        => $event_label,
				'event_street'       => $event_street,
				'event_street2'      => $event_street2,
				'event_city'         => $event_city,
				'event_state'        => $event_state,
				'event_postcode'     => $event_postcode,
				'event_region'       => $event_region,
				'event_country'      => $event_country,
				'event_url'          => $event_url,
				'event_recur'        => 'S1',
				'event_image'        => '',
				'event_phone'        => $event_phone,
				'event_phone2'       => $event_phone2,
				'event_access'       => '',
				'event_tickets'      => '',
				'event_registration' => '',                 // integers.
				'event_post'         => $inserted_event_id,
				'event_location'     => isset( $location_id ) ? $location_id : 0,
				'event_repeats'      => 0,
				'event_author'       => $event_author,
				'event_category'     => $event_category,
				'event_link_expires' => 0,
				'event_zoom'         => $event_zoom,
				'event_approved'     => $event_approved,
				'event_host'         => $host,
				'event_flagged'      => 0,
				'event_fifth_week'   => 1,
				'event_holiday'      => 0,
				'event_span'         => 0,
				'event_hide_end'     => 0,
				// floats.
				'event_longitude'    => $event_longitude,
				'event_latitude'     => $event_latitude,
			);

			$event_formats = array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%d',
				'%f',
				'%f',
			);

			$my_calendar_table = my_calendar_table();
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$db_event_id = $wpdb->get_var( $wpdb->prepare( "SELECT `event_id` FROM {$my_calendar_table} WHERE `event_post`= %d LIMIT 1", $inserted_event_id ) );

			if ( $db_event_id > 0 && is_numeric( $db_event_id ) && ! empty( $db_event_id ) ) {

				if ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable('status') ) {
					unset( $event_data['event_approved'] );
				}
				if ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable('category') ) {
					unset( $event_data['event_category'] );
				}

				$event_where = array( 'event_id' => absint( $db_event_id ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$wpdb->update( my_calendar_table(), $event_data, $event_where, $event_formats ); // db call ok; no-cache ok.
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$wpdb->insert( my_calendar_table(), $event_data, $event_formats ); // db call ok;.
				$db_event_id = $wpdb->insert_id;
			}

			if ( isset( $db_event_id ) && ! empty( $db_event_id ) ) {

				$occur_data = array(
					'occur_event_id' => $db_event_id,
					'occur_begin'    => gmdate( 'Y-m-d H:i:s', $start_time ),
					'occur_end'      => gmdate( 'Y-m-d H:i:s', $end_time ),
					'occur_group_id' => 0,
				);

				$my_calendar_event_table = my_calendar_event_table();
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$occur_id     = $wpdb->get_var( $wpdb->prepare( "SELECT `occur_id` FROM {$my_calendar_event_table} WHERE `occur_event_id`= %d", absint( $db_event_id ) ) ); // cache ok, db call ok.
				$occur_format = array( '%d', '%s', '%s', '%d' );
				if ( $occur_id > 0 && is_numeric( $occur_id ) && ! empty( $occur_id ) ) {

					$occur_where = array( 'occur_id' => absint( $occur_id ) );
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->update( my_calendar_event_table(), $occur_data, $occur_where, $occur_format ); // db call ok; no-cache ok.
				} else {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$wpdb->insert( my_calendar_event_table(), $occur_data, $occur_format ); // db call ok;.
					$occur_id = $wpdb->insert_id;
				}
			}

			if ( isset( $db_event_id ) && ! empty( $db_event_id ) ) {
				update_post_meta( $inserted_event_id, '_mc_event_shortcode', "[my_calendar_event event='" . $db_event_id . "' template='details' list='']" );
				update_post_meta( $inserted_event_id, '_mc_event_id', $db_event_id );
			}
			update_post_meta( $inserted_event_id, '_mc_event_access', array( 'notes' => '' ) );
			update_post_meta( $inserted_event_id, '_mc_event_desc', $inserted_event->post_content );
			update_post_meta( $inserted_event_id, '_mc_event_image', '' );
			if ( isset( $location_id ) && ! empty( $location_id ) ) {
				update_post_meta( $inserted_event_id, '_mc_event_location', $location_id );
			}

			if ( $is_exitsing_event ) {
				do_action( 'ife_after_update_my_calendar_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id'     => $inserted_event_id,
				);
			} else {
				do_action( 'ife_after_create_my_calendar_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
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
}
