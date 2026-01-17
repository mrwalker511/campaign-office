<?php
/**
 * Donation Enhancements for CampaignPress
 *
 * Provides comprehensive donation handling with multiple payment processor support,
 * customizable donation buttons, recurring donation options, FEC compliance notices,
 * and integration with analytics platforms.
 *
 * Supported Payment Processors:
 * - ActBlue (Democratic campaigns)
 * - WinRed (Republican campaigns)
 * - PayPal
 * - Stripe
 * - Square
 * - Donorbox
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Donation_Enhancements
 *
 * Handles all donation-related functionality including button generation,
 * processor integration, tracking, and compliance notices.
 */
class CP_Donation_Enhancements {

    /**
     * Available payment processors
     *
     * @var array
     */
    private $processors = array();

    /**
     * Default quick donation amounts
     *
     * @var array
     */
    private $default_amounts = array(25, 50, 100, 250, 500);

    /**
     * Recurring donation frequencies
     *
     * @var array
     */
    private $frequencies = array(
        'once'      => 'One-Time',
        'monthly'   => 'Monthly',
        'quarterly' => 'Quarterly',
        'annually'  => 'Annually',
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize payment processors
        $this->init_processors();

        // Register admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Register donation shortcode
        add_shortcode('cp_donation_button', array($this, 'render_donation_shortcode'));

        // Add meta box for post-specific donation CTAs
        add_action('add_meta_boxes', array($this, 'add_donation_meta_box'));
        add_action('save_post', array($this, 'save_donation_meta_box'));

        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Add donation tracking parameters
        add_filter('the_content', array($this, 'append_post_donation_cta'));

        // AJAX handlers
        add_action('wp_ajax_cp_track_donation_click', array($this, 'track_donation_click'));
        add_action('wp_ajax_nopriv_cp_track_donation_click', array($this, 'track_donation_click'));

        // Add admin notices for configuration
        add_action('admin_notices', array($this, 'show_admin_notices'));
    }

    /**
     * Initialize payment processor configuration
     *
     * Defines supported payment processors and their settings
     */
    private function init_processors() {
        $this->processors = array(
            'actblue' => array(
                'name'        => 'ActBlue',
                'description' => 'Popular Democratic campaign fundraising platform',
                'url_pattern' => 'https://secure.actblue.com/donate/{campaign_slug}',
                'fields'      => array('campaign_slug'),
                'icon'        => 'dashicons-money-alt',
                'color'       => '#0039A6',
            ),
            'winred' => array(
                'name'        => 'WinRed',
                'description' => 'Popular Republican campaign fundraising platform',
                'url_pattern' => 'https://winred.com/donate/{campaign_slug}',
                'fields'      => array('campaign_slug'),
                'icon'        => 'dashicons-money-alt',
                'color'       => '#DC143C',
            ),
            'paypal' => array(
                'name'        => 'PayPal',
                'description' => 'PayPal donation button',
                'url_pattern' => 'https://www.paypal.com/donate/?hosted_button_id={button_id}',
                'fields'      => array('button_id'),
                'icon'        => 'dashicons-money',
                'color'       => '#0070BA',
            ),
            'stripe' => array(
                'name'        => 'Stripe',
                'description' => 'Stripe Payment Links',
                'url_pattern' => '{payment_link}',
                'fields'      => array('payment_link'),
                'icon'        => 'dashicons-money',
                'color'       => '#635BFF',
            ),
            'square' => array(
                'name'        => 'Square',
                'description' => 'Square Online Checkout',
                'url_pattern' => '{checkout_url}',
                'fields'      => array('checkout_url'),
                'icon'        => 'dashicons-money',
                'color'       => '#000000',
            ),
            'donorbox' => array(
                'name'        => 'Donorbox',
                'description' => 'Donorbox fundraising platform',
                'url_pattern' => 'https://donorbox.org/{campaign_slug}',
                'fields'      => array('campaign_slug'),
                'icon'        => 'dashicons-money-alt',
                'color'       => '#00C853',
            ),
        );
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_theme_page(
            __('Donation Settings', 'campaignpress'),
            __('Donation Settings', 'campaignpress'),
            'manage_options',
            'cp-donation-settings',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // General donation settings
        register_setting('cp_donation_settings', 'cp_default_processor', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'actblue',
        ));

        register_setting('cp_donation_settings', 'cp_donation_amounts', array(
            'type'              => 'string',
            'sanitize_callback' => array($this, 'sanitize_amounts'),
            'default'           => implode(',', $this->default_amounts),
        ));

        register_setting('cp_donation_settings', 'cp_enable_recurring', array(
            'type'    => 'boolean',
            'default' => true,
        ));

        register_setting('cp_donation_settings', 'cp_default_frequency', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'once',
        ));

