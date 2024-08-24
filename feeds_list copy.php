<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
$wp_load_path = get_home_path() . 'wp-load.php';
// اگر فایل wp-load.php در مسیر محاسبه شده وجود داشته باشد، آن را لود کنید
if (file_exists($wp_load_path)) {
    require_once ($wp_load_path);
} else {
    // //error_log('wp-load.php not found!');
    exit;
}



// Hook into the admin menu
add_action('admin_menu', 'custom_rss_parser_menu');

// Function to add menu and page
function custom_rss_parser_menu()
{
    add_menu_page(
        'آخرین فیدهای دستیار',
        'دستیار',
        'manage_options',
        'publisher_copoilot',
        'publisher_copoilot_callback',
        'dashicons-image-filter',
        5
    );
}


// Callback function for menu page
function publisher_copoilot_callback()
{
    
    if (isset($_GET['success'])) {
        $action_status = $_GET['success'];
        if ($action_status == 'true') {
            echo '<div class="notice notice-success is-dismissible">
            <p>عملیات با موفقیت انجام شد!</p>
        </div>';
        } elseif ($action_status == 'false') {
            echo '<div class="notice notice-error is-dismissible">
            <p>مشکلی پیش آمد!</p>
        </div>';

        }
    }

    $action = (isset($_GET['action'])) ? $_GET['action'] : '';
    if ($action == 'delete_all') {
        remove_all_feed_on_feeds_table();
    }

    if ($action == 'update_feeds') {
        do_action('custom_rss_parser_event');
        wp_safe_redirect(add_query_arg('success', 'true', wp_get_referer()));
    }
    // $current_url = wp_get_referer();

    // اگر URL صفحه قبل موجود نبود، از URL فعلی استفاده کنید

    $current_url = add_query_arg(NULL, NULL);


    // if (isset($_GET['source']) && $_GET['source'] == '#') {
    //     $url = strtok($current_url, '?'); 
    //     header("Location: $url");
    //     exit;
    // }
?>

    <div class="wrap">
        <p style="padding:0px;margin:0px;float:left;color: #757575;font-size: 12px;">
            <span>واکشی بعدی: </span>
            <?php 
            if($i8_next_scrap_all_resource_feed_time = get_option( 'i8_next_scrap_all_resource_feed_time', '' )){
                date_default_timezone_set('Asia/Tehran');
                echo date( 'H:i', $i8_next_scrap_all_resource_feed_time );
            }
            ?>
        </p>    
        <div class="wp-filter">
            <div class="d-flex p-4 justify-content-between gap-2 flex-wrap">
                <h5 class="wp-heading-inline">جدیدترین فیدها</h5>
                <div class="d-flex flex-wrap gap-2 btn-group ">
                    <div>
                        <form action="<?php echo esc_url($current_url); ?>" method="get">
                            <?php
                            foreach ($_GET as $key => $value) {
                                if ($key != 'source_name') {
                                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                                }
                            }
                            ?>
                            <div class="d-flex">
                                <select name="source" class="form-select rounded-pill btn-outline-secondar">
                                    <option value="#"> همه منابع</option>
                                    <?php foreach (get_all_source_name() as $source_name): ?>
                                        <option value="<?php echo $source_name->resource_id; ?>" 
                                        <?php echo (isset($_GET['source']) && $_GET['source'] == $source_name->resource_id) ? esc_attr('selected') : ''; ?> ><?php echo $source_name->resource_title; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm rounded-pill btn-outline-secondary">فیلتر</button>
                            </div>
                        </form>
                    </div>
                    <div>
                       
                    </div>
                    <div class="d-flex flex-wrap gap-1 align-items-center">
                        <?php


                        ?>
                        <span>تعداد در صفحه:</span>
                        <a href="<?php echo add_query_arg('item_per_page', 25, $current_url); ?>"
                            class="btn btn-sm rounded-pill btn-outline-secondary">
                            25
                        </a>
                        <a href="<?php echo add_query_arg('item_per_page', 50, $current_url); ?>"
                            class="btn btn-sm rounded-pill btn-outline-secondary">
                            50
                        </a>
                        <a href="<?php echo add_query_arg('item_per_page', 100, $current_url); ?> ?>"
                            class="btn btn-sm rounded-pill btn-outline-secondary">
                            100
                        </a>
                    </div>

                    <div class="d-flex gap-1">
                        <a href="admin.php?page=publisher_copoilot&action=update_feeds"
                            class="btn btn-sm rounded-pill btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-arrow-repeat" viewBox="0 0 16 16">
                                <path
                                    d="M11.534 7h3.932a.25.25 0 0 1 .192.41l-1.966 2.36a.25.25 0 0 1-.384 0l-1.966-2.36a.25.25 0 0 1 .192-.41m-11 2h3.932a.25.25 0 0 0 .192-.41L2.692 6.23a.25.25 0 0 0-.384 0L.342 8.59A.25.25 0 0 0 .534 9" />
                                <path fill-rule="evenodd"
                                    d="M8 3c-1.552 0-2.94.707-3.857 1.818a.5.5 0 1 1-.771-.636A6.002 6.002 0 0 1 13.917 7H12.9A5 5 0 0 0 8 3M3.1 9a5.002 5.002 0 0 0 8.757 2.182.5.5 0 1 1 .771.636A6.002 6.002 0 0 1 2.083 9z" />
                            </svg>
                            به روزرسانی
                        </a>

                        <a href="admin.php?page=publisher_copoilot&action=delete_all"
                            class="btn btn-sm rounded-pill btn-outline-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-eraser" viewBox="0 0 16 16">
                                <path
                                    d="M8.086 2.207a2 2 0 0 1 2.828 0l3.879 3.879a2 2 0 0 1 0 2.828l-5.5 5.5A2 2 0 0 1 7.879 15H5.12a2 2 0 0 1-1.414-.586l-2.5-2.5a2 2 0 0 1 0-2.828zm2.121.707a1 1 0 0 0-1.414 0L4.16 7.547l5.293 5.293 4.633-4.633a1 1 0 0 0 0-1.414zM8.746 13.547 3.453 8.254 1.914 9.793a1 1 0 0 0 0 1.414l2.5 2.5a1 1 0 0 0 .707.293H7.88a1 1 0 0 0 .707-.293z" />
                            </svg>
                            حذف همه
                        </a>
                    </div>
                </div>



            </div>
        </div>

        <!-- Add additional HTML and PHP code for displaying the list of items -->
        <?php custom_rss_parser_display_items(); ?>
    </div>
    <?php
}


