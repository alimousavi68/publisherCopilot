<?php

// Include necessary WordPress files


// require_once ABSPATH . 'wp-admin/includes/file.php';
// تعیین مسیر نسبی فایل wp-load.php
// $wp_load_path = get_home_path() . 'wp-load.php';

// // اگر فایل wp-load.php در مسیر محاسبه شده وجود داشته باشد، آن را لود کنید
// if (file_exists($wp_load_path)) {
//     require_once ($wp_load_path);
// } else {
//     error_log('wp-load.php not found!');
//     exit;
// }

// Check if the request is an Ajax request
// if (defined('DOING_AJAX') && DOING_AJAX) {
//     // Check if the required data is received
//     if (isset($_POST['guid'])) {
//         $guid = sanitize_text_field($_POST['guid']);
//         // Call the function
//         scrape_and_publish_post($guid);
//     }
// }

// require_once (__DIR__ . '/../../../wp-load.php');
// require_once ABSPATH . 'wp-admin/includes/file.php';
// $wp_load_path = get_home_path() . 'wp-load.php';
// // اگر فایل wp-load.php در مسیر محاسبه شده وجود داشته باشد، آن را لود کنید
// if (file_exists($wp_load_path)) {
//     require_once ($wp_load_path);
// } else {
//     error_log('wp-load.php not found!');
//     exit;
// }
require_once (__DIR__ . '/../../../wp-load.php');
require_once (__DIR__ . '/../../../wp-admin/includes/media.php');
require_once (__DIR__ . '/../../../wp-admin/includes/image.php');
require_once (__DIR__ . '/../../../wp-admin/includes/file.php');



if (isset($_POST['action']) && !empty($_POST['action'])) {
    if ($_POST['action'] == 'publish_scraper') {
        $guid = $_POST['post_Guid'];
        $resource_id = $_POST['resource_id'];

        // give priority string value at $_POST['resource_id'] 
        $publish_priority = isset($_POST['publish_priority']) ? $_POST['publish_priority'] : 'now';


        // error_log('publish_priority here:' . $publish_priority);

        $response = scrape_and_publish_post($guid, $resource_id, $publish_priority);
        echo json_encode($response);
    }
}
function complete_url($url, $base_url)
{
    // چک می‌کند که آیا URL شامل پروتکل است یا خیر (http:// یا https://)
    if (parse_url($url, PHP_URL_SCHEME) === null) {
        // اگر URL با '/' شروع شود، به ابتدای آن base_url را اضافه می‌کند
        if ($url[0] === '/') {
            return rtrim($base_url, '/') . $url;
        } else {
            return rtrim($base_url, '/') . '/' . ltrim($url, '/');
        }
    }
    // اگر URL شامل پروتکل باشد، همان URL را برمی‌گرداند
    return $url;
}