        register_setting('cp_donation_settings', 'cp_show_fec_notice', array(
            'type'    => 'boolean',
            'default' => true,
        ));

        register_setting('cp_donation_settings', 'cp_enable_analytics', array(
            'type'    => 'boolean',
            'default' => true,
        ));

        register_setting('cp_donation_settings', 'cp_contribution_limit', array(
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'default'           => 3300,
        ));

        // Processor-specific settings
        foreach ($this->processors as $key => $processor) {
            foreach ($processor['fields'] as $field) {
                register_setting('cp_donation_settings', "cp_{$key}_{$field}", array(
                    'type'              => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ));
            }
        }

        // Button customization
        register_setting('cp_donation_settings', 'cp_button_text', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => __('Donate Now', 'campaignpress'),
        ));

        register_setting('cp_donation_settings', 'cp_button_style', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'primary',
        ));

        register_setting('cp_donation_settings', 'cp_button_size', array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'large',
        ));
    }

    /**
     * Sanitize donation amounts
     *
     * @param string $input Comma-separated amounts
     * @return string Sanitized amounts
     */
    public function sanitize_amounts($input) {
        $amounts = array_map('trim', explode(',', $input));
        $amounts = array_filter($amounts, 'is_numeric');
        $amounts = array_map('absint', $amounts);
        return implode(',', $amounts);
    }

    /**
     * Render admin settings page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if (isset($_POST['cp_donation_settings_submit'])) {
            if (!isset($_POST['cp_donation_settings_nonce']) ||
                !wp_verify_nonce($_POST['cp_donation_settings_nonce'], 'cp_donation_settings')) {
                wp_die(__('Security check failed', 'campaignpress'));
            }

            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__('Settings saved successfully.', 'campaignpress') . '</p></div>';
        }

        // Get current values
        $default_processor = get_option('cp_default_processor', 'actblue');
        $donation_amounts = get_option('cp_donation_amounts', implode(',', $this->default_amounts));
        $enable_recurring = get_option('cp_enable_recurring', true);
        $default_frequency = get_option('cp_default_frequency', 'once');
        $show_fec_notice = get_option('cp_show_fec_notice', true);
        $enable_analytics = get_option('cp_enable_analytics', true);
        $contribution_limit = get_option('cp_contribution_limit', 3300);
        $button_text = get_option('cp_button_text', __('Donate Now', 'campaignpress'));
        $button_style = get_option('cp_button_style', 'primary');
        $button_size = get_option('cp_button_size', 'large');

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="notice notice-info">
                <p>
                    <strong><?php esc_html_e('Donation Enhancement Features:', 'campaignpress'); ?></strong>
                    <?php esc_html_e('Configure your donation buttons, payment processors, and compliance settings.', 'campaignpress'); ?>
                </p>
            </div>

            <form method="post" action="options.php">
                <?php
                settings_fields('cp_donation_settings');
                wp_nonce_field('cp_donation_settings', 'cp_donation_settings_nonce');
                ?>

                <h2><?php esc_html_e('Payment Processor Settings', 'campaignpress'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cp_default_processor"><?php esc_html_e('Default Processor', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <select name="cp_default_processor" id="cp_default_processor" class="regular-text">
                                <?php foreach ($this->processors as $key => $processor) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($default_processor, $key); ?>>
                                        <?php echo esc_html($processor['name']); ?> - <?php echo esc_html($processor['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Select your primary payment processor.', 'campaignpress'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php foreach ($this->processors as $key => $processor) : ?>
                    <h3><?php echo esc_html($processor['name']); ?> <?php esc_html_e('Configuration', 'campaignpress'); ?></h3>
                    <table class="form-table">
                        <?php foreach ($processor['fields'] as $field) : ?>
                            <tr>
                                <th scope="row">
                                    <label for="cp_<?php echo esc_attr($key . '_' . $field); ?>">
                                        <?php echo esc_html(ucwords(str_replace('_', ' ', $field))); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text"
                                           name="cp_<?php echo esc_attr($key . '_' . $field); ?>"
                                           id="cp_<?php echo esc_attr($key . '_' . $field); ?>"
                                           value="<?php echo esc_attr(get_option("cp_{$key}_{$field}", '')); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php
                                        printf(
                                            esc_html__('Enter your %s for %s', 'campaignpress'),
                                            esc_html($field),
                                            esc_html($processor['name'])
                                        );
                                        ?>
                                    </p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endforeach; ?>

                <h2><?php esc_html_e('Donation Amount Settings', 'campaignpress'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cp_donation_amounts"><?php esc_html_e('Quick Amounts', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <input type="text"
                                   name="cp_donation_amounts"
                                   id="cp_donation_amounts"
                                   value="<?php echo esc_attr($donation_amounts); ?>"
                                   class="regular-text">
                            <p class="description">
                                <?php esc_html_e('Enter comma-separated amounts (e.g., 25,50,100,250,500)', 'campaignpress'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Recurring Donation Settings', 'campaignpress'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Recurring Donations', 'campaignpress'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="cp_enable_recurring"
                                       value="1"
                                       <?php checked($enable_recurring, true); ?>>
                                <?php esc_html_e('Allow donors to set up recurring donations', 'campaignpress'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cp_default_frequency"><?php esc_html_e('Default Frequency', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <select name="cp_default_frequency" id="cp_default_frequency">
                                <?php foreach ($this->frequencies as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($default_frequency, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Button Customization', 'campaignpress'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="cp_button_text"><?php esc_html_e('Button Text', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <input type="text"
                                   name="cp_button_text"
                                   id="cp_button_text"
                                   value="<?php echo esc_attr($button_text); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cp_button_style"><?php esc_html_e('Button Style', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <select name="cp_button_style" id="cp_button_style">
                                <option value="primary" <?php selected($button_style, 'primary'); ?>><?php esc_html_e('Primary', 'campaignpress'); ?></option>
                                <option value="secondary" <?php selected($button_style, 'secondary'); ?>><?php esc_html_e('Secondary', 'campaignpress'); ?></option>
                                <option value="success" <?php selected($button_style, 'success'); ?>><?php esc_html_e('Success', 'campaignpress'); ?></option>
                                <option value="danger" <?php selected($button_style, 'danger'); ?>><?php esc_html_e('Danger', 'campaignpress'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cp_button_size"><?php esc_html_e('Button Size', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <select name="cp_button_size" id="cp_button_size">
                                <option value="small" <?php selected($button_size, 'small'); ?>><?php esc_html_e('Small', 'campaignpress'); ?></option>
                                <option value="medium" <?php selected($button_size, 'medium'); ?>><?php esc_html_e('Medium', 'campaignpress'); ?></option>
                                <option value="large" <?php selected($button_size, 'large'); ?>><?php esc_html_e('Large', 'campaignpress'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('FEC Compliance & Analytics', 'campaignpress'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Show FEC Compliance Notice', 'campaignpress'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="cp_show_fec_notice"
                                       value="1"
                                       <?php checked($show_fec_notice, true); ?>>
                                <?php esc_html_e('Display federal contribution limits and compliance information', 'campaignpress'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="cp_contribution_limit"><?php esc_html_e('Contribution Limit', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <input type="number"
                                   name="cp_contribution_limit"
                                   id="cp_contribution_limit"
                                   value="<?php echo esc_attr($contribution_limit); ?>"
                                   class="small-text">
                            <p class="description">
                                <?php esc_html_e('Federal individual contribution limit per election cycle (e.g., $3,300 for 2024)', 'campaignpress'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Analytics Tracking', 'campaignpress'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="cp_enable_analytics"
                                       value="1"
                                       <?php checked($enable_analytics, true); ?>>
                                <?php esc_html_e('Track donation button clicks for Google Analytics goals', 'campaignpress'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Sends events to Google Analytics when donation buttons are clicked. Requires GA integration.', 'campaignpress'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Donation Settings', 'campaignpress')); ?>
            </form>

            <div class="card">
                <h2><?php esc_html_e('Shortcode Usage', 'campaignpress'); ?></h2>
                <p><?php esc_html_e('Use the following shortcode to add donation buttons to your content:', 'campaignpress'); ?></p>
                <pre><code>[cp_donation_button processor="actblue" amounts="25,50,100,250" frequency="monthly"]</code></pre>

                <h3><?php esc_html_e('Shortcode Parameters:', 'campaignpress'); ?></h3>
                <ul style="list-style: disc; margin-left: 2em;">
                    <li><strong>processor</strong> - <?php esc_html_e('Payment processor (actblue, winred, paypal, stripe, square, donorbox)', 'campaignpress'); ?></li>
                    <li><strong>amounts</strong> - <?php esc_html_e('Comma-separated quick amounts', 'campaignpress'); ?></li>
                    <li><strong>frequency</strong> - <?php esc_html_e('Default frequency (once, monthly, quarterly, annually)', 'campaignpress'); ?></li>
                    <li><strong>text</strong> - <?php esc_html_e('Custom button text', 'campaignpress'); ?></li>
                    <li><strong>style</strong> - <?php esc_html_e('Button style (primary, secondary, success, danger)', 'campaignpress'); ?></li>
                </ul>

                <h3><?php esc_html_e('Google Analytics Integration:', 'campaignpress'); ?></h3>
                <p><?php esc_html_e('To track donations in Google Analytics:', 'campaignpress'); ?></p>
                <ol style="margin-left: 2em;">
                    <li><?php esc_html_e('Enable "Analytics Tracking" above', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Ensure Google Analytics is installed on your site', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Create a goal in GA4 for the event "donation_click"', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Monitor your donation conversion rates in GA4 reports', 'campaignpress'); ?></li>
                </ol>
            </div>

            <div class="card">
                <h2><?php esc_html_e('FEC Compliance Best Practices', 'campaignpress'); ?></h2>
                <p><?php esc_html_e('Federal campaigns must comply with FEC regulations:', 'campaignpress'); ?></p>
                <ul style="list-style: disc; margin-left: 2em;">
                    <li><?php esc_html_e('Individual contribution limits apply per election cycle', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Collect donor name, address, occupation, and employer for contributions over $200', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Display contribution limits prominently on donation pages', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Maintain accurate records of all contributions', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Consult with your campaign treasurer for specific requirements', 'campaignpress'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render donation button shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_donation_shortcode($atts) {
        // Check for HTTPS - required for PCI compliance when handling donations
        if (!is_ssl() && !is_admin()) {
            return '<div class="cp-donation-error" style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">' .
                   '<strong>' . esc_html__('Security Warning:', 'campaignpress') . '</strong> ' .
                   esc_html__('Donation forms require HTTPS (SSL certificate) for secure payment processing and PCI compliance. Please enable HTTPS on this site.', 'campaignpress') .
                   '</div>';
        }

        $atts = shortcode_atts(array(
            'processor' => get_option('cp_default_processor', 'actblue'),
            'amounts'   => get_option('cp_donation_amounts', implode(',', $this->default_amounts)),
            'frequency' => get_option('cp_default_frequency', 'once'),
            'text'      => get_option('cp_button_text', __('Donate Now', 'campaignpress')),
            'style'     => get_option('cp_button_style', 'primary'),
            'size'      => get_option('cp_button_size', 'large'),
        ), $atts, 'cp_donation_button');

        // Get processor configuration
        $processor = $this->get_processor_config($atts['processor']);
        if (!$processor) {
            return '<p class="cp-donation-error">' . esc_html__('Invalid payment processor selected.', 'campaignpress') . '</p>';
        }

        // Get donation URL
        $donation_url = $this->get_donation_url($atts['processor']);
        if (!$donation_url) {
            return '<p class="cp-donation-error">' . esc_html__('Donation URL not configured.', 'campaignpress') . '</p>';
        }

        // Parse amounts
        $amounts = array_map('trim', explode(',', $atts['amounts']));
        $amounts = array_filter($amounts, 'is_numeric');

        // Start output buffering
        ob_start();
        ?>
        <div class="cp-donation-widget" data-processor="<?php echo esc_attr($atts['processor']); ?>">
            <?php if (get_option('cp_enable_recurring', true)) : ?>
                <div class="cp-donation-frequency">
                    <label><?php esc_html_e('Donation Frequency:', 'campaignpress'); ?></label>
                    <div class="cp-frequency-buttons">
                        <?php foreach ($this->frequencies as $key => $label) : ?>
                            <button type="button"
                                    class="cp-frequency-btn <?php echo esc_attr($atts['frequency'] === $key ? 'active' : ''); ?>"
                                    data-frequency="<?php echo esc_attr($key); ?>">
                                <?php echo esc_html($label); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="cp-donation-amounts">
                <label><?php esc_html_e('Select Amount:', 'campaignpress'); ?></label>
                <div class="cp-amount-buttons">
                    <?php foreach ($amounts as $amount) : ?>
                        <button type="button"
                                class="cp-amount-btn"
                                data-amount="<?php echo esc_attr($amount); ?>">
                            $<?php echo esc_html(number_format($amount)); ?>
                        </button>
                    <?php endforeach; ?>
                    <button type="button" class="cp-amount-btn" data-amount="custom">
                        <?php esc_html_e('Custom', 'campaignpress'); ?>
                    </button>
                </div>
                <div class="cp-custom-amount-wrapper" style="display: none;">
                    <label for="cp-custom-amount"><?php esc_html_e('Enter Amount:', 'campaignpress'); ?></label>
                    <input type="number"
                           id="cp-custom-amount"
                           class="cp-custom-amount-input"
                           placeholder="<?php esc_attr_e('Enter amount', 'campaignpress'); ?>"
                           min="1"
                           step="1">
                </div>
            </div>

            <div class="cp-donation-action">
                <a href="<?php echo esc_url($donation_url); ?>"
                   class="cp-donate-btn cp-btn-<?php echo esc_attr($atts['style']); ?> cp-btn-<?php echo esc_attr($atts['size']); ?>"
                   data-processor="<?php echo esc_attr($atts['processor']); ?>"
                   target="_blank"
                   rel="noopener noreferrer">
                    <?php echo esc_html($atts['text']); ?>
                </a>
            </div>

            <?php if (get_option('cp_show_fec_notice', true)) : ?>
                <div class="cp-fec-notice">
                    <p class="cp-small-text">
                        <?php
                        printf(
                            esc_html__('Contributions are not tax-deductible. Federal law requires us to use our best efforts to collect and report the name, mailing address, occupation, and employer for individuals whose contributions exceed $200 in an election cycle. The maximum amount an individual may contribute is $%s per election.', 'campaignpress'),
                            esc_html(number_format(get_option('cp_contribution_limit', 3300)))
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get processor configuration
     *
     * @param string $processor_key Processor identifier
     * @return array|false Processor config or false
     */
    private function get_processor_config($processor_key) {
        return isset($this->processors[$processor_key]) ? $this->processors[$processor_key] : false;
    }

    /**
     * Get donation URL for processor
     *
     * @param string $processor_key Processor identifier
     * @return string|false Donation URL or false
     */
    private function get_donation_url($processor_key) {
        $processor = $this->get_processor_config($processor_key);
        if (!$processor) {
            return false;
        }

        $url = $processor['url_pattern'];

        // Replace placeholders with configured values
        foreach ($processor['fields'] as $field) {
            $value = get_option("cp_{$processor_key}_{$field}", '');
            if (empty($value)) {
                return false;
            }
            $url = str_replace('{' . $field . '}', $value, $url);
        }

        // Add tracking parameters
        $url = add_query_arg(array(
            'source'   => 'website',
            'refcode'  => get_the_ID(),
            'utm_source' => 'campaignpress',
            'utm_medium' => 'donation_button',
        ), $url);

        return $url;
    }

    /**
     * Add donation meta box to posts/pages
     */
    public function add_donation_meta_box() {
        $post_types = array('post', 'page', 'cp_press_release', 'cp_event');

        foreach ($post_types as $post_type) {
            add_meta_box(
                'cp_donation_cta',
                __('Donation Call-to-Action', 'campaignpress'),
                array($this, 'render_donation_meta_box'),
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render donation meta box
     *
     * @param WP_Post $post Current post object
     */
    public function render_donation_meta_box($post) {
        wp_nonce_field('cp_donation_meta_box', 'cp_donation_meta_box_nonce');

        $show_cta = get_post_meta($post->ID, '_cp_show_donation_cta', true);
        $custom_text = get_post_meta($post->ID, '_cp_donation_custom_text', true);

        ?>
        <p>
            <label>
                <input type="checkbox"
                       name="cp_show_donation_cta"
                       value="1"
                       <?php checked($show_cta, '1'); ?>>
                <?php esc_html_e('Show donation CTA at end of content', 'campaignpress'); ?>
            </label>
        </p>
        <p>
            <label for="cp_donation_custom_text">
                <?php esc_html_e('Custom CTA Text (optional):', 'campaignpress'); ?>
            </label>
            <textarea name="cp_donation_custom_text"
                      id="cp_donation_custom_text"
                      rows="3"
                      class="widefat"
                      placeholder="<?php esc_attr_e('Help us continue this work...', 'campaignpress'); ?>"><?php echo esc_textarea($custom_text); ?></textarea>
        </p>
        <?php
    }

    /**
     * Save donation meta box data
     *
     * @param int $post_id Post ID
     */
    public function save_donation_meta_box($post_id) {
        // Security checks
        if (!isset($_POST['cp_donation_meta_box_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['cp_donation_meta_box_nonce'], 'cp_donation_meta_box')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save show CTA checkbox
        $show_cta = isset($_POST['cp_show_donation_cta']) ? '1' : '0';
        update_post_meta($post_id, '_cp_show_donation_cta', $show_cta);

        // Save custom text
        if (isset($_POST['cp_donation_custom_text'])) {
            $custom_text = sanitize_textarea_field($_POST['cp_donation_custom_text']);
            update_post_meta($post_id, '_cp_donation_custom_text', $custom_text);
        }
    }

    /**
     * Append donation CTA to post content
     *
     * @param string $content Post content
     * @return string Modified content
     */
    public function append_post_donation_cta($content) {
        if (!is_singular() || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $show_cta = get_post_meta(get_the_ID(), '_cp_show_donation_cta', true);
        if ($show_cta !== '1') {
            return $content;
        }

        $custom_text = get_post_meta(get_the_ID(), '_cp_donation_custom_text', true);
        $shortcode = '[cp_donation_button]';

        $cta_html = '<div class="cp-post-donation-cta">';
        if (!empty($custom_text)) {
            $cta_html .= '<p class="cp-cta-text">' . esc_html($custom_text) . '</p>';
        }
        $cta_html .= do_shortcode($shortcode);
        $cta_html .= '</div>';

        return $content . $cta_html;
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Inline CSS for donation widgets
        $css = "
        /* Donation Widget Styles */
        .cp-donation-widget {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 30px;
            margin: 30px 0;
            max-width: 600px;
        }

        .cp-donation-frequency,
        .cp-donation-amounts {
            margin-bottom: 25px;
        }

        .cp-donation-frequency label,
        .cp-donation-amounts label {
            display: block;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 16px;
            color: #212529;
        }

        .cp-frequency-buttons,
        .cp-amount-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 10px;
        }

        .cp-frequency-btn,
        .cp-amount-btn {
            padding: 12px 20px;
            border: 2px solid #dee2e6;
            background: #fff;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-align: center;
        }

        .cp-frequency-btn:hover,
        .cp-amount-btn:hover {
            border-color: #0073aa;
            color: #0073aa;
            transform: translateY(-2px);
        }

        .cp-frequency-btn.active,
        .cp-amount-btn.active {
            background: #0073aa;
            color: #fff;
            border-color: #0073aa;
        }

        .cp-custom-amount-wrapper {
            margin-top: 15px;
        }

        .cp-custom-amount-wrapper label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .cp-custom-amount-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 16px;
        }

        .cp-donation-action {
            margin-bottom: 20px;
        }

        .cp-donate-btn {
            display: block;
            width: 100%;
            padding: 16px 30px;
            text-align: center;
            text-decoration: none;
            font-weight: 700;
            font-size: 18px;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .cp-btn-primary {
            background: #0073aa;
            color: #fff;
        }

        .cp-btn-primary:hover {
            background: #005a87;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 115, 170, 0.3);
        }

        .cp-btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .cp-btn-success {
            background: #28a745;
            color: #fff;
        }

        .cp-btn-danger {
            background: #dc3545;
            color: #fff;
        }

        .cp-btn-small {
            padding: 10px 20px;
            font-size: 14px;
        }

        .cp-btn-medium {
            padding: 12px 24px;
            font-size: 16px;
        }

        .cp-btn-large {
            padding: 16px 30px;
            font-size: 18px;
        }

        .cp-fec-notice {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 4px;
        }

        .cp-fec-notice p {
            margin: 0;
            font-size: 12px;
            line-height: 1.6;
            color: #856404;
        }

        .cp-small-text {
            font-size: 12px;
            color: #6c757d;
        }

        .cp-donation-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #dc3545;
        }

        .cp-post-donation-cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 40px;
            border-radius: 12px;
            margin: 40px 0;
            text-align: center;
        }

        .cp-cta-text {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #fff;
        }

        .cp-post-donation-cta .cp-donation-widget {
            background: rgba(255, 255, 255, 0.95);
            margin: 0 auto;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .cp-donation-widget {
                padding: 20px;
            }

            .cp-frequency-buttons,
            .cp-amount-buttons {
                grid-template-columns: repeat(2, 1fr);
            }

            .cp-donate-btn {
                font-size: 16px;
                padding: 14px 24px;
            }

            .cp-post-donation-cta {
                padding: 30px 20px;
            }

            .cp-cta-text {
                font-size: 18px;
            }
        }
        ";

        wp_add_inline_style('campaignpress-main', $css);

        // Ensure main script is enqueued
        wp_enqueue_script('campaignpress-main');

        // Inline JavaScript for donation widget interaction
        $js = "
        jQuery(document).ready(function($) {
            // Handle frequency selection
            $('.cp-frequency-btn').on('click', function() {
                $(this).addClass('active').siblings().removeClass('active');
            });

            // Handle amount selection
            $('.cp-amount-btn').on('click', function() {
                $(this).addClass('active').siblings().removeClass('active');

                if ($(this).data('amount') === 'custom') {
                    $(this).closest('.cp-donation-amounts').find('.cp-custom-amount-wrapper').slideDown();
                } else {
                    $(this).closest('.cp-donation-amounts').find('.cp-custom-amount-wrapper').slideUp();
                }
            });

            // Track donation clicks
            $('.cp-donate-btn').on('click', function(e) {
                var processor = $(this).data('processor');
                var frequency = $(this).closest('.cp-donation-widget').find('.cp-frequency-btn.active').data('frequency') || 'once';
                var amount = $(this).closest('.cp-donation-widget').find('.cp-amount-btn.active').data('amount') || '';

                if (amount === 'custom') {
                    amount = $(this).closest('.cp-donation-widget').find('.cp-custom-amount-input').val();
                }

                // Send to analytics if enabled
                if (typeof gtag !== 'undefined' && " . (get_option('cp_enable_analytics', true) ? 'true' : 'false') . ") {
                    gtag('event', 'donation_click', {
                        'processor': processor,
                        'frequency': frequency,
                        'amount': amount,
                        'event_category': 'Donation',
                        'event_label': processor + ' - ' + frequency + ' - $' + amount
                    });
                }

                // Track via AJAX
                $.ajax({
                    url: '" . esc_url(admin_url('admin-ajax.php')) . "',
                    type: 'POST',
                    data: {
                        action: 'cp_track_donation_click',
                        processor: processor,
                        frequency: frequency,
                        amount: amount,
                        nonce: '" . wp_create_nonce('cp_donation_tracking') . "'
                    }
                });
            });
        });
        ";

        // Ensure script is enqueued before adding inline script
        if (wp_script_is('campaignpress-main', 'enqueued') || wp_script_is('campaignpress-main', 'registered')) {
            wp_add_inline_script('campaignpress-main', $js, 'after');
        }
    }

    /**
     * Track donation click via AJAX
     */
    public function track_donation_click() {
        check_ajax_referer('cp_donation_tracking', 'nonce');

        // Rate limiting: 20 tracking requests per hour per IP
        if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('donation_tracking', 20, 3600)) {
            wp_send_json_error(array('message' => 'Rate limit exceeded'));
        }

        $processor = isset($_POST['processor']) ? sanitize_text_field($_POST['processor']) : '';
        $frequency = isset($_POST['frequency']) ? sanitize_text_field($_POST['frequency']) : '';
        $amount = isset($_POST['amount']) ? absint($_POST['amount']) : 0;

        // Store comprehensive tracking data for audit trail
        $tracking_data = array(
            'processor'   => $processor,
            'frequency'   => $frequency,
            'amount'      => $amount,
            'timestamp'   => current_time('mysql'),
            'post_id'     => isset($_POST['post_id']) ? absint($_POST['post_id']) : 0,
            // Additional audit data
            'user_id'     => get_current_user_id(), // 0 if not logged in
            'user_ip'     => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent'  => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'referer'     => wp_get_referer() ?? '',
            'session_id'  => session_id() ? session_id() : wp_create_nonce('cp_session_' . $_SERVER['REMOTE_ADDR']),
        );

        // Save to transient for recent tracking (last 100 clicks)
        $recent_clicks = get_transient('cp_donation_clicks') ?: array();
        $recent_clicks[] = $tracking_data;
        set_transient('cp_donation_clicks', array_slice($recent_clicks, -100), DAY_IN_SECONDS);

        // Also save to permanent log for compliance auditing
        $permanent_log = get_option('cp_donation_transaction_log', array());
        $permanent_log[] = $tracking_data;
        // Keep last 1000 transactions in the database
        $permanent_log = array_slice($permanent_log, -1000);
        update_option('cp_donation_transaction_log', $permanent_log, false); // autoload = false

        wp_send_json_success(array('message' => 'Tracked successfully'));
    }

    /**
     * Show admin notices for configuration
     */
    public function show_admin_notices() {
        // Critical: Show HTTPS warning on all admin pages if site is not secure
        if (!is_ssl()) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong><?php esc_html_e('CRITICAL SECURITY WARNING:', 'campaignpress'); ?></strong>
                    <?php esc_html_e('Your site is not using HTTPS. Donation forms will NOT display until you enable SSL/HTTPS. This is required for PCI compliance and secure payment processing.', 'campaignpress'); ?>
                    <a href="https://wordpress.org/support/article/https-for-wordpress/" target="_blank"><?php esc_html_e('Learn how to enable HTTPS', 'campaignpress'); ?></a>
                </p>
            </div>
            <?php
        }

        $screen = get_current_screen();
        if ($screen->id !== 'appearance_page_cp-donation-settings') {
            return;
        }

        $default_processor = get_option('cp_default_processor', 'actblue');
        $processor = $this->get_processor_config($default_processor);

        if (!$processor) {
            return;
        }

        // Check if processor is configured
        $configured = true;
        foreach ($processor['fields'] as $field) {
            if (empty(get_option("cp_{$default_processor}_{$field}"))) {
                $configured = false;
                break;
            }
        }

        if (!$configured) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong><?php esc_html_e('Configuration Required:', 'campaignpress'); ?></strong>
                    <?php
                    printf(
                        esc_html__('Please configure your %s settings below to enable donation buttons.', 'campaignpress'),
                        esc_html($processor['name'])
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }
}

// Initialize donation enhancements
new CP_Donation_Enhancements();
