<?php 
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
    


    // تعریف زمان شروع و پایان کار ربات 
    $start_time = get_option('start_cron_time') ? get_option('start_cron_time') : '08:30';
    $start_time_res = strtotime($start_time);
    $end_time = get_option('end_cron_time') ? get_option('end_cron_time') : '22:02';
    $end_time_res = strtotime($end_time);

    // تعیین زمان بین هر ارسال پست روی سایت
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
    //تعریف کرون ۲۴ ساعته
    $schedules['i8_daily_cron'] = array(
        'interval' => (60 * 60) * 24,
        'display' => __('این کرون هر ۲۴ ساعت اجرا میشود')
    );
    // تعریف کرون ۵ دقیقه ای
    $schedules['5minutes'] = array(
        'interval' => (5 * 60),
        'display' => __('این کرون هر ۵دقیقه اجرا میشود')
    );

    // تعریف کرون متغییر برای انتشار پست روی سایت 
    $post_interval_publishing = get_option('post_interval_publishing') + rand(500, 1500);
    $schedules['i8_pc_post_publisher_cron'] = array(
        'interval' => ($post_interval_publishing),
        'display' => __('این کرون هر چند دقیقه پستی را از جدول زمانبدی افزونه دستیار در سایت منتشر میکند')
    );
    return $schedules;
}






add_action('remove_all_feed_on_feeds_table', 'remove_all_feed_on_feeds_table');

