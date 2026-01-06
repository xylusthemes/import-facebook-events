<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $ife_events;
?>
<div class="ife-card" style="margin-top:20px;" >			
	<div class="ife-content ife_source_import"  aria-expanded="true" style=" ">
		<div class="ife-inner-main-section"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Import by', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<select name="ical_import_by" id="ical_import_by">
					<option value="ics_file"><?php esc_attr_e( '.ics File','import-facebook-events' ); ?></option>
					<option value="ical_url" <?php if( ife_is_pro() ){ echo 'selected="selected"'; } ?> ><?php esc_attr_e( 'iCal URL','import-facebook-events' ); ?></option>
					<option value="outlook_calendar" <?php if( ife_is_pro() ){ echo 'selected="selected"'; } ?> ><?php esc_attr_e( 'Outlook Calendar','import-facebook-events' ); ?></option>
				</select>
			</div>
		</div>

		<div class="ife-inner-main-section ical_url_wrapper"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'iCal URL', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<input class="ife_text ical_url" name="ical_url" type="text" <?php if( !ife_is_pro() ){ echo 'disabled="disabled"'; } ?>/>
				<span class="ife_small">
					<?php 
						esc_attr_e( 'You can get the iCal URL here ', 'import-facebook-events'  );
						echo '<strong><a href="https://facebook.com/events/calendar/" target="_blank">https://facebook.com/events/calendar/</a></strong>';
					?>
				</span>
				<?php do_action( 'ife_render_pro_notice' ); ?>
			</div>
		</div>

		<div class="ife-inner-main-section ics_file_wrapper"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( '.ics File', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<input class="ife_text ics_file_class" name="ics_file" type="file" accept=".ics" />
			</div>
		</div>

		<div class="ife-inner-main-section outlook_calendar_wrapper" >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Outlook Calendar','import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<?php 
					if ( function_exists('ife_is_pro') && ife_is_pro() ) {
						do_action(  'ife_render_outlookcalendar_dropdown' );
					} else {
						?>
						<select name="" id="" disabled="disabled">
							<option value=""><?php esc_html_e('Select Calendar', 'import-facebook-events'); ?></option>
						</select>
						<?php
						do_action( 'ife_render_pro_notice' );
					}
				?>
			</div>
		</div>

		<div class="ife-inner-main-section import_date_range"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Events date range', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<input type="text" name="start_date" class="ife_datepicker start_date" style="width: 22%;" placeholder="<?php esc_html_e('Select start date', 'import-facebook-events' ); ?>"> - 
				<input type="text" name="end_date" class="ife_datepicker end_date" style="width: 22%;" placeholder="<?php esc_html_e('Select end date', 'import-facebook-events' ); ?>">
				<span class="ife_small">
					<?php esc_attr_e( 'Select date range from which you want to import events. Default startdate is Today', 'import-facebook-events' ); ?>
				</span>
			</div>
		</div>

		<div class="ife-inner-main-section import_type_wrapper"  >
			<div class="ife-inner-section-1" >
				<span class="ife-title-text" ><?php esc_attr_e( 'Import type', 'import-facebook-events' ); ?></span>
			</div>
			<div class="ife-inner-section-2">
				<?php $ife_events->common->render_import_type(); ?>
			</div>
		</div>

		<?php
			$ife_events->common->render_import_into_and_taxonomy();
			$ife_events->common->render_eventstatus_input();
		?>
    </div>
</div>