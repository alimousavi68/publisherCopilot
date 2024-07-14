<?php

require_once (__DIR__ . '/../../../wp-load.php');
require_once (__DIR__ . '/../../../wp-admin/includes/media.php');
require_once (__DIR__ . '/../../../wp-admin/includes/image.php');
require_once (__DIR__ . '/../../../wp-admin/includes/file.php');



if (isset($_POST['action']) && !empty($_POST['action'])) {
    if ($_POST['action'] == 'delete_item') {

        $item_id = intval($_POST['id']);

        $response = i8_delete_item_at_scheulde_list($item_id);
        echo json_encode($response);
        wp_die();          
    }
}

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