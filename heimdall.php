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

 /*
    Plugin Name: Heimdall
    Plugin URI: https://wordpress.org/plugins/heimdall
    Description: A simple way to tracking clients activities.
    Version: 1.1.2
    Author: Arman Afzal
    Author URI: https://github.com/Rmanaf
    License: Apache License, Version 2.0
    Text Domain: heimdall
 */


defined('ABSPATH') or die;

require_once __DIR__ . "/includes/query-builder.php";

if (!class_exists('WP_Heimdall_Plugin')) {

    class WP_Heimdall_Plugin
    {

        private static $text_domain = 'heimdall';

        private static $db_version = '0.9.0';

        private static $content_type = [
            'Undefined',
            'Home',
            'Page',
            'Post',
            'Category',
            'Tag',
            'Comment'
        ];

        private static $query_params = ['unique',   'today', 'visitors',  'network' ];

        private static $styles = [ 'lt-10',  'lt-50', 'lt-100', 'lt-500',  'gt-500',  'gt-1k' , 'gt-5k' , 'em-10k' , 'em-1m' , 'em-5m' ];

        private static $hit_hooks = [ 'wp_footer' ];

       
    
        function __construct()
        {


            $this->check_db();

            
            add_shortcode('statistics', [$this, 'statistics_shortcode']);


            add_action('admin_init', [$this, 'admin_init']);

            add_action('admin_print_scripts', [$this, 'enqueue_admin_scripts']);

            add_action( "wp_dashboard_setup", [$this, "wp_dashboard_setup"] );

            add_action( 'pre_get_posts', [$this , 'pre_get_posts']);


            add_filter( "the_content" , [$this , 'filter_content']);

            add_filter( "the_excerpt" , [$this , 'filter_excerpt']);

           

            $hooks = get_option( 'wp_dcp_heimdall_active_hooks', '');


           

            if(empty($hooks))
            {  
                $hooks = self::$hit_hooks;
            }else {
                $hooks = explode(',' ,  $hooks );
            }


            foreach($hooks as $h)
            {
                add_action($h, [$this, 'record_activity']);
            }


            /**
             * DCP hooks
             */
            add_action('dcp_settings_before_content', [$this, 'settings_page']);

            add_filter('dcp_shortcodes_list', [$this, 'add_shortcode_to_list']);

        }

        /**
         * register general settings
         * @since 1.0.0
         */
        public function admin_init()
        {

            // checks for control panel plugin 
            if (defined('DIVAN_CONTROL_PANEL')) {

                global $_DCP_PLUGINS;

                // control panel will take setting section
                $group = 'dcp-settings-general';

                array_push($_DCP_PLUGINS, ['slug' => self::$text_domain, 'version' => $this->get_version()]);

            } else {

                $group = 'general';

            }

            register_setting($group, 'wp_dcp_heimdall_active_hooks', ['default' => '']);
            register_setting($group, 'wp_dcp_heimdall_post_position', ['default' => 0]);
            register_setting($group, 'wp_dcp_heimdall_page_position', ['default' => 0]);

            // settings section
            add_settings_section(
                'wp_dcp_heimdall_plugin',
                __('Heimdall', self::$text_domain) . "<span class=\"gdcp-version-box wp-ui-notification\">" . $this->get_version() . "<span>",
                [&$this, 'settings_section_cb'],
                $group
            );

            add_settings_field(
                'wp_dcp_heimdall_active_hooks',
                "Hooks",
                [&$this, 'settings_field_cb'],
                $group,
                'wp_dcp_heimdall_plugin',
                ['label_for' => 'wp_dcp_heimdall_active_hooks']
            );

            add_settings_field(
                'wp_dcp_heimdall_post_position',
                "The Number of Visitors",
                [&$this, 'settings_field_cb'],
                $group,
                'wp_dcp_heimdall_plugin',
                ['label_for' => 'wp_dcp_heimdall_post_position']
            );

            add_settings_field(
                'wp_dcp_heimdall_page_position',
                "",
                [&$this, 'settings_field_cb'],
                $group,
                'wp_dcp_heimdall_plugin',
                ['label_for' => 'wp_dcp_heimdall_page_position']
            );

        }

        public function pre_get_posts($query)
        {

            if( is_admin() )
            {
                return;
            }


            if ( $query->is_search() && $query->is_main_query()) 
            {

                $search = get_search_query();

                echo  $search;

            }

        }


        /**
         * add statistics shortcode to the excerpt
         * @since 1.0.0
         */
        public function filter_excerpt($excerpt)
        {

            $option = '';

            if(is_single()){
                $option = 'wp_dcp_heimdall_post_position';
            }

            if(empty($option))
            {
                return $excerpt;
            }

            $pos = get_option($option, 0);

            switch($pos){
                case 3: return $excerpt . "[statistics]" ;
                case 4: return "[statistics]" . $excerpt ;
                default:
                    return $excerpt;
            }

        }

        /**
         * add statistics shortcode to the content
         * @since 1.0.0
         */
        public function filter_content($content)
        {

            $option = '';

            if(is_page()){
                $option = 'wp_dcp_heimdall_page_position';
            }

            if(is_single()){
                $option = 'wp_dcp_heimdall_post_position';
            }

            if(empty($option))
            {
                return $content;
            }

            $pos = get_option($option, 0);

            switch($pos){
                case 1: return $content . "[statistics]" ;
                case 2: return "[statistics]" . $content ;
                default:
                    return $content;
            }

        }


        /**
         * add statistics dashboard widget
         * @since 1.0.0
         */
        public function wp_dashboard_setup()
        {
        
            if(current_user_can( 'administrator' )){
                
                wp_add_dashboard_widget('dcp_heimdall_statistics', 'Statistics', [$this,'admin_weekly_report_chart']);

            }

        }
        

        /**
         * weekly report chart body
         * @since 1.0.0
         */
        public function admin_weekly_report_chart()
        {

            echo '<div class="chart-container" style="position: relative; width:100%; height:300px;">
                    <canvas id="statisticsChart"></canvas>
                 </div>';

        }


         /**
         * Add shortcodes to the DCP shortcodes list
         * @since 1.0.0
         */
        public function add_shortcode_to_list($list){

            $list[] = [
                'template' => "[statistics class='' params='' hook='']",
                'description' => __("Renders the number of visitors", self::$text_domain),
            ];

            return $list;

        }

        /**
         * statistics shortcode
         * @since 1.0.0
         */
        public function statistics_shortcode($atts = [], $content = null)
        {

            global $wpdb;

            extract(shortcode_atts( [
                'class'  => '',
                'params' => 'unique', // by default get unique IP
                'hook'  => '',
                'tag'   => 'p'
            ], $atts));

            $query_builder = new WP_Heimdall_Query_Builder(
                self::table_name() , 
                explode(',', strtolower(trim($params))) ,
                $hook
            );

            $count = $wpdb->get_var( $query_builder->get_query() );

            $style = $this->get_style($count);

            $tag = strtolower(trim($tag));

            return "<$tag data-statistics-value=\"$count\" class=\"$class statistics-$style\">$count</$tag>";

        }


        /**
         * settings page content
         * @since 1.0.0
         */
        public function settings_page()
        {

            global $_DCP_ACTIVE_TAB;

            if (defined('DIVAN_CONTROL_PANEL') && $_DCP_ACTIVE_TAB != 'overview') {
                return;
            }

            ?> 
            
            <h2><?php _e('Settings' , self::$text_domain) ?></h2>


            <?php

        }


        /**
         * settings section header
         * @since 1.0.0
         */
        public function settings_section_cb(){

            ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label>
                                <?php _e("Bug & Issues Reporting"); ?>
                            </label>
                        </th>
                        <td>
                            <?php _e("<p>If you faced any issues, please tell us on <strong><a target=\"_blank\" href=\"https://github.com/Rmanaf/wp-statistics/issues/new\">Github</a></strong>"); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php

        }


        /** 
         * settings section 
         * @since 1.0.0
         */
        public function settings_field_cb($args)
        {
            switch ($args['label_for']) {

                case 'wp_dcp_heimdall_active_hooks':

                    $hooks = get_option('wp_dcp_heimdall_active_hooks', implode(',' , self::$hit_hooks));

                    ?>
                        <textarea data-placeholder="Enter Hooks:" class="large-text code" id="wp_dcp_heimdall_active_hooks" name="wp_dcp_heimdall_active_hooks"><?php echo $hooks; ?></textarea>   
                    <?php
                    break;

                case 'wp_dcp_heimdall_page_position':
                
                    $pos = get_option('wp_dcp_heimdall_page_position', 0);

                    ?>

                    <span><?php _e('Page', self::$text_domain) ?></span>
                    
                    <select name="wp_dcp_heimdall_page_position" id="wp_dcp_heimdall_page_position" >
                        <option <?php selected( $pos , 0); ?> value="0"> <?php _e('— Do not show —', self::$text_domain); ?>
                        <option <?php selected( $pos , 1); ?> value="1"> <?php _e('After the Content', self::$text_domain); ?>
                        <option <?php selected( $pos , 2); ?> value="2"> <?php _e('Before the Content', self::$text_domain); ?>
                    </select>
                
                    <?php
                    break;

                case 'wp_dcp_heimdall_post_position':
                
                    $pos = get_option('wp_dcp_heimdall_post_position', 0);

                    ?>

                    <span><?php _e('Post', self::$text_domain) ?></span>

                    <select name="wp_dcp_heimdall_post_position" id="wp_dcp_heimdall_post_position" >
                        <option <?php selected( $pos , 0); ?> value="0"> <?php _e('— Do not show —', self::$text_domain); ?>
                        <option <?php selected( $pos , 1); ?> value="1"> <?php _e('After the Content', self::$text_domain); ?>
                        <option <?php selected( $pos , 2); ?> value="2"> <?php _e('Before the Content', self::$text_domain); ?>
                        <option <?php selected( $pos , 3); ?> value="3"> <?php _e('After the Excerpt', self::$text_domain); ?>
                        <option <?php selected( $pos , 4); ?> value="4"> <?php _e('Before the Excerpt', self::$text_domain); ?>
                    </select>

                    <?php
                    break;

            }

        }


        /**
         * returns table name
         * @since 1.0.0
         */
        public static function table_name()
        {

            return 'dcp_heimdall_activities';

        }


        /**
         * checks if plugin table exists in database
         * @since 1.0.0
         */
        private function check_db()
        {

            global $wpdb;

            $dbv = get_option('wp_dcp_heimdall_db_version', '');

            if ($dbv == self::$db_version) {

                return;

            }

            $table_name = self::table_name();

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                blog smallint NOT NULL,
                time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                ip tinytext,
                page smallint,
                type smallint,
                user smallint,
                hook tinytext NOT NULL,
                PRIMARY KEY  (id)
                ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql);

            update_option('wp_dcp_heimdall_db_version', self::$db_version);
            

        }


        /**
         * prints admin scripts
         * @since 1.0.0
         */
        public function enqueue_admin_scripts()
        {

            global $wpdb;

            $ver = $this->get_version();

            $screen = get_current_screen();

            if(current_user_can( 'administrator' ) && $screen->id  == 'dashboard' )
            {

                $query_builder = new WP_Heimdall_Query_Builder(self::table_name());

                // get GMT
                $cdate = current_time( 'mysql' , 1 );

                // start from 6 days ago
                $start = new DateTime($cdate);
                $start->sub(new DateInterval('P6D')); 

                // today
                $end = new DateTime($cdate);

                
                wp_enqueue_script( 'dcp-chart-js-bundle', plugins_url( '/assets/chart.bundle.min.js', __FILE__ ), [], $ver, false);
                wp_enqueue_script( 'dcp-chart-js', plugins_url( '/assets/chart.min.js', __FILE__ ), [], $ver, false);
                wp_enqueue_script( 'statistics-admin', plugins_url( '/assets/statistics-admin.js', __FILE__ ), ['jquery'], $ver, true);    
            
                wp_localize_script( 'statistics-admin', 'statistics_data', [
                    'is_multisite' => is_multisite(),
                    'visitors' => $wpdb->get_results($query_builder->get_chart_query($start , $end), ARRAY_A )
                ]);


            }

            if(in_array($screen->id , ['options-general', 'settings_page_dcp-settings'])){

                //settings
                wp_enqueue_style('dcp-tag-editor', plugins_url('assets/jquery.tag-editor.css', __FILE__), [], $ver, 'all');
                wp_enqueue_style('heimdall-settings', plugins_url('assets/heimdall-settings.css', __FILE__), [], $ver, 'all');

                wp_enqueue_script('dcp-caret', plugins_url('assets/jquery.caret.min.js', __FILE__), ['jquery'], $ver, true);
                wp_enqueue_script('dcp-tag-editor', plugins_url('assets/jquery.tag-editor.min.js', __FILE__), [], $ver, true);
                wp_enqueue_script('heimdall-settings', plugins_url('assets/heimdall-settings.js', __FILE__), [], $ver, true);

            }
            

        }



        /**
         * returns client ip address
         * @since 1.0.0
         */
        private function get_ip_address()
        {

            foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {

                if (array_key_exists($key, $_SERVER) === true) {

                    foreach (explode(',', $_SERVER[$key]) as $ip) {
                        $ip = trim($ip);

                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                            return $ip;
                        }

                    }

                }

            }

        }


        /**
         * records client activity
         * @since 1.0.0
         */
        public function record_activity()
        {

            global $wpdb, $post;

            $type = 0;
            $page = null;

            /**
             * type 0 is undefined
             * type 1 is home page
             * type 2 is inner page
             * type 3 is post
             */
            if(is_home()){

                $type = 1;

            } else if (is_page()) {

                $page = $post->ID;

                $type = 2;

            } else if (is_single()) {

                $page = $post->ID;

                $type = 3;

            }

            $wpdb->insert(
                self::table_name(),
                [
                    'time' => current_time('mysql' , 1),
                    'ip' => $this->get_ip_address(),
                    'page' => $page,
                    'type' => $type,
                    'blog' => get_current_blog_id(),
                    'user' => get_current_user_id(),
                    'hook' => current_filter()
                ]
            );

            echo $wpdb->last_error;

        }

        private function get_style($v){

            $res = 0;

            // lt-50
            if($v < 50 && $v > 10 ) $res = 1;
            
            // lt-100
            if($v < 100 && $v > 50 ) $res = 2;
            
            // lt-500
            if($v < 500 && $v > 100 )  $res = 3;
            
            // gt-500
            if($v < 1000 && $v > 500 ) $res = 4;

            // gt-1k
            if($v < 5000 && $v > 1000) $res = 5;
            
            // gt-5k
            if($v < 10000 && $v > 5000) $res = 6;
            
            // em-10k
            if($v < 1000000 && $v > 10000) $res = 7;
            
            // em-1m
            if($v < 5000000 && $v > 1000000) $res = 8;
            
            // em-5m
            if($v > 5000000) $res = 9;
            
            return self::$styles[$res];

        }


        /**
         * returns plugin version
         * @since 1.0.0
         */
        private function get_version()
        {

            return get_plugin_data(__FILE__)['Version'];

        }

    }

}

$HEIMDALL_PLUGIN_INSTANCE = new WP_Heimdall_Plugin();