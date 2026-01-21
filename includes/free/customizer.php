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
            'title'    => __('Colors', 'campaignpress'),
            'priority' => 30,
        ));
    }

    $wp_customize->add_setting('campaignpress_color_scheme', array(
        'default'           => 'democrat-blue',
        'sanitize_callback' => 'campaignpress_sanitize_color_scheme',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_color_scheme', array(
        'label'       => __('Party Color Scheme', 'campaignpress'),
        'description' => __('Applies a preset palette across the site.', 'campaignpress'),
        'section'     => 'colors',
        'type'        => 'select',
        'choices'     => array(
            'democrat-blue'      => __('Democrat Blue', 'campaignpress'),
            'republican-red'     => __('Republican Red', 'campaignpress'),
            'independent-purple' => __('Independent Purple', 'campaignpress'),
            'green-party'        => __('Green Party', 'campaignpress'),
            'neutral'            => __('Neutral', 'campaignpress'),
        ),
    ));

    // Optional per-site color overrides.
    $wp_customize->add_setting('campaignpress_primary_color', array(
        'default'           => '#0053c3',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'campaignpress_primary_color', array(
        'label'       => __('Primary Color Override', 'campaignpress'),
        'description' => __('Overrides the theme primary color (useful for matching your logo).', 'campaignpress'),
        'section'     => 'colors',
    )));

    $wp_customize->add_setting('campaignpress_secondary_color', array(
        'default'           => '#ff8800',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'campaignpress_secondary_color', array(
        'label'       => __('Accent Color Override', 'campaignpress'),
        'description' => __('Overrides the theme accent color.', 'campaignpress'),
        'section'     => 'colors',
    )));

    // Footer compliance disclaimer text.
    $wp_customize->add_setting('campaignpress_disclaimer_text', array(
        'default'           => __('Paid for by Friends of the Candidate', 'campaignpress'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_disclaimer_text', array(
        'label'       => __('"Paid for by" Disclaimer', 'campaignpress'),
        'description' => __('Shown in the footer (use for campaign compliance).', 'campaignpress'),
        'section'     => 'title_tagline',
        'type'        => 'text',
    ));

    /**
     * Navigation
     */
    $wp_customize->add_section('campaignpress_navigation', array(
        'title'    => __('Navigation', 'campaignpress'),
        'priority' => 36,
    ));

    $wp_customize->add_setting('campaignpress_primary_menu_layout', array(
        'default'           => 'inline',
        'sanitize_callback' => 'campaignpress_sanitize_menu_layout',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_primary_menu_layout', array(
        'label'       => __('Primary Menu Layout', 'campaignpress'),
        'description' => __('Controls whether the header menu is inline or stacked vertically.', 'campaignpress'),
        'section'     => 'campaignpress_navigation',
        'type'        => 'radio',
        'choices'     => array(
            'inline'   => __('Inline (Horizontal)', 'campaignpress'),
            'vertical' => __('Vertical (Stacked)', 'campaignpress'),
        ),
    ));

    /**
     * Layout Section
     */
    $wp_customize->add_section('campaignpress_layout', array(
        'title'    => __('Layout Options', 'campaignpress'),
        'priority' => 35,
    ));

    // Homepage layout
    $wp_customize->add_setting('campaignpress_homepage_layout', array(
        'default'           => 'modern',
        'sanitize_callback' => 'campaignpress_sanitize_layout',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('campaignpress_homepage_layout', array(
        'label'       => __('Homepage Layout', 'campaignpress'),
        'description' => __('Changes the homepage template. The page will refresh when you change this option.', 'campaignpress'),
        'section'     => 'campaignpress_layout',
        'type'        => 'select',
        'choices'     => array(
            'classic'      => __('Classic Candidate', 'campaignpress'),
            'modern'       => __('Modern Progressive', 'campaignpress'),
            'traditional'  => __('Conservative Traditional', 'campaignpress'),
        ),
    ));

    // General layout
    $wp_customize->add_setting('campaignpress_layout', array(
        'default'           => 'default',
        'sanitize_callback' => 'campaignpress_sanitize_general_layout',
    ));

    $wp_customize->add_control('campaignpress_layout', array(
        'label'    => __('General Layout', 'campaignpress'),
        'section'  => 'campaignpress_layout',
        'type'     => 'select',
        'choices'  => array(
            'default'      => __('Default (Sidebar Right)', 'campaignpress'),
            'left-sidebar' => __('Sidebar Left', 'campaignpress'),
            'no-sidebar'   => __('No Sidebar (Full Width)', 'campaignpress'),
        ),
    ));

    /**
     * Social Media Section
     */
    $wp_customize->add_section('campaignpress_social_media', array(
        'title'       => __('Social Media Links', 'campaignpress'),
        'description' => __('Enter your social media profile URLs', 'campaignpress'),
        'priority'    => 40,
    ));

    // Facebook URL
    $wp_customize->add_setting('campaignpress_facebook_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_facebook_url', array(
        'label'   => __('Facebook URL', 'campaignpress'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // Twitter URL
    $wp_customize->add_setting('campaignpress_twitter_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_twitter_url', array(
        'label'   => __('Twitter URL', 'campaignpress'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // Instagram URL
    $wp_customize->add_setting('campaignpress_instagram_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_instagram_url', array(
        'label'   => __('Instagram URL', 'campaignpress'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // YouTube URL
    $wp_customize->add_setting('campaignpress_youtube_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_youtube_url', array(
        'label'   => __('YouTube URL', 'campaignpress'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    // LinkedIn URL
    $wp_customize->add_setting('campaignpress_linkedin_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_linkedin_url', array(
        'label'   => __('LinkedIn URL', 'campaignpress'),
        'section' => 'campaignpress_social_media',
        'type'    => 'url',
    ));

    /**
     * Campaign Information Section
     */
    $wp_customize->add_section('campaignpress_campaign_info', array(
        'title'    => __('Campaign Information', 'campaignpress'),
        'priority' => 45,
    ));

    // Candidate name
    $wp_customize->add_setting('campaignpress_candidate_name', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('campaignpress_candidate_name', array(
        'label'   => __('Candidate Name', 'campaignpress'),
        'section' => 'campaignpress_campaign_info',
        'type'    => 'text',
    ));

    // Office seeking
    $wp_customize->add_setting('campaignpress_office_seeking', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('campaignpress_office_seeking', array(
        'label'       => __('Office Seeking', 'campaignpress'),
        'section'     => 'campaignpress_campaign_info',
        'type'        => 'text',
        'description' => __('e.g., "City Council", "State Senate District 12", "U.S. Congress"', 'campaignpress'),
    ));

    // Campaign tagline
    $wp_customize->add_setting('campaignpress_campaign_tagline', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('campaignpress_campaign_tagline', array(
        'label'   => __('Campaign Tagline', 'campaignpress'),
        'section' => 'campaignpress_campaign_info',
        'type'    => 'text',
    ));

    // Donation button URL
    $wp_customize->add_setting('campaignpress_donation_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control('campaignpress_donation_url', array(
        'label'       => __('Donation Page URL', 'campaignpress'),
        'section'     => 'campaignpress_campaign_info',
        'type'        => 'url',
        'description' => __('Link to ActBlue, WinRed, or other donation processor', 'campaignpress'),
    ));

    /**
     * Hero Section
     */
    $wp_customize->add_section('campaignpress_hero', array(
        'title'       => __('Hero Section', 'campaignpress'),
        'description' => __('Customize your homepage hero section background and overlay.', 'campaignpress'),
        'priority'    => 37,
    ));

    // Hero media type (image or video)
    $wp_customize->add_setting('campaignpress_hero_media_type', array(
        'default'           => 'image',
        'sanitize_callback' => 'campaignpress_sanitize_hero_media_type',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_hero_media_type', array(
        'label'       => __('Background Media Type', 'campaignpress'),
        'description' => __('Choose between an image or video background.', 'campaignpress'),
        'section'     => 'campaignpress_hero',
        'type'        => 'radio',
        'choices'     => array(
            'image' => __('Image', 'campaignpress'),
            'video' => __('Video', 'campaignpress'),
        ),
    ));

    // Hero background image
    $wp_customize->add_setting('campaignpress_hero_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'campaignpress_hero_image', array(
        'label'       => __('Hero Background Image', 'campaignpress'),
        'description' => __('Upload or select an image for the hero section background.', 'campaignpress'),
        'section'     => 'campaignpress_hero',
    )));

    // Hero background video URL
    $wp_customize->add_setting('campaignpress_hero_video', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_hero_video', array(
        'label'       => __('Hero Background Video URL', 'campaignpress'),
        'description' => __('Enter the URL for a self-hosted video file (MP4 recommended).', 'campaignpress'),
        'section'     => 'campaignpress_hero',
        'type'        => 'url',
    ));

    // Hero overlay opacity
    $wp_customize->add_setting('campaignpress_hero_overlay_opacity', array(
        'default'           => 50,
        'sanitize_callback' => 'campaignpress_sanitize_opacity',
        'transport'         => 'postMessage',
    ));

    $wp_customize->add_control('campaignpress_hero_overlay_opacity', array(
        'label'       => __('Overlay Opacity', 'campaignpress'),
        'description' => __('Adjust the darkness of the overlay (0 = transparent, 100 = fully dark). Recommended: 40-60% for optimal text readability.', 'campaignpress'),
        'section'     => 'campaignpress_hero',
        'type'        => 'range',
        'input_attrs' => array(
            'min'  => 0,
            'max'  => 100,
            'step' => 5,
        ),
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
 * Color scheme definitions
 *
 * @return array
 */
function campaignpress_get_color_schemes() {
    return array(
        'democrat-blue' => array(
            'primary'      => '#0053c3',
            'primary_dark' => '#003275',
            'accent'       => '#ff8800',
        ),
        'republican-red' => array(
            'primary'      => '#e81b23',
            'primary_dark' => '#9e0e14',
            'accent'       => '#002868',
        ),
        'independent-purple' => array(
            'primary'      => '#6554c0',
            'primary_dark' => '#4a3d91',
            'accent'       => '#00b8d9',
        ),
        'green-party' => array(
            'primary'      => '#228B22',
            'primary_dark' => '#1a6b1a',
            'accent'       => '#FFD700',
        ),
        'neutral' => array(
            'primary'      => '#495057',
            'primary_dark' => '#343a40',
            'accent'       => '#6c757d',
        ),
    );
}

/**
 * Sanitize color scheme
 */
function campaignpress_sanitize_color_scheme($input) {
    $valid = array('democrat-blue', 'republican-red', 'independent-purple', 'green-party', 'neutral');
    return in_array($input, $valid, true) ? $input : 'democrat-blue';
}

/**
 * Apply color scheme colors when scheme is changed
 *
 * @param mixed $value The new value.
 * @param mixed $old_value The old value.
 * @return mixed The sanitized value.
 */
function campaignpress_apply_color_scheme_on_save($value, $old_value) {
    if ($value === $old_value) {
        return $value;
    }

    $schemes = campaignpress_get_color_schemes();
    if (isset($schemes[$value])) {
        $colors = $schemes[$value];
        set_theme_mod('campaignpress_primary_color', $colors['primary']);
        set_theme_mod('campaignpress_secondary_color', $colors['accent']);
    }

    return $value;
}
add_filter('pre_set_theme_mod_campaignpress_color_scheme', 'campaignpress_apply_color_scheme_on_save', 10, 2);

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
 * Sanitize hero media type
 */
function campaignpress_sanitize_hero_media_type($input) {
    $valid = array('image', 'video');
    return in_array($input, $valid, true) ? $input : 'image';
}

/**
 * Sanitize opacity value (0-100)
 */
function campaignpress_sanitize_opacity($input) {
    $input = absint($input);
    return min(100, max(0, $input));
}

/**
 * Output customizer-driven CSS overrides
 */
function campaignpress_customizer_output_css() {
    $color_scheme = get_theme_mod('campaignpress_color_scheme', 'democrat-blue');
    $schemes = campaignpress_get_color_schemes();

    // Get colors from color scheme as defaults
    $scheme_colors = isset($schemes[$color_scheme]) ? $schemes[$color_scheme] : $schemes['democrat-blue'];

    // Get custom colors (use scheme colors as defaults)
    $primary = sanitize_hex_color(get_theme_mod('campaignpress_primary_color', $scheme_colors['primary']));
    $primary_dark = $scheme_colors['primary_dark'];
    $accent = sanitize_hex_color(get_theme_mod('campaignpress_secondary_color', $scheme_colors['accent']));
    $hero_opacity = get_theme_mod('campaignpress_hero_overlay_opacity', 50);
    $hero_image = esc_url(get_theme_mod('campaignpress_hero_image', ''));
    $hero_media_type = get_theme_mod('campaignpress_hero_media_type', 'image');

    $declarations = array();
    $hero_css = '';

    // Always output primary color (either custom or from scheme)
    if ($primary) {
        $declarations[] = '--wp--preset--color--primary: ' . $primary . ' !important;';
        $declarations[] = '--cp-primary: ' . $primary . ' !important;';
        $declarations[] = '--wp--preset--color--primary-dark: ' . $primary_dark . ' !important;';
        $declarations[] = '--cp-primary-dark: ' . $primary_dark . ' !important;';
    }

    // Always output accent color (either custom or from scheme)
    if ($accent) {
        $declarations[] = '--wp--preset--color--accent: ' . $accent . ' !important;';
        $declarations[] = '--cp-secondary: ' . $accent . ' !important;';
    }

    // Hero overlay opacity (convert 0-100 to 0-1)
    $opacity_decimal = $hero_opacity / 100;
    $hero_css .= '.is-style-campaign-hero .wp-block-cover__background,';
    $hero_css .= '.campaign-hero .wp-block-cover__background,';
    $hero_css .= '.hero-section .wp-block-cover__background,';
    $hero_css .= '.hero-video-section .wp-block-cover__background,';
    $hero_css .= '.cp-hero .wp-block-cover__background,';
    $hero_css .= '.cp-hero__overlay {';
    $hero_css .= 'opacity: ' . esc_attr($opacity_decimal) . ' !important;';
    $hero_css .= '}';

    // Hero background image (only apply if media type is image and URL is set)
    if ($hero_media_type === 'image' && $hero_image) {
        $hero_css .= '.is-style-campaign-hero,';
        $hero_css .= '.campaign-hero,';
        $hero_css .= '.hero-section,';
        $hero_css .= '.hero-video-section,';
        $hero_css .= '.cp-hero,';
        $hero_css .= '.cp-hero__bg {';
        $hero_css .= 'background-image: url("' . $hero_image . '") !important;';
        $hero_css .= 'background-size: cover !important;';
        $hero_css .= 'background-position: center !important;';
        $hero_css .= '}';
        
        // Ensure standard wp-block-cover image is hidden when custom background is applied
        $hero_css .= '.is-style-campaign-hero .wp-block-cover__image-background,';
        $hero_css .= '.campaign-hero .wp-block-cover__image-background,';
        $hero_css .= '.hero-section .wp-block-cover__image-background,';
        $hero_css .= '.hero-video-section .wp-block-cover__image-background,';
        $hero_css .= '.cp-hero .wp-block-cover__image-background {';
        $hero_css .= 'display: none !important;';
        $hero_css .= '}';
    }

    $output = '';
    if (!empty($declarations)) {
        $output .= '<style id="campaignpress-customizer-css">body{' . implode('', $declarations) . '}</style>' . "\n";
    }
    if ($hero_css) {
        $output .= '<style id="campaignpress-hero-css">' . $hero_css . '</style>' . "\n";
    }

    echo $output;
}
add_action('wp_head', 'campaignpress_customizer_output_css', 100);

/**
 * Enqueue customizer preview JavaScript
 */
function campaignpress_customize_preview_js() {
    wp_enqueue_script(
        'campaignpress-customizer',
        CAMPAIGNPRESS_ASSETS_URI . '/js/customizer.js',
        array('customize-preview', 'jquery'),
        CAMPAIGNPRESS_VERSION,
        true
    );
}
add_action('customize_preview_init', 'campaignpress_customize_preview_js');

/**
 * Enqueue customizer control JavaScript (runs in the customizer panel)
 */
function campaignpress_customize_controls_js() {
    wp_enqueue_script(
        'campaignpress-customizer-controls',
        CAMPAIGNPRESS_ASSETS_URI . '/js/customizer-controls.js',
        array('customize-controls', 'jquery'),
        CAMPAIGNPRESS_VERSION,
        true
    );
}
add_action('customize_controls_enqueue_scripts', 'campaignpress_customize_controls_js');

/**
 * Output hero video background if video media type is selected
 */
function campaignpress_hero_video_output() {
    $hero_media_type = get_theme_mod('campaignpress_hero_media_type', 'image');
    $hero_video = esc_url(get_theme_mod('campaignpress_hero_video', ''));

    if ($hero_media_type !== 'video' || empty($hero_video)) {
        return;
    }

    // Output inline script to inject video into hero sections
    ?>
    <script id="campaignpress-hero-video-script">
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            var heroSections = document.querySelectorAll('.is-style-campaign-hero, .campaign-hero, .hero-section, .hero-video-section, .cp-hero');
            var videoUrl = <?php echo wp_json_encode($hero_video); ?>;

            heroSections.forEach(function(section) {
                // Check if video already exists
                if (section.querySelector('.cp-hero-video')) {
                    return;
                }

                var video = document.createElement('video');
                video.className = 'wp-block-cover__video-background intrinsic-ignore cp-hero-video';
                video.autoplay = true;
                video.muted = true;
                video.loop = true;
                video.playsInline = true;
                video.src = videoUrl;
                video.style.cssText = 'position:absolute;top:50%;left:50%;min-width:100%;min-height:100%;width:auto;height:auto;transform:translate(-50%,-50%);object-fit:cover;z-index:0;';

                // Insert video as first child
                section.insertBefore(video, section.firstChild);
            });
        });
    })();
    </script>
    <?php
}
add_action('wp_footer', 'campaignpress_hero_video_output');
