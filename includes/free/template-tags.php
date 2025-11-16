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
 */
function campaignpress_event_details() {
    if ('cp_event' !== get_post_type()) {
        return;
    }

    $event_date = get_post_meta(get_the_ID(), '_cp_event_date', true);
    $event_time = get_post_meta(get_the_ID(), '_cp_event_time', true);
    $event_location = get_post_meta(get_the_ID(), '_cp_event_location', true);
    $event_address = get_post_meta(get_the_ID(), '_cp_event_address', true);
    $event_city = get_post_meta(get_the_ID(), '_cp_event_city', true);
    $event_state = get_post_meta(get_the_ID(), '_cp_event_state', true);
    $event_zip = get_post_meta(get_the_ID(), '_cp_event_zip', true);
    $event_rsvp_link = get_post_meta(get_the_ID(), '_cp_event_rsvp_link', true);

    if (!$event_date && !$event_location) {
        return;
    }

    ?>
    <div class="cp-event-details">
        <?php if ($event_date || $event_time) : ?>
            <div class="cp-event-datetime">
                <span class="dashicons dashicons-calendar-alt"></span>
                <?php
                if ($event_date) {
                    echo '<strong>' . esc_html(date_i18n(get_option('date_format'), strtotime($event_date))) . '</strong>';
                }
                if ($event_time) {
                    echo ' ' . esc_html__('at', 'campaignpress') . ' ' . esc_html(date_i18n(get_option('time_format'), strtotime($event_time)));
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if ($event_location || $event_address) : ?>
            <div class="cp-event-location">
                <span class="dashicons dashicons-location"></span>
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
                    <?php esc_html_e('RSVP Now', 'campaignpress'); ?>
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
        esc_html_x('Posted on %s', 'post date', 'campaignpress'),
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
        esc_html_x('by %s', 'post author', 'campaignpress'),
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
        $categories_list = get_the_category_list(esc_html__(', ', 'campaignpress'));
        if ($categories_list) {
            /* translators: 1: list of categories. */
            printf('<span class="cat-links">' . esc_html__('Posted in %1$s', 'campaignpress') . '</span>', $categories_list);
        }

        /* translators: used between list items, there is a space after the comma */
        $tags_list = get_the_tag_list('', esc_html_x(', ', 'list item separator', 'campaignpress'));
        if ($tags_list) {
            /* translators: 1: list of tags. */
            printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'campaignpress') . '</span>', $tags_list);
        }
    }

    if (!is_single() && !post_password_required() && (comments_open() || get_comments_number())) {
        echo '<span class="comments-link">';
        comments_popup_link(
            sprintf(
                wp_kses(
                    /* translators: %s: post title */
                    __('Leave a Comment<span class="screen-reader-text"> on %s</span>', 'campaignpress'),
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
                __('Edit <span class="screen-reader-text">%s</span>', 'campaignpress'),
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
 * Display social media links
 */
function campaignpress_social_links() {
    $facebook = get_theme_mod('campaignpress_facebook_url', '');
    $twitter = get_theme_mod('campaignpress_twitter_url', '');
    $instagram = get_theme_mod('campaignpress_instagram_url', '');
    $youtube = get_theme_mod('campaignpress_youtube_url', '');
    $linkedin = get_theme_mod('campaignpress_linkedin_url', '');

    if (!$facebook && !$twitter && !$instagram && !$youtube && !$linkedin) {
        return;
    }

    echo '<div class="cp-social-links">';

    if ($facebook) {
        echo '<a href="' . esc_url($facebook) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('Facebook', 'campaignpress') . '"><span class="dashicons dashicons-facebook"></span></a>';
    }
    if ($twitter) {
        echo '<a href="' . esc_url($twitter) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('Twitter', 'campaignpress') . '"><span class="dashicons dashicons-twitter"></span></a>';
    }
    if ($instagram) {
        echo '<a href="' . esc_url($instagram) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('Instagram', 'campaignpress') . '"><span class="dashicons dashicons-instagram"></span></a>';
    }
    if ($youtube) {
        echo '<a href="' . esc_url($youtube) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('YouTube', 'campaignpress') . '"><span class="dashicons dashicons-video-alt3"></span></a>';
    }
    if ($linkedin) {
        echo '<a href="' . esc_url($linkedin) . '" target="_blank" rel="noopener" aria-label="' . esc_attr__('LinkedIn', 'campaignpress') . '"><span class="dashicons dashicons-linkedin"></span></a>';
    }

    echo '</div>';
}
