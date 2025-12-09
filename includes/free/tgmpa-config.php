<?php
/**
 * TGM Plugin Activation Configuration
 *
 * Registers recommended and required plugins for CampaignPress theme
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Include the TGM_Plugin_Activation class
 */
require_once CAMPAIGNPRESS_INCLUDES_DIR . '/lib/tgmpa/class-tgm-plugin-activation.php';

add_action('tgmpa_register', 'campaignpress_register_required_plugins');

/**
 * Register the required and recommended plugins for this theme
 */
function campaignpress_register_required_plugins() {
    /**
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = array(

        // Contact Form 7 - Essential for campaign contact forms
        array(
            'name'     => 'Contact Form 7',
            'slug'     => 'contact-form-7',
            'required' => false,
            'version'  => '5.8',
        ),

        // The Events Calendar - Essential for campaign event management
        array(
            'name'     => 'The Events Calendar',
            'slug'     => 'the-events-calendar',
            'required' => false,
            'version'  => '6.0',
        ),

        // MailChimp for WordPress - Email list building
        array(
            'name'     => 'MC4WP: Mailchimp for WordPress',
            'slug'     => 'mailchimp-for-wp',
            'required' => false,
        ),

        // Yoast SEO - Search engine optimization
        array(
            'name'     => 'Yoast SEO',
            'slug'     => 'wordpress-seo',
            'required' => false,
        ),

        // GiveWP - Advanced donation management (campaign-specific)
        array(
            'name'     => 'GiveWP - Donation Plugin',
            'slug'     => 'give',
            'required' => false,
        ),

        // Wordfence Security - Campaign website security
        array(
            'name'     => 'Wordfence Security',
            'slug'     => 'wordfence',
            'required' => false,
        ),

        // Social Warfare - Social sharing optimization
        array(
            'name'     => 'Social Warfare',
            'slug'     => 'social-warfare',
            'required' => false,
        ),

        // MonsterInsights - Google Analytics for WordPress
        array(
            'name'     => 'MonsterInsights',
            'slug'     => 'google-analytics-for-wordpress',
            'required' => false,
        ),

        // WP Fastest Cache - Performance optimization
        array(
            'name'     => 'WP Fastest Cache',
            'slug'     => 'wp-fastest-cache',
            'required' => false,
        ),

        // Really Simple SSL - SSL/HTTPS enforcement
        array(
            'name'     => 'Really Simple SSL',
            'slug'     => 'really-simple-ssl',
            'required' => false,
        ),

    );

    /**
     * Array of configuration settings
     */
    $config = array(
        'id'           => 'campaign-office',                 // Unique ID for hashing notices
        'default_path' => '',                              // Default absolute path to bundled plugins
        'menu'         => 'tgmpa-install-plugins',         // Menu slug
        'parent_slug'  => 'themes.php',                    // Parent menu slug
        'capability'   => 'edit_theme_options',            // Capability needed to view plugin install page
        'has_notices'  => true,                            // Show admin notices or not
        'dismissable'  => true,                            // If false, a user cannot dismiss the nag message
        'dismiss_msg'  => '',                              // If 'dismissable' is false, this message will be output
        'is_automatic' => false,                           // Automatically activate plugins after installation or not
        'message'      => '',                              // Message to output right before the plugins table

        'strings'      => array(
            'page_title'                      => __('Install Required & Recommended Plugins', 'campaign-office'),
            'menu_title'                      => __('Install Plugins', 'campaign-office'),
            'installing'                      => __('Installing Plugin: %s', 'campaign-office'),
            'updating'                        => __('Updating Plugin: %s', 'campaign-office'),
            'oops'                            => __('Something went wrong with the plugin API.', 'campaign-office'),
            'notice_can_install_required'     => _n_noop(
                'CampaignPress requires the following plugin: %1$s.',
                'CampaignPress requires the following plugins: %1$s.',
                'campaign-office'
            ),
            'notice_can_install_recommended'  => _n_noop(
                'CampaignPress recommends the following plugin for enhanced campaign functionality: %1$s.',
                'CampaignPress recommends the following plugins for enhanced campaign functionality: %1$s.',
                'campaign-office'
            ),
            'notice_ask_to_update'            => _n_noop(
                'The following plugin needs to be updated to its latest version for CampaignPress: %1$s.',
                'The following plugins need to be updated to their latest versions for CampaignPress: %1$s.',
                'campaign-office'
            ),
            'notice_ask_to_update_maybe'      => _n_noop(
                'There is an update available for: %1$s.',
                'There are updates available for the following plugins: %1$s.',
                'campaign-office'
            ),
            'notice_can_activate_required'    => _n_noop(
                'The following required plugin is currently inactive: %1$s.',
                'The following required plugins are currently inactive: %1$s.',
                'campaign-office'
            ),
            'notice_can_activate_recommended' => _n_noop(
                'The following recommended plugin is currently inactive: %1$s.',
                'The following recommended plugins are currently inactive: %1$s.',
                'campaign-office'
            ),
            'install_link'                    => _n_noop(
                'Begin installing plugin',
                'Begin installing plugins',
                'campaign-office'
            ),
            'update_link'                     => _n_noop(
                'Begin updating plugin',
                'Begin updating plugins',
                'campaign-office'
            ),
            'activate_link'                   => _n_noop(
                'Begin activating plugin',
                'Begin activating plugins',
                'campaign-office'
            ),
            'return'                          => __('Return to Required Plugins Installer', 'campaign-office'),
            'plugin_activated'                => __('Plugin activated successfully.', 'campaign-office'),
            'activated_successfully'          => __('The following plugin was activated successfully:', 'campaign-office'),
            'plugin_already_active'           => __('No action taken. Plugin %1$s was already active.', 'campaign-office'),
            'plugin_needs_higher_version'     => __('Plugin not activated. A higher version of %s is needed for CampaignPress. Please update the plugin.', 'campaign-office'),
            'complete'                        => __('All plugins installed and activated successfully. %1$s', 'campaign-office'),
            'dismiss'                         => __('Dismiss this notice', 'campaign-office'),
            'notice_cannot_install_activate'  => __('There are one or more required or recommended plugins to install, update or activate.', 'campaign-office'),
            'contact_admin'                   => __('Please contact the administrator of this site for help.', 'campaign-office'),

            'nag_type'                        => 'updated', // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'
        ),
    );

    tgmpa($plugins, $config);
}
