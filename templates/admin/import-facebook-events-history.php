<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ife_events;
?>
<div class="ife_container">
    <div class="ife_row">
        <div class="">
            <form id="import-history" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? $_REQUEST['tab'] : 'history' ?>" />
				<input type="hidden" name="ntab" value="" />
        		<?php
				$listtable = new Import_Facebook_Events_History_List_Table();
				$listtable->prepare_items();
				$listtable->display();
        		?>
			</form>
        </div>
    </div>
</div>