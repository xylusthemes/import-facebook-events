<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $importfbevents;
?>
<div class="ife_container">
    <div class="ife_row">
    	<div class="wpea-column ife_well">
            <h3><?php esc_attr_e( 'Facebook Import', 'import-facebook-events' ); ?></h3>
            <form method="post" id="ife_facebook_form">
           	
               	<table class="form-table">
		            <tbody>
		                <tr>
					        <th scope="row">
					        	<?php esc_attr_e( 'Import by','import-facebook-events' ); ?> :
					        </th>
					        <td>
					            <select name="facebook_import_by" id="facebook_import_by">
			                    	<option value="facebook_event_id"><?php esc_attr_e( 'Facebook Event ID','import-facebook-events' ); ?></option>
			                    	<option value="facebook_organization"><?php esc_attr_e( 'Facebook Organization or Page','import-facebook-events' ); ?></option>
			                    </select>
			                    <span class="ife_small">
			                        <?php _e( 'Select Event source. 1. by Facebook Event ID, 2. Facebook Organization or Page ( import events belonging to a Facebook organization or a Facebook page ).', 'import-facebook-events' ); ?>
			                    </span>
					        </td>
					    </tr>
					    
					    <tr class="facebook_eventid_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Facebook Event IDs','import-facebook-events' ); ?> : 
					    	</th>
					    	<td>
					    		<textarea name="facebook_event_ids" class="facebook_event_ids" rows="5" cols="50"></textarea>
					    		<span class="ife_small">
			                        <?php _e( 'One event ID per line, ( Eg. Event ID for https://www.facebook.com/events/123456789/ is "123456789" ).', 'import-facebook-events' ); ?>
			                    </span>
					    	</td>
					    </tr>

					    <tr class="facebook_page_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Organization and page username / ID to fetch events from','import-facebook-events' ); ?> : 
					    	</th>
					    	<td> 
					    		<input class="ife_text" name="facebook_page_username" class="facebook_page_username" type="text" disabled="disabled" />
			                    <span class="ife_small">
			                        <?php _e( ' Eg. username for https://www.facebook.com/xylusinfo/ is "xylusinfo".', 'import-facebook-events' ); ?>
			                    </span>
			                    <?php do_action( 'ife_render_pro_notice'); ?>
					    	</td>
					    </tr>

					    <tr class="import_type_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Import type','import-facebook-events' ); ?> : 
					    	</th>
					    	<td>
						    	<?php ife_render_import_type(); ?>
					    	</td>
					    </tr>

					    <?php 
					    ife_render_eventstatus_input();
					    ife_render_em_category_input();
					    ?>

					</tbody>
		        </table>
                
                <div class="ife_element">
                	<input type="hidden" name="import_origin" value="facebook_em" />
                    <input type="hidden" name="ife_action" value="ife_import_submit" />
                    <?php wp_nonce_field( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary ife_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-facebook-events' ); ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
