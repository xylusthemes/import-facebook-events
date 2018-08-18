<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ife_events;
?>
<div class="ife_container">
    <div class="ife_row">
    	<div class="ife-column ife_well">
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

			                    	<option value="facebook_organization"><?php esc_attr_e( 'Facebook Page','import-facebook-events' ); ?></option>
			                    	
			                    	<?php if( $ife_events->common->has_authorized_user_token() ){ ?>
			                    		<option value="facebook_group"><?php esc_attr_e( 'Facebook Group','import-facebook-events' ); ?></option>
			                    		<option value="my_events"><?php esc_attr_e( 'My Events','import-facebook-events' ); ?></option>
			                    	<?php } ?>

			                    </select>
			                    <span class="ife_small">
			                        <?php _e( 'Select Event source. <strong>1. by Facebook Event ID</strong>, <strong>2. Facebook Organization or Page</strong> ( import events belonging to a Facebook organization or a Facebook page ).', 'import-facebook-events' ); ?><br/>
			                        <?php
			                        if( $ife_events->common->has_authorized_user_token() ){
			                        	_e( '<strong>3. Facebook Group</strong> (Import events from facebook group), <strong>4. My Events</strong> (Import events which you have marked intrested or going on facebook, this also include your events on facebook)', 'import-facebook-events' );
			                        } ?>
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

					    <tr class="facebook_page_wrapper" style="display: none;">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Page username / ID to fetch events from','import-facebook-events' ); ?> : 
					    	</th>
					    	<td> 
					    		<input class="ife_text facebook_page_username" name="facebook_page_username" type="text" <?php if( !ife_is_pro() ){ echo 'disabled="disabled"'; } ?>/>
			                    <span class="ife_small">
			                        <?php _e( ' Eg. username for https://www.facebook.com/xylusinfo/ is "xylusinfo".', 'import-facebook-events' ); ?>
			                    </span>
			                    <?php do_action( 'ife_render_pro_notice' ); ?>
					    	</td>
					    </tr>

					    <tr class="facebook_group_wrapper" style="display: none;">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Facebook Group URL / Numeric ID to fetch events from','import-facebook-events' ); ?> : 
					    	</th>
					    	<td> 
					    		<input class="ife_text facebook_group" name="facebook_group_id" type="text" <?php if( !ife_is_pro() ){ echo 'disabled="disabled"'; } ?> />
			                    <span class="ife_small">
			                        <?php _e( ' Eg.Input value for https://www.facebook.com/groups/123456789123456/ <br/>https://www.facebook.com/groups/123456789123456/ OR "123456789123456"', 'import-facebook-events' ); ?>
			                    </span>
			                    <?php do_action( 'ife_render_pro_notice' ); ?>
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
                	<input type="hidden" name="import_origin" value="facebook" />
                    <input type="hidden" name="ife_action" value="ife_import_submit" />
                    <?php wp_nonce_field( 'ife_import_form_nonce_action', 'ife_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary ife_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'import-facebook-events' ); ?>" />
                </div>
            </form>
        </div>
    </div>
</div>
