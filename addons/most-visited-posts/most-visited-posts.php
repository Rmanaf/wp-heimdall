<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_HeimdallAddon_MostVisitedPostsShortcode')) {



    class WP_HeimdallAddon_MostVisitedPostsShortcode
    {

         // addon slug
         private static $slug = "most-visited-shortcode";

         private static $query_params = ['unique',   'today', 'network'];

       /**
         * @since 1.3.2
         */
        static function init()
        {

            $class = get_called_class();

            add_shortcode('top-posts', "$class::top_posts_shortcode");

        }

        /**
         * top posts shortcode
         * @since 1.3.2
         */
        static function top_posts_shortcode($atts = [], $content = null)
        {

            global $wpdb;

            $template = "";

            if(empty($content)){
                $template = "<a href='{{permalink}}'>{{post_title}} - {{hits}}</a><br>";
            }else{
                $template = $content;
            }

            extract(shortcode_atts([
                'params' => 'unique', 
                'hook'  => '',
                'limit' => 5
            ], $atts));

            $query = WP_Heimdall_Database::get_most_visited_posts_query(
                explode(',', strtolower(trim($params))),
                $limit,
                $hook
            );

            $posts = $wpdb->get_results($query , ARRAY_A);
            
            $result = "";

            foreach($posts as $post){

                $permalink = get_post_permalink($post["ID"]);

                $item = str_replace("{{permalink}}", $permalink , $template);

                $item = str_replace("{{hits}}", $post["records"] ,$item);

                foreach(['post_title' , 'guid' , 'post_content' , 'post_excerpt' , 'ID'] as $param){
                    $item = str_replace("{{{$param}}}", $post[$param] , $item);
                }

                $result .= $item;
            }

            return $result;

        }

         

    }


}

WP_HeimdallAddon_MostVisitedPostsShortcode::init();