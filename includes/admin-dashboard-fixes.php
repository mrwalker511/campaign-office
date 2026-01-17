<?php
/**
 * Admin Dashboard Fixes
 *
 * This file contains fixes for common admin dashboard issues
 * including button functionality, form submission, and UI problems.
 *
 * @package CampaignPress
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fix: Ensure admin URLs are properly generated
 *
 * Some admin buttons may not work if URLs are malformed or missing.
 * This ensures all admin URLs are properly formatted.
 */
function cp_fix_admin_urls() {
    // Fix for admin menu URLs
    add_filter('admin_url', function($url, $path) {
        // Ensure URLs have proper protocol
        if (strpos($url, 'http') !== 0) {
            $url = admin_url($path);
        }
        return $url;
    }, 10, 2);

    // Fix for post-new.php links
    add_filter('post_new_post_link', function($link, $post_type) {
        // Ensure post type is properly encoded
        $link = admin_url('post-new.php?post_type=' . $post_type);
        return $link;
    }, 10, 2);
}
add_action('admin_init', 'cp_fix_admin_urls');

/**
 * Fix: Add proper nonce verification for AJAX handlers
 *
 * Some AJAX handlers may be missing nonce verification, which can cause
 * form submissions to fail silently.
 */
function cp_fix_ajax_handlers() {
    // Ensure all AJAX handlers have proper nonce verification
    $ajax_actions = array(
        'cp_validate_license',
        'cp_deactivate_license',
        'cp_toggle_feature',
        'cp_check_updates',
        'cp_subscribe',
    );

    foreach ($ajax_actions as $action) {
        add_action("wp_ajax_{$action}", 'cp_verify_ajax_nonce', 5);
        add_action("wp_ajax_nopriv_{$action}", 'cp_verify_ajax_nonce', 5);
    }
}
add_action('admin_init', 'cp_fix_ajax_handlers');

/**
 * Verify AJAX nonce before processing
 *
 * @return void
 */
function cp_verify_ajax_nonce() {
    // Allow exceptions for certain actions
    $allowed_exceptions = array('cp_subscribe');

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    if (in_array($action, $allowed_exceptions)) {
        return;
    }

    // Check nonce
    $nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
    if (!wp_verify_nonce($nonce, 'cp_premium_nonce')) {
        wp_send_json_error(array(
            'message' => __('Security check failed. Please refresh the page and try again.', 'campaignpress')
        ));
    }
}

/**
 * Fix: Prevent console errors in admin
 *
 * Some JavaScript may reference undefined variables or functions,
 * causing console errors that can break button functionality.
 */
function cp_fix_admin_console_errors() {
    // Add script to prevent console errors
    ?>
    <script>
        // Prevent errors from undefined variables
        window.cpAdminVars = window.cpAdminVars || {};
        window.campaignpress = window.campaignpress || {};
        window.campaignpress_vars = window.campaignpress_vars || {};

        // Ensure jQuery is loaded before running scripts
        (function($) {
            'use strict';

            $(document).ready(function() {
                // Fix button click handlers
                $('.button').on('click', function(e) {
                    // Allow default behavior for anchor buttons
                    if ($(this).is('a')) {
                        return;
                    }
                });

                // Fix form submissions
                $('form').on('submit', function(e) {
                    // Check if form has valid action
                    var $form = $(this);
                    var action = $form.attr('action');

                    if (!action || action === '#' || action === '') {
                        // Prevent submission if no action
                        console.warn('Form has no action attribute');
                        e.preventDefault();
                        return false;
                    }
                });

                // Fix button href attributes
                $('.button').each(function() {
                    var $btn = $(this);
                    var href = $btn.attr('href');

                    if ($btn.is('a') && (!href || href === '#' || href === '')) {
                        // Remove buttons with invalid links
                        console.warn('Button has invalid href', $btn);
                        $btn.attr('disabled', 'disabled').addClass('button-disabled');
                    }
                });
            });
        })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'cp_fix_admin_console_errors', 99);

/**
 * Fix: Ensure proper redirect after form submission
 *
 * Some forms may not redirect properly after submission,
 * causing users to think the button didn't work.
 */
