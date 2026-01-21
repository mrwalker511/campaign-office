<?php
/**
 * CampaignPress Block Registration
 *
 * @package CampaignPress
 * @since 1.0.0
 * @since 2.1.0 Migrated to modern block.json format
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
