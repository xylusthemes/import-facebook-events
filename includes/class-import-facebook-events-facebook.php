<?php
/**
 * Class for Facebook Imports.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Facebook_Events_Facebook {

	/*
	*	Facebook app ID
	*/
	public $fb_app_id;

	/*
	*	Facebook app Secret
	*/
	public $fb_app_secret;

	/*
	*	Facebook Graph URL
	*/
	public $fb_graph_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$options = get_option( IFE_OPTIONS );
		$this->fb_app_id = isset( $options['facebook_app_id'] ) ? $options['facebook_app_id'] : '';
		$this->fb_app_secret = isset( $options['facebook_app_secret'] ) ? $options['facebook_app_secret'] : '';
		$this->fb_graph_url = 'https://graph.facebook.com/v2.5/';
	}

	/**
	 * import facebook events by oraganization or facebook page.
	 *
	 * @since  1.0.0
	 * @param  array $eventdata  import event data.
	 * @return array/boolean
	 */
	public function import_events( $event_data = array() ){

		global $fb_errors;
		set_time_limit(0);
		$imported_events = array();
		$facebook_event_ids = array();

		if( $this->fb_app_id == '' || $this->fb_app_secret == '' ){
			$fb_errors[] = __( 'Please insert Facebook app ID and app Secret.', 'import-facebook-events');
			return;
		}			

		$import_by = isset( $event_data['import_by'] ) ? esc_attr( $event_data['import_by'] ) : '';

		if( 'facebook_organization' == $import_by ){
			$page_username = isset( $event_data['page_username'] ) ? $event_data['page_username'] : '';
			if( $page_username == '' || $this->fb_app_id == '' ){
				return false;
			}
			$facebook_event_ids = $this->get_events_for_facebook_page( $page_username );

		} elseif ( 'facebook_event_id' == $import_by ){
				
			$facebook_event_ids = isset( $event_data['event_ids'] ) ? $event_data['event_ids'] : array();
		}		
		
		if( !empty( $facebook_event_ids ) ){
			foreach ($facebook_event_ids as $facebook_event_id ) {
				if( $facebook_event_id != '' ){
					$imported_events[] = $this->import_event_by_event_id( $facebook_event_id, $event_data );
				}		
			}
		}
		return $imported_events;
	}

	/**
	 * import facebook event by ID.
	 *
	 * @since  1.0.0
	 * @param  array $eventdata  import event data.
	 * @return int/boolean
	 */
	public function import_event_by_event_id( $facebook_event_id, $event_data = array() ){

		global $fb_errors;
		$options = get_option( IFE_OPTIONS );
		$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
		
		if( $facebook_event_id == '' || $this->fb_app_id == '' || $this->fb_app_secret == '' ){
			if( $this->fb_app_id == '' || $this->fb_app_secret == '' ){
				$fb_errors[] = __( 'Please insert Facebook app ID and app Secret.', 'import-facebook-events');
				return;
			}
			return false;
		}

		if( 'facebook_tec' == $event_data['import_origin'] ){
			$is_exitsing_event = $this->get_event_by_event_id( $facebook_event_id );
			if ( $is_exitsing_event && $update_events == 'no' ) {
				$fb_errors[] = __( 'Facebook event is already exists.', 'import-facebook-events');
				return;
			}
		}

		$facebook_event_object = $this->get_facebook_event_by_event_id( $facebook_event_id );
		if( 'facebook_tec' == $event_data['import_origin'] ){
			return $this->save_tec_facebook_event( $facebook_event_object, $event_data );

		}elseif( 'facebook_em' == $event_data['import_origin'] ){

			return $this->save_em_facebook_event( $facebook_event_object, $event_data );

		}
	}

	/**
	 * Save (Create or update) facebook imported to The Event Calendar Events.
	 *
	 * @since  1.0.0
	 * @param array  $facebook_event_object Event object get from facebook.com.
	 * @return void
	 */
	public function save_tec_facebook_event( $facebook_event_object = array(), $event_args = array() ) {

		if ( ! empty( $facebook_event_object ) && isset( $facebook_event_object->id ) ) {

			$is_exitsing_event = $this->get_event_by_event_id( $facebook_event_object->id );
			$formated_args = $this->format_event_args_for_tec( $facebook_event_object );

			if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
				$formated_args['post_status'] = $event_args['event_status'];
			}

			if ( $is_exitsing_event ) {
				// Update event using TEC advanced functions if already exits.
				$options = get_option( IFE_OPTIONS );
				$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
				if ( 'yes' == $update_events ) {
					return $this->update_tec_facebook_event( $is_exitsing_event, $facebook_event_object, $formated_args, $event_args );
				}
			} else {
				return $this->create_tec_facebook_event( $facebook_event_object, $formated_args, $event_args );
			}
		}
	}

	/**
	 * Create New Facebook event.
	 *
	 * @since    1.0.0
	 * @param array $facebook_event Facebook event.
	 * @param array $formated_args Formated arguments for facebook event.
	 * @param array $event_args
	 * @return int
	 */
	public function create_tec_facebook_event( $facebook_event = array(), $formated_args = array(), $event_args = array() ) {
		// Create event using TEC advanced functions.
		$new_event_id = tribe_create_event( $formated_args );
		if ( $new_event_id ) {
			update_post_meta( $new_event_id, 'ife_facebook_event_id',  $facebook_event->id );
			update_post_meta( $new_event_id, 'ife_facebook_response_raw_data', wp_json_encode( $facebook_event ) );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				wp_set_object_terms( $new_event_id, $ife_cats, IFE_TEC_TAXONOMY );
			}

			$cover_image = isset( $facebook_event->cover->source ) ? ife_clean_url( esc_url( $facebook_event->cover->source ) ) : '';

			if( $cover_image != '' ){
				$this->setup_featured_image_to_event( $new_event_id, $cover_image );
			}

			do_action( 'ife_after_create_tec_facebook_event', $new_event_id, $formated_args, $facebook_event );
			return $new_event_id;

		}else{
			$fb_errors[] = __( 'Something went wrong, please try again.', 'import-facebook-events' );
			return;
		}
	}


	/**
	 * Update facebook event.
	 *
	 * @since 1.0.0
	 * @param int   $event_id existing facebook event.
	 * @param array $facebook_event facebook event.
	 * @param array $formated_args Formated arguments for facebook event.
	 * @param array $event_args User submited data at a time of schedule event
	 * @return int   $post_id Post id.
	 */
	public function update_tec_facebook_event( $event_id, $facebook_event, $formated_args = array(), $event_args = array() ) {
		// Update event using TEC advanced functions.
		$update_event_id =  tribe_update_event( $event_id, $formated_args );
		if ( $update_event_id ) {
			update_post_meta( $update_event_id, 'ife_facebook_event_id',  $facebook_event->id );
			update_post_meta( $update_event_id, 'ife_facebook_response_raw_data', wp_json_encode( $facebook_event ) );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? (array) $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				wp_set_object_terms( $update_event_id, $ife_cats, IFE_TEC_TAXONOMY );
			}

			$cover_image = isset( $facebook_event->cover->source ) ? ife_clean_url( esc_url( $facebook_event->cover->source ) ) : '';

			if( $cover_image != '' ){
				$this->setup_featured_image_to_event( $update_event_id, $cover_image );
			}

			do_action( 'ife_after_update_tec_facebook_event', $update_event_id, $formated_args, $facebook_event );
			return $update_event_id;

		}else{

			$fb_errors[] = __( 'Something went wrong, please try again.', 'import-facebook-events' );
			return;
		}
	}


	/**
	 * Save (Create or update) facebook imported to The Events Manager.
	 *
	 * @since  1.0.0
	 * @param array  $facebook_event_object Event object get from facebook.com.
	 * @return void
	 */
	public function save_em_facebook_event( $facebook_event_object = array(), $event_args = array() ) {

		if ( ! empty( $facebook_event_object ) && isset( $facebook_event_object->id ) ) {

			global $wpdb;
			$is_exitsing_event = $this->get_em_event_by_event_id( $facebook_event_object->id );

			if ( $is_exitsing_event ) {
				// Update event or not?
				$options = get_option( IFE_OPTIONS );
				$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
				if ( 'yes' != $update_events ) {
					return;
				}
			}

			$facebook_id = $facebook_event_object->id;
			$post_title = isset( $facebook_event_object->name ) ? $facebook_event_object->name : '';
			$post_description = isset( $facebook_event_object->description ) ? $facebook_event_object->description : '';
		
			$start_time = isset( $facebook_event_object->start_time ) ? strtotime( ife_convert_datetime_to_db_datetime( $facebook_event_object->start_time ) ) : date( 'Y-m-d H:i:s');
			$end_time = isset( $facebook_event_object->end_time ) ? strtotime( ife_convert_datetime_to_db_datetime( $facebook_event_object->end_time ) ) : $start_time;

			$ticket_uri = isset( $facebook_event_object->ticket_uri ) ? esc_url( $facebook_event_object->ticket_uri ) : '';
			$timezone = $this->get_utc_offset( $facebook_event_object->start_time );
			
			$emeventdata = array(
				'post_title'  => $post_title,
				'post_content' => $post_description,
				'post_type'   => IFE_EM_POSTTYPE,
				'post_status' => 'pending',
			);
			if ( $is_exitsing_event ) {
				$emeventdata['ID'] = $is_exitsing_event;
			}
			if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
				$emeventdata['post_status'] = $event_args['event_status'];
			}

			$inserted_event_id = wp_insert_post( $emeventdata, true );

			if ( ! is_wp_error( $inserted_event_id ) ) {
				$inserted_event = get_post( $inserted_event_id );
				if ( empty( $inserted_event ) ) { return '';}

				// Asign event category.
				$ife_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
				if ( ! empty( $ife_cats ) ) {
					foreach ( $ife_cats as $ife_catk => $ife_catv ) {
						$ife_cats[ $ife_catk ] = (int) $ife_catv;
					}
				}
				if ( ! empty( $ife_cats ) ) {
					wp_set_object_terms( $inserted_event_id, $ife_cats, IFE_EM_TAXONOMY );
				}

				// Assign Featured images
				$cover_image = isset( $facebook_event->cover->source ) ? ife_clean_url( esc_url( $facebook_event->cover->source ) ) : '';
				if( $cover_image != '' ){
					$this->setup_featured_image_to_event( $inserted_event_id, $cover_image );
				}

				if ( $is_exitsing_event ) {
					$location_id = $this->em_get_location_args( $facebook_event_object, $inserted_event_id );
				}else{
					$location_id = $this->em_get_location_args( $facebook_event_object, false );
				}

				$event_status = null;
				if ( $inserted_event->post_status == 'publish' ) { $event_status = 1;}
				if ( $inserted_event->post_status == 'pending' ) { $event_status = 0;}
				// Save Meta.
				update_post_meta( $inserted_event_id, '_event_start_time', date( 'H:i:s', $start_time ) );
				update_post_meta( $inserted_event_id, '_event_end_time', date( 'H:i:s', $end_time ) );
				update_post_meta( $inserted_event_id, '_event_all_day', 0 );
				update_post_meta( $inserted_event_id, '_event_start_date', date( 'Y-m-d', $start_time ) );
				update_post_meta( $inserted_event_id, '_event_end_date', date( 'Y-m-d', $end_time ) );
				update_post_meta( $inserted_event_id, '_location_id', $location_id );
				update_post_meta( $inserted_event_id, '_event_status', $event_status );
				update_post_meta( $inserted_event_id, '_event_private', 0 );
				update_post_meta( $inserted_event_id, '_start_ts', str_pad( $start_time, 10, 0, STR_PAD_LEFT));
				update_post_meta( $inserted_event_id, '_end_ts', str_pad( $end_time, 10, 0, STR_PAD_LEFT));
				update_post_meta( $inserted_event_id, 'ife_facebook_event_id', $facebook_id );
				update_post_meta( $inserted_event_id, '_xt_facebook_event_link', esc_url( $ticket_uri ) );
				update_post_meta( $inserted_event_id, '_xt_facebook_response_raw_data', wp_json_encode( $facebook_event_object ) );

				// Custom table Details
				$event_array = array(
					'post_id' => $inserted_event_id,
					'event_slug' => $inserted_event->post_name,
					'event_owner' => $inserted_event->post_author,
					'event_name' => $inserted_event->post_title,
					'event_start_time' => date( 'H:i:s', $start_time ),
					'event_end_time' => date( 'H:i:s', $end_time ),
					'event_all_day' => 0,
					'event_start_date' => date( 'Y-m-d', $start_time ),
					'event_end_date' => date( 'Y-m-d', $end_time ),
					'post_content' => $inserted_event->post_content,
					'location_id' => $location_id,
					'event_status' => $event_status,
					'event_date_created' => $inserted_event->post_date,
				);

				$event_table = ( defined( 'EM_EVENTS_TABLE' ) ? EM_EVENTS_TABLE : $wpdb->prefix . 'em_events' );
				if ( $is_exitsing_event ) {
					$eve_id = get_post_meta( $inserted_event_id, '_event_id', true );
					$where = array( 'event_id' => $eve_id );
					$wpdb->update( $event_table , $event_array, $where );
				}else{
					if ( $wpdb->insert( $event_table , $event_array ) ) {
						update_post_meta( $inserted_event_id, '_event_id', $wpdb->insert_id );
					}
				}

				if ( $is_exitsing_event ) {
					do_action( 'ife_after_update_em_facebook_event', $inserted_event_id, $facebook_event_object );
				}else{
					do_action( 'ife_after_create_em_facebook_event', $inserted_event_id, $facebook_event_object );
				}
				return $inserted_event_id;

			}else{
				return array( 'status'=> 0, 'message'=> 'Something went wrong, please try again.' );
			}
		}
	}

	/**
	 * Set Location for event
	 *
	 * @since    1.0.0
	 * @param array $facebook_event Facebook event.
	 * @return array
	 */
	public function em_get_location_args( $facebook_event, $event_id = false ) {
		global $wpdb;

		if ( !isset( $facebook_event->place->id ) ) {
			return null;
		}
		
		$event_venue_id = $facebook_event->place->id;
		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => IFE_LOCATION_POSTTYPE,
			'meta_key' => '_fb_event_location_id',
			'meta_value' => $event_venue_id,
			'suppress_filters' => false,
		) );


		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) && ! $event_id ) {
			return get_post_meta( $existing_venue[0]->ID, '_location_id', true );
		}
		wp_reset_query();

		$event_venue = $facebook_event->place;
		$locationdata = array(
			'post_title'  => isset( $event_venue->name ) ? $event_venue->name : 'Untitled - Location',
			'post_content' => '',
			'post_type'   => IFE_LOCATION_POSTTYPE,
			'post_status' => 'publish',
		);

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			$locationdata['ID'] = $existing_venue[0]->ID;
		}
		$location_id = wp_insert_post( $locationdata, true );

		if ( ! is_wp_error( $location_id ) ) {

			$blog_id = 0;
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
			}
			$location = get_post( $location_id );
			if ( empty( $location ) ) { return null;}
			// Location information.

			$address = isset( $event_venue->location->street ) ? $event_venue->location->street : '';
			$city = isset( $event_venue->location->city ) ? $event_venue->location->city : '';
			$state = isset( $event_venue->location->state ) ? $event_venue->location->state : '';
			$country = isset( $event_venue->location->country ) ? $event_venue->location->country : '';
			$cnames = json_decode(file_get_contents('http://country.io/names.json'), true);
			foreach($cnames as $iso2 => $name_country) {
				if( strtolower( $country ) == strtolower( $name_country ) ){
					$country = $iso2;
				}
			}
			if( strlen( $country ) > 2 ){
				$country = substr( $country, 0, 2);
			} 
			$zip = isset( $event_venue->location->zip ) ? $event_venue->location->zip : '';
			$lat = isset( $event_venue->location->latitude ) ? round( $event_venue->location->latitude, 6 ) : 0.000000;
			$lon = isset( $event_venue->location->longitude ) ? round( $event_venue->location->longitude, 6 ) : 0.000000;

			// Save metas.
			//update_post_meta( $location_id, '_location_id', 0 );
			update_post_meta( $location_id, '_blog_id', $blog_id );
			update_post_meta( $location_id, '_location_address', $address );
			update_post_meta( $location_id, '_location_town', $city );
			update_post_meta( $location_id, '_location_state', $state );
			update_post_meta( $location_id, '_location_postcode', $zip );
			update_post_meta( $location_id, '_location_region','' );
			update_post_meta( $location_id, '_location_country', $country );
			update_post_meta( $location_id, '_location_latitude', $lat );
			update_post_meta( $location_id, '_location_longitude', $lon );
			update_post_meta( $location_id, '_location_status', 1 );
			update_post_meta( $location_id, '_fb_event_location_id', $facebook_event->place->id );

			global $wpdb;
			$location_array = array(
				'post_id' => $location_id,
				'blog_id' => $blog_id,
				'location_slug' => $location->post_name,
				'location_name' => $location->post_title,
				'location_owner' => $location->post_author,
				'location_address' => $address,
				'location_town' => $city,
				'location_state' => $state,
				'location_postcode' => $zip,
				'location_region' => $state,
				'location_country' => $country,
				'location_latitude' => $lat,
				'location_longitude' => $lon,
				'post_content' => $location->post_content,
				'location_status' => 1,
				'location_private' => 0,
			);
			$location_format = array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%d', '%d' );
			$where_format = array( '%d' );

			if( defined( 'EM_LOCATIONS_TABLE' ) ){
				$event_location_table = EM_LOCATIONS_TABLE;
			}else{
				$event_location_table = $wpdb->prefix . 'em_locations';
			}

			if( $event_id && is_numeric( $event_id ) && $event_id > 0 ){
				$loc_id = get_post_meta( $event_id, '_location_id', true );
				if( $loc_id != '' ){
					$where = array( 'location_id' => $loc_id );	
					$is_update = $wpdb->update( $event_location_table, $location_array, $where, $location_format, $where_format );
					if ( false !== $is_update ) {
						return $loc_id;    
					}

				}else{
					$is_insert = $wpdb->insert( $event_location_table , $location_array, $location_format );
					if ( false !== $is_insert ) {
						$insert_loc_id = $wpdb->insert_id;
						update_post_meta( $location_id, '_location_id', $insert_loc_id );
						return $insert_loc_id;
					}
				}				
				
			}else{
				$is_insert = $wpdb->insert( $event_location_table , $location_array, $location_format );
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
	 * Create New Facebook event.
	 *
	 * @since    1.0.0
	 * @param array $facebook_event Facebook event.
	 * @param array $formated_args Formated arguments for facebook event.
	 * @param array $event_args
	 * @return int
	 */
	public function create_em_facebook_event( $facebook_event = array(), $formated_args = array(), $event_args = array() ) {
		// Create event using TEC advanced functions.
		$new_event_id = tribe_create_event( $formated_args );
		if ( $new_event_id ) {
			update_post_meta( $new_event_id, 'ife_facebook_event_id',  $facebook_event->id );
			update_post_meta( $new_event_id, 'ife_facebook_response_raw_data', wp_json_encode( $facebook_event ) );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				wp_set_object_terms( $new_event_id, $ife_cats, IFE_TEC_TAXONOMY );
			}

			$cover_image = isset( $facebook_event->cover->source ) ? ife_clean_url( esc_url( $facebook_event->cover->source ) ) : '';

			if( $cover_image != '' ){
				$this->setup_featured_image_to_event( $new_event_id, $cover_image );
			}

			do_action( 'ife_after_create_tec_facebook_event', $new_event_id, $formated_args, $facebook_event );
			return $new_event_id;

		}else{
			$fb_errors[] = __( 'Something went wrong, please try again.', 'import-facebook-events' );
			return;
		}
	}


	/**
	 * Update facebook event.
	 *
	 * @since 1.0.0
	 * @param int   $event_id existing facebook event.
	 * @param array $facebook_event facebook event.
	 * @param array $formated_args Formated arguments for facebook event.
	 * @param array $event_args User submited data at a time of schedule event
	 * @return int   $post_id Post id.
	 */
	public function update_em_facebook_event( $event_id, $facebook_event, $formated_args = array(), $event_args = array() ) {
		// Update event using TEC advanced functions.
		$update_event_id =  tribe_update_event( $event_id, $formated_args );
		if ( $update_event_id ) {
			update_post_meta( $update_event_id, 'ife_facebook_event_id',  $facebook_event->id );
			update_post_meta( $update_event_id, 'ife_facebook_response_raw_data', wp_json_encode( $facebook_event ) );

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? (array) $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				wp_set_object_terms( $update_event_id, $ife_cats, IFE_TEC_TAXONOMY );
			}

			$cover_image = isset( $facebook_event->cover->source ) ? ife_clean_url( esc_url( $facebook_event->cover->source ) ) : '';

			if( $cover_image != '' ){
				$this->setup_featured_image_to_event( $update_event_id, $cover_image );
			}

			do_action( 'ife_after_update_tec_facebook_event', $update_event_id, $formated_args, $facebook_event );
			return $update_event_id;

		}else{

			$fb_errors[] = __( 'Something went wrong, please try again.', 'import-facebook-events' );
			return;
		}
	}


	/**
	 * get access token
	 *
	 * @since 1.0.0
	 */
	public function get_access_token(){

		if( $this->fb_app_id == '' || $this->fb_app_secret == '' ){
			$fb_errors[] = __( 'Please insert Facebook app ID and app Secret.', 'import-facebook-events');
			return false;
		}

		$args = array(
			'grant_type' => 'client_credentials', 
			'client_id'  => $this->fb_app_id,
			'client_secret' => $this->fb_app_secret
			);
		$access_token_url = add_query_arg( $args, $this->fb_graph_url . 'oauth/access_token' );
		$access_token_response = wp_remote_get( $access_token_url );
		
		$access_token_response_body = wp_remote_retrieve_body( $access_token_response );
		$access_token_data = json_decode( $access_token_response_body );

		$access_token = ! empty( $access_token_data->access_token ) ? $access_token_data->access_token : null;
		
		return $access_token;
	}
	
	/**
	 * Generate Facebook api URL for grab Event.
	 *
	 * @since 1.0.0
	 */
	public function generate_facebook_api_url( $path = '', $query_args = array() ) {
		$query_args = array_merge( $query_args, array( 'access_token' => $this->get_access_token() ) );
		
		$url = add_query_arg( $query_args, $this->fb_graph_url . $path );

		return $url;
	}

	/**
	 * get a facebook object.
	 *
	 * @since 1.0.0
	 */
	public function get_facebook_response_data( $event_id, $args = array() ) {
		$url = $this->generate_facebook_api_url( $event_id, $args );
		$event_data = $this->get_json_response_from_url( $url );
		return $event_data;
	}

	/**
	 * get a facebook event object
	 *
	 * @since 1.0.0
	 */
	public function get_facebook_event_by_event_id( $event_id ) {
		return $this->get_facebook_response_data(
			$event_id,
			array(
				'fields' => implode(
					',',
					array(
						'id',
						'name',
						'description',
						'start_time',
						'end_time',
						'updated_time',
						'cover',
						'ticket_uri',
						'timezone',
						'owner',
						'place',
					)
				),
			)
		);
	}

	/**
	* Get body data from url and return decoded data.
	*
	* @since 1.0.0
	*/
	public function get_json_response_from_url( $url ) {
		
		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ) );
		return $response;
	}

	/**
	 * get all events for facebook page or organizer
	 *
	 * @since 1.0.0
	 * @return array the events
	 */
	public function get_events_for_facebook_page( $facebook_page_id ) {
		
		$args = array(
			'limit' => 9999,
			'since' => date( 'Y-m-d' ),
			'fields' => 'id'
		);

		$url = $this->generate_facebook_api_url( $facebook_page_id . '/events', $args );

		$response = $this->get_json_response_from_url( $url );
		$response_data = !empty( $response->data ) ? (array) $response->data : array();

		if ( empty( $response_data ) || empty( $response_data[0] ) ) {	
			return false;
		}

		$event_ids = array();		
		foreach ( $response_data as $event ) {
			$event_ids[] = $event->id;
		}
		return array_reverse( $event_ids );
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $facebook_event Facebook event.
	 * @return array
	 */
	public function format_event_args_for_tec( $facebook_event ) {

		if( !isset( $facebook_event->id ) || $facebook_event->id == '' ){
			return;
		}

		$facebook_id = $facebook_event->id;
		$post_title = isset( $facebook_event->name ) ? $facebook_event->name : '';
		$post_description = isset( $facebook_event->description ) ? $facebook_event->description : '';
		
		$start_time = isset( $facebook_event->start_time ) ? strtotime( ife_convert_datetime_to_db_datetime( $facebook_event->start_time ) ) : date( 'Y-m-d H:i:s');
		$end_time = isset( $facebook_event->end_time ) ? strtotime( ife_convert_datetime_to_db_datetime( $facebook_event->end_time ) ) : $start_time;

		$ticket_uri = isset( $facebook_event->ticket_uri ) ? esc_url( $facebook_event->ticket_uri ) : '';
		$timezone = $this->get_utc_offset( $facebook_event->start_time );

		$event_args  = array(
			'post_type'             => IFE_TEC_POSTTYPE,
			'post_title'            => $post_title,
			'post_status'           => 'pending',
			'post_content'          => $post_description,
			'EventStartDate'        => date( 'Y-m-d', $start_time ),
			'EventStartHour'        => date( 'h', $start_time ),
			'EventStartMinute'      => date( 'i', $start_time ),
			'EventStartMeridian'    => date( 'a', $start_time ),
			'EventEndDate'          => date( 'Y-m-d', $end_time ),
			'EventEndHour'          => date( 'h', $end_time ),
			'EventEndMinute'        => date( 'i', $end_time ),
			'EventEndMeridian'      => date( 'a', $end_time ),
			'EventTimezone' 		=> $timezone,
			'EventURL'              => $ticket_uri,
			'EventShowMap' 			=> 1,
			'EventShowMapLink'		=> 1,
		);

		if ( isset( $facebook_event->owner ) ) {
			$event_args['organizer'] = $this->get_organizer_args( $facebook_event );
		}

		if ( isset( $facebook_event->place ) ) {
			$event_args['venue'] = $this->get_venue_args( $facebook_event );
		}
		return $event_args;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since  1.0.0
	 * @param  array $facebook_event Facebook event.
	 * @return array
	 */
	public function get_organizer_args( $facebook_event ) {

		if ( !isset( $facebook_event->owner->id ) ) {
			return null;
		}

		$event_organizer = $facebook_event->owner->id;
		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => IFE_TEC_ORGANIZER_POSTTYPE,
			'meta_key' => 'ife_fb_event_organizer_id',
			'meta_value' => $event_organizer,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return array(
				'OrganizerID' => $existing_organizer[0]->ID,
			);
		}

		$organizer_raw_data = $this->get_facebook_response_data(
			$facebook_event->owner->id,
			array(
				'fields' => implode(
					',',
					array(
						'id',
						'name',
						'link',
						'phone'
					)
				),
			)
		);

		if ( !isset( $organizer_raw_data->id ) ) {
			return null;
		}

		$create_organizer = tribe_create_organizer( array(
			'Organizer'       => isset( $organizer_raw_data->name ) ? $organizer_raw_data->name : '',
			'Website'         => isset( $organizer_raw_data->link ) ? $organizer_raw_data->link : '',
			'Phone'           => isset( $organizer_raw_data->phone ) ? $organizer_raw_data->phone : '',
			'fb_organizer_ID' => isset( $organizer_raw_data->id ) ? $organizer_raw_data->id : '',
		) );

		if ( $create_organizer ) {
			update_post_meta( $create_organizer, 'ife_fb_event_organizer_id', $event_organizer );
			return array(
				'OrganizerID' => $create_organizer,
			);
		}
		return null;
	}

	/**
	 * Get venue args for event
	 *
	 * @since    1.0.0
	 * @param array $facebook_event Facebook event.
	 * @return array
	 */
	public function get_venue_args( $facebook_event ) {
		
		if ( !isset( $facebook_event->place->id ) ) {
			return null;
		}
		
		$event_venue_id = $facebook_event->place->id;
		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => IFE_TEC_VENUE_POSTTYPE,
			'meta_key' => 'ife_fb_event_venue_id',
			'meta_value' => $event_venue_id,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return array(
				'VenueID' => $existing_venue[0]->ID,
			);
		}

		$event_venue = $facebook_event->place;
		$crate_venue = tribe_create_venue( array(
			'Venue' 	  => isset( $event_venue->name ) ? $event_venue->name : '',
			'Address'     => isset( $event_venue->location->street ) ? $event_venue->location->street : '',
			'City'		  => isset( $event_venue->location->city ) ? $event_venue->location->city : '',
			'State' 	  => isset( $event_venue->location->state ) ? $event_venue->location->state : '',
			'Country' 	  => isset( $event_venue->location->country ) ? $event_venue->location->country : '',
			'Zip' 		  => isset( $event_venue->location->zip ) ? $event_venue->location->zip : '',
			'ShowMap' 	  => true,
			'ShowMapLink' => true,
		) );

		if ( $crate_venue ) {
			update_post_meta( $crate_venue, 'ife_fb_event_venue_id', $event_venue_id );
			return array(
				'VenueID' => $crate_venue,
			);
		}
		return null;
	}

	/**
	 * Setup Featured image to events
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @param int $image_url Image URL
	 * @return void
	 */
	public function setup_featured_image_to_event( $event_id, $image_url = '' ) {
		if ( $image_url == '' ) {
			return;
		}
		$event = get_post( $event_id );
		if( Empty ( $event ) ){
			return;
		}
		
		// Add Featured Image to Post
		$image_name       = $event->post_name . '_image.png';
		$upload_dir       = wp_upload_dir(); // Set upload folder
		$image_data       = file_get_contents( $image_url ); // Get image data
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
		$filename         = basename( $unique_file_name ); // Create image file name

		// Check folder permission and define file location
		if( wp_mkdir_p( $upload_dir['path'] ) ) {
		    $file = $upload_dir['path'] . '/' . $filename;
		} else {
		    $file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server
		file_put_contents( $file, $image_data );

		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

		// Set attachment data
		$attachment = array(
		    'post_mime_type' => $wp_filetype['type'],
		    'post_title'     => sanitize_file_name( $filename ),
		    'post_content'   => '',
		    'post_status'    => 'inherit'
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $event_id );

		// Include image.php
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, $attach_data );

		// And finally assign featured image to post
		set_post_thumbnail( $event_id, $attach_id );

	}

	/**
	 * Check for Existing Facebook Event
	 *
	 * @since    1.0.0
	 * @param int $facebook_event_id facebook event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $facebook_event_id ) {
		$event_args = array(
			'post_type' => IFE_TEC_POSTTYPE,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'meta_key'   => 'ife_facebook_event_id',
			'meta_value' => $facebook_event_id,
		);

		$events = new WP_Query( $event_args );
		if ( $events->have_posts() ) {
			while ( $events->have_posts() ) {
				$events->the_post();
				return get_the_ID();
			}
		}
		wp_reset_postdata();
		return false;
	}

	/**
	 * Check for Existing Facebook Event for Events Manager
	 *
	 * @since    1.0.0
	 * @param int $facebook_event_id facebook event id.
	 * @return /boolean
	 */
	public function get_em_event_by_event_id( $facebook_event_id ) {
		$event_args = array(
			'post_type' => IFE_EM_POSTTYPE,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'meta_key'   => 'ife_facebook_event_id',
			'meta_value' => $facebook_event_id,
		);

		$events = new WP_Query( $event_args );
		if ( $events->have_posts() ) {
			while ( $events->have_posts() ) {
				$events->the_post();
				return get_the_ID();
			}
		}
		wp_reset_postdata();
		return false;
	}

	/**
	 * Get organizer Name based on Organiser ID.
	 *
	 * @since    1.0.0
	 * @param array $organizer_id Organizer event.
	 * @return array
	 */
	public function get_organizer_name_by_id( $organizer_id ) {
		
		if( !$organizer_id || $organizer_id == '' ){
			return;
		}

		$organizer_raw_data = $this->get_facebook_response_data( $organizer_id, array() );
		if( ! isset( $organizer_raw_data->name ) ){
			return '';
		}
		
		$oraganizer_name = isset( $organizer_raw_data->name ) ? $organizer_raw_data->name : '';
		return $oraganizer_name;

	}

	/**
	 * Get UTC offset
	 *
	 * @since    1.0.0
	 */
	public function get_utc_offset( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
		} catch ( Exception $e ) {
			return '';
		}

		$timezone = $datetime->getTimezone();
		$offset   = $timezone->getOffset( $datetime ) / 60 / 60;

		if ( $offset >= 0 ) {
			$offset = '+' . $offset;
		}

		return 'UTC' . $offset;
	}
}
