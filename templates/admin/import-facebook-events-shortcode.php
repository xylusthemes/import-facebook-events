<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$shortcode_table = new IFE_Shortcode_List_Table();
$shortcode_table->prepare_items();

?>
<div class="ife_container">
    <div class="ife_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Shortcodes', 'import-facebook-events' ); ?></h3>
        <?php $shortcode_table->display(); ?>
    </div>
</div>
