<?php
/*
Plugin Name: Real Estate Listings
Description: Displays real estate listings in a data table with advanced features.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue scripts and styles
function real_estate_listings_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js', array('jquery'), null, true);
    wp_enqueue_script('datatables-responsive', 'https://cdn.datatables.net/responsive/2.2.6/js/dataTables.responsive.min.js', array('jquery', 'datatables'), null, true);
    wp_enqueue_script('jquery-ui-slider', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', array('jquery'), null, true);
    wp_enqueue_script('real-estate-listings-script', plugin_dir_url(__FILE__) . 'js/real-estate-listings.js', array('jquery', 'datatables', 'datatables-responsive', 'jquery-ui-slider'), null, true);
    wp_localize_script('real-estate-listings-script', 'realEstateListings', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('real_estate_listings_nonce')
    ));
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css');
    wp_enqueue_style('datatables-responsive-css', 'https://cdn.datatables.net/responsive/2.2.6/css/responsive.dataTables.min.css');
    wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    wp_enqueue_style('custom-styles', plugin_dir_url(__FILE__) . 'css/custom-styles.css');
}
add_action('wp_enqueue_scripts', 'real_estate_listings_enqueue_scripts');

// Create settings page in admin area
function real_estate_listings_admin_menu() {
    add_menu_page(
        'Real Estate Listings',
        'Real Estate Listings',
        'manage_options',
        'real-estate-listings',
        'real_estate_listings_settings_page',
        'dashicons-admin-home'
    );
    
    add_submenu_page(
        'real-estate-listings',
        'Import CSV',
        'Import CSV',
        'manage_options',
        'real-estate-listings-import',
        'real_estate_listings_import_page'
    );
    
    add_submenu_page(
        'real-estate-listings',
        'Filter Settings',
        'Filter Settings',
        'manage_options',
        'real-estate-listings-filter',
        'real_estate_listings_filter_page'
    );
}
add_action('admin_menu', 'real_estate_listings_admin_menu');

// Register settings
function real_estate_listings_register_settings() {
    register_setting('real_estate_listings_settings', 'real_estate_listings_display_fields');
    register_setting('real_estate_listings_settings', 'real_estate_listings_click_fields');
    register_setting('real_estate_listings_filter_settings', 'real_estate_listings_filter_fields');
}
add_action('admin_init', 'real_estate_listings_register_settings');

// Settings page content
function real_estate_listings_settings_page() {
    ?>
    <div class="wrap">
        <h1>Real Estate Listings Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('real_estate_listings_settings');
            do_settings_sections('real_estate_listings_settings');
            ?>
            <h2>Fields to Display in Table Header</h2>
            <p>Select the fields to display in the table header:</p>
            <label><input type="checkbox" name="real_estate_listings_display_fields[]" value="title" <?php checked(in_array('title', get_option('real_estate_listings_display_fields', array()))); ?>> Title</label><br>
            <label><input type="checkbox" name="real_estate_listings_display_fields[]" value="price" <?php checked(in_array('price', get_option('real_estate_listings_display_fields', array()))); ?>> Price</label><br>
            <label><input type="checkbox" name="real_estate_listings_display_fields[]" value="region" <?php checked(in_array('region', get_option('real_estate_listings_display_fields', array()))); ?>> Region</label><br>
            <label><input type="checkbox" name="real_estate_listings_display_fields[]" value="city" <?php checked(in_array('city', get_option('real_estate_listings_display_fields', array()))); ?>> City</label><br>
            <label><input type="checkbox" name="real_estate_listings_display_fields[]" value="property_type" <?php checked(in_array('property_type', get_option('real_estate_listings_display_fields', array()))); ?>> Property Type</label><br>
            <h2>Fields to Display on Click</h2>
            <p>Select the fields to display when a row is clicked:</p>
            <label><input type="checkbox" name="real_estate_listings_click_fields[]" value="description2" <?php checked(in_array('description2', get_option('real_estate_listings_click_fields', array()))); ?>> Description</label><br>
            <label><input type="checkbox" name="real_estate_listings_click_fields[]" value="adddons" <?php checked(in_array('adddons', get_option('real_estate_listings_click_fields', array()))); ?>> Add-ons</label><br>
            <label><input type="checkbox" name="real_estate_listings_click_fields[]" value="details_table" <?php checked(in_array('details_table', get_option('real_estate_listings_click_fields', array()))); ?>> Details Table</label><br>
            <label><input type="checkbox" name="real_estate_listings_click_fields[]" value="name" <?php checked(in_array('name', get_option('real_estate_listings_click_fields', array()))); ?>> Owner Name</label><br>
            <label><input type="checkbox" name="real_estate_listings_click_fields[]" value="phone" <?php checked(in_array('phone', get_option('real_estate_listings_click_fields', array()))); ?>> Owner Phone</label><br>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Import page content
function real_estate_listings_import_page() {
    ?>
    <div class="wrap">
        <h1>Import Real Estate Listings from CSV</h1>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="real_estate_listings_import_csv">
            <input type="hidden" name="real_estate_listings_import_nonce" value="<?php echo wp_create_nonce('real_estate_listings_import_nonce'); ?>">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="csv_file">CSV File</label></th>
                    <td><input type="file" name="csv_file" id="csv_file" required></td>
                </tr>
            </table>
            <?php submit_button('Import'); ?>
        </form>
    </div>
    <?php
}

// Handle CSV import
function real_estate_listings_import_csv() {
    if (!isset($_POST['real_estate_listings_import_nonce']) || !wp_verify_nonce($_POST['real_estate_listings_import_nonce'], 'real_estate_listings_import_nonce')) {
        wp_die(__('Invalid nonce', 'textdomain'));
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized user', 'textdomain'));
    }

    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $csv_file = $_FILES['csv_file']['tmp_name'];
        $db = new SQLite3(plugin_dir_path(__FILE__) . 'real-estate-listings.db');

        if (($handle = fopen($csv_file, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, ',');
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $query = 'INSERT INTO real_estate_listings (' . implode(',', $header) . ') VALUES ("' . implode('","', array_map(array($db, 'escapeString'), $data)) . '")';
                $db->exec($query);
            }
            fclose($handle);
        }
        $db->close();
        wp_redirect(admin_url('admin.php?page=real-estate-listings-import&import=success'));
        exit;
    } else {
        wp_redirect(admin_url('admin.php?page=real-estate-listings-import&import=error'));
        exit;
    }
}
add_action('admin_post_real_estate_listings_import_csv', 'real_estate_listings_import_csv');

// Filter settings page content
function real_estate_listings_filter_page() {
    $filter_fields = get_option('real_estate_listings_filter_fields', array());
    ?>
    <div class="wrap">
        <h1>Real Estate Listings Filter Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('real_estate_listings_filter_settings');
            do_settings_sections('real_estate_listings_filter_settings');
            ?>
            <h2>Filter Settings</h2>
            <p>Select the fields to include in the filter and their type:</p>
            <table class="form-table">
                <?php
                $fields = array('title' => 'Title', 'price' => 'Price', 'region' => 'Region', 'city' => 'City', 'property_type' => 'Property Type');
                foreach ($fields as $field_key => $field_label) {
                    $type = isset($filter_fields[$field_key]['type']) ? $filter_fields[$field_key]['type'] : '';
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php echo $field_label; ?></th>
                        <td>
                            <label><input type="checkbox" name="real_estate_listings_filter_fields[<?php echo $field_key; ?>][enabled]" value="1" <?php checked(isset($filter_fields[$field_key]['enabled']) ? $filter_fields[$field_key]['enabled'] : 0); ?>> Enable</label>
                            <select name="real_estate_listings_filter_fields[<?php echo $field_key; ?>][type]">
                                <option value="select" <?php selected($type, 'select'); ?>>Select</option>
                                <option value="range" <?php selected($type, 'range'); ?>>Range</option>
                                <option value="slider" <?php selected($type, 'slider'); ?>>Slider</option>
                                <option value="text" <?php selected($type, 'text'); ?>>Text</option>
                            </select>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Shortcode function
function real_estate_listings_shortcode() {
    $display_fields = get_option('real_estate_listings_display_fields', array('title', 'price', 'region', 'city', 'property_type'));
    $click_fields = get_option('real_estate_listings_click_fields', array('description2', 'adddons', 'details_table', 'name', 'phone'));
    $filter_fields = get_option('real_estate_listings_filter_fields', array());

    ob_start();
    ?>
    <div class="filters">
        <?php foreach ($filter_fields as $field_key => $settings) {
            if (isset($settings['enabled']) && $settings['enabled']) {
                echo '<div class="filter-row">';
                echo '<label for="filter-' . $field_key . '">' . ucfirst($field_key) . ':</label>';
                switch ($settings['type']) {
                    case 'select':
                        echo '<select id="filter-' . $field_key . '"><option value="">All</option></select>';
                        break;
                    case 'range':
                        echo '<input type="number" id="filter-' . $field_key . '-min" placeholder="Min">';
                        echo '<input type="number" id="filter-' . $field_key . '-max" placeholder="Max">';
                        break;
                    case 'slider':
                        echo '<div id="' . $field_key . '-slider"></div>';
                        echo '<input type="text" id="' . $field_key . '-amount" readonly>';
                        break;
                    case 'text':
                        echo '<input type="text" id="filter-' . $field_key . '">';
                        break;
                }
                echo '</div>';
            }
        } ?>
    </div>
    <table id="real-estate-listings" class="display responsive nowrap" style="width:100%">
        <thead>
            <tr>
                <th></th>
                <?php if (in_array('title', $display_fields)) echo '<th>כותרת</th>'; ?>
                <?php if (in_array('price', $display_fields)) echo '<th>מחיר</th>'; ?>
                <?php if (in_array('region', $display_fields)) echo '<th>אזור</th>'; ?>
                <?php if (in_array('city', $display_fields)) echo '<th>עיר</th>'; ?>
                <?php if (in_array('property_type', $display_fields)) echo '<th>סוג נכס</th>'; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $db = new SQLite3(plugin_dir_path(__FILE__) . 'real-estate-listings.db');
            $results = $db->query('SELECT * FROM real_estate_listings LIMIT 50');

            while ($listing = $results->fetchArray(SQLITE3_ASSOC)) {
                $details = htmlspecialchars(json_encode($listing), ENT_QUOTES, 'UTF-8');
                echo "<tr data-details='{$details}'>
                    <td class='details-control'>פתח</td>";
                if (in_array('title', $display_fields)) echo "<td>{$listing['title']}</td>";
                if (in_array('price', $display_fields)) echo "<td>{$listing['price']}</td>";
                if (in_array('region', $display_fields)) echo "<td>{$listing['region']}</td>";
                if (in_array('city', $display_fields)) echo "<td>{$listing['city']}</td>";
                if (in_array('property_type', $display_fields)) echo "<td>{$listing['property_type']}</td>";
                echo "</tr>";
            }
            $db->close();
            ?>
        </tbody>
    </table>
    <script>
        jQuery(document).ready(function($) {
            var minPrice = 0, maxPrice = 1000000; // Adjust these values as needed

            function updateCityFilter(region) {
                var citySelect = $('#filter-city');
                citySelect.empty();
                citySelect.append('<option value="">הכל</option>');
                
                if (region) {
                    $.ajax({
                        url: realEstateListings.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'get_cities_by_region',
                            region: region,
                            nonce: realEstateListings.nonce
                        },
                        success: function(response) {
                            try {
                                var cities = JSON.parse(response);
                                cities.forEach(function(city) {
                                    citySelect.append('<option value="' + city + '">' + city + '</option>');
                                });
                            } catch (error) {
                                console.error('Error parsing response:', error);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('AJAX request failed:', textStatus, errorThrown);
                        }
                    });
                }
            }

            var table = $('#real-estate-listings').DataTable({
                "responsive": true,
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "language": {
                    "lengthMenu": "הצג _MENU_ רשומות",
                    "zeroRecords": "לא נמצאו רשומות",
                    "info": "מציג _PAGE_ מתוך _PAGES_",
                    "infoEmpty": "לא נמצאו רשומות",
                    "infoFiltered": "(מסונן מתוך _MAX_ רשומות)",
                    "paginate": {
                        "first": "ראשון",
                        "last": "אחרון",
                        "next": "הבא",
                        "previous": "קודם"
                    }
                },
                "dom": '<"top">rt<"bottom"ip><"clear">',  // Remove default search box
                "columns": [
                    { "orderable": false },
                    <?php if (in_array('title', $display_fields)) echo 'null,'; ?>
                    <?php if (in_array('price', $display_fields)) echo 'null,'; ?>
                    <?php if (in_array('region', $display_fields)) echo 'null,'; ?>
                    <?php if (in_array('city', $display_fields)) echo 'null,'; ?>
                    <?php if (in_array('property_type', $display_fields)) echo 'null,'; ?>
                ]
            });

            $('#filter-region').on('change', function() {
                var selectedRegion = $(this).val();
                updateCityFilter(selectedRegion);
                table.draw();
            });

            $('#filter-city').on('change', function () {
                table.draw();
            });

            $("#price-slider").slider({
                range: true,
                min: minPrice,
                max: maxPrice,
                values: [minPrice, maxPrice],
                slide: function(event, ui) {
                    $("#price-amount").val("₪" + ui.values[0] + " - ₪" + ui.values[1]);
                },
                change: function(event, ui) {
                    table.draw();
                }
            });
            $("#price-amount").val("₪" + $("#price-slider").slider("values", 0) + " - ₪" + $("#price-slider").slider("values", 1));

            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var region = $('#filter-region').val();
                    var city = $('#filter-city').val();
                    var priceRange = $("#price-slider").slider("values");
                    var price = parseInt(data[2]) || 0;

                    var regionData = data[3];
                    var cityData = data[4];

                    if (
                        (region === "" || region == regionData) &&
                        (city === "" || city == cityData) &&
                        (price >= priceRange[0] && price <= priceRange[1])
                    ) {
                        return true;
                    }
                    return false;
                }
            );

            $('#real-estate-listings tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var icon = $(this);

                if (row.child.isShown()) {
                    row.child.hide();
                    icon.text('פתח');
                } else {
                    var details = JSON.parse(tr.attr('data-details'));
                    row.child(format(details)).show();
                    icon.text('סגור');
                }
            });

            $('#real-estate-listings tbody').on('click', '.view-phone', function () {
                $(this).siblings('.phone-number').show();
                $(this).hide();
            });

            function format(details) {
                var content = '';
                <?php if (in_array('description2', $click_fields)) { ?>
                    content += `<strong>תיאור:</strong> ${details.description2}<br>`;
                <?php } ?>
                <?php if (in_array('adddons', $click_fields)) { ?>
                    content += `<strong>תוספות:</strong> ${details.adddons}<br>`;
                <?php } ?>
                <?php if (in_array('details_table', $click_fields)) { ?>
                    content += `<strong>טבלת פרטים:</strong> ${details.details_table}<br>`;
                <?php } ?>
                <?php if (in_array('name', $click_fields)) { ?>
                    content += `<strong>שם המפרסם:</strong> ${details.name}<br>`;
                <?php } ?>
                <?php if (in_array('phone', $click_fields)) { ?>
                    var phoneButton = '<?php if (is_user_logged_in()) { echo "<button class=\'view-phone\'>לחץ כדי לראות מספר טלפון</button><span class=\'phone-number\' style=\'display:none;\'>" . "<a href=\'tel:" . "' + details.phone + '" . "\'>" . "' + details.phone + '" . "</a></span>"; } else { echo "הרשמה כדי לראות מספר טלפון"; } ?>';
                    content += `<strong>טלפון:</strong> ${phoneButton}<br>`;
                <?php } ?>
                return `<div>
                    <img src="${details.image}" alt="Image" style="max-width: 100px;"><br>
                    ${content}
                </div>`;
            }
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('real_estate_listings', 'real_estate_listings_shortcode');

// AJAX handler function
function get_cities_by_region() {
    check_ajax_referer('real_estate_listings_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_die(__('Unauthorized user', 'textdomain'));
    }

    if (isset($_POST['region'])) {
        $region = sanitize_text_field($_POST['region']);
        $db = new SQLite3(plugin_dir_path(__FILE__) . 'real-estate-listings.db');
        $results = $db->query("SELECT DISTINCT city FROM real_estate_listings WHERE region = '$region'");
        
        $cities = [];
        while ($city = $results->fetchArray(SQLITE3_ASSOC)) {
            $cities[] = $city['city'];
        }
        echo json_encode($cities);
    }
    wp_die();
}
add_action('wp_ajax_get_cities_by_region', 'get_cities_by_region');
add_action('wp_ajax_nopriv_get_cities_by_region', 'get_cities_by_region');
