<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ife_events;
?>
<div class="ife_container">
    <div class="ife_row">
        <div class="">
			<form id="scheduled-import" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? $_REQUEST['tab'] : 'scheduled' ?>" />
				<input type="hidden" name="ntab" value="" />
				<?php
				do_action( 'ife_render_pro_notice' );
        		?>
			</form>
        </div>
    </div>
</div>
