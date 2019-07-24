<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ife_events;
$ife_options = get_option( IFE_OPTIONS );
$facebook_options = isset( $ife_options )? $ife_options : array();
$facebook_app_id = isset( $facebook_options['facebook_app_id'] ) ? $facebook_options['facebook_app_id'] : '';
$facebook_app_secret = isset( $facebook_options['facebook_app_secret'] ) ? $facebook_options['facebook_app_secret'] : '';
$ife_user_token_options = get_option( 'ife_user_token_options', array() );
$ife_fb_authorize_user = get_option( 'ife_fb_authorize_user', array() );
?>
<div class="ife_container">
    <div class="ife_row">
        <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Settings', 'import-facebook-events' ); ?></h3>
        <?php
        $site_url = get_home_url();
        if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) {
            ?>
            <div class="widefat ife_settings_error">
                <?php printf( '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s', __( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required for authorize your facebook account.","import-facebook-events" ), __( 'Click here','import-facebook-events' ), __( 'for more information.','import-facebook-events' ) ); ?>
            </div>
        <?php
        } ?>
        <div class="widefat ife_settings_notice">
            <?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s',  __( 'Note : ','import-facebook-events' ), __( 'You have to create a Facebook application before filling the following details.','import-facebook-events' ), __( 'Click here','import-facebook-events' ),  __( 'to create new Facebook application.','import-facebook-events' ) ); ?>
            <br/>
            <?php _e( 'For detailed step by step instructions ', 'import-facebook-events' ); ?>
            <strong><a href="http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php _e( 'Click here', 'import-facebook-events' ); ?></a></strong>.
            <br/>
            <?php _e( '<strong>Set the site url as : </strong>', 'import-facebook-events' ); ?>
            <span style="color: green;"><?php echo get_site_url(); ?></span>
            <br/>
            <?php _e( '<strong>Set Valid OAuth redirect URI : </strong>', 'import-facebook-events' ); ?>
            <span style="color: green;"><?php echo admin_url( 'admin-post.php?action=ife_facebook_authorize_callback' ); ?></span>
        </div>

        <?php
        if( $facebook_app_id != '' && $facebook_app_secret != '' ){
            ?>
            <h4 class="setting_bar"><?php esc_attr_e( 'Authorize your Facebook Account', 'import-facebook-events' ); ?></h4>
            <div class="fb_authorize">
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <?php _e( 'Facebook Authorization','import-facebook-events' ); ?> :
                            </th>
                            <td>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                                    <input type="hidden" name="action" value="ife_facebook_authorize_action"/>
                                    <?php wp_nonce_field('ife_facebook_authorize_action', 'ife_facebook_authorize_nonce'); ?>
                                    <?php
                                    $button_value = __('Authorize', 'import-facebook-events');
                                    if( isset( $ife_user_token_options['authorize_status'] ) && $ife_user_token_options['authorize_status'] == 1 && isset(  $ife_user_token_options['access_token'] ) &&  $ife_user_token_options['access_token'] != '' ){
                                        $button_value = __('Reauthorize', 'import-facebook-events');
                                    }
                                    ?>
                                    <input type="submit" class="button" name="ife_facebook_authorize" value="<?php echo $button_value; ?>" />
                                    <?php
                                    if( !empty( $ife_fb_authorize_user ) && isset( $ife_fb_authorize_user['name'] ) && $ife_events->common->has_authorized_user_token() ){
                                        $fbauthname = sanitize_text_field( $ife_fb_authorize_user['name'] );
                                        if( $fbauthname != '' ){
                                           printf( __(' ( Authorized as: %s )', 'import-facebook-events'), '<b>'.$fbauthname.'</b>' );
                                        }
                                    }
                                    ?>
                                </form>

                                <span class="ife_small">
                                    <?php _e( 'Please authorize your facebook account for import facebook events. Please authorize with account which you have used for create an facebook app.','import-facebook-events' ); ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>
        <form method="post" id="ife_setting_form">
            <table class="form-table">
                <tbody>
                    <?php do_action( 'ife_before_settings_section' ); ?>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App ID','import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_id" name="facebook[facebook_app_id]" type="text" value="<?php if ( $facebook_app_id != '' ) { echo $facebook_app_id; } ?>" />
                            <span class="ife_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can view or create your Facebook Apps', 'import-facebook-events'),
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
                            <input class="facebook_app_secret" name="facebook[facebook_app_secret]" type="text" value="<?php if ( $facebook_app_secret != '' ) { echo $facebook_app_secret; } ?>" />
                            <span class="ife_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can view or create your Facebook Apps', 'import-facebook-events'),
                                    __('from here', 'import-facebook-events')
                                 );
                                ?>
                            </span>
                        </td>
                    </tr>
                    <?php do_action( 'ife_after_app_settings' ); ?>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Update existing events', 'import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $update_facebook_events = isset( $facebook_options['update_events'] ) ? $facebook_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" id="update_events" name="facebook[update_events]" value="yes" <?php if( $update_facebook_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="ife_small">
                                <?php _e( 'Check to updates existing events.', 'import-facebook-events' ); ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Advanced Synchronization', 'import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $advanced_sync = isset( $facebook_options['advanced_sync'] ) ? $facebook_options['advanced_sync'] : 'no';
                            ?>
                            <input type="checkbox" name="facebook[advanced_sync]" value="yes" <?php if( $advanced_sync == 'yes' ) { echo 'checked="checked"'; } ?> <?php if( !ife_is_pro() ){ echo 'disabled="disabled"'; } ?>/>
                            <span>
                                <?php _e( 'Check to enable advanced synchronization, this will delete events which are removed from Facebook. Also, it deletes passed events.', 'import-facebook-events' ); ?>
                            </span>
                            <?php do_action( 'ife_render_pro_notice' ); ?>
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
                            <span class="ife_small">
                                <?php _e( 'Check to disable inbuilt event management system.', 'import-facebook-events' ); ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Accent Color', 'import-facebook-events' ); ?> : 
                        </th>
                        <td>
                        <?php 
                        $accent_color = isset( $facebook_options['accent_color'] ) ? $facebook_options['accent_color'] : '#039ED7';
                        ?>
                        <input class="ife_color_field" type="text" name="facebook[accent_color]" value="<?php esc_attr_e( $accent_color ); ?>"/>
                        <span class="ife_small">
                            <?php _e( 'Choose accent color for front-end event grid and event widget.', 'import-facebook-events', 'import-facebook-events' ); ?>
                        </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Delete Import Facebook Events data on Uninstall', 'import-facebook-events' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $delete_ifedata = isset( $facebook_options['delete_ifedata'] ) ? $facebook_options['delete_ifedata'] : 'no';
                            ?>
                            <input type="checkbox" name="facebook[delete_ifedata]" value="yes" <?php if( $delete_ifedata == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="ife_small">
                                <?php _e( 'Delete Import Facebook Events data like settings, scheduled imports, import history on Uninstall.', 'import-facebook-events' ); ?>
                            </span>
                        </td>
                    </tr>
                    <?php do_action( 'ife_after_settings_section' ); ?>
                
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
