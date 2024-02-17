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
    // Your code to display the page content goes here
    echo '<div class="wrap">';
    echo '<h1 class="wp-heading-inline">آخرین فیدهای منابع</h1>';
    // Add additional HTML and PHP code for displaying the list of items
    custom_rss_parser_display_items();
    echo '</div>';
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
    echo '<table class="widefat wp-list-table fixed striped table-view-list">';
    echo '<thead>
            <tr>
                <th class="" style="width: 30px;" >ردیف</th>
                <th>عنوان</th>
                <th style="text-align:left;">تاریخ انتشار</th>
                <th>عملیات</th>
            </tr>
           </thead>';
    echo '<tbody>';
    foreach ($items as $item) {
        echo '<tr>';
        echo '<td>' . esc_html($item->id) . '</td>';
        echo '<td><a href="' . esc_html($item->guid) . '" target="_blank">' . esc_html($item->title) . '</a></td>';
        echo '<td class="ltr">' .
        \jDateTime::convertFormatToFormat('Y-m-d / H:i', 'Y-m-d H:i:s', $item->pub_date , 'Asia/Tehran')         
         . '</td>';
        echo '<td><a href="#" class="scrape-link" data-guid="' . esc_attr($item->guid) . '">واکشی و انتشار</a></td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';


    // Add JavaScript to trigger the scrape_and_publish_post function via Ajax
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var scrapeLinks = document.querySelectorAll(".scrape-link");
        scrapeLinks.forEach(function(link) {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                var postGuid = this.getAttribute("data-guid");

                // Use Ajax to call the scraper.php file and pass the postGuid
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "' . admin_url('admin-ajax.php') . '", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4) {
                        if (xhr.status == 200) {
                            // Handle the response if needed
                            console.log(xhr.responseText);
                        } else {
                            // Handle error
                            console.error("Ajax request failed. Status: " + xhr.status);
                        }
                    }
                };

                xhr.send("action=scrape_and_publish_post&guid=" + postGuid);
            });
        });
    });
</script>';

}