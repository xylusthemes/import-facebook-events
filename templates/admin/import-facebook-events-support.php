<?php
/**
 * The template for displaying Support and help.
 *
 * @package Import_Facebook_Events
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $ife_events;
$open_source_support_url = 'https://wordpress.org/support/plugin/import-facebook-events/';
$support_url             = 'https://xylusthemes.com/support/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin';

$review_url   = 'https://wordpress.org/support/plugin/import-facebook-events/reviews/?rate=5#new-post';
$facebook_url = 'https://www.facebook.com/xylusinfo/';
$twitter_url  = 'https://twitter.com/XylusThemes/';

?>
<div class="wpea_container">
	<div class="wpea_row">
		<div class="wpea-column support_well">
			<h3><?php esc_attr_e( 'Getting Support', 'import-facebook-events' ); ?></h3>
			<p><?php esc_attr_e( 'Thanks you for using Import Facebook Events, We are sincerely appreciate your support and we’re excited to see you using our plugins.', 'import-facebook-events' ); ?> </p>
			<p><?php esc_attr_e( 'Our support team is always around to help you.', 'import-facebook-events' ); ?></p>

							<p><strong><?php esc_attr_e( 'Looking for free support?', 'import-facebook-events' ); ?></strong></p>
			<a class="button button-secondary" href="<?php echo esc_url( $open_source_support_url ); ?>" target="_blank" >
				<?php esc_attr_e( 'Open-source forum on WordPress.org', 'import-facebook-events' ); ?>
			</a>

			<p><strong><?php esc_attr_e( 'Looking for more immediate support?', 'import-facebook-events' ); ?></strong></p>
			<p><?php esc_attr_e( 'We offer premium support on our website with the purchase of our premium plugins.', 'import-facebook-events' ); ?>
			</p>

			<a class="button button-primary" href="<?php echo esc_url( $support_url ); ?>" target="_blank" >
				<?php esc_attr_e( 'Contact us directly (Premium Support)', 'import-facebook-events' ); ?>
			</a>

			<p><strong><?php esc_attr_e( 'Enjoying Import Facebook Events or have feedback?', 'import-facebook-events' ); ?></strong></p>
			<a class="button button-secondary" href="<?php echo esc_url( $review_url ); ?>" target="_blank" ><?php esc_attr_e( 'Leave us a review', 'import-facebook-events' ); ?></a> 
			<a class="button button-secondary" href="<?php echo esc_url( $twitter_url ); ?>" target="_blank" ><?php esc_attr_e( 'Follow us on Twitter', 'import-facebook-events' ); ?></a> 
			<a class="button button-secondary" href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" ><?php esc_attr_e( 'Like us on Facebook', 'import-facebook-events' ); ?></a>
		</div>

		<?php
		$plugins     = array();
		$plugin_list = $ife_events->admin->get_xyuls_themes_plugins();
		if ( ! empty( $plugin_list ) ) {
			foreach ( $plugin_list as $key => $value ) {
				$plugins[] = $ife_events->admin->get_wporg_plugin( $key );
			}
		}
		?>
		<div class="" style="margin-top: 20px;">
			<h3 class="setting_bar"><?php esc_attr_e( 'Plugins you should try', 'import-facebook-events' ); ?></h3>
			<?php
			if ( ! empty( $plugins ) ) {
				foreach ( $plugins as $plugin ) {
					?>
					<div class="plugin_box">
						<?php if ( ! empty( $plugin->banners['low'] ) ) { ?>
							<img src="<?php echo esc_url( $plugin->banners['low'] ); ?>" class="plugin_img" title="<?php echo esc_attr( $plugin->name ); ?>">
						<?php } ?>                    
						<div class="plugin_content">
							<h3><?php echo esc_attr( $plugin->name ); ?></h3>

							<?php
							wp_star_rating(
								array(
									'rating' => $plugin->rating,
									'type'   => 'percent',
									'number' => $plugin->num_ratings,
								)
							);
								?>

							<?php if ( ! empty( $plugin->version ) ) { ?>
								<p><strong><?php esc_attr_e( 'Version:', 'import-facebook-events' ); ?> </strong><?php echo esc_attr( $plugin->version ); ?></p>
							<?php } ?>

							<?php if ( ! empty( $plugin->requires ) ) { ?>
								<p><strong><?php esc_attr_e( 'Requires:', 'import-facebook-events' ); ?> </strong> 
									<?php
									esc_attr_e( 'WordPress ', 'import-facebook-events' );
									echo esc_attr( $plugin->requires ) . '+';
									?>
								</p>
							<?php } ?>

							<?php if ( ! empty( $plugin->active_installs ) ) { ?>
								<p><strong><?php esc_attr_e( 'Active Installs:', 'import-facebook-events' ); ?> </strong><?php echo esc_attr( $plugin->active_installs ); ?>+</p>
							<?php } ?>

							<a class="button button-secondary" href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . esc_attr( $plugin->slug ) . '&TB_iframe=1&width=772&height=600' ) ); ?>" target="_blank">
								<?php esc_attr_e( 'Install Now', 'import-facebook-events' ); ?>
							</a>
							<a class="button button-primary" href="<?php echo esc_url( $plugin->homepage ) . '?utm_source=crosssell&utm_medium=web&utm_content=supportpage&utm_campaign=freeplugin'; ?>" target="_blank">
								<?php esc_attr_e( 'Buy Now', 'import-facebook-events' ); ?>
							</a>
						</div>
					</div>
					<?php
				}
			}
			?>
			<div style="clear: both;">
		</div>
	</div>

</div>
