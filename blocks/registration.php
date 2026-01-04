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
    $block_registry = WP_Block_Type_Registry::get_instance();

    // List of advanced blocks to register
    $blocks = [
        'countdown'         => 'campaignpress/countdown',
        'progress'          => 'campaignpress/progress',
        'donation-form'     => 'campaignpress/donation-form',
        'event-organizer'   => 'campaignpress/event-organizer',
        'volunteer-matcher' => 'campaignpress/volunteer-matcher',
        'policy-platform'   => 'campaignpress/policy-platform',
        'mission-control'   => 'campaignpress/mission-control',
        'hero-commander'    => 'campaignpress/hero-commander',
        'style-panel'       => 'campaignpress/style-panel',
        'section-wrapper'   => 'campaignpress/section-wrapper',
        'icon'              => 'campaignpress/icon',
    ];

    foreach ( $blocks as $folder => $block_name ) {
        $block_path = $blocks_dir . '/' . $folder;

        // Only register if block.json exists and block is not already registered
        if ( file_exists( $block_path . '/block.json' ) && ! $block_registry->is_registered( $block_name ) ) {
            register_block_type( $block_path );
        }
    }
}
add_action( 'init', 'campaignpress_register_advanced_blocks' );
