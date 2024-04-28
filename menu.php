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
        'Custom RSS Items',
        'Custom RSS',
        'manage_options',
        'custom_rss_parser_page',
        'custom_rss_parser_page_callback'
    );
}

// Callback function for menu page
function custom_rss_parser_page_callback()
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
    ?>

    <div class="wrap">
        <h1 class="wp-heading-inline">آخرین فیدهای منابع</h1>
        <hr>
        <div class="wp-filter">
            <div style="float:left ; padding:10px 10px;">

                <a href="admin.php?page=custom_rss_parser_page&action=delete_all" class="button button-secondary">حذف
                    همه</a>
                <a href="admin.php?page=custom_rss_parser_page&action=delete_all" class="button button-secondary">حذف</a>
                <a href="admin.php?page=custom_rss_parser_page&action=update_feeds"
                    class="button button-secondary">بروزرسانی</a>

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
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_rss_items';

    // Fetch items from the database
    $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    // Display the items in a table
    ?>


    <?php
    echo '<div class="table table-hover border rounded-3 overflow-hidden ">

    <div class="row border-bottom">
        <div class="th col">#</div>
        <div class="th col">عنوان</div>
        <div class="th col d-none d-md-flex">منبع</div>
        <div class="th col d-none d-md-flex">تاریخ انتشار</div>
        <div class="th col d-none d-md-flex">عملیات</div>
    </div>';
    foreach ($items as $item) {
        echo '<div class="tr row border-bottom p-2" id="item-' . esc_html($item->id) . '" >';
        echo '<div class="col-1 bg-transparent">' . esc_html($item->id) . '</div>';
        echo '<div class="col-11 row bg-transparent"><div class="col-12 col-xl-6 bg-transparent feed-item-title"><a href="' . esc_html($item->guid) . '" target="_blank">' . esc_html($item->title) . '</a></div>';

        echo '<div class="col-4 col-xl-1 bg-transparent">' . (($item->resource_name) ? ($item->resource_name) : '-') . '</div>';
        echo '<div class="col-8 col-xl-2 bg-transparent">' . \jDateTime::convertFormatToFormat('Y-m-d / H:i', 'Y-m-d H:i:s', $item->pub_date, 'Asia/Tehran') . '</div>';
        echo '<div class="col-12 col-xl-3 row gap-2 bg-transparent">

                    <a href="' . esc_html($item->guid) . '" target="_blank" class="col btn btn-sm rounded-pill btn-outline-dark" title="بازدید در سایت مرجع">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye"
                            viewBox="0 0 16 16">
                            <path
                                d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z" />
                            <path
                                d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0" />
                        </svg>

                    </a>

                    <a class="col btn btn-sm rounded-pill btn-outline-primary" title="انتشار فوری">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-fire" viewBox="0 0 16 16">
                            <path
                                d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15" />
                        </svg>
                    </a>
                    <a class="scrape-link col btn btn-sm rounded-pill btn-outline-danger" title="زمانبندی با الویت بالا"
                        id="scraper-link-' . $item->id . '"
                        data-id="' . $item->id . '"
                        data-guid="' . $item->guid . '"
                        data-resource-id="' . esc_attr($item->resource_id) . '"
                    >
                        <img src="' . esc_url(get_admin_url() . 'images/wpspin_light-2x.gif') . '" style="display:none;position:absolute;left:50%;z-index:100;" />
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-stopwatch" viewBox="0 0 16 16">
                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                            <path
                                d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                        </svg>
                    </a>
                    <a class="col btn btn-sm rounded-pill btn-outline-warning" title="زمانبندی با الویت متوسط">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-stopwatch" viewBox="0 0 16 16">
                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                            <path
                                d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                        </svg>
                    </a>
                    <a class="col btn btn-sm rounded-pill btn-outline-success" title="زمانبندی با الویت کم">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-stopwatch" viewBox="0 0 16 16">
                            <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                            <path
                                d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                        </svg>
                    </a>
                    <a class="col btn btn-sm rounded-pill btn-outline-secondary" title="حذف فید">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-trash" viewBox="0 0 16 16">
                            <path
                                d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z" />
                            <path
                                d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z" />
                        </svg>
                    </a>
                </div>';
        echo '</div></div>';

    }
    echo ' </div>';
    ?>




    <?php


    // Add JavaScript to trigger the scrape_and_publish_post function via Ajax
    ?>
    
    <link href="<?php echo  plugin_dir_url( __FILE__ ); ?>/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo  plugin_dir_url( __FILE__ ); ?>/bootstrap.bundle.min.js"></script>
    <script>
        jQuery(document).ready(function ($) {


            var scrapeLinks = document.querySelectorAll(".scrape-link");
            scrapeLinks.forEach(function (link) {
                link.addEventListener("click", function (e) {
                    var post_Guid = this.getAttribute("data-guid");
                    var resource_id = this.getAttribute("data-resource-id");
                    var post_id = this.getAttribute("data-id");


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
            .th:nth-child(even) {
                padding: 5px;
                background-color: #fefefe;
                font-weight: bold;
                min-height: 50px;
                display: flex;
                align-content: center;
            }

            .tr:nth-child(even) {
                background-color: #f5fcff;
            }

            .tr:nth-child(odd) {
                background-color: #ffffff;
            }

            .tr:hover {
                background-color: rgb(232, 246, 255);
            }
            .feed-item-title a{
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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