//include_once plugin_dir_path(__FILE__) . 'inc/scraper.php';

// Function to display the list of items
function custom_rss_parser_display_items()
{
    if (isset($_GET['item_per_page'])) {
        $items_per_page = $_GET['item_per_page'];
        if (get_option('pc_item_per_page') && get_option('pc_item_per_page') != $items_per_page) {
            update_option('pc_item_per_page', $items_per_page);
        } else {
            add_option('pc_item_per_page', $items_per_page);
        }
    } else {

        if (get_option('pc_item_per_page')) {
            $items_per_page = get_option('pc_item_per_page');
        } else {
            add_option('pc_item_per_page', 20);
            $items_per_page = 20;
        }
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';

    // Define the number of items per page

    // Get the current page number
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;

    // Calculate the offset for the query
    $offset = ($paged - 1) * $items_per_page;

    // Fetch items from the database with pagination
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name ORDER BY pub_date DESC");
    if (isset($_GET['source'])) {
        // اگر $source_id موجود بود، شرط WHERE را اضافه کنید
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE resource_id = %d ORDER BY pub_date DESC LIMIT %d OFFSET %d",
            $_GET['source'],
            $items_per_page,
            $offset
        ));

    } else {
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY pub_date DESC LIMIT %d OFFSET %d",
                $items_per_page,
                $offset
            )
        );

    }

    // Pagination
    $total_pages = ceil($total_items / $items_per_page);

    if ($total_pages > 1) {
        $current_page = max(1, $paged);
        echo "<div class='tablenav mb-3' ><div class='tablenav-pages'>";
        echo paginate_links(
            array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'current' => $current_page,
                'total' => $total_pages,
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
                'type' => 'list',
            )
        );
        echo "</div>";
        echo ' نتایج: ' . $total_items . ' مورد - ';
        echo 'از ' . $offset + 1 . ' تا ' . ($offset + $items_per_page);
        echo "</div>";
    }
    ?>

    

    <div class="table table-hover px-3 ">
    <div class="row d-none d-md-flex">
        <div class="th col col-1"> # </div>
        <div class="col col-9 col-md-11 row ">
            <div class="th col col-12 col-xl-6">عنوان</div>
            <div class="th col col-4 col-xl-2  d-none d-md-flex">منبع</div>
            <div class="th col col-8 col-xl-2  d-none d-md-flex">تاریخ انتشار</div>
            <div class="th col col-12 col-xl-2 d-none d-md-flex"></div>
        </div>
    </div>
    

