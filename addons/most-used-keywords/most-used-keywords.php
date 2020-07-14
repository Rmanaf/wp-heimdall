<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_HeimdallAddon_MostUsedKeywords')) {
    
    class WP_HeimdallAddon_MostUsedKeywords
    {

        // addon slug
        private static $slug = "most-used-keywords";
        

        /**
         * @since 1.3.1
         */
        static function init()
        {
            
            $class = get_called_class();

            add_action("admin_enqueue_scripts", "$class::admin_enqueue_scripts");

            add_action("heimdall--dashboard-statistic-widget" , "$class::dashboard_statistic_widget" , 10);

            add_filter("heimdall--localize-script", "$class::get_kewords_data", 10, 1);

        }

        /**
         * @since 1.0.0
         */
        static function admin_enqueue_scripts()
        {

            $screen = get_current_screen();

            if(current_user_can( 'administrator' ) && $screen->id  == 'dashboard' )
            {
                wp_enqueue_style("muk-styles", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/css/muk-styles.css'), [], WP_Heimdall_Plugin::$version, "all");
            
                wp_enqueue_script("muk-script", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/js/muk-scripts.js'), ["jquery"], WP_Heimdall_Plugin::$version , true);
            }

        }

        /**
         * @since 1.0.0
         */
        static function dashboard_statistic_widget()
        {
            ?>
            <h3><?php esc_html_e("The Most Searched Terms of the Past 7 Days:" , "heimdall"); ?></h3>
            <ul id="most-used-keywords" class="keywords">
                <li><?php esc_html_e("No terms found." , "heimdall"); ?></li>
            </ul>
            <?php
        }


        /**
         * @since 1.0.0
         */
        static function get_kewords_data($data)
        {

            global $wpdb;

            // get GMT
            $cdate = current_time( 'mysql' , 1 );

            $start = new DateTime($cdate);
            $start->sub(new DateInterval('P6D')); 

            // today
            $end = new DateTime($cdate);

            $dt = $wpdb->get_results(self::get_most_used_keywords_query($start , $end), ARRAY_A);

            $data['keywords'] = $dt;

            return $data;

        }


        /**
         * @since 1.0.0
         */
        static function get_most_used_keywords_query($start , $end)
        {
            // convert dates to mysql format
            $start = $start->format('Y-m-d H:i:s');
            $end   = $end->format('Y-m-d H:i:s');

            $blog_id = get_current_blog_id();

            $table_name = WP_Heimdall_Database::$table_name;

            return "SELECT COUNT(*) count, meta
                    FROM $table_name
                    WHERE `type`='4' AND `blog`='$blog_id' AND `meta` IS NOT NULL AND (`time` BETWEEN '$start' AND '$end')
                    GROUP BY meta
                    ORDER BY count DESC
                    LIMIT 20" ;

        }

    }

}