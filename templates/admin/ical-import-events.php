<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ife_events;
?>
<div class="ife_container">
    <div class="ife_row">

        <div class="ife-column ife_well">
            <h3><?php esc_attr_e( 'iCal / .ics Import', 'import-facebook-events' ); ?></h3>
            <form method="post" enctype="multipart/form-data" id="ife_ics_form">

				<table class="form-table">
		            <tbody>
		                <tr>
					        <th scope="row">
								<?php esc_attr_e( 'Import by','import-facebook-events' ); ?> :
					        </th>
					        <td>
					            <select name="ical_import_by" id="ical_import_by">
									<option value="ics_file"><?php esc_attr_e( '.ics File','import-facebook-events' ); ?></option>
									<option value="ical_url" <?php if( ife_is_pro() ){ echo 'selected="selected"'; } ?> ><?php esc_attr_e( 'iCal URL','import-facebook-events' ); ?></option>
			                    </select>
			                    <span class="ife_small">
			                        <?php _e( 'Select Event source.', 'import-facebook-events' ); ?>
			                    </span>
					        </td>
					    </tr>

						<tr class="ical_url_wrapper">
							<th scope="row">
								<?php esc_attr_e( 'iCal URL','import-facebook-events' ); ?> :
							</th>
							<td>
								<input class="ife_text ical_url" name="ical_url" type="text" <?php if( !ife_is_pro() ){ echo 'disabled="disabled"'; } ?>/>
								<span class="ife_small">
									<?php _e( 'Enter iCal URL ( Eg. webcal://www.facebook.com/ical/u.php?uid=XXXXXXXXXXXXXX&key=XXXXXXXXXXXXXX )', 'import-facebook-events' ); ?>
								</span>
								<?php do_action( 'ife_render_pro_notice' ); ?>
							</td>
					    </tr>

					    <tr class="ics_file_wrapper">
							<th scope="row">
								<?php esc_attr_e( '.ics File','import-facebook-events' ); ?> :
							</th>
							<td>
								<input class="ife_text ics_file_class" name="ics_file" type="file" accept=".ics" />
							</td>
					    </tr>

					    <tr class="import_date_range">
					        <th scope="row">
								<?php esc_attr_e( 'Events date range','import-facebook-events' ); ?> :
					        </th>
					        <td>
					            <input type="text" name="start_date" class="ife_datepicker start_date" placeholder="<?php esc_html_e('Select start date', 'import-facebook-events' ); ?>"> - 
					            <input type="text" name="end_date" class="ife_datepicker end_date" placeholder="<?php esc_html_e('Select end date', 'import-facebook-events' ); ?>">
			                    <span class="ife_small">
			                        <?php _e( 'Select date range from which you want to import events. Default startdate is Today', 'import-facebook-events' ); ?>
			                    </span>
					        </td>
					    </tr>

					    <tr class="import_type_wrapper">
							<th scope="row">
								<?php esc_attr_e( 'Import type','import-facebook-events' ); ?> :
							</th>
							<td>
								<?php $ife_events->common->render_import_type(); ?>
							</td>
					    </tr>

					    <?php
					    $ife_events->common->render_import_into_and_taxonomy();
					    $ife_events->common->render_eventstatus_input();
					    ?>

					</tbody>
		        </table>

                <div class="ife_element">
					<input type="hidden" name="import_origin" value="ical" />
                    <input type="hidden" name="ife_action" value="ife_import_submit" />
                    <?php wp_nonce_field( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary ife_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-facebook-events' ); ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
