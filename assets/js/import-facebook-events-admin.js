(function( $ ) {
	'use strict';

	jQuery(document).ready(function(){
		jQuery('#facebook_import_by').on('change', function(){

			if( jQuery(this).val() == 'facebook_event_id' ){
				jQuery('.import_type_wrapper').hide();

				jQuery('.facebook_page_wrapper').hide();
				jQuery('.facebook_page_wrapper .facebook_page_username').removeAttr( 'required' );

				jQuery('.facebook_eventid_wrapper').show();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').attr('required', 'required');

			} else {
				jQuery('.import_type_wrapper').show();

				jQuery('.facebook_eventid_wrapper').hide();
				jQuery('.facebook_eventid_wrapper .facebook_event_ids').removeAttr( 'required' );

				jQuery('.facebook_page_wrapper').show();
				jQuery('.facebook_page_wrapper .facebook_page_username').attr('required', 'required');

			}

		});

		jQuery("#facebook_import_by").trigger('change');
	});	

})( jQuery );


