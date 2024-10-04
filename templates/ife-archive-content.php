<?php
/**
 * Template part for displaying posts
 *
 * @package Import_Facebook_Events
 */

$event_date = get_post_meta( get_the_ID(), 'event_start_date', true );
if ( ! empty( $event_date ) ) {
	$event_date = strtotime( $event_date );
}
$event_address = get_post_meta( get_the_ID(), 'venue_name', true );
$venue_address = get_post_meta( get_the_ID(), 'venue_address', true );
if ( ! empty( $event_address ) && ! empty( $venue_address ) ) {
	$event_address .= ' - ' . $venue_address;
} elseif ( ! empty( $venue_address ) ) {
	$event_address = $venue_address;
}


$image_url = array();
if ( '' !== get_the_post_thumbnail() ) {
	$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
} else {
	if ( '' !== $ife_ed_image ) {
		$image_url = wp_get_attachment_image_src( $ife_ed_image, 'full' );
	}else{
		$image_date  = date_i18n( 'F+d', $event_date );
		$image_url[] = 'https://dummyimage.com/420x210/ccc/969696.png?text=' . $image_date;
	}
}
$ticket_uri = esc_url( get_permalink() );
$target     = '';
if ( 'yes' === $direct_link ) {
	$facebook_event_id = get_post_meta( get_the_ID(), 'ife_facebook_event_id', true );
	$ticket_uri        = 'https://www.facebook.com/events/' . $facebook_event_id;
	$target            = 'target="_blank"';
}
?>
<a href="<?php echo $ticket_uri; ?>" <?php echo $target; ?>>
	<div <?php post_class( array( $css_class, 'archive-event' ) ); ?>>
		<div class="ife_event" >
			<div class="img_placeholder" style=" background: url('<?php echo esc_url( $image_url[0] ); ?>') no-repeat left top;"></div>
			<div class="event_details">
				<div class="event_date">
					<span class="month"><?php echo esc_attr( date_i18n( 'M', $event_date ) ); ?></span>
					<span class="date"> <?php echo esc_attr( date_i18n( 'd', $event_date ) ); ?> </span>
				</div>
				<div class="event_desc">
					<a href="<?php echo $ticket_uri; ?>" <?php echo $target; ?> rel="bookmark">
					<?php the_title( '<div class="event_title">', '</div>' ); ?>
					</a>
					<?php if ( ! empty( $event_address ) ) { ?>
						<div class="event_address"><i class="fa fa-map-marker"></i>  <?php echo esc_html( $event_address ); ?></div>
					<?php } ?>
				</div>
				<div style="clear: both"></div>
			</div>
		</div>
	</div>
</a>
