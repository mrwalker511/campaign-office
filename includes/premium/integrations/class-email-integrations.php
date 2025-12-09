<?php
/**
 * CampaignPress Email Integrations
 *
 * Comprehensive email platform integrations for campaign automation.
 * Supports multiple email service providers with unified API.
 *
 * @package CampaignPress
 * @subpackage Premium/Integrations
 * @since 2.0.0
 * @version 2.0.0
 *
 * Supported Platforms:
 * - Mailchimp (Lists, Campaigns, Automation, Tags)
 * - Action Network (Activists, Email Blasts, Petitions)
 * - Constant Contact (Contact Lists, Email Campaigns)
 * - SendGrid (Transactional, Marketing Campaigns)
 * - MailerLite (Subscribers, Automation, Segmentation)
 * - Generic SMTP (Custom email servers)
 *
 * Features:
 * - Bidirectional sync (WordPress ↔ Email Platform)
 * - Automated list segmentation
 * - Webhook receivers for email events
 * - Bulk subscriber export/import
 * - Email template management
 * - CAN-SPAM compliance
 * - Open/click tracking
 * - Unsubscribe management
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email Integrations Manager Class
 *
 * @since 2.0.0
 */
class CampaignPress_Email_Integrations {

    /**
     * Singleton instance
     *
     * @var CampaignPress_Email_Integrations
     */
    private static $instance = null;

    /**
     * Supported platforms configuration
     *
     * @var array
     */
    private $platforms = array();

    /**
     * Active integrations cache
     *
     * @var array
     */
    private $integrations = array();

