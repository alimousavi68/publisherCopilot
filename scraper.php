<?php

// // Include necessary WordPress files
// // require_once(ABSPATH . '/wp-load.php');

// $document_root = $_SERVER['DOCUMENT_ROOT'] . '/rasadi';
// error_log('root:' . $document_root);

// if (file_exists($document_root . '/wp-load.php')) {
//     require_once($document_root . '/wp-load.php');
// } else {
//     error_log('wp-load.php not found!');
//     exit;
// }

// // Check if the request is an Ajax request
// if (defined('DOING_AJAX') && DOING_AJAX) {

//     // error_log('me fired/ url: ' . $_POST['guid']);

//     // Check if the required data is received
//     if (isset($_POST['guid'])) {
//         $guid = sanitize_text_field($_POST['guid']);
//         // Call the function
//         scrape_and_publish_post($guid);
//     }
// }

// // Function to scrape data from a given URL and create a new WordPress post
// function scrape_and_publish_post($guid)
// {
//     $url = $guid;
//     // $url = "https://www.farsnews.ir/news/14021011000961/%D8%A7%D9%86%D8%AA%D8%B4%D8%A7%D8%B1-%D9%86%D8%AE%D8%B3%D8%AA%DB%8C%D9%86%E2%80%8C%D8%A8%D8%A7%D8%B1%7C-%D8%AE%D8%A7%D8%B7%D8%B1%D9%87-%D8%B1%D9%87%D8%A8%D8%B1-%D8%A7%D9%86%D9%82%D9%84%D8%A7%D8%A8-%D8%A7%D8%B2-%D9%86%D9%82%D9%84-%D9%82%D9%88%D9%84-%D8%AD%D8%A7%D8%AC-%D9%82%D8%A7%D8%B3%D9%85-%D8%AF%D8%B1%D8%A8%D8%A7%D8%B1%D9%87";

//     error_log('url in function: ' . $url);

//     // Load the HTML from the provided URL
//     $html = file_get_html($url);
//     // error_log('url-content: ' . $html);




//     // Check if HTML is successfully loaded
//     if ($html) {
//         // Find and extract the required elements

//         // انتخاب المان h1 با کلاس "title" و مشخصه itemprop="headline"
//         $title_element = $html->find('h1.title', 0);

//         // بررسی وجود المان قبل از استفاده از تابع find()
//         if ($title_element) {
//             // دریافت متن موجود در المان
//             $title = $title_element->plaintext;
//         } else {
//             // در صورت عدم وجود المان، مقدار پیشفرض یا اقدام مناسب دیگر
//             $title = 'عنوان پیدا نشد';
//         }


//         $excerpt = $html->find('p.lead', 0);
//         $excerpt = $excerpt->plaintext;


//         $content = $html->find('div#CK_editor', 0);
//         $content = $content->innertext;

//         $thumbnail_url = $html->find('.contain-img img', 0)->src;



//         // Check if all required elements are found
//         if ($title && $excerpt && $content && $thumbnail_url) {

//             // Prepare data for creating a WordPress post
//             $post_data = array(
//                 'post_title' => $title,
//                 'post_content' => $content,
//                 'post_excerpt' => $excerpt,
//                 'post_status' => 'publish',
//                 'post_type' => 'post',
//             );


//             // Insert the post into the WordPress database
//             // درست کردن پست در وردپرس
//             try {
//                 error_log('dolly1 ');
//                 $post_id = wp_insert_post($post_data);
//                 ob_flush(); // تخلیه خروجی
//             } catch (Exception $e) {
//                 echo '<script>console.log("Failed to insert the post. Error: ' . $e->getMessage() . '");</script>';
//                 error_log('Failed to insert the post. Error: ' . $e->getMessage());
//                 ob_flush(); // تخلیه خروجی
//             }

//             // Upload and set the featured image
//             if ($post_id && function_exists('media_sideload_image')) {
//                 $attachment_id = media_sideload_image($thumbnail_url, $post_id, 'thumbnail');
//                 if (!is_wp_error($attachment_id)) {
//                     set_post_thumbnail($post_id, $attachment_id);
//                     echo '<script>console.log("ok. its set");</script>';
//                     error_log('ok. its set');

//                 } elseif (is_wp_error($attachment_id)) {
//                     echo '<script>console.log("Failed to upload the featured image. Error: ' . $attachment_id->get_error_message() . '");</script>';
//                     error_log('Failed to upload the featured image. Error: ' . $attachment_id->get_error_message());
//                 }
//             } elseif (!function_exists('media_sideload_image')) {
//                 echo '<script>console.log("media_sideload_image() function is not available.");</script>';
//                 error_log('media_sideload_image() function is not available.');
//             }

//             // Output success or failure message
//             if ($post_id) {
//                 echo '<script>console.log("Post created successfully with ID: " + ' . $post_id . ');</script>';
//             } else {
//                 echo '<script>console.log("Failed to create post. ");</script>';
//                 error_log('Failed to create post.');
//             }
//         } else {
//             echo '<script>console.log("Required elements not found on the page. ");</script>';

//             error_log('Required elements not found on the page.');
//         }
//     } else {
//         echo '<script>console.log("Failed to load HTML from the URL.");</script>';
//         error_log('Failed to load HTML from the URL.');
//     }
// }






