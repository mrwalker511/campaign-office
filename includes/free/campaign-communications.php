<?php
/**
 * Campaign Communications System
 *
 * Integrated SMS and Email campaign management for reaching voters,
 * volunteers, and donors through Twilio SMS and Mailchimp/Constant Contact.
 *
 * Features:
 * - Mass SMS campaigns (Twilio)
 * - Email campaigns (Mailchimp, Constant Contact)
 * - Audience segmentation
 * - Campaign templates
 * - Delivery tracking and analytics
 * - Unsubscribe management
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Campaign_Communications
 *
 * Manages SMS and email campaign communications
 */
class CP_Campaign_Communications {

    /**
     * Database table names
     *
     * @var string
     */
    private $campaigns_table;
    private $messages_table;
    private $subscribers_table;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->campaigns_table = $wpdb->prefix . 'cp_campaigns';
        $this->messages_table = $wpdb->prefix . 'cp_campaign_messages';
        $this->subscribers_table = $wpdb->prefix . 'cp_subscribers';

        // Database setup - only create tables when needed
        add_action('after_switch_theme', array($this, 'maybe_create_communications_tables'));
        add_action('admin_init', array($this, 'maybe_create_communications_tables'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // AJAX handlers
        add_action('wp_ajax_cp_send_test_sms', array($this, 'ajax_send_test_sms'));
        add_action('wp_ajax_cp_send_campaign', array($this, 'ajax_send_campaign'));
        add_action('wp_ajax_cp_get_campaign_stats', array($this, 'ajax_get_campaign_stats'));
        add_action('wp_ajax_cp_create_campaign', array($this, 'ajax_create_campaign'));

        // Shortcodes
        add_shortcode('cp_subscribe_form', array($this, 'render_subscribe_form'));
        add_shortcode('cp_unsubscribe_form', array($this, 'render_unsubscribe_form'));

        // Handle subscription forms
        add_action('wp_ajax_cp_subscribe', array($this, 'handle_subscribe'));
        add_action('wp_ajax_nopriv_cp_subscribe', array($this, 'handle_subscribe'));
    }

    /**
     * Maybe create communications tables - only runs once
     * Prevents table creation on every page load
     */
    public function maybe_create_communications_tables() {
        // Check if we've already created the tables for this version
        $db_version = get_option('cp_communications_db_version', '0');
        $current_version = '2.0.0';

        if (version_compare($db_version, $current_version, '<')) {
            $this->create_communications_tables();
            update_option('cp_communications_db_version', $current_version);
        }
    }

    /**
     * Create communications database tables
     */
    public function create_communications_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Campaigns table
        $sql_campaigns = "CREATE TABLE IF NOT EXISTS {$this->campaigns_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            type varchar(20) DEFAULT 'email',
            subject varchar(255) DEFAULT NULL,
            message text NOT NULL,
            audience varchar(50) DEFAULT 'all',
            segment_criteria text DEFAULT NULL,
            status varchar(20) DEFAULT 'draft',
            scheduled_at datetime DEFAULT NULL,
            sent_at datetime DEFAULT NULL,
            total_sent int(11) DEFAULT 0,
            total_delivered int(11) DEFAULT 0,
            total_failed int(11) DEFAULT 0,
            total_opened int(11) DEFAULT 0,
            total_clicked int(11) DEFAULT 0,
            created_by bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY type (type),
            KEY status (status),
            KEY scheduled_at (scheduled_at)
        ) $charset_collate;";

