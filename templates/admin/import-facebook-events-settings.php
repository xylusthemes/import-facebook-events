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
$is_direct_auth         = isset( $ife_user_token_options['direct_auth'] ) ? ( 1 === $ife_user_token_options['direct_auth'] ) : false;
$is_authenticated       = isset( $ife_user_token_options['authorize_status'] ) ? ( 1 === $ife_user_token_options['authorize_status'] ) : false;
$is_key_saved           = ( ! empty( $facebook_app_id ) && ! empty( $facebook_app_secret ) );
?>
<div class="ife_container">
	<div class="ife_row">
		<h3 class="setting_bar"><?php esc_attr_e( 'Facebook Connection Settings', 'import-facebook-events' ); ?></h3>
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

		<?php
		if ( ! $is_key_saved ) {
			?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php esc_html_e( 'Facebook Authorization', 'import-facebook-events' ); ?> :
						</th>
						<td>
							<?php
							if ( ! empty( $ife_fb_authorize_user ) && isset( $ife_fb_authorize_user['name'] ) ) {
								$name  = $ife_fb_authorize_user['name'];
								$avtar = $ife_fb_authorize_user['avtar'];
								?>
								<div class="ife_connection_wrapper">
									<div class="image_wrap">
										<img src="<?php echo esc_url( $avtar ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
									</div>
									<div class="name_wrap">
										<?php printf( __( 'Connected as: %s', 'import-facebook-events' ), '<strong>' . esc_attr( $name ) . '</strong>' ); ?>
										<br/>
										<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'action', 'ife_deauthorize_action', admin_url( 'admin-post.php' ) ), 'ife_deauthorize_action', 'ife_deauthorize_nonce' ) ); ?>">
											<?php esc_html_e( 'Remove Connection', 'import-facebook-events' ); ?>
										</a>
									</div>
								</div>
								<?php
							} else {
								$button_value = esc_attr__( 'Log in	With Facebook', 'import-facebook-events' );
								$redirect_url = wp_nonce_url( add_query_arg( 'action', 'ife_fb_login_action', admin_url( 'admin-post.php' ) ), 'ife_fb_login_action', 'ife_fb_login_nonce' );
								$fb_login_url = add_query_arg(
									array(
										'redirect' => rawurlencode( $redirect_url ),
									),
									'https://connect.xylusthemes.com/login/facebook'
								);
								?>
								<a href="<?php echo esc_url( $fb_login_url ); ?>" class="button button-primary"><?php echo esc_attr( $button_value ); ?></a>
								<span class="ife_small">
									<?php esc_attr_e( 'Please authorize your Facebook account for import Facebook events from your Facebook page. (Supports import from Facebook page only, if you want to import Intersted/Going events, please continue by creating App as suggested below).', 'import-facebook-events' ); ?>
								</span>
								<?php
							}
							?>
						</td>
					</tr>

					<?php if ( ! $is_key_saved && ! $is_direct_auth ) { ?>
					<tr>
						<th scope="row" style="text-align: center" colspan="2">
							<?php esc_html_e( ' - OR -', 'import-facebook-events' ); ?>
						</th>
					</tr>
					<?php } ?>

				</tbody>
			</table>
		<?php } ?>

		<?php if ( ! $is_direct_auth ) { ?>
			<div class="widefat ife_settings_notice">
				<?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s', esc_attr__( 'Note : ', 'import-facebook-events' ), esc_attr__( 'You have to create a Facebook application before filling the following details.', 'import-facebook-events' ), esc_attr__( 'Click here', 'import-facebook-events' ), esc_attr__( 'to create new Facebook application.', 'import-facebook-events' ) ); ?>
				<br/>
				<?php esc_attr_e( 'For detailed step by step instructions ', 'import-facebook-events' ); ?>
				<strong><a href="http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php esc_attr_e( 'Click here', 'import-facebook-events' ); ?></a></strong>.
				<br/>
				<strong><?php esc_attr_e( 'Set the site url as :', 'import-facebook-events' ); ?> </strong>
				<span style="color: green;"><?php echo esc_url( get_site_url() ); ?></span>
				<br/>
				<strong><?php esc_attr_e( 'Set Valid OAuth redirect URI :', 'import-facebook-events' ); ?> </strong>
				<span style="color: green;"><?php echo esc_url( admin_url( 'admin-post.php?action=ife_facebook_authorize_callback' ) ); ?></span>
			</div>
		<?php } ?>

		<?php
		if ( $is_key_saved ) {
			?>
			<h4 class="setting_bar"><?php esc_attr_e( 'Authorize your Facebook Account', 'import-facebook-events' ); ?></h4>
			<div class="fb_authorize">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<?php esc_attr_e( 'Facebook Authorization', 'import-facebook-events' ); ?> :
							</th>
							<td>
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
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php
		}
		?>
		<form method="post" id="ife_setting_form">
		<?php if ( ! $is_direct_auth ) { ?>
			<table class="form-table">
				<tbody>
					<?php do_action( 'ife_before_settings_section' ); ?>
					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Facebook App ID', 'import-facebook-events' ); ?> :
						</th>
						<td>
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
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Facebook App secret', 'import-facebook-events' ); ?> :
						</th>
						<td>
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
						</td>
					</tr>
					<?php do_action( 'ife_after_app_settings' ); ?>
				</tbody>
			</table>
		<?php } ?>

			<h3 class="setting_bar"><?php esc_attr_e( 'Import Settings', 'import-facebook-events' ); ?></h3>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Update existing events', 'import-facebook-events' ); ?> :
						</th>
						<td>
							<?php
							$update_facebook_events = isset( $facebook_options['update_events'] ) ? $facebook_options['update_events'] : 'no';
							?>
							<input type="checkbox" id="update_events" name="facebook[update_events]" value="yes"
							<?php echo( ( 'yes' === $update_facebook_events ) ? 'checked="checked"' : '' ); ?> />
							<span class="ife_small">
								<?php esc_attr_e( 'Check to updates existing events.', 'import-facebook-events' ); ?>
							</span>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( "Don't Update these data.", "import-facebook-events" ); ?> :
						</th>
						<td>
							<?php
							$donotupdate = isset( $facebook_options['dont_update'] ) ? $facebook_options['dont_update'] : array();
							$sdontupdate = isset( $donotupdate['status'] ) ? $donotupdate['status'] : 'no';
							$cdontupdate = isset( $donotupdate['category'] ) ? $donotupdate['category'] : 'no';
							?>
							<input type="checkbox" name="facebook[dont_update][status]" value="yes" <?php checked( $sdontupdate, 'yes' ); disabled( ife_is_pro(), false );?> />
							<span class="xtei_small">
								<?php esc_html_e( 'Status ( Publish, Pending, Draft etc.. )', 'import-facebook-events' ); ?>
							</span><br/>
							<input type="checkbox" name="facebook[dont_update][category]" value="yes" <?php checked( $cdontupdate, 'yes' ); disabled( ife_is_pro(), false ); ?> />
							<span class="xtei_small">
								<?php esc_html_e( 'Event category', 'import-facebook-events' ); ?>
							</span><br/>
							<span class="ife_small">
								<?php esc_html_e( "Select data which you don't want to update during existing events update. (This is applicable only if you have checked 'update existing events')", 'import-facebook-events' ); ?>
							</span>
							<?php do_action( 'ife_render_pro_notice' ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e('Direct link to Facebook', 'import-facebook-events'); ?> :
						</th>
						<td>
							<?php
							$direct_link = isset( $facebook_options['direct_link'] ) ? $facebook_options['direct_link'] : 'no';
							?>
							<input type="checkbox" name="facebook[direct_link]" value="yes" <?php if ( $direct_link == 'yes' ) { echo 'checked="checked"'; }if (!ife_is_pro()) { echo 'disabled="disabled"'; } ?> />
							<span class="ife_small">
								<?php esc_html_e('Check to enable direct event link to Facebook instead of event detail page.', 'import-facebook-events'); ?>
							</span>
							<?php do_action('ife_render_pro_notice'); ?>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Advanced Synchronization', 'import-facebook-events' ); ?> :
						</th>
						<td>
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
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Disable Facebook events', 'import-facebook-events' ); ?> :
						</th>
						<td>
							<?php
							$deactive_fbevents = isset( $facebook_options['deactive_fbevents'] ) ? $facebook_options['deactive_fbevents'] : 'no';
							?>
							<input type="checkbox" name="facebook[deactive_fbevents]" value="yes" <?php echo( ( 'yes' === $deactive_fbevents ) ? 'checked="checked"' : '' ); ?> />
							<span class="ife_small">
								<?php esc_attr_e( 'Check to disable inbuilt event management system.', 'import-facebook-events' ); ?>
							</span>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Accent Color', 'import-facebook-events' ); ?> :
						</th>
						<td>
						<?php
						$accent_color = isset( $facebook_options['accent_color'] ) ? $facebook_options['accent_color'] : '#039ED7';
						?>
						<input class="ife_color_field" type="text" name="facebook[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>"/>
						<span class="ife_small">
							<?php esc_attr_e( 'Choose accent color for front-end event grid and event widget.', 'import-facebook-events' ); ?>
						</span>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Event Slug', 'import-facebook-events' ); ?> :
						</th>
						<td>
							<?php
							$event_slug = isset( $facebook_options['event_slug'] ) ? $facebook_options['event_slug'] : 'facebook-event';
							if ( ! ife_is_pro() ) {
								echo '<input type="text" name="" value="" disabled="disabled" />';
							} else {
								?>
								<input type="text" name="facebook[event_slug]" value="<?php if ( $event_slug ) { echo $event_slug; } ?>" />
								<?php
							} ?>
							<span class="ife_small">
								<?php _e('Slug for the event.', 'import-facebook-events'); ?>
							</span>
							<?php do_action( 'ife_render_pro_notice' ); ?>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Event Display Time Format', 'import-facebook-events' ); ?> :
						</th>
						<td>
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
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_attr_e( 'Delete Import Facebook Events data on Uninstall', 'import-facebook-events' ); ?> :
						</th>
						<td>
							<?php
							$delete_ifedata = isset( $facebook_options['delete_ifedata'] ) ? $facebook_options['delete_ifedata'] : 'no';
							?>
							<input type="checkbox" name="facebook[delete_ifedata]" value="yes" <?php echo( ( 'yes' === $delete_ifedata ) ? 'checked="checked"' : '' ); ?> />
							<span class="ife_small">
								<?php esc_attr_e( 'Delete Import Facebook Events data like settings, scheduled imports, import history on Uninstall.', 'import-facebook-events' ); ?>
							</span>
						</td>
					</tr>
					<?php do_action( 'ife_after_settings_section' ); ?>

				</tbody>
			</table>
			<br/>

			<div class="ife_element">
				<input type="hidden" name="ife_action" value="ife_save_settings" />
				<?php wp_nonce_field( 'ife_setting_form_nonce_action', 'ife_setting_form_nonce' ); ?>
				<input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'import-facebook-events' ); ?>" />
			</div>
			</form>
	</div>
</div>
