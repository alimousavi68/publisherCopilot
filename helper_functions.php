<?php

require_once (__DIR__ . '/../../../wp-load.php');
require_once (__DIR__ . '/../../../wp-admin/includes/media.php');
require_once (__DIR__ . '/../../../wp-admin/includes/image.php');
require_once (__DIR__ . '/../../../wp-admin/includes/file.php');



// Send request to server and get response
function send_license_validation_request()
{
    $response = wp_remote_post(
        COP_REST_API_SERVER_URL,
        array(
            'body' => array(
                'subscription_secret_code' => COP_SUBSCRIPTION_SECRET_CODE
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
            'plan_duration' => $recived_data['plan_duration'],
            'plan_cron_interval' => $recived_data['plan_cron_interval'],
            'plan_max_post_fetch' => $recived_data['plan_max_post_fetch'],
            'resources_data' => $recived_data['resources_data'],
        );
        i8_save_response_license_data($response_data);

    } else {
        // FOR DOING : some doing work for notif to admin for expire lisence and disable plugin 
        error_log('i am client:' . 'License is not valid');
    }
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
        <p>
            <span>🚨</span>
            <span style="font-size:10px;">برای اعمال زمانبدی بعد از انتخاب، پست را ذخیره پیش نویس کنید!</span>
        </p>
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