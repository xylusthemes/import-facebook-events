<?php
/**
 * Template file for admin import events form.
 *
 * @package Import_Facebook_Events
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $ife_events;
$user_fb_pages = get_option( 'ife_fb_user_pages', array() );
$disabled      = '';
if ( ! ife_is_pro() ) {
	$disabled = 'disabled';
}
?>
<div class="ife-card" style="margin-top:20px;" >			
	<div class="ife-content"  aria-expanded="true" style=" ">
		<div class="ife-inner-main-section">
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" >
					<?php esc_attr_e( 'Import by', 'import-facebook-events' ); ?>
					<span class="ife-tooltip">
						<div>
							<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="ife-circle-question-mark">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
							</svg>
							<span class="ife-popper">
								<?php 
									$text = sprintf(
										esc_html__( 'Select Event source. %1$s, %2$s.', 'import-facebook-events' ),
										'<br><strong>' . esc_html__( '1. by Facebook Event ID', 'import-facebook-events' ) . '</strong>',
										'<br><strong>' . esc_html__( '2. Facebook Page', 'import-facebook-events' ) . '</strong>'
									);
									
									echo wp_kses(
										$text,
										array(
											'strong' => array(),
											'br' => array(),
										)
									);
								?>
								<div class="ife-popper__arrow"></div>
							</span>
						</div>
					</span>
				</span>
			</div>
			<div class="ife-inner-section-2">
				<label for="cleanup_post_type">
					<select name="facebook_import_by" id="facebook_import_by">
						<option value="facebook_event_id"><?php esc_attr_e( 'Facebook Event ID', 'import-facebook-events' ); ?></option>
						<option value="facebook_organization"><?php esc_attr_e( 'Facebook Page', 'import-facebook-events' ); ?></option>
						<?php if ( ! empty( $user_fb_pages ) ) { ?>
							<option value="my_pages"><?php esc_attr_e( 'My Pages', 'import-facebook-events' ); ?></option>
						<?php } ?>
					</select>
				</label>
			</div>
		</div>

		<div class="ife-inner-main-section facebook_eventid_wrapper" >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Facebook Event IDs', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<textarea name="facebook_event_ids" class="facebook_event_ids" placeholder="<?php esc_attr_e( 'One event ID per line, ( Eg. Event ID for https://www.facebook.com/events/123456789/ is "123456789" ).', 'import-facebook-events' ); ?>" rows="5" cols="50"></textarea>
			</div>
		</div>

		<div class="ife-inner-main-section facebook_page_wrapper" style="display: none;" >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Page username / ID to fetch events from', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<input class="ife_text facebook_page_username" name="facebook_page_username" type="text" <?php echo esc_attr( $disabled ); ?>/>
				<span class="ife_small">
					<?php esc_attr_e( ' Eg. username for https://www.facebook.com/xylusinfo/ is "xylusinfo".', 'import-facebook-events' ); ?>
				</span>
				<?php do_action( 'ife_render_pro_notice' ); ?>
			</div>
		</div>

		<div class="ife-inner-main-section facebook_account_wrapper" style="display: none;" >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'My Pages', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<?php if ( ! empty( $user_fb_pages ) ) { ?>
					<select name="my_page" class="my_page" required="required" <?php echo esc_attr( $disabled ); ?>>
						<option value=""><?php esc_attr_e( 'Select Page', 'import-facebook-events' ); ?></option>
						<?php
						foreach ( $user_fb_pages as $pkey => $pvalue ) {
							echo '<option value="' . esc_attr( $pkey ) . '">' . esc_attr( $pvalue['name'] ) . '</option>';
						}
						?>
					</select>
				<?php } ?>
				<span class="ife_small">
					<?php esc_attr__( 'Select Page for import events from it.', 'import-facebook-events' ); ?>
				</span>
				<?php do_action( 'ife_render_pro_notice' ); ?>
			</div>
		</div>

		<div class="ife-inner-main-section"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Import type', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<?php $ife_events->common->render_import_type(); ?>
			</div>
		</div>
		<?php
			$ife_events->common->render_import_into_and_taxonomy();
			$ife_events->common->render_eventstatus_input();
		?>

		<div class="ife-inner-main-section"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Author', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<?php wp_dropdown_users( array( 'show_option_none' => esc_attr__( 'Select Author','import-facebook-events'), 'name' => 'event_author', 'option_none_value' => get_current_user_id() ) ); ?>
				<span class="ife_small">
					<?php _e( 'Select event author for imported events. Default event author is current loggedin user.', 'import-facebook-events' ); ?>
				</span>
			</div>
		</div>
	</div>
</div>