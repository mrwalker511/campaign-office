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
                'slug'  => 'campaign-office',
                'title' => __('CampaignPress Blocks', 'campaign-office'),
                'icon'  => 'megaphone',
            ),
        )
    );
}
add_filter('block_categories_all', 'campaignpress_block_categories', 10, 2);

/**
 * Register CampaignPress blocks
 */
function campaignpress_register_blocks() {
    // Check if Gutenberg is active
    if (!function_exists('register_block_type')) {
        return;
    }

    // Enqueue block editor assets
    wp_register_style(
        'campaignpress-blocks-editor-css',
        CAMPAIGNPRESS_ASSETS_URI . '/css/editor.css',
        array('wp-edit-blocks'),
        CAMPAIGNPRESS_VERSION
    );

    wp_register_style(
        'campaignpress-blocks-css',
        CAMPAIGNPRESS_ASSETS_URI . '/css/blocks.css',
        array(),
        CAMPAIGNPRESS_VERSION
    );

    // Register Donation Button Block
    register_block_type('campaignpress/donation-button', array(
        'editor_style'    => 'campaignpress-blocks-editor-css',
        'style'           => 'campaignpress-blocks-css',
        'render_callback' => 'campaignpress_render_donation_button_block',
        'attributes'      => array(
            'buttonText' => array(
                'type'    => 'string',
                'default' => __('Donate Now', 'campaign-office'),
            ),
            'donationUrl' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'buttonStyle' => array(
                'type'    => 'string',
                'default' => 'primary',
            ),
            'alignment' => array(
                'type'    => 'string',
                'default' => 'left',
            ),
        ),
    ));

    // Register Issue Card Block
    register_block_type('campaignpress/issue-card', array(
        'editor_style'    => 'campaignpress-blocks-editor-css',
        'style'           => 'campaignpress-blocks-css',
        'render_callback' => 'campaignpress_render_issue_card_block',
        'attributes'      => array(
            'issueTitle' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'issueDescription' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'iconName' => array(
                'type'    => 'string',
                'default' => 'megaphone',
            ),
        ),
    ));

    // Register Volunteer CTA Block
    register_block_type('campaignpress/volunteer-cta', array(
        'editor_style'    => 'campaignpress-blocks-editor-css',
        'style'           => 'campaignpress-blocks-css',
        'render_callback' => 'campaignpress_render_volunteer_cta_block',
        'attributes'      => array(
            'title' => array(
                'type'    => 'string',
                'default' => __('Join Our Campaign', 'campaign-office'),
            ),
            'description' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'buttonText' => array(
                'type'    => 'string',
                'default' => __('Sign Up to Volunteer', 'campaign-office'),
            ),
            'buttonUrl' => array(
                'type'    => 'string',
                'default' => '',
            ),
        ),
    ));

    // Register Modern Interactive Blocks
    register_block_type( CAMPAIGNPRESS_THEME_DIR . '/blocks/countdown' );
    register_block_type( CAMPAIGNPRESS_THEME_DIR . '/blocks/progress' );

    // Register Heroicon Block
    if ( file_exists( CAMPAIGNPRESS_THEME_DIR . '/blocks/icon/block.json' ) ) {
        register_block_type( CAMPAIGNPRESS_THEME_DIR . '/blocks/icon' );
    }
}
add_action('init', 'campaignpress_register_blocks');

/**
 * Render Donation Button Block
 */
function campaignpress_render_donation_button_block($attributes) {
    // Type-safe attribute extraction with validation
    $button_text = isset($attributes['buttonText']) && is_string($attributes['buttonText'])
        ? $attributes['buttonText']
        : __('Donate Now', 'campaign-office');

    $donation_url = isset($attributes['donationUrl']) && is_string($attributes['donationUrl'])
        ? $attributes['donationUrl']
        : campaignpress_get_donation_url();

    // Validate URL format
    if (!empty($donation_url) && !filter_var($donation_url, FILTER_VALIDATE_URL)) {
        $donation_url = '';
    }

    // Whitelist validation for button style
    $button_style = isset($attributes['buttonStyle']) && is_string($attributes['buttonStyle'])
        ? $attributes['buttonStyle']
        : 'primary';
    $valid_styles = array('primary', 'secondary', 'outline');
    if (!in_array($button_style, $valid_styles, true)) {
        $button_style = 'primary';
    }

    // Whitelist validation for alignment
    $alignment = isset($attributes['alignment']) && is_string($attributes['alignment'])
        ? $attributes['alignment']
        : 'left';
    $valid_alignments = array('left', 'center', 'right');
    if (!in_array($alignment, $valid_alignments, true)) {
        $alignment = 'left';
    }

    if (empty($donation_url)) {
        if (current_user_can('edit_posts')) {
            return '<div class="cp-block-notice">' . __('Please set a donation URL in the block settings or theme customizer.', 'campaign-office') . '</div>';
        }
        return '';
    }

    $output = '<div class="cp-donation-button-wrapper align-' . esc_attr($alignment) . '">';
    $output .= sprintf(
        '<a href="%s" class="cp-donation-button cp-button-%s" target="_blank" rel="noopener">%s</a>',
        esc_url($donation_url),
        esc_attr($button_style),
        esc_html($button_text)
    );
    $output .= '</div>';

    return $output;
}

/**
 * Render Campaign Progress Block
 */
