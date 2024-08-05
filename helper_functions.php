<?php

require_once (__DIR__ . '/../../../wp-load.php');
require_once (__DIR__ . '/../../../wp-admin/includes/media.php');
require_once (__DIR__ . '/../../../wp-admin/includes/image.php');
require_once (__DIR__ . '/../../../wp-admin/includes/file.php');

// Send request to server and get response
function send_license_validation_request($secret_code)
{
    
    $response = wp_remote_post(
        COP_REST_API_SERVER_URL,
        array(
            'body' => array(
                'subscription_secret_code' => $secret_code
            )
        )
    );

    if (is_wp_error($response)) {
        return 'Error in sending request';
    }

    $body = wp_remote_retrieve_body($response);
    $status = wp_remote_retrieve_response_code($response);

    if ($status == 200) {
        $recived_data = json_decode($body, true);

        $response_data = array(
            'plan_name' => $recived_data['plan_name'],
            'subscription_start_date' => $recived_data['subscription_start_date'],
            'subscription_end_date' => $recived_data['subscription_end_date'],
            'plan_duration' => $recived_data['plan_duration'],
            'plan_cron_interval' => $recived_data['plan_cron_interval'],
            'plan_max_post_fetch' => $recived_data['plan_max_post_fetch'],
            'resources_data' => $recived_data['resources_data'],
        );
        i8_save_response_license_data($response_data);

        return true;

    } else {
        error_log('error');
        // FOR DOING : some doing work for notif to admin for expire lisence and disable plugin 
        error_log('i am client:' . 'License is not valid');
        cop_expired_subscription_actions();
        return false;
    }
}

