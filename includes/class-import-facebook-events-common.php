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
		add_action( 'admin_init', array( $this, 'ife_check_if_access_token_invalidated' ) );
		add_action( 'admin_init', array( $this, 'ife_check_for_minimum_pro_version' ) );
		add_action( 'ife_render_pro_notice', array( $this, 'render_pro_notice' ) );
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function render_import_into_and_taxonomy( $selected = '', $taxonomy_terms = array() ) {

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
							<option value="<?php echo $slug;?>" <?php selected( $selected, $slug ); ?> ><?php echo $name; ?></option>
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
				<?php 
				$taxo_cats = $taxo_tags = '';
				if( !empty( $taxonomy_terms ) && isset( $taxonomy_terms['cats'] ) ){
					$taxo_cats = implode(',', $taxonomy_terms['cats'] );
				}
				if( !empty( $taxonomy_terms ) && isset( $taxonomy_terms['tags'] ) ){
					$taxo_tags = implode(',', $taxonomy_terms['tags'] );
				}
				?>
				<input type="hidden" id="ife_taxo_cats" value="<?php echo $taxo_cats;?>">
				<input type="hidden" id="ife_taxo_tags" value="<?php echo $taxo_tags;?>">
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
		$taxo_cats = $taxo_tags = array();
		if( isset( $_REQUEST['taxo_cats'] ) ){
			$taxo_cats = explode(',', sanitize_text_field($_REQUEST['taxo_cats']) );
		}
		if( isset( $_REQUEST['taxo_tags'] ) ){
			$taxo_tags = explode(',', sanitize_text_field($_REQUEST['taxo_tags']) );
		}
		$event_taxonomy = $event_tag_taxonomy = '';
		if( !empty( $event_plugin ) ){
			$event_taxonomy = $ife_events->$event_plugin->get_taxonomy();
		}

		/**
		 * Import into tag Supported plugins.
		 */
		$tag_supported_plugins = apply_filters( 'ife_tag_supported_plugins', array('ife', 'em', 'tec' ) );
		if( in_array( $event_plugin, $tag_supported_plugins ) ){
			$event_tag_taxonomy = $ife_events->$event_plugin->get_tag_taxonomy();
		}

		// Event Taxonomy
		$terms = array();
		if ( $event_taxonomy != '' ) {
			if( taxonomy_exists( $event_taxonomy ) ){
				$terms = get_terms( $event_taxonomy, array( 'hide_empty' => false ) );
			}
		}
		if( ! empty( $terms ) ){ ?>
			<?php if( in_array( $event_plugin, $tag_supported_plugins ) && ife_is_pro() ){ ?>
				<strong style="display: block;margin: 5px 0px;">
					<?php _e( 'Event Categories:', 'import-facebook-events' );?>
				</strong>
			<?php } ?>
			<select name="event_cats[]" multiple="multiple">
		        <?php foreach ($terms as $term ) { ?>
					<option value="<?php echo $term->term_id; ?>" <?php if( in_array( $term->term_id, $taxo_cats ) ){ echo 'selected="selected"'; } ?> >
	                	<?php echo $term->name; ?>                                	
	                </option>
				<?php } ?> 
			</select>
			<?php
		}

		// Event Tag Taxonomy
		$tag_terms = array();
		if ( $event_tag_taxonomy != '' ) {
			if( taxonomy_exists( $event_tag_taxonomy ) ){
				$tag_terms = get_terms( $event_tag_taxonomy, array( 'hide_empty' => false ) );
			}
		}

		if( ! empty( $tag_terms ) && ife_is_pro() ){ ?>
			<?php if( in_array( $event_plugin, $tag_supported_plugins ) ){ ?>
				<strong style="display: block;margin: 5px 0px;">
					<?php _e( 'Event Tags:', 'import-facebook-events' );?>
				</strong>
			<?php } ?>
			<select name="event_tags[]" multiple="multiple">
		        <?php foreach ($tag_terms as $tag_term ) { ?>
					<option value="<?php echo $tag_term->term_id; ?>" <?php if( in_array( $tag_term->term_id, $taxo_tags ) ){ echo 'selected="selected"'; } ?>>
	                	<?php echo $tag_term->name; ?>                               	
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

		// check Event Espresso (EE4)
		if ( defined( 'EVENT_ESPRESSO_VERSION' ) &&  defined( 'EVENT_ESPRESSO_MAIN_FILE' ) ) {
			$supported_plugins['ee4'] = __( 'Event Espresso (EE4)', 'import-facebook-events' );
		}

		$supported_plugins['ife'] = __( 'Facebook Events', 'import-facebook-events' );
		$supported_plugins = apply_filters( 'ife_supported_plugins', $supported_plugins );
		return $supported_plugins;
	}

	/**
	 * Setup Featured image to events
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @param int $image_url Image URL
	 * @return attachment_id
	 */
	public function setup_featured_image_to_event( $event_id, $image_url = '' ) {

		if ( $image_url == '' ) {
			return;
		}
		$event = get_post( $event_id );
		if( empty ( $event ) ){
			return;
		}
		
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$event_title = $event->post_title;
		//$image = media_sideload_image( $image_url, $event_id, $event_title );
		if ( ! empty( $image_url ) ) {

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $image_url, $matches );
			if ( ! $matches ) {
				return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL' ) );
			}

			$args = array(
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => array( // @codingStandardsIgnoreLine.
					array(
						'value' => $image_url,
						'key'   => '_ife_attachment_source',
					),
				),
			);
			$id = 0;
			$ids = get_posts( $args ); // @codingStandardsIgnoreLine.
			if ( $ids ) {
				$id = current( $ids );
			}
			if( $id && $id > 0 ){
				set_post_thumbnail( $event_id, $id );
				return $id;
			}

			$file_array = array();
			$file_array['name'] = $event->ID . '_image_'.basename( $matches[0] );
			
			if( has_post_thumbnail( $event_id ) ){
				$attachment_id = get_post_thumbnail_id( $event_id );
				$attach_filename = basename( get_attached_file( $attachment_id ) );
				if( $attach_filename == $file_array['name'] ){
					return $attachment_id;
				}
			}

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $image_url );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$att_id = media_handle_sideload( $file_array, $event_id, $event_title );

			// If error storing permanently, unlink.
			if ( is_wp_error( $att_id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $att_id;
			}

			if ($att_id) {
				set_post_thumbnail($event_id, $att_id);
			}

			// Save attachment source for future reference.
			update_post_meta( $att_id, '_ife_attachment_source', $image_url );

			return $att_id;
		}

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
		if ( !empty( $ife_errors ) ) {
			return;
		}

		if( empty( $import_data ) ){
			return;
		}

		$import_status = $import_ids = array();
		if( !empty( $import_data ) ){
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
		$nothing_to_import = false;
		if($created == 0 && $updated == 0 && $skipped == 0 ){
			$nothing_to_import = true;
		}
		
		if( $created > 0 || $updated > 0 || $skipped >0 || $nothing_to_import) {
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
				update_post_meta( $insert, 'nothing_to_import', $nothing_to_import );
				update_post_meta( $insert, 'imported_data', $import_data );
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

		if( !empty( $event_import_into ) ){
			$import_result = $ife_events->$event_import_into->import_event( $centralize_array, $event_args );
		}

		return $import_result;
	}

	/**
	 * Render import Frequency
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_frequency( $selected = 'daily' ){
		?>
		<select name="import_frequency" class="import_frequency" <?php if( !ife_is_pro()){ echo 'disabled="disabled"'; } ?> >
	        <option value='hourly' <?php selected( $selected, 'hourly' ); ?>>
	            <?php esc_html_e( 'Once Hourly','import-facebook-events' ); ?>
	        </option>
	        <option value='twicedaily' <?php selected( $selected, 'twicedaily' ); ?>>
	            <?php esc_html_e( 'Twice Daily','import-facebook-events' ); ?>
	        </option>
	        <option value="daily" <?php selected( $selected, 'daily' ); ?> >
	            <?php esc_html_e( 'Once Daily','import-facebook-events' ); ?>
	        </option>
	        <option value="weekly" <?php selected( $selected, 'weekly' ); ?>>
	            <?php esc_html_e( 'Once Weekly','import-facebook-events' ); ?>
	        </option>
	        <option value="monthly" <?php selected( $selected, 'monthly' ); ?>>
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
		<select name="import_type" id="import_type"  <?php if( !ife_is_pro()){ echo 'disabled="disabled"'; } ?> >
	    	<option value="onetime" ><?php esc_attr_e( 'One-time Import','import-facebook-events' ); ?></option>
	    	<option value="scheduled"  <?php if( !ife_is_pro()){ echo 'disabled="disabled"  selected="selected"'; } ?> ><?php esc_attr_e( 'Scheduled Import','import-facebook-events' ); ?></option>
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
	function render_eventstatus_input( $selected = 'publish' ) {
		?>
		<tr class="event_status_wrapper">
			<th scope="row">
				<?php esc_attr_e( 'Status','import-facebook-events' ); ?> :
			</th>
			<td>
				<select name="event_status" >
	                <option value="publish" <?php selected( $selected, 'publish' ); ?>>
	                    <?php esc_html_e( 'Published','import-facebook-events' ); ?>
	                </option>
	                <option value="pending" <?php selected( $selected, 'pending' ); ?>>
	                    <?php esc_html_e( 'Pending','import-facebook-events' ); ?>
	                </option>
	                <option value="draft" <?php selected( $selected, 'draft' ); ?>>
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
	 * Check for user have Authorized user Token
	 *
	 * @since    1.2
	 * @return /boolean
	 */
	public function has_authorized_user_token() {
		$ife_user_token_options = get_option( 'ife_user_token_options', array() );
		if( !empty( $ife_user_token_options ) ){
			$authorize_status =	isset( $ife_user_token_options['authorize_status'] ) ? $ife_user_token_options['authorize_status'] : 0;
			$access_token = isset( $ife_user_token_options['access_token'] ) ? $ife_user_token_options['access_token'] : '';
			if( 1 == $authorize_status && $access_token != '' ){
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if user has minimum pro version.
	 *
	 * @since    1.6
	 * @return /boolean
	 */
	public function ife_check_for_minimum_pro_version(){
		if( defined('IFEPRO_VERSION') ){
			if ( version_compare( IFEPRO_VERSION, IFE_MIN_PRO_VERSION, '<' ) ) {
				global $ife_warnings;
				$ife_warnings[] = __( 'Your current "Import Facebok Event Pro" add-on is not competible with Free plugin. Please Upgrade Pro latest to work event importing Flawlessly.', 'import-facebook-events' );
			}
		}
	}

	/**
	 * Check if user access token has beed invalidated.
	 *
	 * @since    1.2
	 * @return /boolean
	 */
	public function ife_check_if_access_token_invalidated() {
		global $ife_warnings;
		$ife_user_token_options = get_option( 'ife_user_token_options', array() );
		if( !empty( $ife_user_token_options ) ){
			$authorize_status =	isset( $ife_user_token_options['authorize_status'] ) ? $ife_user_token_options['authorize_status'] : 0;
			if( 0 == $authorize_status ){
				$ife_warnings[] = __( 'The Access Token has been invalidated because the user changed their password or Facebook has changed the session for security reasons. Can you please Authorize/Reauthorize your Facebook account from <strong>Facebook Import</strong> > <strong>Settings</strong>.', 'import-facebook-events');
			}
		}
	}
	
	/**
	 * Get do not update data fields
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function ife_is_updatable( $field = '' ) {
		// deprectated status & category update feature.
		return false;
	}

	/**
	 * Display upgrade to pro notice in form.
	 *
	 * @since 1.0.0
	 */
	public function render_pro_notice(){
		if( !ife_is_pro() ){
			?>
			<span class="ife_small">
		        <?php printf( '<span style="color: red">%s</span> <a href="' . IFE_PLUGIN_BUY_NOW_URL. '" target="_blank" >%s</a>', __( 'Available in Pro version.', 'import-facebook-events' ), __( 'Upgrade to PRO', 'import-facebook-events' ) ); ?>
		    </span>
			<?php
		}
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

/**
 * Check is pro active or not.
 *
 * @since  1.5.0
 * @return boolean
 */
function ife_is_pro(){
	if( !function_exists( 'is_plugin_active' ) ){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	if ( is_plugin_active( 'import-facebook-events-pro/import-facebook-events-pro.php' ) ) {
		return true;
	}
	return false;
}


/**
 * Template Functions
 *
 * Template functions specifically created for Event Listings
 *
 * @author 		Dharmesh Patel
 * @version     1.5.0
 */

/**
 * Gets and includes template files.
 *
 * @since 1.5.0
 * @param mixed  $template_name
 * @param array  $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function get_ife_template( $template_name, $args = array(), $template_path = 'import-facebook-events', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}
	include( locate_ife_template( $template_name, $template_path, $default_path ) );
}

/**
 * Locates a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @since 1.5.0
 * @param string      $template_name
 * @param string      $template_path (default: 'import-facebook-events')
 * @param string|bool $default_path (default: '') False to not load a default
 * @return string
 */
function locate_ife_template( $template_name, $template_path = 'import-facebook-events', $default_path = '' ) {
	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);
	// Get default template
	if ( ! $template && $default_path !== false ) {
		$default_path = $default_path ? $default_path : IFE_PLUGIN_DIR . '/templates/';
		if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
			$template = trailingslashit( $default_path ) . $template_name;
		}
	}
	// Return what we found
	return apply_filters( 'ife_locate_template', $template, $template_name, $template_path );
}

/**
 * Gets template part (for templates in loops).
 *
 * @since 1.0.0
 * @param string      $slug
 * @param string      $name (default: '')
 * @param string      $template_path (default: 'import-facebook-events')
 * @param string|bool $default_path (default: '') False to not load a default
 */
function get_ife_template_part( $slug, $name = '', $template_path = 'import-facebook-events', $default_path = '' ) {
	$template = '';
	if ( $name ) {
		$template = locate_ife_template( "{$slug}-{$name}.php", $template_path, $default_path );
	}
	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/import-facebook-events/slug.php
	if ( ! $template ) {
		$template = locate_ife_template( "{$slug}.php", $template_path, $default_path );
	}
	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get Batch of in-progress background imports.
 *
 * @return array $batches
 */
function ife_get_inprogress_import(){
	global $wpdb;
	$batch_query = "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '%ife_import_batch_%' ORDER BY option_id ASC";
	if ( is_multisite() ) {
		$batch_query = "SELECT * FROM {$wpdb->sitemeta} WHERE meta_key LIKE '%ife_import_batch_%' ORDER BY meta_id ASC";
	}
	$batches = $wpdb->get_results( $batch_query );
	return $batches;
}