function campaignpress_render_campaign_progress_block($attributes) {
    // Type-safe attribute extraction with validation
    $goal_amount = isset($attributes['goalAmount']) && is_numeric($attributes['goalAmount'])
        ? max(0, intval($attributes['goalAmount']))
        : 10000;

    $raised_amount = isset($attributes['raisedAmount']) && is_numeric($attributes['raisedAmount'])
        ? max(0, intval($attributes['raisedAmount']))
        : 0;

    $title = isset($attributes['title']) && is_string($attributes['title'])
        ? $attributes['title']
        : __('Campaign Progress', 'campaign-office');

    $show_percentage = isset($attributes['showPercentage']) && is_bool($attributes['showPercentage'])
        ? $attributes['showPercentage']
        : true;

    $percentage = $goal_amount > 0 ? min(100, ($raised_amount / $goal_amount) * 100) : 0;

    $output = '<div class="cp-campaign-progress">';

    if ($title) {
        $output .= '<h3 class="cp-progress-title">' . esc_html($title) . '</h3>';
    }

    $output .= '<div class="cp-progress-stats">';
    $output .= '<span class="cp-progress-raised">$' . number_format($raised_amount) . '</span>';
    $output .= '<span class="cp-progress-goal">' . sprintf(__('Goal: $%s', 'campaign-office'), number_format($goal_amount)) . '</span>';
    $output .= '</div>';

    $output .= '<div class="cp-progress-bar-container">';
    $output .= '<div class="cp-progress-bar" style="width: ' . esc_attr($percentage) . '%">';

    if ($show_percentage) {
        $output .= '<span class="cp-progress-percentage">' . round($percentage) . '%</span>';
    }

    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

/**
 * Render Issue Card Block
 */
function campaignpress_render_issue_card_block($attributes) {
    // Type-safe attribute extraction with validation
    $title = isset($attributes['issueTitle']) && is_string($attributes['issueTitle'])
        ? $attributes['issueTitle']
        : '';

    $description = isset($attributes['issueDescription']) && is_string($attributes['issueDescription'])
        ? $attributes['issueDescription']
        : '';

    $icon = isset($attributes['iconName']) && is_string($attributes['iconName'])
        ? $attributes['iconName']
        : 'megaphone';

    // Sanitize icon name to prevent CSS injection (only alphanumeric and hyphens)
    $icon = preg_replace('/[^a-z0-9\-]/', '', strtolower($icon));

    $output = '<div class="cp-issue-card">';

    if ($icon) {
        $output .= '<div class="cp-issue-icon"><span class="dashicons dashicons-' . esc_attr($icon) . '"></span></div>';
    }

    if ($title) {
        $output .= '<h3 class="cp-issue-title">' . esc_html($title) . '</h3>';
    }

    if ($description) {
        $output .= '<div class="cp-issue-description">' . wp_kses_post($description) . '</div>';
    }

    $output .= '</div>';

    return $output;
}

/**
 * Render Event Countdown Block
 */
function campaignpress_render_event_countdown_block($attributes) {
    // Type-safe attribute extraction with validation
    $event_date = isset($attributes['eventDate']) && is_string($attributes['eventDate'])
        ? $attributes['eventDate']
        : '';

    $event_title = isset($attributes['eventTitle']) && is_string($attributes['eventTitle'])
        ? $attributes['eventTitle']
        : __('Election Day', 'campaign-office');

    // Validate date format (YYYY-MM-DD)
    if (empty($event_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        if (current_user_can('edit_posts')) {
            return '<div class="cp-block-notice">' . __('Please set a valid event date (YYYY-MM-DD format).', 'campaign-office') . '</div>';
        }
        return '';
    }

    $target_timestamp = strtotime($event_date . ' midnight', current_time('timestamp'));
    if ($target_timestamp === false) {
        return '';
    }
    $current_timestamp = current_time('timestamp');
    $time_diff = $target_timestamp - $current_timestamp;

    if ($time_diff < 0) {
        return '<div class="cp-event-countdown"><p>' . esc_html__('This event has passed.', 'campaign-office') . '</p></div>';
    }

    $days = floor($time_diff / (60 * 60 * 24));

    $output = '<div class="cp-event-countdown">';
    $output .= '<h3 class="cp-countdown-title">' . esc_html($event_title) . '</h3>';
    $output .= '<div class="cp-countdown-display">';
    $output .= '<span class="cp-countdown-number">' . $days . '</span>';
    $output .= '<span class="cp-countdown-label">' . _n('Day', 'Days', $days, 'campaign-office') . '</span>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

/**
 * Render Volunteer CTA Block
 */
function campaignpress_render_volunteer_cta_block($attributes) {
    // Type-safe attribute extraction with validation
    $title = isset($attributes['title']) && is_string($attributes['title'])
        ? $attributes['title']
        : __('Join Our Campaign', 'campaign-office');

    $description = isset($attributes['description']) && is_string($attributes['description'])
        ? $attributes['description']
        : '';

    $button_text = isset($attributes['buttonText']) && is_string($attributes['buttonText'])
        ? $attributes['buttonText']
        : __('Sign Up to Volunteer', 'campaign-office');

    $button_url = isset($attributes['buttonUrl']) && is_string($attributes['buttonUrl'])
        ? $attributes['buttonUrl']
        : '';

    // Validate URL format
    if (!empty($button_url) && !filter_var($button_url, FILTER_VALIDATE_URL)) {
        $button_url = '';
    }

    $output = '<div class="cp-volunteer-cta">';

    if ($title) {
        $output .= '<h3 class="cp-cta-title">' . esc_html($title) . '</h3>';
    }

    if ($description) {
        $output .= '<p class="cp-cta-description">' . esc_html($description) . '</p>';
    }

    if ($button_url) {
        $output .= sprintf(
            '<a href="%s" class="cp-button cp-button-primary">%s</a>',
            esc_url($button_url),
            esc_html($button_text)
        );
    }

    $output .= '</div>';

    return $output;
}
