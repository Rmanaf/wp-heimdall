; (($) => {
    "use strict";

    $(document).ready(() => {
        $.post(heimdall['ajaxurl'], {
            'action': 'heimdall_today_report',
            '_wpnonce': heimdall['ajaxnonce']
        }, (res) => {
            var ctx = $("#statisticsTodayChart")[0];

            $("#statisticsTodayChart").parents('.busy').removeClass('busy');

            var now_hour = parseInt(res.data['today_now_hour']);

            var yarray = new Array(25).join('0').split('');
            var zarray = new Array(25).join('0').split('');
            var warray = new Array(25).join('0').split('');
            var parray = new Array(25).join('0').split('');

            res.data['today'].forEach((e, i) => {

                var ind = parseInt(e['x']);

                if (now_hour < ind) {
                    return;
                }

                yarray[ind] = parseInt(e['y']);
                zarray[ind] = parseInt(e['z']);
                warray[ind] = parseInt(e['w']);
                parray[ind] = parseInt(e['p']);
            });

            var dtset = [{
                label: 'Total',
                borderColor: '#00325b',
                backgroundColor: 'transparent',
                borderWidth: 2,
                data: zarray,
                pointRadius: 0,
                lineTension: 0
            }, {
                label: 'Unique Visitors',
                borderColor: '#005171',
                backgroundColor: 'transparent',
                borderWidth: 2,
                data: yarray,
                pointRadius: 0,
                lineTension: 0
            }, {
                label: 'Home Page',
                borderColor: '#ffe06a',
                backgroundColor: 'transparent',
                borderWidth: 2,
                data: parray,
                pointRadius: 0,
                lineTension: 0
            }];

            if (heimdall['is_multisite'] == '1') {
                dtset.push({
                    label: 'This Blog',
                    borderColor: '#fd5a35',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    data: warray,
                    pointRadius: 0,
                    lineTension: 0
                });
            }

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Array.apply(null, { length: 24 }).map(Number.call, Number),
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
                    maintainAspectRatio: false,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                callback: function (value) { if (value % 1 === 0) { return value; } }
                            }
                        }]
                    }
                }
            });
        });
    });


})(jQuery);