<?php
foreach ($items as $key => $item) {
    $item_id = esc_html($item->id);
    $row_counter = $offset + $key + 1;
    $item_guid = esc_html($item->guid);
    $item_title = esc_html($item->title);
    $resource_name = $item->resource_name ? $item->resource_name : '-';
    // fetch and set time zome feed_time and date
    $dateTime = new DateTime($item->pub_date, new DateTimeZone('GMT'));
    $dateTime->setTimezone(new DateTimeZone('Asia/Tehran'));
    $pub_date = \jDateTime::convertFormatToFormat('d / M | H:i', 'Y-m-d H:i:s', $dateTime->format('Y-m-d H:i:s'));

    $resource_id = esc_attr($item->resource_id);
    $admin_url = esc_url(get_admin_url() . 'images/wpspin_light-2x.gif');
    ?>

    <div class="tr row p-2" id="item-<?php echo $item_id; ?>">
        <div class="col-auto bg-transparent row-counter"><?php echo $row_counter; ?></div>
        <div class="col-11 row bg-transparent">
            <div class="col-12 col-xl-5 bg-transparent feed-item-title">
                <a href="<?php echo $item_guid; ?>" target="_blank"><?php echo $item_title; ?></a>
            </div>
            <div class="col-4 col-xl-2 bg-transparent text-secondary item-meta-data">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hash" viewBox="0 0 16 16">
                    <path d="M8.39 12.648a1 1 0 0 0-.015.18c0 .305.21.508.5.508.266 0 .492-.172.555-.477l.554-2.703h1.204c.421 0 .617-.234.617-.547 0-.312-.188-.53-.617-.53h-.985l.516-2.524h1.265c.43 0 .618-.227.618-.547 0-.313-.188-.524-.618-.524h-1.046l.476-2.304a1 1 0 0 0 .016-.164.51.51 0 0 0-.516-.516.54.54 0 0 0-.539.43l-.523 2.554H7.617l.477-2.304c.008-.04.015-.118.015-.164a.51.51 0 0 0-.523-.516.54.54 0 0 0-.531.43L6.53 5.484H5.414c-.43 0-.617.22-.617.532s.187.539.617.539h.906l-.515 2.523H4.609c-.421 0-.609.219-.609.531s.188.547.61.547h.976l-.516 2.492c-.008.04-.015.125-.015.18 0 .305.21.508.5.508.265 0 .492-.172.554-.477l.555-2.703h2.242zm-1-6.109h2.266l-.515 2.563H6.859l.532-2.563z"/>
                </svg>
                <?php echo $resource_name; ?>
            </div>
            <div class="col-8 col-xl-2 bg-transparent text-secondary item-meta-data" style="direction:left">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                    <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                    <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
                </svg>
                <?php echo $pub_date; ?>
            </div>
            <div class="col-12 col-xl-3 row gap-2 bg-transparent action-bar">
                
                <a href="<?php echo $item_guid; ?>" target="_blank" class="col btn btn-sm rounded-pill btn-outline-secondary" title="بازدید در سایت مرجع">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                    </svg>
                </a>
                <a class="scrape-link col btn btn-sm rounded-pill btn-outline-secondary" title="واکشی و درانتظار بررسی"
                   id="scraper-link-<?php echo $item->id; ?>"
                   data-id="<?php echo $item->id; ?>"
                   data-guid="<?php echo $item_guid; ?>"
                   data-priority="pending"
                   data-resource-id="<?php echo $resource_id; ?>">
                    <img src="<?php echo $admin_url; ?>" style="display:none;position:absolute;left:50%;z-index:100;" />
                    <svg width="16px" height="16px" stroke-width="1.7" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="currentColor"><path d="M3 6.5V5C3 3.89543 3.89543 3 5 3H16.1716C16.702 3 17.2107 3.21071 17.5858 3.58579L20.4142 6.41421C20.7893 6.78929 21 7.29799 21 7.82843V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V17.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 3H16V8.4C16 8.73137 15.7314 9 15.4 9H8.6C8.26863 9 8 8.73137 8 8.4V3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path><path d="M18 21V13.6C18 13.2686 17.7314 13 17.4 13H15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6 21V17.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 12H1M1 12L4 9M1 12L4 15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </a>

                <a class="scrape-link col btn btn-sm rounded-pill btn-outline-secondary" title="انتشار با تاخیر"
                   id="scraper-link-<?php echo $item->id; ?>"
                   data-id="<?php echo $item->id; ?>"
                   data-guid="<?php echo $item_guid; ?>"
                   data-priority="now"
                   data-resource-id="<?php echo $resource_id; ?>">
                    <img src="<?php echo $admin_url; ?>" style="display:none;position:absolute;left:50%;z-index:100;" />
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                    </svg>
                </a>
                
                <a class="scrape-link col btn btn-sm rounded-pill btn-outline-danger" title="زمانبندی با الویت بالا"
                   id="scraper-link-<?php echo $item->id; ?>"
                   data-id="<?php echo $item->id; ?>"
                   data-guid="<?php echo $item_guid; ?>"
                   data-priority="high"
                   data-resource-id="<?php echo $resource_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stopwatch" viewBox="0 0 16 16">
                        <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z"/>
                        <path d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57

c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3"/>
                    </svg>
                </a>
                <a class="scrape-link col btn btn-sm rounded-pill btn-outline-warning" title="زمانبندی با الویت متوسط"
                   id="scraper-link-<?php echo $item->id; ?>"
                   data-id="<?php echo $item->id; ?>"
                   data-guid="<?php echo $item_guid; ?>"
                   data-priority="medium"
                   data-resource-id="<?php echo $resource_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stopwatch" viewBox="0 0 16 16">
                        <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z"/>
                        <path d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3"/>
                    </svg>
                </a>
                <a class="scrape-link col btn btn-sm rounded-pill btn-outline-success" title="زمانبندی با الویت کم"
                   id="scraper-link-<?php echo $item->id; ?>"
                   data-id="<?php echo $item->id; ?>"
                   data-guid="<?php echo $item_guid; ?>"
                   data-priority="low"
                   data-resource-id="<?php echo $resource_id; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-stopwatch" viewBox="0 0 16 16">
                        <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z"/>
                        <path d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3"/>
                    </svg>
                </a>
            
            </div>
        </div>
    </div>
    <?php
}

