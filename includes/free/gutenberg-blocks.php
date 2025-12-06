<?php
/**
 * Gutenberg Block Registration
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom block category for political blocks
 */
function campaignpress_block_categories($categories) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'campaignpress',
                'title' => __('CampaignPress Blocks', 'campaignpress'),
                'icon'  => 'megaphone',
            ),
        )
    );
}
add_filter('block_categories_all', 'campaignpress_block_categories', 10, 2);

/**
 * Register CampaignPress blocks
 *
 * Note: Most custom functionality is now handled via Block Patterns and Styles
 * as per the WordPress 6.9 Implementation Guide.
 */
function campaignpress_register_blocks() {
    // Check if Gutenberg is active
    if (!function_exists('register_block_type')) {
        return;
    }

    // Enqueue block editor assets
    wp_register_script(
        'campaignpress-blocks-js',
        CAMPAIGNPRESS_ASSETS_URI . '/js/blocks.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        CAMPAIGNPRESS_VERSION,
        true
    );

    wp_register_style(
        'campaignpress-blocks-editor-css',
        CAMPAIGNPRESS_ASSETS_URI . '/css/blocks-editor.css',
        array('wp-edit-blocks'),
        CAMPAIGNPRESS_VERSION
    );

    wp_register_style(
        'campaignpress-blocks-css',
        CAMPAIGNPRESS_ASSETS_URI . '/css/blocks.css',
        array(),
        CAMPAIGNPRESS_VERSION
    );
}
add_action('init', 'campaignpress_register_blocks');
