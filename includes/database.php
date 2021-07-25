<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_Heimdall_Database')) {

    class WP_Heimdall_Database
    {


        static $table_name = 'dcp_heimdall_activities';

        static $db_version = '1.0.2';



        /**
         * @since 1.3.1
         */
        static function insert($ip, $page, $type,  $hook, $meta = null , $metav2 = null , $time = null, $blog = null, $user = null)
        {
            global $wpdb;

            self::check_params($time, $blog, $user);

            $wpdb->insert(
                self::$table_name,
                [
                    'time' => $time,
                    'ip' => $ip,
                    'page' =>  $page,
                    'type' =>  $type,
                    'blog' => $blog,
                    'user' => $user,
                    'hook' => $hook,
                    'meta' => $meta,
                    'meta_v2' => $metav2
                ]
            );

            echo $wpdb->last_error;
        }



        /**
         * @since 1.3.1
         */
        static function insert_once($ip, $page, $type,  $hook, $meta = null , $metav2 = null , $time = null, $blog = null, $user = null)
        {
            global $wpdb;

            self::check_params($time, $blog, $user);

            $query = self::get_record_exists_query($ip, $page, $type, $hook , $meta , 7 , $time , $blog, $user);

            $count = $wpdb->get_var($query);

            if ($count == 0) {
                self::insert($ip, $page, $type,   $hook, $meta ,  $metav2 ,  $time,  $blog, $user);
            }
        }

        /**
         * @since 1.3.2
         */
        static function get_most_visited_posts_query($params = [] , $limit = 5, $hook = ''){

            global $wpdb;

            $table_name = self::$table_name;

            $records = "COUNT(*)";

            $timeCheck = "";

            $hookCheck = "";

            $limit = esc_sql($limit);

            if (in_array('unique' , $params)) {
                $records = "COUNT(DISTINCT activity.ip)";
            }

            if (in_array('today' , $params)) {
                $timeCheck = "AND DATE(`activity.time`)=CURDATE()";
            }

            if(!empty($hook)){
                $hookCheck = esc_sql($hook);
                $hookCheck = "AND activity.hook = $hook";
            }

            return "SELECT  post.* , $records AS records FROM `$table_name` AS activity , `$wpdb->posts` AS post
                        WHERE activity.page = post.ID $timeCheck $hookCheck
                        GROUP BY post.ID 
                        ORDER BY `records` DESC 
                        LIMIT $limit";

        }


        /**
         * @since 1.3.1
         */
        static function get_shortcode_query($params = [], $hook = '')
        {
            global $post;

            $table_name = self::$table_name;

            $query = "SELECT ";

            /**
             * $query_params data:
             *      0 is unique
             *      1 is today
             *      2 is visitors
             *      3 is network
             */
            if (in_array('unique', $params)) {
                $query = $query . "COUNT(DISTINCT ip)";
            } else {
                $query = $query . "COUNT(*)";
            }


            $query = $query . " FROM $table_name";

            $today = in_array('today', $params);

            if ($today) {

                $query = $query . " WHERE DATE(`time`)=CURDATE()";
            }

            if (!in_array('visitors', $params)) {

                $prefix =  $today ? " AND " : " WHERE ";

                if (is_home()) {
                    $query = $query . "{$prefix}type='1'";
                } else if (isset($post)) {
                    $query = $query . "{$prefix}page='$post->ID'";
                }
            } else if (!in_array('network', $params)) {

                $prefix =  $today ? " AND " : " WHERE ";

                $blog = get_current_blog_id();

                $query = $query . "{$prefix}blog='$blog'";
            }


            if (!empty($hook)) {

                if (strpos($query, 'WHERE') !== false) {

                    $query = $query . " AND hook='$hook'";
                } else {

                    $query = $query . " WHERE hook='$hook'";
                }
            }

            return $query;
        }

        


        /**
         * @since 1.3.1
         */
        static function get_record_exists_query($ip, $page, $type,  $filter, $meta = null , $threshold = 3,  $time = null, $blog = null, $user = null)
        {

            $table_name = self::$table_name;

            self::check_params($time, $blog, $user);

            $start = new DateTime($time);

            $start->sub(new DateInterval("PT{$threshold}S"));

            $start_date = $start->format('Y-m-d H:i:s');

            $page_param = is_null($page) ? "`page` IS NULL" : "`page` = '$page'";

            $meta_param = is_null($meta) ? "`meta` IS NULL" : "`meta` = '$meta'";

            return  "SELECT COUNT(*) FROM `$table_name` WHERE `ip` = '$ip' AND 
                    $page_param AND 
                    $meta_param AND
                    `type` = '$type' AND 
                    `hook` = '$filter' AND
                    `blog` = '$blog' AND
                    `user` = '$user' AND
                    `time` BETWEEN '$start_date' AND '$time'";
        }




        /**
         * @since 1.3.1
         */
        private static function check_params(&$time, &$blog, &$user)
        {

            if ($blog == null)
                $blog = get_current_blog_id();

            if ($user == null)
                $user = get_current_user_id();

            if ($time == null)
                $time = current_time('mysql', 1);
        }


        /**
         * @since 1.3.1
         */
        static function check_db()
        {

            global $wpdb;

            $dbv = get_option('wp_dcp_heimdall_db_version', '');

            if ($dbv == self::$db_version) {
                return;
            }

            $table_name = self::$table_name;

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                blog bigint(20) NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                ip tinytext,
                page bigint(20),
                type smallint,
                user smallint,
                hook tinytext,
                meta tinytext,
                meta_v2 text,
                PRIMARY KEY  (id)
                ) $charset_collate;";
            
           
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);

            update_option('wp_dcp_heimdall_db_version', self::$db_version);
        }

    }
}
