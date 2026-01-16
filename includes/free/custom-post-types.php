<?php
/**
 * Custom Post Types Registration
 *
 * Registers custom post types for campaign management.
 * This provides fallback CPT registration if Campaign Office Core plugin is not active.
 *
 * @package CampaignPress
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Campaign Custom Post Types
 */
function campaignpress_register_custom_post_types() {
    // Only register if plugin is NOT active
    if (class_exists('Campaign_Office_Core')) {
        return;
    }

    // Issues
    register_post_type('cp_issue', array(
        'labels' => array(
            'name' => __('Issues', 'campaign-office'),
            'singular_name' => __('Issue', 'campaign-office'),
            'menu_name' => __('Issues', 'campaign-office'),
            'add_new' => __('Add New', 'campaign-office'),
            'add_new_item' => __('Add New Issue', 'campaign-office'),
            'edit_item' => __('Edit Issue', 'campaign-office'),
            'new_item' => __('New Issue', 'campaign-office'),
            'view_item' => __('View Issue', 'campaign-office'),
            'search_items' => __('Search Issues', 'campaign-office'),
            'not_found' => __('No issues found', 'campaign-office'),
            'not_found_in_trash' => __('No issues found in trash', 'campaign-office'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'issues'),
        'menu_icon' => 'dashicons-flag',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
    ));

    // Events
    register_post_type('cp_event', array(
        'labels' => array(
            'name' => __('Events', 'campaign-office'),
            'singular_name' => __('Event', 'campaign-office'),
            'menu_name' => __('Events', 'campaign-office'),
            'add_new' => __('Add New', 'campaign-office'),
            'add_new_item' => __('Add New Event', 'campaign-office'),
            'edit_item' => __('Edit Event', 'campaign-office'),
            'new_item' => __('New Event', 'campaign-office'),
            'view_item' => __('View Event', 'campaign-office'),
            'search_items' => __('Search Events', 'campaign-office'),
            'not_found' => __('No events found', 'campaign-office'),
            'not_found_in_trash' => __('No events found in trash', 'campaign-office'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'events'),
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
    ));

    // Endorsements
    register_post_type('cp_endorsement', array(
        'labels' => array(
            'name' => __('Endorsements', 'campaign-office'),
            'singular_name' => __('Endorsement', 'campaign-office'),
            'menu_name' => __('Endorsements', 'campaign-office'),
            'add_new' => __('Add New', 'campaign-office'),
            'add_new_item' => __('Add New Endorsement', 'campaign-office'),
            'edit_item' => __('Edit Endorsement', 'campaign-office'),
            'new_item' => __('New Endorsement', 'campaign-office'),
            'view_item' => __('View Endorsement', 'campaign-office'),
            'search_items' => __('Search Endorsements', 'campaign-office'),
            'not_found' => __('No endorsements found', 'campaign-office'),
            'not_found_in_trash' => __('No endorsements found in trash', 'campaign-office'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'endorsements'),
        'menu_icon' => 'dashicons-thumbs-up',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
    ));

    // Team Members
    register_post_type('cp_team', array(
        'labels' => array(
            'name' => __('Team', 'campaign-office'),
            'singular_name' => __('Team Member', 'campaign-office'),
            'menu_name' => __('Team', 'campaign-office'),
            'add_new' => __('Add New', 'campaign-office'),
            'add_new_item' => __('Add New Team Member', 'campaign-office'),
            'edit_item' => __('Edit Team Member', 'campaign-office'),
            'new_item' => __('New Team Member', 'campaign-office'),
            'view_item' => __('View Team Member', 'campaign-office'),
            'search_items' => __('Search Team', 'campaign-office'),
            'not_found' => __('No team members found', 'campaign-office'),
            'not_found_in_trash' => __('No team members found in trash', 'campaign-office'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'team'),
        'menu_icon' => 'dashicons-groups',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
    ));

    // Volunteer Opportunities
    register_post_type('cp_volunteer', array(
        'labels' => array(
            'name' => __('Volunteers', 'campaign-office'),
            'singular_name' => __('Volunteer Opportunity', 'campaign-office'),
            'menu_name' => __('Volunteers', 'campaign-office'),
            'add_new' => __('Add New', 'campaign-office'),
            'add_new_item' => __('Add New Volunteer Opportunity', 'campaign-office'),
            'edit_item' => __('Edit Volunteer Opportunity', 'campaign-office'),
            'new_item' => __('New Volunteer Opportunity', 'campaign-office'),
            'view_item' => __('View Volunteer Opportunity', 'campaign-office'),
            'search_items' => __('Search Volunteers', 'campaign-office'),
            'not_found' => __('No volunteer opportunities found', 'campaign-office'),
            'not_found_in_trash' => __('No volunteer opportunities found in trash', 'campaign-office'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'volunteers'),
        'menu_icon' => 'dashicons-heart',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
    ));

    // Press Releases
    register_post_type('cp_press_release', array(
        'labels' => array(
            'name' => __('Press Releases', 'campaign-office'),
            'singular_name' => __('Press Release', 'campaign-office'),
            'menu_name' => __('Press Releases', 'campaign-office'),
            'add_new' => __('Add New', 'campaign-office'),
            'add_new_item' => __('Add New Press Release', 'campaign-office'),
            'edit_item' => __('Edit Press Release', 'campaign-office'),
            'new_item' => __('New Press Release', 'campaign-office'),
            'view_item' => __('View Press Release', 'campaign-office'),
            'search_items' => __('Search Press Releases', 'campaign-office'),
            'not_found' => __('No press releases found', 'campaign-office'),
            'not_found_in_trash' => __('No press releases found in trash', 'campaign-office'),
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'press-releases'),
        'menu_icon' => 'dashicons-media-document',
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'author', 'custom-fields'),
        'show_in_rest' => true,
        'capability_type' => 'post',
        'hierarchical' => false,
    ));
}
add_action('init', 'campaignpress_register_custom_post_types', 20);

/**
 * Add Endorsement Meta Box
 */
function campaignpress_add_endorsement_meta_box() {
    add_meta_box(
        'campaignpress_endorsement_details',
        __('Endorsement Details', 'campaign-office'),
        'campaignpress_endorsement_meta_box_callback',
        'cp_endorsement',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'campaignpress_add_endorsement_meta_box');

/**
 * Endorsement Meta Box Callback
 */
function campaignpress_endorsement_meta_box_callback($post) {
    wp_nonce_field('campaignpress_endorsement_meta_box', 'campaignpress_endorsement_meta_box_nonce');

    $endorser_title = get_post_meta($post->ID, '_cp_endorser_title', true);
    $endorser_organization = get_post_meta($post->ID, '_cp_endorser_organization', true);
    ?>
    <p>
        <label for="cp_endorser_title">
            <strong><?php esc_html_e('Title/Position', 'campaign-office'); ?></strong>
        </label><br>
        <input type="text" id="cp_endorser_title" name="cp_endorser_title"
               value="<?php echo esc_attr($endorser_title); ?>" class="widefat"
               placeholder="<?php esc_attr_e('e.g., Mayor, Senator, CEO', 'campaign-office'); ?>">
    </p>

    <p>
        <label for="cp_endorser_organization">
            <strong><?php esc_html_e('Organization', 'campaign-office'); ?></strong>
        </label><br>
        <input type="text" id="cp_endorser_organization" name="cp_endorser_organization"
               value="<?php echo esc_attr($endorser_organization); ?>" class="widefat"
               placeholder="<?php esc_attr_e('e.g., City Council, ABC Company', 'campaign-office'); ?>">
    </p>
    <?php
}

/**
 * Save Endorsement Meta
 */
function campaignpress_save_endorsement_meta($post_id) {
    // Check nonce
    if (!isset($_POST['campaignpress_endorsement_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['campaignpress_endorsement_meta_box_nonce'], 'campaignpress_endorsement_meta_box')) {
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

    // Save endorser title
    if (isset($_POST['cp_endorser_title'])) {
        update_post_meta($post_id, '_cp_endorser_title', sanitize_text_field($_POST['cp_endorser_title']));
    }

    // Save endorser organization
    if (isset($_POST['cp_endorser_organization'])) {
        update_post_meta($post_id, '_cp_endorser_organization', sanitize_text_field($_POST['cp_endorser_organization']));
    }
}
add_action('save_post_cp_endorsement', 'campaignpress_save_endorsement_meta');

/**
 * Add Team Meta Box
 */
function campaignpress_add_team_meta_box() {
    add_meta_box(
        'campaignpress_team_details',
        __('Team Member Details', 'campaign-office'),
        'campaignpress_team_meta_box_callback',
        'cp_team',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'campaignpress_add_team_meta_box');

/**
 * Team Meta Box Callback
 */
function campaignpress_team_meta_box_callback($post) {
    wp_nonce_field('campaignpress_team_meta_box', 'campaignpress_team_meta_box_nonce');

    $team_position = get_post_meta($post->ID, '_cp_team_position', true);
    $team_email = get_post_meta($post->ID, '_cp_team_email', true);
    $team_phone = get_post_meta($post->ID, '_cp_team_phone', true);
    ?>
    <p>
        <label for="cp_team_position">
            <strong><?php esc_html_e('Position', 'campaign-office'); ?></strong>
        </label><br>
        <input type="text" id="cp_team_position" name="cp_team_position"
               value="<?php echo esc_attr($team_position); ?>" class="widefat"
               placeholder="<?php esc_attr_e('e.g., Campaign Manager', 'campaign-office'); ?>">
    </p>

    <p>
        <label for="cp_team_email">
            <strong><?php esc_html_e('Email', 'campaign-office'); ?></strong>
        </label><br>
        <input type="email" id="cp_team_email" name="cp_team_email"
               value="<?php echo esc_attr($team_email); ?>" class="widefat"
               placeholder="<?php esc_attr_e('email@example.com', 'campaign-office'); ?>">
    </p>

    <p>
        <label for="cp_team_phone">
            <strong><?php esc_html_e('Phone', 'campaign-office'); ?></strong>
        </label><br>
        <input type="tel" id="cp_team_phone" name="cp_team_phone"
               value="<?php echo esc_attr($team_phone); ?>" class="widefat"
               placeholder="<?php esc_attr_e('(555) 123-4567', 'campaign-office'); ?>">
    </p>
    <?php
}

/**
 * Save Team Meta
 */
function campaignpress_save_team_meta($post_id) {
    // Check nonce
    if (!isset($_POST['campaignpress_team_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['campaignpress_team_meta_box_nonce'], 'campaignpress_team_meta_box')) {
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

    // Save team position
    if (isset($_POST['cp_team_position'])) {
        update_post_meta($post_id, '_cp_team_position', sanitize_text_field($_POST['cp_team_position']));
    }

    // Save team email
    if (isset($_POST['cp_team_email'])) {
        update_post_meta($post_id, '_cp_team_email', sanitize_email($_POST['cp_team_email']));
    }

    // Save team phone
    if (isset($_POST['cp_team_phone'])) {
        update_post_meta($post_id, '_cp_team_phone', sanitize_text_field($_POST['cp_team_phone']));
    }
}
add_action('save_post_cp_team', 'campaignpress_save_team_meta');

/**
 * Add Event Meta Box
 */
function campaignpress_add_event_meta_box() {
    add_meta_box(
        'campaignpress_event_details',
        __('Event Details', 'campaign-office'),
        'campaignpress_event_meta_box_callback',
        'cp_event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'campaignpress_add_event_meta_box');

/**
 * Event Meta Box Callback
 */
function campaignpress_event_meta_box_callback($post) {
    wp_nonce_field('campaignpress_event_meta_box', 'campaignpress_event_meta_box_nonce');

    $event_date = get_post_meta($post->ID, '_cp_event_date', true);
    $event_time = get_post_meta($post->ID, '_cp_event_time', true);
    $event_location = get_post_meta($post->ID, '_cp_event_location', true);
    $event_capacity = get_post_meta($post->ID, '_cp_event_capacity', true);
    ?>
    <div class="campaignpress-event-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <p>
            <label for="cp_event_date">
                <strong><?php esc_html_e('Date', 'campaign-office'); ?></strong>
            </label><br>
            <input type="date" id="cp_event_date" name="cp_event_date"
                   value="<?php echo esc_attr($event_date); ?>" class="widefat">
        </p>

        <p>
            <label for="cp_event_time">
                <strong><?php esc_html_e('Time', 'campaign-office'); ?></strong>
            </label><br>
            <input type="time" id="cp_event_time" name="cp_event_time"
                   value="<?php echo esc_attr($event_time); ?>" class="widefat">
        </p>
    </div>

    <p>
        <label for="cp_event_location">
            <strong><?php esc_html_e('Location', 'campaign-office'); ?></strong>
        </label><br>
        <input type="text" id="cp_event_location" name="cp_event_location"
               value="<?php echo esc_attr($event_location); ?>" class="widefat"
               placeholder="<?php esc_attr_e('e.g., Town Hall, 123 Main St', 'campaign-office'); ?>">
    </p>

    <p>
        <label for="cp_event_capacity">
            <strong><?php esc_html_e('Capacity', 'campaign-office'); ?></strong>
        </label><br>
        <input type="number" id="cp_event_capacity" name="cp_event_capacity"
               value="<?php echo esc_attr($event_capacity); ?>" class="widefat"
               min="1" placeholder="<?php esc_attr_e('Max attendees', 'campaign-office'); ?>">
    </p>
    <?php
}

/**
 * Save Event Meta
 */
function campaignpress_save_event_meta($post_id) {
    // Check nonce
    if (!isset($_POST['campaignpress_event_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['campaignpress_event_meta_box_nonce'], 'campaignpress_event_meta_box')) {
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

    // Save event date
    if (isset($_POST['cp_event_date'])) {
        update_post_meta($post_id, '_cp_event_date', sanitize_text_field($_POST['cp_event_date']));
    }

    // Save event time
    if (isset($_POST['cp_event_time'])) {
        update_post_meta($post_id, '_cp_event_time', sanitize_text_field($_POST['cp_event_time']));
    }

    // Save event location
    if (isset($_POST['cp_event_location'])) {
        update_post_meta($post_id, '_cp_event_location', sanitize_text_field($_POST['cp_event_location']));
    }

    // Save event capacity
    if (isset($_POST['cp_event_capacity'])) {
        update_post_meta($post_id, '_cp_event_capacity', absint($_POST['cp_event_capacity']));
    }
}
add_action('save_post_cp_event', 'campaignpress_save_event_meta');