$total_pages = ceil($total_items / $items_per_page);

if ($total_pages > 1) {
    $current_page = max(1, $paged);
    ?>
    <div class='tablenav'>
        <div class='tablenav-pages'>
            <?php
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'current' => $current_page,
                'total' => $total_pages,
                'prev_text' => __('&laquo; Previous'),
                'next_text' => __('Next &raquo;'),
                'type' => 'list',
            ));
            ?>
        </div>
    </div>
    </div>
    <?php
}
?>

    <link href="<?php echo (COP_PLUGIN_URL . '/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
    <script>
        jQuery(document).ready(function ($) {
            var scrapeLinks = document.querySelectorAll(".scrape-link");
            scrapeLinks.forEach(function (link) {
                link.addEventListener("click", function (e) {
                    var post_Guid = this.getAttribute("data-guid");
                    var resource_id = this.getAttribute("data-resource-id");
                    var post_id = this.getAttribute("data-id");
                    var publish_priority = this.getAttribute("data-priority");

                    // style effect for this item
                    var scrapeImg = document.querySelectorAll("#scraper-link-" + post_id + ">img");
                    scrapeImg.forEach(function (link) {
                        link.style.display = 'inline-block';
                    });
                    document.getElementById("item-" + post_id).classList.add("blinking");


                    //send ajax request to scrape the post
                    $.ajax({
                        url: '<?php echo plugin_dir_url(__DIR__) . "rssnews/inc/scraper.php"; ?>',
                        type: 'POST',
                        data: {
                            action: 'publish_scraper',
                            post_Guid: post_Guid,
                            resource_id: resource_id,
                            publish_priority: publish_priority
                        },
                        success: function (response) {
                            var response = JSON.parse(response);

                            if (response.status === true) {
                                Toast.fire({
                                    icon: "success",
                                    title: response.message
                                });
                            } else {
                                Toast.fire({
                                    icon: "error",
                                    title: response.message
                                });
                            }

                            var scrapeImg = document.querySelectorAll("#scraper-link-" + post_id + ">img");
                            scrapeImg.forEach(function (link) {
                                link.style.display = 'none';
                            });
                            document.getElementById("item-" + post_id).classList.remove("blinking");
                        }
                    });
                });
            });
        });
    </script>


    <style>
        .row-counter {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 13px;
            font-family: serif !important;
            color: #767575;
        }

        .item-meta-data {
            font-size: 13px;
            color: #767575;
        }

        .wp-filter {
            border: 3px solid #f2f2f2;
        }

        .action-bar .btn {
            max-height: 31px;
        }

        .table {
            border: 3px solid #f2f2f2;
        }

        .th {
            padding: 5px;

            font-weight: bold;
            min-height: 50px;
            display: flex !important;
            align-content: center;
            justify-content: flex-start;
            flex-wrap: wrap;
            align-items: center;
            color: #757575;
        }

        .tr:nth-child(even) {
            background-color: #f2f2f2;
            color: #4c4c4ccc;
        }

        .tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .tr:hover {
            background-color: rgb(209 209 209);
        }

        .feed-item-title a {
            color: #297aa9;
            text-decoration: none !important;
        }


        @keyframes blink {
            0% {
                background-color: #f0f0f1;
            }

            50% {
                background-color: gold;
            }

            100% {
                background-color: #f0f0f1;
            }
        }

        .blinking {
            animation: blink 2s infinite;
            filter: blur(1.2px);
        }

        .scraped-feeds-table .tr:hover {
            background-color: #eaf9ff;
        }

        /* pagination */
        .tablenav-pages .page-numbers {
            display: flex;
            gap: 15px;
            padding-left: 0;
        }

        .tablenav .tablenav-pages li {
            display: flex;
            flex-wrap: nowrap;
            align-content: center;
            justify-content: center;
            min-width: 30px;
            min-height: 30px;
            margin: 0;
            padding: 0 4px;
            font-size: 16px;
            line-height: 1.625;
            text-align: center;
            color: #000;
            border: 3px solid #f2f2f2;
            border-radius: 1px;

        }

        .tablenav .tablenav-pages li:hover {
            background-color: #f2f2f2;

        }

        .tablenav .tablenav-pages li a {
            text-decoration: none;
            color: #297aa9;
            width: 100%;
            height: 100%;
        }

    </style>


    <script src="<?php echo plugin_dir_url(__FILE__); ?>/assets/js/sweetalert2@11.js"></script>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "bottom-start",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
    </script>


