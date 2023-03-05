<?php

function multifamily_shortcode()
{
    // get all unit posts
    $unit_posts = get_posts(array(
        'post_type' => 'unit',
        'numberposts' => -1,
    ));

    // filter unit posts into two arrays based on unit area
    $units_area_one = array();
    $units_area_greater_than_one = array();
    foreach ($unit_posts as $post) {
        $unit_area = get_post_meta($post->ID, '_area', true);
        if ($unit_area == 1) {
            $units_area_one[] = $post;
        } else {
            $units_area_greater_than_one[] = $post;
        }
    }

    // render the unit posts as an HTML list with links to each post
    $html = '<h3>Units with an area of 1:</h3><ul>';
    foreach ($units_area_one as $post) {
        $html .= '<li><a href="' . esc_url(get_permalink($post->ID)) . '">' . $post->post_title . '</a></li>';
    }
    $html .= '</ul><h3>Units with an area greater than 1:</h3><ul>';
    foreach ($units_area_greater_than_one as $post) {
        $html .= '<li><a href="' . esc_url(get_permalink($post->ID)) . '">' . $post->post_title . '</a></li>';
    }
    $html .= '</ul>';

    return $html;
}
add_shortcode('multifamily_units', 'multifamily_shortcode');
