<?php
/**
 * The template for IFE Settings.
 *
 * @package Import_Facebook_Events
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $ife_events;
$ife_options            = get_option( IFE_OPTIONS );
$facebook_options       = isset( $ife_options ) ? $ife_options : array();
$facebook_app_id        = isset( $facebook_options['facebook_app_id'] ) ? $facebook_options['facebook_app_id'] : '';
$facebook_app_secret    = isset( $facebook_options['facebook_app_secret'] ) ? $facebook_options['facebook_app_secret'] : '';
$ife_user_token_options = get_option( 'ife_user_token_options', array() );
$ife_fb_authorize_user  = get_option( 'ife_fb_authorize_user', array() );
$stab = isset( $_GET['stab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['stab'] ) ) ) : 'setting'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$ife_google_maps_api_key = get_option( 'ife_google_maps_api_key', array() );
$ife_google_geolocation_api_key = get_option( 'ife_google_geolocation_api_key', array() );
?>

<div class="ife-card" style="margin-top:20px;" >
	<div class="ife-content"  aria-expanded="true" style="padding: 10px 20px;">
		<div id="postbox-container-2" class="postbox-container">
			<div class="">
				<div class="ife-app">
					<div class="ife-tabs">
						<div class="tabs-scroller">
							<div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding ife_navbar nav-tab-wrapper">
								<div class="var-tabs__tab-wrap var-tabs--layout-horizontal ife_nav_tabs">
									<a href="javascript:void(0)" class="var-tab var-tab--active ife_tab_link"  data-tab="settings">
										<span class="tab-label"><?php esc_attr_e( 'General Settings', 'import-facebook-events' ); ?></span>
									</a>
									<a href="javascript:void(0)"  class="var-tab var-tab--inactive ife_tab_link" data-tab="google_maps_key" >
										<span class="tab-label"><?php esc_attr_e( 'Google Maps API', 'import-facebook-events' ); ?></span>
									</a>
									<?php if( ife_is_pro() ){ ?>
										<a href="javascript:void(0)"  class="var-tab var-tab--inactive ife_tab_link" data-tab="license">
											<span class="tab-label"><?php esc_attr_e( 'License', 'import-facebook-events' ); ?></span>
										</a>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				</div>
					
				<div id="poststuff">
					<div  id="settings" class=" ife_tab_content var-tab--active"  >
						<div class="ife_container" >
							<div class="ife_row" style="margin-top:10px;">
								<?php
								$site_url = get_home_url();
								if ( ! isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) { // WPCS: input var okay.
									?>
									<div class="widefat ife_settings_error">
										<?php printf( '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s', esc_attr__( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required for authorize your Facebook account.", 'import-facebook-events' ), esc_attr__( 'Click here', 'import-facebook-events' ), esc_attr__( 'for more information.', 'import-facebook-events' ) ); ?>
									</div>
									<?php
								}
								?>
								<div class="widefat ife_settings_notice">
									<?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s', esc_attr__( 'Note : ', 'import-facebook-events' ), esc_attr__( 'You have to create a Facebook application before filling the following details.', 'import-facebook-events' ), esc_attr__( 'Click here', 'import-facebook-events' ), esc_attr__( 'to create new Facebook application.', 'import-facebook-events' ) ); ?>
									<br/>
									<?php esc_attr_e( 'For detailed step by step instructions ', 'import-facebook-events' ); ?>
									<strong><a href="http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php esc_attr_e( 'Click here', 'import-facebook-events' ); ?></a></strong>.
									<br/>
									<strong><?php esc_attr_e( 'Set the site url as :', 'import-facebook-events' ); ?> </strong>
									<span style="color: green;"><?php echo esc_url( get_site_url() ); ?></span>
									<span class="dashicons dashicons-admin-page ife-btn-copy-shortcode ife_link_cp" data-value='<?php echo esc_url( get_site_url() ); ?>' ></span>
									<br/>
									<strong><?php esc_attr_e( 'Set Valid OAuth redirect URI :', 'import-facebook-events' ); ?> </strong>
									<span style="color: green;"><?php echo esc_url( admin_url( 'admin-post.php?action=ife_facebook_authorize_callback' ) ); ?></span>
									<span class="dashicons dashicons-admin-page ife-btn-copy-shortcode ife_link_cp" data-value='<?php echo esc_url( admin_url( 'admin-post.php?action=ife_facebook_authorize_callback' ) ); ?>' ></span>
								</div>

								<!-- Microsoft Authorization Section -->
                    			<?php do_action( 'ife_microsoft_authorize' ); ?>

								<?php
								if ( ! empty( $facebook_app_id ) && ! empty( $facebook_app_secret ) ) {
									?>
									<div class="fb_authorize" style="margin-top:20px;margin-bottom: 20px;" >
										<div class="ife-inner-main-section"  >
											<div class="ife-inner-section-1" >
												<span class="ife-title-text" ><?php esc_attr_e( 'Facebook Authorization', 'import-facebook-events' ); ?></span>
											</div>
											<div class="ife-inner-section-2">
												<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
													<input type="hidden" name="action" value="ife_facebook_authorize_action"/>
													<?php wp_nonce_field( 'ife_facebook_authorize_action', 'ife_facebook_authorize_nonce' ); ?>
													<?php
													$button_value = esc_attr__( 'Authorize', 'import-facebook-events' );
													if ( isset( $ife_user_token_options['authorize_status'] ) && 1 === $ife_user_token_options['authorize_status'] && isset( $ife_user_token_options['access_token'] ) && ! empty( $ife_user_token_options['access_token'] ) ) {
														$button_value = esc_attr__( 'Reauthorize', 'import-facebook-events' );
													}
													?>
													<input type="submit" class="button" name="ife_facebook_authorize" value="<?php echo esc_attr( $button_value ); ?>" />
													<?php
													if ( ! empty( $ife_fb_authorize_user ) && isset( $ife_fb_authorize_user['name'] ) && $ife_events->common->has_authorized_user_token() ) {
														$fbauthname = sanitize_text_field( $ife_fb_authorize_user['name'] );
														if ( ! empty( $fbauthname ) ) {
															// translators: %s is user's name.
															printf( esc_attr__( ' ( Authorized as: %s )', 'import-facebook-events' ), '<b>' . esc_attr( $fbauthname ) . '</b>' );
														}
													}
													?>
												</form>
												<span class="ife_small">
													<?php esc_attr_e( 'Please authorize your Facebook account for import Facebook events. Please authorize with account which you have used for create an Facebook app.', 'import-facebook-events' ); ?>
												</span>
											</div>
										</div>
									</div>
									<?php
								}
								?>
								<form method="post" id="ife_setting_form">
								<?php do_action( 'ife_before_settings_section' ); ?>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Facebook App ID', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<input class="facebook_app_id" name="facebook[facebook_app_id]" type="text" value="<?php echo( ! empty( $facebook_app_id ) ? esc_attr( $facebook_app_id ) : '' ); ?>" />
										<span class="ife_small">
											<?php
											printf(
												'%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>',
												esc_attr__( 'You can view or create your Facebook Apps', 'import-facebook-events' ),
												esc_attr__( 'from here', 'import-facebook-events' )
											);
											?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Facebook App secret', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<input class="facebook_app_secret" name="facebook[facebook_app_secret]" type="text" value="<?php echo( ! empty( $facebook_app_secret ) ? esc_attr( $facebook_app_secret ) : '' ); ?>" />
										<span class="ife_small">
											<?php
											printf(
												'%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>',
												esc_attr__( 'You can view or create your Facebook Apps', 'import-facebook-events' ),
												esc_attr__( 'from here', 'import-facebook-events' )
											);
											?>
										</span>
									</div>
								</div>

								<?php do_action( 'ife_after_app_settings' ); ?>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Skip Trashed Events', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$skip_trash = isset($facebook_options['skip_trash']) ? $facebook_options['skip_trash'] : 'no';
										?>
										<input type="checkbox" name="facebook[skip_trash]" value="yes" <?php if ($skip_trash == 'yes') {echo 'checked="checked"';}if (!ife_is_pro()) {echo 'disabled="disabled"'; } ?> />
										<span class="ife_small">
											<?php esc_attr_e('Check to enable skip-the-trash events during importing.', 'import-facebook-events'); ?>
										</span>
										<?php do_action('ife_render_pro_notice'); ?>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Update existing events', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$update_facebook_events = isset( $facebook_options['update_events'] ) ? $facebook_options['update_events'] : 'no';
										?>
										<input type="checkbox" id="update_events" name="facebook[update_events]" value="yes"
										<?php echo( ( 'yes' === $update_facebook_events ) ? 'checked="checked"' : '' ); ?> />
										<span class="ife_small">
											<?php esc_attr_e( 'Check to updates existing events.', 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Advanced Synchronization', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$advanced_sync = isset( $facebook_options['advanced_sync'] ) ? $facebook_options['advanced_sync'] : 'no';
										$checked       = ( 'yes' === $advanced_sync ) ? 'checked' : '';
										$disabled      = ( ! ife_is_pro() ) ? 'disabled' : '';
										?>
										<input type="checkbox" name="facebook[advanced_sync]" value="yes" <?php echo esc_attr( $checked . ' ' . $disabled ); ?> />
										<span class="ife_small">
											<?php esc_attr_e( 'Check to enable advanced synchronization, this will delete events which are removed from Facebook. Also, it deletes passed events.', 'import-facebook-events' ); ?>
										</span>
										<?php do_action( 'ife_render_pro_notice' ); ?>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Import Past Events', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$import_past_events = isset( $facebook_options['import_past_events'] ) ? $facebook_options['import_past_events'] : 'no';
										?>
										<input type="checkbox" id="import_past_events" name="facebook[import_past_events]" value="yes" <?php echo( ( 'yes' === $import_past_events ) ? 'checked="checked"' : '' ); if (!ife_is_pro()) {echo 'disabled="disabled"'; } ?> />
										<span class="ife_small">
											<?php esc_attr_e( 'Check to import past events this option will import events from the last 2 years and upcoming events..', 'import-facebook-events' ); ?>
										</span>
										<?php do_action('ife_render_pro_notice'); ?>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( "Import Facebook's Event Category", 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$import_fb_event_cats = isset( $facebook_options['import_fb_event_cats'] ) ? $facebook_options['import_fb_event_cats'] : 'no';
										?>
										<input type="checkbox" id="import_fb_event_cats" name="facebook[import_fb_event_cats]" value="yes" <?php echo( ( 'yes' === $import_fb_event_cats ) ? 'checked="checked"' : '' ); ?> />
										<span class="ife_small">
											<?php esc_attr_e( 'Check to import the Facebook event category and assign it to events.', 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Move past events in trash', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$move_peit_events = isset( $facebook_options['move_peit'] ) ? $facebook_options['move_peit'] : 'no';
										?>
										<input type="checkbox" name="facebook[move_peit]" value="yes" <?php if ( $move_peit_events == 'yes' ) { echo 'checked="checked"'; } ?> />
										<span class="ife_small">
											<?php esc_attr_e( 'Check to move past events in the trash, Automatically move events to the trash 24 hours after their end date using wp-cron. This runs once daily in the background.', 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Direct link to Facebook', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$direct_link = isset($facebook_options['direct_link']) ? $facebook_options['direct_link'] : 'no';
										?>
										<input type="checkbox" name="facebook[direct_link]" value="yes" <?php if ($direct_link == 'yes') {echo 'checked="checked"';}if (!ife_is_pro()) {echo 'disabled="disabled"'; } ?> />
										<span class="ife_small">
											<?php esc_attr_e('Check to enable direct event link to Facebook instead of event detail page.', 'import-facebook-events'); ?>
										</span>
										<?php do_action('ife_render_pro_notice'); ?>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Don\'t Update these data.', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$donotupdate = isset($facebook_options['dont_update'])? $facebook_options['dont_update'] : array();
										$sdontupdate = isset( $donotupdate['status'] ) ? $donotupdate['status'] : 'no';
										$cdontupdate = isset( $donotupdate['category'] ) ? $donotupdate['category'] : 'no';
										?>
										<input type="checkbox" name="facebook[dont_update][status]" value="yes" <?php checked( $sdontupdate, 'yes' ); disabled( ife_is_pro(), false );?> />
										<span class="xtei_small">
											<?php esc_attr_e( 'Status ( Publish, Pending, Draft etc.. )', 'import-facebook-events' ); ?>
										</span><br/>
										<input type="checkbox" name="facebook[dont_update][category]" value="yes" <?php checked( $cdontupdate, 'yes' ); disabled( ife_is_pro(), false );?> />
										<span class="xtei_small">
											<?php esc_attr_e( 'Event category', 'import-facebook-events' ); ?>
										</span><br/>
										<span class="ife_small">
											<?php esc_attr_e( "Select data which you don't want to update during existing events update. (This is applicable only if you have checked 'update existing events')", 'import-facebook-events' ); ?>
										</span>
										<?php do_action('ife_render_pro_notice'); ?>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Accent Color', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$accent_color = isset( $facebook_options['accent_color'] ) ? $facebook_options['accent_color'] : '#039ED7';
										?>
										<input class="ife_color_field" type="text" name="facebook[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>"/>
										<span class="ife_small">
											<?php esc_attr_e( 'Choose accent color for front-end event grid and event widget.', 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Event Slug', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$event_slug = isset($facebook_options['event_slug']) ? $facebook_options['event_slug'] : 'facebook-event';
										if (!ife_is_pro()) {
											echo '<input type="text" name="" value="" disabled="disabled" />';
										} else {
											?>
											<input type="text" name="facebook[event_slug]" value="<?php if ( $event_slug ) { echo esc_attr( $event_slug ); } ?>" />
											<?php
										} ?>
										<span class="ife_small">
											<?php esc_attr_e('Slug for the event.', 'import-facebook-events'); ?>
										</span>
										<?php do_action('ife_render_pro_notice'); ?>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Default Event Thumbnail', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										wp_enqueue_media();
										
										$ife_cfulb     = ' upload-button button-add-media button-add-site-icon ';
										$ife_cfub      = ' button ';
										$ife_cfw       = '';
									
										if ( has_site_icon() ) {
											$ife_cfw  .= ' has-site-icon';
											$ife_cfb   = $ife_cfub;
											$ife_cfboc = $ife_cfulb;
										} else {
											$ife_cfw  .= ' hidden';
											$ife_cfb   = $ife_cfulb;
											$ife_cfboc = $ife_cfub;
										}

										$ife_edt_id    = isset( $facebook_options['ife_event_default_thumbnail'] ) ? $facebook_options['ife_event_default_thumbnail'] : '';
										$ife_edt_url   = !empty( $ife_edt_id ) ? wp_get_attachment_url( $ife_edt_id ) : '';
										$button_text   = empty( $ife_edt_url ) ? 'Choose Event Thumbnail' : 'Change Event Thumbnail';
										$remove_class  = empty( $ife_edt_url ) ? 'hidden' : '';
										?>

										<div id="ife-event-thumbnail-preview" class="wp-clearfix settings-page-preview <?php echo esc_attr( ! empty( $ife_edt_url ) ? '' : 'hidden' ); ?>">
											<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
											<img id="ife-event-thumbnail-img" src="<?php echo esc_url( $ife_edt_url ); ?>" alt="<?php esc_attr_e( 'Event Thumbnail', 'import-facebook-events' ); ?>" style="max-width:100%;width: 15%;height: auto;" >
										</div>

										<input type="hidden" name="facebook[ife_event_default_thumbnail]" id="ife-event_thumbnail_hidden_field" value="<?php echo esc_attr( $ife_edt_id ); ?>" />

										<div class="action-buttons">
											<button type="button" id="ife-choose-from-library-button" class="<?php echo esc_attr( $ife_cfb ); ?>" data-alt-classes="<?php echo esc_attr( $ife_cfboc ); ?>" >
												<?php echo esc_attr( $button_text ); ?>
											</button>
											<button id="ife-js-remove-thumbnail" type="button" data-alt-classes="<?php echo esc_attr( $ife_cfboc ); ?>" class="reset <?php echo esc_attr( $remove_class ); ?><?php echo esc_attr( $ife_cfb ); ?>" >
												<?php esc_attr_e( 'Remove Event Thumbnail', 'import-facebook-events' ); ?>
											</button>
										</div>
										<span class="ife_small">
											<?php esc_attr_e( "This option will display this image in the event's grid view if the event does not have a featured image.", 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Event Display Time Format', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$time_format = isset( $facebook_options['time_format'] ) ? $facebook_options['time_format'] : '12hours';
										?>
										<select name="facebook[time_format]">
											<option value="12hours" <?php selected('12hours', $time_format); ?>><?php esc_attr_e( '12 Hours', 'import-facebook-events' );  ?></option>
											<option value="24hours" <?php selected('24hours', $time_format); ?>><?php esc_attr_e( '24 Hours', 'import-facebook-events' ); ?></option>
											<option value="wordpress_default" <?php selected('wordpress_default', $time_format); ?>><?php esc_attr_e( 'WordPress Default', 'import-facebook-events' ); ?></option>
										</select>
										<span class="ife_small">
											<?php esc_attr_e( 'Choose event display time format for front-end.', 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Disable Facebook events', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$deactive_fbevents = isset( $facebook_options['deactive_fbevents'] ) ? $facebook_options['deactive_fbevents'] : 'no';
										?>
										<input type="checkbox" name="facebook[deactive_fbevents]" value="yes" <?php echo( ( 'yes' === $deactive_fbevents ) ? 'checked="checked"' : '' ); ?> />
										<span class="ife_small">
											<?php esc_attr_e( 'Check to disable inbuilt event management system.', 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<div class="ife-inner-main-section"  >
									<div class="ife-inner-section-1" >
										<span class="ife-title-text" ><?php esc_attr_e( 'Delete Import Facebook Events data on Uninstall', 'import-facebook-events' ); ?></span>
									</div>
									<div class="ife-inner-section-2">
										<?php
										$delete_ifedata = isset( $facebook_options['delete_ifedata'] ) ? $facebook_options['delete_ifedata'] : 'no';
										?>
										<input type="checkbox" name="facebook[delete_ifedata]" value="yes" <?php echo( ( 'yes' === $delete_ifedata ) ? 'checked="checked"' : '' ); ?> />
										<span class="ife_small">
											<?php esc_attr_e( 'Delete Import Facebook Events data like settings, scheduled imports, import history on Uninstall.', 'import-facebook-events' ); ?>
										</span>
									</div>
								</div>

								<?php do_action( 'ife_after_settings_section' ); ?>
									<div>
										<input type="hidden" name="ife_action" value="ife_save_settings" />
										<?php wp_nonce_field( 'ife_setting_form_nonce_action', 'ife_setting_form_nonce' ); ?>
										<input type="submit" class="ife_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'import-facebook-events' ); ?>" />
									</div>
								</form>
							</div>
						</div>
					</div>

					<div id="google_maps_key" class="ife_tab_content">
						<div class="ife_container">
							<div class="ife_row" >
								<form method="post" id="ife_gma_setting_form" style="margin-top: 20px;">
									<?php do_action( 'ife_before_settings_section' ); ?>
									<div class="ife-inner-main-section"  >
										<div class="ife-inner-section-1" >
											<span class="ife-title-text" ><?php esc_attr_e( 'Google Maps API', 'import-facebook-events' ); ?></span>
										</div>
										<div class="ife-inner-section-2">
											<input class="ife_google_maps_api_key" name="ife_google_maps_api_key" Placeholder="Enter Google Maps API Key Here..." type="text" value="<?php echo( ! empty( $ife_google_maps_api_key ) ? esc_attr( $ife_google_maps_api_key ) : '' ); ?>" />
											<span class="ife_check_key"><a href="javascript:void(0)" > <?php esc_attr_e( 'Check Google Maps Key', 'import-facebook-events' ); ?></a><span class="ife_loader" id="ife_loader"></span></span>
											<span id="ife_gmap_error_message"></span>
											<span id="ife_gmap_success_message"></span>
											<span class="ife_small">
												<?php
													printf(
														'%s <a href="https://developers.google.com/maps/documentation/embed/get-api-key#create-api-keys" target="_blank">%s</a> / %s',
														esc_attr__( 'Google maps API Key (Required)', 'import-facebook-events' ),
														esc_attr__( 'How to get an API Key', 'import-facebook-events' ),
														'<a href="https://developers.google.com/maps/documentation/embed/get-api-key#restrict_key" target="_blank">' . esc_attr__( 'Find out more about API Key restrictions', 'import-facebook-events' ) . '</a>'
													);
												?>
											</span>
										</div>
									</div>
									<br/>

									<div class="ife-inner-main-section"  >
										<div class="ife-inner-section-1" >
											<span class="ife-title-text" ><?php esc_attr_e( 'Google GeoLocation API', 'import-facebook-events' ); ?></span>
										</div>
										<div class="ife-inner-section-2">
											<input class="ife_google_geolocation_api_key" name="ife_google_geolocation_api_key" Placeholder="Enter Google Maps API Key Here..." type="text" value="<?php echo( ! empty( $ife_google_geolocation_api_key ) ? esc_attr( $ife_google_geolocation_api_key ) : '' ); ?>" />
											<span class="ife_ggl_check_key"><a href="javascript:void(0)" > <?php esc_attr_e( 'Check Google GeoLocation Key', 'import-facebook-events' ); ?></a><span class="ife_ggl_loader" id="ife_ggl_loader"></span></span>
											<span id="ife_ggl_error_message"></span>
											<span id="ife_ggl_success_message"></span>
											<span class="ife_small">
												<?php
													printf(
														'%s <a href="https://developers.google.com/maps/documentation/geolocation/get-api-key" target="_blank">%s</a> / %s',
														esc_attr__( 'Google GeoLocation API Key (Required)', 'import-facebook-events' ),
														esc_attr__( 'How to get an API Key', 'import-facebook-events' ),
														'<a href="https://developers.google.com/maps/documentation/geolocation/get-api-key#restrict_key" target="_blank">' . esc_attr__( 'Find out more about API Key restrictions', 'import-facebook-events' ) . '</a>'
													);
												?>
											</span>
										</div>
									</div>
								
									<div>
										<input type="hidden" name="ife_gma_action" value="ife_save_gma_settings" />
										<?php wp_nonce_field( 'ife_gma_setting_form_nonce_action', 'ife_gma_setting_form_nonce' ); ?>
										<input type="submit" class="ife_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'import-facebook-events' ); ?>" />
									</div>
								</form>
							</div>
						</div>
					</div>
					<?php if( ife_is_pro() ){ ?>
						<div id="license" class="ife_tab_content">
							<?php
								if( class_exists( 'Import_Facebook_Events_Pro_Common' ) && method_exists( $ife_events->common_pro, 'ife_licence_page_in_setting' ) ){
									$ife_events->common_pro->ife_licence_page_in_setting(); 
								}else{
									$license_section = sprintf(
										'<h3 class="setting_bar" >Once you have updated the plugin Pro version <a href="%s">%s</a>, you will be able to access this section.</h3>',
										esc_url( admin_url( 'plugins.php?s=import+facebook+events+pro' ) ),
										esc_html__( 'Here', 'import-facebook-events' )
									);
									echo wp_kses_post( $license_section );
								}
							?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>