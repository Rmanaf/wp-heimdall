<?php
/**
 * Apache License, Version 2.0
 * 
 * Copyright (C) 2018 Arman Afzal <rman.afzal@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

 
if (!class_exists('WP_HeimdallAddon_WeeklyReport')) {
    
    class WP_HeimdallAddon_WeeklyReport
    {

        private static $version = "1.0.0";
        private static $slug = "weekly-report";

        function __construct()
        {

            add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);

            add_action("dcp-heimdall--dashboad-statistic-widget" , [$this , "dashboard_statistic_widget"] , 10);

            add_filter("dcp-heimdall--localized-data", [$this , "get_weekly_report_data"], 10, 1);

        }

        public function admin_enqueue_scripts()
        {

            wp_enqueue_script("weekly-report", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/js/statistics-admin.js'), ['jquery'], self::$version, true);

        }

        public function dashboard_statistic_widget()
        {
            ?>
            <h3><?php _e("Weekly report" , WP_Heimdall_Plugin::$text_domain); ?></h2>
            <div class="chart-container" style="position: relative; width:100%; height:300px;">
                <canvas id="statisticsChart"></canvas>
            </div>
            <?php
        }


        public function get_weekly_report_data()
        {

            global $wpdb;

            // get GMT
            $cdate = current_time( 'mysql' , 1 );

            // start from 6 days ago
            $start = new DateTime($cdate);
            $start->sub(new DateInterval('P6D')); 

            // today
            $end = new DateTime($cdate);

            $data['visitors'] = $wpdb->get_results($this->get_chart_query($start , $end), ARRAY_A );

            return $data;

        }

        public function get_chart_query($start , $end)
        {

            // convert dates to mysql format
            $start = $start->format('Y-m-d H:i:s');
            $end   = $end->format('Y-m-d H:i:s');

            $blog_id = get_current_blog_id();
            $extra_field = is_multisite() ? ", SUM(case when blog='$blog_id' then 1 else 0 end) w" : "";
            
            $table_name = WP_Heimdall_Plugin::table_name();

            return "SELECT WEEKDAY(time) x,
                COUNT(DISTINCT ip) y,
                COUNT(*) z,
                SUM(case when type='1' then 1 else 0 end) p
                $extra_field
                FROM $table_name
                WHERE (time BETWEEN '$start' AND '$end')
                GROUP BY x";

        }

    }

}


new WP_HeimdallAddon_WeeklyReport();