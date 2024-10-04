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
<div class="ife_container">
	<div class="ife_row">
		<div class="ife-column ife_well">
			<h3><?php esc_attr_e( 'Facebook Import', 'import-facebook-events' ); ?></h3>
			<form method="post" id="ife_facebook_form">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php esc_attr_e( 'Import by', 'import-facebook-events' ); ?> :
							</th>
							<td>
								<select name="facebook_import_by" id="facebook_import_by">
									<option value="facebook_event_id"><?php esc_attr_e( 'Facebook Event ID', 'import-facebook-events' ); ?></option>
									<option value="facebook_organization"><?php esc_attr_e( 'Facebook Page', 'import-facebook-events' ); ?></option>
									<?php //if ( $ife_events->common->has_authorized_user_token() ) { ?>
										<!-- <option value="facebook_group"><?php //esc_attr_e( 'Facebook Group', 'import-facebook-events' ); ?></option> -->
									<?php //} ?>
									<?php if ( ! empty( $user_fb_pages ) ) { ?>
										<option value="my_pages"><?php esc_attr_e( 'My Pages', 'import-facebook-events' ); ?></option>
									<?php } ?>
								</select>
								<span class="ife_small">
									<?php
									printf(
										// translators: please ignore %1$s and  %2$s.
										esc_attr__( 'Select Event source. %1$s, %2$s ( import events belonging to a Facebook organization or a Facebook page ).', 'import-facebook-events' ),
										'<strong>' . esc_attr__( '1. by Facebook Event ID', 'import-facebook-events' ) . '</strong>',
										'<strong>' . esc_attr__( '2. Facebook Organization or Page', 'import-facebook-events' ) . '</strong>'
									);
									?>
									<br/>
									<?php
									// if ( $ife_events->common->has_authorized_user_token() ) {
									// 	printf(
									// 		// translators: please ignore %1$s and  %2$s.
									// 		esc_attr__( '%1$s (Import events from Facebook group)', 'import-facebook-events' ),
									// 		'<strong>' . esc_attr__( '3. Facebook Group', 'import-facebook-events' ) . '</strong>',
									// 	);
									// }
									?>
								</span>
							</td>
						</tr>

						<tr class="facebook_eventid_wrapper">
							<th scope="row">
								<?php esc_attr_e( 'Facebook Event IDs', 'import-facebook-events' ); ?> :
							</th>
							<td>
								<textarea name="facebook_event_ids" class="facebook_event_ids" rows="5" cols="50"></textarea>
								<span class="ife_small">
									<?php esc_attr__( 'One event ID per line, ( Eg. Event ID for https://www.facebook.com/events/123456789/ is "123456789" ).', 'import-facebook-events' ); ?>
								</span>
							</td>
						</tr>

						<tr class="facebook_page_wrapper" style="display: none;">
							<th scope="row">
								<?php esc_attr_e( 'Page username / ID to fetch events from', 'import-facebook-events' ); ?> :
							</th>
							<td>
								<input class="ife_text facebook_page_username" name="facebook_page_username" type="text" <?php echo esc_attr( $disabled ); ?>/>
								<span class="ife_small">
									<?php esc_attr_e( ' Eg. username for https://www.facebook.com/xylusinfo/ is "xylusinfo".', 'import-facebook-events' ); ?>
								</span>
								<?php do_action( 'ife_render_pro_notice' ); ?>
							</td>
						</tr>

						<!-- <tr class="facebook_group_wrapper" style="display: none;">
							<th scope="row">
								<?php //esc_attr_e( 'Facebook Group Numeric ID to fetch events from', 'import-facebook-events' ); ?> :
							</th>
							<td>
								<input class="ife_text facebook_group" name="facebook_group_id" type="text" <?php //echo esc_attr( $disabled ); ?>/>
								<span class="ife_small">
									<?php //esc_attr__( ' Eg.Input value for group https://www.facebook.com/groups/123456789123456/ is "123456789123456"', 'import-facebook-events' ); ?>
								</span>
								<?php //do_action( 'ife_render_pro_notice' ); ?>
							</td>
						</tr> -->

						<tr class="facebook_account_wrapper" style="display: none;">
							<th scope="row">
								<?php esc_attr_e( 'My Pages', 'import-facebook-events' ); ?> :
							</th>
							<td>
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
							</td>
						</tr>

						<tr class="import_type_wrapper">
							<th scope="row">
								<?php esc_attr_e( 'Import type', 'import-facebook-events' ); ?> :
							</th>
							<td>
								<?php $ife_events->common->render_import_type(); ?>
							</td>
						</tr>

						<?php
						$ife_events->common->render_import_into_and_taxonomy();
						$ife_events->common->render_eventstatus_input();
						?>
                        <tr>
							<th scope="row">
								<?php _e('Author','import-facebook-events'); ?> :
							</th>
							<td>
								<?php wp_dropdown_users( array( 'show_option_none' => esc_attr__( 'Select Author','import-facebook-events'), 'name' => 'event_author', 'option_none_value' => get_current_user_id() ) ); ?>
								<span class="ife_small">
									<?php _e( 'Select event author for imported events. Default event author is current loggedin user.', 'import-facebook-events' ); ?>
								</span>
							</td>
						</tr>
					</tbody>
				</table>

				<div class="ife_element">
					<input type="hidden" name="import_origin" value="facebook" />
					<input type="hidden" name="ife_action" value="ife_import_submit" />
					<?php wp_nonce_field( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ); ?>
					<input type="submit" class="button-primary ife_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-facebook-events' ); ?>" />
				</div>
			</form>
		</div>
	</div>
</div>
