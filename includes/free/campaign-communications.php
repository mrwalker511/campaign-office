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

        // Database setup
        add_action('after_setup_theme', array($this, 'create_communications_tables'));

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // AJAX handlers
        add_action('wp_ajax_cp_send_test_sms', array($this, 'ajax_send_test_sms'));
        add_action('wp_ajax_cp_send_campaign', array($this, 'ajax_send_campaign'));
        add_action('wp_ajax_cp_get_campaign_stats', array($this, 'ajax_get_campaign_stats'));

        // Shortcodes
        add_shortcode('cp_subscribe_form', array($this, 'render_subscribe_form'));
        add_shortcode('cp_unsubscribe_form', array($this, 'render_unsubscribe_form'));

        // Handle subscription forms
        add_action('wp_ajax_cp_subscribe', array($this, 'handle_subscribe'));
        add_action('wp_ajax_nopriv_cp_subscribe', array($this, 'handle_subscribe'));
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
            __('Communications', 'campaign-office'),
            __('Communications', 'campaign-office'),
            'edit_posts',
            'cp-communications',
            array($this, 'render_campaigns_page'),
            'dashicons-email',
            25
        );

        add_submenu_page(
            'cp-communications',
            __('Campaigns', 'campaign-office'),
            __('Campaigns', 'campaign-office'),
            'edit_posts',
            'cp-communications',
            array($this, 'render_campaigns_page')
        );

        add_submenu_page(
            'cp-communications',
            __('Subscribers', 'campaign-office'),
            __('Subscribers', 'campaign-office'),
            'edit_posts',
            'cp-subscribers',
            array($this, 'render_subscribers_page')
        );

        add_submenu_page(
            'cp-communications',
            __('Settings', 'campaign-office'),
            __('Settings', 'campaign-office'),
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
            <h1><?php esc_html_e('Communications Settings', 'campaign-office'); ?></h1>

            <?php settings_errors('cp_communications'); ?>

            <form method="post" action="options.php">
                <?php settings_fields('cp_communications'); ?>

                <!-- Twilio SMS Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-smartphone"></span>
                        <?php esc_html_e('Twilio SMS Integration', 'campaign-office'); ?>
                    </h2>
                    <p class="description">
                        <?php
                        printf(
                            esc_html__('Send mass SMS campaigns to supporters. Get your API credentials from %s', 'campaign-office'),
                            '<a href="https://www.twilio.com/console" target="_blank">Twilio Console</a>'
                        );
                        ?>
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Enable SMS', 'campaign-office'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cp_sms_enabled" value="1" <?php checked($sms_enabled, 1); ?>>
                                    <?php esc_html_e('Enable Twilio SMS campaigns', 'campaign-office'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_twilio_account_sid"><?php esc_html_e('Account SID', 'campaign-office'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_twilio_account_sid" name="cp_twilio_account_sid"
                                       value="<?php echo esc_attr($twilio_sid); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_twilio_auth_token"><?php esc_html_e('Auth Token', 'campaign-office'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="cp_twilio_auth_token" name="cp_twilio_auth_token"
                                       value="<?php echo esc_attr($twilio_token); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_twilio_phone_number"><?php esc_html_e('Phone Number', 'campaign-office'); ?></label>
                            </th>
                            <td>
                                <input type="tel" id="cp_twilio_phone_number" name="cp_twilio_phone_number"
                                       value="<?php echo esc_attr($twilio_phone); ?>" class="regular-text">
                                <p class="description"><?php esc_html_e('Format: +1234567890', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                    </table>

                    <?php if ($sms_enabled && $twilio_sid && $twilio_token) : ?>
                        <p>
                            <button type="button" class="button" id="cp-test-sms-btn">
                                <?php esc_html_e('Send Test SMS', 'campaign-office'); ?>
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
                        <?php esc_html_e('Mailchimp Integration', 'campaign-office'); ?>
                    </h2>
                    <p class="description">
                        <?php
                        printf(
                            esc_html__('Sync subscribers with Mailchimp. Get your API key from %s', 'campaign-office'),
                            '<a href="https://admin.mailchimp.com/account/api/" target="_blank">Mailchimp</a>'
                        );
                        ?>
                    </p>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Enable Mailchimp', 'campaign-office'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cp_mailchimp_enabled" value="1" <?php checked($mailchimp_enabled, 1); ?>>
                                    <?php esc_html_e('Sync subscribers with Mailchimp', 'campaign-office'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_mailchimp_api_key"><?php esc_html_e('API Key', 'campaign-office'); ?></label>
                            </th>
                            <td>
                                <input type="password" id="cp_mailchimp_api_key" name="cp_mailchimp_api_key"
                                       value="<?php echo esc_attr($mailchimp_key); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_mailchimp_list_id"><?php esc_html_e('Audience ID', 'campaign-office'); ?></label>
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
                    <h2><?php esc_html_e('General Settings', 'campaign-office'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cp_default_sender_name"><?php esc_html_e('Sender Name', 'campaign-office'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_default_sender_name" name="cp_default_sender_name"
                                       value="<?php echo esc_attr($sender_name); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_default_sender_email"><?php esc_html_e('Sender Email', 'campaign-office'); ?></label>
                            </th>
                            <td>
                                <input type="email" id="cp_default_sender_email" name="cp_default_sender_email"
                                       value="<?php echo esc_attr($sender_email); ?>" class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Double Opt-in', 'campaign-office'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cp_enable_double_optin" value="1" <?php checked(get_option('cp_enable_double_optin'), 1); ?>>
                                    <?php esc_html_e('Require email confirmation for new subscribers', 'campaign-office'); ?>
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
                    alert('<?php esc_js_e('Please enter a phone number', 'campaign-office'); ?>');
                    return;
                }

                $(this).prop('disabled', true).text('<?php esc_js_e('Sending...', 'campaign-office'); ?>');

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
                        $('#cp-test-sms-btn').prop('disabled', false).text('<?php esc_js_e('Send Test SMS', 'campaign-office'); ?>');
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
                <?php esc_html_e('Communication Campaigns', 'campaign-office'); ?>
                <a href="#" class="page-title-action" id="cp-new-campaign-btn">
                    <?php esc_html_e('New Campaign', 'campaign-office'); ?>
                </a>
            </h1>

            <div class="cp-campaigns-stats" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin: 2rem 0;">
                <?php
                $total_campaigns = count($campaigns);
                $total_sent = $wpdb->get_var("SELECT SUM(total_sent) FROM {$this->campaigns_table} WHERE status = 'sent'");
                $total_delivered = $wpdb->get_var("SELECT SUM(total_delivered) FROM {$this->campaigns_table}");
                $avg_open_rate = $wpdb->get_var("SELECT AVG(total_opened / NULLIF(total_delivered, 0) * 100) FROM {$this->campaigns_table} WHERE total_delivered > 0");
                ?>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #0073aa;">
                    <div style="font-size: 2rem; font-weight: 700; color: #0073aa;"><?php echo esc_html($total_campaigns); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Total Campaigns', 'campaign-office'); ?></div>
                </div>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #00a32a;">
                    <div style="font-size: 2rem; font-weight: 700; color: #00a32a;"><?php echo esc_html(number_format($total_sent ?? 0)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Messages Sent', 'campaign-office'); ?></div>
                </div>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #d63638;">
                    <div style="font-size: 2rem; font-weight: 700; color: #d63638;"><?php echo esc_html(number_format($total_delivered ?? 0)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Delivered', 'campaign-office'); ?></div>
                </div>
                <div class="cp-stat-card" style="background: #fff; padding: 1.5rem; border-left: 4px solid #dba617;">
                    <div style="font-size: 2rem; font-weight: 700; color: #dba617;"><?php echo esc_html(number_format($avg_open_rate ?? 0, 1)); ?>%</div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Avg. Open Rate', 'campaign-office'); ?></div>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Campaign', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Sent', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Delivered', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Opened', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Date', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($campaigns)) : ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                <?php esc_html_e('No campaigns yet. Create your first campaign to get started!', 'campaign-office'); ?>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($campaigns as $campaign) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($campaign->name); ?></strong></td>
                                <td>
                                    <?php if ($campaign->type === 'sms') : ?>
                                        <span class="dashicons dashicons-smartphone"></span> <?php esc_html_e('SMS', 'campaign-office'); ?>
                                    <?php else : ?>
                                        <span class="dashicons dashicons-email"></span> <?php esc_html_e('Email', 'campaign-office'); ?>
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
            <h1><?php esc_html_e('Subscribers', 'campaign-office'); ?></h1>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin: 2rem 0;">
                <div style="background: #fff; padding: 1.5rem; border-left: 4px solid #0073aa;">
                    <div style="font-size: 2rem; font-weight: 700; color: #0073aa;"><?php echo esc_html(number_format($total_subscribers)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Total Subscribers', 'campaign-office'); ?></div>
                </div>
                <div style="background: #fff; padding: 1.5rem; border-left: 4px solid #00a32a;">
                    <div style="font-size: 2rem; font-weight: 700; color: #00a32a;"><?php echo esc_html(number_format($email_subscribers)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('Email Subscribers', 'campaign-office'); ?></div>
                </div>
                <div style="background: #fff; padding: 1.5rem; border-left: 4px solid #dba617;">
                    <div style="font-size: 2rem; font-weight: 700; color: #dba617;"><?php echo esc_html(number_format($sms_subscribers)); ?></div>
                    <div style="color: #666; font-size: 0.875rem;"><?php esc_html_e('SMS Subscribers', 'campaign-office'); ?></div>
                </div>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Email', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Phone', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Subscribed', 'campaign-office'); ?></th>
                        <th><?php esc_html_e('Date', 'campaign-office'); ?></th>
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
            'title' => __('Stay Connected', 'campaign-office'),
        ), $atts, 'cp_subscribe_form');

        ob_start();
        ?>
        <div class="cp-subscribe-form-wrapper">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <form class="cp-subscribe-form">
                <?php wp_nonce_field('cp_subscribe', 'cp_subscribe_nonce'); ?>
                <div class="form-row">
                    <input type="text" name="first_name" placeholder="<?php esc_attr_e('First Name', 'campaign-office'); ?>" required class="cp-input">
                    <input type="text" name="last_name" placeholder="<?php esc_attr_e('Last Name', 'campaign-office'); ?>" required class="cp-input">
                </div>
                <?php if (in_array($atts['type'], array('both', 'email'), true)) : ?>
                    <div class="form-field">
                        <input type="email" name="email" placeholder="<?php esc_attr_e('Email Address', 'campaign-office'); ?>" required class="cp-input">
                    </div>
                <?php endif; ?>
                <?php if (in_array($atts['type'], array('both', 'sms'), true)) : ?>
                    <div class="form-field">
                        <input type="tel" name="phone" placeholder="<?php esc_attr_e('Phone Number', 'campaign-office'); ?>" class="cp-input">
                    </div>
                <?php endif; ?>
                <div class="form-field">
                    <input type="text" name="zip" placeholder="<?php esc_attr_e('ZIP Code', 'campaign-office'); ?>" class="cp-input">
                </div>
                <div class="form-actions">
                    <button type="submit" class="cp-button cp-button-primary">
                        <?php esc_html_e('Subscribe', 'campaign-office'); ?>
                    </button>
                </div>
                <div class="cp-form-message" style="display:none;"></div>
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
            <p><?php esc_html_e('To unsubscribe from our communications, please enter your email address below.', 'campaign-office'); ?></p>
            <form class="cp-unsubscribe-form">
                <input type="email" name="email" placeholder="<?php esc_attr_e('Email Address', 'campaign-office'); ?>" required class="cp-input">
                <button type="submit" class="cp-button"><?php esc_html_e('Unsubscribe', 'campaign-office'); ?></button>
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
            wp_send_json_error(array('message' => __('Permission denied', 'campaign-office')));
        }

        $phone = sanitize_text_field($_POST['phone']);

        $result = $this->send_sms($phone, __('This is a test message from CampaignPress. Your SMS integration is working correctly!', 'campaign-office'));

        if ($result['success']) {
            wp_send_json_success(array('message' => __('Test SMS sent successfully!', 'campaign-office')));
        } else {
            wp_send_json_error(array('message' => __('Failed to send SMS: ', 'campaign-office') . $result['error']));
        }
    }

    public function handle_subscribe() {
        check_ajax_referer('cp_subscribe', 'cp_subscribe_nonce');

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

        // Sync with Mailchimp if enabled
        if (get_option('cp_mailchimp_enabled')) {
            $this->sync_to_mailchimp($data);
        }

        wp_send_json_success(array('message' => __('Thank you for subscribing!', 'campaign-office')));
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

        wp_remote_post($url, array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('anystring:' . $api_key),
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
        ));

        return true;
    }

    public function ajax_send_campaign() {
        // Placeholder for campaign sending logic
        wp_send_json_success();
    }

    public function ajax_get_campaign_stats() {
        // Placeholder for campaign stats
        wp_send_json_success();
    }
}

// Initialize communications system
new CP_Campaign_Communications();
