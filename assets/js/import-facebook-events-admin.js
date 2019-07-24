(function( $ ) {
	'use strict';

	jQuery(document).ready(function(){
		jQuery(document).on("click", ".ife_datepicker", function(){
		    jQuery(this).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				showOn:'focus'
			}).focus();
		});

		jQuery(document).on("click", ".vc_ui-panel .ife_datepicker input[type='text']", function(){
		    jQuery(this).datepicker({
				changeMonth: true,
				changeYear: true,
				dateFormat: 'yy-mm-dd',
				showOn:'focus'
			}).focus();
		});
	});
	
	jQuery(document).ready(function(){
		jQuery('#import_type').on('change', function(){
			if( jQuery(this).val() != 'onetime' ){
				jQuery('.hide_frequency .import_frequency').show();
			}else{
				jQuery('.hide_frequency .import_frequency').hide();
			}
		});

		jQuery("#import_type").trigger('change');
	});


	jQuery(document).ready(function(){
		jQuery('#facebook_import_by').live('change', function(){
			var current_value = jQuery(this).val();
			
			if( current_value == 'facebook_event_id' ){
				jQuery('.import_type_wrapper').hide();

				jQuery('.facebook_page_wrapper').hide();
				jQuery('.facebook_page_wrapper .facebook_page_username').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').hide();
				jQuery('.facebook_group_wrapper .facebook_group').removeAttr( 'required' );

				jQuery('.facebook_account_wrapper').hide();
				jQuery('.facebook_account_wrapper .my_page').removeAttr( 'required' );

				jQuery('.facebook_eventid_wrapper').show();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').attr('required', 'required');

			} else if( current_value == 'facebook_group' ){
				jQuery('.import_type_wrapper').show();

				jQuery('.facebook_eventid_wrapper').hide();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').removeAttr( 'required' );

				jQuery('.facebook_page_wrapper').hide();
				jQuery('.facebook_page_wrapper input.facebook_page_username').removeAttr( 'required' );

				jQuery('.facebook_account_wrapper').hide();
				jQuery('.facebook_account_wrapper .my_page').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').show();
				jQuery('.facebook_group_wrapper .facebook_group').attr('required', 'required');

			} else if( current_value == 'my_events' ){
				jQuery('.import_type_wrapper').show();

				jQuery('.facebook_eventid_wrapper').hide();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').removeAttr( 'required' );

				jQuery('.facebook_page_wrapper').hide();
				jQuery('.facebook_page_wrapper input.facebook_page_username').removeAttr( 'required' );

				jQuery('.facebook_account_wrapper').hide();
				jQuery('.facebook_account_wrapper .my_page').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').hide();
				jQuery('.facebook_group_wrapper .facebook_group').removeAttr( 'required' );

			} else if( current_value == 'facebook_organization' ){

				jQuery('.import_type_wrapper').show();

				jQuery('.facebook_eventid_wrapper').hide();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').hide();
				jQuery('.facebook_group_wrapper .facebook_group').removeAttr( 'required' );

				jQuery('.facebook_account_wrapper').hide();
				jQuery('.facebook_account_wrapper .my_page').removeAttr( 'required' );

				jQuery('.facebook_page_wrapper').show();
				jQuery('.facebook_page_wrapper .facebook_page_username').attr('required', 'required');

			} else if( current_value == 'my_pages' ){

				jQuery('.import_type_wrapper').show();

				jQuery('.facebook_eventid_wrapper').hide();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').removeAttr( 'required' );

				jQuery('.facebook_page_wrapper').hide();
				jQuery('.facebook_page_wrapper input.facebook_page_username').removeAttr( 'required' );

				jQuery('.facebook_group_wrapper').hide();
				jQuery('.facebook_group_wrapper .facebook_group').removeAttr( 'required' );

				jQuery('.facebook_account_wrapper').show();
				jQuery('.facebook_account_wrapper .my_page').attr('required', 'required');
			}


		});

		jQuery("#facebook_import_by").trigger('change');
	});

	// Render Dynamic Terms.
	jQuery(document).ready(function() {
	    jQuery('.fb_event_plugin').on( 'change', function() {

	    	var event_plugin = jQuery(this).val();
	    	var taxo_cats = jQuery('#ife_taxo_cats').val();
	    	var taxo_tags = jQuery('#ife_taxo_tags').val();
	    	var data = {
	            'action': 'ife_render_terms_by_plugin',
	            'event_plugin': event_plugin,
	            'taxo_cats': taxo_cats,
	            'taxo_tags': taxo_tags
	        };

	        var terms_space = jQuery('.event_taxo_terms_wraper');
	        terms_space.html('<span class="spinner is-active" style="float: none;"></span>');
	        // send ajax request.
	        jQuery.post(ajaxurl, data, function(response) {
	            if( response != '' ){
	            	terms_space.html( response );
	            }else{
	            	terms_space.html( '' );
	            }	            
	        });    
	    });
	    jQuery(".fb_event_plugin").trigger('change');                  
	});

	// Color Picker
	jQuery(document).ready(function($){
		$('.ife_color_field').each(function(){
			$(this).wpColorPicker();
		});
	});

})( jQuery );
