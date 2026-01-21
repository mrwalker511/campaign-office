<?php
/**
 * Volunteer Management Functions
 *
 * Handles volunteer signup processing, status management, and integration.
 *
 * @package CampaignPress
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Process volunteer signup form submission
 *
 * @param array $data Form data
 * @return int|WP_Error Volunteer ID on success, WP_Error on failure
 */
function campaignpress_process_volunteer_signup($data) {
    global $wpdb;

    // Verify nonce
    if (!isset($data['nonce']) || !wp_verify_nonce($data['nonce'], 'cp_volunteer_signup_nonce')) {
        return new WP_Error('security_check_failed', __('Security check failed.', 'campaignpress'));
    }

    // Sanitize inputs
    $first_name = sanitize_text_field($data['first_name'] ?? '');
    $last_name  = sanitize_text_field($data['last_name'] ?? '');
    $email      = sanitize_email($data['email'] ?? '');
    $phone      = sanitize_text_field($data['phone'] ?? '');
    $zip        = sanitize_text_field($data['zip'] ?? $data['volunteer_zip'] ?? '');
    $interests  = isset($data['interests']) ? (array) $data['interests'] : (isset($data['volunteer_interests']) ? (array) $data['volunteer_interests'] : array());
    $availability = isset($data['availability']) ? (array) $data['availability'] : (isset($data['volunteer_availability']) ? (array) $data['volunteer_availability'] : array());
    $skills     = sanitize_textarea_field($data['skills'] ?? $data['volunteer_skills'] ?? '');
    $source     = sanitize_text_field($data['source'] ?? 'website_form');

    if (empty($first_name) || empty($last_name) || empty($email)) {
        return new WP_Error('missing_fields', __('Please fill in all required fields.', 'campaignpress'));
    }

    if (!is_email($email)) {
        return new WP_Error('invalid_email', __('Please enter a valid email address.', 'campaignpress'));
    }

    // Central Contact Management
    global $cp_contact_manager;
    $contact_id = 0;
    if ($cp_contact_manager) {
        $contact_data = array(
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'email'         => $email,
            'phone'         => $phone,
            'zip_code'      => $zip,
            'source'        => $source,
        );
        $contact_id = $cp_contact_manager->find_or_create($contact_data);
        if (is_wp_error($contact_id)) {
            return $contact_id;
        }
    }

    $volunteers_table = $wpdb->prefix . 'cp_volunteers';

    // Check for existing volunteer
    $existing_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$volunteers_table} WHERE email = %s",
        $email
    ));

    $volunteer_data = array(
        'contact_id'   => $contact_id,
        'first_name'   => $first_name,
        'last_name'    => $last_name,
        'email'        => $email,
        'phone'        => $phone,
        'zip_code'     => $zip,
        'interests'    => json_encode($interests),
        'availability' => json_encode($availability),
        'skills'       => $skills,
        'source'       => $source,
        'status'       => 'new',
        'updated_at'   => current_time('mysql'),
    );

    if ($existing_id) {
        unset($volunteer_data['created_at']);
        $wpdb->update($volunteers_table, $volunteer_data, array('id' => $existing_id));
        $volunteer_id = $existing_id;
    } else {
        $volunteer_data['created_at'] = current_time('mysql');
        $result = $wpdb->insert($volunteers_table, $volunteer_data);
        if (false === $result) {
            return new WP_Error('db_error', __('Failed to save volunteer data.', 'campaignpress'));
        }
        $volunteer_id = $wpdb->insert_id;
    }

    // Trigger actions
    do_action('cp_volunteer_signup_success', $volunteer_id, $volunteer_data);

    return $volunteer_id;
}

/**
 * Handle volunteer signup AJAX request
 */
function campaignpress_ajax_volunteer_signup() {
    check_ajax_referer('cp_volunteer_signup', 'cp_volunteer_nonce');

    $result = campaignpress_process_volunteer_signup($_POST);

    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => $result->get_error_message()));
    }

    wp_send_json_success(array(
        'message' => __('Thank you for signing up! We will be in touch soon.', 'campaignpress'),
        'volunteer_id' => $result
    ));
}
add_action('wp_ajax_cp_volunteer_signup', 'campaignpress_ajax_volunteer_signup');
add_action('wp_ajax_nopriv_cp_volunteer_signup', 'campaignpress_ajax_volunteer_signup');
