<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!class_exists('WP_Heimdall_Options')) {

    class WP_Heimdall_Options
    {

        /**
         * @since 1.3.1
         */
        static function init()
        {

            $class = get_called_class();

            add_action('admin_init', "$class::admin_init");

            add_action('admin_print_scripts', "$class::enqueue_admin_scripts");

        }

        /**
         * @since 1.3.1
         */
        static function admin_init()
        {

            $class = get_called_class();

            $group = 'general';

            register_setting($group, 'wp_dcp_heimdall_active_hooks', ['default' => '']);
            register_setting($group, 'wp_dcp_heimdall_custom_stats', ['default' => '']);
            register_setting($group, 'wp_dcp_heimdall_post_position', ['default' => 0]);
            register_setting($group, 'wp_dcp_heimdall_page_position', ['default' => 0]);

            $title = esc_html__('Heimdall', "heimdall");

            $title_version = "<span class=\"gdcp-version-box wp-ui-notification\">v" . WP_Heimdall_Plugin::$version . "</span>";

            // settings section
            add_settings_section(
                'wp_dcp_heimdall_plugin',
                is_rtl() ? $title_version . $title : $title . $title_version ,
                "$class::settings_section_cb",
                $group
            );

            add_settings_field(
                'wp_dcp_heimdall_active_hooks',
                "Action hooks",
                "$class::settings_field_cb",
                $group,
                'wp_dcp_heimdall_plugin',
                ['label_for' => 'wp_dcp_heimdall_active_hooks']
            );

            add_settings_field( 
                'wp_dcp_heimdall_custom_stats',
                "Custom stats code",
                "$class::settings_field_cb",
                $group,
                'wp_dcp_heimdall_plugin',
                ['label_for' => 'wp_dcp_heimdall_custom_stats']
            );

            add_settings_field(
                'wp_dcp_heimdall_post_position',
                "Display the number of visitors",
                "$class::settings_field_cb",
                $group,
                'wp_dcp_heimdall_plugin',
                ['label_for' => 'wp_dcp_heimdall_post_position']
            );

            add_settings_field(
                'wp_dcp_heimdall_page_position',
                "",
                "$class::settings_field_cb",
                $group,
                'wp_dcp_heimdall_plugin',
                ['label_for' => 'wp_dcp_heimdall_page_position']
            );
        }


         /**
         * @since 1.3.1
         */
        static function enqueue_admin_scripts()
        {

            $ver = WP_Heimdall_Plugin::$version;

            $screen = get_current_screen();

            if (in_array($screen->id, ['options-general', 'settings_page_dcp-settings'])) {

                //settings
                wp_enqueue_style('dcp-tag-editor', plugins_url('../assets/css/jquery.tag-editor.css', __FILE__), [], $ver, 'all');
                wp_enqueue_style('heimdall-settings', plugins_url('../assets/css/heimdall-settings.css', __FILE__), [], $ver, 'all');

                wp_enqueue_script('dcp-caret', plugins_url('../assets/js/jquery.caret.min.js', __FILE__), ['jquery'], $ver, true);
                wp_enqueue_script('dcp-tag-editor', plugins_url('../assets/js/jquery.tag-editor.min.js', __FILE__), [], $ver, true);
                wp_enqueue_script('heimdall-settings', plugins_url('../assets/js/heimdall-settings.js', __FILE__), [], $ver, true);

            }

        }


        /**
         * @since 1.3.1
         */
        static function settings_section_cb()
        {
        }


        /** 
         * settings section 
         * @since 1.0.0
         */
        static function settings_field_cb($args)
        {
            switch ($args['label_for']) {

                case 'wp_dcp_heimdall_custom_stats':

                    $code = get_option('wp_dcp_heimdall_custom_stats', '' );
                    ?>
                    <textarea class="large-text code" id="wp_dcp_heimdall_custom_stats" name="wp_dcp_heimdall_custom_stats"><?php echo $code; ?></textarea>
                    <?php
                    break;

                case 'wp_dcp_heimdall_active_hooks':

                    $hooks = get_option('wp_dcp_heimdall_active_hooks', implode(',', WP_Heimdall_Plugin::$hit_hooks));

                    ?>
                    <textarea data-placeholder="..." class="large-text code" id="wp_dcp_heimdall_active_hooks" name="wp_dcp_heimdall_active_hooks"><?php echo $hooks; ?></textarea>
                    <dl>
                        <dd>
                            <p class="description">
                                e.g.&nbsp;&nbsp;&nbsp;&nbsp;wp_footer,&nbsp;&nbsp;wp_head ...
                            </p>
                        </dd>
                    </dl>
                    <?php
                    break;

                case 'wp_dcp_heimdall_page_position':

                    $pos = get_option('wp_dcp_heimdall_page_position', 0);

                    ?>

                    <span><?php esc_html_e('Page', "heimdall") ?></span>

                    <select name="wp_dcp_heimdall_page_position" id="wp_dcp_heimdall_page_position">
                        <option <?php selected($pos, 0); ?> value="0"> <?php esc_html_e('— Do not show —', "heimdall"); ?>
                        <option <?php selected($pos, 1); ?> value="1"> <?php esc_html_e('After Content', "heimdall"); ?>
                        <option <?php selected($pos, 2); ?> value="2"> <?php esc_html_e('Before Content', "heimdall"); ?>
                    </select>

                    <?php
                    break;

                case 'wp_dcp_heimdall_post_position':

                    $pos = get_option('wp_dcp_heimdall_post_position', 0);

                    ?>

                    <span><?php esc_html_e('Post', "heimdall") ?></span>

                    <select name="wp_dcp_heimdall_post_position" id="wp_dcp_heimdall_post_position">
                        <option <?php selected($pos, 0); ?> value="0"> <?php esc_html_e('— Do not show —', "heimdall"); ?>
                        <option <?php selected($pos, 1); ?> value="1"> <?php esc_html_e('After Content', "heimdall"); ?>
                        <option <?php selected($pos, 2); ?> value="2"> <?php esc_html_e('Before Content', "heimdall"); ?>
                        <option <?php selected($pos, 3); ?> value="3"> <?php esc_html_e('After Excerpt', "heimdall"); ?>
                        <option <?php selected($pos, 4); ?> value="4"> <?php esc_html_e('Before Excerpt', "heimdall"); ?>
                    </select>
                    <dl>
                        <dd>
                            <p class="description">
                                <?php
                                    $url = "https://github.com/Rmanaf/wp-heimdall/blob/master/README.md";
                                    printf(
                                        /* translators: %1$s is replaced with "string" */
                                        esc_html__('There is also a shortcode to display post views. See %1$s for more information.' , "heimdall" ) ,
                                        sprintf(
                                            '<a target="_blank" href="%s">%s</a>',
                                            $url,
                                            esc_html__( 'Readme', 'heimdall' )
                                        ));
                                 ?>
                                <pre><code>[statistics class='' params='' hook='']</code></pre>
                            </p>
                        </dd>
                    </dl>
                    <?php
                    break;
            }
        }
    }
}
