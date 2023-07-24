<?php
/**
 * The template for displaying all single Event meta
 *
 * @package Import_Facebook_Events
 */

global $ife_events;

if ( ! isset( $event_id ) || empty( $event_id ) ) {
	$event_id = get_the_ID();
}

$get_gmap_key        = get_option( 'ife_google_maps_api_key', false );
$start_date_str      = get_post_meta( $event_id, 'start_ts', true );
$end_date_str        = get_post_meta( $event_id, 'end_ts', true );
$start_date_formated = date_i18n( 'F j', $start_date_str );
$end_date_formated   = date_i18n( 'F j', $end_date_str );
$website             = get_post_meta( $event_id, 'ife_event_link', true );
$ife_options  = get_option( IFE_OPTIONS );
$time_format = isset( $ife_options['time_format'] ) ? $ife_options['time_format'] : '12hours';
if($time_format == '12hours' ){
    $start_time          = date_i18n( 'h:i a', $start_date_str );
    $end_time            = date_i18n( 'h:i a', $end_date_str );
}elseif($time_format == '24hours' ){
    $start_time          = date_i18n( 'H:i', $start_date_str );
    $end_time            = date_i18n( 'H:i', $end_date_str );
}else{
    $start_time          = date_i18n( get_option( 'time_format' ), $start_date_str );
    $end_time            = date_i18n( get_option( 'time_format' ), $end_date_str );
}

?>
<div class="ife_eventmeta">
<div class="organizermain">
	<div class="details">
	<div class="titlemain" > <?php esc_html_e( 'Details', 'import-facebook-events' ); ?> </div>

	<?php
	if ( date( 'Y-m-d', $start_date_str ) === date( 'Y-m-d', $end_date_str ) ) {
		?>
		<strong><?php esc_html_e( 'Date', 'import-facebook-events' ); ?>:</strong>
		<p><?php echo esc_attr( $start_date_formated ); ?></p>

		<strong><?php esc_html_e( 'Time', 'import-facebook-events' ); ?>:</strong>

		<p>
		<?php
		if ( $start_time !== $end_time ) {
				echo esc_attr( $start_time . ' - ' . $end_time );
		} else {
			echo esc_attr( $start_time );
		}
		?>
		</p>
		<?php
	} else {
		?>
		<strong><?php esc_html_e( 'Start', 'import-facebook-events' ); ?>:</strong>
		<p><?php echo esc_attr( $start_date_formated . ' - ' . $start_time ); ?></p>

		<strong><?php esc_html_e( 'End', 'import-facebook-events' ); ?>:</strong>
		<p><?php echo esc_attr( $end_date_formated . ' - ' . $end_time ); ?></p>
		<?php
	}

	$eve_tags         = array();
	$eve_cats         = array();
	$event_categories = wp_get_post_terms( $event_id, $ife_events->cpt->get_event_categroy_taxonomy() );
	if ( ! empty( $event_categories ) ) {
		foreach ( $event_categories as $event_category ) {
			$eve_cats[] = '<a href="' . esc_url( get_term_link( $event_category->term_id ) ) . '">' . $event_category->name . '</a>';
		}
	}

	$event_tags = wp_get_post_terms( $event_id, $ife_events->cpt->get_event_tag_taxonomy() );
	if ( ! empty( $event_tags ) ) {
		foreach ( $event_tags as $event_tag ) {
			$eve_tags[] = '<a href="' . esc_url( get_term_link( $event_tag->term_id ) ) . '">' . $event_tag->name . '</a>';
		}
	}

	if ( ! empty( $eve_cats ) ) {
		$cats = implode( ', ', $eve_cats );
		?>
		<strong><?php esc_html_e( 'Event Category', 'import-facebook-events' ); ?>:</strong>
		<p><?php echo wp_kses_post( $cats ); ?></p>
		<?php
	}

	if ( ! empty( $eve_tags ) ) {
		$tags = implode( ', ', $eve_tags );
		?>
		<strong><?php esc_html_e( 'Event Tags', 'import-facebook-events' ); ?>:</strong>
		<p><?php echo wp_kses_post( $tags ); ?></p>
		<?php
	}
	?>

	<?php if ( ! empty( $website ) ) { ?>
		<div class="website_wrap">
			<strong><?php esc_html_e( 'Website', 'import-facebook-events' ); ?>:</strong><br/>
			<a href="<?php echo esc_url( $website ); ?>"><?php esc_attr_e( 'Event Website', 'import-facebook-events' ); ?></a>
		</div>
	<?php } ?>

	</div>

	<?php
		// Organizer.
		$org_name  = get_post_meta( $event_id, 'organizer_name', true );
		$org_email = get_post_meta( $event_id, 'organizer_email', true );
		$org_phone = get_post_meta( $event_id, 'organizer_phone', true );
		$org_url   = get_post_meta( $event_id, 'organizer_url', true );

	if ( ! empty( $org_name ) ) {
		?>
		<div class="organizer">
			<div class="titlemain"><?php esc_html_e( 'Organizer', 'import-facebook-events' ); ?></div>
			<p><?php echo esc_attr( $org_name ); ?></p>
			</div>
			<?php if ( ! empty( $org_email ) ) { ?>
				<strong style="display: block;"><?php esc_html_e( 'Email', 'import-facebook-events' ); ?>:</strong>
				<a href="<?php echo esc_attr( 'mailto:' . $org_email ); ?>"><?php echo esc_attr( $org_email ); ?></a>
			<?php } ?>
			<?php if ( ! empty( $org_phone ) ) { ?>
				<strong style="display: block;"><?php esc_html_e( 'Phone', 'import-facebook-events' ); ?>:</strong>
				<a href="<?php echo esc_attr( 'tel:' . $org_phone ); ?>"><?php echo esc_attr( $org_phone ); ?></a>
			<?php } ?>
			<?php if ( ! empty( $website ) ) { ?>
				<strong style="display: block;"><?php esc_html_e( 'Website', 'import-facebook-events' ); ?>:</strong>
				<a href="<?php echo esc_url( $org_url ); ?>"><?php esc_html_e( "Organizer's Website", 'import-facebook-events' ); ?></a>
				<?php
			}
	}
	?>
	<div style="clear: both"></div>
