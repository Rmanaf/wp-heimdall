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

if (!class_exists('WP_Most_Used_Keywords_Plugin')) {
    
    class WP_Most_Used_Keywords_Plugin
    {

        private static $version = "1.0.0";
        private static $slug = "most-used-keywords";

        function __construct()
        {
            
            add_action("admin_enqueue_scripts", [$this, "admin_enqueue_scripts"]);

            add_action("dcp-heimdall--dashboad-statistic-widget" , [$this , "dashboard_statistic_widget"] , 10);

            add_filter("dcp-heimdall--localized-data", [$this , "get_kewords_data"], 10, 1);

        }

        public function admin_enqueue_scripts()
        {

            wp_enqueue_style("muk-styles", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/css/muk-styles.css'), [], self::$version, "all");
            
            wp_enqueue_script("muk-script", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/js/muk-scripts.js'), ["jquery"], self::$version , true);
        
        }

        public function dashboard_statistic_widget()
        {
            ?>
            <h3><?php _e("Most used keywords" , WP_Heimdall_Plugin::$text_domain); ?></h2>
            <ul id="most-used-keywords" class="tags"></ul>
            <hr />
            <?php
        }

        public function get_kewords_data($data)
        {

            global $wpdb;

            // get GMT
            $cdate = current_time( 'mysql' , 1 );

            // start from 6 days ago
            $start = new DateTime($cdate);
            $start->sub(new DateInterval('P6D')); 

            // today
            $end = new DateTime($cdate);

            $data['keywords'] = $wpdb->get_results($this->get_most_used_keywords_query($start , $end), ARRAY_A);

            return $data;

        }

        public function get_most_used_keywords_query($start , $end)
        {
            // convert dates to mysql format
            $start = $start->format('Y-m-d H:i:s');
            $end   = $end->format('Y-m-d H:i:s');

            $blog_id = get_current_blog_id();

            $table_name = WP_Heimdall_Plugin::table_name();

            return "SELECT COUNT(*) count, meta
                    FROM $table_name
                    WHERE type='4' AND blog='$blog_id' AND (time BETWEEN '$start' AND '$end')
                    GROUP BY meta
                    ORDER BY count DESC
                    LIMIT 20" ;

        }

    }

}

new WP_Most_Used_Keywords_Plugin();