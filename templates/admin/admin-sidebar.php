<?php
/**
 * Sidebar for Admin Pages
 *
 * @package     Import_Facebook_Events
 * @subpackage  Import_Facebook_Events/templates
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="upgrade_to_pro">
	<h2><?php esc_html_e( 'Upgrade to Pro', 'import-facebook-events' ); ?></h2>
	<p><?php esc_html_e( 'Unlock more power to events import operation, enable scheduled imports today, Upgrade today!!', 'import-facebook-events' ); ?></p>
	<a class="button button-primary ife-lh upgrade_button" href="<?php echo esc_url( IFE_PLUGIN_BUY_NOW_URL ); ?>" target="_blank">
		<?php esc_html_e( 'Upgrade to Pro', 'import-facebook-events' ); ?>
	</a>
</div>

<div class="upgrade_to_pro">
	<h2><?php esc_html_e( 'Custom WordPress Development Services', 'import-facebook-events' ); ?></h2>
	<p><?php esc_html_e( 'From small blog to complex web apps, we push the limits of what\'s possible with WordPress.', 'import-facebook-events' ); ?></p>
	<a class="button button-primary ife-lh upgrade_button" href="<?php echo esc_url( 'https://xylusthemes.com/contact/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin' ); ?>" target="_blank">
		<?php esc_html_e( 'Hire Us', 'import-facebook-events' ); ?>
	</a>
</div>

<div>
	<p style="text-align:center">
		<strong><?php esc_html_e( 'Would you like to remove these ads?', 'import-facebook-events' ); ?></strong><br>
		<a href="<?php echo esc_url( IFE_PLUGIN_BUY_NOW_URL ); ?>" target="_blank">
			<?php esc_html_e( 'Get Premium', 'import-facebook-events' ); ?>
		</a>
	</p>
</div>
