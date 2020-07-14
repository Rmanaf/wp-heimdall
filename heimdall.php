<?php

/**
 * Plugin Name: Heimdall
 * Plugin URI: https://github.com/Rmanaf/wp-heimdall
 * Description: This plugin is for tracking your client activities.
 * Version: 1.3.1
 * Author: Rmanaf
 * Author URI: https://profiles.wordpress.org/rmanaf/
 * License: MIT License
 * License URI: https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE
 * Text Domain: heimdall
 * Domain Path: /languages
 */


defined('ABSPATH') or die;

require_once __DIR__ . "/includes/commons.php";
require_once __DIR__ . "/includes/database.php";
require_once __DIR__ . "/includes/options.php";
require_once __DIR__ . "/includes/dashboard.php";

if (!class_exists('WP_Heimdall_Plugin')) {

    class WP_Heimdall_Plugin
    {

        static $version = "1.3.1";

        private static $content_type = [
            'Undefined',
            'Home',
            'Page',
            'Post',
            'Category',
            'Tag',
            'Comment'
        ];


        static $hit_hooks = [];


        private static $addons = [
            "StatisticsShortcode",
            "MostUsedKeywords",
            "WeeklyReport",
            "Today",
            "WorldMap"
        ];


        /**
         * @since 1.3.1
         */
        static function init()
        {

            $class = get_called_class();

            self::$hit_hooks = explode(',',  get_option('wp_dcp_heimdall_active_hooks', ''));



            WP_Heimdall_Database::check_db();

            WP_Heimdall_Options::init();

            WP_Heimdall_Dashboard::init();




            add_action('admin_print_scripts', "$class::enqueue_admin_scripts");


            add_action("wp_enqueue_scripts",  "$class::enqueue_scripts");


            add_action('pre_get_posts', "$class::pre_get_posts");


            add_filter("the_content", "$class::filter_content");

            add_filter("the_excerpt", "$class::filter_excerpt");

            add_action('plugins_loaded',  "$class::load_plugin_textdomain");



            foreach (self::$hit_hooks as $h) {

                add_action($h, function () use ($h) {

                    if (did_action($h) > 1) {
                        return;
                    }

                    self::record_activity();
                });
            }

            self::activate_addons();
        }


        /**
         * @since 1.3.1
         */
        static function load_plugin_textdomain(){
            load_plugin_textdomain( "heimdall", FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
        }


        /**
         * @since 1.0.0
         */
        static function pre_get_posts($query)
        {

            if (is_admin()) {
                return;
            }

            if (did_action("pre_get_posts") > 1) {
                return;
            }


            if ($query->is_search() && $query->is_main_query()) {

                $keyword = get_search_query();

                // ignore whitespace and empty values
                if (empty(trim($keyword))) {
                    return;
                }

                $ip = WP_Heimdall_Commons::get_ip_address();

                if ($ip == null) {
                    $ip = 'unknown';
                }

                WP_Heimdall_Database::insert_once($ip, null,  4,   'pre_get_posts', $keyword);
            }
        }


        /**
         * @since 1.0.0
         */
        static function filter_excerpt($excerpt)
        {

            $option = '';

            if (is_single()) {
                $option = 'wp_dcp_heimdall_post_position';
            }

            if (empty($option)) {
                return $excerpt;
            }

            $pos = get_option($option, 0);

            $value =  apply_filters("heimdall--views-num",  "[statistics]");

            switch ($pos) {
                case 3:
                    return $excerpt . $value;
                case 4:
                    return $value . $excerpt;
                default:
                    return $excerpt;
            }
        }

        /**
         * @since 1.0.0
         */
        static function filter_content($content)
        {

            $option = '';

            if (is_page()) {
                $option = 'wp_dcp_heimdall_page_position';
            }

            if (is_single()) {
                $option = 'wp_dcp_heimdall_post_position';
            }

            if (empty($option)) {
                return $content;
            }

            $pos = get_option($option, 0);

            $value =  apply_filters("heimdall--views-num",  "[statistics]");

            switch ($pos) {
                case 1:
                    return $content . $value;
                case 2:
                    return $value . $content;
                default:
                    return $content;
            }
        }


        /**
         * @since 1.0.0
         */
        static function enqueue_scripts()
        {

            $ver = self::$version;

            $data =  apply_filters("heimdall--client-script", ['ajaxurl' => admin_url('admin-ajax.php')]);

            wp_register_script("heimdall-client", "", []);

            wp_localize_script("heimdall-client", "heimdall",  $data);

            wp_enqueue_script("heimdall-client");

            wp_enqueue_script("heimdall-client-ajax", plugins_url('/assets/js/client-ajax.js', __FILE__), ["heimdall-client"], $ver, false);
        }


        /**
         * @since 1.0.0
         */
        static function enqueue_admin_scripts()
        {

            $ver = self::$version;

            $screen = get_current_screen();

            if (current_user_can('administrator') && $screen->id  == 'dashboard') {

                // tabs
                wp_enqueue_style("heimdall-tabs", plugins_url('/assets/css/tabs.css', __FILE__), [], $ver, "all");

                wp_enqueue_script("heimdall-tabs", plugins_url('/assets/js/tabs.js', __FILE__), [], $ver, false);


                // chart.js
                wp_enqueue_script('dcp-chart-js-bundle', plugins_url('/assets/js/chart.bundle.min.js', __FILE__), [], $ver, false);



                wp_register_script('heimdall-admin', '', [], false);

                wp_enqueue_script('heimdall-admin');

                wp_localize_script('heimdall-admin', 'heimdall', apply_filters("heimdall--localize-script", [
                    'is_multisite' => is_multisite(),
                    'ajaxurl' => admin_url('admin-ajax.php')
                ]));
            }
        }


        /**
         * @since 1.3.1
         */
        static function get_request_type_page()
        {

            global $post;

            $type = 0;
            $page = null;

             /**
             * type 0 is undefined
             * type 1 is homepage
             * type 2 is page
             * type 3 is post
             * type 4 is search
             * type 5 is 404
             */
            if (is_home() || is_front_page()) {
                $type = 1;
            } else if (is_404()) {
                $type = 5;
            } else if (is_page() && !is_front_page()) {
                $page = $post->ID;
                $type = 2;
            } else if (is_single()) {
                $page = $post->ID;
                $type = 3;
            }

            $type = apply_filters("heimdall--record-type", $type);

            return [
                "page" => $page,
                "type" => $type
            ];
        }


        /**
         * @since 1.0.0
         */
        static function record_activity($type = null, $pid = null)
        {

            global $wp;

            $type_post_dt = self::get_request_type_page();

            $ip = WP_Heimdall_Commons::get_ip_address();

            if ($ip == null) {
                $ip = 'unknown';
            }

            $filter = current_filter();

            $url = add_query_arg($wp->query_vars, home_url($wp->request));

            $metav2 = [];

            if (!empty($ip) && $ip != "unknown") {
                $metav2["ip"] = $ip;
            }

            $metav2["url"] = urlencode($url);

            $metav2 = apply_filters("heimdall--record-metadata", $metav2);

            $metav2 = json_encode($metav2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            WP_Heimdall_Database::insert_once(
                $ip,
                is_null($pid) ?  $type_post_dt["page"] : $pid,
                is_null($type) ? $type_post_dt["type"] : $type,
                $filter,
                $type_post_dt["type"] == 5 ? get_queried_object() : null,
                $metav2
            );

        }



        /**
         * @since 1.0.0
         */
        static function activate_addons()
        {

            $addons_dir = __DIR__ . "/addons";

            foreach (glob("$addons_dir/*", GLOB_ONLYDIR) as $addon) {

                $name = basename($addon);

                $path = path_join($addon, "{$name}.php");

                if (file_exists($path)) {

                    require_once $path;
                }
            }

            foreach (self::$addons as $addon) {
                $class = "WP_HeimdallAddon_" . $addon;
                $class::init();
            }
        }


        /**
         * @since 1.0.0
         */
        static function addon_url($addon, $path)
        {
            $path = rtrim(ltrim($path, "/"), "/");
            return plugins_url("/addons/$addon/$path", __FILE__);
        }
    }
}

WP_Heimdall_Plugin::init();