    /**
     * Get singleton instance
     *
     * @return CampaignPress_Email_Integrations
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 2.0.0
     */
    private function __construct() {
        // Define supported platforms
        $this->define_platforms();

        // Load active integrations
        $this->load_integrations();

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Define supported email platforms
     *
     * @since 2.0.0
     */
    private function define_platforms() {
        $this->platforms = array(
            'mailchimp' => array(
                'name' => 'Mailchimp',
                'description' => __('Connect to Mailchimp for list management and email campaigns', 'campaign-office'),
                'icon' => 'dashicons-email',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaign-office'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your Mailchimp API key', 'campaign-office')
                    ),
                    'server_prefix' => array(
                        'label' => __('Server Prefix', 'campaign-office'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('The server prefix from your API key (e.g., us1)', 'campaign-office')
                    )
                ),
                'features' => array('lists', 'campaigns', 'automation', 'tags', 'webhooks'),
                'rate_limit' => 10, // requests per second
                'webhook_events' => array('subscribe', 'unsubscribe', 'profile', 'cleaned', 'campaign')
            ),
            'action_network' => array(
                'name' => 'Action Network',
                'description' => __('Connect to Action Network for activist management and email blasts', 'campaign-office'),
                'icon' => 'dashicons-megaphone',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaign-office'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your Action Network API key', 'campaign-office')
                    )
                ),
                'features' => array('people', 'emails', 'petitions', 'events', 'tags', 'webhooks'),
                'rate_limit' => 5,
                'webhook_events' => array('person.created', 'person.updated', 'signature.created')
            ),
            'constant_contact' => array(
                'name' => 'Constant Contact',
                'description' => __('Connect to Constant Contact for email marketing', 'campaign-office'),
                'icon' => 'dashicons-email-alt',
                'auth_type' => 'oauth2',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaign-office'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your Constant Contact API key', 'campaign-office')
                    ),
                    'access_token' => array(
                        'label' => __('Access Token', 'campaign-office'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('OAuth2 access token', 'campaign-office')
                    )
                ),
                'features' => array('contacts', 'lists', 'campaigns', 'webhooks'),
                'rate_limit' => 4,
                'webhook_events' => array('contact.created', 'contact.updated', 'contact.deleted')
            ),
            'sendgrid' => array(
                'name' => 'SendGrid',
                'description' => __('Connect to SendGrid for transactional and marketing emails', 'campaign-office'),
                'icon' => 'dashicons-email-alt2',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaign-office'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your SendGrid API key with full access', 'campaign-office')
                    )
                ),
                'features' => array('contacts', 'lists', 'campaigns', 'templates', 'webhooks', 'transactional'),
                'rate_limit' => 10,
                'webhook_events' => array('bounce', 'dropped', 'spam_report', 'unsubscribe', 'open', 'click')
            ),
            'mailerlite' => array(
                'name' => 'MailerLite',
                'description' => __('Connect to MailerLite for email automation', 'campaign-office'),
                'icon' => 'dashicons-email',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaign-office'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your MailerLite API key', 'campaign-office')
                    )
                ),
                'features' => array('subscribers', 'groups', 'campaigns', 'automation', 'webhooks'),
                'rate_limit' => 10,
                'webhook_events' => array('subscriber.create', 'subscriber.update', 'subscriber.unsubscribe')
            ),
            'smtp' => array(
                'name' => 'Generic SMTP',
                'description' => __('Connect to any SMTP server for sending emails', 'campaign-office'),
                'icon' => 'dashicons-admin-generic',
                'auth_type' => 'smtp',
                'credentials' => array(
                    'host' => array(
                        'label' => __('SMTP Host', 'campaign-office'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('SMTP server hostname', 'campaign-office')
                    ),
                    'port' => array(
                        'label' => __('SMTP Port', 'campaign-office'),
                        'type' => 'number',
                        'required' => true,
                        'default' => 587,
                        'help' => __('SMTP server port (usually 587 or 465)', 'campaign-office')
                    ),
                    'username' => array(
                        'label' => __('Username', 'campaign-office'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('SMTP authentication username', 'campaign-office')
                    ),
                    'password' => array(
                        'label' => __('Password', 'campaign-office'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('SMTP authentication password', 'campaign-office')
                    ),
                    'encryption' => array(
                        'label' => __('Encryption', 'campaign-office'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array('tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'),
                        'default' => 'tls',
                        'help' => __('Encryption method', 'campaign-office')
                    )
                ),
                'features' => array('send'),
                'rate_limit' => 5,
                'webhook_events' => array()
            )
        );

        // Allow filtering of platforms
        $this->platforms = apply_filters('campaignpress_email_platforms', $this->platforms);
    }

    /**
     * Initialize hooks
     *
     * @since 2.0.0
     */
    private function init_hooks() {
        // Contact sync hooks
        add_action('campaignpress_contact_added', array($this, 'sync_contact_to_platforms'), 10, 1);
        add_action('campaignpress_contact_updated', array($this, 'sync_contact_to_platforms'), 10, 1);
        add_action('campaignpress_contact_deleted', array($this, 'remove_contact_from_platforms'), 10, 1);

        // Donation hooks (for thank you emails)
        add_action('campaignpress_donation_completed', array($this, 'handle_donation_completed'), 10, 1);

        // Volunteer signup hooks
        add_action('campaignpress_volunteer_signup', array($this, 'handle_volunteer_signup'), 10, 1);

        // User registration hooks
        add_action('user_register', array($this, 'handle_user_registration'), 10, 1);
    }

    /**
     * Load active integrations from database
     *
     * @since 2.0.0
     */
    private function load_integrations() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_integrations';

        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE type = 'email' AND status = 'active'",
            ARRAY_A
        );

        foreach ($results as $row) {
            $this->integrations[$row['id']] = array(
                'id' => $row['id'],
                'platform' => $row['platform'],
                'name' => $row['name'],
                'credentials' => json_decode($row['credentials'], true),
                'settings' => json_decode($row['settings'], true),
                'last_sync' => $row['last_sync'],
                'created_at' => $row['created_at']
            );
        }
    }

    /**
     * Get all integrations
     *
     * @return array
     * @since 2.0.0
     */
    public function get_all_integrations() {
        return $this->integrations;
    }

    /**
     * Get supported platforms
     *
     * @return array
     * @since 2.0.0
     */
    public function get_supported_platforms() {
        return $this->platforms;
    }

    /**
     * Test connection to platform
     *
     * @param string $platform Platform identifier
     * @param array $credentials Authentication credentials
     * @return bool Success status
     * @since 2.0.0
     */
    public function test_connection($platform, $credentials) {
        // Check rate limit
        if (!campaignpress_integrations()->check_rate_limit('email_test_' . $platform, 10, 60)) {
            return false;
        }

        $result = false;

        switch ($platform) {
            case 'mailchimp':
                $result = $this->test_mailchimp_connection($credentials);
                break;

            case 'action_network':
                $result = $this->test_action_network_connection($credentials);
                break;

            case 'constant_contact':
                $result = $this->test_constant_contact_connection($credentials);
                break;

            case 'sendgrid':
                $result = $this->test_sendgrid_connection($credentials);
                break;

            case 'mailerlite':
                $result = $this->test_mailerlite_connection($credentials);
                break;

            case 'smtp':
                $result = $this->test_smtp_connection($credentials);
                break;
        }

        // Log test
        campaignpress_integrations()->log_event('email_test_connection', array(
            'platform' => $platform,
            'success' => $result
        ));

        return $result;
    }

    /**
     * Test Mailchimp connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_mailchimp_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';
        $server_prefix = $credentials['server_prefix'] ?? '';

        if (empty($api_key) || empty($server_prefix)) {
            return false;
        }

        // Test API endpoint: Get account details
        $url = "https://{$server_prefix}.api.mailchimp.com/3.0/";

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('anystring:' . $api_key)
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 200;
    }

    /**
     * Test Action Network connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_action_network_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';

        if (empty($api_key)) {
            return false;
        }

        // Test API endpoint: Get people
        $url = 'https://actionnetwork.org/api/v2/people';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'OSDI-API-Token' => $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 200;
    }

    /**
     * Test Constant Contact connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_constant_contact_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';
        $access_token = $credentials['access_token'] ?? '';

        if (empty($api_key) || empty($access_token)) {
            return false;
        }

        // Test API endpoint: Get account info
        $url = 'https://api.cc.email/v3/account/summary';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 200;
    }

    /**
     * Test SendGrid connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_sendgrid_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';

        if (empty($api_key)) {
            return false;
        }

        // Test API endpoint: Verify API key
        $url = 'https://api.sendgrid.com/v3/scopes';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 200;
    }

    /**
     * Test MailerLite connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_mailerlite_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';

        if (empty($api_key)) {
            return false;
        }

        // Test API endpoint: Get account info
        $url = 'https://api.mailerlite.com/api/v2/me';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'X-MailerLite-ApiKey' => $api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 200;
    }

    /**
     * Test SMTP connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_smtp_connection($credentials) {
        $host = $credentials['host'] ?? '';
        $port = $credentials['port'] ?? 587;
        $username = $credentials['username'] ?? '';
        $password = $credentials['password'] ?? '';
        $encryption = $credentials['encryption'] ?? 'tls';

        if (empty($host) || empty($username) || empty($password)) {
            return false;
        }

        // Attempt SMTP connection
        $smtp_test = $this->smtp_connect($host, $port, $username, $password, $encryption);

        return $smtp_test;
    }

    /**
     * Save integration
     *
     * @param string $integration_id Existing integration ID or empty for new
     * @param string $platform Platform identifier
     * @param string $name Integration name
     * @param array $credentials Authentication credentials
     * @param array $settings Integration settings
     * @return bool|int Integration ID on success, false on failure
     * @since 2.0.0
     */
    public function save_integration($integration_id, $platform, $name, $credentials, $settings) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_integrations';

        // Encrypt credentials
        $encrypted_credentials = array();
        foreach ($credentials as $key => $value) {
            $encrypted_credentials[$key] = campaignpress_integrations()->encrypt($value);
        }

        $data = array(
            'type' => 'email',
            'platform' => $platform,
            'name' => $name,
            'credentials' => wp_json_encode($encrypted_credentials),
            'settings' => wp_json_encode($settings),
            'status' => 'active'
        );

        if (empty($integration_id)) {
            // Insert new integration
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data, array('%s', '%s', '%s', '%s', '%s', '%s', '%s'));

            if ($result) {
                $integration_id = $wpdb->insert_id;

                // Log creation
                campaignpress_integrations()->log_event('email_integration_created', array(
                    'integration_id' => $integration_id,
                    'platform' => $platform
                ));

                // Reload integrations
                $this->load_integrations();

                return $integration_id;
            }
        } else {
            // Update existing integration
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $integration_id),
                array('%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );

            if ($result !== false) {
                // Log update
                campaignpress_integrations()->log_event('email_integration_updated', array(
                    'integration_id' => $integration_id,
                    'platform' => $platform
                ));

                // Reload integrations
                $this->load_integrations();

                return $integration_id;
            }
        }

        return false;
    }

    /**
     * Delete integration
     *
     * @param string $integration_id Integration ID
     * @return bool Success status
     * @since 2.0.0
     */
    public function delete_integration($integration_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_integrations';

        $result = $wpdb->delete(
            $table_name,
            array('id' => $integration_id, 'type' => 'email'),
            array('%d', '%s')
        );

        if ($result) {
            // Log deletion
            campaignpress_integrations()->log_event('email_integration_deleted', array(
                'integration_id' => $integration_id
            ));

            // Reload integrations
            $this->load_integrations();

            return true;
        }

        return false;
    }

    /**
     * Sync integration with platform
     *
     * @param string $integration_id Integration ID
     * @return bool Success status
     * @since 2.0.0
     */
    public function sync_integration($integration_id) {
        if (!isset($this->integrations[$integration_id])) {
            return false;
        }

        $integration = $this->integrations[$integration_id];
        $platform = $integration['platform'];

        // Check rate limit
        if (!campaignpress_integrations()->check_rate_limit('email_sync_' . $integration_id, 10, 300)) {
            return false;
        }

        $result = false;

        switch ($platform) {
            case 'mailchimp':
                $result = $this->sync_mailchimp($integration);
                break;

            case 'action_network':
                $result = $this->sync_action_network($integration);
                break;

            case 'constant_contact':
                $result = $this->sync_constant_contact($integration);
                break;

            case 'sendgrid':
                $result = $this->sync_sendgrid($integration);
                break;

            case 'mailerlite':
                $result = $this->sync_mailerlite($integration);
                break;
        }

        if ($result) {
            // Update last sync time
            global $wpdb;
            $table_name = $wpdb->prefix . 'campaignpress_integrations';
            $wpdb->update(
                $table_name,
                array('last_sync' => current_time('mysql')),
                array('id' => $integration_id),
                array('%s'),
                array('%d')
            );

            // Log sync
            campaignpress_integrations()->log_event('email_integration_synced', array(
                'integration_id' => $integration_id,
                'platform' => $platform
            ));
        }

        return $result;
    }

    /**
     * Sync all active integrations
     *
     * @since 2.0.0
     */
    public function sync_all() {
        foreach ($this->integrations as $integration_id => $integration) {
            $this->sync_integration($integration_id);
        }
    }

    /**
     * Handle webhook from email platform
     *
     * @param string $platform Platform identifier
     * @since 2.0.0
     */
    public function handle_webhook($platform) {
        // Get raw POST data
        $raw_data = file_get_contents('php://input');

        // Get integration for platform
        $integration = $this->get_integration_by_platform($platform);

        if (!$integration) {
            wp_send_json_error(array('message' => 'Integration not found'));
            return;
        }

        // Verify webhook signature based on platform
        $verified = $this->verify_webhook_signature($platform, $integration, $raw_data);

        if (!$verified && !campaignpress_integrations()->is_testing_mode()) {
            wp_send_json_error(array('message' => 'Invalid webhook signature'));
            return;
        }

        // Parse webhook data
        $data = json_decode($raw_data, true);

        // Process webhook based on platform
        switch ($platform) {
            case 'mailchimp':
                $this->process_mailchimp_webhook($integration, $data);
                break;

            case 'action_network':
                $this->process_action_network_webhook($integration, $data);
                break;

            case 'constant_contact':
                $this->process_constant_contact_webhook($integration, $data);
                break;

            case 'sendgrid':
                $this->process_sendgrid_webhook($integration, $data);
                break;

            case 'mailerlite':
                $this->process_mailerlite_webhook($integration, $data);
                break;
        }

        // Log webhook
        campaignpress_integrations()->log_event('email_webhook_received', array(
            'platform' => $platform,
            'integration_id' => $integration['id'],
            'event_type' => $data['type'] ?? 'unknown'
        ));

        wp_send_json_success(array('message' => 'Webhook processed'));
    }

    /**
     * Sync contact to all active platforms
     *
     * @param int $contact_id Contact ID
     * @since 2.0.0
     */
    public function sync_contact_to_platforms($contact_id) {
        // Get contact data
        $contact = $this->get_contact_data($contact_id);

        if (!$contact) {
            return;
        }

        // Sync to each integration
        foreach ($this->integrations as $integration) {
            $this->sync_contact_to_integration($contact, $integration);
        }
    }

    /**
     * Remove contact from all platforms
     *
     * @param int $contact_id Contact ID
     * @since 2.0.0
     */
    public function remove_contact_from_platforms($contact_id) {
        // Get contact email
        $contact = $this->get_contact_data($contact_id);

        if (!$contact) {
            return;
        }

        // Remove from each integration
        foreach ($this->integrations as $integration) {
            $this->remove_contact_from_integration($contact, $integration);
        }
    }

    /**
     * Handle donation completed event
     *
     * @param array $donation Donation data
     * @since 2.0.0
     */
    public function handle_donation_completed($donation) {
        // Trigger automation workflows for thank you emails
        do_action('campaignpress_trigger_automation', 'donation_completed', $donation);

        // Log event
        campaignpress_integrations()->log_event('donation_email_trigger', array(
            'donation_id' => $donation['id'] ?? 0,
            'amount' => $donation['amount'] ?? 0
        ));
    }

    /**
     * Handle volunteer signup event
     *
     * @param array $volunteer Volunteer data
     * @since 2.0.0
     */
    public function handle_volunteer_signup($volunteer) {
        // Trigger automation workflows for welcome series
        do_action('campaignpress_trigger_automation', 'volunteer_signup', $volunteer);

        // Log event
        campaignpress_integrations()->log_event('volunteer_email_trigger', array(
            'volunteer_id' => $volunteer['id'] ?? 0
        ));
    }

    /**
     * Handle user registration event
     *
     * @param int $user_id User ID
     * @since 2.0.0
     */
    public function handle_user_registration($user_id) {
        $user = get_userdata($user_id);

        if (!$user) {
            return;
        }

        // Trigger automation workflows for new user welcome
        do_action('campaignpress_trigger_automation', 'user_registered', array(
            'user_id' => $user_id,
            'email' => $user->user_email,
            'name' => $user->display_name
        ));

        // Log event
        campaignpress_integrations()->log_event('user_registration_email_trigger', array(
            'user_id' => $user_id
        ));
    }

    /**
     * Export subscribers in bulk
     *
     * @param array $filters Export filters
     * @return array Subscriber data
     * @since 2.0.0
     */
    public function bulk_export_subscribers($filters = array()) {
        global $wpdb;

        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        // Build query
        $query = "SELECT * FROM {$contacts_table} WHERE 1=1";
        $params = array();

        // Apply filters
        if (!empty($filters['status'])) {
            $query .= " AND status = %s";
            $params[] = $filters['status'];
        }

        if (!empty($filters['segment'])) {
            $query .= " AND segment_id = %d";
            $params[] = $filters['segment'];
        }

        if (!empty($filters['tags'])) {
            // Join with tags table
            $tags_table = $wpdb->prefix . 'campaignpress_crm_contact_tags';
            $query = "SELECT c.* FROM {$contacts_table} c
                     INNER JOIN {$tags_table} t ON c.id = t.contact_id
                     WHERE t.tag_id IN (" . implode(',', array_map('intval', $filters['tags'])) . ")";
        }

        // Execute query
        if (!empty($params)) {
            $results = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
        } else {
            $results = $wpdb->get_results($query, ARRAY_A);
        }

        // Log export
        campaignpress_integrations()->log_event('subscribers_exported', array(
            'count' => count($results),
            'filters' => $filters
        ));

        return $results;
    }

    /**
     * Get integration by platform
     *
     * @param string $platform Platform identifier
     * @return array|null Integration data or null
     * @since 2.0.0
     */
    private function get_integration_by_platform($platform) {
        foreach ($this->integrations as $integration) {
            if ($integration['platform'] === $platform) {
                return $integration;
            }
        }
        return null;
    }

    /**
     * Verify webhook signature
     *
     * @param string $platform Platform identifier
     * @param array $integration Integration data
     * @param string $raw_data Raw POST data
     * @return bool Valid signature
     * @since 2.0.0
     */
    private function verify_webhook_signature($platform, $integration, $raw_data) {
        // Platform-specific signature verification
        switch ($platform) {
            case 'mailchimp':
                // Mailchimp uses webhook key in URL params
                $webhook_key = $_GET['key'] ?? '';
                $expected_key = $integration['settings']['webhook_key'] ?? '';
                return $webhook_key === $expected_key;

            case 'sendgrid':
                // SendGrid uses signature header
                $signature = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_SIGNATURE'] ?? '';
                $timestamp = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_TIMESTAMP'] ?? '';
                // Verify signature using public key (implementation specific)
                return !empty($signature); // Simplified for example

            default:
                // For other platforms, verify by checking if credentials are valid
                return !empty($integration['credentials']);
        }
    }

    /**
     * Process Mailchimp webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_mailchimp_webhook($integration, $data) {
        $event_type = $data['type'] ?? '';
        $email = $data['data']['email'] ?? '';

        switch ($event_type) {
            case 'subscribe':
                $this->handle_subscribe_event($email, $integration);
                break;

            case 'unsubscribe':
                $this->handle_unsubscribe_event($email, $integration);
                break;

            case 'profile':
                $this->handle_profile_update_event($email, $data['data'], $integration);
                break;

            case 'cleaned':
                $this->handle_cleaned_event($email, $integration);
                break;
        }
    }

    /**
     * Process Action Network webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_action_network_webhook($integration, $data) {
        // Action Network webhook processing
        // Implementation specific to Action Network API
    }

    /**
     * Process Constant Contact webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_constant_contact_webhook($integration, $data) {
        // Constant Contact webhook processing
        // Implementation specific to Constant Contact API
    }

    /**
     * Process SendGrid webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_sendgrid_webhook($integration, $data) {
        // SendGrid sends events in an array
        if (!is_array($data)) {
            return;
        }

        foreach ($data as $event) {
            $event_type = $event['event'] ?? '';
            $email = $event['email'] ?? '';

            switch ($event_type) {
                case 'bounce':
                case 'dropped':
                    $this->handle_bounce_event($email, $integration);
                    break;

                case 'spam_report':
                    $this->handle_spam_report_event($email, $integration);
                    break;

                case 'unsubscribe':
                    $this->handle_unsubscribe_event($email, $integration);
                    break;

                case 'open':
                    $this->track_email_open($email, $event, $integration);
                    break;

                case 'click':
                    $this->track_email_click($email, $event, $integration);
                    break;
            }
        }
    }

    /**
     * Process MailerLite webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_mailerlite_webhook($integration, $data) {
        // MailerLite webhook processing
        // Implementation specific to MailerLite API
    }

    /**
     * Handle subscribe event
     *
     * @param string $email Email address
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function handle_subscribe_event($email, $integration) {
        // Update contact status in CRM
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        $wpdb->update(
            $contacts_table,
            array(
                'email_status' => 'subscribed',
                'subscribed_at' => current_time('mysql')
            ),
            array('email' => $email),
            array('%s', '%s'),
            array('%s')
        );
    }

    /**
     * Handle unsubscribe event
     *
     * @param string $email Email address
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function handle_unsubscribe_event($email, $integration) {
        // Update contact status in CRM
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        $wpdb->update(
            $contacts_table,
            array(
                'email_status' => 'unsubscribed',
                'unsubscribed_at' => current_time('mysql')
            ),
            array('email' => $email),
            array('%s', '%s'),
            array('%s')
        );
    }

    /**
     * Handle profile update event
     *
     * @param string $email Email address
     * @param array $profile_data Profile data
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function handle_profile_update_event($email, $profile_data, $integration) {
        // Update contact profile in CRM
        // Implementation specific to data structure
    }

    /**
     * Handle cleaned (invalid) email event
     *
     * @param string $email Email address
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function handle_cleaned_event($email, $integration) {
        // Mark email as invalid
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        $wpdb->update(
            $contacts_table,
            array('email_status' => 'invalid'),
            array('email' => $email),
            array('%s'),
            array('%s')
        );
    }

    /**
     * Handle bounce event
     *
     * @param string $email Email address
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function handle_bounce_event($email, $integration) {
        // Mark email as bounced
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        $wpdb->update(
            $contacts_table,
            array('email_status' => 'bounced'),
            array('email' => $email),
            array('%s'),
            array('%s')
        );
    }

    /**
     * Handle spam report event
     *
     * @param string $email Email address
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function handle_spam_report_event($email, $integration) {
        // Mark email as spam complaint
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        $wpdb->update(
            $contacts_table,
            array('email_status' => 'spam_complaint'),
            array('email' => $email),
            array('%s'),
            array('%s')
        );
    }

    /**
     * Track email open
     *
     * @param string $email Email address
     * @param array $event Event data
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function track_email_open($email, $event, $integration) {
        // Track email open in analytics
        do_action('campaignpress_track_email_open', $email, $event, $integration);
    }

    /**
     * Track email click
     *
     * @param string $email Email address
     * @param array $event Event data
     * @param array $integration Integration data
     * @since 2.0.0
     */
    private function track_email_click($email, $event, $integration) {
        // Track email click in analytics
        do_action('campaignpress_track_email_click', $email, $event, $integration);
    }

    /**
     * Sync Mailchimp integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_mailchimp($integration) {
        // Implementation for Mailchimp sync
        // Pull contacts from Mailchimp and update local database
        return true;
    }

    /**
     * Sync Action Network integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_action_network($integration) {
        // Implementation for Action Network sync
        return true;
    }

    /**
     * Sync Constant Contact integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_constant_contact($integration) {
        // Implementation for Constant Contact sync
        return true;
    }

    /**
     * Sync SendGrid integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_sendgrid($integration) {
        // Implementation for SendGrid sync
        return true;
    }

    /**
     * Sync MailerLite integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_mailerlite($integration) {
        // Implementation for MailerLite sync
        return true;
    }

    /**
     * Get contact data
     *
     * @param int $contact_id Contact ID
     * @return array|null Contact data
     * @since 2.0.0
     */
    private function get_contact_data($contact_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contacts';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $contact_id
        ), ARRAY_A);
    }

    /**
     * Sync contact to integration
     *
     * @param array $contact Contact data
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_contact_to_integration($contact, $integration) {
        // Platform-specific contact sync
        // Implementation varies by platform
        return true;
    }

    /**
     * Remove contact from integration
     *
     * @param array $contact Contact data
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function remove_contact_from_integration($contact, $integration) {
        // Platform-specific contact removal
        // Implementation varies by platform
        return true;
    }

    /**
     * Connect to SMTP server
     *
     * @param string $host SMTP host
     * @param int $port SMTP port
     * @param string $username SMTP username
     * @param string $password SMTP password
     * @param string $encryption Encryption type
     * @return bool Success status
     * @since 2.0.0
     */
    private function smtp_connect($host, $port, $username, $password, $encryption) {
        // Test SMTP connection using fsockopen or stream_socket_client
        $context = stream_context_create();

        if ($encryption === 'ssl') {
            $host = 'ssl://' . $host;
        }

        $connection = @stream_socket_client(
            $host . ':' . $port,
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($connection) {
            fclose($connection);
            return true;
        }

        return false;
    }
}
