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
function i8_delete_item_at_scheulde_list($id)
{

    global $wpdb;
    $table_post_schedule = $wpdb->prefix . 'pc_post_schedule';

    $deleted = $wpdb->delete($table_post_schedule, array('id' => $id));

    if ($deleted > 0) {
        return array('status' => 'success', 'message' => 'تعداد ' . $deleted . ' ردیف با موفقیت حذف شد.');
    } else {
        return array('status' => 'error', 'message' => 'هیچ ردیفی حذف نشد. امکان دارد شناسه مورد نظر وجود نداشته باشد.');
    }

}