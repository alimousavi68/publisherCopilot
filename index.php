<?php
/*
Plugin Name: Publisher Copilot
Description: اافزونه دستیار هوشمند (کبلاینت)
Version: 1.3
Author: Hasht Behesht
*/


$encoded_Code= 'CnJlZ2lzdGVyX2FjdGl2YXRpb25faG9vayhfX0ZJTEVfXywgJ2k4X3BjX3BsdWdpbl9hY3RpdmF0ZScpOwpmdW5jdGlvbiBpOF9wY19wbHVnaW5fYWN0aXZhdGUoKQp7CiAgICBpZiAoIWdldF9vcHRpb24oJ2k4X3BjX3BsdWdpbl9zZCcpKSB7CiAgICAgICAgJGVuY29kZWRfZGF0ZSA9IGJhc2U2NF9lbmNvZGUoY3VycmVudF90aW1lKCd0aW1lc3RhbXAnKSk7CiAgICAgICAgYWRkX29wdGlvbignaThfcGNfcGx1Z2luX3NkJywgJGVuY29kZWRfZGF0ZSwgJycsICdubycpOwogICAgfQogICAgaWYgKCFnZXRfb3B0aW9uKCdpOF9wY19wbHVnaW5fdmQnKSkgewogICAgICAgICR2YWxpZF9kb21haW5zID0gYXJyYXkoCiAgICAgICAgICAgICdyYXNhZGlwbHVzLmlyJywKICAgICAgICAgICAgJ2FuZGlzaGVocWFybi5pcicsCiAgICAgICAgICAgICdhc2hrYWFyLmlyJywKICAgICAgICAgICAgJ3Jhc2FkZXJvb3ouY29tJywKICAgICAgICAgICAgJ2FuZGlzaGVtb2FzZXIuaXInLAogICAgICAgICAgICAnbG9jYWxob3N0Ojg4ODgnCiAgICAgICAgKTsKICAgICAgICAkZW5jb2RlZF9kb21haW5zID0gYmFzZTY0X2VuY29kZShzZXJpYWxpemUoJHZhbGlkX2RvbWFpbnMpKTsKICAgICAgICBhZGRfb3B0aW9uKCdpOF9wY19wbHVnaW5fdmQnLCAkZW5jb2RlZF9kb21haW5zLCAnJywgJ25vJyk7CiAgICB9Cn0KCmZ1bmN0aW9uIGk4X3BjX3BsdWdpbl9jaGVja19jb25kaXRpb25zKCkKewoKICAgICRlbmNvZGVkX2RhdGUgPSBnZXRfb3B0aW9uKCdpOF9wY19wbHVnaW5fc2QnKTsKICAgICRpbnN0YWxsX2RhdGUgPSBpbnR2YWwoYmFzZTY0X2RlY29kZSgkZW5jb2RlZF9kYXRlKSk7CiAgICAkY3VycmVudF9kYXRlID0gY3VycmVudF90aW1lKCd0aW1lc3RhbXAnKTsKICAgICR2YWxpZF9wZXJpb2QgPSAxMCAqIERBWV9JTl9TRUNPTkRTOyAvLyDbsduwINix2YjYsgoKCiAgICBpZiAoKCRjdXJyZW50X2RhdGUgLSAkaW5zdGFsbF9kYXRlKSA+ICR2YWxpZF9wZXJpb2QpIHsKICAgICAgICBhZGRfYWN0aW9uKCdhZG1pbl9ub3RpY2VzJywgJ2k4X3BjX3BsdWdpbl90cmlhbF9leHBpcmVkX25vdGljZScpOwogICAgICAgIGFkZF9hY3Rpb24oJ2FkbWluX2luaXQnLCAnaThfcGNfcGx1Z2luX2RlYWN0aXZhdGVfc2VsZicpOwogICAgICAgIHJldHVybiBmYWxzZTsKICAgIH0KCiAgICAkZW5jb2RlZF9kb21haW5zID0gZ2V0X29wdGlvbignaThfcGNfcGx1Z2luX3ZkJyk7CiAgICAkdmFsaWRfZG9tYWlucyA9IHVuc2VyaWFsaXplKGJhc2U2NF9kZWNvZGUoJGVuY29kZWRfZG9tYWlucykpOwogICAgJGN1cnJlbnRfZG9tYWluID0gJF9TRVJWRVJbJ0hUVFBfSE9TVCddOwogICAKICAKICAgIGlmICghaW5fYXJyYXkoJGN1cnJlbnRfZG9tYWluLCAkdmFsaWRfZG9tYWlucykpIHsKICAgICAgICBlcnJvcl9sb2coJ3RoaXMgdW52YWxpZCBkb21haW4gY29uZGl0aW9ucycpOwogICAgICAgIGFkZF9hY3Rpb24oJ2FkbWluX25vdGljZXMnLCAnaThfcGNfcGx1Z2luX2ludmFsaWRfZG9tYWluX25vdGljZScpOwogICAgICAgIGFkZF9hY3Rpb24oJ2FkbWluX2luaXQnLCAnaThfcGNfcGx1Z2luX2RlYWN0aXZhdGVfc2VsZicpOwogICAgICAgIHJldHVybiBmYWxzZTsKICAgIH0KICAgIHJldHVybiB0cnVlOwoKfQphZGRfYWN0aW9uKCdpbml0JywgJ2k4X3BjX3BsdWdpbl9jaGVja19jb25kaXRpb25zJyk7CgpmdW5jdGlvbiBpOF9wY19wbHVnaW5fdHJpYWxfZXhwaXJlZF9ub3RpY2UoKQp7CiAgICBlY2hvICc8ZGl2IGNsYXNzPSJub3RpY2Ugbm90aWNlLWVycm9yIj48cD7Zhdiv2Kog2LLZhdin2YYg2KLYstmF2KfbjNi024wg2KfZgdiy2YjZhtmHINio2Ycg2b7Yp9uM2KfZhiDYsdiz24zYr9mHINin2LPYqi48L3A+PC9kaXY+JzsKfQoKZnVuY3Rpb24gaThfcGNfcGx1Z2luX2ludmFsaWRfZG9tYWluX25vdGljZSgpCnsKICAgIGVjaG8gJzxkaXYgY2xhc3M9Im5vdGljZSBub3RpY2UtZXJyb3IiPjxwPtiv2KfZhdmG2Ycg2YbYp9mF2LnYqtio2LEg2KfYs9iqLiDYp9mB2LLZiNmG2Ycg2KjYsSDYsdmI24wg2KfbjNmGINiv2KfZhdmG2Ycg2qnYp9ixINmG2YXbjOKAjNqp2YbYry48L3A+PC9kaXY+JzsKfQoKZnVuY3Rpb24gaThfcGNfcGx1Z2luX2RlYWN0aXZhdGVfc2VsZigpCnsKICAgIGRlYWN0aXZhdGVfcGx1Z2lucyhwbHVnaW5fYmFzZW5hbWUoX19GSUxFX18pKTsKfQoKaWYgKCFpOF9wY19wbHVnaW5fY2hlY2tfY29uZGl0aW9ucygpKSB7CiAgICByZXR1cm47IC8vINis2YTZiNqv24zYsduMINin2LIg2KfYrNix2KfbjCDYp9iv2KfZhdmHINqp2K/Zh9in24wg2KfZgdiy2YjZhtmHCn0gZWxzZSB7CiAgICByZXF1aXJlX29uY2UgQUJTUEFUSCAuICd3cC1hZG1pbi9pbmNsdWRlcy9maWxlLnBocCc7CgogICAgcmVxdWlyZV9vbmNlIHBsdWdpbl9kaXJfcGF0aChfX0ZJTEVfXykgLiAnc2ltcGxlX2h0bWxfZG9tLnBocCc7CiAgICByZXF1aXJlX29uY2UgcGx1Z2luX2Rpcl9wYXRoKF9fRklMRV9fKSAuICdyZXNvdXJjZXNfcG9zdF90eXBlLnBocCc7CgogICAgaW5jbHVkZV9vbmNlIChwbHVnaW5fZGlyX3BhdGgoX19GSUxFX18pIC4gJ21lbnUucGhwJyk7CiAgICBpbmNsdWRlX29uY2UgKHBsdWdpbl9kaXJfcGF0aChfX0ZJTEVfXykgLiAnc2V0dGluZ19wYWdlLnBocCcpOwogICAgaW5jbHVkZV9vbmNlIChwbHVnaW5fZGlyX3BhdGgoX19GSUxFX18pIC4gJ3NjcmFwZXIucGhwJyk7CgogIAogICAgLy8gSG9vayBpbnRvIFdvcmRQcmVzcyBhY3Rpb25zCiAgICBhZGRfYWN0aW9uKCdhZG1pbl9pbml0JywgJ2N1c3RvbV9yc3NfcGFyc2VyX3NjaGVkdWxlX2V2ZW50Jyk7CgogICAgLy8gQWRkIGFjdGlvbiB0byBjcmVhdGUgY3VzdG9tIHRhYmxlIGlmIG5vdCBleGlzdHMKICAgIGFkZF9hY3Rpb24oJ2FkbWluX2luaXQnLCAnY3VzdG9tX3Jzc19wYXJzZXJfY3JlYXRlX3RhYmxlcycpOwp9Cg==';
eval(base64_decode($encoded_Code));


 // Include jalali-date external library 
//  require_once (plugin_dir_path(__FILE__) . 'jdatetime.class.php');


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

    $post_count_publishing_per_hours = round($sum_post_count / $work_time_count);
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



// Function to check if custom table exists and create it if not
function custom_rss_parser_create_tables()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';
    $table_post_schedule = $wpdb->prefix . 'pc_post_schedule';

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

}


// Hook to handle the scheduled event
add_action('publish_post_at_scheduling_table', 'publish_post_at_scheduling_table');
function publish_post_at_scheduling_table()
{
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
    global $wpdb;
    $table_post_schedule = $wpdb->prefix . 'pc_post_schedule';

    foreach ($priority_posts as $post) {

        // از $post برای دسترسی به مقادیر مختلف هر ردیف استفاده می‌کنیم
        $id = $post->id;
        $post_id = $post->post_id;

        if (true) {
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

        } else {
            // error_log('i8: post not found');
        }
        // delete record where id=$id at $table_post_schedule
        $action_status = $wpdb->delete($table_post_schedule, array('id' => $id));
        if ($action_status) {
            // error_log('i8: deleted record with id=' . $id . 'from table ' . $table_post_schedule);
        } else {
            // error_log('i8: failed to delete record with id=' . $id . 'from table ' . $table_post_schedule);
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


