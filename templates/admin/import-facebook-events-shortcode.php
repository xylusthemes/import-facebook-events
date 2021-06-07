<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ShortcodeTable = new IFE_Shortcode_List_Table();
$ShortcodeTable->prepare_items();

?>
<div class="ife_container">
    <div class="ife_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Shortcodes', 'import-facebook-events' ); ?></h3>
        <?php $ShortcodeTable->display(); ?>
    </div>
</div>