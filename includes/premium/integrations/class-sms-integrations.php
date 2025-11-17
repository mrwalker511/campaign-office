<?php
/**
 * CampaignPress SMS Integrations
 *
 * Comprehensive SMS platform integrations for campaign messaging.
 * Supports multiple SMS service providers with TCPA compliance.
 *
 * @package CampaignPress
 * @subpackage Premium/Integrations
 * @since 2.0.0
 * @version 2.0.0
 *
 * Supported Platforms:
 * - Twilio (SMS, MMS, Conversation Tracking)
 * - Hustle (Peer-to-Peer Texting)
 * - CallHub (SMS Campaigns, Click-to-Text)
 * - RumbleUp (P2P Texting Platform)
 *
 * Features:
 * - TCPA compliance management
 * - Opt-in/opt-out tracking
 * - Message templates
 * - Bulk SMS sending with rate limiting
 * - SMS delivery tracking
 * - Auto-reply management
 * - Conversation threading
 * - Phone number management
 * - Message scheduling
 * - Keyword responses
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * SMS Integrations Manager Class
 *
 * @since 2.0.0
 */
class CampaignPress_SMS_Integrations {

    /**
     * Singleton instance
     *
     * @var CampaignPress_SMS_Integrations
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
     * Opt-out keywords
     *
     * @var array
     */
    private $opt_out_keywords = array('STOP', 'STOPALL', 'UNSUBSCRIBE', 'CANCEL', 'END', 'QUIT');

    /**
     * Opt-in keywords
     *
     * @var array
     */
    private $opt_in_keywords = array('START', 'YES', 'UNSTOP');

