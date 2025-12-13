<?php
/**
 * Block View Scripts Enqueue
 * 
 * Properly enqueues frontend JavaScript for custom blocks
 * 
 * @package CampaignPress
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue block view scripts
 */
function campaignpress_enqueue_block_view_scripts() {
    $blocks_dir = CAMPAIGNPRESS_THEME_DIR . '/blocks';
    $blocks_uri = CAMPAIGNPRESS_THEME_URI . '/blocks';
    
    // Blocks that have view.js files
    $blocks_with_scripts = [
        'countdown',
        'progress',
        'donation-form',
        'event-organizer',
        'volunteer-matcher',
        'policy-platform',
        'mission-control',
        'hero-commander'
    ];
    
    foreach ($blocks_with_scripts as $block) {
        $script_path = $blocks_dir . '/' . $block . '/view.js';
        $script_uri = $blocks_uri . '/' . $block . '/view.js';
        
        if (file_exists($script_path)) {
            wp_enqueue_script(
                'campaignpress-block-' . $block,
                $script_uri,
                array(), // No dependencies needed for vanilla JS
                filemtime($script_path), // Use file modification time for cache busting
                true // Load in footer
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'campaignpress_enqueue_block_view_scripts');
