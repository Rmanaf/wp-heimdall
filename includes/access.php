<?php
/*
 * Copyright 2018 Divan Kia Akam <info@divanhub.com>
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


require_once __DIR__ . "/commons.php";

if (!class_exists('WP_Access_Plugin')) {
    class WP_Access_Plugin
    {

        public static $_FORBIDDEN_CODE = 403;
        public static $_FORBIDDEN_TITLE = "Forbidden";
        public static $_FORBIDDEN_MESSAGE = "You don't have permission to access this page";

        function __construct()
        { 

            add_shortcode('access', [$this, 'access_shortcode']);

        }


        public function access_shortcode($atts = [], $content = null)
        {

            extract(shortcode_atts([
                'ip' => '',
                'error' => -1,
                'after' => '',
                'before' => '',
                'die' => true
            ], $atts));

            if ($this->has_access(WP_Heimdall_Commons::get_ip_address(), $ip)) {

                return do_shortcode($content);

            }

            if ($error > -1) {

                status_header($error);

                nocache_headers();

                echo $before;

                include get_query_template("$error");

                echo $after;

                if ($die) {
                    die();
                }

            }

        }

        private function extract_ip($text)
        {

            return array_filter(explode(',', $text), function ($elem) {
                
                return preg_replace('/\s/', '', $elem);

            });

        }

        private function has_access($ip = '', $list = null)
        {

            $mode = get_option('wp_access_allow_only', 1);

            if ($mode == 1) {
                return true;
            }


            if (empty($ip)) {
                $ip = WP_Heimdall_Commons::get_ip_address();
            }

            if (!isset($list)) {

                $list = get_option('wp_access_allow_only_list', '');

                if (empty($list)) {

                    return true;

                }

            }

            $iplist = $this->extract_ip($list);

            if (in_array($ip, $iplist)) {
                return $mode == 2;
            }

            foreach ($iplist as $i) {

                $pos = strpos($i, '*');

                if ($pos !== false) {
                    if (strncmp($ip, $i, $pos) == 0) {
                        return $mode == 2;
                    }
                }

            }

            return $mode == 3;

        }

        

        private function correct_ip_hint(){

            ?>

            <p>
                <?php _e("Add the following code to the <strong>wp-config.php</strong> file to get the correct IP address of the clients."); ?>
            </p>

            <textarea readonly class="large-text code" >
            // Code for showing correct client IP address
            if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { 
                $mte_xffaddrs = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] ); 
                $_SERVER['REMOTE_ADDR'] = $mte_xffaddrs[0]; 
            }
            </textarea>

            <?php

        }

    }
}
