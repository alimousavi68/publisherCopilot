<?php 

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