        // Messages table (individual message delivery tracking)
        $sql_messages = "CREATE TABLE IF NOT EXISTS {$this->messages_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) UNSIGNED NOT NULL,
            subscriber_id bigint(20) UNSIGNED DEFAULT NULL,
            recipient varchar(255) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            external_id varchar(255) DEFAULT NULL,
            sent_at datetime DEFAULT NULL,
            delivered_at datetime DEFAULT NULL,
            opened_at datetime DEFAULT NULL,
            clicked_at datetime DEFAULT NULL,
            failed_reason text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY campaign_id (campaign_id),
            KEY subscriber_id (subscriber_id),
            KEY status (status)
        ) $charset_collate;";

        // Subscribers table
        $sql_subscribers = "CREATE TABLE IF NOT EXISTS {$this->subscribers_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            email varchar(255) DEFAULT NULL,
            phone varchar(20) DEFAULT NULL,
            first_name varchar(100) DEFAULT NULL,
            last_name varchar(100) DEFAULT NULL,
            subscribed_email tinyint(1) DEFAULT 1,
            subscribed_sms tinyint(1) DEFAULT 1,
            subscriber_type varchar(50) DEFAULT 'general',
            tags text DEFAULT NULL,
            zip varchar(10) DEFAULT NULL,
            source varchar(100) DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            unsubscribed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY email (email),
            KEY phone (phone),
            KEY status (status),
            KEY subscriber_type (subscriber_type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_campaigns);
        dbDelta($sql_messages);
        dbDelta($sql_subscribers);

        update_option('cp_communications_tables_created', true);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Communications', 'campaignpress'),
            __('Communications', 'campaignpress'),
            'edit_posts',
            'cp-communications',
            array($this, 'render_campaigns_page'),
            'dashicons-email',
            25
        );

        add_submenu_page(
            'cp-communications',
            __('Campaigns', 'campaignpress'),
            __('Campaigns', 'campaignpress'),
            'edit_posts',
            'cp-communications',
            array($this, 'render_campaigns_page')
        );

        add_submenu_page(
            'cp-communications',
            __('Subscribers', 'campaignpress'),
            __('Subscribers', 'campaignpress'),
            'edit_posts',
            'cp-subscribers',
            array($this, 'render_subscribers_page')
        );

        add_submenu_page(
            'cp-communications',
            __('Settings', 'campaignpress'),
            __('Settings', 'campaignpress'),
            'manage_options',
            'cp-communications-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Twilio SMS settings
        register_setting('cp_communications', 'cp_twilio_account_sid');
        register_setting('cp_communications', 'cp_twilio_auth_token');
        register_setting('cp_communications', 'cp_twilio_phone_number');
        register_setting('cp_communications', 'cp_sms_enabled');

        // Mailchimp settings
        register_setting('cp_communications', 'cp_mailchimp_api_key');
        register_setting('cp_communications', 'cp_mailchimp_list_id');
        register_setting('cp_communications', 'cp_mailchimp_enabled');

        // Constant Contact settings
        register_setting('cp_communications', 'cp_constantcontact_api_key');
        register_setting('cp_communications', 'cp_constantcontact_access_token');
        register_setting('cp_communications', 'cp_constantcontact_enabled');

        // General settings
        register_setting('cp_communications', 'cp_default_sender_name');
        register_setting('cp_communications', 'cp_default_sender_email');
        register_setting('cp_communications', 'cp_enable_double_optin');
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        $twilio_sid = get_option('cp_twilio_account_sid', '');
        $twilio_token = get_option('cp_twilio_auth_token', '');
        $twilio_phone = get_option('cp_twilio_phone_number', '');
        $sms_enabled = get_option('cp_sms_enabled', false);

        $mailchimp_key = get_option('cp_mailchimp_api_key', '');
        $mailchimp_list = get_option('cp_mailchimp_list_id', '');
        $mailchimp_enabled = get_option('cp_mailchimp_enabled', false);

        $sender_name = get_option('cp_default_sender_name', '');
        $sender_email = get_option('cp_default_sender_email', '');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Communications Settings', 'campaignpress'); ?></h1>

            <?php settings_errors('cp_communications'); ?>

            <form method="post" action="options.php">
                <?php settings_fields('cp_communications'); ?>

                <!-- Twilio SMS Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-smartphone"></span>
                        <?php esc_html_e('Twilio SMS Integration', 'campaignpress'); ?>
                    </h2>
                    <p class="description">
                        <?php
                        printf(
                            esc_html__('Send mass SMS campaigns to supporters. Get your API credentials from %s', 'campaignpress'),
                            '<a href="https://www.twilio.com/console" target="_blank">Twilio Console</a>'
                        );
                        ?>
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Enable SMS', 'campaignpress'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cp_sms_enabled" value="1" <?php checked($sms_enabled, 1); ?>>
                                    <?php esc_html_e('Enable Twilio SMS campaigns', 'campaignpress'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_twilio_account_sid"><?php esc_html_e('Account SID', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_twilio_account_sid" name="cp_twilio_account_sid"
                                       value="<?php echo esc_attr($twilio_sid); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_twilio_auth_token"><?php esc_html_e('Auth Token', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="cp_twilio_auth_token" name="cp_twilio_auth_token"
                                       value="<?php echo esc_attr($twilio_token); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_twilio_phone_number"><?php esc_html_e('Phone Number', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="tel" id="cp_twilio_phone_number" name="cp_twilio_phone_number"
                                       value="<?php echo esc_attr($twilio_phone); ?>" class="regular-text">
                                <p class="description"><?php esc_html_e('Format: +1234567890', 'campaignpress'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <?php if ($sms_enabled && $twilio_sid && $twilio_token) : ?>
                        <p>
                            <button type="button" class="button" id="cp-test-sms-btn">
                                <?php esc_html_e('Send Test SMS', 'campaignpress'); ?>
                            </button>
                            <input type="tel" id="cp-test-phone" placeholder="+1234567890" class="regular-text">
                        </p>
                        <div id="cp-test-sms-result" style="margin-top: 10px;"></div>
                    <?php endif; ?>
                </div>

                <!-- Mailchimp Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-email"></span>
                        <?php esc_html_e('Mailchimp Integration', 'campaignpress'); ?>
                    </h2>
                    <p class="description">
                        <?php
                        printf(
                            esc_html__('Sync subscribers with Mailchimp. Get your API key from %s', 'campaignpress'),
                            '<a href="https://admin.mailchimp.com/account/api/" target="_blank">Mailchimp</a>'
                        );
                        ?>
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Enable Mailchimp', 'campaignpress'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cp_mailchimp_enabled" value="1" <?php checked($mailchimp_enabled, 1); ?>>
                                    <?php esc_html_e('Sync subscribers with Mailchimp', 'campaignpress'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_mailchimp_api_key"><?php esc_html_e('API Key', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="cp_mailchimp_api_key" name="cp_mailchimp_api_key"
                                       value="<?php echo esc_attr($mailchimp_key); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_mailchimp_list_id"><?php esc_html_e('Audience ID', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_mailchimp_list_id" name="cp_mailchimp_list_id"
                                       value="<?php echo esc_attr($mailchimp_list); ?>" class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- General Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2><?php esc_html_e('General Settings', 'campaignpress'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cp_default_sender_name"><?php esc_html_e('Sender Name', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_default_sender_name" name="cp_default_sender_name"
                                       value="<?php echo esc_attr($sender_name); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_default_sender_email"><?php esc_html_e('Sender Email', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="cp_default_sender_email" name="cp_default_sender_email"
                                       value="<?php echo esc_attr($sender_email); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Double Opt-in', 'campaignpress'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cp_enable_double_optin" value="1" <?php checked(get_option('cp_enable_double_optin'), 1); ?>>
                                    <?php esc_html_e('Require email confirmation for new subscribers', 'campaignpress'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#cp-test-sms-btn').click(function() {
                var phone = $('#cp-test-phone').val();
                if (!phone) {
                    alert('<?php esc_js_e('Please enter a phone number', 'campaignpress'); ?>');
                    return;
                }

                $(this).prop('disabled', true).text('<?php esc_js_e('Sending...', 'campaignpress'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'cp_send_test_sms',
                        phone: phone,
                        _wpnonce: '<?php echo wp_create_nonce('cp_test_sms'); ?>'
                    },
                    success: function(response) {
                        $('#cp-test-sms-result').html(
                            '<div class="notice notice-' + (response.success ? 'success' : 'error') + '"><p>' +
                            response.data.message + '</p></div>'
                        );
                        $('#cp-test-sms-btn').prop('disabled', false).text('<?php esc_js_e('Send Test SMS', 'campaignpress'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render campaigns page
     */
    public function render_campaigns_page() {
        global $wpdb;

        $campaigns = $wpdb->get_results("
            SELECT * FROM {$this->campaigns_table}
            ORDER BY created_at DESC
            LIMIT 50
        ");

        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Communication Campaigns', 'campaignpress'); ?>
                <a href="#" class="page-title-action" id="cp-new-campaign-btn">
                    <?php esc_html_e('New Campaign', 'campaignpress'); ?>
                </a>
            </h1>

            <?php wp_nonce_field('cp_campaign_nonce_action', 'cp_campaign_nonce'); ?>

            <div class="cp-campaigns-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin: 2rem 0;">
                <?php
                $total_campaigns = count($campaigns);
                $total_sent = $wpdb->get_var("SELECT SUM(total_sent) FROM {$this->campaigns_table} WHERE status = 'sent'");
                $total_delivered = $wpdb->get_var("SELECT SUM(total_delivered) FROM {$this->campaigns_table}");
                $avg_open_rate = $wpdb->get_var("SELECT AVG(total_opened / NULLIF(total_delivered, 0) * 100) FROM {$this->campaigns_table} WHERE total_delivered > 0");
                ?>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #0073aa;">
                    <div style="font-size: 2rem; font-weight: 700; color: #0073aa;"><?php echo esc_html($total_campaigns); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Total Campaigns', 'campaignpress'); ?></div>
                </div>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #00a32a;">
                    <div style="font-size: 2rem; font-weight: 700; color: #00a32a;"><?php echo esc_html(number_format($total_sent ?? 0)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Messages Sent', 'campaignpress'); ?></div>
                </div>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #d63638;">
                    <div style="font-size: 2rem; font-weight: 700; color: #d63638;"><?php echo esc_html(number_format($total_delivered ?? 0)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Delivered', 'campaignpress'); ?></div>
                </div>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #dba617;">
                    <div style="font-size: 2rem; font-weight: 700; color: #dba617;"><?php echo esc_html(number_format($avg_open_rate ?? 0, 1)); ?>%</div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Avg. Open Rate', 'campaignpress'); ?></div>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Campaign', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Type', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Status', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Sent', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Delivered', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Opened', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Date', 'campaignpress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($campaigns)) : ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                <?php esc_html_e('No campaigns yet. Create your first campaign to get started!', 'campaignpress'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($campaigns as $campaign) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($campaign->name); ?></strong></td>
                                <td>
                                    <?php if ($campaign->type === 'sms') : ?>
                                        <span class="dashicons dashicons-smartphone"></span> <?php esc_html_e('SMS', 'campaignpress'); ?>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-email"></span> <?php esc_html_e('Email', 'campaignpress'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_colors = array(
                                        'draft' => '#999',
                                        'scheduled' => '#dba617',
                                        'sending' => '#0073aa',
                                        'sent' => '#00a32a',
                                        'failed' => '#d63638',
                                    );
                                    $color = $status_colors[$campaign->status] ?? '#999';
                                    ?>
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: <?php echo esc_attr($color); ?>; margin-right: 5px;"></span>
                                    <?php echo esc_html(ucfirst($campaign->status)); ?>
                                </td>
                                <td><?php echo esc_html(number_format($campaign->total_sent)); ?></td>
                                <td><?php echo esc_html(number_format($campaign->total_delivered)); ?></td>
                                <td>
                                    <?php
                                    $open_rate = $campaign->total_delivered > 0 ? ($campaign->total_opened / $campaign->total_delivered * 100) : 0;
                                    echo esc_html(number_format($open_rate, 1)) . '%';
                                    ?>
                                </td>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($campaign->created_at))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- New Campaign Modal -->
            <div id="cp-new-campaign-modal" class="cp-modal" style="display: none;">
                <div class="cp-modal-overlay"></div>
                <div class="cp-modal-content" style="max-width: 600px;">
                    <div class="cp-modal-header">
                        <h2><?php esc_html_e('Create New Campaign', 'campaignpress'); ?></h2>
                        <button type="button" class="cp-modal-close">&times;</button>
                    </div>
                    <form id="cp-new-campaign-form">
                        <div class="cp-modal-body">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="campaign_name"><?php esc_html_e('Campaign Name', 'campaignpress'); ?> <span class="required">*</span></label>
                                    </th>
                                    <td>
                                        <input type="text" id="campaign_name" name="campaign_name" class="regular-text" required>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="campaign_type"><?php esc_html_e('Type', 'campaignpress'); ?></label>
                                    </th>
                                    <td>
                                        <select id="campaign_type" name="campaign_type" class="regular-text">
                                            <option value="email"><?php esc_html_e('Email', 'campaignpress'); ?></option>
                                            <option value="sms"><?php esc_html_e('SMS', 'campaignpress'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="campaign_subject"><?php esc_html_e('Subject', 'campaignpress'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="campaign_subject" name="campaign_subject" class="regular-text">
                                        <p class="description"><?php esc_html_e('Email subject line (not used for SMS)', 'campaignpress'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="campaign_message"><?php esc_html_e('Message', 'campaignpress'); ?> <span class="required">*</span></label>
                                    </th>
                                    <td>
                                        <textarea id="campaign_message" name="campaign_message" rows="6" class="large-text" required></textarea>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="campaign_audience"><?php esc_html_e('Audience', 'campaignpress'); ?></label>
                                    </th>
                                    <td>
                                        <select id="campaign_audience" name="campaign_audience" class="regular-text">
                                            <option value="all"><?php esc_html_e('All Subscribers', 'campaignpress'); ?></option>
                                            <option value="donors"><?php esc_html_e('Donors Only', 'campaignpress'); ?></option>
                                            <option value="volunteers"><?php esc_html_e('Volunteers Only', 'campaignpress'); ?></option>
                                            <option value="new"><?php esc_html_e('New Subscribers (Last 30 days)', 'campaignpress'); ?></option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="cp-modal-footer">
                            <button type="button" class="button cp-modal-cancel"><?php esc_html_e('Cancel', 'campaignpress'); ?></button>
                            <button type="submit" class="button button-primary" id="cp-save-campaign-btn"><?php esc_html_e('Create Campaign', 'campaignpress'); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <style>
                .cp-modal { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 100000; }
                .cp-modal-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); }
                .cp-modal-content { position: relative; background: #fff; margin: 50px auto; border-radius: 4px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); max-height: calc(100vh - 100px); overflow-y: auto; }
                .cp-modal-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid #ddd; }
                .cp-modal-header h2 { margin: 0; }
                .cp-modal-close { background: none; border: none; font-size: 24px; cursor: pointer; padding: 0; line-height: 1; }
                .cp-modal-body { padding: 20px; }
                .cp-modal-footer { padding: 15px 20px; border-top: 1px solid #ddd; text-align: right; }
                .cp-modal-footer .button { margin-left: 10px; }
                .required { color: #d63638; }
            </style>

            <script>
            jQuery(document).ready(function($) {
                // Show new campaign modal
                $('#cp-new-campaign-btn').on('click', function(e) {
                    e.preventDefault();
                    $('#cp-new-campaign-form')[0].reset();
                    $('#cp-new-campaign-modal').fadeIn(200);
                });

                // Close modal handlers
                $('.cp-modal-close, .cp-modal-cancel, .cp-modal-overlay').on('click', function(e) {
                    if (e.target === this) {
                        $('#cp-new-campaign-modal').fadeOut(200);
                    }
                });

                // Close on Escape key
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape' && $('#cp-new-campaign-modal').is(':visible')) {
                        $('#cp-new-campaign-modal').fadeOut(200);
                    }
                });

                // Save campaign
                $('#cp-new-campaign-form').on('submit', function(e) {
                    e.preventDefault();

                    var $btn = $('#cp-save-campaign-btn');
                    $btn.prop('disabled', true).text('<?php esc_js_e('Creating...', 'campaignpress'); ?>');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'cp_create_campaign',
                            nonce: $('#cp_campaign_nonce').val(),
                            name: $('#campaign_name').val(),
                            type: $('#campaign_type').val(),
                            subject: $('#campaign_subject').val(),
                            message: $('#campaign_message').val(),
                            audience: $('#campaign_audience').val()
                        },
                        success: function(response) {
                            $btn.prop('disabled', false).text('<?php esc_js_e('Create Campaign', 'campaignpress'); ?>');
                            if (response.success) {
                                $('#cp-new-campaign-modal').fadeOut(200);
                                location.reload();
                            } else {
                                alert(response.data.message || '<?php esc_js_e('Failed to create campaign', 'campaignpress'); ?>');
                            }
                        },
                        error: function() {
                            $btn.prop('disabled', false).text('<?php esc_js_e('Create Campaign', 'campaignpress'); ?>');
                            alert('<?php esc_js_e('Error creating campaign. Please try again.', 'campaignpress'); ?>');
                        }
                    });
                });

                // Toggle subject field visibility based on campaign type
                $('#campaign_type').on('change', function() {
                    var $subjectRow = $('#campaign_subject').closest('tr');
                    if ($(this).val() === 'sms') {
                        $subjectRow.hide();
                    } else {
                        $subjectRow.show();
                    }
                });
            });
            </script>
        </div>
        <?php
    }

    /**
     * Render subscribers page
     */
    public function render_subscribers_page() {
        global $wpdb;

        $subscribers = $wpdb->get_results("
            SELECT * FROM {$this->subscribers_table}
            WHERE status = 'active'
            ORDER BY created_at DESC
            LIMIT 100
        ");

        $total_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM {$this->subscribers_table} WHERE status = 'active'");
        $email_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM {$this->subscribers_table} WHERE status = 'active' AND subscribed_email = 1");
        $sms_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM {$this->subscribers_table} WHERE status = 'active' AND subscribed_sms = 1");

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Subscribers', 'campaignpress'); ?></h1>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin: 2rem 0;">
                <div style="background: #fff; padding: 1.5rem; border-left: 4px solid #0073aa;">
                    <div style="font-size: 2rem; font-weight: 700; color: #0073aa;"><?php echo esc_html(number_format($total_subscribers)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Total Subscribers', 'campaignpress'); ?></div>
                </div>
                <div style="background: #fff; padding: 1.5rem; border-left: 4px solid #00a32a;">
                    <div style="font-size: 2rem; font-weight: 700; color: #00a32a;"><?php echo esc_html(number_format($email_subscribers)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Email Subscribers', 'campaignpress'); ?></div>
                </div>
                <div style="background: #fff; padding: 1.5rem; border-left: 4px solid #dba617;">
                    <div style="font-size: 2rem; font-weight: 700; color: #dba617;"><?php echo esc_html(number_format($sms_subscribers)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('SMS Subscribers', 'campaignpress'); ?></div>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Email', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Phone', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Type', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Subscribed', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Date', 'campaignpress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribers as $sub) : ?>
                        <tr>
                            <td><?php echo esc_html($sub->first_name . ' ' . $sub->last_name); ?></td>
                            <td><?php echo esc_html($sub->email); ?></td>
                            <td><?php echo esc_html($sub->phone); ?></td>
                            <td><?php echo esc_html(ucfirst($sub->subscriber_type)); ?></td>
                            <td>
                                <?php if ($sub->subscribed_email) : ?>
                                    <span class="dashicons dashicons-email"></span>
                                <?php endif; ?>
                                <?php if ($sub->subscribed_sms) : ?>
                                    <span class="dashicons dashicons-smartphone"></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($sub->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render subscribe form shortcode
     */
    public function render_subscribe_form($atts) {
        $atts = shortcode_atts(array(
            'type' => 'both',
            'title' => __('Stay Connected', 'campaignpress'),
        ), $atts, 'cp_subscribe_form');

        ob_start();
        ?>
        <div class="cp-subscribe-form-wrapper">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <form class="cp-subscribe-form">
                <?php wp_nonce_field('cp_subscribe', 'cp_subscribe_nonce'); ?>
                <div class="form-row">
                    <input type="text" name="first_name" placeholder="<?php esc_attr_e('First Name', 'campaignpress'); ?>" required class="cp-input">
                    <input type="text" name="last_name" placeholder="<?php esc_attr_e('Last Name', 'campaignpress'); ?>" required class="cp-input">
                </div>
                <?php if (in_array($atts['type'], array('both', 'email'), true)) : ?>
                    <div class="form-field">
                        <input type="email" name="email" placeholder="<?php esc_attr_e('Email Address', 'campaignpress'); ?>" required class="cp-input">
                    </div>
                <?php endif; ?>
                <?php if (in_array($atts['type'], array('both', 'sms'), true)) : ?>
                    <div class="form-field">
                        <input type="tel" name="phone" placeholder="<?php esc_attr_e('Phone Number', 'campaignpress'); ?>" class="cp-input">
                    </div>
                <?php endif; ?>
                <div class="form-field">
                    <input type="text" name="zip" placeholder="<?php esc_attr_e('ZIP Code', 'campaignpress'); ?>" class="cp-input">
                </div>
                <div class="form-actions">
                    <button type="submit" class="cp-button cp-button-primary">
                        <?php esc_html_e('Subscribe', 'campaignpress'); ?>
                    </button>
                </div>
                <div class="cp-form-message" style="display:none; margin-top: 1rem; padding: 0.75rem; border-radius: 4px;"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render unsubscribe form
     */
    public function render_unsubscribe_form($atts) {
        ob_start();
        ?>
        <div class="cp-unsubscribe-wrapper">
            <p><?php esc_html_e('To unsubscribe from our communications, please enter your email address below.', 'campaignpress'); ?></p>
            <form class="cp-unsubscribe-form">
                <input type="email" name="email" placeholder="<?php esc_attr_e('Email Address', 'campaignpress'); ?>" required class="cp-input">
                <button type="submit" class="cp-button"><?php esc_html_e('Unsubscribe', 'campaignpress'); ?></button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Handlers
     */

    public function ajax_send_test_sms() {
        check_ajax_referer('cp_test_sms');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaignpress')));
        }

        $phone = sanitize_text_field($_POST['phone']);

        $result = $this->send_sms($phone, __('This is a test message from CampaignPress. Your SMS integration is working correctly!', 'campaignpress'));

        if ($result['success']) {
            wp_send_json_success(array('message' => __('Test SMS sent successfully!', 'campaignpress')));
        } else {
            wp_send_json_error(array('message' => __('Failed to send SMS: ', 'campaignpress') . $result['error']));
        }
    }

    public function handle_subscribe() {
        check_ajax_referer('cp_subscribe', 'cp_subscribe_nonce');

        // Rate limiting: 3 subscriptions per hour per IP
        if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('subscribe', 3, 3600)) {
            wp_send_json_error(array('message' => __('Too many subscription attempts. Please try again later.', 'campaignpress')));
        }

        global $wpdb;

        $data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'zip' => sanitize_text_field($_POST['zip'] ?? ''),
            'source' => 'website_form',
            'subscriber_type' => 'general',
        );

        $wpdb->insert($this->subscribers_table, $data);

        $mailchimp_warning = '';

        // Sync with Mailchimp if enabled
        if (get_option('cp_mailchimp_enabled')) {
            $sync_result = $this->sync_to_mailchimp($data);
            if (!$sync_result) {
                // Subscription saved locally but Mailchimp sync failed
                $mailchimp_warning = ' ' . __('Note: Email added to local list, but sync with Mailchimp failed. Please check your Mailchimp settings.', 'campaignpress');

                // Store failed sync for admin notification
                $failed_syncs = get_option('cp_mailchimp_failed_syncs', array());
                $failed_syncs[] = array(
                    'email' => $data['email'],
                    'time' => current_time('mysql'),
                );
                // Keep only last 50 failed syncs
                $failed_syncs = array_slice($failed_syncs, -50);
                update_option('cp_mailchimp_failed_syncs', $failed_syncs);
            }
        }

        wp_send_json_success(array('message' => __('Thank you for subscribing!', 'campaignpress') . $mailchimp_warning));
    }

    /**
     * Helper methods
     */

    private function send_sms($to, $message) {
        $account_sid = get_option('cp_twilio_account_sid');
        $auth_token = get_option('cp_twilio_auth_token');
        $from_number = get_option('cp_twilio_phone_number');

        if (!$account_sid || !$auth_token || !$from_number) {
            return array('success' => false, 'error' => 'Twilio not configured');
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";

        // Validate URL before making request (SSRF protection)
        if (class_exists('CampaignPress_URL_Validator')) {
            $url_validator = CampaignPress_URL_Validator::get_instance();
            $validation = $url_validator->validate_url($url);
            if (is_wp_error($validation)) {
                return array('success' => false, 'error' => 'URL validation failed: ' . $validation->get_error_message());
            }
        }

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token),
            ),
            'body' => array(
                'To' => $to,
                'From' => $from_number,
                'Body' => $message,
            ),
        ));

        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Validate Twilio API response structure
        if (!$this->validate_twilio_response($body)) {
            // Log suspicious response
            if (class_exists('CampaignPress_Security_Logger')) {
                CampaignPress_Security_Logger::get_instance()->log_event(
                    'external_api_validation_failed',
                    'Twilio API response validation failed',
                    array(
                        'response_keys' => array_keys($body),
                        'expected_keys' => array('sid', 'status', 'date_created')
                    ),
                    'high'
                );
            }
            return array('success' => false, 'error' => 'Invalid response from SMS provider');
        }

        if (isset($body['sid'])) {
            return array('success' => true, 'sid' => $body['sid']);
        }

        return array('success' => false, 'error' => $body['message'] ?? 'Unknown error');
    }

    private function sync_to_mailchimp($subscriber_data) {
        $api_key = get_option('cp_mailchimp_api_key');
        $list_id = get_option('cp_mailchimp_list_id');

        if (!$api_key || !$list_id) {
            return false;
        }

        $datacenter = substr($api_key, strpos($api_key, '-') + 1);
        $url = "https://{$datacenter}.api.mailchimp.com/3.0/lists/{$list_id}/members";

        // Validate URL before making request (SSRF protection)
        if (class_exists('CampaignPress_URL_Validator')) {
            $url_validator = CampaignPress_URL_Validator::get_instance();
            $validation = $url_validator->validate_url($url);
            if (is_wp_error($validation)) {
                // Log failed sync attempt
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('CampaignPress Mailchimp URL Validation Error: ' . $validation->get_error_message());
                }
                return false;
            }
        }

        $data = array(
            'email_address' => $subscriber_data['email'],
            'status' => 'subscribed',
            'merge_fields' => array(
                'FNAME' => $subscriber_data['first_name'],
                'LNAME' => $subscriber_data['last_name'],
            ),
        );

        if (!empty($subscriber_data['phone'])) {
            $data['merge_fields']['PHONE'] = $subscriber_data['phone'];
        }

        $response = wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('anystring:' . $api_key),
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
        ));

        // Check for errors
        if (is_wp_error($response)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('CampaignPress Mailchimp Error: ' . $response->get_error_message());
            }
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Validate Mailchimp API response structure
        if (!$this->validate_mailchimp_response($body, $response_code)) {
            // Log suspicious response
            if (class_exists('CampaignPress_Security_Logger')) {
                CampaignPress_Security_Logger::get_instance()->log_event(
                    'external_api_validation_failed',
                    'Mailchimp API response validation failed',
                    array(
                        'response_code' => $response_code,
                        'response_keys' => is_array($body) ? array_keys($body) : array(),
                        'expected_structure' => 'Mailchimp API response with status and data fields'
                    ),
                    'medium'
                );
            }
            return false;
        }

        $error_message = $body['detail'] ?? 'Unknown error';

        if ($response_code < 200 || $response_code >= 300) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('CampaignPress Mailchimp Error: ' . $error_message . ' (Code: ' . $response_code . ')');
            }
            return false;
        }

        return true;
    }

    public function ajax_send_campaign() {
        check_ajax_referer('cp_campaign_nonce_action', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaignpress')));
        }

        $campaign_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;
        if (!$campaign_id) {
            wp_send_json_error(array('message' => __('Invalid campaign ID', 'campaignpress')));
        }

        global $wpdb;
        $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->campaigns_table} WHERE id = %d", $campaign_id));

        if (!$campaign) {
            wp_send_json_error(array('message' => __('Campaign not found', 'campaignpress')));
        }

        // Mark as sending
        $wpdb->update($this->campaigns_table, array(
            'status' => 'sending',
            'sent_at' => current_time('mysql')
        ), array('id' => $campaign_id));

        // Placeholder: In a real implementation, this would trigger a background process or cron job
        // to actually send the emails/SMS to all subscribers in the target audience.

        wp_send_json_success(array('message' => __('Campaign sending started!', 'campaignpress')));
    }

    public function ajax_get_campaign_stats() {
        check_ajax_referer('cp_campaign_nonce_action', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaignpress')));
        }

        $campaign_id = isset($_POST['campaign_id']) ? absint($_POST['campaign_id']) : 0;
        if (!$campaign_id) {
            wp_send_json_error(array('message' => __('Invalid campaign ID', 'campaignpress')));
        }

        global $wpdb;
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT total_sent, total_delivered, total_opened, total_clicked, total_failed FROM {$this->campaigns_table} WHERE id = %d",
            $campaign_id
        ), ARRAY_A);

        if (!$stats) {
            wp_send_json_error(array('message' => __('Stats not found', 'campaignpress')));
        }

        wp_send_json_success($stats);
    }

    /**
     * AJAX handler to create a new campaign
     */
    public function ajax_create_campaign() {
        check_ajax_referer('cp_campaign_nonce_action', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission denied', 'campaignpress')));
        }

        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'email';
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $audience = isset($_POST['audience']) ? sanitize_text_field($_POST['audience']) : 'all';

        if (empty($name)) {
            wp_send_json_error(array('message' => __('Campaign name is required', 'campaignpress')));
        }

        if (empty($message)) {
            wp_send_json_error(array('message' => __('Campaign message is required', 'campaignpress')));
        }

        // Validate type
        if (!in_array($type, array('email', 'sms'), true)) {
            $type = 'email';
        }

        // Validate audience
        if (!in_array($audience, array('all', 'donors', 'volunteers', 'new'), true)) {
            $audience = 'all';
        }

        global $wpdb;

        $result = $wpdb->insert(
            $this->campaigns_table,
            array(
                'name' => $name,
                'type' => $type,
                'subject' => $subject,
                'message' => $message,
                'audience' => $audience,
                'status' => 'draft',
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to create campaign. Please try again.', 'campaignpress')));
        }

        $campaign_id = $wpdb->insert_id;

        wp_send_json_success(array(
            'message' => __('Campaign created successfully!', 'campaignpress'),
            'campaign_id' => $campaign_id
        ));
    }

    /**
     * Validate Twilio API response structure
     *
     * @param array $response The response body from Twilio API
     * @return bool True if response is valid, false otherwise
     */
    private function validate_twilio_response($response) {
        // Check if response is valid JSON array/object
        if (!is_array($response)) {
            return false;
        }

        // Check for expected Twilio response fields
        $expected_fields = array('sid', 'status', 'date_created');
        foreach ($expected_fields as $field) {
            if (!isset($response[$field])) {
                return false;
            }
        }

        // Validate SID format (starts with "SM" for messages)
        if (isset($response['sid']) && !preg_match('/^SM[a-fA-F0-9]{32}$/', $response['sid'])) {
            return false;
        }

        // Validate status field
        $valid_statuses = array('queued', 'sending', 'sent', 'failed', 'delivered', 'undelivered', 'receiving', 'received');
        if (isset($response['status']) && !in_array($response['status'], $valid_statuses, true)) {
            return false;
        }

        // Check for suspicious additional fields (potential injection)
        $allowed_fields = array('sid', 'date_created', 'date_updated', 'date_sent', 'account_sid', 'from', 'to', 'body', 'status', 'num_segments', 'direction', 'api_version', 'price', 'price_unit', 'error_code', 'error_message', 'uri', 'subresource_uris');
        foreach ($response as $key => $value) {
            if (!in_array($key, $allowed_fields, true)) {
                // Unknown field, but don't fail validation - just log it
                if (class_exists('CampaignPress_Security_Logger')) {
                    CampaignPress_Security_Logger::get_instance()->log_event(
                        'external_api_unknown_field',
                        'Twilio API response contains unknown field',
                        array(
                            'field_name' => $key,
                            'field_value' => $value
                        ),
                        'low'
                    );
                }
            }
        }

        return true;
    }

    /**
     * Validate Mailchimp API response structure
     *
     * @param array|object $response The response body from Mailchimp API
     * @param int $response_code The HTTP response code
     * @return bool True if response is valid, false otherwise
     */
    private function validate_mailchimp_response($response, $response_code) {
        // Check if response is valid JSON array/object
        if (!is_array($response) && !is_object($response)) {
            return false;
        }

        // Convert to array for easier handling
        $response = (array) $response;

        // Check for success response (2xx codes)
        if ($response_code >= 200 && $response_code < 300) {
            // Successful responses should have at least an id or email_address
            if (!isset($response['id']) && !isset($response['email_address'])) {
                return false;
            }
        }

        // Check for error response (4xx, 5xx codes)
        if ($response_code >= 400) {
            // Error responses should have a detail field
            if (!isset($response['detail'])) {
                return false;
            }

            // Validate error detail is a string
            if (!is_string($response['detail'])) {
                return false;
            }
        }

        // Validate expected fields if present
        if (isset($response['id']) && !preg_match('/^[a-zA-Z0-9]+$/', $response['id'])) {
            return false; // Invalid ID format
        }

        if (isset($response['email_address']) && !is_email($response['email_address'])) {
            return false; // Invalid email format
        }

        // Check for suspicious fields
        $suspicious_fields = array('__construct', '__wakeup', 'system', 'exec', 'shell_exec', 'passthru', 'eval');
        foreach ($response as $key => $value) {
            if (in_array(strtolower($key), $suspicious_fields, true)) {
                if (class_exists('CampaignPress_Security_Logger')) {
                    CampaignPress_Security_Logger::get_instance()->log_event(
                        'external_api_potential_injection',
                        'Mailchimp API response contains suspicious field',
                        array(
                            'field_name' => $key,
                            'field_value' => $value
                        ),
                        'high'
                    );
                }
                return false;
            }
        }

        return true;
    }
}

// Initialize communications system
new CP_Campaign_Communications();
