<?php

/**
 * Plugin Name: Multi-family Site Management Plugin
 * Plugin URI: 
 * Description: A plugin that imports unit data from the Sightmap API and creates custom post types for each unit in WordPress. The plugin includes an admin page where users can trigger the API import process and view imported unit data, as well as a shortcode to display a styled list of unit posts on the front end of the site.
 * Version: 1.0.0
 * Author: Travis Brewer
 * Author URI: 
 * License: 
 */

// register a custom post type for units
function create_unit_post_type()
{
    $labels = array(
        // allow for translation of the custom post type labels, making the plugin multilingual
        'name'               => _x('Units', 'post type general name', 'wp11'),
        'singular_name'      => _x('Unit', 'post type singular name', 'wp11'),
        // name for the post type in the admin bar
        'menu_name'          => _x('Units', 'admin menu', 'wp11'),
        'name_admin_bar'     => _x('Unit', 'add new on admin bar', 'wp11'),
        'add_new'            => _x('Add New', 'unit', 'wp11'),
        'add_new_item'       => __('Add New Unit', 'wp11'),
        'new_item'           => __('New Unit', 'wp11'),
        'edit_item'          => __('Edit Unit', 'wp11'),
        'view_item'          => __('View Unit', 'wp11'),
        'all_items'          => __('All Units', 'wp11'),
        'search_items'       => __('Search Units', 'wp11'),
        'parent_item_colon'  => __('Parent Units:', 'wp11'),
        'not_found'          => __('No units found.', 'wp11'),
        'not_found_in_trash' => __('No units found in Trash.', 'wp11')
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        // arguments for rewriting URL
        'rewrite' => array(
            'slug' => 'unit',
            // removes default front base string from URL
            'with_front' => false,
            'pages' => true,
            'feeds' => false,
        ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'custom-fields'),
        'register_meta_box_cb' => 'add_unit_custom_fields'
    );

    register_post_type('unit', $args);
}

// hook post type creation function to init action
add_action('init', 'create_unit_post_type');

function wp11_plugin_menu()
{
    add_menu_page(
        'Multi-family Sites Plugin Admin Page', // page title
        'Multi-family Sites', // menu title
        'manage_options', // capabilities
        'wp11_plugin', // menu slug
        'wp11_plugin_page' // function to output page contents
    );
}

// hook menu function to admin menu page
add_action('admin_menu', 'wp11_plugin_menu');

// output contents of admin page
function wp11_plugin_page()
{
?>
    <div class="wrap">
        <!-- escape, translate, print header -->
        <h1><?php esc_html_e('Import Units', 'wp11'); ?></h1>
        <p><?php esc_html_e('Click the button below to import units from the Sightmap API', 'wp11'); ?></p>

        <div class="submit">
            <form method="post">
                <!-- verify form data submitted by current site, not from external source -->
                <input type="hidden" name="wp11_import_units" value="<?php echo wp_create_nonce('wp11_import_units'); ?>">
                <input type="submit" name="import_units" class="button button-primary" value="<?php esc_attr_e('Import Units', 'wp11'); ?>" />
            </form>
        </div>
    </div>
<?php
}

// get api .env variables without third party dependencies
function get_api_info()
{
    $env_file = dirname(__FILE__) . '/.env';
    $env_vars = parse_ini_file($env_file);
    $api_info = [];

    if (isset($env_vars['API_URL'])) {
        $api_info['api_url'] = $env_vars['API_URL'];
    }

    if (isset($env_vars['API_KEY'])) {
        $api_info['api_key'] = $env_vars['API_KEY'];
    }

    return $api_info;
}

// fetch unit data from sightmap api
function fetch_unit_data()
{
    $api_info = get_api_info();
    $api_url = $api_info['api_url'] . "/units?per-page=250";
    $api_key = $api_info['api_key'];

    $args = array(
        'headers' => array(
            'API-key' =>  $api_key
        )
    );

    $response = wp_remote_get($api_url, $args);

    if (is_wp_error($response)) {
        echo 'There was an error fetching data from Sightmap';
    } else {

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return $data;
    }
}

