; (function ($) {

    $(document).ready(() => {

        $active_hooks = $('#wp_dcp_statistics_active_hooks');

        $active_hooks.tagEditor({
            placeholder: $active_hooks.data('placeholder') || ''
        });

        var ctx = $("#statisticsChart")[0];

        var dtset = [{
            label: 'Unique Visitors',
            backgroundColor: '#f18226',
            stack: 'Stack 0',
            data: statistics_data['visitors'].map((el) => {
                return { x: el['x'], y: el['y'] };
            })
        }, {
            label: 'Total',
            backgroundColor: '#7bc0f7',
            stack: 'Stack 1',
            data: statistics_data['visitors'].map((el) => {
                return { x: el['x'], y: el['z'] };
            })
        }];

        if (statistics_data['is_multisite']) {
            dtset.push({
                label: 'This Blog',
                backgroundColor: '#ffdb69',
                stack: 'Stack 2',
                data: statistics_data['visitors'].map((el) => {
                    return { x: el['x'], y: el['w'] };
                })
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