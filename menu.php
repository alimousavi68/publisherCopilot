<?php
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
    echo '<table class="widefat wp-list-table fixed striped table-view-list scraped-feeds-table">';
    echo '<thead>
            <tr>
                <th class="" style="width: 30px;" >ردیف</th>
                <th>عنوان</th>
                <th>منبع</th>
                <th style="text-align:left;">تاریخ انتشار</th>
                <th>عملیات</th>
            </tr>
           </thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr id="item-'.esc_html($item->id).'">';
        echo '<td>' . esc_html($item->id) . '</td>';
        echo '<td><a href="' . esc_html($item->guid) . '" target="_blank">' . esc_html($item->title) . '</a></td>';
        echo '<td style="max-width:50px;">' . (($item->resource_name) ? ($item->resource_name) : '-') . '</td>';
        echo '<td class="ltr">' .
            \jDateTime::convertFormatToFormat('Y-m-d / H:i', 'Y-m-d H:i:s', $item->pub_date, 'Asia/Tehran')
            . '</td>';
        echo '<td>
            <div class="">
                <form id="scrape-form" style="display:inline-block;"  action="' . admin_url('admin-post.php') . '" method="post">
                    ' . wp_nonce_field('scrape_and_publish_post_nonce', 'my_nonce_field') . '
                    <input type="hidden" name="action" value="scrape_and_publish_post">
                    <input type="hidden" name="post_guid" value="' . esc_attr($item->guid) . '">
                    <input type="hidden" name="resource_id" value="' . esc_attr($item->resource_id) . '">
                    <button type="submit" id="scraper-link-' . $item->id . '" data-guid="' . $item->id . '" class="scrape-link button button-secondary" style="position:none;">
                    <img src="' . esc_url( get_admin_url() . 'images/wpspin_light-2x.gif' ) . '" style="display:none;position:absolute;left:50%;z-index:100;" />
                        واکشی و انتشار          
                    </button>

                </form>
                <a href="' . esc_html($item->guid) . '" class="button button-secondary" target="_blank">' . "بازدید" . '</a>
            </div>
        </td>';

        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';


    // Add JavaScript to trigger the scrape_and_publish_post function via Ajax
    ?>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var scrapeLinks = document.querySelectorAll(".scrape-link");
            scrapeLinks.forEach(function (link) {
                link.addEventListener("click", function (e) {

                    var postGuid = this.getAttribute("data-guid");

                    var scrapeImg = document.querySelectorAll("#scraper-link-" + postGuid + ">img");
                    scrapeImg.forEach(function (link) {
                        link.style.display = 'inline-block';
                    });
                    document.getElementById("item-"+postGuid).classList.add("blinking");
                });
            });
        });
    </script>

    <style>
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
            filter:blur(1.2px);
        }

        .scraped-feeds-table tr:hover{
            background-color: #eaf9ff;
        }
    </style>
    <?php
}