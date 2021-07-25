<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */
 
if (!class_exists('WP_HeimdallAddon_Today')) {
    
    
    class WP_HeimdallAddon_Today
    {

         // addon slug
        private static $slug = "today";


        /**
         * @since 1.3.1
         */
        static function init(){
            
            $class = get_called_class();

            add_action("admin_enqueue_scripts", "$class::admin_enqueue_scripts");

            // add_action("heimdall--dashboard-statistic-widget" , "$class::dashboard_statistic_widget" , 20);

            add_action("heimdall--dashboard-statistic-widget-tabs", "$class::dashboard_statistic_widget_tabs", 10);

            add_action("heimdall--dashboard-statistic-widget-tab-content", "$class::dashboard_statistic_widget_tab_content", 10);

            //add_filter("heimdall--localize-script", "$class::get_today_report_data" , 10, 1);

            add_action("wp_ajax_heimdall_today_report" , "$class::get_today_report_data");
            
        }

        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget(){
        }


        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget_tabs(){
            WP_Heimdall_Dashboard::create_admin_widget_tab(esc_html__( "Today", "heimdall") , "today");
        }


        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget_tab_content(){

            ob_start();

            ?>
            <div class="chart-container-today" style="position: relative; width:100%; height:300px;">
                <canvas id="statisticsTodayChart"></canvas>
            </div>
            <?php

            WP_Heimdall_Dashboard::create_admin_widget_tab_content("today" , ob_get_clean() );

        }


        /**
         * @since 1.3.1
         */
        static function admin_enqueue_scripts(){

            $screen = get_current_screen();

            if(current_user_can( 'administrator' ) && $screen->id  == 'dashboard' )
            {

                wp_enqueue_script("today-report", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/js/today-admin.js'), ['jquery'], WP_Heimdall_Plugin::$version, true);

            }

        }


        /**
         * @since 1.3.1
         */
        static function get_today_report_data(){

            global $wpdb;

            check_ajax_referer("heimdall-nonce");

            $data = [];

            // get GMT
            $cdate = current_time( 'mysql' , 1 );

            // start from 6 days ago
            $start = new DateTime($cdate);
            $start->sub(new DateInterval('PT24H')); 

            // today
            $end = new DateTime($cdate);

            $data['today'] = $wpdb->get_results(self::get_chart_query($start , $end), ARRAY_A );

            $data['today_now_hour'] = (new DateTime($cdate))->format('H');

            wp_send_json_success( $data );

        }

        /**
         * @since 1.0.0
         */
        static function get_chart_query($start , $end)
        {

            // convert dates to mysql format
            $start = $start->format('Y-m-d H:i:s');
            $end   = $end->format('Y-m-d H:i:s');

            $blog_id = get_current_blog_id();
            
            $extra_field = is_multisite() ? ", SUM(case when blog='$blog_id' then 1 else 0 end) w" : "";
            
            $table_name = WP_Heimdall_Database::$table_name;

            return "SELECT HOUR(time) x,
                COUNT(DISTINCT ip) y,
                COUNT(*) z,
                SUM(case when type='1' then 1 else 0 end) p
                $extra_field
                FROM $table_name
                WHERE (time BETWEEN '$start' AND '$end')
                AND type != '4'
                GROUP BY x";

        }

    }

}