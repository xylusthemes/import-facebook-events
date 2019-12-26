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
?>
<div class="wpea_container">
	<div class="wpea_row">
		<div class="wrap"style="min-width: 100%;">
            <h3 class="setting_bar"><?php esc_attr_e( 'Getting Support', 'import-eventbrite-events' ); ?></h3>
            <div class="xylus-support-page">
                <div class="support-block">
                    <img src="<?php echo IFE_PLUGIN_URL.'assets/images/target.png'; ?>" alt="Looking for Something?">
                    <h3>Looking for Something?</h3>
                    <p>We have documentation of how to import eventbrite events.</p>
                    <a target="_blank" class="button button-primary" href="https://docs.xylusthemes.com/docs/import-facebook-events/">Visit the Plugin Documentation</a>
                </div>

                <div class="support-block">
                    <img src="<?php echo IFE_PLUGIN_URL.'assets/images/assistance.png'; ?>" alt="Need Any Assistance?">
                    <h3>Need Any Assistance?</h3>
                    <p>Our EXPERT Support Team is always ready to Help you out.</p>
                    <a target="_blank" class="button button-primary" href="https://xylusthemes.com/support/">Contact Support</a>
                </div>

                <div class="support-block">
                    <img src="<?php echo IFE_PLUGIN_URL.'assets/images/bug.png'; ?>" alt="Found Any Bugs?">
                    <h3>Found Any Bugs?</h3>
                    <p>Report any Bug that you Discovered, Get Instant Solutions.</p>
                    <a target="_blank" class="button button-primary" href="https://github.com/xylusthemes/import-facebook-events">Report to GitHub</a>
                </div>

                <div class="support-block">
                    <img src="<?php echo IFE_PLUGIN_URL.'assets/images/tools.png'; ?>" alt="Require Customization?">
                    <h3>Require Customization?</h3>
                    <p>We would Love to hear your Integration and Customization Ideas.</p>
                    <a target="_blank" class="button button-primary" href="https://xylusthemes.com/what-we-do/">Connect Our Service</a>
                </div>

                <div class="support-block">
                    <img src="<?php echo IFE_PLUGIN_URL.'assets/images/like.png'; ?>" alt="Like The Plugin?">
                    <h3>Like The Plugin?</h3>
                    <p>Your Review is very important to us as it helps us to grow more.</p>
                    <a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/import-facebook-events/reviews/?rate=5#new-post">Review US on WP.org</a>
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
