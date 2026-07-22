<?php
/**
 * IFE Live Feed - Card Grid Template
 *
 * Variables available:
 *   $event  array  Normalized event from IFEPRO_Feed_API::normalize_event()
 *   $meta   array  Feed settings from IFEPRO_Feed_API::get_feed_meta()
 *
 * @package ImportFacebookEvents\Pro\Feed
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$ife_feed_id      = $meta['feed_id'] ?? 0;
$ife_ticket_style = $meta['ticket_style'];
$ife_btn_id       = 'ifeprofeed-ticket-btn-' . esc_attr( $event['id'] );
$ife_is_free      = $event['is_free'];
$ife_btn_label    = esc_html( $meta['register_label'] );

// Price display
$ife_price_display = '';
if ( $meta['show_price'] ) {
	if ( ! $ife_is_free ) {
		$ife_price_display = '<span class="ifeprofeed-price-wrapper"><span class="ifeprofeed-price">' . esc_html( IFEPRO_Feed_Shortcode::format_price( $event ) ) . '</span></span>';
	}
}

// Days left display
$ife_days_left_info = IFEPRO_Feed_Shortcode::get_days_left_info( $event['start_local'], $event['timezone'] ?? '' );
$ife_days_left_html = '';
if ( ! empty( $ife_days_left_info ) ) {
	$ife_days_left_html = '<span class="ifeprofeed-badge ifeprofeed-badge--days-left ifeprofeed-badge--' . esc_attr( $ife_days_left_info['class'] ) . '">' . esc_html( $ife_days_left_info['text'] ) . '</span>';
}

// Venue display
$ife_venue_text = '';
if ( $meta['show_venue'] ) {
	if ( $event['is_online'] ) {
		$ife_venue_text = __( 'Online Event', 'import-facebook-events' );
	} elseif ( $event['venue_name'] ) {
		$ife_venue_text = $event['venue_name'];
		if ( $event['venue_city'] ) {
			$ife_venue_text .= ' · ' . $event['venue_city'];
		}
	}
}
?>

<div class="ifeprofeed-event-card ifeprofeed-card-grid-item">

	<?php if ( $meta['show_image'] ) : ?>
	<a href="<?php echo esc_url( $event['url'] ); ?>" target="_blank" rel="noopener" class="ifeprofeed-card-image-link" tabindex="-1" aria-hidden="true">
		<div class="ifeprofeed-card-image">
			<?php if ( ! empty( $event['image_url'] ) ) : ?>
			<img
				src="<?php echo esc_url( $event['image_url'] ); ?>"
				alt="<?php echo esc_attr( $event['name'] ); ?>"
				loading="lazy"
				style="opacity: 0; transition: opacity 0.3s ease;"
				onload="this.style.opacity=1; if(this.nextElementSibling &amp;&amp; this.nextElementSibling.classList.contains('ifeprofeed-skeleton')) this.nextElementSibling.style.display='none';"
				onerror="this.style.display='none';"
			/>
			<?php endif; ?>
			<div class="ifeprofeed-skeleton"></div>
		</div>
	</a>
	<?php endif; ?>

	<div class="ifeprofeed-card-body">

		<?php if ( $meta['show_category'] && $event['category'] ) : ?>
		<div class="ifeprofeed-card-category"><?php echo esc_html( $event['category'] ); ?></div>
		<?php endif; ?>

		<h3 class="ifeprofeed-card-title">
			<a href="<?php echo esc_url( $event['url'] ); ?>" target="_blank" rel="noopener">
				<?php echo esc_html( $event['name'] ); ?>
			</a>
		</h3>

		<?php if ( $meta['show_date'] && $event['start_local'] ) : ?>
		<div class="ifeprofeed-card-meta ifeprofeed-card-date">
			<svg class="ifeprofeed-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM5 8V6h14v2H5zm2 4h10v2H7zm0 4h7v2H7z"/></svg>
			<?php echo esc_html( IFEPRO_Feed_Shortcode::format_date( $event['start_local'], $event['timezone'] ) ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $ife_venue_text ) : ?>
		<div class="ifeprofeed-card-meta ifeprofeed-card-venue">
			<svg class="ifeprofeed-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
			<?php echo esc_html( $ife_venue_text ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $meta['show_organizer'] && $event['organizer_name'] ) : ?>
		<div class="ifeprofeed-card-meta ifeprofeed-card-organizer">
			<svg class="ifeprofeed-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>
			<?php echo esc_html( $event['organizer_name'] ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $meta['show_tags'] && ! empty( $event['tags'] ) ) : ?>
		<div class="ifeprofeed-card-tags">
			<?php foreach ( array_slice( $event['tags'], 0, 3 ) as $tag ) : ?>
			<span class="ifeprofeed-tag"><?php echo esc_html( $tag ); ?></span>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div><!-- .ifeprofeed-card-body -->

	<?php if ( $meta['show_price'] || $meta['show_ticket_btn'] || ! empty( $ife_days_left_html ) ) : ?>
	<div class="ifeprofeed-card-footer">

		<div class="ifeprofeed-price-days-container">
			<?php echo wp_kses_post( $ife_price_display ); ?>
			<?php echo wp_kses_post( $ife_days_left_html ); ?>
		</div>

		<?php if ( $meta['show_ticket_btn'] ) : ?>
			<?php if ( 'modal' === $ife_ticket_style ) : ?>
			<button
				id="<?php echo esc_attr( $ife_btn_id ); ?>"
				class="ifeprofeed-btn ifeprofeed-btn--ticket"
				data-event-id="<?php echo esc_attr( $event['id'] ); ?>"
				data-ticket-style="modal"
				aria-label="<?php echo esc_attr( $ife_btn_label . ' — ' . $event['name'] ); ?>">
				<?php echo esc_html( $ife_btn_label ); ?>
			</button>
			<?php else : // link ?>
			<a
				href="<?php echo esc_url( $event['url'] ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				class="ifeprofeed-btn ifeprofeed-btn--ticket"
				aria-label="<?php echo esc_attr( $ife_btn_label . ' — ' . $event['name'] ); ?>">
				<?php echo esc_html( $ife_btn_label ); ?>
			</a>
			<?php endif; ?>
		<?php endif; ?>

	</div><!-- .ifeprofeed-card-footer -->
	<?php endif; ?>

</div><!-- .ifeprofeed-event-card -->
