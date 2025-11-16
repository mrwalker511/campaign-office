<?php
/**
 * Custom Post Types for CampaignPress
 *
 * Registers political-specific custom post types:
 * - Issues/Policy Positions
 * - Events
 * - Endorsements
 * - Team Members
 * - Volunteer Opportunities
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Issues Custom Post Type
 */
function campaignpress_register_issues_post_type() {
    $labels = array(
        'name'                  => _x('Issues', 'Post Type General Name', 'campaignpress'),
        'singular_name'         => _x('Issue', 'Post Type Singular Name', 'campaignpress'),
        'menu_name'             => __('Issues', 'campaignpress'),
        'name_admin_bar'        => __('Issue', 'campaignpress'),
        'archives'              => __('Issue Archives', 'campaignpress'),
        'attributes'            => __('Issue Attributes', 'campaignpress'),
        'parent_item_colon'     => __('Parent Issue:', 'campaignpress'),
        'all_items'             => __('All Issues', 'campaignpress'),
        'add_new_item'          => __('Add New Issue', 'campaignpress'),
        'add_new'               => __('Add New', 'campaignpress'),
        'new_item'              => __('New Issue', 'campaignpress'),
        'edit_item'             => __('Edit Issue', 'campaignpress'),
        'update_item'           => __('Update Issue', 'campaignpress'),
        'view_item'             => __('View Issue', 'campaignpress'),
        'view_items'            => __('View Issues', 'campaignpress'),
        'search_items'          => __('Search Issue', 'campaignpress'),
        'not_found'             => __('Not found', 'campaignpress'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress'),
        'featured_image'        => __('Featured Image', 'campaignpress'),
        'set_featured_image'    => __('Set featured image', 'campaignpress'),
        'remove_featured_image' => __('Remove featured image', 'campaignpress'),
        'use_featured_image'    => __('Use as featured image', 'campaignpress'),
        'insert_into_item'      => __('Insert into issue', 'campaignpress'),
        'uploaded_to_this_item' => __('Uploaded to this issue', 'campaignpress'),
        'items_list'            => __('Issues list', 'campaignpress'),
        'items_list_navigation' => __('Issues list navigation', 'campaignpress'),
        'filter_items_list'     => __('Filter issues list', 'campaignpress'),
    );

    $args = array(
        'label'                 => __('Issue', 'campaignpress'),
        'description'           => __('Policy positions and campaign issues', 'campaignpress'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields'),
        'taxonomies'            => array('issue_category'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-megaphone',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'issues',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('cp_issue', $args);

    // Register Issue Categories taxonomy
    $tax_labels = array(
        'name'              => _x('Issue Categories', 'taxonomy general name', 'campaignpress'),
        'singular_name'     => _x('Issue Category', 'taxonomy singular name', 'campaignpress'),
        'search_items'      => __('Search Issue Categories', 'campaignpress'),
        'all_items'         => __('All Issue Categories', 'campaignpress'),
        'parent_item'       => __('Parent Issue Category', 'campaignpress'),
        'parent_item_colon' => __('Parent Issue Category:', 'campaignpress'),
        'edit_item'         => __('Edit Issue Category', 'campaignpress'),
        'update_item'       => __('Update Issue Category', 'campaignpress'),
        'add_new_item'      => __('Add New Issue Category', 'campaignpress'),
        'new_item_name'     => __('New Issue Category Name', 'campaignpress'),
        'menu_name'         => __('Categories', 'campaignpress'),
    );

    $tax_args = array(
        'hierarchical'      => true,
        'labels'            => $tax_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'issue-category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('issue_category', array('cp_issue'), $tax_args);
}
add_action('init', 'campaignpress_register_issues_post_type', 0);

/**
 * Register Events Custom Post Type
 */
function campaignpress_register_events_post_type() {
    $labels = array(
        'name'                  => _x('Events', 'Post Type General Name', 'campaignpress'),
        'singular_name'         => _x('Event', 'Post Type Singular Name', 'campaignpress'),
        'menu_name'             => __('Events', 'campaignpress'),
        'name_admin_bar'        => __('Event', 'campaignpress'),
        'archives'              => __('Event Archives', 'campaignpress'),
        'attributes'            => __('Event Attributes', 'campaignpress'),
        'parent_item_colon'     => __('Parent Event:', 'campaignpress'),
        'all_items'             => __('All Events', 'campaignpress'),
        'add_new_item'          => __('Add New Event', 'campaignpress'),
        'add_new'               => __('Add New', 'campaignpress'),
        'new_item'              => __('New Event', 'campaignpress'),
        'edit_item'             => __('Edit Event', 'campaignpress'),
        'update_item'           => __('Update Event', 'campaignpress'),
        'view_item'             => __('View Event', 'campaignpress'),
        'view_items'            => __('View Events', 'campaignpress'),
        'search_items'          => __('Search Event', 'campaignpress'),
        'not_found'             => __('Not found', 'campaignpress'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress'),
        'featured_image'        => __('Event Image', 'campaignpress'),
        'set_featured_image'    => __('Set event image', 'campaignpress'),
        'remove_featured_image' => __('Remove event image', 'campaignpress'),
        'use_featured_image'    => __('Use as event image', 'campaignpress'),
        'insert_into_item'      => __('Insert into event', 'campaignpress'),
        'uploaded_to_this_item' => __('Uploaded to this event', 'campaignpress'),
        'items_list'            => __('Events list', 'campaignpress'),
        'items_list_navigation' => __('Events list navigation', 'campaignpress'),
        'filter_items_list'     => __('Filter events list', 'campaignpress'),
    );

    $args = array(
        'label'                 => __('Event', 'campaignpress'),
        'description'           => __('Campaign events and appearances', 'campaignpress'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'taxonomies'            => array('event_type'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 21,
        'menu_icon'             => 'dashicons-calendar-alt',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'events',
    );

    register_post_type('cp_event', $args);

    // Register Event Type taxonomy
    $tax_labels = array(
        'name'              => _x('Event Types', 'taxonomy general name', 'campaignpress'),
        'singular_name'     => _x('Event Type', 'taxonomy singular name', 'campaignpress'),
        'search_items'      => __('Search Event Types', 'campaignpress'),
        'all_items'         => __('All Event Types', 'campaignpress'),
        'edit_item'         => __('Edit Event Type', 'campaignpress'),
        'update_item'       => __('Update Event Type', 'campaignpress'),
        'add_new_item'      => __('Add New Event Type', 'campaignpress'),
        'new_item_name'     => __('New Event Type Name', 'campaignpress'),
        'menu_name'         => __('Event Types', 'campaignpress'),
    );

    $tax_args = array(
        'hierarchical'      => false,
        'labels'            => $tax_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'event-type'),
        'show_in_rest'      => true,
    );

    register_taxonomy('event_type', array('cp_event'), $tax_args);
}
add_action('init', 'campaignpress_register_events_post_type', 0);

/**
 * Register Endorsements Custom Post Type
 */
function campaignpress_register_endorsements_post_type() {
    $labels = array(
        'name'                  => _x('Endorsements', 'Post Type General Name', 'campaignpress'),
        'singular_name'         => _x('Endorsement', 'Post Type Singular Name', 'campaignpress'),
        'menu_name'             => __('Endorsements', 'campaignpress'),
        'name_admin_bar'        => __('Endorsement', 'campaignpress'),
        'archives'              => __('Endorsement Archives', 'campaignpress'),
        'attributes'            => __('Endorsement Attributes', 'campaignpress'),
        'parent_item_colon'     => __('Parent Endorsement:', 'campaignpress'),
        'all_items'             => __('All Endorsements', 'campaignpress'),
        'add_new_item'          => __('Add New Endorsement', 'campaignpress'),
        'add_new'               => __('Add New', 'campaignpress'),
        'new_item'              => __('New Endorsement', 'campaignpress'),
        'edit_item'             => __('Edit Endorsement', 'campaignpress'),
        'update_item'           => __('Update Endorsement', 'campaignpress'),
        'view_item'             => __('View Endorsement', 'campaignpress'),
        'view_items'            => __('View Endorsements', 'campaignpress'),
        'search_items'          => __('Search Endorsement', 'campaignpress'),
        'not_found'             => __('Not found', 'campaignpress'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress'),
        'featured_image'        => __('Endorser Photo', 'campaignpress'),
        'set_featured_image'    => __('Set endorser photo', 'campaignpress'),
        'remove_featured_image' => __('Remove endorser photo', 'campaignpress'),
        'use_featured_image'    => __('Use as endorser photo', 'campaignpress'),
        'insert_into_item'      => __('Insert into endorsement', 'campaignpress'),
        'uploaded_to_this_item' => __('Uploaded to this endorsement', 'campaignpress'),
        'items_list'            => __('Endorsements list', 'campaignpress'),
        'items_list_navigation' => __('Endorsements list navigation', 'campaignpress'),
        'filter_items_list'     => __('Filter endorsements list', 'campaignpress'),
    );

    $args = array(
        'label'                 => __('Endorsement', 'campaignpress'),
        'description'           => __('Campaign endorsements', 'campaignpress'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-thumbs-up',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'endorsements',
    );

    register_post_type('cp_endorsement', $args);
}
add_action('init', 'campaignpress_register_endorsements_post_type', 0);

/**
 * Register Team Members Custom Post Type
 */
function campaignpress_register_team_post_type() {
    $labels = array(
        'name'                  => _x('Team Members', 'Post Type General Name', 'campaignpress'),
        'singular_name'         => _x('Team Member', 'Post Type Singular Name', 'campaignpress'),
        'menu_name'             => __('Team', 'campaignpress'),
        'name_admin_bar'        => __('Team Member', 'campaignpress'),
        'archives'              => __('Team Member Archives', 'campaignpress'),
        'attributes'            => __('Team Member Attributes', 'campaignpress'),
        'parent_item_colon'     => __('Parent Team Member:', 'campaignpress'),
        'all_items'             => __('All Team Members', 'campaignpress'),
        'add_new_item'          => __('Add New Team Member', 'campaignpress'),
        'add_new'               => __('Add New', 'campaignpress'),
        'new_item'              => __('New Team Member', 'campaignpress'),
        'edit_item'             => __('Edit Team Member', 'campaignpress'),
        'update_item'           => __('Update Team Member', 'campaignpress'),
        'view_item'             => __('View Team Member', 'campaignpress'),
        'view_items'            => __('View Team Members', 'campaignpress'),
        'search_items'          => __('Search Team Member', 'campaignpress'),
        'not_found'             => __('Not found', 'campaignpress'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress'),
        'featured_image'        => __('Team Member Photo', 'campaignpress'),
        'set_featured_image'    => __('Set photo', 'campaignpress'),
        'remove_featured_image' => __('Remove photo', 'campaignpress'),
        'use_featured_image'    => __('Use as photo', 'campaignpress'),
        'insert_into_item'      => __('Insert into team member', 'campaignpress'),
        'uploaded_to_this_item' => __('Uploaded to this team member', 'campaignpress'),
        'items_list'            => __('Team members list', 'campaignpress'),
        'items_list_navigation' => __('Team members list navigation', 'campaignpress'),
        'filter_items_list'     => __('Filter team members list', 'campaignpress'),
    );

    $args = array(
        'label'                 => __('Team Member', 'campaignpress'),
        'description'           => __('Campaign team members and staff', 'campaignpress'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 23,
        'menu_icon'             => 'dashicons-groups',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'team',
    );

    register_post_type('cp_team', $args);
}
add_action('init', 'campaignpress_register_team_post_type', 0);

/**
 * Register Volunteer Opportunities Custom Post Type
 */
function campaignpress_register_volunteer_post_type() {
    $labels = array(
        'name'                  => _x('Volunteer Opportunities', 'Post Type General Name', 'campaignpress'),
        'singular_name'         => _x('Volunteer Opportunity', 'Post Type Singular Name', 'campaignpress'),
        'menu_name'             => __('Volunteer Opportunities', 'campaignpress'),
        'name_admin_bar'        => __('Volunteer Opportunity', 'campaignpress'),
        'archives'              => __('Volunteer Opportunity Archives', 'campaignpress'),
        'attributes'            => __('Volunteer Opportunity Attributes', 'campaignpress'),
        'parent_item_colon'     => __('Parent Opportunity:', 'campaignpress'),
        'all_items'             => __('All Opportunities', 'campaignpress'),
        'add_new_item'          => __('Add New Opportunity', 'campaignpress'),
        'add_new'               => __('Add New', 'campaignpress'),
        'new_item'              => __('New Opportunity', 'campaignpress'),
        'edit_item'             => __('Edit Opportunity', 'campaignpress'),
        'update_item'           => __('Update Opportunity', 'campaignpress'),
        'view_item'             => __('View Opportunity', 'campaignpress'),
        'view_items'            => __('View Opportunities', 'campaignpress'),
        'search_items'          => __('Search Opportunity', 'campaignpress'),
        'not_found'             => __('Not found', 'campaignpress'),
        'not_found_in_trash'    => __('Not found in Trash', 'campaignpress'),
        'featured_image'        => __('Opportunity Image', 'campaignpress'),
        'set_featured_image'    => __('Set opportunity image', 'campaignpress'),
        'remove_featured_image' => __('Remove opportunity image', 'campaignpress'),
        'use_featured_image'    => __('Use as opportunity image', 'campaignpress'),
        'insert_into_item'      => __('Insert into opportunity', 'campaignpress'),
        'uploaded_to_this_item' => __('Uploaded to this opportunity', 'campaignpress'),
        'items_list'            => __('Opportunities list', 'campaignpress'),
        'items_list_navigation' => __('Opportunities list navigation', 'campaignpress'),
        'filter_items_list'     => __('Filter opportunities list', 'campaignpress'),
    );

    $args = array(
        'label'                 => __('Volunteer Opportunity', 'campaignpress'),
        'description'           => __('Volunteer opportunities and positions', 'campaignpress'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 24,
        'menu_icon'             => 'dashicons-heart',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'volunteer-opportunities',
    );

    register_post_type('cp_volunteer', $args);
}
add_action('init', 'campaignpress_register_volunteer_post_type', 0);

/**
 * Add custom meta boxes for event details
 */
function campaignpress_add_event_meta_boxes() {
    add_meta_box(
        'cp_event_details',
        __('Event Details', 'campaignpress'),
        'campaignpress_event_details_callback',
        'cp_event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'campaignpress_add_event_meta_boxes');

/**
 * Event details meta box callback
 */
function campaignpress_event_details_callback($post) {
    wp_nonce_field('campaignpress_event_details_nonce', 'campaignpress_event_details_nonce_field');

    $event_date = get_post_meta($post->ID, '_cp_event_date', true);
    $event_time = get_post_meta($post->ID, '_cp_event_time', true);
    $event_location = get_post_meta($post->ID, '_cp_event_location', true);
    $event_address = get_post_meta($post->ID, '_cp_event_address', true);
    $event_city = get_post_meta($post->ID, '_cp_event_city', true);
    $event_state = get_post_meta($post->ID, '_cp_event_state', true);
    $event_zip = get_post_meta($post->ID, '_cp_event_zip', true);
    $event_rsvp_link = get_post_meta($post->ID, '_cp_event_rsvp_link', true);

    ?>
    <p>
        <label for="cp_event_date"><strong><?php esc_html_e('Event Date:', 'campaignpress'); ?></strong></label><br>
        <input type="date" id="cp_event_date" name="cp_event_date" value="<?php echo esc_attr($event_date); ?>" style="width: 100%; max-width: 300px;">
    </p>
    <p>
        <label for="cp_event_time"><strong><?php esc_html_e('Event Time:', 'campaignpress'); ?></strong></label><br>
        <input type="time" id="cp_event_time" name="cp_event_time" value="<?php echo esc_attr($event_time); ?>" style="width: 100%; max-width: 300px;">
    </p>
    <p>
        <label for="cp_event_location"><strong><?php esc_html_e('Location Name:', 'campaignpress'); ?></strong></label><br>
        <input type="text" id="cp_event_location" name="cp_event_location" value="<?php echo esc_attr($event_location); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_address"><strong><?php esc_html_e('Street Address:', 'campaignpress'); ?></strong></label><br>
        <input type="text" id="cp_event_address" name="cp_event_address" value="<?php echo esc_attr($event_address); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_city"><strong><?php esc_html_e('City:', 'campaignpress'); ?></strong></label><br>
        <input type="text" id="cp_event_city" name="cp_event_city" value="<?php echo esc_attr($event_city); ?>" style="width: 100%;">
    </p>
    <p>
        <label for="cp_event_state"><strong><?php esc_html_e('State:', 'campaignpress'); ?></strong></label><br>
        <input type="text" id="cp_event_state" name="cp_event_state" value="<?php echo esc_attr($event_state); ?>" maxlength="2" style="width: 100px;">
    </p>
    <p>
        <label for="cp_event_zip"><strong><?php esc_html_e('ZIP Code:', 'campaignpress'); ?></strong></label><br>
        <input type="text" id="cp_event_zip" name="cp_event_zip" value="<?php echo esc_attr($event_zip); ?>" style="width: 150px;">
    </p>
    <p>
        <label for="cp_event_rsvp_link"><strong><?php esc_html_e('RSVP Link:', 'campaignpress'); ?></strong></label><br>
        <input type="url" id="cp_event_rsvp_link" name="cp_event_rsvp_link" value="<?php echo esc_url($event_rsvp_link); ?>" style="width: 100%;" placeholder="https://">
    </p>
    <?php
}

/**
 * Save event meta data
 */
function campaignpress_save_event_meta($post_id) {
    // Check nonce
    if (!isset($_POST['campaignpress_event_details_nonce_field']) ||
        !wp_verify_nonce($_POST['campaignpress_event_details_nonce_field'], 'campaignpress_event_details_nonce')) {
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

    // Define allowed sanitization callbacks (whitelist for security)
    $allowed_callbacks = array('sanitize_text_field', 'esc_url_raw');

    // Save fields with validated callbacks
    $fields = array(
        'cp_event_date' => 'sanitize_text_field',
        'cp_event_time' => 'sanitize_text_field',
        'cp_event_location' => 'sanitize_text_field',
        'cp_event_address' => 'sanitize_text_field',
        'cp_event_city' => 'sanitize_text_field',
        'cp_event_state' => 'sanitize_text_field',
        'cp_event_zip' => 'sanitize_text_field',
        'cp_event_rsvp_link' => 'esc_url_raw',
    );

    foreach ($fields as $field => $sanitize_callback) {
        if (isset($_POST[$field]) && in_array($sanitize_callback, $allowed_callbacks, true)) {
            update_post_meta($post_id, '_' . $field, call_user_func($sanitize_callback, $_POST[$field]));
        }
    }
}
add_action('save_post_cp_event', 'campaignpress_save_event_meta');

/**
 * Flush rewrite rules on theme activation
 *
 * Note: Moved to functions.php using after_setup_theme hook
 * because register_activation_hook() doesn't work in themes.
 */
