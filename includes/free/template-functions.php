<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds custom classes to the array of body classes
 */
function campaignpress_body_class_additions($classes) {
    // Adds a class of hfeed to non-singular pages
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }

    // Front page always gets no-sidebar and full-width
    if (is_front_page()) {
        $classes[] = 'no-sidebar';
        $classes[] = 'full-width';
    }
    // Adds a class of no-sidebar when there is no sidebar present
    elseif (!is_active_sidebar('sidebar-1')) {
        $classes[] = 'no-sidebar';
    }

    return $classes;
}
add_filter('body_class', 'campaignpress_body_class_additions');

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments
 */
function campaignpress_pingback_header() {
    if (is_singular() && pings_open()) {
        printf('<link rel="pingback" href="%s">', esc_url(get_bloginfo('pingback_url')));
    }
}
add_action('wp_head', 'campaignpress_pingback_header');

/**
 * Customize excerpt length
 */
function campaignpress_excerpt_length($length) {
    return 30;
}
add_filter('excerpt_length', 'campaignpress_excerpt_length', 999);

/**
 * Customize excerpt more text
 */
function campaignpress_excerpt_more($more) {
    return '&hellip;';
}
add_filter('excerpt_more', 'campaignpress_excerpt_more');

/**
 * Add custom image sizes to media library
 */
function campaignpress_custom_image_sizes($sizes) {
    return array_merge($sizes, array(
        'campaignpress-candidate-headshot' => __('Candidate Headshot', 'campaign-office'),
        'campaignpress-team-member' => __('Team Member', 'campaign-office'),
        'campaignpress-endorsement' => __('Endorsement Photo', 'campaign-office'),
        'campaignpress-event-hero' => __('Event Hero', 'campaign-office'),
    ));
}
add_filter('image_size_names_choose', 'campaignpress_custom_image_sizes');

/**
 * Get the current page/post layout
 *
 * Checks for per-page meta, then theme option, then default
 *
 * @return string Layout slug
 */
function campaignpress_get_layout() {
    $layout = 'sidebar-right'; // Default

    // Check for per-page/post meta
    if (is_singular()) {
        $post_layout = get_post_meta(get_the_ID(), '_campaignpress_layout', true);
        if ($post_layout && $post_layout !== 'default') {
            return $post_layout;
        }
    }

    // Check theme options
    $theme_layout = get_option('campaignpress_layout');
    if ($theme_layout) {
        $layout = $theme_layout;
    }

    // Check customizer (for backwards compatibility)
    $customizer_layout = get_theme_mod('campaignpress_layout');
    if ($customizer_layout) {
        $layout = $customizer_layout;
    }

    return apply_filters('campaignpress_layout', $layout);
}

/**
 * Determine if sidebar should be shown
 *
 * @return bool
 */
function campaignpress_show_sidebar() {
    $layout = campaignpress_get_layout();

    // Don't show sidebar on full-width layouts
    if ($layout === 'no-sidebar' || $layout === 'full-width') {
        return false;
    }

    // Don't show if sidebar is not active
    if (!is_active_sidebar('sidebar-1')) {
        return false;
    }

    return apply_filters('campaignpress_show_sidebar', true);
}

/**
 * Get container class based on layout
 *
 * @return string
 */
function campaignpress_get_container_class() {
    $layout = campaignpress_get_layout();
    $classes = array('site-container');

    switch ($layout) {
        case 'sidebar-left':
            $classes[] = 'has-sidebar';
            $classes[] = 'sidebar-left';
            break;
        case 'sidebar-right':
            $classes[] = 'has-sidebar';
            $classes[] = 'sidebar-right';
            break;
        case 'no-sidebar':
        case 'full-width':
            $classes[] = 'no-sidebar';
            $classes[] = 'full-width';
            break;
    }

    return implode(' ', apply_filters('campaignpress_container_class', $classes));
}

/**
 * Add layout meta box to posts and pages
 */
function campaignpress_add_layout_meta_box() {
    $post_types = array('post', 'page', 'cp_issue', 'cp_event', 'cp_endorsement', 'cp_team', 'cp_volunteer');

    foreach ($post_types as $post_type) {
        add_meta_box(
            'campaignpress_layout_meta_box',
            __('Layout Options', 'campaign-office'),
            'campaignpress_layout_meta_box_callback',
            $post_type,
            'side',
            'default'
        );
    }
}
add_action('add_meta_boxes', 'campaignpress_add_layout_meta_box');

/**
 * Layout meta box callback
 */
