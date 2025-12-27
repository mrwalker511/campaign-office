<?php
/**
 * Custom template tags for CampaignPress
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display event details
 *
 * Optimized to use single meta query instead of 8 separate queries
 */
function campaignpress_event_details() {
    if ('cp_event' !== get_post_type()) {
        return;
    }

    // Single query to get all post meta (performance optimization)
    $post_id = get_the_ID();
    $event_meta = get_post_meta($post_id);

    // Extract meta values with defaults
    $event_date = isset($event_meta['_cp_event_date'][0]) ? $event_meta['_cp_event_date'][0] : '';
    $event_time = isset($event_meta['_cp_event_time'][0]) ? $event_meta['_cp_event_time'][0] : '';
    $event_location = isset($event_meta['_cp_event_location'][0]) ? $event_meta['_cp_event_location'][0] : '';
    $event_address = isset($event_meta['_cp_event_address'][0]) ? $event_meta['_cp_event_address'][0] : '';
    $event_city = isset($event_meta['_cp_event_city'][0]) ? $event_meta['_cp_event_city'][0] : '';
    $event_state = isset($event_meta['_cp_event_state'][0]) ? $event_meta['_cp_event_state'][0] : '';
    $event_zip = isset($event_meta['_cp_event_zip'][0]) ? $event_meta['_cp_event_zip'][0] : '';
    $event_rsvp_link = isset($event_meta['_cp_event_rsvp_link'][0]) ? $event_meta['_cp_event_rsvp_link'][0] : '';

    if (!$event_date && !$event_location) {
        return;
    }

    ?>
    <div class="cp-event-details event-meta">
        <?php if ($event_date || $event_time) : ?>
            <div class="cp-event-datetime">
                <?php echo campaignpress_get_ui_icon('calendar', array('aria-hidden' => 'true')); ?>
                <?php
                // Display date with timezone awareness and format validation
                if ($event_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
                    $timestamp = strtotime($event_date . ' midnight', current_time('timestamp'));
                    if ($timestamp !== false) {
                        echo '<strong>' . esc_html(date_i18n(get_option('date_format'), $timestamp)) . '</strong>';
                    }
                }

                // Display time using DateTime for proper HH:MM parsing
                if ($event_time) {
                    $time_obj = false;
                    if (preg_match('/^\d{2}:\d{2}$/', $event_time)) {
                        $time_obj = DateTime::createFromFormat('H:i', $event_time);
                    }

                    if ($time_obj) {
                        echo ' ' . esc_html__('at', 'campaign-office') . ' ' . esc_html($time_obj->format(get_option('time_format')));
                    } else {
                        // Fallback for other formats
                        echo ' ' . esc_html__('at', 'campaign-office') . ' ' . esc_html(date_i18n(get_option('time_format'), strtotime($event_time)));
                    }
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ($event_location || $event_address) : ?>
            <div class="cp-event-location">
                <?php echo campaignpress_get_ui_icon('location', array('aria-hidden' => 'true')); ?>
                <?php
                if ($event_location) {
                    echo '<strong>' . esc_html($event_location) . '</strong><br>';
                }
                if ($event_address) {
                    echo esc_html($event_address) . '<br>';
                }
                if ($event_city || $event_state || $event_zip) {
                    echo esc_html($event_city);
                    if ($event_state) {
                        echo ', ' . esc_html($event_state);
                    }
                    if ($event_zip) {
                        echo ' ' . esc_html($event_zip);
                    }
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ($event_rsvp_link) : ?>
            <div class="cp-event-rsvp">
                <a href="<?php echo esc_url($event_rsvp_link); ?>" class="cp-button cp-button-primary" target="_blank" rel="noopener">
                    <?php esc_html_e('RSVP Now', 'campaign-office'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Display posted on date
 */
function campaignpress_posted_on() {
    $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
    if (get_the_time('U') !== get_the_modified_time('U')) {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated" datetime="%3$s">%4$s</time>';
    }

    $time_string = sprintf(
        $time_string,
        esc_attr(get_the_date(DATE_W3C)),
        esc_html(get_the_date()),
        esc_attr(get_the_modified_date(DATE_W3C)),
        esc_html(get_the_modified_date())
    );

    $posted_on = sprintf(
        /* translators: %s: post date. */
        esc_html_x('Posted on %s', 'post date', 'campaign-office'),
        '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>'
    );

    echo '<span class="posted-on">' . $posted_on . '</span>';
}

/**
 * Display post author
 */
function campaignpress_posted_by() {
    $byline = sprintf(
        /* translators: %s: post author. */
        esc_html_x('by %s', 'post author', 'campaign-office'),
        '<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
    );

    echo '<span class="byline"> ' . $byline . '</span>';
}

/**
 * Display entry footer metadata
 */
function campaignpress_entry_footer() {
    // Hide category and tag text for pages.
    if ('post' === get_post_type()) {
        /* translators: used between list items, there is a space after the comma */
        $categories_list = get_the_category_list(esc_html__(', ', 'campaign-office'));
        if ($categories_list) {
            /* translators: 1: list of categories. */
            printf('<span class="cat-links">' . esc_html__('Posted in %1$s', 'campaign-office') . '</span>', $categories_list);
        }

        /* translators: used between list items, there is a space after the comma */
        $tags_list = get_the_tag_list('', esc_html_x(', ', 'list item separator', 'campaign-office'));
        if ($tags_list) {
            /* translators: 1: list of tags. */
            printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'campaign-office') . '</span>', $tags_list);
        }
    }

    if (!is_single() && !post_password_required() && (comments_open() || get_comments_number())) {
        echo '<span class="comments-link">';
        comments_popup_link(
            sprintf(
                wp_kses(
                    /* translators: %s: post title */
                    __('Leave a Comment<span class="screen-reader-text"> on %s</span>', 'campaign-office'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            )
        );
        echo '</span>';
    }

    edit_post_link(
        sprintf(
            wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
                __('Edit <span class="screen-reader-text">%s</span>', 'campaign-office'),
                array(
                    'span' => array(
                        'class' => array(),
                    ),
                )
            ),
            wp_kses_post(get_the_title())
        ),
        '<span class="edit-link">',
        '</span>'
    );
}

/**
 * Get social media URLs with caching
 * Uses static caching to prevent multiple database queries
 *
 * @return array Associative array of social media URLs
 */
function campaignpress_get_social_urls() {
    static $social_urls = null;

    if ($social_urls === null) {
        $social_urls = array(
            'facebook'  => get_theme_mod('campaignpress_facebook_url', ''),
            'twitter'   => get_theme_mod('campaignpress_twitter_url', ''),
            'instagram' => get_theme_mod('campaignpress_instagram_url', ''),
            'youtube'   => get_theme_mod('campaignpress_youtube_url', ''),
            'linkedin'  => get_theme_mod('campaignpress_linkedin_url', ''),
        );
    }

    return $social_urls;
}

/**
 * Display social media links
 * Optimized to reduce database queries from 5 to 1 (per page load)
 */
function campaignpress_social_links() {
    $social_urls = campaignpress_get_social_urls();

    // Check if any social URLs are set
    if (!array_filter($social_urls)) {
        return;
    }

    echo '<div class="cp-social-links social-links">';

    if ($social_urls['facebook']) {
        echo '<a href="' . esc_url($social_urls['facebook']) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('Facebook', 'campaign-office') . '">';
        echo campaignpress_get_social_heroicon('facebook', array('aria-hidden' => 'true'));
        echo '</a>';
    }
    if ($social_urls['twitter']) {
        echo '<a href="' . esc_url($social_urls['twitter']) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('Twitter', 'campaign-office') . '">';
        echo campaignpress_get_social_heroicon('twitter', array('aria-hidden' => 'true'));
        echo '</a>';
    }
    if ($social_urls['instagram']) {
        echo '<a href="' . esc_url($social_urls['instagram']) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('Instagram', 'campaign-office') . '">';
        echo campaignpress_get_social_heroicon('instagram', array('aria-hidden' => 'true'));
        echo '</a>';
    }
    if ($social_urls['youtube']) {
        echo '<a href="' . esc_url($social_urls['youtube']) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('YouTube', 'campaign-office') . '">';
        echo campaignpress_get_social_heroicon('youtube', array('aria-hidden' => 'true'));
        echo '</a>';
    }
    if ($social_urls['linkedin']) {
        echo '<a href="' . esc_url($social_urls['linkedin']) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('LinkedIn', 'campaign-office') . '">';
        echo campaignpress_get_social_heroicon('linkedin', array('aria-hidden' => 'true'));
        echo '</a>';
    }

    echo '</div>';
}
