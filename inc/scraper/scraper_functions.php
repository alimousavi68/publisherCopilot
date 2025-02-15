<?php
// Function to get the base URL of the current site
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

// Clear Not Allowed Tags
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


// Check Post Link Status
function check_post_link_status($encoded_url)
{
    // مقداردهی اولیه cURL
    $ch = curl_init();

    // تنظیمات cURL
    curl_setopt($ch, CURLOPT_URL, $encoded_url); // آدرس URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // بازگشت پاسخ به‌جای نمایش مستقیم
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // دنبال کردن ریدایرکت‌ها
    curl_setopt($ch, CURLOPT_HEADER, true); // بازگشت هدرها
    curl_setopt($ch, CURLOPT_NOBODY, false); // واکشی محتوای صفحه

    // اجرای درخواست
    $content = curl_exec($ch);

    // دریافت اطلاعات مربوط به URL نهایی و وضعیت HTTP
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // URL نهایی پس از دنبال کردن ریدایرکت‌ها

    // بستن cURL
    curl_close($ch);

    // تحلیل پاسخ
    if ($http_code == 301 || $http_code == 302) {
        return array('code' => $http_code, 'new_location' => $final_url);
    } elseif ($final_url !== $encoded_url) {
        // URL تغییر کرده، به احتمال زیاد ریدایرکت 301 یا مشابه وجود داشته
        return array('code' => '301-like', 'new_location' => $final_url);
    } elseif ($http_code == 200) {
        // بررسی محتوای HTML برای پیام‌های خاص
        if (strpos($content, '301 Moved Permanently') !== false) {
            return array('code' => '301-in-html', 'message' => '301 found in HTML content');
        }
        return array('code' => 200);
    } else {
        return array('code' => $http_code);
    }
}


// Function to fetch HTML content using cURL
function fetch_html_with_curl($url)
{
    // مقداردهی اولیه cURL
    $ch = curl_init();

    // تنظیمات cURL
    curl_setopt($ch, CURLOPT_URL, $url); // آدرس URL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // بازگشت پاسخ به‌جای نمایش مستقیم
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // دنبال کردن ریدایرکت‌ها
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'); // تنظیم User-Agent مشابه مرورگر
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt'); // ذخیره کوکی‌ها
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt'); // استفاده از کوکی‌ها
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // محدودیت زمانی درخواست

    // اجرای درخواست و دریافت محتوا
    $content = curl_exec($ch);

    // بررسی خطاهای cURL
    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
        return false;
    }

    // بستن cURL
    curl_close($ch);

    return $content; // بازگشت محتوای HTML
}


// Encode the URL
// فرض کنید url برابر با http://example.com/سلام باشد.
// الگوی منظم /[^\x20-\x7f]/ کاراکترهای غیر ASCII مانند س, ل, ا, م را پیدا می‌کند.
// تابع callback این کاراکترها را به صورت درصد-رمزگذاری شده تبدیل می‌کند.
// نتیجه نهایی چیزی شبیه به http://example.com/%D8%B3%D9%84%D8%A7%D9%85 خواهد بود.
function encode_persian_chracter_allowed_url($url)
{
    $encoded_url = preg_replace_callback('/[^\x20-\x7f]/', function ($matches) {
        return rawurlencode($matches[0]);
    }, $url);
    return $encoded_url;
}
