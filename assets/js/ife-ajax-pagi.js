jQuery(document).ready(function($){
    $(document).on('click', '.prev-next-posts a', function(e){
        var $link = $(this);
        var $container = $link.closest('.ife_archive'); // old container
        var atts = $container.data('shortcode');       // shortcode attributes
        var nextPage = parseInt($link.data('page')) || 1;

        if (!atts || atts.ajaxpagi !== 'yes') return true;

        e.preventDefault();
        $container.addClass('ife-loading');

        $.ajax({
            url: ife_ajax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'ife_load_paged_events',
                atts: JSON.stringify(atts),
                page: nextPage
            },
            success: function(response){
                if(response.success){
                    // Replace old container with new HTML
                    $container.replaceWith(response.data);

                    // Update $container reference for next click
                    var $newContainer = $('.ife_archive').filter(function(){
                        return $(this).data('shortcode').ajaxpagi === 'yes';
                    }).first();

                    // Update pagination links dynamically
                    $newContainer.find('.ife-next-page').attr('data-page', nextPage + 1);
                    $newContainer.find('.ife-prev-page').attr('data-page', nextPage - 1);
                }
            },
            complete: function(){
                $container.removeClass('ife-loading');
            }
        });

    });
});
