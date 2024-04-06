<?php
/**
 * Class for Import Events into The Events Calendar
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
 * Import_Facebook_Events_TEC Class
 */
class Import_Facebook_Events_TEC {

	/**
	 * $taxonomy The Events Calendar Event Taxonomy
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * $tag_taxonomy Event tag Taxonomy.
	 *
	 * @var string
	 */
	protected $tag_taxonomy;

	/**
	 * $event_posttype The Events Calendar Event Posttype
	 *
	 * @var string
	 */
	protected $event_posttype;

	/**
	 * $venue_posttype The Events Calendar Venue Posttype
	 *
	 * @var string
	 */
	protected $venue_posttype;

	/**
	 * $oraganizer_posttype The Events Calendar Oraganizer Posttype
	 *
	 * @var string
	 */
	protected $oraganizer_posttype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->taxonomy     = 'tribe_events_cat';
		$this->tag_taxonomy = 'post_tag';
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$this->event_posttype = Tribe__Events__Main::POSTTYPE;
		} else {
			$this->event_posttype = 'tribe_events';
		}

		if ( class_exists( 'Tribe__Events__Organizer' ) ) {
			$this->oraganizer_posttype = Tribe__Events__Organizer::POSTTYPE;
		} else {
			$this->oraganizer_posttype = 'tribe_organizer';
		}

		if ( class_exists( 'Tribe__Events__Venue' ) ) {
			$this->venue_posttype = Tribe__Events__Venue::POSTTYPE;
		} else {
			$this->venue_posttype = 'tribe_venue';
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
	 * Get Organizer Posttype
	 *
	 * @return string
	 */
	public function get_oraganizer_posttype() {
		return $this->oraganizer_posttype;
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
	 * @param  array $centralize_array event array.
	 * @param  array $event_args event arguments.
	 *
	 * @return array
	 */
	public function import_event( $centralize_array, $event_args ) {
		global $ife_events;

		$is_exitsing_event = $ife_events->common->get_event_by_event_id( $this->event_posttype, $centralize_array['ID'] );
        
		if ( $is_exitsing_event && is_numeric( $is_exitsing_event ) && $is_exitsing_event > 0 ) {

			$options       = ife_get_import_options( $centralize_array['origin'] );
			$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
			$skip_trash    = isset( $options['skip_trash'] ) ? $options['skip_trash'] : 'no';
			$post_status   = get_post_status( $is_exitsing_event );
			if ( 'trash' == $post_status && $skip_trash == 'yes' ) {
				return array(
					'status' => 'skip_trash',
					'id'     => $is_exitsing_event,
				);
			}
			if ( 'yes' === $update_events ) {

				if( function_exists( 'tribe_events' ) ){
					$formated_args = $this->format_event_args_for_tec_orm( $centralize_array );
					if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
						$formated_args['status'] = $event_args['event_status'];
					}
				}else{
					$formated_args = $this->format_event_args_for_tec( $centralize_array );
					if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
						$formated_args['post_status'] = $event_args['event_status'];
					}
				}
				$formated_args['post_author'] = isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id();
				if ( ! $ife_events->common->ife_is_updatable( 'status' ) ) {
					if( function_exists( 'tribe_events' ) ){
						$formated_args['status'] = get_post_status( $is_exitsing_event );
					} else {
						$formated_args['post_status'] = get_post_status( $is_exitsing_event );
					}
				}

				return $this->update_event( $is_exitsing_event, $centralize_array, $formated_args, $event_args );
			} else {
				return array(
					'status' => 'skipped',
					'id'     => $is_exitsing_event,
				);
			}
		} else {

			if( function_exists( 'tribe_events' ) ){
				$formated_args = $this->format_event_args_for_tec_orm( $centralize_array );
				if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
					$formated_args['status'] = $event_args['event_status'];
				}
			}else{
				$formated_args = $this->format_event_args_for_tec( $centralize_array );
				if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
					$formated_args['post_status'] = $event_args['event_status'];
				}
			}
			$formated_args['post_author'] = isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id();


			if ( ! $ife_events->common->ife_is_updatable( 'status' ) ) {
				if( function_exists( 'tribe_events' ) ){
					$formated_args['status'] = get_post_status( $is_exitsing_event );
				} else {
					$formated_args['post_status'] = get_post_status( $is_exitsing_event );
				}
			}

			return $this->create_event( $centralize_array, $formated_args, $event_args );
		}

	}

	/**
	 * Create New TEC event.
	 *
	 * @since    1.0.0
	 * @param array $centralize_array event array.
	 * @param array $formated_args Formated arguments for eventbrite event.
	 * @param array $event_args event arguments.
	 * @return array
	 */
	public function create_event( $centralize_array = array(), $formated_args = array(), $event_args = array() ) {
		// Create event using TEC advanced functions.
		global $ife_events;
		if( function_exists( 'tribe_events' ) ){
			$new_event_id = tribe_events()->set_args( $formated_args )->create()->ID;
		}else{
			if( function_exists( 'tribe_create_event' ) ){
				$new_event_id = tribe_create_event( $formated_args );
			}
		}
		if ( $new_event_id ) {
			$timezone      = isset( $centralize_array['timezone'] ) ? sanitize_text_field( $centralize_array['timezone'] ) : 'UTC';
			$timezone_name = isset( $centralize_array['timezone_name'] ) ? sanitize_text_field( $centralize_array['timezone_name'] ) : 'Africa/Abidjan';

			update_post_meta( $new_event_id, '_EventTimezone', $timezone_name );
			update_post_meta( $new_event_id, 'ife_facebook_event_id', $centralize_array['ID'] );
			update_post_meta( $new_event_id, 'ife_event_origin', $event_args['import_origin'] );
			update_post_meta( $new_event_id, 'ife_event_link', esc_url( $centralize_array['url'] ) );
			update_post_meta( $new_event_id, 'ife_event_timezone', $timezone );
			update_post_meta( $new_event_id, 'ife_event_timezone_name', $timezone_name );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				wp_set_object_terms( $new_event_id, $ife_cats, $this->taxonomy );
			}

			// Asign event tag.
			$ife_tags = isset( $event_args['event_tags'] ) ? $event_args['event_tags'] : array();
			if ( ! empty( $ife_tags ) ) {
				foreach ( $ife_tags as $ife_tagk => $ife_tagv ) {
					$ife_tags[ $ife_tagk ] = (int) $ife_tagv;
				}
			}
			if ( ! empty( $ife_tags ) ) {
				wp_set_object_terms( $new_event_id, $ife_tags, $this->tag_taxonomy );
			}

			$event_featured_image = $centralize_array['image_url'];
			if ( ! empty( $event_featured_image ) ) {
				$ife_events->common->setup_featured_image_to_event( $new_event_id, $event_featured_image );
			}

			do_action( 'ife_after_create_tec_' . $centralize_array['origin'] . '_event', $new_event_id, $formated_args, $centralize_array );
			return array(
				'status' => 'created',
				'id'     => $new_event_id,
			);

		} else {
			$ife_errors[] = __( 'Something went wrong, please try again.', 'import-facebook-events' );
			return;
		}
	}


	/**
	 * Update eventbrite event.
	 *
	 * @since 1.0.0
	 * @param int   $event_id Event id.
	 * @param array $centralize_array Eventbrite event.
	 * @param array $formated_args Formated arguments for eventbrite event.
	 * @param array $event_args event arguments.
	 * @return array
	 */
	public function update_event( $event_id, $centralize_array, $formated_args = array(), $event_args = array() ) {
		// Update event using TEC advanced functions.
		global $ife_events;

		if( function_exists( 'tribe_events' ) ){
			$update_event_id = tribe_events()->where( 'id', $event_id )->set_args( $formated_args )->save();
			$update_event_id = $event_id;
			$tec_event = array( 'ID' => $event_id, 'post_status' => $formated_args['status'] );
			wp_update_post( $tec_event );
		}else{
			if( function_exists( 'tribe_update_event' ) ){
				$update_event_id = tribe_update_event( $event_id, $formated_args );
			}
		}

		if ( $update_event_id ) {

			$start_time    = $centralize_array['starttime_local'];
			$end_time      = $centralize_array['endtime_local'];
			$timezone      = isset( $centralize_array['timezone'] ) ? sanitize_text_field( $centralize_array['timezone'] ) : 'UTC';
			$timezone_name = isset( $centralize_array['timezone_name'] ) ? $centralize_array['timezone_name'] : 'Africa/Abidjan';

			update_post_meta( $update_event_id, '_EventStartDate',  date( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $update_event_id, '_EventEndDate', date( 'Y-m-d H:i:s', $end_time ) );
			update_post_meta( $update_event_id, '_EventTimezone', $timezone_name );

			update_post_meta( $update_event_id, 'ife_facebook_event_id', $centralize_array['ID'] );
			update_post_meta( $update_event_id, 'ife_event_origin', $event_args['import_origin'] );
			update_post_meta( $update_event_id, 'ife_event_link', esc_url( $centralize_array['url'] ) );
			update_post_meta( $update_event_id, 'ife_event_timezone', $timezone );
			update_post_meta( $update_event_id, 'ife_event_timezone_name', $timezone_name );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? (array) $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				if ( $ife_events->common->ife_is_updatable( 'category' ) ) {
					wp_set_object_terms( $update_event_id, $ife_cats, $this->taxonomy );
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
				if ( $ife_events->common->ife_is_updatable( 'category' ) ) {
					wp_set_object_terms( $update_event_id, $ife_tags, $this->tag_taxonomy );
				}
			}

			$event_featured_image = $centralize_array['image_url'];
			if ( ! empty( $event_featured_image ) ) {
				$ife_events->common->setup_featured_image_to_event( $update_event_id, $event_featured_image );
			} else {
				delete_post_thumbnail( $update_event_id );
			}

			do_action( 'ife_after_update_tec_' . $centralize_array['origin'] . '_event', $update_event_id, $formated_args, $centralize_array );
			return array(
				'status' => 'updated',
				'id'     => $update_event_id,
			);
		} else {
			$ife_errors[] = __( 'Something went wrong, please try again.', 'import-facebook-events' );
			return;
		}
	}


	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $centralize_array Facebook event.
	 * @return array
	 */
	public function format_event_args_for_tec_orm( $centralize_array ) {

		if ( empty( $centralize_array ) ) {
			return;
		}
		$start_time = $centralize_array['starttime_local'];
		$end_time   = $centralize_array['endtime_local'];
		$timezone_name = isset( $centralize_array['timezone_name'] ) ? $centralize_array['timezone_name'] : 'Africa/Abidjan'; 
		$event_args = array(
			'title'             => $centralize_array['name'],
			'post_content'      => $centralize_array['description'],
			'status'            => 'pending',
			'url'               => $centralize_array['url'],
			'timezone'          => $timezone_name,
			'start_date'        => date( 'Y-m-d H:i:s', $start_time ),
			'end_date'          => date( 'Y-m-d H:i:s', $end_time ),
		);

		if( isset( $centralize_array['is_all_day'] ) && true === $centralize_array['is_all_day'] ){
			$event_args['_EventAllDay'] = 'yes';
		}

		if ( array_key_exists( 'organizer', $centralize_array ) ) {
			$organizer               = $this->get_organizer_args( $centralize_array['organizer'] );      
			$event_args['organizer'] = $organizer['OrganizerID'];
		}

		if( isset( $centralize_array['is_online'] ) && $centralize_array['is_online'] == true ){
			$centralize_array['location']['name'] = 'Online Event';
		}
		if ( array_key_exists( 'location', $centralize_array ) ) {
			$venue               = $this->get_venue_args( $centralize_array['location'] );
			$event_args['venue'] = $venue['VenueID'];
		}
		return $event_args;
	}

	/**
	 * Format event arguments as per TEC less than V4.9  
	 *
	 * @since    1.6.20
	 * @param array $centralize_array Facebook event.
	 * @return array
	 */
	public function format_event_args_for_tec( $centralize_array ) {

		if ( empty( $centralize_array ) ) {
			return;
		}
		$start_time = $centralize_array['starttime_local'];
		$end_time   = $centralize_array['endtime_local'];
		$event_args = array(
			'post_type'          => $this->event_posttype,
			'post_title'         => $centralize_array['name'],
			'post_status'        => 'pending',
			'post_content'       => $centralize_array['description'],
			'EventStartDate'     => date( 'Y-m-d', $start_time ),
			'EventStartHour'     => date( 'h', $start_time ),
			'EventStartMinute'   => date( 'i', $start_time ),
			'EventStartMeridian' => date( 'a', $start_time ),
			'EventEndDate'       => date( 'Y-m-d', $end_time ),
			'EventEndHour'       => date( 'h', $end_time ),
			'EventEndMinute'     => date( 'i', $end_time ),
			'EventEndMeridian'   => date( 'a', $end_time ),
			'EventStartDateUTC'  => ! empty( $centralize_array['startime_utc'] ) ? date( 'Y-m-d H:i:s', $centralize_array['startime_utc'] ) : '',
			'EventEndDateUTC'    => ! empty( $centralize_array['endtime_utc'] ) ? date( 'Y-m-d H:i:s', $centralize_array['endtime_utc'] ) : '',
			'EventURL'           => $centralize_array['url'],
			'EventShowMap'       => 1,
			'EventShowMapLink'   => 1,
		);

		if( isset( $centralize_array['is_all_day'] ) && true === $centralize_array['is_all_day'] ){
			$event_args['_EventAllDay']      = 'yes';
		}

		if ( array_key_exists( 'organizer', $centralize_array ) ) {
			$event_args['organizer'] = $this->get_organizer_args( $centralize_array['organizer'] );
		}

		if ( array_key_exists( 'location', $centralize_array ) ) {
			$event_args['venue'] = $this->get_venue_args( $centralize_array['location'] );
		}
		return $event_args;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since    1.0.0
	 * @param array $centralize_org_array Location array.
	 * @return array
	 */
	public function get_organizer_args( $centralize_org_array ) {

		if ( ! isset( $centralize_org_array['name'] ) ) {
			return null;
		}
		$organizer_name = str_replace( '\\', '', $centralize_org_array['name'] );
		$existing_organizer = $this->get_organizer_by_id( $organizer_name );
		if ( $existing_organizer && is_numeric( $existing_organizer ) && $existing_organizer > 0 ) {
			return array(
				'OrganizerID' => $existing_organizer
			);
		}

		$create_organizer = tribe_create_organizer(
			array(
				'Organizer' => isset( $centralize_org_array['name'] ) ? $centralize_org_array['name'] : '',
				'Phone'     => isset( $centralize_org_array['phone'] ) ? $centralize_org_array['phone'] : '',
				'Email'     => isset( $centralize_org_array['email'] ) ? $centralize_org_array['email'] : '',
				'Website'   => isset( $centralize_org_array['url'] ) ? $centralize_org_array['url'] : '',
			)
		);

		if ( $create_organizer ) {
			update_post_meta( $create_organizer, 'ife_event_organizer_name', $centralize_org_array['name'] );
			update_post_meta( $create_organizer, 'ife_event_organizer_id', $centralize_org_array['ID'] );
			return array(
				'OrganizerID' => $create_organizer	
			);
		}
		return null;
	}

	/**
	 * Get venue args for event
	 *
	 * @since    1.0.0
	 * @param array $venue venue array.
	 * @return array
	 */
	public function get_venue_args( $venue ) {
		global $ife_events;
		$venue_id = !empty( $venue['ID'] ) ? $venue['ID'] : '';
		if( !empty( $venue['ID'] ) ){
			$existing_venue = $this->get_venue_by_id( $venue_id );
		}
		if( empty( $existing_venue ) ){
			$existing_venue = $this->get_venue_by_name( $venue['name'] );
		}
		if ( $existing_venue && is_numeric( $existing_venue ) && $existing_venue > 0 ) {
			return array(
				'VenueID' => $existing_venue
			);
		}

		$country = isset( $venue['country'] ) ? $venue['country'] : '';
		if ( strlen( $country ) > 2 && ! empty( $country ) ) {
			$country = $ife_events->common->ife_get_country_code( $country );
		}
		$address_1    = isset( $venue['address_1'] ) ? $venue['address_1'] : '';
		$create_venue = tribe_create_venue(
			array(
				'Venue'       => isset( $venue['name'] ) ? $venue['name'] : '',
				'Address'     => isset( $venue['full_address'] ) ? $venue['full_address'] : $address_1,
				'City'        => isset( $venue['city'] ) ? $venue['city'] : '',
				'State'       => isset( $venue['state'] ) ? $venue['state'] : '',
				'Country'     => $country,
				'Zip'         => isset( $venue['zip'] ) ? $venue['zip'] : '',
				'Phone'       => isset( $venue['phone'] ) ? $venue['phone'] : '',
				'ShowMap'     => true,
				'ShowMapLink' => true,
			)
		);

		if ( $create_venue ) {
			update_post_meta( $create_venue, 'ife_event_venue_name', $venue['name'] );
			update_post_meta( $create_venue, 'ife_event_venue_id', $venue_id );
			
			return array(
				'VenueID' => $create_venue
			);
		}
		return false;
	}

	/**
	 * Check for Existing TEC Organizer
	 *
	 * @since    1.0.0
	 * @param int $organizer_id Organizer id.
	 * @return int/boolean
	 */
	public function get_organizer_by_id( $organizer_name ) {
		$existing_organizer = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->oraganizer_posttype,
				'meta_key'         => 'ife_event_organizer_name', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
				'meta_value'       => $organizer_name, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Ignore.
				'suppress_filters' => false,
			)
		);

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return $existing_organizer[0]->ID;
		}
		return false;
	}

	/**
	 * Check for Existing TEC Venue
	 *
	 * @since    1.0.0
	 * @param int $venue_id Venue id.
	 * @return int/boolean
	 */
	public function get_venue_by_id( $venue_id ) {
		$existing_organizer = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->venue_posttype,
				'meta_key'         => 'ife_event_venue_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
				'meta_value'       => $venue_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Ignore.
				'suppress_filters' => false,
			)
		);

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return $existing_organizer[0]->ID;
		}
		return false;
	}

	/**
	 * Check for Existing TEC Venue Name
	 *
	 * @since    1.0.0
	 * @param int $venue_name Venue Name.
	 * @return int/boolean
	 */
	public function get_venue_by_name( $venue_name ) {
		$existing_organizer = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->venue_posttype,
				'meta_key'         => 'ife_event_venue_name', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
				'meta_value'       => $venue_name, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Ignore.
				'suppress_filters' => false,
			)
		);

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return $existing_organizer[0]->ID;
		}
		return false;
	}

}
