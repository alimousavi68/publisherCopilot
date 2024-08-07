<?php
/*
Plugin Name: Publisher Copilot
Description: اافزونه دستیار هوشمند (کلاینت)
Version: 1.3
Author: Hasht Behesht
*/

// Declare Const vraibleS
define('COP_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
define('COP_PLUGIN_URL', plugins_url('', __FILE__));
define('COP_REST_API_SERVER_URL', 'https://copublisher.ir/wp-json/license/v1/validate/');

require_once (COP_PLUGIN_DIR_PATH . '/helper_functions.php');


// DEACTIVATE PLUGIN FUNCTION
function i8_pc_plugin_deactivate_self()
{
    deactivate_plugins(plugin_basename(__FILE__));
}

// چک می‌کنیم آیا کلاس قبلاً تعریف شده است
if (!class_exists('jDateTime')) {
    // Include jalali-date external library
    require_once plugin_dir_path(__FILE__) . 'jdatetime.class.php';
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once plugin_dir_path(__FILE__) . 'simple_html_dom.php';
include_once (plugin_dir_path(__FILE__) . 'menu.php');
include_once (plugin_dir_path(__FILE__) . 'setting_page.php');
include_once (plugin_dir_path(__FILE__) . 'schedule-queue.php');
include_once (plugin_dir_path(__FILE__) . 'scraper.php');





// Add action to create custom table if not exists
add_action('admin_init', 'custom_rss_parser_create_tables');
// Function to check if custom tables exist and create them if not
function custom_rss_parser_create_tables()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';
    $table_post_schedule = $wpdb->prefix . 'pc_post_schedule';
    $table_resource_details = $wpdb->prefix . 'custom_resource_details'; // نام جدول جدید

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title text NOT NULL,
            resource_name text NOT NULL,
            resource_id mediumint(9) NOT NULL,
            pub_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            guid text NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_post_schedule'") != $table_post_schedule) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql_2 = "CREATE TABLE $table_post_schedule (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id mediumint(9) NOT NULL,
            publish_priority tinytext NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_2);
    }

    // ایجاد جدول جدید برای جزئیات منابع
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_resource_details'") != $table_resource_details) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql_3 = "CREATE TABLE $table_resource_details (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            resource_id bigint(20) NOT NULL,
            resource_title text NOT NULL,
            title_selector varchar(255) DEFAULT NULL,
            img_selector varchar(255) DEFAULT NULL,
            lead_selector varchar(255) DEFAULT NULL,
            body_selector varchar(255) DEFAULT NULL,
            bup_date_selector varchar(255) DEFAULT NULL,
            category_selector varchar(255) DEFAULT NULL,
            tags_selector varchar(255) DEFAULT NULL,
            escape_elements text DEFAULT NULL,
            source_root_link varchar(255) DEFAULT NULL,
            source_feed_link varchar(255) DEFAULT NULL,
            need_to_merge_guid_link tinyint(1) DEFAULT 0,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql_3);
    }
}


// Hook into WordPress actions
add_action('admin_init', 'custom_rss_parser_schedule_event');
// Schedule event to run every 5 minutes
function custom_rss_parser_schedule_event()
{

    // Schedule the event to run every 5 minutes
    if (!wp_next_scheduled('custom_rss_parser_event')) {
        wp_schedule_event(time(), '5minutes', 'custom_rss_parser_event');
    }

    if (!wp_next_scheduled('remove_all_feed_on_feeds_table')) {
        wp_schedule_event(time(), 'i8_daily_cron', 'remove_all_feed_on_feeds_table');
    }


    $start_time = get_option('start_cron_time') ? get_option('start_cron_time') : '08:30';
    $start_time_res = strtotime($start_time);

    $end_time = get_option('end_cron_time') ? get_option('end_cron_time') : '22:02';
    $end_time_res = strtotime($end_time);

    $news_interval_start = get_option('news_interval_start') ? get_option('news_interval_start') : '20';
    $news_interval_end = get_option('news_interval_end') ? get_option('news_interval_end') : '30';

    $work_time_count = intval($end_time) - intval($start_time);
    $sum_post_count = rand($news_interval_start, $news_interval_end);

    if ($work_time_count == 0) {
        $post_count_publishing_per_hours = 10;
    } else {
        $post_count_publishing_per_hours = round($sum_post_count / $work_time_count);
    }

    $post_interval_publishing = (60 / round($post_count_publishing_per_hours)) * 60;

    update_option('post_interval_publishing', $post_interval_publishing);

    if (!wp_next_scheduled('publish_post_at_scheduling_table')) {

        // تنظیم محدوده زمانی
        if (time() >= $start_time_res && time() <= $end_time_res) {
            // error_log('current time' . time());
            // error_log('start time' . $start_time);
            // error_log('end time' . $end_time);
            wp_schedule_event(time(), 'i8_pc_post_publisher_cron', 'publish_post_at_scheduling_table');
        }

    }
}

add_filter('cron_schedules', 'i8_register_daily_cron_schedule');
function i8_register_daily_cron_schedule($schedules)
{
    $schedules['i8_daily_cron'] = array(
        'interval' => (60 * 60) * 24,
        'display' => __('این کرون هر ۲۴ ساعت اجرا میشود')
    );

    $schedules['5minutes'] = array(
        'interval' => (5 * 60),
        'display' => __('این کرون هر ۵دقیقه اجرا میشود')
    );

    $post_interval_publishing = get_option('post_interval_publishing') + rand(500, 1500);
    $schedules['i8_pc_post_publisher_cron'] = array(
        'interval' => ($post_interval_publishing),
        'display' => __('این کرون هر چند دقیقه پستی را از جدول زمانبدی افزونه دستیار در سایت منتشر میکند')
    );
    return $schedules;
}
add_action('remove_all_feed_on_feeds_table', 'remove_all_feed_on_feeds_table');


