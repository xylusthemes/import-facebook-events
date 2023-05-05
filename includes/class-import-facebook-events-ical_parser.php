<?php
/**
 * Class for iCal Parser.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */

use Kigkonsult\Icalcreator\Vcalendar;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Facebook_Events_Ical_Parser {

	/**
	 * Calendar Timezone get from "X-WR-TIMEZONE"
	 *
	 * @since    1.0.0
	 */
	protected $timezone;

	/**
	 * Calendar Timezone get from "X-WR-TIMEZONE"
	 *
	 * @since    1.0.0
	 */
	protected $is_aioec_active = false;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		// init operations for iCal
	}

	/**
	 * parse events from ical content as per selected criteria
	 *
	 * @since  1.1.0
	 * @param  array $eventdata  import event data.
	 * @param  strine $ics_content  ics file content.
	 * @return array/boolean
	 */
	public function parse_import_events( $event_data = array(), $ics_content = '' ){
		global $ife_errors, $ife_events;
		if( empty( $ics_content ) ){
			return false;
		}
		$activate_plugins = $ife_events->common->get_active_supported_event_plugins();
		if( !empty( $activate_plugins ) ){
			foreach ($activate_plugins as $key => $value) {
				if( $key == 'aioec' ){
					$this->is_aioec_active = true;			
				}
			}
		}
		$imported_events = array();

		$start_date = date('Y-m-d' );
		$end_date = date('Y-m-d', strtotime('+2 years') );
		
		if( isset( $event_data['start_date'] ) && $event_data['start_date'] != '' ){
			$start_date = $event_data['start_date'];
		}
		if( isset( $event_data['end_date'] ) && $event_data['end_date'] != '' ){
 			$end_date = $event_data['end_date'];
		}

		$start_date  = strtotime( $start_date );
		$end_date  = strtotime( $end_date );
		if( ( $end_date - $start_date ) < 0 ){
			$ife_errors[] = esc_html__( 'Please select end date bigger than start date.', 'import-facebook-events' );
			return false;
		}

		// Get Start and End date  day,month,year
		$start_month = date( 'm', $start_date );
		$start_year  = date( 'Y', $start_date );
		$start_day   = date( 'd', $start_date );
		$end_month   = date( 'm', $end_date );
		$end_year    = date( 'Y', $end_date );
		$end_day     = date( 'd', $end_date );

		// initiate vcalendar
		//$config = array( 'unique_id' => 'Import_Facebook_Events_Ical_Parser' . microtime( true ) ); 
		//$calendar = new vcalendar( $config );
		$calendar = new Vcalendar();
		if ( ! $calendar ) {
			return false;
		}

		// Parse ics Content.
		$calendar->parse( $ics_content );

		$calendar_name = $calendar->getXprop( Vcalendar::X_WR_CALNAME );
		$timezone = $calendar->getXprop( Vcalendar::X_WR_TIMEZONE );
		if ( ! empty( $timezone[1] ) ) {
			$this->timezone = $timezone[1];
		}

		$all_events = $calendar->selectComponents( $start_year, $start_month, $start_day, $end_year, $end_month, $end_day, 'vevent' );
		$centralize_events = array();

       	// Events per Year
		foreach ( $all_events as $year => $year_arr ) {
			if ( strtotime( "$year-12-31" ) < $start_date ) {
				continue;
			}

			if ( strtotime( "$year-01-01" ) > $end_date ) {
				continue;
			}

			// Events per Month
			foreach ( $year_arr as $month => $month_arr ) {
				$mm = str_pad( $month, 2, '0', STR_PAD_LEFT );
				$d = new DateTime( "{$year}-{$mm}-01" );
				$d->setDate( $year, $mm, $d->format( 't' ) );
				// skip is event date is less then start date
				if ( $d->format( 'U' ) < $start_date ) {
					continue;
				}
				// skip is event date is greater then end date
				if ( strtotime( "{$year}-{$mm}-01" ) > $end_date ) {
					continue;
				}

				// Events per Day
				foreach ( $month_arr as $day => $day_arr ) {
					$dd = str_pad( $day, 2, '0', STR_PAD_LEFT );

					if ( strtotime( "{$year}-{$mm}-{$dd} 23:59:59" ) < $start_date ) {
						continue;
					}

					if ( strtotime( "{$year}-{$mm}-{$dd}" ) > $end_date ) {
						continue;
					}

					// Fitered Events
					foreach ( $day_arr as $event ) {
						
						$centralize_event = $this->generate_centralize_event_array( $event, $event_data );
						if( !empty( $centralize_event ) ){
							$centralize_events[$centralize_event['ID']] = $centralize_event;
						}
					}
				} //end day foreach
			} //end month foreach
		} //end year foreach
		//exit();

		$imported_events = array();
		if( !empty( $centralize_events ) ){
			foreach ($centralize_events as $central_event ) {
				$imported_events[] = $ife_events->common->import_events_into( $central_event, $event_data );
			}
		}
		
		return $imported_events;
	}

	/**
	 * Format events arguments as per centralize event format
	 *
	 * @since    1.0.0
	 * @param 	 Object $event iCal vevent Object.
	 * @return 	 array
	 */
	public function generate_centralize_event_array( $event, $event_data = array()  ) {
		if( empty( $event ) ){
			return false;
		}
		global $ife_events;

		$post_title = str_replace('\n', ' ', $event->getSummary() );
		$post_description = str_replace('\n', '<br/>', $event->getDescription() );
		$uid = $this->generate_uid_for_ical_event( $event );
		$uid_old = $this->generate_uid_for_ical_event_old_support( $event );
		$url = $event->getUrl();
		$is_all_day = false;

		$system_timezone    = date_default_timezone_get();
		$wordpress_timezone = $this->wordpress_timezone();
		$calendar_timezone  = $this->timezone;

		$start = $event->getDtstart();
		$end   = $event->getDtend();
		if ( empty( $end ) ) {
			$end = $start;
		}

		// Check if all day event.
		if ( "000000" === $start->format('His') && "000000" === $end->format('His') ) {
			$is_all_day = true;
		}
		// Also check the proprietary MS all-day field.
		$ms_allday = $event->getXprop( 'X-MICROSOFT-CDO-ALLDAYEVENT');
		if ( ! empty( $ms_allday ) && $ms_allday[1] == 'TRUE' ) {
			$is_all_day = true;
		}

		// Setup timezone for event.
		$timezone = $start->getTimezone()->getName();
        $force_timezone = false;
		if ( 'UTC' === $timezone && ! $is_all_day && $this->timezone != '' ){
			$force_timezone = $this->timezone;
			$timezone = $this->timezone;
		} elseif ( ! empty( $this->timezone ) ) {
			// if there is a global timezone in the iCal file, use that
			$timezone = $this->timezone;
            if( !$is_all_day ){
                $force_timezone = $this->timezone;
            }
		}

		if( $is_all_day || empty( $timezone ) ){
			$timezone = $system_timezone;
		}
		/*if( empty( $timezone ) ){
			$timezone = $system_timezone;
		}*/

		$start_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $start, $force_timezone ) );
		$end_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $end, $force_timezone ) );
		/*$start_time = strtotime( $start ); 
		$end_time = strtotime( $end );*/

		$x_start_str  = $event->getXprop( Vcalendar::X_CURRENT_DTSTART );
		$x_start_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( end( $x_start_str ), $force_timezone ) );
		//$x_start_time = strtotime( end( $x_start_str ) );
		//$x_start_str  = $this->convert_date_to_according_timezone( end( $x_start_str ), $system_timezone, $timezone );
		$x_end_str = $event->getXprop( Vcalendar::X_CURRENT_DTEND );
		$x_end_str = end( $x_end_str );
		if( $x_end_str == '' ){
			$x_end_str = end( $x_start_str );
		}
		$x_end_time = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $x_end_str ,$force_timezone ) );
		//$x_end_time = strtotime( $x_end_str );
		//$x_end_str  = $this->convert_date_to_according_timezone( end( $x_end_str ), $system_timezone, $timezone );
		
		//check event has an X-CURRENT-DTSTART the correct date will be X-CURRENT-DTSTART
		if ( ! empty( $x_start_time ) && ! empty( $x_end_time ) ) {
			$x_time_diff = ( $x_end_time - $x_start_time );
			$time_diff = ( $end_time - $start_time );
			if( $is_all_day ){
				if( $this->is_aioec_active ){
					$time_diff += 1;
				}else{
					$time_diff -= 86399;
				}				
			}
			if( $x_time_diff == $time_diff ){
				$start_time = $x_start_time;
				$end_time = $x_end_time;
                if ( $is_all_day ) {
                    if( !$this->is_aioec_active ){
                        // if all day events add 86399 secs (1 day - 1 sec) to end date.
                        $end_time += 86399;
                    }else{
                        // if all day events deduct 1 sec from end date.
                        $end_time -= 1;
                    }
                }
			}		
		}

		if( $is_all_day === true ){
			$end_time = $end_time-1;
		}

		$event_image     = '';
		$event_venue     = null;
		$ical_attachment = $event->getAttach( true, true );
		if( isset($ical_attachment['params']) && isset($ical_attachment['params']['FMTTYPE']) ) {
			$attachment_type = $ical_attachment['params']['FMTTYPE'];
			$image_types = array('image/jpeg', 'image/gif', 'image/png', 'image/jpg');
			if( in_array($attachment_type, $image_types ) && !empty($ical_attachment['value']) ){
				$event_image =  $ical_attachment['value'];
			}
		}

		$ical_wp_images = $event->getXprop('X-WP-IMAGES-URL');
		if( !empty( $ical_wp_images ) && !empty( $ical_wp_images[1]) ){
			$event_image =  $ical_wp_images[1];
		}
		$timezone_name = !empty( $timezone ) ? $timezone : $calendar_timezone;

		// Only for facebook ical imports.
		$match = 'https://www.facebook.com/events/';
		if ( strpos( $url, $match ) !== false ) {
			$ife_user_token_options = get_option( 'ife_user_token_options', array() );
			if( !empty( $ife_user_token_options ) ){
				$authorize_status =	isset( $ife_user_token_options['authorize_status'] ) ? $ife_user_token_options['authorize_status'] : 0;
				if( 1 == $authorize_status ){
					$check_facebook = explode( '/', $url);
					if( $check_facebook[2] == 'www.facebook.com' ){
						$event_api_data = $this->get_event_image_and_location( $event_data['import_into'], $uid );

						if( !empty( $event_api_data ) ){
							$event_image   = $event_api_data['image'];
							$event_venue   = $event_api_data['location'];
							$start_time    = $event_api_data['start_time'];
							$end_time      = $event_api_data['end_time'];
							$timezone      = $event_api_data['ical_timezone'];
							$timezone_name = $event_api_data['ical_timezone_name'];
						}

						if( isset( $event_api_data['ical_timezone_name'] ) && empty( $event_api_data['start_time'] ) ){
							$start_time = strtotime( $this->convert_fb_ical_timezone( $start->format('Y-m-d H:i:s'), $event_api_data['ical_timezone_name'] ) );
						}
						if( isset( $event_api_data['ical_timezone_name'] ) &&  empty( $event_api_data['end_time'] ) ){
							$end_time   = strtotime( $this->convert_fb_ical_timezone( $end->format('Y-m-d H:i:s'), $event_api_data['ical_timezone_name'] ) );
						}
					}
				}
			}
		}

		$xt_event = array(
			'origin'          => 'ical',
			'ID'              => $uid,
			'ID_ical_old'     => $uid_old,
			'name'            => $post_title,
			'description'     => $post_description,
			'starttime_local' => $start_time,
			'endtime_local'   => $end_time,
			'starttime'       => date('Ymd\THis', $start_time),
			'endtime'         => date('Ymd\THis', $end_time),
			'startime_utc'    => '',
			'endtime_utc'     => '',
			'timezone'        => $timezone,
			'timezone_name'   => $timezone_name,
			'utc_offset'      => '',
			'event_duration'  => '',
			'is_all_day'      => $is_all_day,
			'url'             => $url,
			'image_url'       => $event_image,
		);

		$oraganizer_data = null;
		$event_location  = $this->get_location( $event, $event_venue );


		$organizer = $event->getOrganizer( true );
		$organizer_params = isset($organizer['params']) ? $organizer['params'] : array();
		$organizer = isset($organizer['value']) ? $organizer['value'] : '';
		if ( !empty( $organizer ) ) {
			$params = wp_parse_args( str_replace( ';', '&', $organizer ) );
			foreach ( $params as $k => $param ) {
				if ( $k == 'CN' ) {
					$oraganizer = explode( ':MAILTO:', $param);
					$oraganizer_data['ID'] = strtolower( str_replace( ' ','_', trim( preg_replace( '/^"(.*)"$/', '\1', $oraganizer[0] ) ) ) );
					$oraganizer_data['name'] = preg_replace( '/^"(.*)"$/', '\1', $oraganizer[0] );
					$oraganizer_data['email'] = preg_replace( '/^"(.*)"$/', '\1', trim( $oraganizer[1]) );
				} else {
					if ( ! empty( $param ) ) {
						$oraganizer_data[ $k ] = $param;
					} else {
						// Check if only email is there.
						$oraganizer = explode( 'MAILTO:', $k);
						if(isset($oraganizer[1]) && !empty($oraganizer[1])){
							$oraganizer_data['ID'] = strtolower( str_replace( ' ','_', trim( preg_replace( '/^"(.*)"$/', '\1', $oraganizer[1] ) ) ) );
							$oraganizer_data['name'] = preg_replace( '/^"(.*)"$/', '\1', $oraganizer[1] );
							$oraganizer_data['email'] = preg_replace( '/^"(.*)"$/', '\1', trim( $oraganizer[1]) );
							if(!empty($organizer_params['CN'])){
								$oraganizer_data['name'] = $organizer_params['CN'];
							}
						}
					}
				}
			}
		}		
		
		$xt_event['organizer'] = $oraganizer_data;
		$xt_event['location']  = $event_location;
		
		return $xt_event;
	}

	/**
	 * Get location args for event
	 *
	 * @since    1.0.0
	 * @param array $event iCal vevent.
	 * @return array
	 */
	public function get_location( $event, $event_venue ) {
		if ( ! empty( $event_venue ) ) {
			$event_location = array(
				'ID'           => isset( $event_venue->id ) ? $event_venue->id : '',
				'name'         => isset( $event_venue->name ) ? $event_venue->name : '',
				'description'  => '',
				'address_1'    => isset( $event_venue->location->street ) ? $event_venue->location->street : '',
				'address_2'    => '',
				'city'         => isset( $event_venue->location->city ) ? $event_venue->location->city : '',
				'state'        => isset( $event_venue->location->state ) ? $event_venue->location->state : '',
				'country'      => isset( $event_venue->location->country ) ? $event_venue->location->country : '',
				'zip'          => isset( $event_venue->location->zip ) ? $event_venue->location->zip : '',
				'lat'          => isset( $event_venue->location->latitude ) ? $event_venue->location->latitude : '',
				'long'         => isset( $event_venue->location->longitude ) ? $event_venue->location->longitude : '',
				'full_address' => isset( $event_venue->location->street ) ? $event_venue->location->street : '',
				'url'          => '',
				'image_url'    => '',
			);	
		} else {
			$geo       = $event->getGeo();
			$latitude  = isset( $geo['latitude'] ) ? (float)$geo['latitude'] : '';	
			$longitude = isset( $geo['longitude'] ) ? (float)$geo['longitude'] : '';
			$location  = str_replace('\n', ' ', $event->getLocation() );
			if ( empty( $location ) ) {
				return null;
			}

			$event_location = array(
				'ID'           => strtolower( trim( stripslashes( $location ) ) ),
				'name'         => isset( $location ) ? stripslashes( $location ) : '',
				'description'  => '',
				'address_1'    => '',
				'address_2'    => '',
				'city'         => '',
				'state'        => '',
				'country'      => '',
				'zip'	       => '',
				'lat'     	   => '',
				'long'		   => '',
				'full_address' => isset( $location ) ? stripslashes( $location ) : '',
				'url'          => '',
				'image_url'    => ''
			);
		}

		return $event_location;
	}

	/**
	 * Generate UID for ical event.
	 *
	 * @since    1.0.0
	 * @param 	 array $ical_event iCal vevent Object.
	 * @return 	 array
	 */
	public function generate_uid_for_ical_event( $ical_event ) {

		$recurrence_id = $ical_event->getRecurrenceid();
		if ( is_a( $recurrence_id, 'DateTime' ) ) {
			$recurrence_id = $recurrence_id->format('Y-m-d');
		}
		if ( is_array( $recurrence_id) ) {
			$recurrence_id = implode('-', $recurrence_id);
		}
		if ( false === $recurrence_id && false !== $ical_event->getXprop( Vcalendar::X_RECURRENCE ) ) {
			$current_dt_start = $ical_event->getXprop( Vcalendar::X_CURRENT_DTSTART );
			if ( is_a( $current_dt_start, 'X-CURRENT-DTSTART' ) ) {
					$recurrence_id = $recurrence_id->format('Y-m-d');
			}elseif( !empty( $current_dt_start[1] ) ){
				$recurrence_id    = isset( $current_dt_start[1] ) ? $current_dt_start[1] : false;
			}
		}
		$event_id = $ical_event->getUid() . $recurrence_id;
		$event_id = explode( '@facebook', $event_id );
		$ical_event_id = str_replace('e', '', $event_id[0]);
		return $ical_event_id;
	}

	/**
	 * Generate UID for ical event old support.
	 *
	 * @since    1.0.0
	 * @param 	 array $ical_event iCal vevent Object.
	 * @return 	 array
	 */
	public function generate_uid_for_ical_event_old_support( $ical_event ) {

		$recurrence_id = $ical_event->getRecurrenceid();
		if ( is_a( $recurrence_id, 'DateTime' ) ) {
			$recurrence_id = $recurrence_id->format('Y-m-d');
		}
		if ( is_array( $recurrence_id) ) {
			$recurrence_id = implode('-', $recurrence_id);
		}
		if ( false === $recurrence_id && false !== $ical_event->getXprop( Vcalendar::X_RECURRENCE ) ) {
			$current_dt_start = $ical_event->getXprop( Vcalendar::X_CURRENT_DTSTART );
			if ( is_a( $current_dt_start, 'X-CURRENT-DTSTART' ) ) {
					$recurrence_id = $recurrence_id->format('Y-m-d');
			}elseif( !empty( $current_dt_start[1] ) ){
				$recurrence_id    = isset( $current_dt_start[1] ) ? $current_dt_start[1] : false;
			}
		}
		return $ical_event->getUid() . $recurrence_id . $ical_event->getSequence();
	}

	/**
	 * Wordpress TimeZone
	 *
	 * @since    1.0.0
	 * @return 	 string.
	 */
	public function wordpress_timezone() {
		$utc_offset = get_option( 'gmt_offset' );
		$timezone   = get_option( 'timezone_string' );

		if ( ! empty( $timezone ) ) {
			return $timezone;
		}

		if ( 0 == $utc_offset ) {
			return 'UTC+0';
		} elseif ( $utc_offset < 0 ) {
			return 'UTC' . $utc_offset;
		}
		return 'UTC+' . $utc_offset;
	}

	/**
	 * Convert datetime to desired timezone.
	 *
	 * @param string $event_datetime     Date string to possibly convert
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function convert_fb_ical_timezone( $event_datetime = '', $timezone = '' ) {

		$timezone_string = get_option( 'timezone_string' );
		$offset  = (float) get_option( 'gmt_offset' );
		if( !empty( $timezone ) ){
			$tz = $timezone;
		}else{

			if ( ! empty( $timezone_string ) ) {
				$tz        = $timezone_string;
			} else {

				$hours     = (int) $offset;
				$minutes   = ( $offset - $hours );
				$sign      = ( $offset < 0 ) ? '-' : '+';
				$abs_hour  = abs( $hours );
				$abs_mins  = abs( $minutes * 60 );
				$tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );
				
				list($hours, $minutes) = explode(':', $tz_offset);
				$seconds = $hours * 60 * 60 + $minutes * 60;
				$tz = timezone_name_from_abbr('', $seconds, 1);
				if($tz === false) $tz = timezone_name_from_abbr('', $seconds, 0);
			}
		}

		$utc_tz         = new DateTimeZone( 'UTC' );
		$datetime       = new DateTime( $event_datetime, $utc_tz );
		$event_timezone = new DateTimeZone( $tz );
		$datetime->setTimezone( $event_timezone );

		return $datetime->format('Y-m-d H:i:s');
	}

	/**
     * Check and Update event image.
     *
     * @param string $import_into
     * @param int $event_id
     *
     * @return array
     *
     * @since 1.0.0
     */
	public function get_event_image_and_location( $import_into, $facebook_event_id = 0 ){
		global $ife_events;
		$event_api_data    = array();
		$event_id          = $facebook_event_id;
		$post_type         = $ife_events->{$import_into}->get_event_posttype();
		$is_exitsing_event = $ife_events->common->get_event_by_event_id( $post_type, $event_id );
		$has_event_image   = get_post_meta( $is_exitsing_event, '_thumbnail_id', true );

		if ( empty( $has_event_image ) ){
			$fetch_image = apply_filters( 'ife_ical_fetch_event_image', true );
			if( $fetch_image ) {
				$facebook_event = $ife_events->facebook->get_facebook_event_by_event_id( $event_id );
				if ( ! empty( $facebook_event->cover )  ) {
					$event_api_data['image']              = $facebook_event->cover->source;
					$event_api_data['location']           = $facebook_event->place;
					$event_api_data['ical_timezone_name'] = $facebook_event->timezone;
					$event_api_data['start_time']         = isset( $facebook_event->start_time ) ? strtotime( $ife_events->common->convert_datetime_to_db_datetime( $facebook_event->start_time ) ) :'';
					$event_api_data['end_time']           = isset( $facebook_event->end_time ) ? strtotime( $ife_events->common->convert_datetime_to_db_datetime( $facebook_event->end_time ) ) : $event_api_data['start_time'] ;
					$event_api_data['ical_timezone']      = $ife_events->common->get_utc_offset( $facebook_event->start_time );
				}
			}
		} else {
			$attachment_id = get_post_thumbnail_id( $is_exitsing_event );
			$imagesource = get_post_meta( $attachment_id, '_ife_attachment_source', true );				
			if( !empty( $imagesource ) ){
				$event_api_data['image'] = $imagesource;
			}
			$event_timezone   = get_post_meta( $is_exitsing_event, 'ife_event_timezone_name', true );
			if( !empty( $event_timezone ) ){
				$event_api_data['ical_timezone_name'] = $event_timezone;
			}
		}
		return $event_api_data;
	}

	/**
	 * Convert a system timezone datetime string to the desired event datetime string
	 *
	 * A conversion only occurs if the system timezone is NOT UTC
	 *
	 * @param string $date_string     Date string to possibly convert
	 * @param string $system_timezone System timezone
	 * @param string $event_timezoen  Timezone of event in ical feed
	 *
	 * @return string
	 */
	public function convert_date_to_according_timezone( $date_string, $system_timezone, $event_timezone ) {
		if ( 'UTC' === $system_timezone ) {
			return $date_string;
		}

		$given_string = new DateTime( $date_string );
		$given_string->setTimezone(new DateTimeZone( $event_timezone ) );
		$date_string = $given_string->format('Ymd\THis');
		return $date_string;
	}

    /**
     * Convert datetime to desired timezone.
     *
     * @param string $date_string     Date string to possibly convert
     * @param string $force_timezone timezone to be conterted
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function convert_datetime_to_timezone_wise_datetime( $datetime, $force_timezone = false ) {
        try {
			if ( ! is_a( $datetime, 'DateTime' ) ) {
				$datetime = new DateTime( $datetime );
			}
            if( $force_timezone && $force_timezone !='' ){
                try{
                    $datetime->setTimezone(new DateTimeZone( $force_timezone ) );
                }catch ( Exception $ee ){ }
            }
            return $datetime->format( 'Y-m-d H:i:s' );
        }
        catch ( Exception $e ) {
            return $datetime;
        }
    }
}
