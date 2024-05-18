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
		jQuery(document).on('change', '#facebook_import_by', function(){
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

	jQuery(document).ready(function(){
		jQuery('#ical_import_by').on('change', function(){

			if( jQuery(this).val() == 'ical_url' ){
				jQuery('.import_type_wrapper').show();
				jQuery('.ical_url_wrapper').show();
				jQuery('.ical_url_wrapper .ical_url').attr('required', 'required');
				jQuery('.ics_file_wrapper').hide();
				jQuery('.ics_file_wrapper .ics_file_class').removeAttr( 'required' );

			} else if( jQuery(this).val() == 'ics_file' ){
				jQuery('.import_type_wrapper').hide();
				jQuery('.ics_file_wrapper').show();
				jQuery('.ics_file_wrapper .ics_file_class').attr('required', 'required');
				jQuery('.ical_url_wrapper').hide();
				jQuery('.ical_url_wrapper .ical_url').removeAttr( 'required' );

			}
		});

		jQuery("#ical_import_by").trigger('change');
	});

	// Render Dynamic Terms.
	jQuery(document).ready(function() {
		jQuery('.fb_event_plugin').on( 'change', function() {

			var event_plugin = jQuery(this).val();
			var taxo_cats = jQuery('#ife_taxo_cats').val();
			var taxo_tags = jQuery('#ife_taxo_tags').val();
			var data = {
				'action': 'ife_render_terms_by_plugin',
				'security': ife_ajax.ajax_nonce,
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

	//Shortcode Copy Text
	jQuery(document).ready(function($){
		$(document).on("click", ".ife-btn-copy-shortcode", function() { 
			var trigger = $(this);
			$(".ife-btn-copy-shortcode").removeClass("text-success");
			var $tempElement = $("<input>");
			$("body").append($tempElement);
			var copyType = $(this).data("value");
			$tempElement.val(copyType).select();
			document.execCommand("Copy");
			$tempElement.remove();
			$(trigger).addClass("text-success");
			var $this = $(this),
			oldText = $this.text();
			$this.attr("disabled", "disabled");
			$this.text("Copied!");
			setTimeout(function(){
				$this.text( oldText );
				$this.removeAttr("disabled");
			}, 800);
	  
		});
		
	});

})( jQuery );

jQuery(document).ready(function($){

	const ife_tab_link = document.querySelectorAll('.ife_tab_link');
	const ife_tabcontents = document.querySelectorAll('.ife_tab_content');

	ife_tab_link.forEach(function(link) {
		link.addEventListener('click', function() {
		const ife_tabId = this.dataset.tab;

		ife_tab_link.forEach(function(link) {
			link.classList.remove('active');
		});

		ife_tabcontents.forEach(function(content) {
			content.classList.remove('active');
		});

		this.classList.add('active');
		document.getElementById(ife_tabId).classList.add('active');
		});
	});

	const ife_gm_apikey_input = document.querySelector('.ife_google_maps_api_key');
	if ( ife_gm_apikey_input ) {
		ife_gm_apikey_input.addEventListener('input', function() {
			const ife_check_key = document.querySelector('.ife_check_key');
			if (ife_gm_apikey_input.value.trim() !== '') {
				ife_check_key.style.display = 'contents';
			} else {
				ife_check_key.style.display = 'none';
			}
		});
	}
  
	const ife_checkkeylink = document.querySelector('.ife_check_key a');
	if ( ife_checkkeylink ) { 
		ife_checkkeylink.addEventListener('click', function(event) { 
			event.preventDefault(); 
			const ife_gm_apikey = ife_gm_apikey_input.value.trim(); 
			if ( ife_gm_apikey !== '' ) { 
				ife_check_gmap_apikey(ife_gm_apikey); 
			} 
		}); 
	}

	function ife_check_gmap_apikey(ife_gm_apikey) {
		const ife_xhr = new XMLHttpRequest();
		ife_xhr.open('GET', 'https://www.google.com/maps/embed/v1/place?q=New+York&key=' + encodeURIComponent(ife_gm_apikey), true);
		const ife_loader = document.getElementById('ife_loader');
		ife_loader.style.display = 'inline-block';
		ife_xhr.onreadystatechange = function() {
			if ( ife_xhr.readyState === XMLHttpRequest.DONE ) {
				ife_loader.style.display = 'none';
				if (ife_xhr.status === 200) {
					const response = ife_xhr.responseText;
					var ife_gm_success_notice = jQuery("#ife_gmap_success_message");
						ife_gm_success_notice.html('<span class="ife_gmap_success_message">Valid Google Maps License Key</span>');
						setTimeout(function(){ ife_gm_success_notice.empty(); }, 2000);
				} else {
					var ife_gm_error_notice = jQuery("#ife_gmap_error_message");
					ife_gm_error_notice.html( '<span class="ife_gmap_error_message" >Inalid Google Maps License Key</span>' );
						setTimeout(function(){ ife_gm_error_notice.empty(); }, 2000);
				}
			}
		};

		ife_xhr.send();
	}

	const ife_ggl_apikey_input = document.querySelector('.ife_google_geolocation_api_key');
	if ( ife_ggl_apikey_input ) {
		ife_ggl_apikey_input.addEventListener('input', function() {
			const ife_ggl_check_key = document.querySelector('.ife_ggl_check_key');
			if (ife_ggl_apikey_input.value.trim() !== '') {
				ife_ggl_check_key.style.display = 'contents';
			} else {
				ife_ggl_check_key.style.display = 'none';
			}
		});
	}
  
	const ife_ggl_checkkeylink = document.querySelector('.ife_ggl_check_key a');
	if ( ife_ggl_checkkeylink ) { 
		ife_ggl_checkkeylink.addEventListener('click', function(event) { 
			event.preventDefault(); 
			const ife_ggl_apikey = ife_ggl_apikey_input.value.trim(); 
			if ( ife_ggl_apikey !== '' ) { 
				ife_check_geolocation_apikey(ife_ggl_apikey); 
			} 
		}); 
	}

	function ife_check_geolocation_apikey(ife_ggl_apikey) {
		const ife_ggl_xhr = new XMLHttpRequest();
		ife_ggl_xhr.open('GET', 'https://maps.googleapis.com/maps/api/geocode/json?address=kalupur+swamianarayan+mandir&key=' + encodeURIComponent(ife_ggl_apikey), true);
		const ife_ggl_loader = document.getElementById('ife_ggl_loader');
		ife_ggl_loader.style.display = 'inline-block';
		ife_ggl_xhr.onreadystatechange = function() {
			if ( ife_ggl_xhr.readyState === XMLHttpRequest.DONE ) {
				ife_ggl_loader.style.display = 'none';
				var responseObject = JSON.parse( ife_ggl_xhr.response );
				console.log( responseObject );
				if (ife_ggl_xhr.status === 200 && responseObject.status === "OK" ) {
					var ife_gm_success_notice = jQuery("#ife_ggl_success_message");
						ife_gm_success_notice.html('<span class="ife_gmap_success_message">Valid Google GeoLocation License Key</span>');
						setTimeout(function(){ ife_gm_success_notice.empty(); }, 2000);
				} else {
					var ife_gm_error_notice = jQuery("#ife_ggl_error_message");
					ife_gm_error_notice.html( '<span class="ife_gmap_error_message" >Inalid Google GeoLocation License Key</span>' );
						setTimeout(function(){ ife_gm_error_notice.empty(); }, 2000);
				}
			}
		};
		
		ife_ggl_xhr.send();
	}

});