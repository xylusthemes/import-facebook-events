<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
$ife_options_temp = get_option( IFE_OPTIONS );
$ife_options = !empty( $ife_options_temp ) ? $ife_options_temp : array();
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
                            <input class="facebook_app_id" name="facebook[facebook_app_id]" type="text" value="<?php if ( isset( $ife_options['facebook_app_id'] ) ) { echo $ife_options['facebook_app_id']; } ?>" />
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
                            <input class="facebook_app_secret" name="facebook[facebook_app_secret]" type="text" value="<?php if ( isset( $ife_options['facebook_app_secret'] ) ) { echo $ife_options['facebook_app_secret']; } ?>" />
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
                            $update_facebook_events = isset( $ife_options['update_events'] ) ? $ife_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="facebook[update_events]" value="yes" <?php if( $update_facebook_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="xtei_small">
                                <?php _e( 'Check to updates existing events.', 'import-facebook-events' ); ?>
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
