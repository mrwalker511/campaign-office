<?php
/**
 * Block Patterns Registration
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register block patterns
 */
function campaignpress_register_block_patterns() {
    // Check if block patterns are supported
    if (!function_exists('register_block_pattern')) {
        return;
    }

    // Register patterns from the patterns directory
    $pattern_files = glob(get_template_directory() . '/patterns/*.php');
    
    foreach ($pattern_files as $pattern_file) {
        $pattern_data = include $pattern_file;
        
        if (is_array($pattern_data)) {
            register_block_pattern(
                $pattern_data['slug'],
                array(
                    'title'       => $pattern_data['title'],
                    'description' => $pattern_data['description'],
                    'content'     => $pattern_data['content'],
                    'categories'  => $pattern_data['categories'],
                )
            );
        }
    }
}
add_action('init', 'campaignpress_register_block_patterns');

/**
 * Add block pattern categories
 */
function campaignpress_register_block_pattern_categories() {
    if (!function_exists('register_block_pattern_category')) {
        return;
    }

    register_block_pattern_category(
        'campaignpress',
        array('label' => __('CampaignPress', 'campaignpress'))
    );

    register_block_pattern_category(
        'campaignpress-hero',
        array('label' => __('CampaignPress Hero', 'campaignpress'))
    );

    register_block_pattern_category(
        'campaignpress-layout',
        array('label' => __('CampaignPress Layouts', 'campaignpress'))
    );
}
add_action('init', 'campaignpress_register_block_pattern_categories');
