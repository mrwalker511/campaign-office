/**
 * Field Operations Frontend JavaScript
 *
 * Handles canvassing, phone banking, and GOTV frontend interfaces
 *
 * @package CampaignPress
 * @subpackage Premium/FieldOperations
 * @since 2.0.0
 */

(function ($) {
    'use strict';

    var currentAddress = 0;
    var walkListData = [];
    var offlineQueue = [];

    // Wait for DOM ready
    $(document).ready(function () {

        // Initialize canvassing interface if present
        if ($('.cp-canvassing-interface').length) {
            initCanvassingInterface();
        }

        // Initialize phone banking interface if present
        if ($('.cp-phone-banking-interface').length) {
            initPhoneBankingInterface();
        }

        // Initialize GOTV interface if present
        if ($('.cp-gotv-public-dashboard').length) {
            initGotvDashboard();
        }

        // Initialize volunteer check-in if present
        if ($('.cp-volunteer-checkin').length) {
            initVolunteerCheckin();
        }

    });

    /**
     * Initialize canvassing interface
     */
    function initCanvassingInterface() {
        var walkListId = $('.cp-canvassing-interface').data('walk-list-id');

        // Load walk list data
        loadWalkList(walkListId);

        // Result button handlers
        $('.cp-result-btn').on('click', function () {
            $('.cp-result-btn').removeClass('active');
            $(this).addClass('active');

            var result = $(this).data('result');

            if (result === 'answered') {
                $('#cp-conversation-form').slideDown();
            } else {
                $('#cp-conversation-form').slideUp();
            }
        });

        // Save interaction
        $('#cp-save-interaction').on('click', function () {
            saveInteraction();
        });

        // Navigation
        $('#cp-next-address').on('click', function () {
            nextAddress();
        });

        $('#cp-prev-address').on('click', function () {
            previousAddress();
        });

        $('#cp-skip-address').on('click', function () {
            nextAddress();
        });

        // GPS tracking
        $('#cp-track-location').on('click', function () {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    // Store location data for canvassing
                });
            }
        });

        // Callback scheduling
        $('#cp-schedule-callback').on('change', function () {
            $('#cp-callback-time').toggle(this.checked);
        });
    }

    /**
     * Initialize phone banking interface
     */
    function initPhoneBankingInterface() {
        var callListId = $('.cp-phone-banking-interface').data('call-list-id');

        // Load next call
        loadNextCall(callListId);

        // Click-to-call
        $('#cp-click-to-call').on('click', function () {
            startCall();
        });

        // Disposition buttons
        $('.cp-disp-btn').on('click', function () {
            $('.cp-disp-btn').removeClass('active');
            $(this).addClass('active');

            var disposition = $(this).data('disposition');

            if (disposition === 'answered') {
                $('#cp-response-form').slideDown();
            } else {
                $('#cp-response-form').slideUp();
            }
        });

        // Save call
        $('#cp-save-call').on('click', function () {
            saveCall();
        });

        // Skip call
        $('#cp-skip-call').on('click', function () {
            loadNextCall(callListId);
        });
    }

    /**
     * Initialize GOTV dashboard
     */
    function initGotvDashboard() {
        // Refresh turnout stats every 60 seconds
        setInterval(function () {
            refreshGotvStats();
        }, 60000);
    }

    /**
     * Initialize volunteer check-in
     */
    function initVolunteerCheckin() {
        $('#cp-checkin-btn').on('click', function () {
            checkIn();
        });

        $('#cp-checkout-btn').on('click', function () {
            checkOut();
        });
    }

    /**
     * Load walk list data
     */
    function loadWalkList(walkListId) {
        if (!walkListId) return;

        $.ajax({
            url: cpFieldOps.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cp_get_walk_list',
                walk_list_id: walkListId,
                nonce: cpFieldOps.nonce
            },
            success: function (response) {
                if (response.success && response.data.addresses) {
                    walkListData = response.data.addresses;
                    displayAddress(0);
                }
            },
            error: function () {
                alert(cpFieldOps.strings.errorOccurred);
            }
        });
    }

    /**
     * Display address at index
     */
    function displayAddress(index) {
        if (!walkListData.length) return;

        currentAddress = index;
        var address = walkListData[index];

        $('#cp-current-address').text(address.full_address);
        $('#cp-address-details').html('<p>' + address.city + ', ' + address.state + ' ' + address.zip + '</p>');

        // Update progress
        var progress = ((index + 1) / walkListData.length) * 100;
        $('#cp-progress-fill').css('width', progress + '%');
        $('#cp-completed-count').text(index + 1);
        $('#cp-total-count').text(walkListData.length);
    }

    /**
     * Save canvass interaction
     */
    function saveInteraction() {
        var result = $('.cp-result-btn.active').data('result');

        if (!result) {
            alert('Please select a result');
            return;
        }

        var data = {
            action: 'cp_save_canvass_interaction',
            nonce: cpFieldOps.nonce,
            walk_list_id: $('.cp-canvassing-interface').data('walk-list-id'),
            address: $('#cp-current-address').text(),
            result: result,
            voter_name: $('#cp-voter-name').val(),
            voter_email: $('#cp-voter-email').val(),
            voter_phone: $('#cp-voter-phone').val(),
            notes: $('#cp-notes').val()
        };

        $.ajax({
            url: cpFieldOps.ajaxUrl,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    // Clear form
                    $('.cp-result-btn').removeClass('active');
                    $('#cp-conversation-form').slideUp();
                    $('#cp-voter-name, #cp-voter-email, #cp-voter-phone, #cp-notes').val('');

                    // Next address
                    nextAddress();
                } else {
                    alert(response.data.message || cpFieldOps.strings.errorOccurred);
                }
            },
            error: function () {
                // Save to offline queue if offline
                offlineQueue.push(data);
                updateOfflineQueue();
                nextAddress();
            }
        });
    }

    /**
     * Navigate to next address
     */
    function nextAddress() {
        if (currentAddress < walkListData.length - 1) {
            displayAddress(currentAddress + 1);
        } else {
            alert('Walk list complete!');
        }
    }

    /**
     * Navigate to previous address
     */
    function previousAddress() {
        if (currentAddress > 0) {
            displayAddress(currentAddress - 1);
        }
    }

    /**
     * Load next call for phone banking
     */
    function loadNextCall(callListId) {
        $.ajax({
            url: cpFieldOps.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cp_get_next_call',
                call_list_id: callListId,
                nonce: cpFieldOps.nonce
            },
            success: function (response) {
                if (response.success && response.data.contact) {
                    var contact = response.data.contact;
                    $('#cp-contact-name').text(contact.name);
                    $('#cp-contact-phone').text(contact.phone);
                    $('#cp-script-content').html(contact.script);
                }
            }
        });
    }

    /**
     * Start call (placeholder)
     */
    function startCall() {
        $(this).prop('disabled', true).text('Connected');
        $('#cp-call-timer').show();

        // Start timer
        var seconds = 0;
        var timer = setInterval(function () {
            seconds++;
            var minutes = Math.floor(seconds / 60);
            var secs = seconds % 60;
            $('#cp-timer-display').text(
                String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0')
            );
        }, 1000);

        // Store timer reference
        $(this).data('timer', timer);
    }

    /**
     * Save phone call
     */
    function saveCall() {
        var disposition = $('.cp-disp-btn.active').data('disposition');

        if (!disposition) {
            alert('Please select a call result');
            return;
        }

        var callListId = $('.cp-phone-banking-interface').data('call-list-id');
        var contactName = $('#cp-contact-name').text();
        var contactPhone = $('#cp-contact-phone').text();
        var timerDisplay = $('#cp-timer-display').text();
        var callDuration = 0;

        if (timerDisplay) {
            var parts = timerDisplay.split(':');
            callDuration = parseInt(parts[0]) * 60 + parseInt(parts[1]);
        }

        var data = {
            action: 'cp_save_call',
            nonce: cpFieldOps.nonce,
            call_list_id: callListId,
            contact_name: contactName,
            contact_phone: contactPhone,
            disposition: disposition,
            call_duration: callDuration,
            notes: $('#cp-call-notes').val(),
            responses: {}
        };

        // Collect response data if answered
        if (disposition === 'answered') {
            $('.cp-response-field').each(function () {
                var fieldName = $(this).data('field');
                data.responses[fieldName] = $(this).val();
            });
        }

        $.ajax({
            url: cpFieldOps.ajaxUrl,
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.success) {
                    // Clear form
                    $('.cp-disp-btn').removeClass('active');
                    $('#cp-response-form').slideUp();
                    $('#cp-call-notes').val('');
                    $('#cp-timer-display').text('00:00');

                    // Load next call
                    loadNextCall(callListId);
                } else {
                    alert(response.data.message || cpFieldOps.strings.errorOccurred);
                }
            },
            error: function () {
                // Save to offline queue if offline
                offlineQueue.push(data);
                updateOfflineQueue();

                // Load next call anyway
                loadNextCall(callListId);
            }
        });
    }

    /**
     * Check in volunteer
     */
    function checkIn() {
        $.ajax({
            url: cpFieldOps.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cp_check_in_volunteer',
                nonce: cpFieldOps.nonce
            },
            success: function (response) {
                if (response.success) {
                    $('#cp-checkin-btn').hide();
                    $('#cp-checkout-btn').show();
                    $('#cp-checkin-status').html('<p>Checked in at ' + new Date().toLocaleTimeString() + '</p>');
                }
            }
        });
    }

    /**
     * Check out volunteer
     */
    function checkOut() {
        $.ajax({
            url: cpFieldOps.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cp_check_out_volunteer',
                nonce: cpFieldOps.nonce
            },
            success: function (response) {
                if (response.success) {
                    $('#cp-checkout-btn').hide();
                    $('#cp-checkin-btn').show();
                    $('#cp-checkin-status').html('<p>Checked out. Thank you!</p>');
                }
            }
        });
    }

    /**
     * Refresh GOTV stats
     */
    function refreshGotvStats() {
        $.ajax({
            url: cpFieldOps.restUrl + 'gotv/turnout',
            type: 'GET',
            success: function (response) {
                // Update turnout display
                if (response.turnout_percentage !== undefined) {
                    $('.cp-turnout-percentage').text(response.turnout_percentage.toFixed(1) + '%');
                }
            }
        });
    }

    /**
     * Update offline queue display
     */
    function updateOfflineQueue() {
        if (offlineQueue.length > 0) {
            $('#cp-offline-queue').show();
            $('#cp-queue-count').text(offlineQueue.length);
        } else {
            $('#cp-offline-queue').hide();
        }
    }

    // Monitor online/offline status
    window.addEventListener('online', function () {
        $('.cp-status-indicator').removeClass('cp-offline').addClass('cp-online');
        $('.cp-status-text').text('Connected');

        // Sync offline queue
        syncOfflineData();
    });

    window.addEventListener('offline', function () {
        $('.cp-status-indicator').removeClass('cp-online').addClass('cp-offline');
        $('.cp-status-text').text('Offline');
    });

    /**
     * Sync offline data when connection restored
     */
    function syncOfflineData() {
        if (offlineQueue.length === 0) return;

        $.ajax({
            url: cpFieldOps.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cp_field_ops_sync',
                sync_data: JSON.stringify({
                    canvassing: offlineQueue
                }),
                nonce: cpFieldOps.nonce
            },
            success: function (response) {
                if (response.success) {
                    offlineQueue = [];
                    updateOfflineQueue();
                }
            }
        });
    }

})(jQuery);
