<?php
/**
 * Event Management Functions
 *
 * Handles event RSVPs, capacity tracking, and calendar integration.
 *
 * @package CampaignPress
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process event RSVP submission
 *
 * @param array $data Form data
 * @return int|WP_Error RSVP ID on success, WP_Error on failure
 */
function campaignpress_process_event_rsvp($data) {
    global $wpdb;

    $event_id = absint($data['event_id'] ?? 0);
    if (!$event_id || get_post_type($event_id) !== 'cp_event') {
        return new WP_Error('invalid_event', __('Invalid event.', 'campaignpress'));
    }

    // Sanitize inputs
    $first_name = sanitize_text_field($data['first_name'] ?? '');
    $last_name  = sanitize_text_field($data['last_name'] ?? '');
    $email      = sanitize_email($data['email'] ?? '');
    $phone      = sanitize_text_field($data['phone'] ?? '');
    $guests     = absint($data['guests'] ?? 0);
    $notes      = sanitize_textarea_field($data['notes'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email)) {
        return new WP_Error('missing_fields', __('Please fill in all required fields.', 'campaignpress'));
    }

    if (!is_email($email)) {
        return new WP_Error('invalid_email', __('Please enter a valid email address.', 'campaignpress'));
    }

    // Check capacity
    $max_attendees = get_post_meta($event_id, '_cp_event_capacity', true);
    if ($max_attendees) {
        $current_count = campaignpress_get_event_attendance_count($event_id);
        if ($current_count + 1 + $guests > $max_attendees) {
            return new WP_Error('event_full', __('Sorry, this event is full.', 'campaignpress'));
        }
    }

    // Central Contact Management
    global $cp_contact_manager;
    $contact_id = 0;
    if ($cp_contact_manager) {
        $contact_id = $cp_contact_manager->find_or_create(array(
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
            'phone'      => $phone,
        ));
    }

    $rsvps_table = $wpdb->prefix . 'cp_event_rsvps';

    // Check for duplicate RSVP
    $existing_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$rsvps_table} WHERE event_id = %d AND email = %s",
        $event_id,
        $email
    ));

    if ($existing_id) {
        return new WP_Error('duplicate_rsvp', __('You have already RSVPed for this event.', 'campaignpress'));
    }

    $rsvp_data = array(
        'event_id'   => $event_id,
        'contact_id' => $contact_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'email'      => $email,
        'phone'      => $phone,
        'guests'     => $guests,
        'status'     => 'confirmed',
        'notes'      => $notes,
        'created_at' => current_time('mysql'),
    );

    $result = $wpdb->insert($rsvps_table, $rsvp_data);

    if (false === $result) {
        return new WP_Error('db_error', __('Failed to save RSVP.', 'campaignpress'));
    }

    $rsvp_id = $wpdb->insert_id;

    // Update event RSVP count meta
    $count = campaignpress_get_event_attendance_count($event_id);
    update_post_meta($event_id, '_campaign_event_rsvp_count', $count);

    // Trigger action
    do_action('campaignpress_event_rsvp_created', $rsvp_id, $event_id, $rsvp_data);

    return $rsvp_id;
}

/**
 * Get total attendance count for an event
 *
 * @param int $event_id
 * @return int
 */
function campaignpress_get_event_attendance_count($event_id) {
    global $wpdb;
    $rsvps_table = $wpdb->prefix . 'cp_event_rsvps';
    
    $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $rsvps_table));
    if (!$exists) return 0;

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(1 + guests) FROM {$rsvps_table} WHERE event_id = %d AND status = 'confirmed'",
        $event_id
    ));
}

/**
 * Handle event RSVP AJAX request
 */
function campaignpress_ajax_event_rsvp() {
    check_ajax_referer('cp_event_rsvp', 'nonce');

    $result = campaignpress_process_event_rsvp($_POST);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    wp_send_json_success(array(
        'message' => __('Thank you for your RSVP!', 'campaignpress'),
        'rsvp_id' => $result
    ));
}
add_action('wp_ajax_cp_event_rsvp', 'campaignpress_ajax_event_rsvp');
add_action('wp_ajax_nopriv_cp_event_rsvp', 'campaignpress_ajax_event_rsvp');
