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

if (!class_exists('WP_Heimdall_Query_Builder')) {

    class WP_Heimdall_Query_Builder
    {

        private $query = "";

        private $table_name = "";

        function __construct($table_name , $params = [] , $hook = '')
        {

            global $post;

            $this->table_name = $table_name;

            $query = "SELECT ";

             /**
             * checks whether looking for unique IP or total hits
             * $query_params data:
             *      0 is unique
             *      1 is today
             *      2 is visitors
             *      3 is network
             */
            if(in_array('unique' ,$params)) {
                $query = $query . "COUNT(DISTINCT ip)";
            }
            else
            {
                $query = $query . "COUNT(*)";
            }


            $query = $query . " FROM $table_name";


            /**
             * checks if "today" parameter has mentioned
             */
            $today = in_array('today' , $params);

            if($today) 
            {

                $query = $query . " WHERE DATE(`time`)=CURDATE()";

            }



            /**
             * checks if specific data intended
             */
            if(!in_array('visitors' , $params))
            {

                $prefix =  $today ? " AND " : " WHERE " ;

                if(is_home())
                {
                    $query = $query . "{$prefix}type='1'";
                } 
                else if(isset($post))
                {
                    $query = $query . "{$prefix}page='$post->ID'";
                }

            } 
            else if(!in_array('network' , $params))
            {

                $prefix =  $today ? " AND " : " WHERE ";

                $blog = get_current_blog_id();

                $query = $query . "{$prefix}blog='$blog'"; 

            }


            if(!empty( $hook )){

                if(strpos($query, 'WHERE') !== false){

                    $query = $query . " AND hook='$hook'";

                } else {

                    $query = $query . " WHERE hook='$hook'";

                }

            }

            $this->query = $query;

        }

        public function get_most_used_keywords_query($start , $end)
        {
            // convert dates to mysql format
            $start = $start->format('Y-m-d H:i:s');
            $end   = $end->format('Y-m-d H:i:s');

            $blog_id = get_current_blog_id();

            return "SELECT COUNT(*) count, meta
                    FROM $this->table_name
                    WHERE type='4' AND blog='$blog_id' AND (time BETWEEN '$start' AND '$end')
                    GROUP BY meta
                    ORDER BY count DESC
                    LIMIT 30" ;

        }

        public function get_chart_query($start , $end){

            // convert dates to mysql format
            $start = $start->format('Y-m-d H:i:s');
            $end   = $end->format('Y-m-d H:i:s');

            $blog_id = get_current_blog_id();
            $extra_field = is_multisite() ? ", SUM(case when blog='$blog_id' then 1 else 0 end) w" : "";
            
            return "SELECT WEEKDAY(time) x,
                COUNT(DISTINCT ip) y,
                COUNT(*) z,
                SUM(case when type='1' then 1 else 0 end) p
                $extra_field
                FROM $this->table_name
                WHERE (time BETWEEN '$start' AND '$end')
                GROUP BY x";

        }

        public function get_query()
        {

            return $this->query;

        }


    }

}
