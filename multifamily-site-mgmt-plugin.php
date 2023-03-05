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
        'WP11 Plugin Admin Page', // page title
        'WP11 Plugin', // menu title
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