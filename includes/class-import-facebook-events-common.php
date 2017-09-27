<?php
/**
 * Common functions class for Import Facebook Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Import_Facebook_Events_Common {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_ife_render_terms_by_plugin', array( $this, 'ife_render_terms_by_plugin' ) );
		add_action( 'ife_render_pro_notice', array( $this, 'render_pro_notice') );
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function render_import_into_and_taxonomy() {

		$active_plugins = $this->get_active_supported_event_plugins();
		?>	
		<tr class="event_plugis_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Import into','import-facebook-events' ); ?> :
			</th>
			<td>
				<select name="event_plugin" class="fb_event_plugin">
					<?php
					if( !empty( $active_plugins ) ){
						foreach ($active_plugins as $slug => $name ) {
							?>
							<option value="<?php echo $slug;?>"><?php echo $name; ?></option>
							<?php
						}
					}
					?>
	            </select>
			</td>
		</tr>

		<tr class="event_cats_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Event Categories for Event Import','import-facebook-events' ); ?> : 
			</th>
			<td>
				<div class="event_taxo_terms_wraper">

				</div>
				<span class="ife_small">
		            <?php esc_attr_e( 'These categories are assign to imported event.', 'import-facebook-events' ); ?>
		        </span>
			</td>
		</tr>
		<?php		

	}

	/**
	 * Render Taxonomy Terms based on event import into Selection.
	 *
	 * @since 1.0
	 * @return void
	 */
	function ife_render_terms_by_plugin() {
		global $ife_events;
		$event_plugin  = esc_attr( $_REQUEST['event_plugin'] );
		$event_taxonomy = '';
		switch ( $event_plugin ) {
			case 'ife':
				$event_taxonomy = $ife_events->ife->get_taxonomy();
				break;

			case 'tec':
				$event_taxonomy = $ife_events->tec->get_taxonomy();
				break;

			case 'em':
				$event_taxonomy = $ife_events->em->get_taxonomy();
				break;

			case 'eventon':
				$event_taxonomy = $ife_events->eventon->get_taxonomy();
				break;

			case 'event_organizer':
				$event_taxonomy = $ife_events->event_organizer->get_taxonomy();
				break;

			case 'aioec':
				$event_taxonomy = $ife_events->aioec->get_taxonomy();
				break;

			case 'my_calendar':
				$event_taxonomy = $ife_events->my_calendar->get_taxonomy();
				break;
			
			default:
				break;
		}
		
		$terms = array();
		if ( $event_taxonomy != '' ) {
			if( taxonomy_exists( $event_taxonomy ) ){
				$terms = get_terms( $event_taxonomy, array( 'hide_empty' => false ) );
			}
		}
		if( ! empty( $terms ) ){ ?>
			<select name="event_cats[]" multiple="multiple">
		        <?php foreach ($terms as $term ) { ?>
					<option value="<?php echo $term->term_id; ?>">
	                	<?php echo $term->name; ?>                                	
	                </option>
				<?php } ?> 
			</select>
			<?php
		}
		wp_die();
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_active_supported_event_plugins() {

		$supported_plugins = array();
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		// check The Events Calendar active or not if active add it into array.
		if( class_exists( 'Tribe__Events__Main' ) ){
			$supported_plugins['tec'] = __( 'The Events Calendar', 'import-facebook-events' );
		}

		// check Events Manager.
		if( defined( 'EM_VERSION' ) ){
			$supported_plugins['em'] = __( 'Events Manager', 'import-facebook-events' );
		}
		
		// Check event_organizer.
		if( defined( 'EVENT_ORGANISER_VER' ) &&  defined( 'EVENT_ORGANISER_DIR' ) ){
			$supported_plugins['event_organizer'] = __( 'Event Organiser', 'import-facebook-events' );
		}

		// check EventON.
		if( class_exists( 'EventON' ) ){
			$supported_plugins['eventon'] = __( 'EventON', 'import-facebook-events' );
		}

		// check All in one Event Calendar
		if( class_exists( 'Ai1ec_Event' ) ){
			$supported_plugins['aioec'] = __( 'All in one Event Calendar', 'import-facebook-events' );
		}

		// check My Calendar
		if ( is_plugin_active( 'my-calendar/my-calendar.php' ) ) {
			$supported_plugins['my_calendar'] = __( 'My Calendar', 'import-facebook-events' );
		}		
		$supported_plugins['ife'] = __( 'Facebook Events', 'import-facebook-events' );
		return $supported_plugins;
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
		$import_origin = get_post_meta( $event_id, 'ife_event_origin', true );
		$image_name = '';
		if( $import_origin != '' ){
			$image_name .= $import_origin."_";
		}
		// Add Featured Image to Post
		$image_name       .= $event->ID . '_' . $event->post_name . '_image.png';
		$upload_dir       = wp_upload_dir(); // Set upload folder
		
		// Check for event file already Exists or not.
		$params = array(
			'numberposts'   => 1,
			'post_type'     => 'attachment',
			'meta_query'    => array(
				array(
					'key'   => '_wp_attached_file',
					'value' => trim( $upload_dir['subdir'] . '/' . $image_name, '/' )
				)
			)
		);

		$existing_file = get_posts( $params );
		if ( file_exists( $upload_dir['path'] . '/' . $image_name ) && isset( $existing_file[0]->ID ) ) {
			
			$attach_id = $existing_file[0]->ID;

		}else{

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

		}

		// And finally assign featured image to post
		set_post_thumbnail( $event_id, $attach_id );
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function display_import_success_message( $import_data = array(),$import_args = array(), $schedule_post = '' ) {
		global $ife_success_msg, $ife_errors;
		if( empty( $import_data ) || !empty( $ife_errors ) ){
			return;
		}

		$import_status = $import_ids = array();
		foreach ($import_data as $key => $value) {
			if( $value['status'] == 'created'){
				$import_status['created'][] = $value;
			}elseif( $value['status'] == 'updated'){
				$import_status['updated'][] = $value;
			}elseif( $value['status'] == 'skipped'){
				$import_status['skipped'][] = $value;
			}else{

			}
			if( isset( $value['id'] ) ){
				$import_ids[] = $value['id'];
			}
		}
		$created = $updated = $skipped = 0;
		$created = isset( $import_status['created'] ) ? count( $import_status['created'] ) : 0;
		$updated = isset( $import_status['updated'] ) ? count( $import_status['updated'] ) : 0;
		$skipped = isset( $import_status['skipped'] ) ? count( $import_status['skipped'] ) : 0;
		
		$success_message = esc_html__( 'Event(s) are imported successfully.', 'import-facebook-events' )."<br>";
		if( $created > 0 ){
			$success_message .= "<strong>".sprintf( __( '%d Created', 'import-facebook-events' ), $created )."</strong><br>";
		}
		if( $updated > 0 ){
			$success_message .= "<strong>".sprintf( __( '%d Updated', 'import-facebook-events' ), $updated )."</strong><br>";
		}
		if( $skipped > 0 ){
			$success_message .= "<strong>".sprintf( __( '%d Skipped (Already exists)', 'import-facebook-events' ), $skipped ) ."</strong><br>";
		}
		$ife_success_msg[] = $success_message;

		if( $schedule_post != '' && $schedule_post > 0 ){
			$temp_title = get_the_title( $schedule_post );
		}else{
			$temp_title = 'Manual Import';
		}
		
		if( $created > 0 || $updated > 0 || $skipped >0 ){
			$insert_args = array(
				'post_type'   => 'ife_import_history',
				'post_status' => 'publish',
				'post_title'  => $temp_title . " - ".ucfirst( $import_args["import_origin"]),
			);
			
			$insert = wp_insert_post( $insert_args, true );
			if ( !is_wp_error( $insert ) ) {
				update_post_meta( $insert, 'import_origin', $import_args["import_origin"] );
				update_post_meta( $insert, 'created', $created );
				update_post_meta( $insert, 'updated', $updated );
				update_post_meta( $insert, 'skipped', $skipped );
				update_post_meta( $insert, 'import_data', $import_args );
				if( $schedule_post != '' && $schedule_post > 0 ){
					update_post_meta( $insert, 'schedule_import_id', $schedule_post );
				}
			}	
		}				
	}

	/**
	 * Get Import events into selected destination.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function import_events_into( $centralize_array, $event_args ){
		global $ife_events;
		$import_result = array();
		$import_origin = isset( $event_args['import_origin'] ) ? $event_args['import_origin'] : '';
		$event_import_into = isset( $event_args['import_into'] ) ?  $event_args['import_into'] : '';

		if ( $event_import_into == '' ) {
			if ( $import_origin == 'facebook_tec' ) {
				$event_import_into = 'tec';
			} elseif ( $import_origin == 'facebook_em' ) {
				$event_import_into = 'em';
			} else {
				$event_import_into = 'tec';
			}
		}

		switch ( $event_import_into ) {
			case 'ife':
				$import_result = $ife_events->ife->import_event( $centralize_array, $event_args );
				break;

			case 'tec':
				$import_result = $ife_events->tec->import_event( $centralize_array, $event_args );
				break;

			case 'em':
				$import_result = $ife_events->em->import_event( $centralize_array, $event_args );
				break;

			case 'eventon':
				$import_result = $ife_events->eventon->import_event( $centralize_array, $event_args );
				break;

			case 'event_organizer':
				$import_result = $ife_events->event_organizer->import_event( $centralize_array, $event_args );
				break;

			case 'aioec':
				$import_result = $ife_events->aioec->import_event( $centralize_array, $event_args );
				break;

			case 'my_calendar':
				$import_result = $ife_events->my_calendar->import_event( $centralize_array, $event_args );
				break;
		
			default:
				break;
		}
		return $import_result;
	}

	/**
	 * Render import Frequency
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_frequency(){
		?>
		<select name="import_frequency" class="import_frequency" disabled="disabled" >
	        <option value='hourly'>
	            <?php esc_html_e( 'Once Hourly','import-facebook-events' ); ?>
	        </option>
	        <option value='twicedaily'>
	            <?php esc_html_e( 'Twice Daily','import-facebook-events' ); ?>
	        </option>
	        <option value="daily" selected="selected">
	            <?php esc_html_e( 'Once Daily','import-facebook-events' ); ?>
	        </option>
	        <option value="weekly" >
	            <?php esc_html_e( 'Once Weekly','import-facebook-events' ); ?>
	        </option>
	        <option value="monthly">
	            <?php esc_html_e( 'Once a Month','import-facebook-events' ); ?>
	        </option>
	    </select>
		<?php
	}

	/**
	 * Render import type, one time or scheduled
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_type(){
		?>
		<select name="import_type" id="import_type" disabled="disabled">
	    	<option value="onetime" disabled="disabled" ><?php esc_attr_e( 'One-time Import','import-facebook-events' ); ?></option>
	    	<option value="scheduled" disabled="disabled" selected="selected" ><?php esc_attr_e( 'Scheduled Import','import-facebook-events' ); ?></option>
	    </select>
	    <span class="hide_frequency">
	    	<?php $this->render_import_frequency(); ?>
	    </span>
	    <?php
	    do_action( 'ife_render_pro_notice' );
	}

	/**
	 * Clean URL.
	 *
	 * @since 1.0.0
	 */
	function clean_url( $url ) {
		
		$url = str_replace( '&amp;#038;', '&', $url );
		$url = str_replace( '&#038;', '&', $url );
		return $url;
		
	}

	/**
	 * Get UTC offset
	 *
	 * @since    1.0.0
	 */
	function get_utc_offset( $datetime ) {
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

	/**
	 * Render dropdown for Imported event status.
	 *
	 * @since 1.0
	 * @return void
	 */
	function render_eventstatus_input() {
		?>
		<tr class="event_status_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Status','import-facebook-events' ); ?> :
			</th>
			<td>
				<select name="event_status" >
	                <option value="publish">
	                    <?php esc_html_e( 'Published','import-facebook-events' ); ?>
	                </option>
	                <option value="pending">
	                    <?php esc_html_e( 'Pending','import-facebook-events' ); ?>
	                </option>
	                <option value="draft">
	                    <?php esc_html_e( 'Draft','import-facebook-events' ); ?>
	                </option>
	            </select>
			</td>
		</tr>
		<?php
	}

	/**
	 * remove query string from URL.
	 *
	 * @since 1.0.0
	 */
	function convert_datetime_to_db_datetime( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
			return $datetime->format( 'Y-m-d H:i:s' );
		}
		catch ( Exception $e ) {
			return $datetime;
		}
	}

	/**
	 * Check for Existing Event
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $post_type, $event_id ) {
		$event_args = array(
			'post_type' => $post_type,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'suppress_filters' => true,
			'meta_key'   => 'ife_facebook_event_id',
			'meta_value' => $event_id,
		);
		if( $post_type == 'tribe_events' && class_exists( 'Tribe__Events__Query' ) ){
			remove_action( 'pre_get_posts', array( 'Tribe__Events__Query', 'pre_get_posts' ), 50 );	
		}		
		$events = new WP_Query( $event_args );
		if( $post_type == 'tribe_events' && class_exists( 'Tribe__Events__Query' ) ){
			add_action( 'pre_get_posts', array( 'Tribe__Events__Query', 'pre_get_posts' ), 50 );
		}		
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
	 * Display upgrade to pro notice in form.
	 *
	 * @since 1.0.0
	 */
	public function render_pro_notice(){
		?>
		<span class="ife_small">
	        <?php printf( '<span style="color: red">%s</span> <a href="' . IFE_PLUGIN_BUY_NOW_URL. '" target="_blank" >%s</a>', __( 'Available in Pro version.', 'import-facebook-events' ), __( 'Upgrade to PRO', 'import-facebook-events' ) ); ?>
	    </span>
		<?php
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function ife_get_country_code( $country ) {
		if ( $country == '' ) {
			return '';
		}

		$countries = array(
		    'AF' => 'AFGHANISTAN',
		    'AL' => 'ALBANIA',
		    'DZ' => 'ALGERIA',
		    'AS' => 'AMERICAN SAMOA',
		    'AD' => 'ANDORRA',
		    'AO' => 'ANGOLA',
		    'AI' => 'ANGUILLA',
		    'AQ' => 'ANTARCTICA',
		    'AG' => 'ANTIGUA AND BARBUDA',
		    'AR' => 'ARGENTINA',
		    'AM' => 'ARMENIA',
		    'AW' => 'ARUBA',
		    'AU' => 'AUSTRALIA',
		    'AT' => 'AUSTRIA',
		    'AZ' => 'AZERBAIJAN',
		    'BS' => 'BAHAMAS',
		    'BH' => 'BAHRAIN',
		    'BD' => 'BANGLADESH',
		    'BB' => 'BARBADOS',
		    'BY' => 'BELARUS',
		    'BE' => 'BELGIUM',
		    'BZ' => 'BELIZE',
		    'BJ' => 'BENIN',
		    'BM' => 'BERMUDA',
		    'BT' => 'BHUTAN',
		    'BO' => 'BOLIVIA',
		    'BA' => 'BOSNIA AND HERZEGOVINA',
		    'BW' => 'BOTSWANA',
		    'BV' => 'BOUVET ISLAND',
		    'BR' => 'BRAZIL',
		    'IO' => 'BRITISH INDIAN OCEAN TERRITORY',
		    'BN' => 'BRUNEI DARUSSALAM',
		    'BG' => 'BULGARIA',
		    'BF' => 'BURKINA FASO',
		    'BI' => 'BURUNDI',
		    'KH' => 'CAMBODIA',
		    'CM' => 'CAMEROON',
		    'CA' => 'CANADA',
		    'CV' => 'CAPE VERDE',
		    'KY' => 'CAYMAN ISLANDS',
		    'CF' => 'CENTRAL AFRICAN REPUBLIC',
		    'TD' => 'CHAD',
		    'CL' => 'CHILE',
		    'CN' => 'CHINA',
		    'CX' => 'CHRISTMAS ISLAND',
		    'CC' => 'COCOS (KEELING) ISLANDS',
		    'CO' => 'COLOMBIA',
		    'KM' => 'COMOROS',
		    'CG' => 'CONGO',
		    'CD' => 'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
		    'CK' => 'COOK ISLANDS',
		    'CR' => 'COSTA RICA',
		    'CI' => 'COTE D IVOIRE',
		    'HR' => 'CROATIA',
		    'CU' => 'CUBA',
		    'CY' => 'CYPRUS',
		    'CZ' => 'CZECH REPUBLIC',
		    'DK' => 'DENMARK',
		    'DJ' => 'DJIBOUTI',
		    'DM' => 'DOMINICA',
		    'DO' => 'DOMINICAN REPUBLIC',
		    'TP' => 'EAST TIMOR',
		    'EC' => 'ECUADOR',
		    'EG' => 'EGYPT',
		    'SV' => 'EL SALVADOR',
		    'GQ' => 'EQUATORIAL GUINEA',
		    'ER' => 'ERITREA',
		    'EE' => 'ESTONIA',
		    'ET' => 'ETHIOPIA',
		    'FK' => 'FALKLAND ISLANDS (MALVINAS)',
		    'FO' => 'FAROE ISLANDS',
		    'FJ' => 'FIJI',
		    'FI' => 'FINLAND',
		    'FR' => 'FRANCE',
		    'GF' => 'FRENCH GUIANA',
		    'PF' => 'FRENCH POLYNESIA',
		    'TF' => 'FRENCH SOUTHERN TERRITORIES',
		    'GA' => 'GABON',
		    'GM' => 'GAMBIA',
		    'GE' => 'GEORGIA',
		    'DE' => 'GERMANY',
		    'GH' => 'GHANA',
		    'GI' => 'GIBRALTAR',
		    'GR' => 'GREECE',
		    'GL' => 'GREENLAND',
		    'GD' => 'GRENADA',
		    'GP' => 'GUADELOUPE',
		    'GU' => 'GUAM',
		    'GT' => 'GUATEMALA',
		    'GN' => 'GUINEA',
		    'GW' => 'GUINEA-BISSAU',
		    'GY' => 'GUYANA',
		    'HT' => 'HAITI',
		    'HM' => 'HEARD ISLAND AND MCDONALD ISLANDS',
		    'VA' => 'HOLY SEE (VATICAN CITY STATE)',
		    'HN' => 'HONDURAS',
		    'HK' => 'HONG KONG',
		    'HU' => 'HUNGARY',
		    'IS' => 'ICELAND',
		    'IN' => 'INDIA',
		    'ID' => 'INDONESIA',
		    'IR' => 'IRAN, ISLAMIC REPUBLIC OF',
		    'IQ' => 'IRAQ',
		    'IE' => 'IRELAND',
		    'IL' => 'ISRAEL',
		    'IT' => 'ITALY',
		    'JM' => 'JAMAICA',
		    'JP' => 'JAPAN',
		    'JO' => 'JORDAN',
		    'KZ' => 'KAZAKSTAN',
		    'KE' => 'KENYA',
		    'KI' => 'KIRIBATI',
		    'KP' => 'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
		    'KR' => 'KOREA REPUBLIC OF',
		    'KW' => 'KUWAIT',
		    'KG' => 'KYRGYZSTAN',
		    'LA' => 'LAO PEOPLES DEMOCRATIC REPUBLIC',
		    'LV' => 'LATVIA',
		    'LB' => 'LEBANON',
		    'LS' => 'LESOTHO',
		    'LR' => 'LIBERIA',
		    'LY' => 'LIBYAN ARAB JAMAHIRIYA',
		    'LI' => 'LIECHTENSTEIN',
		    'LT' => 'LITHUANIA',
		    'LU' => 'LUXEMBOURG',
		    'MO' => 'MACAU',
		    'MK' => 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
		    'MG' => 'MADAGASCAR',
		    'MW' => 'MALAWI',
		    'MY' => 'MALAYSIA',
		    'MV' => 'MALDIVES',
		    'ML' => 'MALI',
		    'MT' => 'MALTA',
		    'MH' => 'MARSHALL ISLANDS',
		    'MQ' => 'MARTINIQUE',
		    'MR' => 'MAURITANIA',
		    'MU' => 'MAURITIUS',
		    'YT' => 'MAYOTTE',
		    'MX' => 'MEXICO',
		    'FM' => 'MICRONESIA, FEDERATED STATES OF',
		    'MD' => 'MOLDOVA, REPUBLIC OF',
		    'MC' => 'MONACO',
		    'MN' => 'MONGOLIA',
		    'MS' => 'MONTSERRAT',
		    'MA' => 'MOROCCO',
		    'MZ' => 'MOZAMBIQUE',
		    'MM' => 'MYANMAR',
		    'NA' => 'NAMIBIA',
		    'NR' => 'NAURU',
		    'NP' => 'NEPAL',
		    'NL' => 'NETHERLANDS',
		    'AN' => 'NETHERLANDS ANTILLES',
		    'NC' => 'NEW CALEDONIA',
		    'NZ' => 'NEW ZEALAND',
		    'NI' => 'NICARAGUA',
		    'NE' => 'NIGER',
		    'NG' => 'NIGERIA',
		    'NU' => 'NIUE',
		    'NF' => 'NORFOLK ISLAND',
		    'MP' => 'NORTHERN MARIANA ISLANDS',
		    'NO' => 'NORWAY',
		    'OM' => 'OMAN',
		    'PK' => 'PAKISTAN',
		    'PW' => 'PALAU',
		    'PS' => 'PALESTINIAN TERRITORY, OCCUPIED',
		    'PA' => 'PANAMA',
		    'PG' => 'PAPUA NEW GUINEA',
		    'PY' => 'PARAGUAY',
		    'PE' => 'PERU',
		    'PH' => 'PHILIPPINES',
		    'PN' => 'PITCAIRN',
		    'PL' => 'POLAND',
		    'PT' => 'PORTUGAL',
		    'PR' => 'PUERTO RICO',
		    'QA' => 'QATAR',
		    'RE' => 'REUNION',
		    'RO' => 'ROMANIA',
		    'RU' => 'RUSSIAN FEDERATION',
		    'RW' => 'RWANDA',
		    'SH' => 'SAINT HELENA',
		    'KN' => 'SAINT KITTS AND NEVIS',
		    'LC' => 'SAINT LUCIA',
		    'PM' => 'SAINT PIERRE AND MIQUELON',
		    'VC' => 'SAINT VINCENT AND THE GRENADINES',
		    'WS' => 'SAMOA',
		    'SM' => 'SAN MARINO',
		    'ST' => 'SAO TOME AND PRINCIPE',
		    'SA' => 'SAUDI ARABIA',
		    'SN' => 'SENEGAL',
		    'SC' => 'SEYCHELLES',
		    'SL' => 'SIERRA LEONE',
		    'SG' => 'SINGAPORE',
		    'SK' => 'SLOVAKIA',
		    'SI' => 'SLOVENIA',
		    'SB' => 'SOLOMON ISLANDS',
		    'SO' => 'SOMALIA',
		    'ZA' => 'SOUTH AFRICA',
		    'GS' => 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
		    'ES' => 'SPAIN',
		    'LK' => 'SRI LANKA',
		    'SD' => 'SUDAN',
		    'SR' => 'SURINAME',
		    'SJ' => 'SVALBARD AND JAN MAYEN',
		    'SZ' => 'SWAZILAND',
		    'SE' => 'SWEDEN',
		    'CH' => 'SWITZERLAND',
		    'SY' => 'SYRIAN ARAB REPUBLIC',
		    'TW' => 'TAIWAN, PROVINCE OF CHINA',
		    'TJ' => 'TAJIKISTAN',
		    'TZ' => 'TANZANIA, UNITED REPUBLIC OF',
		    'TH' => 'THAILAND',
		    'TG' => 'TOGO',
		    'TK' => 'TOKELAU',
		    'TO' => 'TONGA',
		    'TT' => 'TRINIDAD AND TOBAGO',
		    'TN' => 'TUNISIA',
		    'TR' => 'TURKEY',
		    'TM' => 'TURKMENISTAN',
		    'TC' => 'TURKS AND CAICOS ISLANDS',
		    'TV' => 'TUVALU',
		    'UG' => 'UGANDA',
		    'UA' => 'UKRAINE',
		    'AE' => 'UNITED ARAB EMIRATES',
		    'GB' => 'UNITED KINGDOM',
		    'US' => 'UNITED STATES',
		    'UM' => 'UNITED STATES MINOR OUTLYING ISLANDS',
		    'UY' => 'URUGUAY',
		    'UZ' => 'UZBEKISTAN',
		    'VU' => 'VANUATU',
		    'VE' => 'VENEZUELA',
		    'VN' => 'VIET NAM',
		    'VG' => 'VIRGIN ISLANDS, BRITISH',
		    'VI' => 'VIRGIN ISLANDS, U.S.',
		    'WF' => 'WALLIS AND FUTUNA',
		    'EH' => 'WESTERN SAHARA',
		    'YE' => 'YEMEN',
		    'YU' => 'YUGOSLAVIA',
		    'ZM' => 'ZAMBIA',
		    'ZW' => 'ZIMBABWE',
		  );

		foreach ( $countries as $code => $name ) {
			if ( strtoupper( $country ) == $name ) {
				return $code;
			}
		}
		return $country;
	}
}
