<?php
/**
 * Third-party integrations
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom CSS class to Contact Form 7 forms
 */
function campaignpress_wpcf7_form_class($class) {
    return $class . ' cp-contact-form';
}

/**
 * Check if Contact Form 7 is active and add custom styling
 */
function campaignpress_contact_form_7_support() {
    if (!function_exists('wpcf7')) {
        return;
    }

    // Add custom CSS class to Contact Form 7 forms
    add_filter('wpcf7_form_class_attr', 'campaignpress_wpcf7_form_class');
}
add_action('wp', 'campaignpress_contact_form_7_support');

/**
 * Check if The Events Calendar is active
 */
function campaignpress_events_calendar_support() {
    if (!class_exists('Tribe__Events__Main')) {
        return;
    }

    // Add theme support for The Events Calendar
    add_theme_support('tribe-events-calendar-pro');
}
add_action('after_setup_theme', 'campaignpress_events_calendar_support');

/**
 * Mailchimp integration helper
 */
function campaignpress_mailchimp_integration_notice() {
    // Check if user is on the theme options page or widgets page
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, array('appearance_page_campaignpress-settings', 'widgets'), true)) {
        return;
    }

    // Check if MailChimp plugin is installed
    if (!function_exists('mc4wp_get_api')) {
        ?>
        <div class="notice notice-info">
            <p>
                <?php
                echo wp_kses_post(
                    sprintf(
                        __('<strong>CampaignPress Tip:</strong> Install the %s plugin to add email signup forms to your campaign website.', 'campaignpress'),
                        '<a href="' . admin_url('plugin-install.php?s=mailchimp+for+wordpress&tab=search&type=term') . '">MailChimp for WordPress</a>'
                    )
                );
                ?>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'campaignpress_mailchimp_integration_notice');

/**
 * ActBlue / WinRed donation integration helpers
 * These are helper functions to make it easier to integrate with external donation processors
 */

/**
 * Get the donation URL from theme settings
 * Uses static caching to prevent multiple database queries
 */
function campaignpress_get_donation_url() {
    static $donation_url = null;

    if ($donation_url === null) {
        $donation_url = esc_url(get_theme_mod('campaignpress_donation_url', ''));
    }

    return $donation_url;
}

/**
 * Display donation button
 */
function campaignpress_donation_button($args = array()) {
    $defaults = array(
        'text'  => __('Donate Now', 'campaignpress'),
        'class' => 'cp-donation-button',
        'url'   => campaignpress_get_donation_url(),
    );

    $args = wp_parse_args($args, $defaults);

    if (empty($args['url'])) {
        return;
    }

    printf(
        '<a href="%s" class="%s" target="_blank" rel="noopener">%s</a>',
        esc_url($args['url']),
        esc_attr($args['class']),
        esc_html($args['text'])
    );
}

/**
 * Add recommended plugins notice
 */
function campaignpress_recommended_plugins_notice() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'themes') {
        return;
    }

    $recommended_plugins = array();

    if (!function_exists('wpcf7')) {
        $recommended_plugins[] = 'Contact Form 7';
    }

    if (!class_exists('Tribe__Events__Main')) {
        $recommended_plugins[] = 'The Events Calendar';
    }

    if (!function_exists('mc4wp_get_api')) {
        $recommended_plugins[] = 'MailChimp for WordPress';
    }

    if (empty($recommended_plugins)) {
        return;
    }

    ?>
    <div class="notice notice-info is-dismissible">
        <h3><?php esc_html_e('Recommended Plugins for CampaignPress', 'campaignpress'); ?></h3>
        <p><?php esc_html_e('To get the most out of CampaignPress, we recommend installing these free plugins:', 'campaignpress'); ?></p>
        <ul style="list-style: disc; margin-left: 20px;">
            <?php foreach ($recommended_plugins as $plugin) : ?>
                <li><?php echo esc_html($plugin); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>
            <a href="<?php echo esc_url(admin_url('plugin-install.php')); ?>" class="button button-primary">
                <?php esc_html_e('Install Plugins', 'campaignpress'); ?>
            </a>
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'campaignpress_recommended_plugins_notice');