// import and post unit data
function import_units()
{
    $data = fetch_unit_data();

    if (!$data) {
        echo 'There was an error importing units';
        return;
    }

    $data = $data['data'];

    // count number of units created
    $unit_count = 0;

    foreach ($data as $unit) {

        if (empty($unit['unit_number'])) {
            continue;
        }

        // set post title to unit number
        $unit_title = $unit['unit_number'];

        $unit_query = new WP_Query(
            array(
                'post_type' => 'unit',
                'meta_query' => array(
                    array(
                        'key' => '_unit_number',
                        'value' => $unit_title
                    )
                )
            )
        );

        // if post doesn't exist, create posts
        if (!$unit_query->have_posts()) {

            // create new unit post
            $new_unit = array(
                'post_type' => 'unit',
                'post_title' => $unit_title,
                'post_status' => 'publish'
            );

            $unit_id = wp_insert_post($new_unit);

            // update custom fields
            update_post_meta($unit_id, '_asset_id', $unit['asset_id']);
            update_post_meta($unit_id, '_building_id', $unit['building_id']);
            update_post_meta($unit_id, '_floor_id', $unit['floor_id']);
            update_post_meta($unit_id, '_floor_plan_id', $unit['floor_plan_id']);
            update_post_meta($unit_id, '_area', $unit['area']);
            update_post_meta($unit_id, '_unit_number', $unit_title);

            $unit_count += 1;
        }
    }

    return $unit_count;
}

// handle form submission
function handle_import_units()
{
    // import units only when import units button is clicked
    if (isset($_POST['import_units'])) {

        // import units
        try {
            $num_imported = import_units();
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage();
        }

        if ($num_imported) {
            $message = "{$num_imported} unit" . ($num_imported === 1 ? '' : 's') . ' imported.';
            $class = 'notice notice-success is-dismissible';
        } else {
            $message = 'Import unsuccessful.';
            $class = 'notice notice-error';
        }

        echo "<div class='{$class}'><p>{$message}</p></div>";
    }
}

// hook handle_import_units to admin_post action
add_action('admin_init', 'handle_import_units');

// add floor plan id column to list of units
function add_unit_custom_column($columns)
{
    // add new column with key 'floor_plan_id' after 'title'
    $columns = array_slice($columns, 0, 2, true) +
        array('floor_plan_id' => __('Floor Plan ID', 'wp11')) +
        array_slice($columns, 2, count($columns) - 1, true);

    return $columns;
}

add_filter('manage_unit_posts_columns', 'add_unit_custom_column');

// output custom column data for unit post type
function output_unit_custom_column($column_name, $post_id)
{
    if ($column_name == 'floor_plan_id') {
        $floor_plan_id = get_post_meta($post_id, '_floor_plan_id', true);
        echo esc_html($floor_plan_id);
    }
}

add_action('manage_unit_posts_custom_column', 'output_unit_custom_column', 10, 2);

// add custom fields to the unit
function add_unit_custom_fields()
{
    add_meta_box(
        'unit_meta',
        'Unit Details',
        'unit_meta_callback',
        'unit',
        'normal',
        'default'
    );
}

// display custom fields
function unit_meta_callback($post)
{
    wp_nonce_field('unit_custom_fields_nonce', 'unit_custom_fields_nonce');

    // sanitize and save any updates to the unit field 
    $asset_id = get_post_meta($post->ID, '_asset_id', true);
    $building_id = get_post_meta($post->ID, '_building_id', true);
    $floor_id = get_post_meta($post->ID, '_floor_id', true);
    $floor_plan_id = get_post_meta($post->ID, '_floor_plan_id', true);
    $area = get_post_meta($post->ID, '_area', true);

    // display fields
    echo '<p><label for="asset_id_field">' . __('Asset ID', 'wp11') . '</label><br /><input type="text" id="asset_id_field" name="unit_meta[asset_id]" value="' . esc_attr($asset_id) . '" /></p>';
    echo '<p><label for="building_id_field">' . __('Building ID', 'wp11') . '</label><br /><input type="text" id="building_id_field" name="unit_meta[building_id]" value="' . esc_attr($building_id) . '" /></p>';
    echo '<p><label for="floor_id_field">' . __('Floor ID', 'wp11') . '</label><br /><input type="text" id="floor_id_field" name="unit_meta[floor_id]" value="' . esc_attr($floor_id) . '" /></p>';
    echo '<p><label for="floor_plan_id_field">' . __('Floor Plan ID', 'wp11') . '</label><br /><input type="text" id="floor_plan_id_field" name="unit_meta[floor_plan_id]" value="' . esc_attr($floor_plan_id) . '" /></p>';
    echo '<p><label for="area_field">' . __('Area', 'wp11') . '</label><br /><input type="text" id="area_field" name="unit_meta[area]" value="' . esc_attr($area) . '" /></p>';
}

// include file that provides shortcode for list of units organized by area
include_once('shortcode.php');

// enqueue stylesheets
function plugin_enqueue_stylesheets()
{
    wp_enqueue_style('frontend-list-styles', plugin_dir_url(__FILE__) . 'frontend-list-styles.css');
}

add_action('wp_enqueue_scripts', 'plugin_enqueue_stylesheets');

function custom_unit_template($template)
{
    global $post;

    if ('unit' === $post->post_type) {
        $template = plugin_dir_path(__FILE__) . 'templates/single-unit.php';
    }

    return $template;
}

add_filter('single_template', 'custom_unit_template');