function campaignpress_layout_meta_box_callback($post) {
    wp_nonce_field('campaignpress_layout_meta_box', 'campaignpress_layout_meta_box_nonce');

    $current_layout = get_post_meta($post->ID, '_campaignpress_layout', true);
    if (!$current_layout) {
        $current_layout = 'default';
    }

    $layouts = array(
        'default' => __('Default (Theme Setting)', 'campaign-office'),
        'sidebar-right' => __('Sidebar Right', 'campaign-office'),
        'sidebar-left' => __('Sidebar Left', 'campaign-office'),
        'no-sidebar' => __('No Sidebar (Full Width)', 'campaign-office'),
    );
    ?>
    <p>
        <label for="campaignpress_layout"><strong><?php esc_html_e('Page Layout:', 'campaign-office'); ?></strong></label><br>
        <select id="campaignpress_layout" name="campaignpress_layout" style="width: 100%;">
            <?php foreach ($layouts as $layout_value => $layout_label) : ?>
                <option value="<?php echo esc_attr($layout_value); ?>" <?php selected($current_layout, $layout_value); ?>>
                    <?php echo esc_html($layout_label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

/**
 * Save layout meta
 */
function campaignpress_save_layout_meta($post_id) {
    // Check nonce
    if (!isset($_POST['campaignpress_layout_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['campaignpress_layout_meta_box_nonce'], 'campaignpress_layout_meta_box')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save layout
    if (isset($_POST['campaignpress_layout'])) {
        update_post_meta($post_id, '_campaignpress_layout', sanitize_text_field($_POST['campaignpress_layout']));
    }
}
add_action('save_post', 'campaignpress_save_layout_meta');

/**
 * Add hero video meta box to pages
 */
function campaignpress_add_hero_video_meta_box() {
    add_meta_box(
        'campaignpress_hero_video_meta_box',
        __('Hero Video Overlay', 'campaign-office'),
        'campaignpress_hero_video_meta_box_callback',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'campaignpress_add_hero_video_meta_box');

/**
 * Hero video meta box callback
 */
function campaignpress_hero_video_meta_box_callback($post) {
    wp_nonce_field('campaignpress_hero_video_meta_box', 'campaignpress_hero_video_meta_box_nonce');

    $video_url = get_post_meta($post->ID, '_campaignpress_hero_video_url', true);
    $video_type = get_post_meta($post->ID, '_campaignpress_hero_video_type', true);
    $show_hero = get_post_meta($post->ID, '_campaignpress_show_hero', true);

    if ($show_hero === '') {
        $show_hero = '1'; // Default to showing hero
    }
    ?>
    <div class="campaignpress-hero-video-settings">
        <p>
            <label for="campaignpress_show_hero">
                <input type="checkbox" id="campaignpress_show_hero" name="campaignpress_show_hero" value="1" <?php checked($show_hero, '1'); ?>>
                <strong><?php esc_html_e('Display Hero Section', 'campaign-office'); ?></strong>
            </label>
            <br>
            <span class="description"><?php esc_html_e('Show the hero section with video or image background on this page.', 'campaign-office'); ?></span>
        </p>

        <p>
            <label for="campaignpress_hero_video_url">
                <strong><?php esc_html_e('Hero Video URL', 'campaign-office'); ?></strong>
            </label><br>
            <input type="url" id="campaignpress_hero_video_url" name="campaignpress_hero_video_url" value="<?php echo esc_url($video_url); ?>" class="widefat" placeholder="https://example.com/video.mp4">
            <span class="description"><?php esc_html_e('Enter the URL of your hero video. If left empty, the featured image will be used instead. Recommended formats: MP4, WebM', 'campaign-office'); ?></span>
        </p>

        <p>
            <label for="campaignpress_hero_video_type">
                <strong><?php esc_html_e('Video MIME Type', 'campaign-office'); ?></strong>
            </label><br>
            <select id="campaignpress_hero_video_type" name="campaignpress_hero_video_type" class="widefat">
                <option value="video/mp4" <?php selected($video_type, 'video/mp4'); ?>>MP4 (video/mp4)</option>
                <option value="video/webm" <?php selected($video_type, 'video/webm'); ?>>WebM (video/webm)</option>
                <option value="video/ogg" <?php selected($video_type, 'video/ogg'); ?>>OGG (video/ogg)</option>
            </select>
            <span class="description"><?php esc_html_e('Select the video format type.', 'campaign-office'); ?></span>
        </p>

        <div class="notice notice-info inline">
            <p>
                <strong><?php esc_html_e('Tips for best results:', 'campaign-office'); ?></strong>
            </p>
            <ul style="margin-left: 20px; list-style-type: disc;">
                <li><?php esc_html_e('Keep video files under 5MB for faster loading', 'campaign-office'); ?></li>
                <li><?php esc_html_e('Use compressed, web-optimized video formats', 'campaign-office'); ?></li>
                <li><?php esc_html_e('Video will autoplay, loop, and be muted by default', 'campaign-office'); ?></li>
                <li><?php esc_html_e('Always set a featured image as a fallback', 'campaign-office'); ?></li>
            </ul>
        </div>
    </div>

    <style>
        .campaignpress-hero-video-settings p {
            margin-bottom: 15px;
        }
        .campaignpress-hero-video-settings .description {
            display: block;
            margin-top: 5px;
            font-style: italic;
            color: #666;
        }
    </style>
    <?php
}

/**
 * Save hero video meta
 */
function campaignpress_save_hero_video_meta($post_id) {
    // Check nonce
    if (!isset($_POST['campaignpress_hero_video_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['campaignpress_hero_video_meta_box_nonce'], 'campaignpress_hero_video_meta_box')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save show hero checkbox
    if (isset($_POST['campaignpress_show_hero'])) {
        update_post_meta($post_id, '_campaignpress_show_hero', '1');
    } else {
        update_post_meta($post_id, '_campaignpress_show_hero', '0');
    }

    // Save video URL
    if (isset($_POST['campaignpress_hero_video_url'])) {
        update_post_meta($post_id, '_campaignpress_hero_video_url', esc_url_raw($_POST['campaignpress_hero_video_url']));
    }

    // Save video type
    if (isset($_POST['campaignpress_hero_video_type'])) {
        update_post_meta($post_id, '_campaignpress_hero_video_type', sanitize_text_field($_POST['campaignpress_hero_video_type']));
    }
}
add_action('save_post', 'campaignpress_save_hero_video_meta');

/**
 * Get formatted event date/time
 *
 * @param int $post_id Event post ID
 * @return array
 */
function campaignpress_get_event_datetime($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $date = get_post_meta($post_id, '_cp_event_date', true);
    $time = get_post_meta($post_id, '_cp_event_time', true);

    $formatted = array(
        'date' => '',
        'time' => '',
        'datetime' => '',
    );

    if ($date) {
        $formatted['date'] = date_i18n(get_option('date_format'), strtotime($date));
        $formatted['datetime'] = $date;
    }

    if ($time) {
        $formatted['time'] = date_i18n(get_option('time_format'), strtotime($time));
    }

    return $formatted;
}

/**
 * Get event location
 *
 * @param int $post_id Event post ID
 * @return array
 */
function campaignpress_get_event_location($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    return array(
        'name' => get_post_meta($post_id, '_cp_event_location', true),
        'address' => get_post_meta($post_id, '_cp_event_address', true),
        'city' => get_post_meta($post_id, '_cp_event_city', true),
        'state' => get_post_meta($post_id, '_cp_event_state', true),
        'zip' => get_post_meta($post_id, '_cp_event_zip', true),
    );
}

/**
 * Display event details
 *
 * @param int $post_id Event post ID
 */
function campaignpress_display_event_details($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $datetime = campaignpress_get_event_datetime($post_id);
    $location = campaignpress_get_event_location($post_id);
    $rsvp_link = get_post_meta($post_id, '_cp_event_rsvp_link', true);

    ?>
    <div class="event-details">
        <?php if ($datetime['date']) : ?>
            <div class="event-date">
                <span class="dashicons dashicons-calendar-alt"></span>
                <strong><?php esc_html_e('Date:', 'campaign-office'); ?></strong>
                <time datetime="<?php echo esc_attr($datetime['datetime']); ?>">
                    <?php echo esc_html($datetime['date']); ?>
                    <?php if ($datetime['time']) : ?>
                        <?php esc_html_e('at', 'campaign-office'); ?> <?php echo esc_html($datetime['time']); ?>
                    <?php endif; ?>
                </time>
            </div>
        <?php endif; ?>

        <?php if ($location['name'] || $location['address']) : ?>
            <div class="event-location">
                <span class="dashicons dashicons-location"></span>
                <strong><?php esc_html_e('Location:', 'campaign-office'); ?></strong>
                <address>
                    <?php if ($location['name']) : ?>
                        <div class="location-name"><?php echo esc_html($location['name']); ?></div>
                    <?php endif; ?>
                    <?php if ($location['address']) : ?>
                        <div class="location-address">
                            <?php echo esc_html($location['address']); ?>
                            <?php if ($location['city'] || $location['state'] || $location['zip']) : ?>
                                <br>
                                <?php echo esc_html($location['city']); ?><?php echo $location['city'] && ($location['state'] || $location['zip']) ? ', ' : ''; ?>
                                <?php echo esc_html($location['state']); ?>
                                <?php echo esc_html($location['zip']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </address>
            </div>
        <?php endif; ?>

        <?php if ($rsvp_link) : ?>
            <div class="event-rsvp">
                <a href="<?php echo esc_url($rsvp_link); ?>" class="button button-primary" target="_blank" rel="noopener">
                    <?php esc_html_e('RSVP for this Event', 'campaign-office'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
