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
 * Event Manager functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/includes
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_EM {

	/**
	 * $taxonomy Event Taxonomy
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * $tag_taxonomy Event tag Taxonomy
	 *
	 * @var string
	 */
	protected $tag_taxonomy;

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
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'EM_POST_TYPE_EVENT' ) ) {
			$this->event_posttype = EM_POST_TYPE_EVENT;
		} else {
			$this->event_posttype = 'event';
		}
		if ( defined( 'EM_TAXONOMY_CATEGORY' ) ) {
			$this->taxonomy = EM_TAXONOMY_CATEGORY;
		} else {
			$this->taxonomy = 'event-categories';
		}
		if ( defined( 'EM_TAXONOMY_TAG' ) ) {
			$this->tag_taxonomy = EM_TAXONOMY_TAG;
		} else {
			$this->tag_taxonomy = 'event-tags';
		}
		if ( defined( 'EM_POST_TYPE_LOCATION' ) ) {
			$this->venue_posttype = EM_POST_TYPE_LOCATION;
		} else {
			$this->venue_posttype = 'location';
		}

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
	 * Get Venue Posttype
	 *
	 * @return string
	 */
	public function get_venue_posttype() {
		return $this->venue_posttype;
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
	 * Get Tag Taxonomy
	 *
	 * @return string
	 */
	public function get_tag_taxonomy() {
		return $this->tag_taxonomy;
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
		$ticket_uri       = $centralize_array['url'];
		$timezone_name    = isset( $centralize_array['timezone_name'] ) ? $centralize_array['timezone_name'] : 'Africa/Abidjan';
		if ( empty( $timezone_name ) ) {
			$timezone_name = isset( $centralize_array['timezone'] ) ? $centralize_array['timezone'] : 'UTC';
		}

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
				return '';}

			//Event ID
			update_post_meta( $inserted_event_id, 'ife_facebook_event_id', $centralize_array['ID'] );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			$category = isset( $centralize_array['category'] ) ? $centralize_array['category'] : '';
			if ( ! empty( $category ) ) {
				$cat_id = $ife_events->common->ife_check_category_exists( $category, $this->taxonomy );

				if ( $cat_id ) {
					$ife_cats[] = (int) $cat_id;
				}
			}
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

			// Asign event tag.
			$ife_tags = isset( $event_args['event_tags'] ) ? $event_args['event_tags'] : array();
			if ( ! empty( $ife_tags ) ) {
				foreach ( $ife_tags as $ife_tagk => $ife_tagv ) {
					$ife_tags[ $ife_tagk ] = (int) $ife_tagv;
				}
			}
			if ( ! empty( $ife_tags ) ) {
				if ( ! ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable( 'category' ) ) ) {
					wp_set_object_terms( $inserted_event_id, $ife_tags, $this->tag_taxonomy );
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

			$location_id = 0;
			$is_online = isset( $centralize_array['is_online'] )  ? $centralize_array['is_online'] : false;
			if ( $is_exitsing_event ) {
				if ( isset( $centralize_array['location'] ) || $is_online == true ) {
					$location_id = $this->get_location_args( $centralize_array['location'], $inserted_event_id, $is_online );
				}
			} else {
				if ( isset( $centralize_array['location'] ) || $is_online == true ) {
					$location_id = $this->get_location_args( $centralize_array['location'], false, $is_online );
				}
			}

			$event_status = null;
			if ( 'publish' === $inserted_event->post_status ) {
				$event_status = 1;}
			if ( 'pending' === $inserted_event->post_status ) {
				$event_status = 0;}
			
			$timezone      = isset( $centralize_array['timezone'] ) ? sanitize_text_field( $centralize_array['timezone'] ) : '';
			$timezone_name = isset( $centralize_array['timezone_name'] ) ? sanitize_text_field( $centralize_array['timezone_name'] ) : '';	
			$is_all_day    = !empty( $centralize_array['is_all_day'] ) ? $centralize_array['is_all_day'] : 0;

			// Save Meta.
			update_post_meta( $inserted_event_id, '_event_start_time', gmdate( 'H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_event_end_time', gmdate( 'H:i:s', $end_time ) );
			update_post_meta( $inserted_event_id, '_event_all_day', $is_all_day );
			update_post_meta( $inserted_event_id, '_event_start_date', gmdate( 'Y-m-d', $start_time ) );
			update_post_meta( $inserted_event_id, '_event_end_date', gmdate( 'Y-m-d', $end_time ) );
			update_post_meta( $inserted_event_id, '_event_timezone', $timezone_name );
			update_post_meta( $inserted_event_id, '_event_start', gmdate( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_event_end', gmdate( 'Y-m-d H:i:s', $end_time ) );
			update_post_meta( $inserted_event_id, '_event_start_local', gmdate( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_event_end_local', gmdate( 'Y-m-d H:i:s', $end_time ) );

			update_post_meta( $inserted_event_id, '_location_id', $location_id );
			update_post_meta( $inserted_event_id, '_event_status', $event_status );
			update_post_meta( $inserted_event_id, '_event_private', 0 );
			update_post_meta( $inserted_event_id, '_start_ts', str_pad( $start_time, 10, 0, STR_PAD_LEFT ) );
			update_post_meta( $inserted_event_id, '_end_ts', str_pad( $end_time, 10, 0, STR_PAD_LEFT ) );
			update_post_meta( $inserted_event_id, 'ife_event_link', esc_url( $ticket_uri ) );
			update_post_meta( $inserted_event_id, 'ife_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, 'ife_event_timezone', $timezone );
			update_post_meta( $inserted_event_id, 'ife_event_timezone_name', $timezone_name );

			// Custom table Details.
			$event_array = array(
				'post_id'            => $inserted_event_id,
				'event_slug'         => $inserted_event->post_name,
				'event_owner'        => $inserted_event->post_author,
				'event_name'         => $inserted_event->post_title,
				'event_start_time'   => gmdate( 'H:i:s', $start_time ),
				'event_end_time'     => gmdate( 'H:i:s', $end_time ),
				'event_all_day'      => 0,
				'event_start'        => gmdate( 'Y-m-d H:i:s', $start_time ),
				'event_end'          => gmdate( 'Y-m-d H:i:s', $end_time ),
				'event_timezone'     => $timezone_name,
				'event_start_date'   => gmdate( 'Y-m-d', $start_time ),
				'event_end_date'     => gmdate( 'Y-m-d', $end_time ),
				'post_content'       => $inserted_event->post_content,
				'location_id'        => $location_id,
				'event_status'       => $event_status,
				'event_date_created' => $inserted_event->post_date,
			);

			$event_table = ( defined( 'EM_EVENTS_TABLE' ) ? EM_EVENTS_TABLE : $wpdb->prefix . 'em_events' );
			if ( $is_exitsing_event ) {
				$eve_id = get_post_meta( $inserted_event_id, '_event_id', true );
				$where  = array( 'event_id' => $eve_id );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$wpdb->update( $event_table, $event_array, $where ); // db call ok; no-cache ok.
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$is_inserted = $wpdb->insert( $event_table, $event_array ); // db call ok.
				if ( $is_inserted ) {
					update_post_meta( $inserted_event_id, '_event_id', $wpdb->insert_id );
				}
			}

			if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$status_changed = $wpdb->update( $wpdb->posts, array( 'post_status' => sanitize_text_field( $event_args['event_status'] ) ), array( 'ID' => $inserted_event_id ) ); // db call ok; no-cache ok.
			}

			if ( $is_exitsing_event ) {
				do_action( 'ife_after_update_em_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id'     => $inserted_event_id,
				);
			} else {
				do_action( 'ife_after_create_em_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
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
	 * Set Location for event
	 *
	 * @since    1.0.0
	 * @param array $venue location.
	 * @param int   $event_id Event id.
	 * @return array
	 */
	public function get_location_args( $venue, $event_id = false, $is_online = false ) {
		global $wpdb, $ife_events;

		if( $is_online == true ){
			$venue['name'] = 'Online Event';
			$existing_venue = $this->get_venue_by_name( 'Online Event' );
		}else{
			if ( ! isset( $venue['ID'] ) ) {
				return null;
			}
			$existing_venue = $this->get_venue_by_id( $venue['ID'] );
		}

		if ( $existing_venue && is_numeric( $existing_venue ) && $existing_venue > 0 && ! $event_id ) {
			return get_post_meta( $existing_venue, '_location_id', true );
		}

		$locationdata = array(
			'post_title'   => isset( $venue['name'] ) ? $venue['name'] : 'Untitled - Location',
			'post_content' => '',
			'post_type'    => $this->venue_posttype,
			'post_status'  => 'publish',
		);

		if ( $existing_venue && is_numeric( $existing_venue ) && $existing_venue > 0 ) {
			$locationdata['ID'] = $existing_venue;
		}
		$location_id = wp_insert_post( $locationdata, true );

		if ( ! is_wp_error( $location_id ) ) {
			$blog_id = 0;
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
			}
			$location = get_post( $location_id );
			if ( empty( $location ) ) {
				return null;}

			// Location information.
			$country = isset( $venue['country'] ) ? $venue['country'] : '';
			if ( strlen( $country ) > 2 && ! empty( $country ) ) {
				$country = $ife_events->common->ife_get_country_code( $country );
			}
			$address = isset( $venue['full_address'] ) ? $venue['full_address'] : $venue['address_1'];
			$city    = isset( $venue['city'] ) ? $venue['city'] : '';
			$state   = isset( $venue['state'] ) ? $venue['state'] : '';
			$zip     = isset( $venue['zip'] ) ? $venue['zip'] : '';
			$lat     = !empty( $venue['lat'] ) ? round( $venue['lat'], 6 ) : 0.000000;
			$lon     = !empty( $venue['long'] ) ? round( $venue['long'], 6 ) : 0.000000;

			$location_name = isset( $venue['ID'] ) ? $venue['ID'] : $venue['name'];
			
			// Save metas.
			update_post_meta( $location_id, '_blog_id', $blog_id );
			update_post_meta( $location_id, '_location_address', $address );
			update_post_meta( $location_id, '_location_town', $city );
			update_post_meta( $location_id, '_location_state', $state );
			update_post_meta( $location_id, '_location_postcode', $zip );
			update_post_meta( $location_id, '_location_region', '' );
			update_post_meta( $location_id, '_location_country', $country );
			update_post_meta( $location_id, '_location_latitude', $lat );
			update_post_meta( $location_id, '_location_longitude', $lon );
			update_post_meta( $location_id, '_location_status', 1 );
			update_post_meta( $location_id, 'ife_event_venue_id', $location_name );

			global $wpdb;
			$location_array  = array(
				'post_id'            => $location_id,
				'blog_id'            => $blog_id,
				'location_slug'      => $location->post_name,
				'location_name'      => $location->post_title,
				'location_owner'     => $location->post_author,
				'location_address'   => $address,
				'location_town'      => $city,
				'location_state'     => $state,
				'location_postcode'  => $zip,
				'location_region'    => $state,
				'location_country'   => $country,
				'location_latitude'  => $lat,
				'location_longitude' => $lon,
				'post_content'       => $location->post_content,
				'location_status'    => 1,
				'location_private'   => 0,
			);
			$location_format = array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%d', '%d' );
			$where_format    = array( '%d' );

			if ( defined( 'EM_LOCATIONS_TABLE' ) ) {
				$event_location_table = EM_LOCATIONS_TABLE;
			} else {
				$event_location_table = $wpdb->prefix . 'em_locations';
			}

			if ( $event_id && is_numeric( $event_id ) && $event_id > 0 ) {
				$loc_id = get_post_meta( $event_id, '_location_id', true );
				if ( ! empty( $loc_id ) ) {
					$where     = array( 'location_id' => $loc_id );
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$is_update = $wpdb->update( $event_location_table, $location_array, $where, $location_format, $where_format ); // db call ok; no-cache ok.
					if ( false !== $is_update ) {
						return $loc_id;
					}
				} else {
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$is_insert = $wpdb->insert( $event_location_table, $location_array, $location_format ); // db call ok;.
					if ( false !== $is_insert ) {
						$insert_loc_id = $wpdb->insert_id;
						update_post_meta( $location_id, '_location_id', $insert_loc_id );
						return $insert_loc_id;
					}
				}
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$is_insert = $wpdb->insert( $event_location_table, $location_array, $location_format ); // db call ok;.
				if ( false !== $is_insert ) {
					$insert_loc_id = $wpdb->insert_id;
					update_post_meta( $location_id, '_location_id', $insert_loc_id );
					return $insert_loc_id;
				}
			}
		}
		return null;
	}

	/**
	 * Check for Existing TEC Venue
	 *
	 * @since    1.0.0
	 * @param int $venue_id Venue id.
	 * @return int/boolean
	 */
	public function get_venue_by_id( $venue_id ) {
		$existing_venue = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->venue_posttype,
				'meta_key'         => 'ife_event_venue_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
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
	 * Check for Existing EM Venue
	 *
	 * @since    1.7.3
	 * @param int $venue_name Venue id.
	 * @return int/boolean
	 */
	public function get_venue_by_name( $venue_name ) {
		$existing_venue = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->venue_posttype,
				'meta_key'         => 'ife_event_venue_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'       => $venue_name,          // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'suppress_filters' => false,
			)
		);

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return $existing_venue[0]->ID;
		}
		return false;
	}

}
