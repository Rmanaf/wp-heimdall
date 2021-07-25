<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_HeimdallAddon_CustomStats')) {

    class WP_HeimdallAddon_CustomStats
    {

        // addon slug
        private static $slug = "custom-query";

        /**
         * @since 1.3.2
         */
        static function init()
        {

            $class = get_called_class();



            add_action('init', "$class::rewrite_rules");

            add_action('parse_request', "$class::parse_request");

            add_filter('query_vars', "$class::query_vars");

            $code = get_option('wp_dcp_heimdall_custom_stats', '');

            if (empty($code)) {
                return;
            }

            add_action("heimdall--dashboard-statistic-widget-tabs", "$class::dashboard_statistic_widget_tabs", 90);

            add_action("heimdall--dashboard-statistic-widget-tab-content", "$class::dashboard_statistic_widget_tab_content", 10);
        }

        /**
         * @since 0.9.0
         */
        static function rewrite_rules()
        {
            $ns = "heimdall";

            add_rewrite_rule('^cstats/(.*?)/?', "index.php?{$ns}_custom_stats=\$matches[1]", 'top');

            flush_rewrite_rules();
        }

        /**
         * @since 0.9.0
         */
        static function query_vars($query_vars)
        {
            $ns = "heimdall";

            $query_vars[] = "{$ns}_custom_stats";

            return $query_vars;
        }

        /**
         * @since 0.9.0
         */
        static function parse_request(&$wp)
        {

            $ns = "heimdall";

            if (array_key_exists("{$ns}_custom_stats", $wp->query_vars)) {

                $filePath = __DIR__ . "/custom-stats-page.php";

                include $filePath;

                exit;
            }

            return;
        }

        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget_tabs()
        {

            WP_Heimdall_Dashboard::create_admin_widget_tab(esc_html__("Custom Stats", "heimdall"), "cstats");
        }

        /**
         * @since 1.3.1
         */
        static function dashboard_statistic_widget_tab_content()
        {

            $home = get_bloginfo("url");

            ob_start();

            echo "<iframe width='100%' height='400' src='$home/cstats/734398da515c4614a1a349fc8ce3a2a0/'></iframe>";

            WP_Heimdall_Dashboard::create_admin_widget_tab_content("cstats", ob_get_clean(), false);
        }
    }
}


WP_HeimdallAddon_CustomStats::init();
