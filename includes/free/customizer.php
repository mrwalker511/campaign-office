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
    $wp_customize->get_setting('blogname')->transport = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport = 'postMessage';
    $wp_customize->get_setting('header_textcolor')->transport = 'postMessage';

    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial(
            'blogname',
            array(
                'selector'        => '.site-title a',
                'render_callback' => 'campaignpress_customize_partial_blogname',
            )
        );
        $wp_customize->selective_refresh->add_partial(
            'blogdescription',
            array(
                'selector'        => '.site-description',
                'render_callback' => 'campaignpress_customize_partial_blogdescription',
            )
        );
    }

    /**
     * Color Scheme Section
     */
    $wp_customize->add_section('campaignpress_color_scheme', array(
        'title'    => __('Color Scheme', 'campaign-office'),
        'priority' => 30,
    ));

    // Color scheme preset
    $wp_customize->add_setting('campaignpress_color_scheme', array(
        'default'           => 'neutral',
        'sanitize_callback' => 'campaignpress_sanitize_color_scheme',
    ));

    $wp_customize->add_control('campaignpress_color_scheme', array(
        'label'    => __('Select Color Scheme', 'campaign-office'),
        'section'  => 'campaignpress_color_scheme',
        'type'     => 'select',
        'choices'  => array(
            'democrat-blue'        => __('Democrat Blue', 'campaign-office'),
            'republican-red'       => __('Republican Red', 'campaign-office'),
            'independent-purple'   => __('Independent Purple', 'campaign-office'),
            'green-party'          => __('Green Party', 'campaign-office'),
            'neutral'              => __('Neutral', 'campaign-office'),
        ),
    ));

    // Primary color
    $wp_customize->add_setting('campaignpress_primary_color', array(
        'default'           => '#0066cc',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'campaignpress_primary_color', array(
        'label'    => __('Primary Color', 'campaign-office'),
        'section'  => 'campaignpress_color_scheme',
    )));

    // Secondary color
    $wp_customize->add_setting('campaignpress_secondary_color', array(
        'default'           => '#333333',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'campaignpress_secondary_color', array(
        'label'    => __('Secondary Color', 'campaign-office'),
        'section'  => 'campaignpress_color_scheme',
    )));

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
    return in_array($input, $valid, true) ? $input : 'neutral';
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
