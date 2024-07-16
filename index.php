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
define('COP_REST_API_SERVER_URL', 'http://localhost:8888/rssnews/wp-json/license/v1/validate/');
define('COP_SUBSCRIPTION_SECRET_CODE', 'i8-3u38rAAg9ntYtXl##');

require_once (COP_PLUGIN_DIR_PATH . '/helper_functions.php');



// Save " LINCENCE PLAN " RESPONSE DATA to database
function i8_save_response_license_data($recived_data)
{
    $response_data = array(
        'plan_name' => $recived_data['plan_name'],
        'subscription_start_date' => $recived_data['subscription_start_date'],
        'plan_duration' => $recived_data['plan_duration'],
        'plan_cron_interval' => $recived_data['plan_cron_interval'],
        'plan_max_post_fetch' => $recived_data['plan_max_post_fetch'],
        'resources_data' => $recived_data['resources_data'],
    );
    update_option('i8_plan_name', $response_data['plan_name']);
    update_option('i8_subscription_start_date', $response_data['plan_name']);
    update_option('i8_plan_duration', $response_data['plan_name']);
    update_option('i8_plan_cron_interval', $response_data['plan_name']);
    update_option('i8_plan_max_post_fetch', $response_data['plan_name']);

    update_resources_details($response_data['resources_data']);
}

// Save " RESOURCE DETAILS " RESPONSE DATA to database
function update_resources_details($data_array)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_resource_details';

    // پاک کردن تمام رکوردهای قبلی
    $wpdb->query("TRUNCATE TABLE $table_name");

    // اضافه کردن داده‌های جدید
    foreach ($data_array as $data) {
        $wpdb->insert($table_name, array(
            'resource_id' => $data['resource_id'],
            'resource_title' => $data['resource_title'],
            'title_selector' => $data['title_selector'],
            'img_selector' => $data['img_selector'],
            'lead_selector' => $data['lead_selector'],
            'body_selector' => $data['body_selector'],
            'bup_date_selector' => $data['bup_date_selector'],
            'category_selector' => $data['category_selector'],
            'tags_selector' => $data['tags_selector'],
            'escape_elements' => $data['escape_elements'],
            'source_root_link' => $data['source_root_link'],
            'source_feed_link' => $data['source_feed_link'],
            'need_to_merge_guid_link' => $data['need_to_merge_guid_link']
        )
        );
    }
}

// get resources details from database
function get_resources_details()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_resource_details';
    $data = $wpdb->get_results("SELECT * FROM $table_name");
    // error_log('i am client:' . print_r($data, true));
    return $data;
}



// ACTIVATE PLUGIN AND FUNCTIONS
register_activation_hook(__FILE__, 'i8_pc_plugin_activate');

function i8_pc_plugin_activate()
{
    if (!get_option('i8_pc_plugin_sd')) {
        $encoded_date = base64_encode(current_time('timestamp'));
        add_option('i8_pc_plugin_sd', $encoded_date, '', 'no');
    }
    if (!get_option('i8_pc_plugin_vd')) {
        $valid_domains = array(
            'ashkaar.ir',
            'andishemoaser.ir',
            'sarkhaat.ir',
            'localhost:8888',
        );
        $encoded_domains = base64_encode(serialize($valid_domains));
        add_option('i8_pc_plugin_vd', $encoded_domains, '', 'no');
    }
}


// CHECK PLUGIN CONDITIONS
function i8_pc_plugin_check_conditions()
{

    $encoded_date = get_option('i8_pc_plugin_sd');
    $install_date = intval(base64_decode($encoded_date));
    $current_date = current_time('timestamp');
    $valid_period = 60 * DAY_IN_SECONDS;

    if (($current_date - $install_date) > $valid_period) {
        add_action('admin_notices', 'i8_pc_plugin_trial_expired_notice');
        add_action('admin_init', 'i8_pc_plugin_deactivate_self');
        return false;
    }

    $encoded_domains = get_option('i8_pc_plugin_vd');
    $valid_domains = unserialize(base64_decode($encoded_domains));
    $current_domain = $_SERVER['HTTP_HOST'];


    if (!in_array($current_domain, $valid_domains)) {
        // add_action('admin_notices', 'i8_pc_plugin_invalid_domain_notice');
        // add_action('admin_init', 'i8_pc_plugin_deactivate_self');
        return true;
    }
    return true;

}
add_action('init', 'i8_pc_plugin_check_conditions');




