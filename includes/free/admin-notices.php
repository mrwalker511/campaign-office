<?php
/**
 * Admin Notices
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Show demo content notice on theme activation
 */
function campaignpress_demo_content_notice() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }

    // Don't show if demo content already imported
    if (get_option('campaignpress_demo_imported', false)) {
        return;
    }

    // Don't show if dismissed
    if (get_user_meta(get_current_user_id(), 'campaignpress_demo_notice_dismissed', true)) {
        return;
    }

    // Only show on theme-related pages
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, array('themes', 'dashboard', 'appearance_page_campaignpress-demo'))) {
        return;
    }

    ?>
    <div class="notice notice-info is-dismissible campaignpress-demo-notice">
        <h3><?php esc_html_e('Welcome to CampaignPress!', 'campaignpress'); ?></h3>
        <p><?php esc_html_e('Want to see CampaignPress in action? Import demo content to explore all the theme features with sample campaign data.', 'campaignpress'); ?></p>
        <p>
            <a href="<?php echo esc_url(admin_url('themes.php?page=campaignpress-demo')); ?>" class="button button-primary">
                <?php esc_html_e('Import Demo Content', 'campaignpress'); ?>
            </a>
            <a href="#" class="button button-secondary campaignpress-dismiss-demo-notice">
                <?php esc_html_e('Maybe Later', 'campaignpress'); ?>
            </a>
        </p>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.campaignpress-dismiss-demo-notice').on('click', function(e) {
            e.preventDefault();

            $.post(ajaxurl, {
                action: 'campaignpress_dismiss_demo_notice',
                nonce: '<?php echo wp_create_nonce('campaignpress_dismiss_demo_notice'); ?>'
            });

            $('.campaignpress-demo-notice').fadeOut();
        });
    });
    </script>
    <?php
}
add_action('admin_notices', 'campaignpress_demo_content_notice');

/**
 * Handle dismissing the demo content notice
 */
function campaignpress_dismiss_demo_notice_handler() {
    check_ajax_referer('campaignpress_dismiss_demo_notice', 'nonce');

    if (current_user_can('manage_options')) {
        update_user_meta(get_current_user_id(), 'campaignpress_demo_notice_dismissed', true);
    }

    wp_die();
}
add_action('wp_ajax_campaignpress_dismiss_demo_notice', 'campaignpress_dismiss_demo_notice_handler');

/**
 * Show welcome message after theme activation
 */
function campaignpress_activation_notice() {
    global $pagenow;

    if (!current_user_can('manage_options')) {
        return;
    }

    // Only show on themes page immediately after activation
    if ($pagenow !== 'themes.php' || !isset($_GET['activated'])) {
        return;
    }

    ?>
    <div class="notice notice-success is-dismissible">
        <h3><?php esc_html_e('CampaignPress Theme Activated!', 'campaignpress'); ?></h3>
        <p><?php esc_html_e('Thank you for choosing CampaignPress. Here are some quick steps to get started:', 'campaignpress'); ?></p>
        <ol style="list-style: decimal; margin-left: 20px;">
            <li><a href="<?php echo esc_url(admin_url('customize.php')); ?>"><?php esc_html_e('Customize your theme settings', 'campaignpress'); ?></a> <?php esc_html_e('(colors, campaign info, social media)', 'campaignpress'); ?></li>
            <li><a href="<?php echo esc_url(admin_url('themes.php?page=campaignpress-demo')); ?>"><?php esc_html_e('Import demo content', 'campaignpress'); ?></a> <?php esc_html_e('to see all features in action', 'campaignpress'); ?></li>
            <li><?php esc_html_e('Create your homepage using CampaignPress blocks', 'campaignpress'); ?></li>
            <li><?php esc_html_e('Add your campaign Issues, Events, and Team Members', 'campaignpress'); ?></li>
        </ol>
        <p>
            <a href="<?php echo esc_url(admin_url('customize.php')); ?>" class="button button-primary">
                <?php esc_html_e('Start Customizing', 'campaignpress'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('themes.php?page=campaignpress-demo')); ?>" class="button button-secondary">
                <?php esc_html_e('Import Demo Content', 'campaignpress'); ?>
            </a>
        </p>
    </div>
    <?php
}
add_action('admin_notices', 'campaignpress_activation_notice');

/**
 * Show notice if no donation URL is set
 */
function campaignpress_donation_url_notice() {
    // Only show to admins
    if (!current_user_can('manage_options')) {
        return;
    }

    // Don't show if donation URL is set
    $donation_url = get_theme_mod('campaignpress_donation_url', '');
    if (!empty($donation_url)) {
        return;
    }

    // Don't show if dismissed
    if (get_user_meta(get_current_user_id(), 'campaignpress_donation_notice_dismissed', true)) {
        return;
    }

    // Only show on specific pages
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->id, array('dashboard', 'edit-page', 'page'))) {
        return;
    }

    ?>
    <div class="notice notice-warning is-dismissible campaignpress-donation-notice">
        <h3><?php esc_html_e('Set Up Your Donation Link', 'campaignpress'); ?></h3>
        <p><?php esc_html_e('Don\'t forget to add your ActBlue, WinRed, or other donation processor URL so your donation buttons work properly.', 'campaignpress'); ?></p>
        <p>
            <a href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=campaignpress_campaign_info')); ?>" class="button button-primary">
                <?php esc_html_e('Add Donation URL', 'campaignpress'); ?>
            </a>
            <button type="button" class="button button-secondary campaignpress-dismiss-donation-notice">
                <?php esc_html_e('Dismiss', 'campaignpress'); ?>
            </button>
        </p>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.campaignpress-dismiss-donation-notice, .campaignpress-donation-notice .notice-dismiss').on('click', function(e) {
            $.post(ajaxurl, {
                action: 'campaignpress_dismiss_donation_notice',
                nonce: '<?php echo wp_create_nonce('campaignpress_dismiss_donation_notice'); ?>'
            });
        });
    });
    </script>
    <?php
}
add_action('admin_notices', 'campaignpress_donation_url_notice');

/**
 * Handle dismissing the donation URL notice
 */
function campaignpress_dismiss_donation_notice_handler() {
    check_ajax_referer('campaignpress_dismiss_donation_notice', 'nonce');

    if (current_user_can('manage_options')) {
        update_user_meta(get_current_user_id(), 'campaignpress_donation_notice_dismissed', true);
    }

    wp_die();
}
add_action('wp_ajax_campaignpress_dismiss_donation_notice', 'campaignpress_dismiss_donation_notice_handler');