// Hook to handle the scheduled event
add_action('publish_post_at_scheduling_table', 'publish_post_at_scheduling_table');
function publish_post_at_scheduling_table()
{
    // error_log('publish_post_at_scheduling_table RUNNING');
    date_default_timezone_set('Asia/Tehran');
    $start_time = strtotime(get_option('start_cron_time'));
    $end_time = strtotime(get_option('end_cron_time'));

    // تنظیم محدوده زمانی
    if (time() >= $start_time && time() <= $end_time) {

        global $wpdb;
        $table_post_schedule = $wpdb->prefix . 'pc_post_schedule';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_post_schedule'") == $table_post_schedule) {

            $high_priority_posts = $wpdb->get_results("SELECT * FROM $table_post_schedule WHERE publish_priority = 'high' ORDER BY id ASC LIMIT 1");
            $medium_priority_posts = $wpdb->get_results("SELECT * FROM $table_post_schedule WHERE publish_priority = 'medium' ORDER BY id ASC LIMIT 1");
            $low_priority_posts = $wpdb->get_results("SELECT * FROM $table_post_schedule WHERE publish_priority = 'low' ORDER BY id ASC LIMIT 1");

            if ($high_priority_posts) {
                i8_change_post_status($high_priority_posts);
            } elseif ($medium_priority_posts) {
                i8_change_post_status($medium_priority_posts);
            } elseif ($low_priority_posts) {
                i8_change_post_status($low_priority_posts);
            }

        }
    }
}

function i8_change_post_status($priority_posts)
{
    // error_log('i8_change_post_status RUNNING');

    global $wpdb;
    $table_post_schedule = $wpdb->prefix . 'pc_post_schedule';

    foreach ($priority_posts as $post) {

        // از $post برای دسترسی به مقادیر مختلف هر ردیف استفاده می‌کنیم
        $id = $post->id;
        $post_id = $post->post_id;

        // chack post status 
        $post_status = get_post_status($post_id);

        if ($post_status == 'draft') {
            // error_log('npost is draf and publishe it');

            date_default_timezone_set('Asia/Tehran');

            $random_interval = rand(400, 900);
            $publish_time = time() + $random_interval;

            // Prepare data for creating a WordPress post
            $post_data = array(
                'ID' => $post_id,
                'post_status' => 'future',
                'post_date' => date('Y-m-d H:i:s', $publish_time), // استفاده از زمان تصادفی برای post_date
                'post_date_gmt' => gmdate('Y-m-d H:i:s', $publish_time), // استفاده از زمان تصادفی برای post_date_gmt
            );
            wp_update_post($post_data);

            // delete record where id=$id at $table_post_schedule
            $action_status = $wpdb->delete($table_post_schedule, array('id' => $id));
            if ($action_status) {
                // error_log('i8: deleted record with id=' . $id . 'from table ' . $table_post_schedule);
            } else {
                // error_log('i8: failed to delete record with id=' . $id . 'from table ' . $table_post_schedule);
            }

        } else {
            // error_log('not fund or not aa draft post and delete record');

            i8_delete_item_at_scheulde_list($id, null);
            publish_post_at_scheduling_table();
        }

    }
}


// Hook to handle the scheduled event
add_action('custom_rss_parser_event', 'custom_rss_parser_run');

// Function to parse and store RSS feed data
function custom_rss_parser_run()
{
    $feeds_list = get_resources_details();

    if ($feeds_list):
        foreach ($feeds_list as $feed):

            // fetch data form feed item to variable 
            $rss_feed_url = $feed->source_feed_link;
            $source_root_link = $feed->source_root_link;
            $resource_id = $feed->resource_id;
            $resource_name = $feed->resource_title;
            $need_to_merge_guid_link = $feed->need_to_merge_guid_link;

            error_log($rss_feed_url);
            // Fetch the RSS feed
            $rss_feed = fetch_rss_feed($rss_feed_url);
            error_log($rss_feed);

            // exit, if rss feed not found
            if (!$rss_feed) {
                error_log('this feed is not available');
                return;
            }

            // Parse and store RSS feed data
            foreach ($rss_feed->channel->item as $item) {
                $title = $item->title;
                $pub_date = date('Y-m-d H:i:s', strtotime($item->pubDate));

                if (isset($item->guid)) {
                    if ($need_to_merge_guid_link == 1) {
                        $guid = $source_root_link . $item->guid . '';
                    } else {
                        $guid = $item->guid . '';
                    }
                } elseif (isset($item->link)) {
                    $guid = $item->link . '';
                }

                // Check if the item already exists in the database
                if (!custom_rss_parser_item_exists($guid)) {
                    // Insert the new item into the custom table
                    custom_rss_parser_insert_item($title, $pub_date, $guid, $resource_id, $resource_name);
                }
            }
        endforeach;
    endif;
}


// Function to fetch the RSS feed
function fetch_rss_feed($url)
{
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $rss_feed = simplexml_load_string($body);
    return $rss_feed;
}

// Function to check if an item already exists in the database
function custom_rss_parser_item_exists($guid)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';

    $result = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE guid = %s", $guid . ''));

    return $result > 0;
}

// Function to insert a new item into the custom table
function custom_rss_parser_insert_item($title, $pub_date, $guid, $resource_id, $resource_name)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';

    $wpdb->insert(
        $table_name,
        array(
            'title' => '' . $title,
            'resource_name' => $resource_name,
            'resource_id' => $resource_id,
            'pub_date' => $pub_date,
            'guid' => '' . $guid,
        )
    );

}


