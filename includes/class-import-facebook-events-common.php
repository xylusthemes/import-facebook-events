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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Common functionality of the plugin.
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
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
		add_action( 'admin_init', array( $this, 'ife_redirect_after_activation' ) );
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param string $selected Selected plugin.
	 * @param array  $taxonomy_terms Taxonomy Terms.
	 * @return void
	 */
	public function render_import_into_and_taxonomy( $selected = '', $taxonomy_terms = array() ) {

		$active_plugins = $this->get_active_supported_event_plugins();
		?>
		<div class="ife-inner-main-section event_plugis_wrapper"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Import into', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<select name="event_plugin" class="fb_event_plugin">
					<?php
					if ( ! empty( $active_plugins ) ) {
						foreach ( $active_plugins as $slug => $name ) {
							?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected, $slug ); ?> ><?php echo esc_attr( $name ); ?></option>
							<?php
						}
					}
					?>
				</select>
			</div>
		</div>

		<div class="ife-inner-main-section event_plugis_wrapper"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" >
					<?php esc_attr_e( 'Event Categories for Event Import', 'import-facebook-events' ); ?>
					<span class="ife-tooltip">
						<div>
							<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="ife-circle-question-mark">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
							</svg>
							<span class="ife-popper">
								<?php esc_attr_e( 'These categories are assign to imported event.', 'import-facebook-events' ); ?>
								<div class="ife-popper__arrow"></div>
							</span>
						</div>
					</span>
				</span>
			</div>
			<div class="ife-inner-section-2">
				<?php
				$taxo_cats = '';
				$taxo_tags = '';
				if ( ! empty( $taxonomy_terms ) && isset( $taxonomy_terms['cats'] ) ) {
					$taxo_cats = implode( ',', $taxonomy_terms['cats'] );
				}
				if ( ! empty( $taxonomy_terms ) && isset( $taxonomy_terms['tags'] ) ) {
					$taxo_tags = implode( ',', $taxonomy_terms['tags'] );
				}
				?>
				<input type="hidden" id="ife_taxo_cats" value="<?php echo esc_attr( $taxo_cats ); ?>">
				<input type="hidden" id="ife_taxo_tags" value="<?php echo esc_attr( $taxo_tags ); ?>">
				<div class="event_taxo_terms_wraper" ></div>
			</div>
		</div>
		<?php

	}

	/**
	 * Redirect after activate the plugin.
	 */
	public function ife_redirect_after_activation() {
		if ( get_option( 'ife_plugin_activated' ) ) {
			delete_option( 'ife_plugin_activated' );
			wp_safe_redirect( admin_url( 'admin.php?page=facebook_import&tab=ife_setup_wizard' ) );
			exit;
		}
	}

	/**
	 * Render Taxonomy Terms based on event import into Selection.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function ife_render_terms_by_plugin() {
		// Check nonce.
		check_ajax_referer( 'ife_admin_js_nonce', 'security' );
		global $ife_events;
		$event_taxonomy     = '';
		$event_tag_taxonomy = '';
		$event_plugin = isset( $_POST['event_plugin'] ) ? sanitize_text_field( wp_unslash( $_POST['event_plugin'] ) ) : 'ife'; // input var okay.
		$taxo_cats    = isset( $_POST['taxo_cats'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST ['taxo_cats'] ) ) ) : array(); // input var okay.
		$taxo_tags    = isset( $_POST['taxo_tags'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['taxo_tags'] ) ) ) : array(); // input var okay.

		if ( ! empty( $event_plugin ) ) {
			$event_taxonomy = $ife_events->$event_plugin->get_taxonomy();
		}

		/**
		 * Import into tag Supported plugins.
		 */
		$tag_supported_plugins = apply_filters( 'ife_tag_supported_plugins', array( 'ife', 'em', 'tec' ) );
		if ( in_array( $event_plugin, $tag_supported_plugins, true ) ) {
			$event_tag_taxonomy = $ife_events->$event_plugin->get_tag_taxonomy();
		}

		// Event Taxonomy.
		$terms = array();
		if ( ! empty( $event_taxonomy ) ) {
			if ( taxonomy_exists( $event_taxonomy ) ) {
				$terms = get_terms( array( 'taxonomy' => $event_taxonomy, 'hide_empty' => false, ) );
			}
		}
		if ( ! empty( $terms ) ) {
			?>
			<?php if ( ife_is_pro() ) { ?>
				<div style="width: 45%;">
					<strong style="display: block;margin-bottom: 5px;">
						<?php esc_attr_e( 'Event Categories:', 'import-facebook-events' ); ?>
					</strong>
					<?php
					$taxo_cats = array_map( 'absint', $taxo_cats );
					$taxo_tags = array_map( 'absint', $taxo_tags );
					?>
					<select name="event_cats[]" class="ife_taxo_tag_multiple_select"  style="width: 100%;" multiple>
						<?php foreach ( $terms as $term ) { ?>
							<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo( ( in_array( $term->term_id, $taxo_cats, true ) ) ? 'selected="selected"' : '' ); ?> ><?php echo esc_attr( $term->name ); ?></option>
						<?php } ?>
					</select>
				</div>
				<?php
			}
		}

		// Event Tag Taxonomy.
		$tag_terms = array();
		if ( ! empty( $event_tag_taxonomy ) ) {
			if ( taxonomy_exists( $event_tag_taxonomy ) ) {
				$tag_terms = get_terms( array( 'taxonomy' => $event_tag_taxonomy, 'hide_empty' => false, ) );
			}
		}

		if ( ! empty( $tag_terms ) && ife_is_pro() ) {
			?>
			<?php 
			if ( in_array( $event_plugin, $tag_supported_plugins, true ) ) { ?>
				<div style="width: 45%;" >
					<strong style="display: block;margin: 5px 0px;">
						<?php esc_attr_e( 'Event Tags:', 'import-facebook-events' ); ?>
					</strong>
					<select name="event_tags[]"  class="ife_taxo_tag_multiple_select" style="width: 100%;" multiple>
						<?php foreach ( $tag_terms as $tag_term ) { ?>
							<option value="<?php echo esc_attr( $tag_term->term_id ); ?>" <?php echo( ( in_array( $tag_term->term_id, $taxo_tags, true ) ) ? 'selected="selected"' : '' ); ?> >
								<?php echo esc_attr( $tag_term->name ); ?>
							</option>
						<?php } ?>
					</select>
				</div>
			<?php
			}
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
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// check The Events Calendar active or not if active add it into array.
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$supported_plugins['tec'] = __( 'The Events Calendar', 'import-facebook-events' );
		}

		// check Events Manager.
		if ( defined( 'EM_VERSION' ) ) {
			$supported_plugins['em'] = __( 'Events Manager', 'import-facebook-events' );
		}

		// check EventPrime.
		if ( class_exists( 'Eventprime_Event_Calendar_Management_Admin' ) ) {
			$supported_plugins['eventprime'] = __( 'EventPrime', 'import-facebook-events' );
		}

		// Check event_organizer.
		if ( defined( 'EVENT_ORGANISER_VER' ) && defined( 'EVENT_ORGANISER_DIR' ) ) {
			$supported_plugins['event_organizer'] = __( 'Event Organiser', 'import-facebook-events' );
		}

		// check EventON.
		if ( class_exists( 'EventON' ) ) {
			$supported_plugins['eventon'] = __( 'EventON', 'import-facebook-events' );
		}

		// check All in one Event Calendar.
		if ( class_exists( 'Ai1ec_Event' ) ) {
			$supported_plugins['aioec'] = __( 'All in one Event Calendar', 'import-facebook-events' );
		}

		// check My Calendar.
		if ( is_plugin_active( 'my-calendar/my-calendar.php' ) ) {
			$supported_plugins['my_calendar'] = __( 'My Calendar', 'import-facebook-events' );
		}

		// check Event Espresso (EE4).
		if ( defined( 'EVENT_ESPRESSO_VERSION' ) && defined( 'EVENT_ESPRESSO_MAIN_FILE' ) ) {
			$supported_plugins['ee4'] = __( 'Event Espresso (EE4)', 'import-facebook-events' );
		}

		$supported_plugins['ife'] = __( 'Facebook Events', 'import-facebook-events' );
		$supported_plugins        = apply_filters( 'ife_supported_plugins', $supported_plugins );
		return $supported_plugins;
	}

	/**
	 * Ubnbale to hyperlink in description
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function ife_convert_text_to_hyperlink( $post_description = '' ){
		if( !empty( $post_description ) ){

			$url_pattern = '/\b(https?:\/\/[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/i';
			
			// Replace URLs with clickable links
			$post_description = preg_replace($url_pattern, '<a href="$0" target="_blank" title="$0">$0</a>', $post_description);
	
			$search  = ['  ', '_ ', ' _'];
			$replace = ['<br />', '<br />', '<br />'];
			$post_description = str_replace($search, $replace, $post_description);
		}
		return $post_description;
	}

	/**
	 * Remove the facebook event link in event desction
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function ife_remove_facebook_link_in_event_description( $post_description = '', $event_id = '' ){

		if ( !empty( $post_description ) && !empty( $event_id ) ) {
			$event_url        = 'https://www.facebook.com/events/'.$event_id.'/';
			$post_description = str_replace( $event_url, '', $post_description );
		}
		return $post_description;
	}

	/**
	 * Get event source link.
	 * 
	 * @since    1.7.1
	 * @param array  $source_data Schedule Data.
	 * @param string $source_title Schedule title.
	 */
	public function get_source_data( $source_data = array(), $source_title = '' ){
		if( $source_data['import_by'] == 'ical_url' ){
			$source = '<a href="' . $source_data['ical_url'] . '" target="_blank" >ICal URL</a>';
		}elseif( $source_data['import_by'] == 'facebook_organization' ){
			$source = '<a href="https://facebook.com/' . $source_data['page_username'] . '" target="_blank" >' . $source_title . '</a>';
		}elseif( $source_data['import_by'] == 'facebook_group' ){
			$source = '<a href="https://facebook.com/groups/' . $source_data['facebook_group_id'] . '" target="_blank" >' . $source_title . '</a>';
		}else{
			$source = '<a href="#">No Data Found</a>';
		}
		return $source;
	}

	/**
	 * Setup Featured image to events
	 *
	 * @since    1.0.0
	 * @param int    $event_id event id.
	 * @param string $image_url Image URL.
	 * @return int Attachment Id.
	 */
	public function setup_featured_image_to_event( $event_id, $image_url = '' ) {

		if ( empty( $image_url ) ) {
			return;
		}
		$event = get_post( $event_id );
		if ( empty( $event ) ) {
			return;
		}

		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$event_title = $event->post_title;
		if ( ! empty( $image_url ) ) {

			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|webp)\b/i', $image_url, $matches );
			if ( ! $matches ) {
				return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL', 'import-facebook-events' ) );
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
			$id   = 0;
			$ids = get_posts( $args ); // @codingStandardsIgnoreLine.
			if ( $ids ) {
				$id = current( $ids );
			}
			if ( $id && $id > 0 ) {
				set_post_thumbnail( $event_id, $id );
				return $id;
			}

			$image_source = strtok( $image_url, '?');
			$path_info    = pathinfo( $image_source );
			$image_name   = $path_info['basename'];
			$i_args = array(
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => array( // @codingStandardsIgnoreLine.
					array(
						'value' => $image_name,
						'key'   => '_ife_attachment_source_name',
					),
				),
			);

			$id  = 0;
			$i_ids = get_posts( $i_args ); // @codingStandardsIgnoreLine.
			if ( $i_ids ) {
				$i_id = current( $i_ids );
			}
			if ( $i_id && $i_id > 0 ) {
				set_post_thumbnail( $event_id, $i_id );
				return $i_id;
			}

			$file_array         = array();
			$file_array['name'] = $event->ID . '_image_' . basename( $matches[0] );

			if ( has_post_thumbnail( $event_id ) ) {
				$attachment_id   = get_post_thumbnail_id( $event_id );
				$attach_filename = basename( get_attached_file( $attachment_id ) );
				if ( $attach_filename === $file_array['name'] ) {
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
				@unlink( $file_array['tmp_name'] ); // @codingStandardsIgnoreLine.
				return $att_id;
			}

			if ( $att_id ) {
				set_post_thumbnail( $event_id, $att_id );
			}

			// Save attachment source for future reference.
			update_post_meta( $att_id, '_ife_attachment_source', $image_url );
			update_post_meta( $att_id, '_ife_attachment_source_name', $image_name );

			return $att_id;
		}

	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array  $import_data Event import Data.
	 * @param array  $import_args Event import args.
	 * @param string $schedule_post Scheduled Post.
	 * @return array
	 */
	public function display_import_success_message( $import_data = array(), $import_args = array(), $schedule_post = '' ) {
		global $ife_success_msg, $ife_errors;
		if ( ! empty( $ife_errors ) ) {
			return;
		}

		if ( empty( $import_data ) ) {
			return;
		}

		$import_status = array();
		$import_ids    = array();
		if ( ! empty( $import_data ) ) {
			foreach ( $import_data as $key => $value ) {
				if ( 'created' === $value['status'] ) {
					$import_status['created'][] = $value;
				} elseif ( 'updated' === $value['status'] ) {
					$import_status['updated'][] = $value;
				} elseif ( 'skipped' === $value['status'] ) {
					$import_status['skipped'][] = $value;
				} elseif ( 'skip_trash' === $value['status'] ) {
					$import_status['skip_trash'][] = $value;
				}

				if ( isset( $value['id'] ) ) {
					$import_ids[] = $value['id'];
				}
			}
		}

		$created = 0;
		$updated = 0;
		$skipped = 0;
		$skip_trash = 0;
		$created = isset( $import_status['created'] ) ? count( $import_status['created'] ) : 0;
		$updated = isset( $import_status['updated'] ) ? count( $import_status['updated'] ) : 0;
		$skipped = isset( $import_status['skipped'] ) ? count( $import_status['skipped'] ) : 0;
		$skip_trash = isset( $import_status['skip_trash'] ) ? count( $import_status['skip_trash'] ) : 0;

		$success_message = esc_html__( 'Event(s) are imported successfully.', 'import-facebook-events' ) . '<br>';
		if ( $created > 0 ) {
			// translators: %d is numbers of event created.
			$success_message .= '<strong>' . sprintf( __( '%d Created', 'import-facebook-events' ), $created ) . '</strong><br>';
		}
		if ( $updated > 0 ) {
			// translators: %d is numbers of event updated.
			$success_message .= '<strong>' . sprintf( __( '%d Updated', 'import-facebook-events' ), $updated ) . '</strong><br>';
		}
		if ( $skipped > 0 ) {
			// translators: %d is numbers of event skipped.
			$success_message .= '<strong>' . sprintf( __( '%d Skipped (Already exists)', 'import-facebook-events' ), $skipped ) . '</strong><br>';
		}
		if ( $skip_trash > 0 ) {
			// translators: %d is numbers of event skipped Trashed.
			$success_message .= '<strong>' . sprintf( __( '%d Skipped (Already exists in Trash)', 'import-facebook-events' ), $skip_trash ) . '</strong><br>';
		}
		$ife_success_msg[] = $success_message;

		if ( ! empty( $schedule_post ) && $schedule_post > 0 ) {
			$temp_title = get_the_title( $schedule_post );
		} else {
			$temp_title = esc_attr__( 'Manual Import', 'import-facebook-events' );
		}
		$nothing_to_import = false;
		if ( 0 === $created && 0 === $updated && 0 === $skipped && 0 === $skip_trash ) {
			$nothing_to_import = true;
		}

		if ( $created > 0 || $updated > 0 || $skipped > 0 || $skip_trash > 0 || $nothing_to_import ) {
			$insert_args = array(
				'post_type'   => 'ife_import_history',
				'post_status' => 'publish',
				'post_title'  => $temp_title . ' - ' . ucfirst( $import_args['import_origin'] ),
			);

			$insert = wp_insert_post( $insert_args, true );
			if ( ! is_wp_error( $insert ) ) {
				update_post_meta( $insert, 'import_origin', $import_args['import_origin'] );
				update_post_meta( $insert, 'created', $created );
				update_post_meta( $insert, 'updated', $updated );
				update_post_meta( $insert, 'skipped', $skipped );
				update_post_meta( $insert, 'skip_trash', $skip_trash );
				update_post_meta( $insert, 'nothing_to_import', $nothing_to_import );
				update_post_meta( $insert, 'imported_data', $import_data );
				update_post_meta( $insert, 'import_data', $import_args );
				if ( ! empty( $schedule_post ) && $schedule_post > 0 ) {
					update_post_meta( $insert, 'schedule_import_id', $schedule_post );
				}
			}
		}
	}

	/**
	 * Get Import events into selected destination.
	 *
	 * @since  1.0.0
	 * @param array $centralize_array Centralize event.
	 * @param array $event_args Event args.
	 * @return array
	 */
	public function import_events_into( $centralize_array, $event_args ) {
		global $ife_events;
		$import_result     = array();
		$import_origin     = isset( $event_args['import_origin'] ) ? $event_args['import_origin'] : '';
		$event_import_into = isset( $event_args['import_into'] ) ? $event_args['import_into'] : '';

		if ( empty( $event_import_into ) ) {
			if ( 'facebook_tec' === $import_origin ) {
				$event_import_into = 'tec';
			} elseif ( 'facebook_em' === $import_origin ) {
				$event_import_into = 'em';
			} else {
				$event_import_into = 'tec';
			}
		}

		if ( ! empty( $event_import_into ) ) {
			$import_result = $ife_events->$event_import_into->import_event( $centralize_array, $event_args );
		}

		return $import_result;
	}

	/**
	 * Render import Frequency
	 *
	 * @since   1.0.0
	 * @param string $selected Selected import frequency.
	 * @return  void
	 */
	public function render_import_frequency( $selected = 'daily' ) {
		?>
		<select name="import_frequency" class="import_frequency" <?php echo( ( ! ife_is_pro() ) ? 'disabled="disabled"' : '' ); ?> >
			<option value='hourly' <?php selected( $selected, 'hourly' ); ?>>
				<?php esc_html_e( 'Once Hourly', 'import-facebook-events' ); ?>
			</option>
			<option value='twicedaily' <?php selected( $selected, 'twicedaily' ); ?>>
				<?php esc_html_e( 'Twice Daily', 'import-facebook-events' ); ?>
			</option>
			<option value="daily" <?php selected( $selected, 'daily' ); ?> >
				<?php esc_html_e( 'Once Daily', 'import-facebook-events' ); ?>
			</option>
			<option value="weekly" <?php selected( $selected, 'weekly' ); ?>>
				<?php esc_html_e( 'Once Weekly', 'import-facebook-events' ); ?>
			</option>
			<option value="monthly" <?php selected( $selected, 'monthly' ); ?>>
				<?php esc_html_e( 'Once a Month', 'import-facebook-events' ); ?>
			</option>
		</select>
		<?php
	}

	/**
	 * Display schedule import source 
	 *
	 * @since   1.7.0
	 * @return  void
	 */
	function render_import_source( $schedule_eventdata = '' ){

		if( !empty( $schedule_eventdata['page_username'] ) ){
			$event_source  = $schedule_eventdata['page_username'];
			$event_origins = 'Facebook Page ID';
			$name          = 'page_username';
		}elseif( !empty( $schedule_eventdata['facebook_group_id'] ) ){
			$event_source  = $schedule_eventdata['facebook_group_id'];
			$event_origins = 'Facebook Group ID';
			$name          = 'facebook_group_id';
		}elseif( !empty( $schedule_eventdata['ical_url'] ) ){
			$event_source  = $schedule_eventdata['ical_url'];
			$event_origins = 'iCal Url';
			$name          = 'ical_url';		
		}else{
			$event_source  = '';
			$event_origins = 'Please create a new schedule after deleting this';
			$name          = '';
		}
		?>
		<td>
			<input type="text" name="<?php echo esc_attr( $name ); ?>" required="required" value="<?php echo esc_attr( $event_source ); ?>" >
			<span><?php echo esc_attr( $event_origins ); ?></span>
		</td>
		<?php
	}

	/**
	 * Render import type, one time or scheduled
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	public function render_import_type() {
		?>
		<select name="import_type" id="import_type" <?php echo( ( ! ife_is_pro() ) ? 'disabled="disabled"' : '' ); ?> >
			<option value="onetime" ><?php esc_attr_e( 'One-time Import', 'import-facebook-events' ); ?></option>
			<option value="scheduled" <?php echo( ( ! ife_is_pro() ) ? 'disabled="disabled"' : '' ); ?> ><?php esc_attr_e( 'Scheduled Import', 'import-facebook-events' ); ?></option>
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
	 * @param string $url Url.
	 * @return string $url Url.
	 */
	public function clean_url( $url ) {

		$url = str_replace( '&amp;#038;', '&', $url );
		$url = str_replace( '&#038;', '&', $url );
		return $url;

	}

	/**
	 * Get UTC offset
	 *
	 * @since    1.0.0
	 * @param string $datetime DateTime.
	 * @return string UTC Offset.
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
	
	/**
	 * Converts a given date and time in a specific timezone to a UNIX timestamp in UTC.
	 *
	 * @param string $datetime The date and time string 
	 * @param string $timezone The timezone of the given date and time
	 * @return int UNIX timestamp in UTC.
	 */
	public function ife_convert_to_utc_timestamp( $datetime, $timezone ) {
		try {
			$date = new DateTime( $datetime, new DateTimeZone( $timezone ) );
			$date->setTimezone( new DateTimeZone( 'UTC' ) );
			return $date->getTimestamp();
		} catch ( Exception $e ) {
			return 0;
		}
	}

	/**
	 * Render dropdown for Imported event status.
	 *
	 * @since 1.0
	 * @param string $selected Selected Event status.
	 * @return void
	 */
	public function render_eventstatus_input( $selected = 'publish' ) {
		?>
		<div class="ife-inner-main-section event_status_wrapper"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Status', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<select name="event_status" >
					<option value="publish" <?php selected( $selected, 'publish' ); ?>>
						<?php esc_html_e( 'Published', 'import-facebook-events' ); ?>
					</option>
					<option value="pending" <?php selected( $selected, 'pending' ); ?>>
						<?php esc_html_e( 'Pending', 'import-facebook-events' ); ?>
					</option>
					<option value="draft" <?php selected( $selected, 'draft' ); ?>>
						<?php esc_html_e( 'Draft', 'import-facebook-events' ); ?>
					</option>
				</select>
			</div>
		</div>
		<?php
	}

	function ife_get_facebook_events_counts() {
		global $wpdb;
	
		// Table names with WordPress prefix
		$posts_table    = $wpdb->prefix . 'posts';
		$postmeta_table = $wpdb->prefix . 'postmeta';
		
		// Current Unix timestamp
		$current_time = current_time( 'timestamp' );
	
		// Single query to get all counts
		$sql = "SELECT 
				COUNT( p.ID ) AS all_posts_count,
				SUM( CASE WHEN pm.meta_value > %d THEN 1 ELSE 0 END ) AS upcoming_events_count,
				SUM( CASE WHEN pm.meta_value <= %d THEN 1 ELSE 0 END ) AS past_events_count
			FROM {$posts_table} AS p
			INNER JOIN {$postmeta_table} AS pm ON p.ID = pm.post_id
			WHERE p.post_type = %s
			AND p.post_status = %s
			AND pm.meta_key = %s";

		$prepared_sql = $wpdb->prepare( $sql, $current_time, $current_time, 'facebook_events', 'publish', 'end_ts' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$counts       = $wpdb->get_row( $prepared_sql );
	
		// Return the counts as an array
		return [
			'all'      => intval( $counts->all_posts_count ),
			'upcoming' => intval( $counts->upcoming_events_count ),
			'past'     => intval( $counts->past_events_count ),
		];
	}

	/**
	 * Remove query string from URL.
	 *
	 * @since 1.0.0
	 * @param string $datetime DateTime.
	 * @return string $datetime DateTime.
	 */
	public function convert_datetime_to_db_datetime( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
			return $datetime->format( 'Y-m-d H:i:s' );
		} catch ( Exception $e ) {
			return $datetime;
		}
	}

	/**
	 * Check for Existing Event
	 *
	 * @since    1.0.0
	 * @param string $post_type Post Type.
	 * @param int    $event_id Event ID.
	 * @return boolean|int Evnet ID or false.
	 */
	public function get_event_by_event_id( $post_type, $event_id ) {
		global $wpdb;
		$ife_options = get_option( IFE_OPTIONS );
		$skip_trash = isset( $ife_options['skip_trash'] ) ? $ife_options['skip_trash'] : 'no';

		if( $skip_trash == 'yes' ){
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$get_post_id = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
					$post_type,
					'ife_facebook_event_id',
					$event_id
				)
			);
		}else{
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$get_post_id = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND ' . $wpdb->prefix . 'posts.post_status != %s AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
					$post_type,
					'trash',
					'ife_facebook_event_id',
					$event_id
				)
			);
		}

		if ( !empty( $get_post_id[0] ) ) {
			return $get_post_id[0];
		}
		return false;
	}

	/**
	 * Check for user have Authorized user Token
	 *
	 * @since  1.2
	 * @return boolean
	 */
	public function has_authorized_user_token() {
		$ife_user_token_options = get_option( 'ife_user_token_options', array() );
		if ( ! empty( $ife_user_token_options ) ) {
			$authorize_status = isset( $ife_user_token_options['authorize_status'] ) ? $ife_user_token_options['authorize_status'] : 0;
			$access_token     = isset( $ife_user_token_options['access_token'] ) ? $ife_user_token_options['access_token'] : '';
			if ( 1 === $authorize_status && ! empty( $access_token ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if user has minimum pro version.
	 *
	 * @since    1.6
	 * @return void
	 */
	public function ife_check_for_minimum_pro_version() {
		if ( defined( 'IFEPRO_VERSION' ) ) {
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
	 * @return void
	 */
	public function ife_check_if_access_token_invalidated() {
		global $ife_warnings;
		$ife_user_token_options = get_option( 'ife_user_token_options', array() );
		if ( ! empty( $ife_user_token_options ) ) {
			$authorize_status = isset( $ife_user_token_options['authorize_status'] ) ? $ife_user_token_options['authorize_status'] : 0;
			if ( 0 === $authorize_status ) {
				$settings_url = esc_url( admin_url( 'admin.php?page=facebook_import&tab=settings' ) );
				$ife_warnings[] = sprintf(
					/* translators: %s: Settings page URL */
					__( 'The Access Token has been invalidated because the user changed their password or Facebook has changed the session for security reasons. Can you please Authorize/Reauthorize your Facebook account from <strong>Facebook Import</strong> > <strong><a style="text-decoration: none;" href="%s">Settings</a></strong>.', 'import-facebook-events' ),
					$settings_url
				);
			}
		}
	}

	/**
	 * Get do not update data fields
	 *
	 * @since  1.0.0
	 * @param string $field Field for check update is allowed or not.
	 * @return boolean
	 */
	public function ife_is_updatable( $field = '' ) {
		if ( empty( $field ) ){ return true; }
		if ( !ife_is_pro() ){ return true; }
		$ife_options = get_option( IFE_OPTIONS, array() );
		$facebook_options = isset( $ife_options['dont_update'] ) ? $ife_options['dont_update'] : array();
		if ( isset( $facebook_options[$field] ) &&  'yes' == $facebook_options[$field] ){
			return false;
		}
		return true;
	}

	/**
	 * Display upgrade to pro notice in form.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_pro_notice() {
		if ( ! ife_is_pro() ) {
			?>
			<div class="ife-blur-filter-cta" >
				<span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'import-facebook-events' ); ?> </span><a href="<?php echo esc_url(IFE_PLUGIN_BUY_NOW_URL); ?>"><?php echo esc_html_e( 'Upgrade to PRO', 'import-facebook-events' ); ?></a>
			</div>
			<?php
		}
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @param string $country Country name or code.
	 * @return string $country Country name or code.
	 */
	public function ife_get_country_code( $country ) {
		if ( empty( $country ) ) {
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
			if ( strtoupper( $country ) === $name ) {
				return $code;
			}
		}
		return $country;
	}

	/**
	 * Render Page header Section
	 *
	 * @since 1.1
	 * @return void
	 */
	function ife_render_common_header( $page_title  ){
		?>
		<div class="ife-header" >
			<div class="ife-container" >
				<div class="ife-header-content" >
					<span style="font-size:18px;"><?php esc_attr_e('Dashboard','import-facebook-events'); ?></span>
					<span class="spacer"></span>
					<span class="page-name"><?php echo esc_attr( $page_title ); ?></span></span>
					<div class="header-actions" >
						<span class="round">
							<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/import-facebook-events/' ); ?>" target="_blank">
								<svg viewBox="0 0 20 20" fill="#000000" height="20px" xmlns="http://www.w3.org/2000/svg" class="ife-circle-question-mark">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
								</svg>
							</a>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Page Footer Section
	 *
	 * @since 1.1
	 * @return void
	 */
	function ife_render_common_footer(){
		?>
			<div id="ife-footer-links" >
				<div class="ife-footer">
					<div><?php esc_attr_e( 'Made with ♥ by the Xylus Themes','import-facebook-events'); ?></div>
					<div class="ife-links" >
						<a href="<?php echo esc_url( 'https://xylusthemes.com/support/' ); ?>" target="_blank" ><?php esc_attr_e( 'Support','import-facebook-events'); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/import-facebook-events' ); ?>" target="_blank" ><?php esc_attr_e( 'Docs','import-facebook-events'); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" ><?php esc_attr_e( 'Free Plugins','import-facebook-events'); ?></a>
					</div>
					<div class="ife-social-links">
						<a href="<?php echo esc_url( 'https://www.facebook.com/xylusinfo/' ); ?>" target="_blank" >
							<svg class="ife-facebook">
								<path fill="currentColor" d="M16 8.05A8.02 8.02 0 0 0 8 0C3.58 0 0 3.6 0 8.05A8 8 0 0 0 6.74 16v-5.61H4.71V8.05h2.03V6.3c0-2.02 1.2-3.15 3-3.15.9 0 1.8.16 1.8.16v1.98h-1c-1 0-1.31.62-1.31 1.27v1.49h2.22l-.35 2.34H9.23V16A8.02 8.02 0 0 0 16 8.05Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.linkedin.com/company/xylus-consultancy-service-xcs-/' ); ?>" target="_blank" >
							<svg class="ife-linkedin">
								<path fill="currentColor" d="M14 1H1.97C1.44 1 1 1.47 1 2.03V14c0 .56.44 1 .97 1H14a1 1 0 0 0 1-1V2.03C15 1.47 14.53 1 14 1ZM5.22 13H3.16V6.34h2.06V13ZM4.19 5.4a1.2 1.2 0 0 1-1.22-1.18C2.97 3.56 3.5 3 4.19 3c.65 0 1.18.56 1.18 1.22 0 .66-.53 1.19-1.18 1.19ZM13 13h-2.1V9.75C10.9 9 10.9 8 9.85 8c-1.1 0-1.25.84-1.25 1.72V13H6.53V6.34H8.5v.91h.03a2.2 2.2 0 0 1 1.97-1.1c2.1 0 2.5 1.41 2.5 3.2V13Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://x.com/XylusThemes" target="_blank' ); ?>" target="_blank" >
							<svg class="ife-twitter" width="24" height="24" viewBox="0 0 24 24">
								<circle cx="12" cy="12" r="12" fill="currentColor"></circle>
								<g>
									<path d="M13.129 11.076L17.588 6H16.5315L12.658 10.4065L9.5665 6H6L10.676 12.664L6 17.9865H7.0565L11.1445 13.332L14.41 17.9865H17.9765L13.129 11.076ZM11.6815 12.7225L11.207 12.0585L7.4375 6.78H9.0605L12.1035 11.0415L12.576 11.7055L16.531 17.2445H14.908L11.6815 12.7225Z" fill="white"></path>
								</g>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.youtube.com/@xylussupport7784' ); ?>" target="_blank" >
							<svg class="ife-youtube">
								<path fill="currentColor" d="M16.63 3.9a2.12 2.12 0 0 0-1.5-1.52C13.8 2 8.53 2 8.53 2s-5.32 0-6.66.38c-.71.18-1.3.78-1.49 1.53C0 5.2 0 8.03 0 8.03s0 2.78.37 4.13c.19.75.78 1.3 1.5 1.5C3.2 14 8.51 14 8.51 14s5.28 0 6.62-.34c.71-.2 1.3-.75 1.49-1.5.37-1.35.37-4.13.37-4.13s0-2.81-.37-4.12Zm-9.85 6.66V5.5l4.4 2.53-4.4 2.53Z"></path>
							</svg>
						</a>
					</div>
				</div>
			</div>
		<?php   
	}

	/**
	 * Insert or update TEC custom tables: tec_events and tec_occurrences.
	 *
	 * @param array $centralize_array Centralized event data (with local and UTC timings, timezone, etc.).
	 * @param int   $event_post_id    The WordPress post ID associated with the event.
	 */
	public function ife_sync_event_to_tec_custom_tables( $centralize_array, $event_post_id ) {
		global $wpdb;

		$esource_id     = $centralize_array['ID'];
		$start_time     = gmdate( 'Y-m-d H:i:s', $centralize_array['starttime_local'] );
		$end_time       = gmdate( 'Y-m-d H:i:s', $centralize_array['endtime_local'] );
		if( $centralize_array['origin'] == 'ical' ){
			$start_date_utc = $centralize_array['startime_utc'];
			$end_date_utc   = $centralize_array['endtime_utc'];
		}else{
			$start_date_utc = gmdate( 'Y-m-d H:i:s', $centralize_array['startime_utc'] );
			$end_date_utc   = gmdate( 'Y-m-d H:i:s', $centralize_array['endtime_utc'] );
		}
		$timezone       = isset( $centralize_array['timezone_name'] ) ? $centralize_array['timezone_name'] : 'Africa/Abidjan';
		$duration       = 0;
		$hash           = sha1( $event_post_id . $duration . $start_time . $end_time . $start_date_utc . $end_date_utc . $timezone );

		$tec_events_table      = $wpdb->prefix . 'tec_events';
		$tec_occurrences_table = $wpdb->prefix . 'tec_occurrences';

		// Check if event already exists
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$existing_event_id = $wpdb->get_var( $wpdb->prepare( "SELECT event_id FROM $tec_events_table WHERE post_id = %d", $event_post_id ) );

		$event_data = array(
			'post_id'        => $event_post_id,
			'start_date'     => $start_time,
			'end_date'       => $end_time,
			'timezone'       => $timezone,
			'start_date_utc' => $start_date_utc,
			'end_date_utc'   => $end_date_utc,
		);

		$occurrence_data = array(
			'post_id'        => $event_post_id,
			'start_date'     => $start_time,
			'start_date_utc' => $start_date_utc,
			'end_date'       => $end_time,
			'end_date_utc'   => $end_date_utc,
		);

		if ( $existing_event_id ) {
			// Update
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->update( $tec_events_table, $event_data, array( 'post_id' => $event_post_id ) );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->update( $tec_occurrences_table, $occurrence_data, array( 'post_id' => $event_post_id ) );
		} else {
			// Insert
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->insert( $tec_events_table, $event_data );
			$occurrence_data['event_id'] = $wpdb->insert_id;
			$occurrence_data['hash']     = $hash;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->insert( $tec_occurrences_table, $occurrence_data );
		}
	}
}

/**
 * Check is pro active or not.
 *
 * @since  1.5.0
 * @return boolean
 */
function ife_is_pro() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'import-facebook-events-pro/import-facebook-events-pro.php' ) ) {
		return true;
	}
	return false;
}

/**
 * Check is pro active or not.
 *
 * @since  1.5.0
 * @return boolean
 */
function ife_aioec_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'all-in-one-event-calendar/all-in-one-event-calendar.php' ) ) {
		return true;
	}
	return false;
}


