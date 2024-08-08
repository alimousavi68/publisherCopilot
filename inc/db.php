<?php 

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
