<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_HeimdallAddon_StatisticsShortcode')) {



    class WP_HeimdallAddon_StatisticsShortcode
    {

        // addon slug
        private static $slug = "statistics-shortcode";


        private static $query_params = ['unique',   'today', 'visitors',  'network'];


        private static $styles = ['lt-10',  'lt-50', 'lt-100', 'lt-500',  'gt-500',  'gt-1k', 'gt-5k', 'em-10k', 'em-1m', 'em-5m'];



        /**
         * @since 1.3.1
         */
        static function init()
        {

            $class = get_called_class();

            add_shortcode('statistics', "$class::statistics_shortcode");

        }



        /**
         * statistics shortcode
         * @since 1.3.1
         */
        static function statistics_shortcode($atts = [], $content = null)
        {

            global $wpdb;

            extract(shortcode_atts([
                'class'  => '',
                'params' => 'unique', 
                'hook'  => '',
                'tag'   => 'p'
            ], $atts));

            $query = WP_Heimdall_Database::get_shortcode_query(
                explode(',', strtolower(trim($params))),
                $hook
            );

            $count = $wpdb->get_var($query);

            $style = self::get_style($count);

            $tag = strtolower(trim($tag));

            $result = "<$tag data-statistics-value=\"$count\" class=\"$class statistics-$style\">$count</$tag>";

            return apply_filters("heimdall--statistics-result", $result);
        }


        /**
         * @since 1.0.0
         */
        private static function get_style($v)
        {

            $res = 0;

            // lt-50
            if ($v < 50 && $v > 10) $res = 1;

            // lt-100
            if ($v < 100 && $v > 50) $res = 2;

            // lt-500
            if ($v < 500 && $v > 100)  $res = 3;

            // gt-500
            if ($v < 1000 && $v > 500) $res = 4;

            // gt-1k
            if ($v < 5000 && $v > 1000) $res = 5;

            // gt-5k
            if ($v < 10000 && $v > 5000) $res = 6;

            // em-10k
            if ($v < 1000000 && $v > 10000) $res = 7;

            // em-1m
            if ($v < 5000000 && $v > 1000000) $res = 8;

            // em-5m
            if ($v > 5000000) $res = 9;

            return self::$styles[$res];
        }

    }

}