/**
 * Template Functions
 *
 * Template functions specifically created for Event Listings
 *
 * @author      Dharmesh Patel
 * @version     1.5.0
 */

/**
 * Gets and includes template files.
 *
 * @since 1.5.0
 * @param mixed  $template_name Template Name.
 * @param array  $args (default: array()).
 * @param string $template_path (default: '').
 * @param string $default_path (default: '').
 */
function get_ife_template( $template_name, $args = array(), $template_path = 'import-facebook-events', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	}
	include locate_ife_template( $template_name, $template_path, $default_path );
}

/**
 * Locates a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path  /   $template_name
 *      yourtheme       /   $template_name
 *      $default_path   /   $template_name
 *
 * @since 1.5.0
 * @param string      $template_name Name of template.
 * @param string      $template_path (default: 'import-facebook-events').
 * @param string|bool $default_path (default: '') False to not load a default.
 * @return string
 */
function locate_ife_template( $template_name, $template_path = 'import-facebook-events', $default_path = '' ) {
	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);
	// Get default template.
	if ( ! $template && false !== $default_path ) {
		$default_path = $default_path ? $default_path : IFE_PLUGIN_DIR . '/templates/';
		if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
			$template = trailingslashit( $default_path ) . $template_name;
		}
	}
	// Return what we found.
	return apply_filters( 'ife_locate_template', $template, $template_name, $template_path );
}