<?php   include_once 'inc/icons.php'; ?>
<!-- New Template -->

    <style>
        @font-face {
        font-family: IRANSansX;
        font-style: normal;
        font-weight: bold;
        src: url('<?php echo COP_PLUGIN_URL; ?>/assets/fonts/woff/IRANSansX-Bold.woff') format('woff'),
            url('<?php echo COP_PLUGIN_URL; ?>/assets/fonts/woff2/IRANSansX-Bold.woff2') format('woff2');
        }

        @font-face {
        font-family: IRANSansX;
        font-style: normal;
        font-weight: normal;
        src: url('<?php echo COP_PLUGIN_URL; ?>/assets/fonts/woff/IRANSansX-Regular.woff') format('woff'),
            url('<?php echo COP_PLUGIN_URL; ?>/assets/fonts/woff2/IRANSansX-Regular.woff2') format('woff2');
        }


        body,
        .tooltip-inner {
        font-family: 'IRANSansX', sans-serif !important;
        }

        .form-floating>.form-select~label {
        color: rgba(var(--bs-body-color-rgb), .65);
        transform: scale(.73) translateY(-.5rem) translateX(-.15rem);
        color: #A6A6A6;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default .select2-selection--multiple {
        border: solid var(--bs-border-color) 1px !important;
        border-radius: 0 !important;
        }

        .table-item:hover {
        background-color: #eeeff5;
        }

        a {
        text-decoration: none;
        color: rgb(18, 18, 18);
        }

        .custom-tooltip {
        --bs-tooltip-bg: var(--bs-primary);
        --bs-tooltip-color: var(--bs-white);
        }

        @media(min-width:760px) {
        .border-lg-1 {
            border: 1px solid var(--bs-border-color);
        }
        }
    </style>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
    integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />


<div class="table-frame border-lg-1 rounded-4 p-2 p-lg-4">
      <!-- filter box -->
      <div class="filter-box mb-3">
        <div class="row">
          <div class="col order-1 order-lg-1 col-6 col-xl-auto d-flex  align-items-end gap-2">
            <img src= "<?php echo COP_PLUGIN_URL; ?>/assets/images/fee-list-logo.svg" width="50px" height="50px" alt="">
            <span class="d-flex d-lg-none fs-4 fw-bolder"> فــیدها</span>
          </div>
          <div class="col order-3 order-lg-2 col-12  col-xl-10 pe-1 mt-3 mt-lg-0">
            <!-- serach input -->
            <div class="input-group">

              <div class="form-floating ">
                <input type="text" class="form-control form-control-sm" id="search_keyword"
                  placeholder="کلمه مورد نظر را وارد کنید:">
                <label for="search_keyword d-flex d-none">جستجو...</label>
              </div>

              <div class="form-floating">
                <select class="form-select form-control border rounded-0" id="category_list" multiple="multiple"
                  aria-label="دسته مورد نظر را انتخاب کنید:">

                  <optgroup label="سرویس ها">
                    <option value="1">سیاسی</option>
                    <option value="2">اجتماعی</option>
                    <option value="3">ورزشی</option>
                    <option value="1">سیاسی</option>
                    <option value="2">اجتماعی</option>
                    <option value="3">ورزشی</option>
                    <option value="1">سیاسی</option>
                    <option value="2">اجتماعی</option>
                    <option value="3">ورزشی</option>
                    <option value="1">سیاسی</option>
                    <option value="2">اجتماعی</option>
                    <option value="3">ورزشی</option>
                  </optgroup>
                </select>
              </div>

              <div class="form-floating">
                <select class="form-select form-control border rounded-0" id="resource_list" multiple="multiple"
                  aria-label="منبع مورد نظر را انتخاب کنید:">

                  <optgroup label="منابع خبری">
                    <option value="1">فارس</option>
                    <option value="2">تسنیم</option>
                    <option value="3">ورزش۳</option>
                    <option value="1">فارس</option>
                    <option value="2">تسنیم</option>
                    <option value="3">ورزش۳</option>
                    <option value="1">فارس</option>
                    <option value="2">تسنیم</option>
                    <option value="3">ورزش۳</option>
                  </optgroup>
                </select>
              </div>

              <button type="button" id="clear_filters" class="btn btn-outline-secondary btn-large border rounded-0"
                data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-custom-class="custom-tooltip"
                data-bs-title="پاکسازی فیلترها">
                <?php echo $icon_eraser; ?>
              </button>


              <button type="button" class="btn btn-primary btn-large" data-bs-toggle="tooltip"
                data-bs-placement="bottom" data-bs-custom-class="custom-tooltip" data-bs-title="اعمال فیلترها">
                <?php echo $icon_search; ?>
              </button>

            </div>

          </div>
          <div class="col order-2 order-lg-4 col-6 col-xl-1 d-flex gap-1 justify-content-end px-0">



            <button class="btn btn-large btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom"
              data-bs-custom-class="custom-tooltip" data-bs-title="به روز رسانی فیدها">
              <?php echo $icon_arrow_repeat; ?>
            </button>

            <button class="btn btn-large btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom"
              data-bs-custom-class="custom-tooltip" data-bs-title="حذف همه فیدها">
              <?php echo $icon_trash; ?>

            </button>

          </div>
        </div>

        <div class="d-flex justify-content-center text-center gap-2 mt-1">
          <span class="text-secondary" style="font-size: 11px;"> <?php echo $total_items; ?> مورد </span>
          <span> - </span>
          <span class="text-secondary" style="font-size: 11px;"><?php 
            if($i8_next_scrap_all_resource_feed_time = get_option( 'i8_next_scrap_all_resource_feed_time', '' )){
                date_default_timezone_set('Asia/Tehran');
                echo date( 'H:i', $i8_next_scrap_all_resource_feed_time );
            }
            ?> :واکشی بعدی </span>
        </div>

      </div>




      <div class="table table-hover px-0 px-lg-3 ">


        <!-- table header -->
        <div class="table-header row d-none d-md-flex border-bottom pb-2">
          <div class="col col-auto fw-bold justify-content-right text-right p-0"> </div>
          <div class="col col-8  col-md-1 align-items-center fw-bold d-none d-md-flex px-0">
            <span style="min-width: 35px;text-align:right;padding-left:5px;">#</span>
            زمان‌انتشار
          </div>
          <div class="col col-12 col-md-6 fw-bold text-center justify-content-center align-items-center  d-md-flex">
            عنوان</div>
          <div
            class="col col-4  col-md-1 fw-bold text-center justify-content-center align-items-center  d-none d-md-flex">
            منبع</div>
          <div
            class="col col-12 col-md-1 fw-bold text-center justify-content-center align-items-center  d-none d-md-flex">
            دسته‌بندی</div>
          <div
            class="col col-12 col-md-3 fw-bold text-center justify-content-center align-items-center  d-none d-md-flex">
            اکشن ها</div>
        </div>

        <div class="table-body">
          <!-- items -->
          <div class="row border-bottom py-3 d-flex align-items-center table-item">

            <div
              class="col col-3  col-lg-1 col-md-2 text-center d-md-flex bg-transparent feed-item-title  align-items-center d-flex">
              <span style="min-width: 35px;text-align:right;padding-left:5px;;"> ۱ </span>
              <div class="d-flex flex-column ">
                <div class="fs-3 text-danger">۱۵:۵۹</div>
                <div class="text-secondary fs-6">۰۲/۲۵</div>
              </div>
            </div>
            <div class="col col-9 col-lg-6 col-md-5 d-md-flex justify-content-center bg-transparent">
              <a href="#">عباس عبدی: پزشکیان به شعار وفاق ملی متعهد ماند؛‌ حالا نوبت مجلس است</a>
              <a class="btn py-0 rounded-pill btn-outline-success ms-2  px-0 text-center" data-bs-toggle="tooltip"
                data-bs-placement="top" data-bs-title="منتشر شده در سایت" style="width: 30px;height: 30px;" title=""
                id="" data-id="" data-guid="" data-priority="high" data-resource-id="">
                <?php echo $icon_check_all; ?>

              </a>

            </div>
            <div
              class="col col-6  col-md-1 text-center justify-content-center d-md-flex align-items-center gap-1 item-meta-data bg-transparent">
              <?php echo $icon_geo_alt; ?>
              خبرآنلاین
            </div>
            <div
              class="col col-6 col-md-1 text-center justify-content-center d-md-flex align-items-center gap-1 item-meta-data bg-transparent">
              <?php echo $icon_tag; ?>
              سیاسی
            </div>
            <!-- Action Buttons -->
            <div
              class="col col-12 col-md-3  text-center d-flex flex-wrap justify-content-center fw-bold d-md-flex gap-2 bg-transparent action-bar">

              <a class="scrape-link col btn py-0 rounded-pill btn-outline-primary" data-bs-toggle="tooltip"
                data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="نمایش خبر در منبع" title=""
                id="" data-id="" data-guid="" data-priority="high" data-resource-id="">
                <?php echo $icon_eyeglasses;?>
              </a>

              <a class="scrape-link col btn py-0 rounded-pill btn-outline-primary" data-bs-toggle="tooltip"
                data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="واکشی / در انتظار بازبینی"
                title="" id="" data-id="" data-guid="" data-priority="high" data-resource-id="">
                <?php echo $icon_cloud_arrow_down; ?> 
                
              </a>

              <a class="scrape-link col btn py-0 rounded-pill btn-outline-primary" data-bs-toggle="tooltip"
                data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="واکشی / انتشار با تاخیر"
                title="" id="" data-id="" data-guid="" data-priority="high" data-resource-id="">
                <?php echo $icon_send_arrow_down; ?>
                
              </a>

              <a class="scrape-link col btn py-0 rounded-pill btn-outline-danger" data-bs-toggle="tooltip"
                data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                data-bs-title=" واکشی / زمانبندی با اولویت بالا" title="" id="" data-id="" data-guid=""
                data-priority="high" data-resource-id="">
                <?php echo $icon_hourglass_top;  ?>
              </a>

              <a class="scrape-link col btn py-0 rounded-pill btn-outline-warning" data-bs-toggle="tooltip"
                data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                data-bs-title="واکشی / زمانبندی با اولویت متوسط" title="" id="" data-id="" data-guid=""
                data-priority="high" data-resource-id="">
                <?php echo $icon_hourglass_top;  ?>
              </a>

              <a class="scrape-link col btn py-0 rounded-pill btn-outline-success" data-bs-toggle="tooltip"
                data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                data-bs-title="واکشی / زمانبدی با اولویت کم" title="" id="" data-id="" data-guid="" data-priority="high"
                data-resource-id="">
                <?php echo $icon_hourglass_top;  ?>
              </a>


            </div>
          </div>


          <!-- table footer -->
          <div class="row mt-2">
            <div
              class="col col-md-6 col-12 d-flex align-items-start justify-content-center justify-content-md-start text-secondary">
              نتایج: ۲۵۶۳ مورد -
              نمایش ۱ تا ۱۰۰</div>
            <div class="col col-md-6 col-12 d-flex flex-row gap-2 justify-content-center justify-content-md-end">
              <!-- paination -->
              <nav aria-label="Page navigation">
                <ul class="pagination pagination justify-content-center">
                  <li class="page-item disabled">
                    <a class="page-link">قبلی</a>
                  </li>
                  <li class="page-item "><a class="page-link" href="#">۱</a></li>
                  <li class="page-item active"><a class="page-link" href="#">۲</a></li>
                  <li class="page-item"><a class="page-link" href="#">۳</a></li>
                  <li class="page-item">
                    <a class="page-link" href="#">بعدی</a>
                  </li>
                </ul>
              </nav>

              <!-- post per page counter -->
              <div class="form-floatin۱g">
                <select class="form-select" id="post_per_page" style="min-width: 150px;" aria-label="تعداد در صفحه"
                  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                  data-bs-title="تعداد در صفحه">
                  <option value="1" selected>۲۰</option>
                  <option value="2">۴۰</option>
                  <option value="2">۸۰</option>
                  <option value="3">۱۰۰</option>
                </select>
                <!-- <label for="post_per_page">تعداد در صفحه</label> -->
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
    integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
    crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
    crossorigin="anonymous"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- setup select2  -->
    <script>
    $(document).ready(function () {
      $('#category_list').select2({
        closeOnSelect: false,
        placeholder: "دسته بندی ها",
        allowClear: true,
        width: '100%',
        minimumInputLength: 0 // حداقل تعداد کاراکترها برای شروع جستجو
      });
      $('#resource_list').select2({
        closeOnSelect: false,
        placeholder: "منابع",
        allowClear: true,
        width: '100%',
        minimumInputLength: 0 // حداقل تعداد کاراکترها برای شروع جستجو
      });
    });
    </script>
    
    <style>
    .select2-container--default .select2-selection--multiple {
      overflow-y: auto;
      height: 58px;
      max-height: 58px;
      /* می‌توانید این مقدار را تنظیم کنید */
    }
    </style>

   

    <!-- enable toltips -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
      // فعال‌سازی تولتیپ‌ها
      var tooltipTriggerEl = document.getElementById('clear_filters');
      var tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      // تابع پاکسازی فیلترها
      document.querySelector('.btn-outline-secondary').addEventListener('click', function () {
        // پاک کردن مقادیر ورودی
        document.getElementById('search_keyword').value = '';

        // پاک کردن انتخاب‌های select2
        $('#category_list').val(null).trigger('change');
        $('#resource_list').val(null).trigger('change');
      });
    });

  </script>
   <!-- clear all filters -->
   <script>
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
            });
    </script>




    <?php
}