// Save " LINCENCE PLAN " RESPONSE DATA to database
function i8_save_response_license_data($recived_data)
{

    $response_data = array(
        'plan_name' => $recived_data['plan_name'],
        'subscription_start_date' => $recived_data['subscription_start_date'],
        'subscription_end_date' => $recived_data['subscription_end_date'],
        'plan_duration' => $recived_data['plan_duration'],
        'plan_cron_interval' => $recived_data['plan_cron_interval'],
        'plan_max_post_fetch' => $recived_data['plan_max_post_fetch'],
        'resources_data' => $recived_data['resources_data'],
    );
    update_option('i8_plan_name', $response_data['plan_name']);
    update_option('i8_subscription_start_date', $response_data['subscription_start_date']);
    update_option('i8_subscription_end_date', $response_data['subscription_end_date']);
    update_option('i8_plan_duration', $response_data['plan_duration']);
    update_option('i8_plan_cron_interval', $response_data['plan_cron_interval']);
    update_option('i8_plan_max_post_fetch', $response_data['plan_max_post_fetch']);

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

function truncate_resources_details_table(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_resource_details';
    $wpdb->query("TRUNCATE TABLE $table_name");
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

function get_all_source_name(){
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_resource_details';
    $data = $wpdb->get_results("SELECT resource_id,resource_title FROM $table_name");
    return $data;
}

function cop_expired_subscription_actions(){
    delete_option('i8_plan_name');
    delete_option('i8_subscription_start_date');
    delete_option('i8_plan_duration');
    delete_option('i8_plan_cron_interval');
    delete_option('i8_plan_max_post_fetch');
    truncate_resources_details_table();
    remove_all_feed_on_feeds_table();
}

// فراخوانی تابع
// send_license_validation_request();



// Recive Ajax request and manage it
if (isset($_POST['action']) && !empty($_POST['action'])) {
    if ($_POST['action'] == 'delete_item') {

        $item_id = intval($_POST['id']);

        $response = i8_delete_item_at_scheulde_list($item_id);
        echo json_encode($response);
        wp_die();
    }
}


// Delete item from " pc_post_schedule " table at database
function i8_delete_item_at_scheulde_list($id = '', $post_id = '')
{

    global $wpdb;
    $table_post_schedule = $wpdb->prefix . 'pc_post_schedule';
    if ($id) {
        $deleted = $wpdb->delete($table_post_schedule, array('id' => $id));
    } elseif ($post_id) {
        $deleted = $wpdb->delete($table_post_schedule, array('post_id' => $post_id));
    }

    if ($deleted > 0) {
        return array('status' => 'success', 'message' => 'تعداد ' . $deleted . ' ردیف با موفقیت حذف شد.');
    } else {
        return array('status' => 'error', 'message' => 'هیچ ردیفی حذف نشد. امکان دارد شناسه مورد نظر وجود نداشته باشد.');
    }

}

// add custom meta box to post publish
add_action('add_meta_boxes', 'render_custom_meta_box');
function render_custom_meta_box()
{
    add_meta_box(
        'cop-manager-metabox',
        __('داشبورد دستیار'),
        'render_cop_manager_meta_box',
        'post',
        'side',
        'high'
    );
}


function render_cop_manager_meta_box()
{
    global $post;
    $old_priority = cop_get_post_priority($post->ID);

    ?>
    <div class="">
        <label for="cop_post_priority">
            ⏰ زمانبندی هوشمند:
            <select name="cop_post_priority" id="cop_post_priority" class="widefat">
                <option value=""> ⏤ بدون زمانبندی </option>
                <option value="now" <?php echo ($old_priority == 'now') ? 'selected' : ''; ?>>⏳ انتشار با تاخیر</option>
                <option value="high" <?php echo ($old_priority == 'high') ? 'selected' : ''; ?>>🔴 الویت بالا</option>
                <option value="medium" <?php echo ($old_priority == 'medium') ? 'selected' : ''; ?>>🟠 اولویت متوسط</option>
                <option value="low" <?php echo ($old_priority == 'low') ? 'selected' : ''; ?>>🟢 اولویت پایین</option>
            </select>
        </label>
    </div>
    <?php
}

add_action('save_post', 'cop_set_post_priority_in_manager_meta_box', 10, 3);

function cop_set_post_priority_in_manager_meta_box($post_id, $post, $update)
{
    static $updating_post = false;

    if ($updating_post) {
        return;
    }


    // اگر در حال اجرای یک بازنویسی خودکار هستیم، اجرا نکنید
    if (wp_is_post_autosave($post_id)) {
        return;
    }
    // اگر در حال اجرای یک بازبینی هستیم، اجرا نکنید
    if (wp_is_post_revision($post_id)) {
        return;
    }
    if ($post->post_type != 'post') {
        return; // اگر پست موجود نیست، کد بیشتر اجرا نشود
    }


    $priority_value = isset($_POST['cop_post_priority']) ? $_POST['cop_post_priority'] : false;
    $old_priority = cop_get_post_priority($post->ID);

    // اطمینان حاصل کنید که وضعیت پست تغییر کرده است تا از لوپ بینهایت جلوگیری شود
    if ($old_priority == $priority_value) {
        return; // اگر وضعیت تغییر نکرده باشد، فرآیند را متوقف کنید
    }


    if ($priority_value != 'now' and $priority_value != false) {
        $updating_post = true;

        if ($old_priority == null) {
            // add this post to cop schueduling list 
            add_post_to_post_schedule_table($post->ID, $priority_value);
        } else {
            cop_update_post_priority($post_id, $priority_value);
        }

        // change post status to draft if priority is not "now"
        $post_data = array(
            'ID' => $post->ID,
            'post_status' => 'draft',
        );
        wp_update_post($post_data);
        $updating_post = false;

    } elseif ($priority_value == 'now') {

        $updating_post = true;

        if ($old_priority != null and $old_priority != 'now') {
            i8_delete_item_at_scheulde_list(null,$post_id);
        }

        date_default_timezone_set('Asia/Tehran');
        $random_interval = rand(400, 900);
        $publish_time = time() + $random_interval;

        // Prepare data for creating a WordPress post
        $post_data = array(
            'ID' => $post->ID,
            'post_status' => 'future',
            'post_date' => date('Y-m-d H:i:s', $publish_time), // استفاده از زمان تصادفی برای post_date
            'post_date_gmt' => gmdate('Y-m-d H:i:s', $publish_time), // استفاده از زمان تصادفی برای post_date_gmt
        );

        wp_update_post($post_data);
        $updating_post = false;

    }
}


// add post to post schedule table 
function add_post_to_post_schedule_table($post_id, $post_priority)
{
    global $wpdb;
    $pc_post_schedule_table_name = $wpdb->prefix . 'pc_post_schedule';
    $wpdb->insert(
        $pc_post_schedule_table_name,
        array(
            'post_id' => $post_id,
            'publish_priority' => $post_priority
        )
    );
}

// check post exist in post schedul table and retrive post priority
function cop_get_post_priority($post_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'pc_post_schedule';

    $query = $wpdb->prepare("SELECT publish_priority FROM $table_name WHERE post_id = %d LIMIT 1", $post_id);

    $post_priority = $wpdb->get_var($query);

    if ($post_priority !== null) {
        return $post_priority;
    } else {
        return null;
    }
}

// update Exist post priority in pc post scheudle table
function cop_update_post_priority($post_id, $new_priority)
{
    global $wpdb;
    $pc_post_schedule_table_name = $wpdb->prefix . 'pc_post_schedule';
    $wpdb->update(
        $pc_post_schedule_table_name,
        array(
            'publish_priority' => $new_priority
        ),
        array('post_id' => $post_id),
        array('%s'), // فرمت داده‌های جدید
        array('%d')  // فرمت شرایط
    );
}



// remove all feed on feed table [custom_rss_items]
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