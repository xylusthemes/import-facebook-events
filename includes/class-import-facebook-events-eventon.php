<?php
/**
 * Class for Import Events into EventON
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
 * EventON functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/includes
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class Import_Facebook_Events_EventON {

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
	 * The Events Calendar Location Taxonomys
	 *
	 * @var string
	 */
	protected $location_taxonomy;

	/**
	 * The Events Calendar Organizer Taxonomy
	 *
	 * @var string
	 */
	protected $organizer_taxonomy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->taxonomy           = 'event_type';
		$this->event_posttype     = 'ajde_events';
		$this->location_taxonomy  = 'event_location';
		$this->organizer_taxonomy = 'event_organizer';
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
	 * Get Location Taxonomy
	 *
	 * @return string
	 */
	public function get_location_taxonomy() {
		return $this->location_taxonomy;
	}

	/**
	 * Get Organizer Taxonomy
	 *
	 * @return string
	 */
	public function get_organizer_taxonomy() {
		return $this->organizer_taxonomy;
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
		$post_title       = isset( $centralize_array['name'] ) ? convert_chars( stripslashes( $centralize_array['name'] ) ) : '';
		$post_description = isset( $centralize_array['description'] ) ? wpautop( convert_chars( stripslashes( $centralize_array['description'] ) ) ) : '';
		$start_time       = $centralize_array['starttime_local'];
		$end_time         = $centralize_array['endtime_local'];
		$ticket_uri       = $centralize_array['url'];

		$evon_eventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
            'post_status'  => 'pending',
            'post_author'  => isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id()
		);
		if ( $is_exitsing_event ) {
			$evon_eventdata['ID'] = $is_exitsing_event;
		}
		if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
			$evon_eventdata['post_status'] = $event_args['event_status'];
		}

		if ( $is_exitsing_event && ! $ife_events->common->ife_is_updatable( 'status' ) ) {
			$evon_eventdata['post_status'] = get_post_status( $is_exitsing_event );
		}

		$inserted_event_id = wp_insert_post( $evon_eventdata, true );

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
				}
			}
			$address      = isset( $centralize_array['location']['address_1'] ) ? sanitize_text_field( $centralize_array['location']['address_1'] ) : '';
			$full_address = isset( $centralize_array['location']['full_address'] ) ? sanitize_text_field( $centralize_array['location']['full_address'] ) : '';
			if ( ! empty( $full_address ) ) {
				$address = $full_address; }
			$city    = isset( $centralize_array['location']['city'] ) ? sanitize_text_field( $centralize_array['location']['city'] ) : '';
			$state   = isset( $centralize_array['location']['state'] ) ? sanitize_text_field( $centralize_array['location']['state'] ) : '';
			$country = isset( $centralize_array['location']['country'] ) ? sanitize_text_field( $centralize_array['location']['country'] ) : '';
			$timezone = isset( $centralize_array['timezone'] ) ? sanitize_text_field( $centralize_array['timezone'] ) : 'UTC';
			$timezone_name = isset( $centralize_array['timezone_name'] ) ? sanitize_text_field( $centralize_array['timezone_name'] ) : 'Africa/Abidjan';
			$is_all_day    = !empty( $centralize_array['is_all_day'] ) ? $centralize_array['is_all_day'] : 0;
			$is_online     = isset( $centralize_array['is_online'] ) ? $centralize_array['is_online'] : false;

			update_post_meta( $inserted_event_id, 'ife_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, 'ife_event_link', $centralize_array['url'] );
			update_post_meta( $inserted_event_id, 'evcal_srow', $start_time );
			update_post_meta( $inserted_event_id, 'evcal_erow', $end_time );
			update_post_meta( $inserted_event_id, 'evcal_lmlink', $centralize_array['url'] );
			update_post_meta( $inserted_event_id, 'ife_event_timezone', $timezone );
			update_post_meta( $inserted_event_id, 'ife_event_timezone_name', $timezone_name );
			update_post_meta( $inserted_event_id, 'evcal_allday', $is_all_day );
			update_post_meta( $inserted_event_id, '_evo_tz', $timezone_name );

			$start_ampm = date("a", $start_time);
			$start_hour = date("h", $start_time);
			$start_minute = date("i", $start_time);
			$end_ampm = date("a", $end_time);
			$end_hour = date("h", $end_time);
			$end_minute = date("i", $end_time);

			// Update post meta fields
			update_post_meta($inserted_event_id, '_start_ampm', $start_ampm);
			update_post_meta($inserted_event_id, '_start_hour', $start_hour);
			update_post_meta($inserted_event_id, '_start_minute', $start_minute);
			update_post_meta($inserted_event_id, '_end_ampm', $end_ampm);
			update_post_meta($inserted_event_id, '_end_hour', $end_hour);
			update_post_meta($inserted_event_id, '_end_minute', $end_minute);
			update_post_meta( $inserted_event_id, '_status', 'scheduled' );

			$location_name = isset( $centralize_array['location']['name'] ) ? sanitize_text_field( $centralize_array['location']['name'] ) : '';
			if( $is_online == true ){
				update_post_meta( $inserted_event_id, '_virtual', 'yes' );
				$location_name = 'Online Event';
			}
			if ( ! empty( $location_name ) ) {
				$loc_term = term_exists( $location_name, $this->location_taxonomy );
				if ( 0 !== $loc_term && null !== $loc_term ) {
					if ( is_array( $loc_term ) ) {
						$loc_term_id = (int) $loc_term['term_id'];
					}
				} else {
					$new_loc_term = wp_insert_term(
						$location_name,
						$this->location_taxonomy
					);
					if ( ! is_wp_error( $new_loc_term ) ) {
						$loc_term_id = (int) $new_loc_term['term_id'];
					}
				}

				// latitude and longitude.
				$loc_term_meta                        = array();
				$loc_term_meta['location_lon']        = ( ! empty( $centralize_array['location']['long'] ) ) ? $centralize_array['location']['long'] : null;
				$loc_term_meta['location_lat']        = ( ! empty( $centralize_array['location']['lat'] ) ) ? $centralize_array['location']['lat'] : null;
				$loc_term_meta['evcal_location_link'] = ( isset( $centralize_array['location']['url'] ) ) ? $centralize_array['location']['url'] : null;
				$loc_term_meta['location_address']    = $address;
				$loc_term_meta['location_city']       = $city;
				$loc_term_meta['location_state']      = $state;
				$loc_term_meta['location_country']    = $country;
				$loc_term_meta['evo_loc_img']         = ( isset( $centralize_array['location']['image_url'] ) ) ? $centralize_array['location']['image_url'] : null;
				update_option( 'taxonomy_' . $loc_term_id, $loc_term_meta );

				if ( function_exists( 'evo_save_term_metas' ) ) {
					evo_save_term_metas( $this->location_taxonomy, $loc_term_id, $loc_term_meta );
				}

				$term_loc_ids = wp_set_object_terms( $inserted_event_id, $loc_term_id, $this->location_taxonomy );
				update_post_meta( $inserted_event_id, 'evo_location_tax_id', $loc_term_id );
				update_post_meta( $inserted_event_id, 'evcal_location_name', $centralize_array['location']['name'] );
				update_post_meta( $inserted_event_id, 'evcal_location_link', $centralize_array['location']['url'] );
				update_post_meta( $inserted_event_id, 'evcal_location', $address );
				update_post_meta( $inserted_event_id, 'evcal_lat', $centralize_array['location']['lat'] );
				update_post_meta( $inserted_event_id, 'evcal_lon', $centralize_array['location']['long'] );
				if ( ! empty( $centralize_array['location']['long'] ) && ! empty( $centralize_array['location']['lat'] ) ) {
					update_post_meta( $inserted_event_id, 'evcal_gmap_gen', 'yes' );
				}
			}

			if ( isset( $centralize_array['organizer'] ) && ! empty( $centralize_array['organizer']['name'] ) ) {

				$org_contact = $centralize_array['organizer']['phone'];
				if ( ! empty( $centralize_array['organizer']['email'] ) ) {
					$org_contact = $centralize_array['organizer']['email'];
				}
				$org_term = term_exists( $centralize_array['organizer']['name'], $this->organizer_taxonomy );
				if ( 0 !== $org_term && null !== $org_term ) {
					if ( is_array( $org_term ) ) {
						$org_term_id = (int) $org_term['term_id'];
					}
				} else {
					$new_org_term = wp_insert_term(
						$centralize_array['organizer']['name'],
						$this->organizer_taxonomy
					);
					if ( ! is_wp_error( $new_org_term ) ) {
						$org_term_id = (int) $new_org_term['term_id'];
					}
				}

				$org_term_meta                      = array();
				$org_term_meta['evcal_org_contact'] = $org_contact;
				$org_term_meta['evcal_org_address'] = null;
				$org_term_meta['evo_org_img']       = ( isset( $centralize_array['organizer']['image_url'] ) ) ? $centralize_array['organizer']['image_url'] : null;
				$org_term_meta['evcal_org_exlink']  = ( isset( $centralize_array['organizer']['url'] ) ) ? $centralize_array['organizer']['url'] : null;

				update_option( 'taxonomy_' . $org_term_id, $org_term_meta );

				if ( function_exists( 'evo_save_term_metas' ) ) {
					evo_save_term_metas( $this->organizer_taxonomy, $org_term_id, $org_term_meta );
				}

				$term_org_ids = wp_set_object_terms( $inserted_event_id, $org_term_id, $this->organizer_taxonomy );
				update_post_meta( $inserted_event_id, 'evo_organizer_tax_id', $org_term_id );
				update_post_meta( $inserted_event_id, 'evcal_organizer', $centralize_array['organizer']['name'] );
				update_post_meta( $inserted_event_id, 'evcal_org_contact', $org_contact );
				update_post_meta( $inserted_event_id, 'evcal_org_exlink', $centralize_array['organizer']['url'] );
				update_post_meta( $inserted_event_id, 'evo_evcrd_field_org', 'no' );

			}

			if ( $is_exitsing_event ) {
				do_action( 'ife_after_update_event_on_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id'     => $inserted_event_id,
				);
			} else {
				do_action( 'ife_after_create_event_on_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
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
