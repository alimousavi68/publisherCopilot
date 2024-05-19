<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
$wp_load_path = get_home_path() . 'wp-load.php';
// اگر فایل wp-load.php در مسیر محاسبه شده وجود داشته باشد، آن را لود کنید
if (file_exists($wp_load_path)) {
    require_once ($wp_load_path);
} else {
    error_log('wp-load.php not found!');
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
function get_all_source_name()
{
    // wp get query for fetch resouces_post_type post type 
    $args = array(
        'post_type' => 'resource',
        'post_status' => 'publish',
    );
    $resources_query = new WP_Query($args);
    if ($resources_query->have_posts()) {
        while ($resources_query->have_posts()) {
            $resources_query->the_post();
            // Output content here
            $resource_arr[] = array(
                'id' => get_the_ID(),
                'name' => get_the_title(),
            );
        }
        return $resource_arr;
        wp_reset_postdata();
    } else {
        error_log('else fired');
        return false;
    }

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

    ?>

    <div class="wrap">

        <div class="wp-filter">
            <div class="d-flex p-4 justify-content-between gap-2">
                <h5 class="wp-heading-inline">جدیدترین فیدها</h5>
                <div class="d-flex gap-2 btn-group ">
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
                                    <?php foreach (get_all_source_name() as $source_name): ?>
                                        <option value="<?php echo $source_name['id']; ?>" 
                                        <?php echo (isset($_GET['source']) && $_GET['source'] == $source_name['id']) ? esc_attr('selected') : ''; ?>
 ><?php echo $source_name['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm rounded-pill btn-outline-secondar">فیلتر</button>
                            </div>
                        </form>
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
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if (isset($_GET['source'])) {
        // اگر $source_id موجود بود، شرط WHERE را اضافه کنید
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE resource_id = %d ORDER BY id DESC LIMIT %d OFFSET %d",
            $_GET['source'],
            $items_per_page,
            $offset
        ));

    } else {
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
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

    <?php

    echo '<div class="table table-hover px-3 ">

    <div class="row d-none d-md-flex">
        <div class="th col col-auto"> # </div>
        <div class="col col-9 col-md-11 row ">
            <div class="th col col-12 col-xl-6">عنوان</div>
            <div class="th col col-4 col-xl-1  d-none d-md-flex">منبع</div>
            <div class="th col col-8 col-xl-2  d-none d-md-flex">تاریخ انتشار</div>
            <div class="th col col-12 col-xl-3 d-none d-md-flex">عملیات</div>
        </div>
        
    </div>';
    foreach ($items as $key => $item) {
        echo '<div class="tr row p-2" id="item-' . esc_html($item->id) . '" >';
        echo '<div class="col-auto bg-transparent row-counter">' . $offset + $key + 1 . '</div>';
        echo '<div class="col-11 row bg-transparent"><div class="col-12 col-xl-6 bg-transparent feed-item-title"><a href="' . esc_html($item->guid) . '" target="_blank">' . esc_html($item->title) . '</a></div>';

        echo '<div class="col-4 col-xl-1 bg-transparent text-secondary item-meta-data">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hash" viewBox="0 0 16 16"><path d="M8.39 12.648a1 1 0 0 0-.015.18c0 .305.21.508.5.508.266 0 .492-.172.555-.477l.554-2.703h1.204c.421 0 .617-.234.617-.547 0-.312-.188-.53-.617-.53h-.985l.516-2.524h1.265c.43 0 .618-.227.618-.547 0-.313-.188-.524-.618-.524h-1.046l.476-2.304a1 1 0 0 0 .016-.164.51.51 0 0 0-.516-.516.54.54 0 0 0-.539.43l-.523 2.554H7.617l.477-2.304c.008-.04.015-.118.015-.164a.51.51 0 0 0-.523-.516.54.54 0 0 0-.531.43L6.53 5.484H5.414c-.43 0-.617.22-.617.532s.187.539.617.539h.906l-.515 2.523H4.609c-.421 0-.609.219-.609.531s.188.547.61.547h.976l-.516 2.492c-.008.04-.015.125-.015.18 0 .305.21.508.5.508.265 0 .492-.172.554-.477l.555-2.703h2.242zm-1-6.109h2.266l-.515 2.563H6.859l.532-2.563z"/></svg>'
            . (($item->resource_name) ? ($item->resource_name) : '-') . '</div>';
        echo '<div class="col-8 col-xl-2 bg-transparent text-secondary item-meta-data" style="direction:left">'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/></svg>  '
            . \jDateTime::convertFormatToFormat('d / M | H:i ', 'Y-m-d H:i:s', $item->pub_date, 'Asia/Tehran')
            . '</div>';
        echo '<div class="col-12 col-xl-3 row gap-2 bg-transparent action-bar">

                    <a href="' . esc_html($item->guid) . '" target="_blank" class="col btn btn-sm rounded-pill btn-outline-secondary" title="بازدید در سایت مرجع">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye"
                            viewBox="0 0 16 16">
                            <path
                                d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                            <path
                                d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                        </svg>

                    </a>

                    <a class="scrape-link col btn btn-sm rounded-pill btn-outline-secondary" title="انتشار فوری"
                        id="scraper-link-' . $item->id . '"
                        data-id="' . $item->id . '"
                        data-guid="' . $item->guid . '"
                        data-priority="now"
                        data-resource-id="' . esc_attr($item->resource_id) . '"
                    >
                    <img src="' . esc_url(get_admin_url() . 'images/wpspin_light-2x.gif') . '" style="display:none;position:absolute;left:50%;z-index:100;" />
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>
                    </svg>
                    </a>
                    <a class="scrape-link col btn btn-sm rounded-pill btn-outline-danger" title="زمانبندی با الویت بالا"
                    id="scraper-link-' . $item->id . '"
                    data-id="' . $item->id . '"
                    data-guid="' . $item->guid . '"
                    data-priority="high"
                    data-resource-id="' . esc_attr($item->resource_id) . '"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-stopwatch" viewBox="0 0 16 16">
                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                            <path
                                d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                        </svg>
                    </a>
                    <a class="scrape-link col btn btn-sm rounded-pill btn-outline-warning" title="زمانبندی با الویت متوسط"
                    id="scraper-link-' . $item->id . '"
                    data-id="' . $item->id . '"
                    data-guid="' . $item->guid . '"
                    data-priority="medium"
                    data-resource-id="' . esc_attr($item->resource_id) . '"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-stopwatch" viewBox="0 0 16 16">
                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                            <path
                                d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                        </svg>
                    </a>
                    <a class="scrape-link col btn btn-sm rounded-pill btn-outline-success" title="زمانبندی با الویت کم"
                        id="scraper-link-' . $item->id . '"
                        data-id="' . $item->id . '"
                        data-guid="' . $item->guid . '"
                        data-priority="low"
                        data-resource-id="' . esc_attr($item->resource_id) . '"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-stopwatch" viewBox="0 0 16 16">
                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                            <path
                                d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                        </svg>
                    </a>
                    <a class="col btn btn-sm rounded-pill btn-outline-secondary" title="حذف فید">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bookmark-x" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M6.146 5.146a.5.5 0 0 1 .708 0L8 6.293l1.146-1.147a.5.5 0 1 1 .708.708L8.707 7l1.147 1.146a.5.5 0 0 1-.708.708L8 7.707 6.854 8.854a.5.5 0 1 1-.708-.708L7.293 7 6.146 5.854a.5.5 0 0 1 0-.708"/>
  <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v13.5a.5.5 0 0 1-.777.416L8 13.101l-5.223 2.815A.5.5 0 0 1 2 15.5zm2-1a1 1 0 0 0-1 1v12.566l4.723-2.482a.5.5 0 0 1 .554 0L13 14.566V2a1 1 0 0 0-1-1z"/>
</svg>
                    </a>
                </div>';
        echo '</div></div>';

    }


    echo ' </div>';
    // Pagination
    $total_pages = ceil($total_items / $items_per_page);

    if ($total_pages > 1) {
        $current_page = max(1, $paged);
        echo "<div class='tablenav' ><div class='tablenav-pages'>";
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
        echo "</div></div>";
    }
    ?>



    <link href="<?php echo plugin_dir_url(__FILE__); ?>/bootstrap.min.css" rel="stylesheet">
    <!-- <script src="<?php //echo plugin_dir_url(__FILE__); ?>/bootstrap.bundle.min.js"></script> -->

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
                        url: '<?php echo plugin_dir_url(__DIR__) . "rssnews/scraper.php"; ?>',
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
    <script src="<?php echo plugin_dir_url(__FILE__); ?>/sweetalert2@11.js"></script>
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
    <?php
}