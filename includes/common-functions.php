<?php
/**
 * Common functions
 *
 * @package     Import_Facebook_Events
 * @subpackage  Common functions
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render multi-select category input for TEC import.
 *
 * @since 1.0
 * @return void
*/
function ife_render_tec_category_input(){
	$ife_event_cats = get_terms( IFE_TEC_TAXONOMY, array( 'hide_empty' => 0 ) );
	?>
	<tr class="event_cats_wrapper">
		<th scope="row">
			<?php esc_attr_e( 'Event Categories for Event Import','import-facebook-events' ); ?> : 
		</th>
		<td>
			<select name="event_cats[]" multiple="multiple">
	            <?php if( ! empty( $ife_event_cats ) ): ?>
	                <?php foreach ($ife_event_cats as $ife_cat ): ?>
	                    <option value="<?php echo $ife_cat->term_id; ?>">
	                    	<?php echo $ife_cat->name; ?>                                	
	                    </option>
	                <?php endforeach; ?>
	            <?php endif; ?>
	        </select>
	        <span class="ife_small">
	            <?php esc_attr_e( 'These categories are assign to imported event.', 'import-facebook-events' ); ?>
	        </span>
		</td>
	</tr>
	<?php
}

/**
 * Render multi-select category input for EM import.
 *
 * @since 1.0
 * @return void
*/
function ife_render_em_category_input(){
	$ife_event_cats = get_terms( IFE_EM_TAXONOMY, array( 'hide_empty' => 0 ) );
	?>
	<tr class="event_cats_wrapper">
		<th scope="row">
			<?php esc_attr_e( 'Event Categories for Event Import','import-facebook-events' ); ?> : 
		</th>
		<td>
			<select name="event_cats[]" multiple="multiple">
	            <?php if( ! empty( $ife_event_cats ) ): ?>
	                <?php foreach ($ife_event_cats as $ife_cat ): ?>
	                    <option value="<?php echo $ife_cat->term_id; ?>">
	                    	<?php echo $ife_cat->name; ?>                                	
	                    </option>
	                <?php endforeach; ?>
	            <?php endif; ?>
	        </select>
	        <span class="ife_small">
	            <?php esc_attr_e( 'These categories are assign to imported event.', 'import-facebook-events' ); ?>
	        </span>
		</td>
	</tr>
	<?php
}


/**
 * Get Import events setting options
 *
 * @since 1.0
 * @return void
*/
function ife_get_import_options( $type = '' ){

	$ife_options = get_option( IFE_OPTIONS );
	if( $type != '' ){
		$ife_options = isset( $ife_options[$type] ) ? $ife_options[$type] : array();	
	}

	return $ife_options;	
}


/**
 * Render dropdown for Imported event status.
 *
 * @since 1.0
 * @return void
*/
function ife_render_eventstatus_input(){
	?>
	<tr class="event_status_wrapper">
		<th scope="row">
			<?php esc_attr_e( 'Status','import-facebook-events' ); ?> :
		</th>
		<td>
			<select name="event_status" >
                <option value="publish">
                    <?php esc_html_e( 'Published','import-facebook-events' ); ?>
                </option>
                <option value="pending">
                    <?php esc_html_e( 'Pending','import-facebook-events' ); ?>
                </option>
                <option value="draft">
                    <?php esc_html_e( 'Draft','import-facebook-events' ); ?>
                </option>
            </select>
		</td>
	</tr>
	<?php
}

function ife_render_import_frequency(){
	?>
	<select name="import_frequency" class="import_frequency" >
        <option value='hourly'>
            <?php esc_html_e( 'Once Hourly','import-facebook-events' ); ?>
        </option>
        <option value='twicedaily'>
            <?php esc_html_e( 'Twice Daily','import-facebook-events' ); ?>
        </option>
        <option value="daily" selected="selected">
            <?php esc_html_e( 'Once Daily','import-facebook-events' ); ?>
        </option>
        <option value="weekly" >
            <?php esc_html_e( 'Once Weekly','import-facebook-events' ); ?>
        </option>
        <option value="monthly">
            <?php esc_html_e( 'Once a Month','import-facebook-events' ); ?>
        </option>
    </select>
	<?php
}

function ife_render_import_type(){
	?>
	<select name="import_type" id="import_type" disabled="disabled">
    	<option value="onetime" ><?php esc_attr_e( 'One-time Import','import-facebook-events' ); ?></option>
    	<option value="scheduled" ><?php esc_attr_e( 'Scheduled Import','import-facebook-events' ); ?></option>
    </select>
    <span class="hide_frequency">
    	<?php ife_render_import_frequency(); ?>
    </span>
    <?php do_action( 'ife_render_pro_notice'); ?>
    <?php
}

/**
 * remove query string from URL.
 *
 * @since 1.0.0
 */
function ife_remove_query_string_from_url( $url ) {
	$query = parse_url( $url, PHP_URL_QUERY );

	if ( is_string( $query ) ) {
		$url = str_replace( "?$query", '', $url );
	}
	$url_array = explode( '#', $url );
	return stripslashes( $url_array[0] );
}

/**
 * remove query string from URL.
 *
 * @since 1.0.0
 */
function ife_convert_datetime_to_db_datetime( $datetime ) {
	try {
		$datetime = new DateTime( $datetime );
		return $datetime->format( 'Y-m-d H:i:s' );
	}
	catch ( Exception $e ) {
		return $datetime;
	}
}

/**
 * remove query string from URL.
 *
 * @since 1.0.0
 */
function ife_clean_url( $url ) {
	
	$url = str_replace( '&amp;#038;', '&', $url );
	$url = str_replace( '&#038;', '&', $url );
	return $url;
	
}

/**
 * Display upgrade to pro notice in form.
 *
 * @since 1.0.0
 */
function ife_render_pro_notice(){
	?>
	<span class="ife_small">
        <?php printf( '<span style="color: red">%s</span> <a href="' . IFE_PLUGIN_BUY_NOW_URL. '" target="_blank" >%s</a>', __( 'Available in Pro version.', 'wp-event-aggregator' ), __( 'Upgrade to PRO', 'import-facebook-events' ) ); ?>
    </span>
	<?php
}
add_action( 'ife_render_pro_notice', 'ife_render_pro_notice' );