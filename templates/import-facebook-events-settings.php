<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
$ife_options = get_option( IFE_OPTIONS );
$facebook_options = isset( $ife_options )? $ife_options : array();
?>
<div class="ife_container">
    <div class="ife_row">
    	
    	<form method="post" id="ife_setting_form">                

            <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Settings', 'import-facebook-events' ); ?></h3>
            <p><?php _e( 'You need a Facebook App ID and App Secret to import your events from Facebook.','import-facebook-events' ); ?> </p>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App ID','import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_id" name="facebook[facebook_app_id]" type="text" value="<?php if ( isset( $facebook_options['facebook_app_id'] ) ) { echo $facebook_options['facebook_app_id']; } ?>" />
                            <span class="xtei_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can veiw or create your Facebook Apps', 'import-facebook-events'),
                                    __('from here', 'import-facebook-events')
                                 );
                                ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App secret','import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_secret" name="facebook[facebook_app_secret]" type="text" value="<?php if ( isset( $facebook_options['facebook_app_secret'] ) ) { echo $facebook_options['facebook_app_secret']; } ?>" />
                            <span class="xtei_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can veiw or create your Facebook Apps', 'import-facebook-events'),
                                    __('from here', 'import-facebook-events')
                                 );
                                ?>
                            </span>
                        </td>
                    </tr>       
                    
                    <tr>
                        <th scope="row">
                            <?php _e( 'Update existing events', 'import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $update_facebook_events = isset( $facebook_options['update_events'] ) ? $facebook_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="facebook[update_events]" value="yes" <?php if( $update_facebook_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="xtei_small">
                                <?php _e( 'Check to updates existing events.', 'import-facebook-events' ); ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Disable Facebook events', 'import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $deactive_fbevents = isset( $facebook_options['deactive_fbevents'] ) ? $facebook_options['deactive_fbevents'] : 'no';
                            ?>
                            <input type="checkbox" name="facebook[deactive_fbevents]" value="yes" <?php if( $deactive_fbevents == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="xtei_small">
                                <?php _e( 'Check to disable inbuilt event management system.', 'import-facebook-events' ); ?>
                            </span>
                        </td>
                    </tr>
                
                </tbody>
            </table>
            <br/>

            <div class="ife_element">
                <input type="hidden" name="ife_action" value="ife_save_settings" />
                <?php wp_nonce_field( 'ife_setting_form_nonce_action', 'ife_setting_form_nonce' ); ?>
                <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'import-facebook-events' ); ?>" />
            </div>
            </form>
    </div>
</div>
