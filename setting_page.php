<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
$wp_load_path = get_home_path() . 'wp-load.php';

if (file_exists($wp_load_path)) {
    require_once ($wp_load_path);
} else {
    error_log('wp-load.php not found!');
    exit;
}

// ایجاد صفحه تنظیمات
function publisher_copoilot_setting_page_callback()
{
    ?>
    <div class="wrap">
        <div>
            <p>
                <span>current date:</span>
                <span><?php
                    echo current_time('Y/m/d - H:i:s');
                ?></span>
            </p>
            <p>
                <span>start active:</span>
                <span><?php
                $encoded_date = get_option('i8_pc_plugin_sd');
                $install_date = intval(base64_decode($encoded_date));
                echo (date('Y/m/d - H:i:s', $install_date));
                ?></span>
            </p>

            <p>
                <span>
                    expire date:
                </span>
                <span>
                    <?php
                    $current_date = current_time('timestamp');
                    $valid_period = 30 * DAY_IN_SECONDS; // ۱۰ روز
                    echo (date('Y/m/d - H:i:s', $valid_period + $install_date));
                    ?>
                </span>
            </p>
        </div>
        <form method="post" action="options.php">
            <?php
            // ایجاد فیلدهای تنظیمات
            settings_fields('publisher_copoilot_settings_group');
            do_settings_sections('publisher_copoilot_setting');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// تابع برای اضافه کردن صفحه تنظیمات
function i8_add_seeting_page_menu()
{
    add_submenu_page(
        'publisher_copoilot',
        'تنظیمات دستیار',
        'تنظیمات',
        'manage_options',
        'publisher_copoilot_setting',
        'publisher_copoilot_setting_page_callback'
    );

    // ثبت فیلدهای تنظیمات
    add_action('admin_init', 'publisher_copoilot_register_settings');
}

// ثبت فیلدهای تنظیمات
function publisher_copoilot_register_settings()
{
    register_setting('publisher_copoilot_settings_group', 'start_cron_time');
    register_setting('publisher_copoilot_settings_group', 'end_cron_time');
    register_setting('publisher_copoilot_settings_group', 'news_interval_start');
    register_setting('publisher_copoilot_settings_group', 'news_interval_end');
    register_setting('publisher_copoilot_settings_group', 'news_interval_end');

    register_setting('publisher_copoilot_settings_group', 'news_interval_end');

    add_settings_section(
        'publisher_copoilot_settings_section',
        'تنظیمات دستیار',
        'publisher_copoilot_settings_section_callback',
        'publisher_copoilot_setting'
    );

    add_settings_field(
        'start_cron_time',
        'ساعت شروع کار کرون جاب',
        'start_cron_time_callback',
        'publisher_copoilot_setting',
        'publisher_copoilot_settings_section'
    );

    add_settings_field(
        'end_cron_time',
        'ساعت پایان کار کرون جاب',
        'end_cron_time_callback',
        'publisher_copoilot_setting',
        'publisher_copoilot_settings_section'
    );

    add_settings_field(
        'news_interval',
        'بازه عددی تعداد اخبار روزانه (بازه شروع و پایان)',
        'news_interval_callback',
        'publisher_copoilot_setting',
        'publisher_copoilot_settings_section'
    );
}

// تابع بازگشتی برای نمایش بخش تنظیمات
function publisher_copoilot_settings_section_callback()
{
    // echo '<p>لطفا تنظیمات مورد نیاز را انجام دهید.</p>';
}

// توابع بازگشتی برای نمایش فیلدها
function start_cron_time_callback()
{
    $start_time = get_option('start_cron_time');
    echo '<input type="time" name="start_cron_time" value="' . esc_attr($start_time) . '" placeholder="07:00:00" />';
}

function end_cron_time_callback()
{
    $end_time = get_option('end_cron_time');
    echo '<input type="time" name="end_cron_time" value="' . esc_attr($end_time) . '"  placeholder="22:00:00" />';
}

function news_interval_callback()
{
    $news_interval_start = get_option('news_interval_start');
    $news_interval_end = get_option('news_interval_end');
    echo '<input type="number" name="news_interval_start" value="' . esc_attr($news_interval_start) . '" /> - <input type="number" name="news_interval_end" value="' . esc_attr($news_interval_end) . '" />';
}

// فراخوانی تابع افزودن صفحه تنظیمات
add_action('admin_menu', 'i8_add_seeting_page_menu');