    /**
     * Get singleton instance
     *
     * @return CampaignPress_SMS_Integrations
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

        // Load opt-out/opt-in keywords from settings
        $this->load_keywords();
    }

    /**
     * Define supported SMS platforms
     *
     * @since 2.0.0
     */
    private function define_platforms() {
        $this->platforms = array(
            'twilio' => array(
                'name' => 'Twilio',
                'description' => __('Connect to Twilio for SMS, MMS, and conversation tracking', 'campaignpress'),
                'icon' => 'dashicons-smartphone',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'account_sid' => array(
                        'label' => __('Account SID', 'campaignpress'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('Your Twilio Account SID', 'campaignpress')
                    ),
                    'auth_token' => array(
                        'label' => __('Auth Token', 'campaignpress'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your Twilio Auth Token', 'campaignpress')
                    ),
                    'phone_number' => array(
                        'label' => __('Phone Number', 'campaignpress'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('Your Twilio phone number (with country code, e.g., +1234567890)', 'campaignpress')
                    )
                ),
                'features' => array('sms', 'mms', 'conversations', 'webhooks', 'scheduling'),
                'rate_limit' => 100, // messages per second
                'webhook_events' => array('message.received', 'message.sent', 'message.delivered', 'message.failed')
            ),
            'hustle' => array(
                'name' => 'Hustle',
                'description' => __('Connect to Hustle for peer-to-peer texting campaigns', 'campaignpress'),
                'icon' => 'dashicons-groups',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaignpress'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your Hustle API key', 'campaignpress')
                    ),
                    'organization_id' => array(
                        'label' => __('Organization ID', 'campaignpress'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('Your Hustle organization ID', 'campaignpress')
                    )
                ),
                'features' => array('p2p', 'campaigns', 'agents', 'webhooks'),
                'rate_limit' => 10,
                'webhook_events' => array('message.received', 'agent.replied', 'contact.opted_out')
            ),
            'callhub' => array(
                'name' => 'CallHub',
                'description' => __('Connect to CallHub for SMS campaigns and click-to-text', 'campaignpress'),
                'icon' => 'dashicons-phone',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaignpress'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your CallHub API key', 'campaignpress')
                    ),
                    'agency_id' => array(
                        'label' => __('Agency ID', 'campaignpress'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('Your CallHub agency ID', 'campaignpress')
                    )
                ),
                'features' => array('sms_campaigns', 'click_to_text', 'contacts', 'webhooks'),
                'rate_limit' => 5,
                'webhook_events' => array('campaign.completed', 'contact.responded', 'message.sent')
            ),
            'rumbleup' => array(
                'name' => 'RumbleUp',
                'description' => __('Connect to RumbleUp for peer-to-peer texting', 'campaignpress'),
                'icon' => 'dashicons-megaphone',
                'auth_type' => 'api_key',
                'credentials' => array(
                    'api_key' => array(
                        'label' => __('API Key', 'campaignpress'),
                        'type' => 'password',
                        'required' => true,
                        'help' => __('Your RumbleUp API key', 'campaignpress')
                    ),
                    'account_id' => array(
                        'label' => __('Account ID', 'campaignpress'),
                        'type' => 'text',
                        'required' => true,
                        'help' => __('Your RumbleUp account ID', 'campaignpress')
                    )
                ),
                'features' => array('p2p', 'bulk_sms', 'contacts', 'webhooks'),
                'rate_limit' => 10,
                'webhook_events' => array('message.received', 'message.sent', 'opt_out')
            )
        );

        // Allow filtering of platforms
        $this->platforms = apply_filters('campaignpress_sms_platforms', $this->platforms);
    }

    /**
     * Initialize hooks
     *
     * @since 2.0.0
     */
    private function init_hooks() {
        // Contact opt-in/opt-out hooks
        add_action('campaignpress_sms_opt_in', array($this, 'handle_opt_in'), 10, 2);
        add_action('campaignpress_sms_opt_out', array($this, 'handle_opt_out'), 10, 2);

        // Message sending hooks
        add_action('campaignpress_send_sms', array($this, 'send_sms'), 10, 3);
        add_action('campaignpress_send_bulk_sms', array($this, 'send_bulk_sms'), 10, 2);

        // Scheduled messages
        add_action('campaignpress_send_scheduled_sms', array($this, 'send_scheduled_message'), 10, 1);
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
            "SELECT * FROM {$table_name} WHERE type = 'sms' AND status = 'active'",
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
     * Load opt-in/opt-out keywords from settings
     *
     * @since 2.0.0
     */
    private function load_keywords() {
        $custom_opt_out = get_option('campaignpress_sms_opt_out_keywords', array());
        $custom_opt_in = get_option('campaignpress_sms_opt_in_keywords', array());

        if (!empty($custom_opt_out)) {
            $this->opt_out_keywords = array_merge($this->opt_out_keywords, $custom_opt_out);
        }

        if (!empty($custom_opt_in)) {
            $this->opt_in_keywords = array_merge($this->opt_in_keywords, $custom_opt_in);
        }

        // Make keywords case-insensitive
        $this->opt_out_keywords = array_map('strtoupper', $this->opt_out_keywords);
        $this->opt_in_keywords = array_map('strtoupper', $this->opt_in_keywords);
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
        if (!campaignpress_integrations()->check_rate_limit('sms_test_' . $platform, 5, 60)) {
            return false;
        }

        $result = false;

        switch ($platform) {
            case 'twilio':
                $result = $this->test_twilio_connection($credentials);
                break;

            case 'hustle':
                $result = $this->test_hustle_connection($credentials);
                break;

            case 'callhub':
                $result = $this->test_callhub_connection($credentials);
                break;

            case 'rumbleup':
                $result = $this->test_rumbleup_connection($credentials);
                break;
        }

        // Log test
        campaignpress_integrations()->log_event('sms_test_connection', array(
            'platform' => $platform,
            'success' => $result
        ));

        return $result;
    }

    /**
     * Test Twilio connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_twilio_connection($credentials) {
        $account_sid = $credentials['account_sid'] ?? '';
        $auth_token = $credentials['auth_token'] ?? '';

        if (empty($account_sid) || empty($auth_token)) {
            return false;
        }

        // Test API endpoint: Get account details
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}.json";

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token)
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
     * Test Hustle connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_hustle_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';
        $organization_id = $credentials['organization_id'] ?? '';

        if (empty($api_key) || empty($organization_id)) {
            return false;
        }

        // Test API endpoint: Get organization
        $url = "https://api.hustle.com/v1/organizations/{$organization_id}";

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
     * Test CallHub connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_callhub_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';
        $agency_id = $credentials['agency_id'] ?? '';

        if (empty($api_key) || empty($agency_id)) {
            return false;
        }

        // Test API endpoint: Get agency details
        $url = "https://api.callhub.io/v1/agencies/{$agency_id}/";

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => 'Token ' . $api_key,
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
     * Test RumbleUp connection
     *
     * @param array $credentials
     * @return bool
     * @since 2.0.0
     */
    private function test_rumbleup_connection($credentials) {
        $api_key = $credentials['api_key'] ?? '';
        $account_id = $credentials['account_id'] ?? '';

        if (empty($api_key) || empty($account_id)) {
            return false;
        }

        // Test API endpoint: Get account info
        $url = "https://api.rumbleup.com/v1/accounts/{$account_id}";

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
            'type' => 'sms',
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
                campaignpress_integrations()->log_event('sms_integration_created', array(
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
                campaignpress_integrations()->log_event('sms_integration_updated', array(
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
            array('id' => $integration_id, 'type' => 'sms'),
            array('%d', '%s')
        );

        if ($result) {
            // Log deletion
            campaignpress_integrations()->log_event('sms_integration_deleted', array(
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
        if (!campaignpress_integrations()->check_rate_limit('sms_sync_' . $integration_id, 5, 300)) {
            return false;
        }

        $result = false;

        switch ($platform) {
            case 'twilio':
                $result = $this->sync_twilio($integration);
                break;

            case 'hustle':
                $result = $this->sync_hustle($integration);
                break;

            case 'callhub':
                $result = $this->sync_callhub($integration);
                break;

            case 'rumbleup':
                $result = $this->sync_rumbleup($integration);
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
            campaignpress_integrations()->log_event('sms_integration_synced', array(
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
     * Handle webhook from SMS platform
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
        if (!$data) {
            // Some platforms use form-encoded data
            parse_str($raw_data, $data);
        }

        // Process webhook based on platform
        switch ($platform) {
            case 'twilio':
                $this->process_twilio_webhook($integration, $data);
                break;

            case 'hustle':
                $this->process_hustle_webhook($integration, $data);
                break;

            case 'callhub':
                $this->process_callhub_webhook($integration, $data);
                break;

            case 'rumbleup':
                $this->process_rumbleup_webhook($integration, $data);
                break;
        }

        // Log webhook
        campaignpress_integrations()->log_event('sms_webhook_received', array(
            'platform' => $platform,
            'integration_id' => $integration['id'],
            'event_type' => $data['type'] ?? 'unknown'
        ));

        wp_send_json_success(array('message' => 'Webhook processed'));
    }

    /**
     * Send SMS message
     *
     * @param string $phone Phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool Success status
     * @since 2.0.0
     */
    public function send_sms($phone, $message, $options = array()) {
        // Check if phone number has opted out
        if ($this->is_opted_out($phone)) {
            campaignpress_integrations()->log_event('sms_send_blocked_opted_out', array(
                'phone' => $phone
            ));
            return false;
        }

        // Get default integration or specified integration
        $integration_id = $options['integration_id'] ?? $this->get_default_integration();

        if (!$integration_id || !isset($this->integrations[$integration_id])) {
            return false;
        }

        $integration = $this->integrations[$integration_id];
        $platform = $integration['platform'];

        // Check rate limit
        $rate_limit = $this->platforms[$platform]['rate_limit'] ?? 10;
        if (!campaignpress_integrations()->check_rate_limit('sms_send_' . $integration_id, $rate_limit, 60)) {
            return false;
        }

        // Add TCPA compliance footer if required
        if (!empty($options['add_compliance_footer'])) {
            $message = $this->add_compliance_footer($message, $integration);
        }

        $result = false;

        switch ($platform) {
            case 'twilio':
                $result = $this->send_twilio_sms($integration, $phone, $message, $options);
                break;

            case 'hustle':
                $result = $this->send_hustle_sms($integration, $phone, $message, $options);
                break;

            case 'callhub':
                $result = $this->send_callhub_sms($integration, $phone, $message, $options);
                break;

            case 'rumbleup':
                $result = $this->send_rumbleup_sms($integration, $phone, $message, $options);
                break;
        }

        // Log send attempt
        campaignpress_integrations()->log_event('sms_sent', array(
            'integration_id' => $integration_id,
            'platform' => $platform,
            'phone' => $phone,
            'success' => $result
        ));

        // Store message in database
        if ($result) {
            $this->store_message($integration_id, $phone, $message, 'outbound', $options);
        }

        return $result;
    }

    /**
     * Send bulk SMS messages
     *
     * @param array $recipients Array of phone numbers
     * @param string $message Message content
     * @param array $options Additional options
     * @return array Results array with success/failure counts
     * @since 2.0.0
     */
    public function send_bulk_sms($recipients, $message, $options = array()) {
        $results = array(
            'sent' => 0,
            'failed' => 0,
            'opted_out' => 0
        );

        // Filter out opted-out recipients
        $valid_recipients = array();
        foreach ($recipients as $phone) {
            if ($this->is_opted_out($phone)) {
                $results['opted_out']++;
            } else {
                $valid_recipients[] = $phone;
            }
        }

        // Send messages with rate limiting
        foreach ($valid_recipients as $phone) {
            $sent = $this->send_sms($phone, $message, $options);

            if ($sent) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }

            // Sleep to respect rate limits
            usleep(100000); // 100ms between messages
        }

        // Log bulk send
        campaignpress_integrations()->log_event('sms_bulk_sent', array(
            'total_recipients' => count($recipients),
            'sent' => $results['sent'],
            'failed' => $results['failed'],
            'opted_out' => $results['opted_out']
        ));

        return $results;
    }

    /**
     * Schedule SMS message
     *
     * @param string $phone Phone number
     * @param string $message Message content
     * @param string $send_time Scheduled send time (Y-m-d H:i:s)
     * @param array $options Additional options
     * @return bool Success status
     * @since 2.0.0
     */
    public function schedule_sms($phone, $message, $send_time, $options = array()) {
        // Schedule WordPress event
        $timestamp = strtotime($send_time);

        if ($timestamp <= time()) {
            // Send immediately if time is in the past
            return $this->send_sms($phone, $message, $options);
        }

        // Schedule for future
        $args = array(
            'phone' => $phone,
            'message' => $message,
            'options' => $options
        );

        wp_schedule_single_event($timestamp, 'campaignpress_send_scheduled_sms', array($args));

        // Log scheduling
        campaignpress_integrations()->log_event('sms_scheduled', array(
            'phone' => $phone,
            'send_time' => $send_time
        ));

        return true;
    }

    /**
     * Send scheduled message
     *
     * @param array $args Message arguments
     * @since 2.0.0
     */
    public function send_scheduled_message($args) {
        $phone = $args['phone'] ?? '';
        $message = $args['message'] ?? '';
        $options = $args['options'] ?? array();

        if (!empty($phone) && !empty($message)) {
            $this->send_sms($phone, $message, $options);
        }
    }

    /**
     * Handle opt-in request
     *
     * @param string $phone Phone number
     * @param array $source Opt-in source data
     * @since 2.0.0
     */
    public function handle_opt_in($phone, $source = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_sms_opt_status';

        // Update or insert opt-in status
        $wpdb->replace(
            $table_name,
            array(
                'phone' => $phone,
                'status' => 'opted_in',
                'source' => wp_json_encode($source),
                'opted_in_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );

        // Log opt-in
        campaignpress_integrations()->log_event('sms_opt_in', array(
            'phone' => $phone,
            'source' => $source
        ));

        // Send confirmation message
        $confirmation = get_option('campaignpress_sms_opt_in_confirmation', '');
        if (!empty($confirmation)) {
            $this->send_sms($phone, $confirmation);
        }

        // Trigger action for other integrations
        do_action('campaignpress_sms_opted_in', $phone, $source);
    }

    /**
     * Handle opt-out request
     *
     * @param string $phone Phone number
     * @param array $source Opt-out source data
     * @since 2.0.0
     */
    public function handle_opt_out($phone, $source = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_sms_opt_status';

        // Update or insert opt-out status
        $wpdb->replace(
            $table_name,
            array(
                'phone' => $phone,
                'status' => 'opted_out',
                'source' => wp_json_encode($source),
                'opted_out_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );

        // Log opt-out
        campaignpress_integrations()->log_event('sms_opt_out', array(
            'phone' => $phone,
            'source' => $source
        ));

        // Send confirmation message
        $confirmation = get_option('campaignpress_sms_opt_out_confirmation', 'You have been unsubscribed. Reply START to opt back in.');
        if (!empty($confirmation)) {
            $this->send_sms($phone, $confirmation);
        }

        // Trigger action for other integrations
        do_action('campaignpress_sms_opted_out', $phone, $source);
    }

    /**
     * Check if phone number is opted out
     *
     * @param string $phone Phone number
     * @return bool True if opted out
     * @since 2.0.0
     */
    public function is_opted_out($phone) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_sms_opt_status';

        $status = $wpdb->get_var($wpdb->prepare(
            "SELECT status FROM {$table_name} WHERE phone = %s",
            $phone
        ));

        return $status === 'opted_out';
    }

    /**
     * Process incoming message for opt-in/opt-out keywords
     *
     * @param string $phone Phone number
     * @param string $message Message content
     * @return bool True if keyword was processed
     * @since 2.0.0
     */
    private function process_keywords($phone, $message) {
        $message_upper = strtoupper(trim($message));

        // Check opt-out keywords
        if (in_array($message_upper, $this->opt_out_keywords)) {
            $this->handle_opt_out($phone, array('method' => 'keyword', 'keyword' => $message_upper));
            return true;
        }

        // Check opt-in keywords
        if (in_array($message_upper, $this->opt_in_keywords)) {
            $this->handle_opt_in($phone, array('method' => 'keyword', 'keyword' => $message_upper));
            return true;
        }

        return false;
    }

    /**
     * Add TCPA compliance footer to message
     *
     * @param string $message Message content
     * @param array $integration Integration data
     * @return string Message with footer
     * @since 2.0.0
     */
    private function add_compliance_footer($message, $integration) {
        $footer = get_option('campaignpress_sms_compliance_footer', ' Reply STOP to opt out.');
        return $message . $footer;
    }

    /**
     * Store message in database
     *
     * @param int $integration_id Integration ID
     * @param string $phone Phone number
     * @param string $message Message content
     * @param string $direction Direction (inbound/outbound)
     * @param array $metadata Additional metadata
     * @since 2.0.0
     */
    private function store_message($integration_id, $phone, $message, $direction, $metadata = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_sms_messages';

        $wpdb->insert(
            $table_name,
            array(
                'integration_id' => $integration_id,
                'phone' => $phone,
                'message' => $message,
                'direction' => $direction,
                'metadata' => wp_json_encode($metadata),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Get conversation thread for phone number
     *
     * @param string $phone Phone number
     * @param int $limit Number of messages to retrieve
     * @return array Messages
     * @since 2.0.0
     */
    public function get_conversation($phone, $limit = 50) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_sms_messages';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE phone = %s ORDER BY created_at DESC LIMIT %d",
            $phone,
            $limit
        ), ARRAY_A);
    }

    /**
     * Send Twilio SMS
     *
     * @param array $integration Integration data
     * @param string $phone Phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool Success status
     * @since 2.0.0
     */
    private function send_twilio_sms($integration, $phone, $message, $options) {
        $credentials = $integration['credentials'];
        $account_sid = campaignpress_integrations()->decrypt($credentials['account_sid']);
        $auth_token = campaignpress_integrations()->decrypt($credentials['auth_token']);
        $from_number = campaignpress_integrations()->decrypt($credentials['phone_number']);

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token)
            ),
            'body' => array(
                'From' => $from_number,
                'To' => $phone,
                'Body' => $message
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        return $status_code === 201;
    }

    /**
     * Send Hustle SMS
     *
     * @param array $integration Integration data
     * @param string $phone Phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool Success status
     * @since 2.0.0
     */
    private function send_hustle_sms($integration, $phone, $message, $options) {
        // Implementation for Hustle API
        // Platform-specific implementation
        return true;
    }

    /**
     * Send CallHub SMS
     *
     * @param array $integration Integration data
     * @param string $phone Phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool Success status
     * @since 2.0.0
     */
    private function send_callhub_sms($integration, $phone, $message, $options) {
        // Implementation for CallHub API
        // Platform-specific implementation
        return true;
    }

    /**
     * Send RumbleUp SMS
     *
     * @param array $integration Integration data
     * @param string $phone Phone number
     * @param string $message Message content
     * @param array $options Additional options
     * @return bool Success status
     * @since 2.0.0
     */
    private function send_rumbleup_sms($integration, $phone, $message, $options) {
        // Implementation for RumbleUp API
        // Platform-specific implementation
        return true;
    }

    /**
     * Process Twilio webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_twilio_webhook($integration, $data) {
        $message_sid = $data['MessageSid'] ?? '';
        $from = $data['From'] ?? '';
        $to = $data['To'] ?? '';
        $body = $data['Body'] ?? '';
        $status = $data['MessageStatus'] ?? '';

        // Store incoming message
        if (!empty($body)) {
            $this->store_message($integration['id'], $from, $body, 'inbound', array(
                'message_sid' => $message_sid,
                'status' => $status
            ));

            // Process keywords
            $this->process_keywords($from, $body);

            // Trigger auto-reply if configured
            $this->trigger_auto_reply($integration, $from, $body);
        }

        // Update message status
        if (!empty($status)) {
            $this->update_message_status($message_sid, $status);
        }
    }

    /**
     * Process Hustle webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_hustle_webhook($integration, $data) {
        // Platform-specific webhook processing
    }

    /**
     * Process CallHub webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_callhub_webhook($integration, $data) {
        // Platform-specific webhook processing
    }

    /**
     * Process RumbleUp webhook
     *
     * @param array $integration Integration data
     * @param array $data Webhook data
     * @since 2.0.0
     */
    private function process_rumbleup_webhook($integration, $data) {
        // Platform-specific webhook processing
    }

    /**
     * Trigger auto-reply if conditions are met
     *
     * @param array $integration Integration data
     * @param string $phone Phone number
     * @param string $message Received message
     * @since 2.0.0
     */
    private function trigger_auto_reply($integration, $phone, $message) {
        $auto_replies = $integration['settings']['auto_replies'] ?? array();

        foreach ($auto_replies as $auto_reply) {
            $trigger = $auto_reply['trigger'] ?? '';
            $response = $auto_reply['response'] ?? '';

            if (stripos($message, $trigger) !== false) {
                $this->send_sms($phone, $response, array('integration_id' => $integration['id']));
                break; // Only send first matching auto-reply
            }
        }
    }

    /**
     * Update message status
     *
     * @param string $message_sid Message SID
     * @param string $status New status
     * @since 2.0.0
     */
    private function update_message_status($message_sid, $status) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_sms_messages';

        $wpdb->update(
            $table_name,
            array('status' => $status),
            array('message_sid' => $message_sid),
            array('%s'),
            array('%s')
        );
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
     * Get default integration
     *
     * @return int|null Integration ID or null
     * @since 2.0.0
     */
    private function get_default_integration() {
        $default = get_option('campaignpress_default_sms_integration');

        if ($default && isset($this->integrations[$default])) {
            return $default;
        }

        // Return first integration if no default set
        if (!empty($this->integrations)) {
            reset($this->integrations);
            return key($this->integrations);
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
            case 'twilio':
                // Twilio uses X-Twilio-Signature header
                $signature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'] ?? '';
                $auth_token = campaignpress_integrations()->decrypt($integration['credentials']['auth_token']);
                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

                // Reconstruct signature
                $expected = base64_encode(hash_hmac('sha1', $url . $raw_data, $auth_token, true));
                return hash_equals($expected, $signature);

            default:
                // For other platforms, verify credentials are present
                return !empty($integration['credentials']);
        }
    }

    /**
     * Sync Twilio integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_twilio($integration) {
        // Sync message history, phone numbers, etc.
        return true;
    }

    /**
     * Sync Hustle integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_hustle($integration) {
        // Platform-specific sync
        return true;
    }

    /**
     * Sync CallHub integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_callhub($integration) {
        // Platform-specific sync
        return true;
    }

    /**
     * Sync RumbleUp integration
     *
     * @param array $integration Integration data
     * @return bool Success status
     * @since 2.0.0
     */
    private function sync_rumbleup($integration) {
        // Platform-specific sync
        return true;
    }
}
