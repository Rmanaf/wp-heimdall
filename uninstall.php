<?php

/**
 * MIT License <https://github.com/Rmanaf/wp-heimdall/blob/master/LICENSE>
 * Copyright (c) 2018 Arman Afzal <rman.afzal@gmail.com>
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('wp_dcp_heimdall_active_hooks');

delete_option('wp_dcp_heimdall_db_version');

delete_option('wp_dcp_heimdall_post_position');

delete_option('wp_dcp_heimdall_page_position');
