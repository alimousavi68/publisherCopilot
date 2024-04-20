<?php






// Include necessary WordPress files
// require_once(ABSPATH . '/wp-load.php');

$document_root = $_SERVER['DOCUMENT_ROOT'] . '/rasadi';
// error_log('root:' . $document_root);

if (file_exists($document_root . '/wp-load.php')) {
    require_once($document_root . '/wp-load.php');
} else {
    error_log('wp-load.php not found!');
    exit;
}

// Check if the request is an Ajax request
if (defined('DOING_AJAX') && DOING_AJAX) {
    // Check if the required data is received
    if (isset($_POST['guid'])) {
        $guid = sanitize_text_field($_POST['guid']);
        // Call the function
        scrape_and_publish_post($guid);
    }
}

add_action('admin_post_scrape_and_publish_post', 'scrape_and_publish_post');
// Function to scrape data from a given URL and create a new WordPress post
function scrape_and_publish_post()
{
    // دریافت مقدار Nonce از فرم
    $nonce = $_POST['my_nonce_field'];

    // بررسی صحت Nonce
    if (!wp_verify_nonce($nonce, 'scrape_and_publish_post_nonce')) {
        // در صورتی که Nonce معتبر نباشد، عملیات را متوقف کنید و پیام خطا نمایش دهید
        echo 'دسترسی غیر مجاز!';
        exit;
    }

    // دریافت مقادیر از فرم
    $guid = $_POST['post_guid'];
    $resource_id = $_POST['resource_id'];

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

    $url = $guid;

    // Load the HTML from the provided URL
    $html = file_get_html($url);


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

        $excerpt = $html->find($lead_selector, 0);
        $excerpt = $excerpt->plaintext;

        $content = $html->find($body_selector, 0);
        $content = clear_not_allowed_tags($content->innertext);


        $thumbnail_url = $html->find($img_selector, 0)->src;



        // Check if all required elements are found
        if ($title && $excerpt && $content && $thumbnail_url) {

            // Prepare data for creating a WordPress post
            $post_data = array(
                'post_title' => $title,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
                'post_status' => 'draft',
                'post_type' => 'post',
            );


            // Insert the post into the WordPress database
            // درست کردن پست در وردپرس
            try {
                $post_id = wp_insert_post($post_data);
                ob_flush(); // تخلیه خروجی
            } catch (Exception $e) {
                error_log('Failed to insert the post. Error: ' . $e->getMessage());
                ob_flush(); // تخلیه خروجی
            }

            // Upload and set the featured image
            if ($post_id && function_exists('media_sideload_image')) {
                $attachment_id = media_sideload_image($thumbnail_url, $post_id, 'thumbnail', 'id');

                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);

                } elseif (is_wp_error($attachment_id)) {
                    error_log('Failed to upload the featured image. Error: ' . $attachment_id->get_error_message());
                }
            } elseif (!function_exists('media_sideload_image')) {
                error_log('media_sideload_image() function is not available.');
            }

            // Output success or failure message
            if ($post_id) {
                echo '<script>console.log("Post created successfully with ID: " + ' . $post_id . ');</script>';
                // ارسال پاسخ به مرورگر
                wp_safe_redirect(add_query_arg('success', 'true', wp_get_referer()));
                exit;
            } else {
                echo '<script>console.log("Failed to create post. ");</script>';
                error_log('Failed to create post.');
                // ارسال پاسخ به مرورگر
                wp_safe_redirect(add_query_arg('success', 'false', wp_get_referer()));
                exit;
            }
        } else {
            echo '<script>console.log("Required elements not found on the page. ");</script>';

            error_log('Required elements not found on the page.');
        }
    } else {
        echo '<script>console.log("Failed to load HTML from the URL.");</script>';
        error_log('Failed to load HTML from the URL.');
    }
}

function clear_not_allowed_tags($html)
{
     // ایجاد یک شیء DOMDocument
     $dom = new DOMDocument();

     // بارگیری HTML بدون عنوان
     libxml_use_internal_errors(true); // غیرفعال کردن پیام‌های خطا
     $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD); // اضافه کردن تگ xml و بارگیری HTML بدون عنوان
     libxml_clear_errors(); // پاک کردن خطاها
 
     // حذف تگ‌های <a> و <h1>
     $tagsToRemove = array('a', 'h1');
     foreach ($tagsToRemove as $tag) {
         $elementsToRemove = $dom->getElementsByTagName($tag);
         foreach ($elementsToRemove as $element) {
             $element->parentNode->removeChild($element);
         }
     }
 
     // حذف ویژگی‌های id، class و style از تمام تگ‌ها
     $allElements = $dom->getElementsByTagName('*');
     foreach ($allElements as $element) {
         $element->removeAttribute('id');
         $element->removeAttribute('class');
         $element->removeAttribute('style');
     }
 
     // دریافت HTML نهایی
     $filteredHtml = $dom->saveHTML();
 
     // حذف <?xml encoding="UTF-8">
     $filteredHtml = substr($filteredHtml, strlen('<?xml encoding="UTF-8">'));
 
     // بازگرداندن HTML نهایی
     return $filteredHtml;
 }