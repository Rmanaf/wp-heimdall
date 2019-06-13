;(($) => {
    "use strict";
    $(document).ready(() => {

        var $muk =  $('#most-used-keywords')
        
        if(statistics_data['keywords'].length > 0)
        {
            $muk.empty();
        }

        statistics_data['keywords'].forEach((e,i)=>{

            var $meta = $('<li>')
                .text(e['meta'])
                .attr('data-count' , e['count'])
                .addClass('tag')
                .appendTo($muk);

            var $count = $('<i>').text(e['count']).appendTo($meta);
            
        });
    })
    
})(jQuery);
