<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
$wp_load_path = get_home_path() . 'wp-load.php';

if (file_exists($wp_load_path)) {
    require_once ($wp_load_path);
} else {
    // error_log('wp-load.php not found!');
    exit;
}




// ایجاد صفحه تنظیمات
function publisher_copoilot_setting_page_callback()
{
    $old_secret_code = get_option('i8_secret_code');
    $secret_code = $_POST['cop_secret_code'];
    $response = false;
    if ($secret_code) {
        
        update_option('i8_secret_code', $secret_code);
        $old_secret_code = get_option('i8_secret_code');
        $response = send_license_validation_request($secret_code);

    } elseif (isset($old_secret_code)) {

        $response = send_license_validation_request($old_secret_code);

    } 
    ?>
    <div class="wrap">
        <div class="license_section">
            <form action="" method="post">
                <label for="cop_secret_code">
                    <span>کد مخفی: </span>
                    <input type="text" value="<?php echo $old_secret_code; ?>" name="cop_secret_code"
                        style="direction:ltr;text-align:left;">
                    <button type="submit" name="cop_send_request_to_server">به روز رسانی وضعیت</button>
                    <?php
                    if ($response == true) {
                        print_r('<p style="color:green;"> لایسنس شما معتبر است </p>');
                    } else {
                        print_r('<p style="color:red;"> کد شما معتبر نیست  </p>');
                    }
                    ?>
                </label>

                <?php
                if ($response):
                    $i8_plan_name = (get_option('i8_plan_name')) ? get_option('i8_plan_name') : '-';
                    $i8_subscription_start_date = (get_option('i8_subscription_start_date')) ? get_option('i8_subscription_start_date') : '-';
                    $i8_subscription_end_date = (get_option('i8_subscription_end_date')) ? get_option('i8_subscription_end_date') : '-';
                    $i8_plan_duration = (get_option('i8_plan_duration')) ? get_option('i8_plan_duration') : '-';
                    $i8_plan_cron_interval = (get_option('i8_plan_cron_interval')) ? get_option('i8_plan_cron_interval') : '-';
                    $i8_plan_max_post_fetch = (get_option('i8_plan_max_post_fetch')) ? get_option('i8_plan_max_post_fetch') : '-';
                    ?>
                    <table class="form-table">
                        <tr>
                            <td class="">نوع اشتراک:</td>
                            <td><?php echo $i8_plan_name; ?></td>
                        </tr>
                     
                        <tr>
                            <td>مدت اشتراک(به روز):</td>
                            <td><?php echo $i8_plan_duration; ?></td>
                        </tr>
                        <tr>
                            <td>تاریخ شروع اشتراک:</td>
                            <td><?php echo $i8_subscription_start_date;
                            echo ' [ ';
                            echo @\jDateTime::convertFormatToFormat('Y/m/d - H:i', 'Y-m-d H:i:s', $i8_subscription_start_date);
                            echo ' ]';

                            ?></td>
                        </tr>
                        <tr>
                            <td>تاریخ پایان اشتراک</td>
                            <td><?php echo $i8_subscription_end_date;
                            echo ' [ ';
                            echo @\jDateTime::convertFormatToFormat('Y/m/d - H:i', 'Y-m-d H:i:s', $i8_subscription_end_date);
                            echo ' ]';
                            ?></td>
                        </tr>
                        <tr>
                            <td>فواصل بروزرسانی اتوماتیک فیدها:</td>
                            <td><?php echo $i8_plan_cron_interval; ?></td>
                        </tr>
                        <tr>
                            <td>تعداد مجاز انتشار پست: </td>
                            <td><?php echo $i8_plan_max_post_fetch; ?></td>
                        </tr>

                    </table>
                    <?php
                endif;
                ?>

            </form>

            <form method="post" action="options.php">
                <?php
                // ایجاد فیلدهای تنظیمات
                settings_fields('publisher_copoilot_settings_group');
                ?>


                <?php
                do_settings_sections('publisher_copoilot_setting');
                submit_button();
                ?>
            </form>
        </div>


    </div>
    <script>

    </script>
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