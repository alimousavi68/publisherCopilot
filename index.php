<?php
/*
Plugin Name: Publisher Copilot
Description: اافزونه دستیار هوشمند (کلاینت)
Version: 1.5
Author: Hasht Behesht
*/

// Declare Const vraibleS
define('COP_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('COP_PLUGIN_URL', plugins_url('', __FILE__));
define('COP_REST_API_SERVER_URL', 'http://localhost:8888/rssnews/wp-json/license/v1/validate/');

// date_default_timezone_set('Asia/Tehran');

// Include Libraries

// چک می‌کنیم آیا کلاس قبلاً تعریف شده است
if (!class_exists('i8_jDateTime')) {
    // Include jalali-date external library
    require_once plugin_dir_path(__FILE__) . '/library/jdatetime.class.php';
}

require_once (COP_PLUGIN_DIR_PATH . '/inc/helper_functions.php');
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once plugin_dir_path(__FILE__) . '/library/simple_html_dom.php';
include_once (plugin_dir_path(__FILE__) . '/feeds_list.php');
include_once (plugin_dir_path(__FILE__) . 'setting_page.php');
include_once (plugin_dir_path(__FILE__) . 'license_page.php');
include_once (plugin_dir_path(__FILE__) . 'schedule-queue.php');
include_once (plugin_dir_path(__FILE__) . '/inc/scraper.php');

include_once (plugin_dir_path(__FILE__) . '/inc/db.php');
include_once (plugin_dir_path(__FILE__) . '/inc/crons/crons.php');
include_once (plugin_dir_path(__FILE__) . '/inc/crons/scheduling_post_publishing.php');