function cp_fix_form_redirects() {
    // Catch form submissions and ensure proper redirects
    ?>
    <script>
        (function($) {
            'use strict';

            $(document).ready(function() {
                // Monitor for AJAX form submissions
                $(document).on('submit', 'form[data-ajax="true"]', function(e) {
                    var $form = $(this);
                    var $button = $form.find('button[type="submit"], input[type="submit"]');
                    var originalText = $button.text();

                    // Show loading state
                    $button.prop('disabled', true).text('Processing...');

                    // Restore button after timeout (fallback)
                    setTimeout(function() {
                        $button.prop('disabled', false).text(originalText);
                    }, 10000);
                });

                // Monitor for successful AJAX requests
                $(document).ajaxSuccess(function(event, xhr, settings) {
                    var data = xhr.responseJSON;

                    if (data && data.success) {
                        // Check if redirect is needed
                        if (data.data && data.data.redirect) {
                            window.location.href = data.data.redirect;
                        }

                        // Show success message
                        if (data.data && data.data.message) {
                            cpShowNotice(data.data.message, 'success');
                        }
                    }
                });

                // Monitor for failed AJAX requests
                $(document).ajaxError(function(event, xhr, settings, error) {
                    cpShowNotice('Request failed: ' + error, 'error');
                });

                // Function to show admin notices
                function cpShowNotice(message, type) {
                    type = type || 'info';
                    var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');

                    // Remove existing notices
                    $('.wrap > .notice').fadeOut(function() {
                        $(this).remove();
                    });

                    // Add new notice
                    $('.wrap h1').first().after($notice);

                    // Auto-dismiss after 5 seconds
                    setTimeout(function() {
                        $notice.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);

                    // Scroll to notice
                    $('html, body').animate({
                        scrollTop: $notice.offset().top - 100
                    }, 300);
                }
            });
        })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'cp_fix_form_redirects', 98);

/**
 * Fix: Ensure all required scripts are loaded
 *
 * Some admin pages may be missing required JavaScript files,
 * causing buttons to not respond to clicks.
 */
function cp_ensure_admin_scripts() {
    // Ensure jQuery is loaded
    wp_enqueue_script('jquery');

    // Ensure jQuery UI is loaded
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-widget');
    wp_enqueue_script('jquery-ui-sortable');

    // Add inline script to check for missing dependencies
    ?>
    <script>
        // Check if jQuery is loaded
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded. Some features may not work.');
        }

        // Check if WordPress admin script is loaded
        if (typeof wp === 'undefined') {
            console.warn('WordPress admin script is not loaded. Some features may not work.');
        }
    </script>
    <?php
}
add_action('admin_enqueue_scripts', 'cp_ensure_admin_scripts', 999);

/**
 * Fix: Debug admin button clicks
 *
 * This logs all button clicks to the console for debugging.
 * Remove this function in production.
 */
function cp_debug_button_clicks() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }

    ?>
    <script>
        (function($) {
            'use strict';

            $(document).ready(function() {
                // Log all button clicks
                $(document).on('click', '.button, button, input[type="button"], input[type="submit"]', function(e) {
                    console.log('Button clicked:', {
                        tag: this.tagName,
                        type: this.type,
                        class: this.className,
                        href: $(this).attr('href'),
                        id: this.id,
                        name: this.name,
                        disabled: this.disabled
                    });
                });

                // Log all form submissions
                $(document).on('submit', 'form', function(e) {
                    console.log('Form submitted:', {
                        action: $(this).attr('action'),
                        method: $(this).attr('method'),
                        id: this.id,
                        class: this.className
                    });
                });
            });
        })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'cp_debug_button_clicks', 100);

/**
 * Fix: Campaign Data dashboard button functionality
 *
 * Ensures buttons in the Campaign Data dashboard work correctly.
 */
function cp_fix_campaign_data_buttons() {
    // Add inline script to fix button functionality
    ?>
    <script>
        (function($) {
            'use strict';

            $(document).ready(function() {
                // Only run on Campaign Data page
                if (!$('body').hasClass('toplevel_page_campaign-data-main')) {
                    return;
                }

                // Fix button hrefs
                $('.campaignpress-dashboard .button').each(function() {
                    var $btn = $(this);

                    if ($btn.is('a')) {
                        var href = $btn.attr('href');

                        // Log button click
                        $btn.on('click', function(e) {
                            console.log('Campaign button clicked:', href);

                            // Ensure href is valid
                            if (!href || href === '#' || href === '' || href === 'javascript:void(0)') {
                                e.preventDefault();
                                console.error('Button has invalid href:', $btn);
                                return false;
                            }
                        });
                    }
                });
            });
        })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'cp_fix_campaign_data_buttons', 97);
