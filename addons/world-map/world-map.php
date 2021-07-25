<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

require_once __DIR__ . "/database.php";

if (!class_exists('WP_HeimdallAddon_WorldMap')) {

    class WP_HeimdallAddon_WorldMap
    {

        // addon slug
        private static $slug = "world-map";


        private static $service = "http://geolocation-db.com/";


        /**
         * @since 1.3.1
         */
        static function init()
        {

            $class = get_called_class();

            WP_HeimdallAddon_WorldMap_Database::check_countries_table();

            WP_HeimdallAddon_WorldMap_Database::check_ip_table();

            add_action("admin_enqueue_scripts", "$class::admin_enqueue_scripts");

            add_action("heimdall--dashboard-statistic-widget-tabs", "$class::dashboard_statistic_widget_tabs", 10);

            add_action("heimdall--dashboard-statistic-widget-tab-content", "$class::dashboard_statistic_widget_tab_content", 10);

            //add_filter("heimdall--localize-script", "$class::get_dashboard_world_map_data" , 10, 1);
            
            add_action("wp_ajax_heimdall_world_map" , "$class::get_dashboard_world_map_data");

            add_filter("heimdall--record-metadata", "$class::add_country_data");

        }


        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget()
        {
        }
        

        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget_tabs() {
            
            WP_Heimdall_Dashboard::create_admin_widget_tab( esc_html__( "Countries", "heimdall" ) ,"countries");

        }

        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget_tab_content($title){

            $image = WP_Heimdall_Plugin::addon_url(self::$slug, "assets/img/map.svg");

            ob_start();

            ?>

            <div id="statisticsWorldMapDataContainer" style="position: relative;">
                <svg width="100%" height="400px"></svg>
                <div class="tooltip"></div>
            </div>

            <?php

            WP_Heimdall_Dashboard::create_admin_widget_tab_content("countries" , ob_get_clean() );
        }


        /**
         * @since 1.3.1
         */
        static function admin_enqueue_scripts(){

            $screen = get_current_screen();

            if(current_user_can( 'administrator' ) && $screen->id  == 'dashboard' )
            {

                wp_enqueue_style("world-map", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/css/world-map-admin.css'), [], WP_Heimdall_Plugin::$version, "all");
                
                wp_enqueue_script("topojson", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/js/topojson.min.js'), ['d3'], WP_Heimdall_Plugin::$version, true);
                
                wp_enqueue_script("world-map-2", WP_Heimdall_Plugin::addon_url(self::$slug ,  '/assets/js/world-map-admin.js'), ['d3' , 'topojson'], WP_Heimdall_Plugin::$version, true);

            }

        }




        /**
         * @since 1.3.1
         */
        static function get_dashboard_world_map_data() {

            global $wpdb;

            check_ajax_referer('heimdall-nonce');

            $data = [];

            $query = WP_HeimdallAddon_WorldMap_Database::get_world_map_data_query();

            $data["world_map_110m2"] = WP_Heimdall_Plugin::addon_url("world-map","assets/world-110m2.json");
            
            $data["world_country_names"] = WP_Heimdall_Plugin::addon_url("world-map","assets/world-country-names.csv");

            $data["world_map_data"] = $wpdb->get_results($query , ARRAY_A);

            $data["world_map_max"] = max(array_map(function($o){return $o["records"];}, $data["world_map_data"]));

            wp_send_json_success( $data );

        }




        /**
         * @since 1.3.1
         */
        static function add_country_data($metav2)
        {

            if (isset($metav2["ip"])) {
                $ip = $metav2["ip"];
                $service = self::$service;

                $ip_data = self::get_ip_data($ip);

                if(empty($ip_data)){

                    $response = wp_remote_get("$service/json/$ip");

                    if(is_wp_error( $response )){
                        return $metav2;
                    }

                    $json = wp_remote_retrieve_body($response);

                    $data = json_decode($json , true);

                    WP_HeimdallAddon_WorldMap_Database::insert_ip_data($ip , $data);
                    
                    $metav2['country_code'] = $data["country_code"];

                } else {

                    $geo = json_decode($ip_data[0]['data'], true);

                    $metav2['country_code'] = $geo["country_code"];

                }
            }

            return $metav2;
        }




        /**
         * @since 1.3.1
         */
        static function get_ip_data($ip)
        {
            
            global $wpdb;

            $query = WP_HeimdallAddon_WorldMap_Database::get_find_ip_query($ip);

            $data = $wpdb->get_results($query , ARRAY_A);

            return $data;

        }






    }
}
