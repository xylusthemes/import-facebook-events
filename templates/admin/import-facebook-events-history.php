<?php
/**
 * Template file for Events import history.
 *
 * @package Import_Facebook_Events
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $ife_events;
// Add Thickbox support.
add_thickbox();
$listtable = new Import_Facebook_Events_History_List_Table();
$listtable->prepare_items();
?>
<div class="ife_container">
	<div class="ife_row">
		<div class="">
			<form id="import-history" method="get">
				<input type="hidden" name="page" value="facebook_import" />
				<input type="hidden" name="tab" value="history" />
				<?php
				$listtable->display();
				?>
			</form>
		</div>
	</div>
</div>
