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

            var $btn = $(this);
            var dataType = $btn.data('type');
            var walkListId = $btn.data('walk-list-id') || 0;

            $btn.prop('disabled', true).text('Exporting...');

            $.ajax({
                url: cpFieldOps.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_export_canvass_data',
                    nonce: cpFieldOps.nonce,
                    data_type: dataType,
                    walk_list_id: walkListId,
                    date_from: $('#export-date-from').val() || '',
                    date_to: $('#export-date-to').val() || ''
                },
                success: function(response) {
                    if (response.success) {
                        var csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(response.data.content);
                        var link = document.createElement('a');
                        link.setAttribute('href', csvContent);
                        link.setAttribute('download', response.data.filename);
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        showNotification('Export complete! Downloaded ' + response.data.records + ' records.', 'success');
                    } else {
                        showNotification(response.data.message || cpFieldOps.strings.errorOccurred, 'error');
                    }
                },
                error: function() {
                    showNotification(cpFieldOps.strings.errorOccurred, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Export Data');
                }
            });
        });
    }

    /**
     * Initialize quick action handlers
     */
    function initQuickActions() {
        // Mark voter as voted
        $(document).on('click', '.cp-mark-voted', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var voterId = $btn.data('voter-id');
            var voterName = $btn.data('voter-name');

            if (confirm('Mark ' + voterName + ' as having voted?')) {
                $.ajax({
                    url: cpFieldOps.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cp_record_turnout',
                        nonce: cpFieldOps.nonce,
                        voter_id: voterId,
                        voter_name: voterName,
                        vote_method: 'in_person'
                    },
                    success: function(response) {
                        if (response.success) {
                            $btn.closest('tr').addClass('voter-recorded');
                            $btn.prop('disabled', true).text('Voted ✓');
                            showNotification(response.data.message, 'success');
                        } else {
                            showNotification(response.data.message || cpFieldOps.strings.errorOccurred, 'error');
                        }
                    },
                    error: function() {
                        showNotification(cpFieldOps.strings.errorOccurred, 'error');
                    }
                });
            }
        });

        // Assign driver to ride
        $(document).on('click', '.cp-assign-driver', function(e) {
            e.preventDefault();
            var rideId = $(this).data('ride-id');
            var driversList = $(this).data('drivers') || [];

            var driverSelect = '<select id="driver-select" class="widefat">';
            driverSelect += '<option value="">Select a driver...</option>';
            
            if (typeof driversList === 'object' && Object.keys(driversList).length > 0) {
                $.each(driversList, function(id, name) {
                    driverSelect += '<option value="' + id + '">' + name + '</option>';
                });
            } else {
                driverSelect += '<option value="">No drivers available</option>';
            }
            driverSelect += '</select>';

            var modal = '<div class="cp-modal-overlay">' +
                '<div class="cp-modal">' +
                '<h2>Assign Driver</h2>' +
                '<p>Select a driver for this ride:</p>' +
                driverSelect +
                '<div class="cp-modal-actions">' +
                '<button class="button button-primary" id="confirm-driver">Assign</button>' +
                '<button class="button" id="cancel-driver">Cancel</button>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('body').append(modal);

            $('#confirm-driver').on('click', function() {
                var driverId = $('#driver-select').val();
                if (!driverId) {
                    alert('Please select a driver');
                    return;
                }

                $.ajax({
                    url: cpFieldOps.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cp_assign_driver',
                        nonce: cpFieldOps.nonce,
                        ride_id: rideId,
                        driver_id: driverId
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.data.message, 'success');
                            location.reload();
                        } else {
                            showNotification(response.data.message || cpFieldOps.strings.errorOccurred, 'error');
                        }
                    },
                    error: function() {
                        showNotification(cpFieldOps.strings.errorOccurred, 'error');
                    },
                    complete: function() {
                        $('.cp-modal-overlay').remove();
                    }
                });
            });

            $('#cancel-driver').on('click', function() {
                $('.cp-modal-overlay').remove();
            });
        });

        // Check out volunteer
        $(document).on('click', '.cp-checkout-btn', function(e) {
            e.preventDefault();
            var checkinId = $(this).data('checkin-id');

            if (confirm('Check out this volunteer?')) {
                $.ajax({
                    url: cpFieldOps.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cp_check_out_volunteer',
                        nonce: cpFieldOps.nonce,
                        checkin_id: checkinId
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification(response.data.message + ' Hours: ' + response.data.hours, 'success');
                            location.reload();
                        } else {
                            showNotification(response.data.message || cpFieldOps.strings.errorOccurred, 'error');
                        }
                    },
                    error: function() {
                        showNotification(cpFieldOps.strings.errorOccurred, 'error');
                    }
                });
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
                if (response.canvassing) {
                    $('.cp-canvassing-doors-knocked').text(numberFormat(response.canvassing.doors_knocked));
                    $('.cp-canvassing-conversations').text(numberFormat(response.canvassing.conversations));
                    $('.cp-canvassing-rate').text(response.canvassing.completion_rate.toFixed(1) + '%');
                }
                
                if (response.phone_banking) {
                    $('.cp-phonebank-calls').text(numberFormat(response.phone_banking.calls_made));
                    $('.cp-phonebank-answered').text(numberFormat(response.phone_banking.answered));
                    $('.cp-phonebank-talk-time').text(formatDuration(response.phone_banking.total_talk_time));
                }
                
                if (response.gotv) {
                    $('.cp-gotv-turnout').text(response.gotv.turnout_percentage.toFixed(1) + '%');
                    $('.cp-gotv-pledges').text(numberFormat(response.gotv.pledges));
                    $('.cp-gotv-rides').text(numberFormat(response.gotv.ride_requests));
                }
                
                if (response.volunteers) {
                    $('.cp-volunteers-active').text(numberFormat(response.volunteers.volunteers_today));
                    $('.cp-volunteers-hours').text(response.volunteers.hours_today.toFixed(1));
                }

                console.log('Stats refreshed successfully');
            },
            error: function() {
                console.log('Failed to refresh stats');
            }
        });
    }

    /**
     * Format number with commas
     */
    function numberFormat(number) {
        return parseInt(number).toLocaleString();
    }

    /**
     * Format duration in seconds to readable format
     */
    function formatDuration(seconds) {
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        if (hours > 0) {
            return hours + 'h ' + minutes + 'm';
        }
        return minutes + 'm';
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
