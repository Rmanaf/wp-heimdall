; (($) => {
    "use strict";

    $(document).ready(() => {


        var $muk =  $('#most-used-keywords')
     
        statistics_data['keywords'].forEach((e,i)=>{

            var $meta = $('<li>')
                .text(e['meta'])
                .attr('data-count' , e['count'])
                .addClass('tag')
                .appendTo($muk);

            var $count = $('<i>').text(e['count']).appendTo($meta);
            
        });



        var ctx = $("#statisticsChart")[0];


        var yarray = [0,0,0,0,0,0,0];
        var zarray = [0,0,0,0,0,0,0];
        var warray = [0,0,0,0,0,0,0];
        var parray = [0,0,0,0,0,0,0];

        statistics_data['visitors'].forEach((e,i)=>{
            var ind = parseInt(e['x']);
            yarray[ind] = parseInt(e['y']);
            zarray[ind] = parseInt(e['z']);
            warray[ind] = parseInt(e['w']);
            parray[ind] = parseInt(e['p']);
        });
        
        var dtset = [{
            label: 'Total',
            backgroundColor: '#00325b',
            stack: 'Stack 1',
            data: zarray
        },{
            label: 'Unique Visitors',
            backgroundColor: '#005171',
            stack: 'Stack 2',
            data: yarray
        },{
            label: 'Home Page',
            backgroundColor: '#ffe06a',
            stack: 'Stack 2',
            data: yarray
        }];

        if (statistics_data['is_multisite'] == '1') {
            dtset.push({
                label: 'This Blog',
                backgroundColor: '#fd5a35',
                stack: 'Stack 3',
                data: warray
            });
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: dtset
            },
            options: {
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    cornerRadius: 4,
                    footerFontColor: '#ccc',
                    footerFontStyle: 'normal'
                },
                responsive: true,
                scales: {
                    xAxes: [{
                        stacked: true,
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            }
        });

    });


})(jQuery);