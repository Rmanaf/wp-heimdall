; (function ($) {

    $(document).ready(() => {

        var ctx = $("#statisticsChart")[0];

        var yarray = [0,0,0,0,0,0,0];
        var zarray = [0,0,0,0,0,0,0];
        var warray = [0,0,0,0,0,0,0];

        statistics_data['visitors'].forEach((e,i)=>{
            var ind = parseInt(e['x']);
            yarray[ind] = parseInt(e['y']);
            zarray[ind] = parseInt(e['z']);
            warray[ind] = parseInt(e['w']);
        });
        
        var dtset = [{
            label: 'Unique Visitors',
            backgroundColor: '#f18226',
            stack: 'Stack 0',
            data: yarray
        }, {
            label: 'Total',
            backgroundColor: '#7bc0f7',
            stack: 'Stack 1',
            data: zarray
        }];

        if (statistics_data['is_multisite'] == '1') {
            dtset.push({
                label: 'This Blog',
                backgroundColor: '#ffdb69',
                stack: 'Stack 2',
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
                title: {
                    display: true,
                    text: 'Weekly Report'
                },
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