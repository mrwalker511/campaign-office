<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds custom classes to the array of body classes
 */
function campaignpress_body_class_additions($classes) {
    // Adds a class of hfeed to non-singular pages
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }

    // Adds a class of no-sidebar when there is no sidebar present
    if (!is_active_sidebar('sidebar-1')) {
        $classes[] = 'no-sidebar';
    }

    return $classes;
}
add_filter('body_class', 'campaignpress_body_class_additions');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments
 */
function campaignpress_pingback_header() {
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
    }
}
add_action('wp_head', 'campaignpress_pingback_header');

/**
 * Customize excerpt length
 */
function campaignpress_excerpt_length($length) {
    return 30;
}
add_filter('excerpt_length', 'campaignpress_excerpt_length', 999);

/**
 * Customize excerpt more text
 */
function campaignpress_excerpt_more($more) {
    return '&hellip;';
}
add_filter('excerpt_more', 'campaignpress_excerpt_more');

/**
 * Add custom image sizes to media library
 */
function campaignpress_custom_image_sizes($sizes) {
    return array_merge($sizes, array(
        'campaignpress-candidate-headshot' => __('Candidate Headshot', 'campaignpress'),
        'campaignpress-team-member' => __('Team Member', 'campaignpress'),
        'campaignpress-endorsement' => __('Endorsement Photo', 'campaignpress'),
        'campaignpress-event-hero' => __('Event Hero', 'campaignpress'),
    ));
}
add_filter('image_size_names_choose', 'campaignpress_custom_image_sizes');
