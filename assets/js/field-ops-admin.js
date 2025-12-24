/**
 * Field Operations Admin JavaScript
 *
 * Handles admin interface functionality for field operations module
 *
 * @package CampaignPress
 * @subpackage Premium/FieldOperations
 * @since 2.0.0
 */

(function($) {
    'use strict';

    // Wait for DOM ready
    $(document).ready(function() {

        // Initialize admin features
        initDataExport();
        initQuickActions();
        initStatsRefresh();

    });

    /**
     * Initialize data export functionality
     */
    function initDataExport() {
        $('.cp-export-data').on('click', function(e) {
            e.preventDefault();

            var dataType = $(this).data('type');

            // Show loading state
            $(this).prop('disabled', true).text('Exporting...');

            // TODO: Implement actual export functionality
            setTimeout(function() {
                alert('Export functionality will be implemented in a future update.');
                $('.cp-export-data').prop('disabled', false).text('Export Data');
            }, 500);
        });
    }

    /**
     * Initialize quick action handlers
     */
    function initQuickActions() {
        // Mark voter as voted
        $(document).on('click', '.cp-mark-voted', function(e) {
            e.preventDefault();
            var voterId = $(this).data('voter-id');

            if (confirm('Mark this voter as having voted?')) {
                // TODO: Implement AJAX call
                alert('This functionality will be fully implemented in a future update.');
            }
        });

        // Assign driver to ride
        $(document).on('click', '.cp-assign-driver', function(e) {
            e.preventDefault();
            var rideId = $(this).data('ride-id');

            // TODO: Show driver selection modal
            alert('Driver assignment interface will be implemented in a future update.');
        });

        // Check out volunteer
        $(document).on('click', '.cp-checkout-btn', function(e) {
            e.preventDefault();
            var checkinId = $(this).data('checkin-id');

            if (confirm('Check out this volunteer?')) {
                // TODO: Implement AJAX call
                alert('This functionality will be fully implemented in a future update.');
            }
        });
    }

    /**
     * Initialize stats auto-refresh
     */
    function initStatsRefresh() {
        // Check if we're on a dashboard page
        if ($('.cp-field-ops-dashboard, .cp-gotv-dashboard, .cp-scheduling-dashboard').length) {
            // Refresh stats every 30 seconds
            setInterval(function() {
                refreshDashboardStats();
            }, 30000);
        }
    }

    /**
     * Refresh dashboard statistics
     */
    function refreshDashboardStats() {
        $.ajax({
            url: cpFieldOps.restUrl + 'field-ops/stats',
            type: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', cpFieldOps.nonce);
            },
            success: function(response) {
                // TODO: Update stats on page
                console.log('Stats refreshed', response);
            },
            error: function() {
                console.log('Failed to refresh stats');
            }
        });
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        type = type || 'success';

        var notificationClass = type === 'error' ? 'notice-error' : 'notice-success';
        var $notice = $('<div class="notice ' + notificationClass + ' is-dismissible"><p>' + message + '</p></div>');

        $('.wrap > h1').after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

})(jQuery);
