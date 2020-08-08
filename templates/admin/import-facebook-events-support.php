<?php
/**
 * The template for displaying Support and help.
 *
 * @package Import_Facebook_Events
 */

// If this file is called directly, abort.
// Icon Credit: Icon made by Freepik and Vectors Market from www.flaticon.com
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
			<h3 class="setting_bar"><?php esc_attr_e( 'Getting Support', 'import-facebook-events' ); ?></h3>
            <div class="ife-support-features">
				<div class="ife-support-features-card">
					<div class="ife-support-features-img">
						<img class="ife-support-features-icon" src="<?php echo IFE_PLUGIN_URL.'assets/images/document.svg'; ?>" alt="<?php esc_attr_e( 'Looking for Something?', 'import-facebook-events' ); ?>">
					</div>
					<div class="ife-support-features-text">
						<h3 class="ife-support-features-title"><?php esc_attr_e( 'Looking for Something?', 'import-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'We have documentation of how to import Facebook events.', 'import-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="http://docs.xylusthemes.com/docs/import-facebook-events/"><?php esc_attr_e( 'Plugin Documentation', 'import-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="ife-support-features-card">
					<div class="ife-support-features-img">
						<img class="ife-support-features-icon" src="<?php echo IFE_PLUGIN_URL.'assets/images/call-center.svg'; ?>" alt="<?php esc_attr_e( 'Need Any Assistance?', 'import-facebook-events' ); ?>">
					</div>
					<div class="ife-support-features-text">
						<h3 class="ife-support-features-title"><?php esc_attr_e( 'Need Any Assistance?', 'import-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Our EXPERT Support Team is always ready to Help you out.', 'import-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/support/"><?php esc_attr_e( 'Contact Support', 'import-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="ife-support-features-card">
					<div class="ife-support-features-img">
						<img class="ife-support-features-icon"  src="<?php echo IFE_PLUGIN_URL.'assets/images/bug.svg'; ?>" alt="<?php esc_attr_e( 'Found Any Bugs?', 'import-facebook-events' ); ?>" />
					</div>
					<div class="ife-support-features-text">
						<h3 class="ife-support-features-title"><?php esc_attr_e( 'Found Any Bugs?', 'import-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Report any Bug that you Discovered, Get Instant Solutions.', 'import-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://github.com/xylusthemes/import-facebook-events"><?php esc_attr_e( 'Report to GitHub', 'import-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="ife-support-features-card">
					<div class="ife-support-features-img">
						<img class="ife-support-features-icon" src="<?php echo IFE_PLUGIN_URL.'assets/images/tools.svg'; ?>" alt="<?php esc_attr_e( 'Require Customization?', 'import-facebook-events' ); ?>" />
					</div>
					<div class="ife-support-features-text">
						<h3 class="ife-support-features-title"><?php esc_attr_e( 'Require Customization?', 'import-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'We would Love to hear your Integration and Customization Ideas.', 'import-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/what-we-do/"><?php esc_attr_e( 'Connect Our Service', 'import-facebook-events' ); ?></a>
					</div>
				</div>
				<div class="ife-support-features-card">
					<div class="ife-support-features-img">
						<img class="ife-support-features-icon" src="<?php echo IFE_PLUGIN_URL.'assets/images/like.svg'; ?>" alt="<?php esc_attr_e( 'Like The Plugin?', 'import-facebook-events' ); ?>" />
					</div>
					<div class="ife-support-features-text">
						<h3 class="ife-support-features-title"><?php esc_attr_e( 'Like The Plugin?', 'import-facebook-events' ); ?></h3>
						<p><?php esc_attr_e( 'Your Review is very important to us as it helps us to grow more.', 'import-facebook-events' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/import-facebook-events/reviews/?rate=5#new-post"><?php esc_attr_e( 'Review Us on WP.org', 'import-facebook-events' ); ?></a>
					</div>
				</div>
			</div>
		</div>

		<?php
		$org_plugins = array();
		$plugin_list = $ife_events->admin->get_xyuls_themes_plugins();
		if ( ! empty( $plugin_list ) ) {
			foreach ( $plugin_list as $key => $value ) {
				$org_plugins[] = $ife_events->admin->get_wporg_plugin( $key );
			}
		}
		?>
		<div class="" style="margin-top: 20px;">
			<h3 class="setting_bar"><?php esc_attr_e( 'Plugins you should try', 'import-facebook-events' ); ?></h3>
			<?php
			if ( ! empty( $org_plugins ) ) {
				foreach ( $org_plugins as $plugin ) {
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
