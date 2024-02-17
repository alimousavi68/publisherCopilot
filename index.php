<?php
/*
Plugin Name: Custom RSS Parser
Description: Parse and store RSS feed data into a custom table.
Version: 1.0
Author: Your Name
*/




// Hook into WordPress actions
add_action('admin_init', 'custom_rss_parser_schedule_event');

// Add action to create custom table if not exists
add_action('admin_init', 'custom_rss_parser_create_table');

// Schedule event to run every 5 minutes
function custom_rss_parser_schedule_event()
{

    // Schedule the event to run every 5 minutes
    // wp_schedule_event(time(), '5minutes', 'custom_rss_parser_event');

    if (!wp_next_scheduled('custom_rss_parser_event')) {
        wp_schedule_event(time(), '5minutes', 'custom_rss_parser_event');
    }
}

// Function to check if custom table exists and create it if not
function custom_rss_parser_create_table()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title text NOT NULL,
            pub_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            guid text NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}

// Hook to handle the scheduled event
add_action('custom_rss_parser_event', 'custom_rss_parser_run');

// Function to parse and store RSS feed data
function custom_rss_parser_run()
{
    // Replace 'YOUR_RSS_FEED_URL' with the actual RSS feed URL
    $rss_feed_url = 'https://www.rokna.net/feeds/';

    // Fetch the RSS feed
    $rss_feed = fetch_rss_feed($rss_feed_url);

    if (!$rss_feed) {
        return;
    }

    // Parse and store RSS feed data
    foreach ($rss_feed->channel->item as $item) {
        $title = $item->title;
        $pub_date = date('Y-m-d H:i:s', strtotime($item->pubDate));
        $guid = 'https://www.rokna.net' . $item->guid . '';

        // Check if the item already exists in the database
        if (!custom_rss_parser_item_exists($guid)) {
            // Insert the new item into the custom table
            custom_rss_parser_insert_item($title, $pub_date, $guid);
        }


    }



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
function custom_rss_parser_insert_item($title, $pub_date, $guid)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';

    $wpdb->insert(
        $table_name,
        array(
            'title' => '' . $title,
            'pub_date' => $pub_date,
            'guid' => '' . $guid,
        )
    );

}

require_once plugin_dir_path(__FILE__) . 'simple_html_dom.php';

include_once(plugin_dir_path(__FILE__) . 'menu.php');
include_once(plugin_dir_path(__FILE__) . 'scraper.php');
// error_log(plugin_dir_path(__FILE__). 'admin/menu.php');