</div>

<?php
$venue_name       = get_post_meta( $event_id, 'venue_name', true );
$venue_address    = get_post_meta( $event_id, 'venue_address', true );
$venue['city']    = get_post_meta( $event_id, 'venue_city', true );
$venue['state']   = get_post_meta( $event_id, 'venue_state', true );
$venue['country'] = get_post_meta( $event_id, 'venue_country', true );
$venue['zipcode'] = get_post_meta( $event_id, 'venue_zipcode', true );
$venue['lat']     = get_post_meta( $event_id, 'venue_lat', true );
$venue['lon']     = get_post_meta( $event_id, 'venue_lon', true );
$venue_url        = esc_url( get_post_meta( $event_id, 'venue_url', true ) );
if ( ife_is_pro() && empty( $get_gmap_key ) ) {
	$map_api_key  = IFEPRO_GM_APIKEY;
}elseif( !empty( $get_gmap_key ) ){
	$map_api_key  = $get_gmap_key;
}else{
	$map_api_key  = '';
}

if ( ! empty( $venue_name  ) || ! empty( $venue_address  ) || ( ! empty( $venue['lat'] ) && ! empty( $venue['lon'] ) ) ) {
	if( $venue_name == 'Online Event' ){
		return;
	}
	?>
	<div class="organizermain library">
		<div class="venue">
			<div class="titlemain"> <?php esc_html_e( 'Venue', 'import-facebook-events' ); ?> </div>
			<p><?php echo esc_attr( $venue_name ); ?></p>
			<?php
			if ( ! empty( $venue_address ) ) {
				echo '<p><i>' . esc_html( $venue_address ) . '</i></p>';
			}
			$venue_array = array();
			foreach ( $venue as $key => $value ) {
				if ( in_array( $key, array( 'city', 'state', 'country', 'zipcode' ), true ) ) {
					if ( ! empty( $value ) ) {
						$venue_array[] = $value;
					}
				}
			}
			$venue_imploded = implode( ', ', $venue_array );
			echo '<p><i>' . esc_html( $venue_imploded ) . '</i></p>';
			?>
		</div>
		<?php
		$q = '';
		$lat_lng = '';
		if ( ! empty( $venue['lat'] ) && ! empty( $venue['lon'] ) ) {
			$lat_lng = esc_attr( $venue['lat'] ) . ',' . esc_attr( $venue['lon'] );
		}
		if ( ! empty( $venue_name ) ) {
			$q = esc_attr( $venue_name );
		}
		if( ! empty( $venue_name ) && ! empty( $venue_address ) ){
			$q = esc_attr( $venue_name ).', '.esc_attr( $venue_address );
		}
		if(empty($q)){
			$q = $lat_lng;
		}
		if ( ! empty( $q ) ) {
			$params = array(
				'q' => $q
			);
			if ( ! empty( $lat_lng ) ) {
				$params['center'] = $lat_lng;
			}
			$query = http_build_query($params);
			if( empty( $map_api_key ) ){
				$full_address = str_replace( ' ', '%20', $venue_address ) .','. $venue['city'] .','. $venue['state'] .','. $venue['country'].'+(' . str_replace( ' ', '%20', $venue_name ) . ')';	
				?>
				<div class="map">
					<iframe src="https://maps.google.com/maps?q=<?php echo $full_address; ?>&hl=es;z=14&output=embed" width="100%" height="350" frameborder="0" style="border:0; margin:0;" allowfullscreen></iframe>
				</div>
				<?php
			}else{ 
				?>
				<div class="map">
					<iframe src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr( $map_api_key ); ?>&<?php echo esc_attr( $query ); ?>" width="100%" height="350" frameborder="0" style="border:0; margin:0;" allowfullscreen></iframe>
				</div>
				<?php
			}
		}
		?>
		<div style="clear: both;"></div>
	</div>
	<?php
}
?>
</div>
<div style="clear: both;"></div>
