<?php
/**
 * Dynamic Block Registration Loader
 * 
 * Scans the blocks directory and registers all valid block.json files.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function campaignpress_register_advanced_blocks() {
    $blocks_dir = CAMPAIGNPRESS_THEME_DIR . '/blocks';
    
    // List of advanced blocks to register
    $blocks = [
        'countdown',
        'progress',
        'donation-form',
        'event-organizer',
        'volunteer-matcher',
        'policy-platform',
        'mission-control',
        'hero-commander',
        'style-panel',
        'section-wrapper'
    ];

    foreach ( $blocks as $block ) {
        $block_path = $blocks_dir . '/' . $block;
        
        if ( file_exists( $block_path . '/block.json' ) ) {
            register_block_type( $block_path );
        }
    }
}
add_action( 'init', 'campaignpress_register_advanced_blocks' );
