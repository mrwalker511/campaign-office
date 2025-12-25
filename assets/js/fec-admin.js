/**
 * CampaignPress FEC Compliance Admin JavaScript
 *
 * JavaScript for the FEC Compliance admin pages
 *
 * @package CampaignPress
 * @subpackage Premium/Compliance
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * FEC Compliance Admin Module
     */
    var CP_FEC_Admin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTabs();
            this.initContributionValidation();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Contribution validation form
            $(document).on('submit', '#cp-fec-validate-contribution', this.validateContribution);

            // Save settings
            $(document).on('click', '#cp-fec-save-settings', this.saveSettings);

            // Export FEC form
            $(document).on('click', '#cp-fec-export-form', this.exportForm);

            // Check donor limits
            $(document).on('change', '#cp-fec-donor-select', this.checkDonorLimits);

            // Generate report
            $(document).on('submit', '#cp-fec-generate-report', this.generateReport);
        },

        /**
         * Initialize tabs
         */
        initTabs: function() {
            $('.cp-fec-tabs .tab').on('click', function(e) {
                e.preventDefault();
                var tabId = $(this).data('tab');

                // Update active tab
                $(this).siblings().removeClass('active');
                $(this).addClass('active');

                // Update content
                $('.cp-fec-tab-content').removeClass('active');
                $('#' + tabId).addClass('active');
            });
        },

        /**
         * Initialize contribution validation
         */
        initContributionValidation: function() {
            // Real-time limit checking
            $('#cp-fec-contribution-amount').on('input', function() {
                var amount = parseFloat($(this).val()) || 0;
                var individualLimit = parseFloat(cpFEC.limits.individual_candidate) || 3300;

                if (amount > individualLimit) {
                    $(this).addClass('over-limit');
                    $('#cp-fec-limit-warning').show();
                } else {
                    $(this).removeClass('over-limit');
                    $('#cp-fec-limit-warning').hide();
                }
            });
        },

        /**
         * Validate contribution
         */
        validateContribution: function(e) {
            e.preventDefault();

            var $form = $(this);
            var donorId = $('#cp-fec-donor-id').val();
            var amount = $('#cp-fec-contribution-amount').val();
            var electionType = $('#cp-fec-election-type').val();

            $form.find('button').prop('disabled', true).text(cpFEC.strings.validating);

            $.ajax({
                url: cpFEC.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_fec_validate_contribution',
                    nonce: cpFEC.nonce,
                    donor_id: donorId,
                    amount: amount,
                    election_type: electionType
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;
                        if (data.valid) {
                            $('#cp-fec-validation-result')
                                .removeClass('notice-error')
                                .addClass('notice-success')
                                .html('<p><strong>' + cpFEC.strings.success + '</strong> ' + data.message + '</p>')
                                .show();
                        } else {
                            $('#cp-fec-validation-result')
                                .removeClass('notice-success')
                                .addClass('notice-error')
                                .html('<p><strong>' + cpFEC.strings.limit_exceeded + '</strong> ' + data.message + '</p>')
                                .show();
                        }
                    } else {
                        $('#cp-fec-validation-result')
                            .removeClass('notice-success')
                            .addClass('notice-error')
                            .html('<p><strong>' + cpFEC.strings.error + '</strong> ' + response.data.message + '</p>')
                            .show();
                    }
                },
                error: function() {
                    $('#cp-fec-validation-result')
                        .removeClass('notice-success')
                        .addClass('notice-error')
                        .html('<p><strong>' + cpFEC.strings.error + '</strong> ' + cpFEC.strings.error + '</p>')
                        .show();
                },
                complete: function() {
                    $form.find('button').prop('disabled', false).text(cpFEC.strings.validating);
                }
            });
        },

        /**
         * Save settings
         */
        saveSettings: function(e) {
            e.preventDefault();

            var $button = $(this);
            $button.prop('disabled', true).text(cpFEC.strings.savingChanges);

            var settings = {
                committee_id: $('#cp-fec-committee-id').val(),
                committee_name: $('#cp-fec-committee-name').val(),
                committee_type: $('#cp-fec-committee-type').val(),
                treasurer_name: $('#cp-fec-treasurer-name').val(),
                street1: $('#cp-fec-street1').val(),
                street2: $('#cp-fec-street2').val(),
                city: $('#cp-fec-city').val(),
                state: $('#cp-fec-state').val(),
                zip: $('#cp-fec-zip').val(),
                email: $('#cp-fec-email').val(),
                phone: $('#cp-fec-phone').val(),
                candidate_name: $('#cp-fec-candidate-name').val(),
                candidate_office: $('#cp-fec-candidate-office').val(),
                candidate_state: $('#cp-fec-candidate-state').val(),
                candidate_district: $('#cp-fec-candidate-district').val(),
                election_cycle: $('#cp-fec-election-cycle').val()
            };

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cp_fec_save_settings',
                    nonce: cpFEC.nonce,
                    settings: settings
                },
                success: function(response) {
                    if (response.success) {
                        $('#cp-fec-settings-result')
                            .removeClass('notice-error')
                            .addClass('notice-success')
                            .html('<p>' + cpFEC.strings.success + '</p>')
                            .show();
                    } else {
                        $('#cp-fec-settings-result')
                            .removeClass('notice-success')
                            .addClass('notice-error')
                            .html('<p>' + response.data.message + '</p>')
                            .show();
                    }
                },
                error: function() {
                    $('#cp-fec-settings-result')
                        .removeClass('notice-success')
                        .addClass('notice-error')
                        .html('<p>' + cpFEC.strings.error + '</p>')
                        .show();
                },
                complete: function() {
                    $button.prop('disabled', false).text(cpFEC.strings.savingChanges);
                }
            });
        },

        /**
         * Export FEC form
         */
        exportForm: function(e) {
            e.preventDefault();

            var $button = $(this);
            $button.prop('disabled', true).text(cpFEC.strings.processing);

            $.ajax({
                url: cpFEC.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_fec_export_fec_form',
                    nonce: cpFEC.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Create download link
                        var downloadUrl = response.data.file_url;
                        window.open(downloadUrl, '_blank');

                        $('#cp-fec-export-result')
                            .removeClass('notice-error')
                            .addClass('notice-success')
                            .html('<p>' + cpFEC.strings.success + ' <a href="' + downloadUrl + '">Download File</a></p>')
                            .show();
                    } else {
                        $('#cp-fec-export-result')
                            .removeClass('notice-success')
                            .addClass('notice-error')
                            .html('<p>' + response.data.message + '</p>')
                            .show();
                    }
                },
                error: function() {
                    $('#cp-fec-export-result')
                        .removeClass('notice-success')
                        .addClass('notice-error')
                        .html('<p>' + cpFEC.strings.error + '</p>')
                        .show();
                },
                complete: function() {
                    $button.prop('disabled', false).text($button.data('original-text'));
                }
            });
        },

        /**
         * Check donor limits
         */
        checkDonorLimits: function(e) {
            var donorId = $(this).val();

            if (!donorId) {
                $('#cp-fec-donor-limits').hide();
                return;
            }

            $.ajax({
                url: cpFEC.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_fec_check_donor_limits',
                    nonce: cpFEC.nonce,
                    donor_id: donorId
                },
                success: function(response) {
                    if (response.success) {
                        var limits = response.data;
                        var html = '<h4>' + cpFEC.strings.currentTotals + '</h4>';
                        html += '<ul>';
                        html += '<li>' + cpFEC.strings.primaryElection + ': $' + limits.primary.toLocaleString() + ' / $' + cpFEC.limits.individual_candidate.toLocaleString() + '</li>';
                        html += '<li>' + cpFEC.strings.generalElection + ': $' + limits.general.toLocaleString() + ' / $' + cpFEC.limits.individual_candidate.toLocaleString() + '</li>';
                        html += '<li>' + cpFEC.strings.pacTotal + ': $' + limits.pac.toLocaleString() + ' / $' + cpFEC.limits.individual_pac.toLocaleString() + '</li>';
                        html += '</ul>';

                        $('#cp-fec-donor-limits')
                            .html(html)
                            .show();
                    }
                }
            });
        },

        /**
         * Generate report
         */
        generateReport: function(e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('button');
            var reportType = $('#cp-fec-report-type').val();
            var startDate = $('#cp-fec-report-start').val();
            var endDate = $('#cp-fec-report-end').val();

            $button.prop('disabled', true).text(cpFEC.strings.processing);

            $.ajax({
                url: cpFEC.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_fec_generate_report',
                    nonce: cpFEC.nonce,
                    report_type: reportType,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    if (response.success) {
                        $('#cp-fec-report-result')
                            .removeClass('notice-error')
                            .addClass('notice-success')
                            .html('<p>' + cpFEC.strings.success + ' Report ID: ' + response.data.report_id + '</p>')
                            .show();
                    } else {
                        $('#cp-fec-report-result')
                            .removeClass('notice-success')
                            .addClass('notice-error')
                            .html('<p>' + response.data.message + '</p>')
                            .show();
                    }
                },
                error: function() {
                    $('#cp-fec-report-result')
                        .removeClass('notice-success')
                        .addClass('notice-error')
                        .html('<p>' + cpFEC.strings.error + '</p>')
                        .show();
                },
                complete: function() {
                    $button.prop('disabled', false).text(cpFEC.strings.processing);
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        CP_FEC_Admin.init();
    });

})(jQuery);
