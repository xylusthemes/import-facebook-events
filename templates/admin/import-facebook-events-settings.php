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
$fb_users = get_option( 'ife_fb_users', array() );
?>
<div class="ife_container">
	<div class="ife_row">
		<h3 class="setting_bar"><?php esc_attr_e( 'Facebook Settings', 'import-facebook-events' ); ?></h3>
		<?php
		$site_url = get_home_url();
		if ( ! isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) { // WPCS: input var okay.
			?>
			<div class="widefat ife_settings_error">
				<?php printf( '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s', esc_attr__( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required for authorize your facebook account.", 'import-facebook-events' ), esc_attr__( 'Click here', 'import-facebook-events' ), esc_attr__( 'for more information.', 'import-facebook-events' ) ); ?>
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
			<br/>
			<strong><?php esc_attr_e( 'Set Valid OAuth redirect URI :', 'import-facebook-events' ); ?> </strong>
			<span style="color: green;"><?php echo esc_url( admin_url( 'admin-post.php?action=ife_facebook_authorize_callback' ) ); ?></span>
		</div>

		<?php
		if ( ! empty( $facebook_app_id ) && ! empty( $facebook_app_secret ) ) {
			?>
			<h4 class="setting_bar"><?php esc_attr_e( 'Authorize your Facebook Account', 'import-facebook-events' ); ?></h4>
			<div class="ife_accounts">
				<div class="bg-white sm:max-w-full max-w-md rounded overflow-hidden shadow-lg">
					<div class="border-b">
						<?php
						$add_label = esc_attr__( 'Connect Facebook account', 'import-facebook-events' );
						$connect_url = wp_nonce_url(admin_url('admin-post.php?action=ife_facebook_authorize_action'), 'ife_facebook_authorize_action', 'ife_facebook_authorize_nonce');
						if(!empty($fb_users)){
							$add_label = esc_attr__( 'Connect another Facebook account', 'import-facebook-events' );
							foreach($fb_users as $fb_user){
								?>
								<div class="px-6 py-3 hover:bg-gray-200 flex"> 
									<div class="mt-1 w-12 h-12 bg-blue-700 rounded-full text-center align-middle text-white text-lg content-center">
										<img class="rounded-full border-none" src="<?php echo $fb_user['avatar'];?>" />
									</div>
									<div class="pl-3">
									<p class="text-sm font-semibold">
										<?php echo $fb_user['name']; ?>
									</p>
									<p class="text-xs text-gray-600">
										<?php esc_attr_e( 'Facebook ID: ', 'import-facebook-events' );echo $fb_user['ID']; ?>
									</p>
									<p class="text-xs text-gray-600">
										<a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=ife_disconnect_user&fbuser_id='.$fb_user['ID']), 'ife_disconnect_user_action', 'ife_disconnect_user_nonce'); ?>" class="text-blue-600 font-medium">
											<?php esc_attr_e( 'Disconnect', 'import-facebook-events' ); ?>
										</a>
									</p>
									</div>
								</div>
								<?php
							}
						}
						?>
						<a href="<?php echo $connect_url; ?>" class="px-6 py-3 hover:bg-gray-200 flex">
							<div class="w-10 h-10 rounded-full text-center align-middle text-lg">
								<img class="w-10 h-10 rounded-full border-none mx-auto" src="<?php echo IFE_PLUGIN_URL.'assets/images/add-user-male.png'; ?>">
							</div>
							<div class="pl-3">
								<p class="text-sm font-semibold text-gray-700">
									<?php echo $add_label; ?>
								</p>
								<p class="text-xs text-gray-600">
									<?php esc_attr_e( 'Please connect your facebook account for import facebook events.', 'import-facebook-events' ); ?>
								</p>
							</div>
						</a>
					</div>
					<?php
					if( count($fb_users) > 0){
						?>
						<div class="border-b">
							<!-- First list item -->
							<div class="px-6 py-4 text-center">
								<a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=ife_disconnect_all_users'), 'ife_disconnect_all_users_action', 'ife_disconnect_all_users_nonce'); ?>" class="border rounded py-2 px-4 text-xs font-semibold text-gray-70">
									<?php esc_attr_e( 'Disconnect all accounts', 'import-facebook-events' ); ?>
								</a>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			
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
									<?php esc_attr_e( 'Please authorize your facebook account for import facebook events. Please authorize with account which you have used for create an facebook app.', 'import-facebook-events' ); ?>
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
							<?php esc_attr_e( 'Advanced Synchronization', 'import-facebook-events' ); ?> :
						</th>
						<td>
							<?php
							$advanced_sync = isset( $facebook_options['advanced_sync'] ) ? $facebook_options['advanced_sync'] : 'no';
							$checked       = ( 'yes' === $advanced_sync ) ? 'checked' : '';
							$disabled      = ( ! ife_is_pro() ) ? 'disabled' : '';
							?>
							<input type="checkbox" name="facebook[advanced_sync]" value="yes" <?php echo esc_attr( $checked . ' ' . $disabled ); ?> />
							<span>
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
