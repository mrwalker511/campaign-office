/**
 * Volunteer Portal JavaScript
 *
 * Handles volunteer portal functionality including login, tab switching,
 * shift signup, hours logging, and profile updates.
 *
 * @package CampaignPress
 * @since 2.0.0
 */

(function($) {
    'use strict';

    var portalVars = window.cp_volunteer_portal_vars || {};

    /**
     * Tab Switching
     */
    function initTabSwitching() {
        $('.cp-tab-button').on('click', function() {
            var tab = $(this).data('tab');

            // Remove active class from all tabs
            $('.cp-tab-button').removeClass('active');

            // Add active class to clicked tab
            $(this).addClass('active');

            // Hide all tab content
            $('.cp-tab-content').removeClass('active');

            // Show selected tab content
            $('[data-tab-content="' + tab + '"]').addClass('active');
        });
    }

    /**
     * Volunteer Login
     */
    function initVolunteerLogin() {
        $('#cp-volunteer-login-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $message = $form.find('.cp-login-message');
            var $submitBtn = $form.find('button[type="submit"]');

            $message.hide().empty();

            // Disable submit button
            $submitBtn.prop('disabled', true).text('Sending...');

            // Submit form data
            $.ajax({
                url: portalVars.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=cp_volunteer_login',
                success: function(response) {
                    if (response.success) {
                        $message
                            .html(
                                '<div class="cp-success-message">' +
                                    (response.data && response.data.message ? response.data.message : 'Check your email for a login link.') +
                                    '</div>'
                            )
                            .show();
                        $form[0].reset();
                    } else {
                        $message
                            .html(
                                '<div class="cp-error-message">' +
                                    (response.data && response.data.message ? response.data.message : 'Unable to send login link.') +
                                    '</div>'
                            )
                            .show();
                    }
                },
                error: function() {
                    var fallbackError = portalVars.login_error ? portalVars.login_error : 'An error occurred. Please try again.';
                    $message.html('<div class="cp-error-message">' + fallbackError + '</div>').show();
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).text('Send Login Link');
                }
            });
        });
    }

    /**
     * Shift Signup
     */
    function initShiftSignup() {
        $(document).on('click', '.cp-signup-shift-btn', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var shiftId = $btn.data('shift-id');
            var $originalText = $btn.text();

            // Disable button and show loading
            $btn.prop('disabled', true).text('Signing up...');

            // Submit signup request
            $.post(
                portalVars.ajax_url,
                {
                    action: 'cp_volunteer_signup_shift',
                    shift_id: shiftId,
                    _wpnonce: portalVars.nonce
                },
                function(response) {
                    if (response.success) {
                        // Show success notification
                        showNotification(response.data.message || 'Successfully signed up for shift!', 'success');
                        // Reload page after short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        // Show error notification
                        showNotification(response.data.message || 'Failed to sign up for shift.', 'error');
                        $btn.prop('disabled', false).text($originalText);
                    }
                }
            ).fail(function() {
                // Show generic error
                showNotification('An error occurred. Please try again.', 'error');
                $btn.prop('disabled', false).text($originalText);
            });
        });
    }

    /**
     * Hours Logging
     */
    function initHoursLogging() {
        $('#cp-log-hours-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $message = $form.find('.cp-form-message');
            var $submitBtn = $form.find('button[type="submit"]');

            // Disable submit button
            $submitBtn.prop('disabled', true).text('Logging Hours...');

            // Collect form data
            var formData = new FormData($form[0]);
            formData.append('action', 'cp_volunteer_log_hours');

            // Submit form data
            $.ajax({
                url: portalVars.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $message.html('<div class="cp-success-message">' + response.data.message + '</div>').show();
                        // Reset form
                        $form[0].reset();
                        // Reload recent hours list
                        if ($('.cp-recent-hours').length) {
                            location.reload();
                        }
                    } else {
                        // Show error message
                        $message.html('<div class="cp-error-message">' + response.data.message + '</div>').show();
                    }
                },
                error: function() {
                    var fallbackError = portalVars.hours_error ? portalVars.hours_error : 'An error occurred. Please try again.';
                    $message.html('<div class="cp-error-message">' + fallbackError + '</div>').show();
                },
                complete: function() {
                    // Re-enable submit button
                    $submitBtn.prop('disabled', false).text('Log Hours');
                }
            });
        });
    }

    /**
     * Profile Update
     */
    function initProfileUpdate() {
        $('#cp-profile-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $message = $form.find('.cp-form-message');
            var $submitBtn = $form.find('button[type="submit"]');

            // Disable submit button
            $submitBtn.prop('disabled', true).text('Updating...');

            // Submit form data
            $.ajax({
                url: portalVars.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=cp_update_volunteer_profile',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $message.html('<div class="cp-success-message">' + response.data.message + '</div>').show();
                    } else {
                        // Show error message
                        $message.html('<div class="cp-error-message">' + response.data.message + '</div>').show();
                    }
                },
                error: function() {
                    var fallbackError = portalVars.profile_error ? portalVars.profile_error : 'An error occurred. Please try again.';
                    $message.html('<div class="cp-error-message">' + fallbackError + '</div>').show();
                },
                complete: function() {
                    // Re-enable submit button
                    $submitBtn.prop('disabled', false).text('Update Profile');
                }
            });
        });
    }

    /**
     * Volunteer Logout
     */
    function cpVolunteerLogout() {
        // Best effort server-side logout
        $.post(portalVars.ajax_url, {
            action: 'cp_volunteer_logout',
            _wpnonce: portalVars.nonce
        }).always(function() {
            // Clear volunteer session cookie
            document.cookie = 'cp_volunteer_session=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
            location.reload();
        });
    }

    /**
     * Show Notification
     *
     * @param {string} message - Notification message
     * @param {string} type - Type: 'success' or 'error'
     */
    function showNotification(message, type) {
        // Create notification element
        var $notification = $('<div class="cp-notification cp-notification-' + type + '">' + message + '</div>');

        // Add to page
        $('body').append($notification);

        // Animate in
        setTimeout(function() {
            $notification.addClass('cp-notification-show');
        }, 10);

        // Auto-hide after 5 seconds
        setTimeout(function() {
            $notification.removeClass('cp-notification-show');
            // Remove after animation
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 5000);
    }

    /**
     * Document Ready
     */
    $(document).ready(function() {
        // Initialize components
        initTabSwitching();
        initVolunteerLogin();
        initShiftSignup();
        initHoursLogging();
        initProfileUpdate();

        // Initialize notification styles dynamically
        var notificationStyles =
            '<style>' +
            '.cp-notification {' +
            'position: fixed;' +
            'top: 20px;' +
            'right: 20px;' +
            'max-width: 400px;' +
            'padding: 1rem;' +
            'border-radius: 0.5rem;' +
            'z-index: 9999;' +
            'opacity: 0;' +
            'transform: translateY(-20px);' +
            'transition: all 0.3s ease;' +
            'box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);' +
            '}' +
            '.cp-notification-show {' +
            'opacity: 1;' +
            'transform: translateY(0);' +
            '}' +
            '.cp-notification-success {' +
            'background: #d4edda;' +
            'color: #155724;' +
            'border-left: 4px solid #28a745;' +
            '}' +
            '.cp-notification-error {' +
            'background: #f8d7da;' +
            'color: #721c24;' +
            'border-left: 4px solid #dc3545;' +
            '}' +
            '</style>';

        // Add notification styles if not already added
        if ($('#cp-portal-notification-styles').length === 0) {
            $('head').append('<style id="cp-portal-notification-styles">' + notificationStyles + '</style>');
        }

        // Make cpVolunteerLogout globally available
        window.cpVolunteerLogout = cpVolunteerLogout;
    });

})(jQuery);
