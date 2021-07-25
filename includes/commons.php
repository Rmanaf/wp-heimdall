<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_Heimdall_Commons')) {
    class WP_Heimdall_Commons
    {

        /**
         * @since 1.0.0
         */
        public static function get_ip_address()
        {

            foreach ([
                'HTTP_CLIENT_IP', 
                'HTTP_X_FORWARDED_FOR', 
                'HTTP_X_FORWARDED', 
                'HTTP_X_CLUSTER_CLIENT_IP', 
                'HTTP_FORWARDED_FOR', 
                'HTTP_FORWARDED', 
                'REMOTE_ADDR'
                ] as $key) {

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
    }
}
