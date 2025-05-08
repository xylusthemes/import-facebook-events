<?php
/**
 * Template file for admin import events form.
 *
 * @package Import_Facebook_Events
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $ife_events;
$counts = $ife_events->common->ife_get_facebook_events_counts();

?>
<div class="ife-container" style="margin-top: 60px;">
    <div class="ife-wrap" >
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <?php 
                    do_action( 'ife_display_all_notice' );
                ?>
                <div class="delete_notice"></div>
                <div id="postbox-container-2" class="postbox-container">
                    <div class="ife-app">
                        <div class="ife-card" style="margin-top:20px;" >			
                            <div class="ife-content"  aria-expanded="true"  >
                                <div id="ife-dashboard" class="wrap about-wrap" >
                                    <div class="ife-w-row" >
                                        <div class="ife-intro-section" >
                                            <div class="ife-w-box-content ife-intro-section-welcome" >
                                                <h3><?php esc_attr_e( 'Getting started with Import Facebook Events', 'import-facebook-events' ); ?></h3>
                                                <p style="margin-bottom: 25px;"><?php esc_attr_e( 'In this video, you can learn how to Import Facebook event into your website. Please watch this 5 minutes video to the end.', 'import-facebook-events' ); ?></p>
                                            </div>
                                            <div class="ife-w-box-content ife-intro-section-ifarme" >
                                                <iframe width="850" height="450" src="https://www.youtube.com/embed/OtiUJlZ4R4E?si=QO0qStRnwyscKBzX" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen=""></iframe>
                                            </div>
                                            <div class="ife-intro-section-links wp-core-ui" >
                                                <a class="ife-intro-section-link-tag button ife-button-primary button-hero" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=facebook_events' ) ); ?>" target="_blank"><?php esc_attr_e( 'Add New Event', 'import-facebook-events' ); ?></a>
                                                <a class="ife-intro-section-link-tag button ife-button-secondary button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=facebook_import&tab=settings' ) ); ?>"target="_blank"><?php esc_attr_e( 'Settings', 'import-facebook-events' ); ?></a>
                                                <a class="ife-intro-section-link-tag button ife-button-secondary button-hero" href="https://docs.xylusthemes.com/docs/import-facebook-events/" target="_blank"><?php esc_attr_e( 'Documentation', 'import-facebook-events' ); ?></a>
                                            </div>
                                        </div>

                                        <div class="ife-counter-main-container" >
                                            <div class="ife-col-sm-3" >
                                                <div class="ife-w-box " >
                                                    <p class="ife_dash_count"><?php echo esc_attr( $counts['all'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Total Events', 'import-facebook-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="ife-col-sm-3" >
                                                <div class="ife-w-box " >
                                                    <p class="ife_dash_count"><?php echo esc_attr( $counts['upcoming'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Upcoming Events', 'import-facebook-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="ife-col-sm-3" >
                                                <div class="ife-w-box " >
                                                    <p class="ife_dash_count"><?php echo esc_attr( $counts['past'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Past Events', 'import-facebook-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="ife-col-sm-3" >
                                                <div class="ife-w-box " >
                                                    <p class="ife_dash_count"><?php echo esc_attr( IFE_VERSION ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Version', 'import-facebook-events' ); ?></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>