/**
 * Gets template part (for templates in loops).
 *
 * @since 1.0.0
 * @param string      $slug template slug.
 * @param string      $name (default: '').
 * @param string      $template_path (default: 'import-facebook-events').
 * @param string|bool $default_path (default: '') False to not load a default.
 */
function get_ife_template_part( $slug, $name = '', $template_path = 'import-facebook-events', $default_path = '' ) {
	$template = '';
	if ( $name ) {
		$template = locate_ife_template( "{$slug}-{$name}.php", $template_path, $default_path );
	}
	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/import-facebook-events/slug.php.
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
function ife_get_inprogress_import() {
	global $wpdb;
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$batches = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '%ife_import_batch_%' ORDER BY option_id ASC" ); // db call ok; no-cache ok.
	if ( is_multisite() ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$batches = $wpdb->get_results( "SELECT * FROM {$wpdb->sitemeta} WHERE meta_key LIKE '%ife_import_batch_%' ORDER BY meta_id ASC" ); // db call ok; no-cache ok.
	}
	return $batches;
}

/**
 * Get HourMins from given time stamp
 *
 * @param [int] $scheduledDate
 * @return string
 */
function ife_get_hour_mins($scheduledDate) {
	try{
		if($scheduledDate){
			$scheduledDate = gmdate('Hi', $scheduledDate);
		}
		return $scheduledDate;
	} catch (Exception $e) {
		return $scheduledDate;
	}
}

/**
 * Get IFE crons.
 *
 * @return Array
 */
function ife_get_crons(){
	$crons = array();
	if(function_exists('_get_cron_array') ){
		$crons = _get_cron_array();
	}
	$ife_scheduled = array_filter($crons, function($cron) {
		$cron_name = array_keys($cron) ? array_keys($cron)[0] : '';
		if (strpos($cron_name, 'xt_run_fb_scheduled_import') !== false) {
			return true;
		}
		return false;
	});
	return $ife_scheduled;
}

/**
 * Get timestamp for schedule crom event.
 *
 * @return int
 */
function ife_get_schedule_time(){
	try {
		$current_time = time();
		if(!function_exists('_get_cron_array') ){
			return $current_time;
		}
		$current_hour = gmdate('Hi', $current_time);
		$current_hour_formated = gmdate('H:i:s', $current_time);
		$ife_scheduled = ife_get_crons();
		if(empty($ife_scheduled)){
			return $current_time;
		}
		$scheduled_times = array_map( 'ife_get_hour_mins', array_keys( $ife_scheduled ) );
		$conflict_times = ife_has_conflict_times( $scheduled_times, $current_hour );
		if(!empty($conflict_times)){
			$slots = ife_get_slots($current_hour);
			foreach( $slots as $slot ){
				$conflict_time = ife_has_conflict_times( $scheduled_times, $slot );
				if(empty($conflict_time)){
					$seconds = strtotime(substr_replace($slot,':',-2,0).':00') - (strtotime($current_hour_formated));
					if( $seconds < 86400 ){
						$current_time = (int)$current_time + (int)$seconds;
					}
					return $current_time;
					break;
				}
			}
			return $current_time;
		}
		return $current_time;
	} catch (Exception $e) {
		return time();
	}
}

/**
 * Check if current slot has conflict or not
 *
 * @param [Array] $scheduled_times
 * @param [strinh] $current_hour
 * @return Array
 */
function ife_has_conflict_times( $scheduled_times, $current_hour ){
	$current_hour = (int) $current_hour;
	$scheduled_gap = 2;
	$conflict_times = array();
	$min_time = (int) $current_hour - $scheduled_gap;
	$max_time = (int) $current_hour + $scheduled_gap;
	foreach( $scheduled_times as $scheduled_time){
		if( $scheduled_time >= $min_time && $scheduled_time <= $max_time ){
			$conflict_times[] = $scheduled_time;
		}
	}
	return $conflict_times;
}

/**
 * Get IFE slots for check cron availability.
 *
 * @param [string] $current_hour
 * @return Array
 */
function ife_get_slots( $current_hour ){
	$slots = array();
	for ($hour=0; $hour < 24; $hour++) {
		if( $hour < 10 ) { $hour = '0'. $hour; }
		for ($min=0; $min < 60; $min++) {
			if( $min < 10 ) { $min = '0'.$min; }
			$slots[] = $hour.$min;
			$min = (int) $min;
		}
		$hour = (int) $hour;
	}
	$current_index = array_search( $current_hour, $slots );
	if($current_hour > 0 ){
		return array_merge( array_slice( $slots, $current_index ), array_slice( $slots, 0, $current_index ) );
	}
	return $slots;
}

/**
 * Get Next run time array for schdeuled import.
 *
 * @return Array
 */
function ife_get_next_run_times(){
	$next_runs = array();
	$crons  = ife_get_crons();
	foreach($crons as $time => $cron){
		foreach($cron as $cron_name){
			foreach($cron_name as $cron_post_id){
				$next_runs[$cron_post_id['args']['post_id']] = $time;
			}
		}
	}
	return $next_runs;
}