// $encoded_Code='';
// eval(base64_decode($encoded_Code));
//End encode 


// NOTICES FUNCTIONS
function i8_pc_plugin_trial_expired_notice()
{
    echo '<div class="notice notice-error"><p>مدت زمان آزمایشی افزونه به پایان رسیده است.</p></div>';
}

function i8_pc_plugin_invalid_domain_notice()
{
    echo '<div class="notice notice-error"><p>دامنه نامعتبر است. افزونه بر روی این دامنه کار نمی‌کند.</p></div>';
}

// DEACTIVATE PLUGIN FUNCTION
function i8_pc_plugin_deactivate_self()
{
    deactivate_plugins(plugin_basename(__FILE__));
}

if (!i8_pc_plugin_check_conditions()) {
    return; // جلوگیری از اجرای ادامه کدهای افزونه
} else {
    require_once ABSPATH . 'wp-admin/includes/file.php';

    require_once plugin_dir_path(__FILE__) . 'simple_html_dom.php';
    require_once plugin_dir_path(__FILE__) . 'resources_post_type.php';

    include_once (plugin_dir_path(__FILE__) . 'menu.php');
    include_once (plugin_dir_path(__FILE__) . 'setting_page.php');
    include_once (plugin_dir_path(__FILE__) . 'schedule-queue.php');
    include_once (plugin_dir_path(__FILE__) . 'scraper.php');

    // Hook into WordPress actions
    add_action('admin_init', 'custom_rss_parser_schedule_event');

    // Add action to create custom table if not exists
    add_action('admin_init', 'custom_rss_parser_create_tables');
}




// Include jalali-date external library
$jdatetime_file_path = plugin_dir_path(__FILE__) . 'jdatetime.class.php';

// چک می‌کنیم آیا کلاس قبلاً تعریف شده است
if (!class_exists('jDateTime')) {
    // اگر کلاس تعریف نشده، چک می‌کنیم آیا فایل وجود دارد
    if (file_exists($jdatetime_file_path)) {
        // اگر فایل وجود دارد، آن را فراخوانی می‌کنیم
        require_once $jdatetime_file_path;
    } else {
        // اگر فایل وجود ندارد، یک پیام خطا نمایش می‌دهیم یا لاگ می‌کنیم
        error_log('فایل jdatetime.class.php یافت نشد.');
    }
}


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

function remove_all_feed_on_feeds_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';
    $delete_status = $wpdb->query("DELETE FROM $table_name");
    if ($delete_status) {
        wp_safe_redirect(add_query_arg('success', 'true', wp_get_referer()));
        exit;
    } else {
        echo '<div class="notice notice-error is-dismissible">
                <p>مشکلی پیش آمد!</p>
            </div>';
    }
}



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

            i8_delete_item_at_scheulde_list($id);
            publish_post_at_scheduling_table();
        }

    }
}


// Hook to handle the scheduled event
add_action('custom_rss_parser_event', 'custom_rss_parser_run');

// Function to parse and store RSS feed data
function custom_rss_parser_run()
{
    // Replace 'YOUR_RSS_FEED_URL' with the actual RSS feed URL
    $args = array(
        'post_type' => 'resource',
        'post_status' => 'publish',
    );

    $feeds_list = new WP_Query($args);

    if ($feeds_list->have_posts()):
        while ($feeds_list->have_posts()):
            $feeds_list->the_post();
            $rss_feed_url = get_post_meta(get_the_ID(), 'source_feed_link', true);
            $source_root_link = get_post_meta(get_the_ID(), 'source_root_link', true);
            $resource_id = get_the_ID();
            $resource_name = get_the_title();
            $need_to_merge_guid_link = get_post_meta(get_the_ID(), 'need_to_merge_guid_link', true);

            // Fetch the RSS feed
            $rss_feed = fetch_rss_feed($rss_feed_url);

            if (!$rss_feed) {
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
        endwhile;

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


