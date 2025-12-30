<?php
/**
 * Theme Customizer
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add postMessage support for site title and description
 */
function campaignpress_customize_register($wp_customize) {
    $blogname = $wp_customize->get_setting('blogname');
    if ($blogname) {
        $blogname->transport = 'postMessage';
    }

    $blogdescription = $wp_customize->get_setting('blogdescription');
    if ($blogdescription) {
        $blogdescription->transport = 'postMessage';
    }

    $header_textcolor = $wp_customize->get_setting('header_textcolor');
    if ($header_textcolor) {
        $header_textcolor->transport = 'postMessage';
    }

    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial(
            'blogname',
            array(
                'selector'        => '.wp-block-site-title a, .site-title a',
                'render_callback' => 'campaignpress_customize_partial_blogname',
            )
        );
        $wp_customize->selective_refresh->add_partial(
            'blogdescription',
            array(
                'selector'        => '.wp-block-site-tagline, .site-description',
                'render_callback' => 'campaignpress_customize_partial_blogdescription',
            )
        );
    }

    /**
     * Colors
     *
     * Theme-specific color controls are registered inside the core "Colors" section
     * to avoid duplicated sections in the Customizer.
     */
    if (!$wp_customize->get_section('colors')) {
        $wp_customize->add_section('colors', array(
            'title'    => __('Colors', 'campaign-office'),
            'priority' => 30,
        ));
    }

    $wp_customize->add_setting('campaignpress_color_scheme', array(
        'default'           => 'democrat-blue',
        'sanitize_callback' => 'campaignpress_sanitize_color_scheme',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_color_scheme', array(
        'label'       => __('Party Color Scheme', 'campaign-office'),
        'description' => __('Applies a preset palette across the site.', 'campaign-office'),
        'section'     => 'colors',
        'type'        => 'select',
        'choices'     => array(
            'democrat-blue'      => __('Democrat Blue', 'campaign-office'),
            'republican-red'     => __('Republican Red', 'campaign-office'),
            'independent-purple' => __('Independent Purple', 'campaign-office'),
            'green-party'        => __('Green Party', 'campaign-office'),
            'neutral'            => __('Neutral', 'campaign-office'),
        ),
    ));

    // Optional per-site color overrides.
    $wp_customize->add_setting('campaignpress_primary_color', array(
        'default'           => '#0053c3',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'campaignpress_primary_color', array(
        'label'       => __('Primary Color Override', 'campaign-office'),
        'description' => __('Overrides the theme primary color (useful for matching your logo).', 'campaign-office'),
        'section'     => 'colors',
    )));

    $wp_customize->add_setting('campaignpress_secondary_color', array(
        'default'           => '#ff8800',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'campaignpress_secondary_color', array(
        'label'       => __('Accent Color Override', 'campaign-office'),
        'description' => __('Overrides the theme accent color.', 'campaign-office'),
        'section'     => 'colors',
    )));

    // Footer compliance disclaimer text.
    $wp_customize->add_setting('campaignpress_disclaimer_text', array(
        'default'           => __('Paid for by Friends of the Candidate', 'campaign-office'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_disclaimer_text', array(
        'label'       => __('"Paid for by" Disclaimer', 'campaign-office'),
        'description' => __('Shown in the footer (use for campaign compliance).', 'campaign-office'),
        'section'     => 'title_tagline',
        'type'        => 'text',
    ));

    /**
     * Navigation
     */
    $wp_customize->add_section('campaignpress_navigation', array(
        'title'    => __('Navigation', 'campaign-office'),
        'priority' => 36,
    ));

    $wp_customize->add_setting('campaignpress_primary_menu_layout', array(
        'default'           => 'inline',
        'sanitize_callback' => 'campaignpress_sanitize_menu_layout',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_primary_menu_layout', array(
        'label'       => __('Primary Menu Layout', 'campaign-office'),
        'description' => __('Controls whether the header menu is inline or stacked vertically.', 'campaign-office'),
        'section'     => 'campaignpress_navigation',
        'type'        => 'radio',
        'choices'     => array(
            'inline'   => __('Inline (Horizontal)', 'campaign-office'),
            'vertical' => __('Vertical (Stacked)', 'campaign-office'),
        ),
    ));

    /**
     * Layout Section
     */
    $wp_customize->add_section('campaignpress_layout', array(
        'title'    => __('Layout Options', 'campaign-office'),
        'priority' => 35,
    ));

    // Homepage layout
    $wp_customize->add_setting('campaignpress_homepage_layout', array(
        'default'           => 'modern',
        'sanitize_callback' => 'campaignpress_sanitize_layout',
    ));

    $wp_customize->add_control('campaignpress_homepage_layout', array(
        'label'    => __('Homepage Layout', 'campaign-office'),
        'section'  => 'campaignpress_layout',
        'type'     => 'select',
        'choices'  => array(
            'classic'      => __('Classic Candidate', 'campaign-office'),
            'modern'       => __('Modern Progressive', 'campaign-office'),
            'traditional'  => __('Conservative Traditional', 'campaign-office'),
        ),
    ));

    // General layout
    $wp_customize->add_setting('campaignpress_layout', array(
        'default'           => 'default',
        'sanitize_callback' => 'campaignpress_sanitize_general_layout',
    ));

    $wp_customize->add_control('campaignpress_layout', array(
        'label'    => __('General Layout', 'campaign-office'),
        'section'  => 'campaignpress_layout',
        'type'     => 'select',
        'choices'  => array(
            'default'      => __('Default (Sidebar Right)', 'campaign-office'),
            'left-sidebar' => __('Sidebar Left', 'campaign-office'),
            'no-sidebar'   => __('No Sidebar (Full Width)', 'campaign-office'),
        ),
    ));

    /**
     * Social Media Section
     */
    $wp_customize->add_section('campaignpress_social_media', array(
        'title'       => __('Social Media Links', 'campaign-office'),
        'description' => __('Enter your social media profile URLs', 'campaign-office'),
        'priority'    => 40,
    ));

    // Facebook URL
    $wp_customize->add_setting('campaignpress_facebook_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_facebook_url', array(
        'label'   => __('Facebook URL', 'campaign-office'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // Twitter URL
    $wp_customize->add_setting('campaignpress_twitter_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_twitter_url', array(
        'label'   => __('Twitter URL', 'campaign-office'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // Instagram URL
    $wp_customize->add_setting('campaignpress_instagram_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_instagram_url', array(
        'label'   => __('Instagram URL', 'campaign-office'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // YouTube URL
    $wp_customize->add_setting('campaignpress_youtube_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_youtube_url', array(
        'label'   => __('YouTube URL', 'campaign-office'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // LinkedIn URL
    $wp_customize->add_setting('campaignpress_linkedin_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_linkedin_url', array(
        'label'   => __('LinkedIn URL', 'campaign-office'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    /**
     * Campaign Information Section
     */
    $wp_customize->add_section('campaignpress_campaign_info', array(
        'title'    => __('Campaign Information', 'campaign-office'),
        'priority' => 45,
    ));

    // Candidate name
    $wp_customize->add_setting('campaignpress_candidate_name', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('campaignpress_candidate_name', array(
        'label'   => __('Candidate Name', 'campaign-office'),
        'section' => 'campaignpress_campaign_info',
        'type'    => 'text',
    ));

    // Office seeking
    $wp_customize->add_setting('campaignpress_office_seeking', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('campaignpress_office_seeking', array(
        'label'       => __('Office Seeking', 'campaign-office'),
        'section'     => 'campaignpress_campaign_info',
        'type'        => 'text',
        'description' => __('e.g., "City Council", "State Senate District 12", "U.S. Congress"', 'campaign-office'),
    ));

    // Campaign tagline
    $wp_customize->add_setting('campaignpress_campaign_tagline', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('campaignpress_campaign_tagline', array(
        'label'   => __('Campaign Tagline', 'campaign-office'),
        'section' => 'campaignpress_campaign_info',
        'type'    => 'text',
    ));

    // Donation button URL
    $wp_customize->add_setting('campaignpress_donation_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_donation_url', array(
        'label'       => __('Donation Page URL', 'campaign-office'),
        'section'     => 'campaignpress_campaign_info',
        'type'        => 'url',
        'description' => __('Link to ActBlue, WinRed, or other donation processor', 'campaign-office'),
    ));
}
add_action('customize_register', 'campaignpress_customize_register');

/**
 * Render the site title for the selective refresh partial
 */
function campaignpress_customize_partial_blogname() {
    bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial
 */
function campaignpress_customize_partial_blogdescription() {
    bloginfo('description');
}

/**
 * Sanitize color scheme
 */
function campaignpress_sanitize_color_scheme($input) {
    $valid = array('democrat-blue', 'republican-red', 'independent-purple', 'green-party', 'neutral');
    return in_array($input, $valid, true) ? $input : 'democrat-blue';
}

/**
 * Sanitize layout (homepage layout)
 */
function campaignpress_sanitize_layout($input) {
    $valid = array('classic', 'modern', 'traditional');
    return in_array($input, $valid, true) ? $input : 'modern';
}

/**
 * Sanitize general layout setting
 */
function campaignpress_sanitize_general_layout($input) {
    $valid = array('default', 'left-sidebar', 'no-sidebar');
    return in_array($input, $valid, true) ? $input : 'default';
}

/**
 * Sanitize primary menu layout
 */
function campaignpress_sanitize_menu_layout($input) {
    $valid = array('inline', 'vertical');
    return in_array($input, $valid, true) ? $input : 'inline';
}

/**
 * Output customizer-driven CSS overrides
 */
function campaignpress_customizer_output_css() {
    $primary = sanitize_hex_color(get_theme_mod('campaignpress_primary_color', null));
    $accent = sanitize_hex_color(get_theme_mod('campaignpress_secondary_color', null));

    if (!$primary && !$accent) {
        return;
    }

    $declarations = array();

    if ($primary) {
        $declarations[] = '--wp--preset--color--primary: ' . $primary . ' !important;';
        $declarations[] = '--cp-primary: ' . $primary . ' !important;';
    }

    if ($accent) {
        $declarations[] = '--wp--preset--color--accent: ' . $accent . ' !important;';
        $declarations[] = '--cp-secondary: ' . $accent . ' !important;';
    }

    echo '<style id="campaignpress-customizer-css">body{' . implode('', $declarations) . '}</style>' . "\n";
}
add_action('wp_head', 'campaignpress_customizer_output_css', 100);

/**
 * Enqueue customizer preview JavaScript
 */
function campaignpress_customize_preview_js() {
    wp_enqueue_script(
        'campaignpress-customizer',
        CAMPAIGNPRESS_ASSETS_URI . '/js/customizer.js',
        array('customize-preview'),
        CAMPAIGNPRESS_VERSION,
        true
    );
}
add_action('customize_preview_init', 'campaignpress_customize_preview_js');
