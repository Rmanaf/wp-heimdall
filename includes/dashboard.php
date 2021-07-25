<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_Heimdall_Dashboard')) {

    class WP_Heimdall_Dashboard
    {

        /**
         * @since 1.3.1
         */
        static function init()
        {

            $class = get_called_class();

            add_action("wp_dashboard_setup", "$class::wp_dashboard_setup");
        }

        /**
         * @since 1.3.1
         */
        static function wp_dashboard_setup()
        {
            $class = get_called_class();

            if (current_user_can('administrator')) {
                wp_add_dashboard_widget('dcp_heimdall_statistics', 'Heimdall', "$class::admin_dashboard_widget");
            }
        }


        /**
         * @since 1.3.1
         */
        static function admin_dashboard_widget()
        {

            do_action("heimdall--dashboard-statistic-widget");

?>

            <div class="hmd-tab">
                <?php do_action("heimdall--dashboard-statistic-widget-tabs"); ?>
            </div>

        <?php

            do_action("heimdall--dashboard-statistic-widget-tab-content");
        }

        /**
         * @since 1.3.1
         */
        static function create_admin_widget_tab($title, $tab_id)
        {

        ?>
            <button class="hmd-btn tablinks" data-target="<?php echo $tab_id; ?>"><?php echo $title; ?></button>
        <?php

        }



        /**
         * @since 1.3.1
         */
        static function create_admin_widget_tab_content($title, $content, $ajax = true)
        {

        ?>

            <div id="<?php echo $title; ?>" class="hmd-tabcontent <?php echo ($ajax ?  'busy' : ''); ?>">
                <?php if ($ajax) : ?>
                    <div class="hmd-spinner-container">
                        <div class="hmd-spinner"></div>
                    </div>
                <?php endif; ?>
                <?php echo $content; ?>
            </div>

        <?php

        }
    }
}