add_action('admin_post_scrape_and_publish_post', 'scrape_and_publish_post');
// Function to scrape data from a given URL and create a new WordPress post
function scrape_and_publish_post($guid, $resource_id, $publish_priority)
{

    // دریافت مقدار Nonce از فرم
    // $nonce = $_POST['my_nonce_field'];

    // // بررسی صحت Nonce
    // if (!wp_verify_nonce($nonce, 'scrape_and_publish_post_nonce')) {
    //     // در صورتی که Nonce معتبر نباشد، عملیات را متوقف کنید و پیام خطا نمایش دهید
    //     echo 'دسترسی غیر مجاز!';
    //     exit;
    // }

    // دریافت مقادیر از فرم
    $title_selector = get_post_meta($resource_id, 'title_selector', true);
    $img_selector = get_post_meta($resource_id, 'img_selector', true);
    $lead_selector = get_post_meta($resource_id, 'lead_selector', true);
    $body_selector = get_post_meta($resource_id, 'body_selector', true);
    $bup_date_selector = get_post_meta($resource_id, 'bup_date_selector', true);
    $category_selector = get_post_meta($resource_id, 'category_selector', true);
    $tags_selector = get_post_meta($resource_id, 'tags_selector', true);
    $escape_elements = get_post_meta($resource_id, 'escape_elements', true);
    $source_root_link = get_post_meta($resource_id, 'source_root_link', true);
    $source_feed_link = get_post_meta($resource_id, 'source_feed_link', true);
    $need_to_merge_guid_link = get_post_meta($resource_id, 'need_to_merge_guid_link', true);


    $guid = $guid . '';
    $url = $guid;
    $encoded_url = preg_replace_callback('/[^\x20-\x7f]/', function ($matches) {
        return rawurlencode($matches[0]); }, $url);

    error_log($encoded_url);

    // Load the HTML from the provided URL
    $html = file_get_html($encoded_url);

    // error_log($html);

    // Check if HTML is successfully loaded
    if ($html) {
        // Find and extract the required elements

        // انتخاب المان h1 با کلاس "title" و مشخصه itemprop="headline"
        $title_element = $html->find($title_selector, 0);

        // بررسی وجود المان قبل از استفاده از تابع find()
        if ($title_element) {
            // دریافت متن موجود در المان
            $title = $title_element->plaintext;
        } else {
            // در صورت عدم وجود المان، مقدار پیشفرض یا اقدام مناسب دیگر
            $title = 'عنوان پیدا نشد';
        }

        if ($html->find($lead_selector, 0) != null) {
            $excerpt = $html->find($lead_selector, 0);
            $excerpt = trim($excerpt->plaintext);
        } else {
            $excerpt = '';
        }


        $content = $html->find($body_selector, 0);
        // error_log($content);
        $content = clear_not_allowed_tags($content->innertext, $source_root_link);

        error_log('img selector :' . $img_selector);
        $thumbnail_url = $html->find($img_selector, 0)->src;


        $post_status = 'draft';
        if ($publish_priority == 'now') {
            $post_status = 'publish';
        }
        // error_log('post_status here:' . $post_status);

        date_default_timezone_set('Asia/Tehran');

        // Check if all required elements are found
        if ($title && $excerpt && $content && $thumbnail_url) {
            $random_interval = rand(300, 600);
            $publish_time = time() + $random_interval;

            // Prepare data for creating a WordPress post
            $post_data = array(
                'post_title' => $title,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
                'post_status' => $post_status,
                'post_date' => date('Y-m-d H:i:s', $publish_time) // زمان انتشار
            );

            // درست کردن پست در وردپرس
            try {
                $post_id = wp_insert_post($post_data);
                ob_flush(); // تخلیه خروجی
            } catch (Exception $e) {
                return (array('status' => false, 'message' => 'Failed to insert the post. Error: ' . $e->getMessage()));
                ob_flush(); // تخلیه خروجی
            }

            // Upload and set the featured image
            if ($post_id && function_exists('media_sideload_image')) {
                $thumbnail_url = complete_url($thumbnail_url, $source_root_link);


                $attachment_id = media_sideload_image($thumbnail_url, $post_id, 'thumbnail', 'id');

                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);

                } elseif (is_wp_error($attachment_id)) {
                    return (array('status' => false, 'message' => 'Failed to upload the featured image. Error: ' . $attachment_id->get_error_message()));
                }
            } elseif (!function_exists('media_sideload_image')) {
                // return (array('status' => false, 'message' => 'media_sideload_image() function is not available.'));
                // error_log('media_sideload_image() function is not available.');
            }

            // Output success or failure message
            if ($post_id) {
                // echo '<script>window.open("' . admin_url('post.php?action=edit&post=' . $post_id) . '", "_blank", "noopener,noreferrer");</script>';
                // wp_safe_redirect(add_query_arg('success', 'true', wp_get_referer()));
                // exit;

                // add to wp_pc_post_schedule table in wordpress database a new record with $post_id and$publish_priority values

                if ($post_status != 'publish' || $publish_priority != 'now') {
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'pc_post_schedule';
                    $wpdb->insert($table_name, array('post_id' => $post_id, 'publish_priority' => $publish_priority));
                }

                return (array('status' => true, 'message' => 'پست منتشر شد'));
            } else {
                // error_log('Failed to create post.');
                // wp_safe_redirect(add_query_arg('success', 'false', wp_get_referer()));
                // exit;
                return (array('status' => false, 'message' => 'پست منتشر نشد'));
            }


        } else {
            return (array('status' => false, 'message' => 'Required elements not found on the page.'));
        }
    } else {
        return (array('status' => false, 'message' => 'Failed to load HTML from the URL.'));
    }
}


function clear_not_allowed_tags($html, $base_url)
{
    // ایجاد یک شیء DOMDocument
    $dom = new DOMDocument();

    // بارگیری HTML بدون عنوان
    libxml_use_internal_errors(true); // غیرفعال کردن پیام‌های خطا
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors(); // پاک کردن خطاها

    // حذف تگ‌های <a> و <h1>
    $tagsToRemove = array('a', 'h1', 'strong');
    foreach ($tagsToRemove as $tag) {
        $elementsToRemove = $dom->getElementsByTagName($tag);
        $elements = [];
        foreach ($elementsToRemove as $element) {
            $elements[] = $element; // Save elements to array for safe removal
        }
        foreach ($elements as $element) {
            $text = $element->nodeValue;
            $textNode = $dom->createTextNode($text);
            $element->parentNode->replaceChild($textNode, $element);
        }
    }

    // حذف ویژگی‌های id، class و style از تمام تگ‌ها
    $allElements = $dom->getElementsByTagName('*');
    foreach ($allElements as $element) {
        $element->removeAttribute('id');
        $element->removeAttribute('class');
        $element->removeAttribute('style');
        $element->removeAttribute('href');
        $element->removeAttribute('title');
    }

    // بررسی و اصلاح src تگ‌های img
    $imgTags = $dom->getElementsByTagName('img');
    foreach ($imgTags as $img) {
        $src = $img->getAttribute('src');
        $complete_src = complete_url($src, $base_url);
        $img->setAttribute('src', $complete_src);
    }

    // دریافت HTML نهایی
    $filteredHtml = $dom->saveHTML();

    // حذف <?xml encoding="UTF-8">
    $filteredHtml = substr($filteredHtml, strlen('<?xml encoding="UTF-8">'));

    // بازگرداندن HTML نهایی
    return $filteredHtml;
}