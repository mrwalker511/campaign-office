/**
 * CampaignPress Premium Admin JavaScript
 *
 * @package CampaignPress
 * @subpackage Premium
 * @since 2.0.0
 */

(function($) {
    'use strict';

    /**
     * Premium Admin Class
     */
    var CPPremiumAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initTooltips();
            this.checkLicenseStatus();
        },

        /**
         * Bind DOM events
         */
        bindEvents: function() {
            // Auto-dismiss notices
            $(document).on('click', '.notice.is-dismissible .notice-dismiss', function() {
                $(this).closest('.notice').fadeOut();
            });

            // Confirm destructive actions
            $(document).on('click', '[data-confirm]', function(e) {
                var message = $(this).data('confirm');
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });

            // Toggle feature details
            $(document).on('click', '.cp-feature-details-toggle', function(e) {
                e.preventDefault();
                $(this).closest('.cp-feature-card').find('.cp-feature-details').slideToggle();
            });

            // Copy to clipboard
            $(document).on('click', '[data-copy]', function(e) {
                e.preventDefault();
                var text = $(this).data('copy');
                CPPremiumAdmin.copyToClipboard(text);
                CPPremiumAdmin.showNotice(__('Copied to clipboard!'), 'success');
            });

            // Form validation
            $(document).on('submit', '.cp-validate-form', function(e) {
                if (!CPPremiumAdmin.validateForm($(this))) {
                    e.preventDefault();
                    return false;
                }
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            if (typeof $.fn.tooltip !== 'undefined') {
                $('.cp-tooltip').tooltip({
                    position: {
                        my: 'center bottom-10',
                        at: 'center top'
                    }
                });
            }
        },

        /**
         * Check license status on page load
         */
        checkLicenseStatus: function() {
            var $statusIndicator = $('.cp-license-status-indicator');
            if ($statusIndicator.length && $statusIndicator.data('auto-check')) {
                // Check status every 5 minutes
                setInterval(function() {
                    CPPremiumAdmin.refreshLicenseStatus();
                }, 5 * 60 * 1000);
            }
        },

        /**
         * Refresh license status
         */
        refreshLicenseStatus: function() {
            $.ajax({
                url: cpPremium.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_check_license_status',
                    nonce: cpPremium.nonce
                },
                success: function(response) {
                    if (response.success && response.data.status_changed) {
                        // Reload page if status changed
                        location.reload();
                    }
                }
            });
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            var isValid = true;
            var errors = [];

            // Check required fields
            $form.find('[required]').each(function() {
                var $field = $(this);
                var value = $field.val().trim();

                if (value === '') {
                    isValid = false;
                    $field.addClass('error');
                    var label = $field.closest('tr').find('label').text() || $field.attr('name');
                    errors.push(label + ' is required.');
                } else {
                    $field.removeClass('error');
                }

                // Email validation
                if ($field.attr('type') === 'email' && value !== '') {
                    if (!CPPremiumAdmin.isValidEmail(value)) {
                        isValid = false;
                        $field.addClass('error');
                        errors.push('Please enter a valid email address.');
                    }
                }
            });

            // Show errors
            if (!isValid) {
                var errorHtml = '<ul><li>' + errors.join('</li><li>') + '</li></ul>';
                CPPremiumAdmin.showNotice(errorHtml, 'error');

                // Scroll to first error
                var $firstError = $form.find('.error').first();
                if ($firstError.length) {
                    $('html, body').animate({
                        scrollTop: $firstError.offset().top - 100
                    }, 300);
                }
            }

            return isValid;
        },

        /**
         * Validate email
         */
        isValidEmail: function(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        /**
         * Copy text to clipboard
         */
        copyToClipboard: function(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
            }
        },

        /**
         * Show notice
         */
        showNotice: function(message, type) {
            type = type || 'info';
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button></div>');

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
        },

        /**
         * Show loading overlay
         */
        showLoading: function($element) {
            $element = $element || $('.wrap');
            $element.addClass('cp-loading');
        },

        /**
         * Hide loading overlay
         */
        hideLoading: function($element) {
            $element = $element || $('.wrap');
            $element.removeClass('cp-loading');
        },

        /**
         * Format date
         */
        formatDate: function(dateString) {
            var date = new Date(dateString);
            var options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString(undefined, options);
        },

        /**
         * Format number
         */
        formatNumber: function(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            var timeout;
            return function() {
                var context = this;
                var args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        },

        /**
         * Get query parameter
         */
        getQueryParam: function(param) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        },

        /**
         * Update query parameter
         */
        updateQueryParam: function(param, value) {
            var url = new URL(window.location);
            url.searchParams.set(param, value);
            window.history.pushState({}, '', url);
        }
    };

    /**
     * License Management
     */
    var CPLicenseManager = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // License key formatting
            $(document).on('input', '#license_key', this.formatLicenseKey);

            // Quick license check
            $(document).on('click', '.cp-quick-license-check', this.quickCheck);
        },

        /**
         * Format license key as user types
         */
        formatLicenseKey: function() {
            var $input = $(this);
            var value = $input.val().replace(/[^A-Za-z0-9]/g, '').toUpperCase();
            var formatted = value.match(/.{1,4}/g);

            if (formatted) {
                $input.val(formatted.join('-'));
            }
        },

        /**
         * Quick license check
         */
        quickCheck: function(e) {
            e.preventDefault();
            var $btn = $(this);
            var originalText = $btn.text();

            $btn.prop('disabled', true).text('Checking...');

            $.ajax({
                url: cpPremium.ajax_url,
                type: 'POST',
                data: {
                    action: 'cp_quick_license_check',
                    nonce: cpPremium.nonce
                },
                success: function(response) {
                    if (response.success) {
                        CPPremiumAdmin.showNotice(response.data.message, 'success');
                    } else {
                        CPPremiumAdmin.showNotice(response.data.message, 'error');
                    }
                    $btn.prop('disabled', false).text(originalText);
                },
                error: function() {
                    CPPremiumAdmin.showNotice('Connection error. Please try again.', 'error');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        }
    };

    /**
     * Feature Manager
     */
    var CPFeatureManager = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.updateFeatureCount();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Feature search
            $(document).on('input', '#cp-feature-search', CPPremiumAdmin.debounce(this.searchFeatures, 300));

            // Feature filter
            $(document).on('change', '#cp-feature-filter', this.filterFeatures);

            // Bulk actions
            $(document).on('click', '.cp-bulk-enable', this.bulkEnable);
            $(document).on('click', '.cp-bulk-disable', this.bulkDisable);
        },

        /**
         * Search features
         */
        searchFeatures: function() {
            var query = $(this).val().toLowerCase();

            $('.cp-feature-card').each(function() {
                var $card = $(this);
                var title = $card.find('h3').text().toLowerCase();
                var description = $card.find('.cp-feature-description').text().toLowerCase();

                if (title.indexOf(query) !== -1 || description.indexOf(query) !== -1) {
                    $card.show();
                } else {
                    $card.hide();
                }
            });
        },

        /**
         * Filter features
         */
        filterFeatures: function() {
            var filter = $(this).val();

            $('.cp-feature-card').each(function() {
                var $card = $(this);

                if (filter === 'all') {
                    $card.show();
                } else if (filter === 'enabled') {
                    if ($card.find('.cp-feature-toggle').is(':checked')) {
                        $card.show();
                    } else {
                        $card.hide();
                    }
                } else if (filter === 'disabled') {
                    if (!$card.find('.cp-feature-toggle').is(':checked')) {
                        $card.show();
                    } else {
                        $card.hide();
                    }
                }
            });
        },

        /**
         * Update feature count
         */
        updateFeatureCount: function() {
            var total = $('.cp-feature-card').length;
            var enabled = $('.cp-feature-toggle:checked').length;

            $('.cp-feature-count').text(enabled + ' / ' + total);
        },

        /**
         * Bulk enable features
         */
        bulkEnable: function(e) {
            e.preventDefault();

            if (!confirm('Enable all available features?')) {
                return;
            }

            $('.cp-feature-toggle:not(:checked):not(:disabled)').each(function() {
                $(this).prop('checked', true).trigger('change');
            });
        },

        /**
         * Bulk disable features
         */
        bulkDisable: function(e) {
            e.preventDefault();

            if (!confirm('Disable all features?')) {
                return;
            }

            $('.cp-feature-toggle:checked:not(:disabled)').each(function() {
                $(this).prop('checked', false).trigger('change');
            });
        }
    };

    /**
     * System Status
     */
    var CPSystemStatus = {

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initCharts();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Refresh status
            $(document).on('click', '.cp-refresh-status', this.refreshStatus);

            // Export report
            $(document).on('click', '.cp-export-report', this.exportReport);
        },

        /**
         * Initialize charts
         */
        initCharts: function() {
            // If Chart.js is available, initialize charts
            if (typeof Chart !== 'undefined') {
                // Implementation for charts would go here
            }
        },

        /**
         * Refresh status
         */
        refreshStatus: function(e) {
            e.preventDefault();
            location.reload();
        },

        /**
         * Export report
         */
        exportReport: function(e) {
            e.preventDefault();
            // Implementation handled in system-status-page.php
        }
    };

    /**
     * Translation helper
     */
    function __(string) {
        return cpPremium.strings[string] || string;
    }

    /**
     * Document ready
     */
    $(document).ready(function() {
        // Initialize all modules
        CPPremiumAdmin.init();
        CPLicenseManager.init();
        CPFeatureManager.init();
        CPSystemStatus.init();

        // Update feature count when toggles change
        $(document).on('change', '.cp-feature-toggle', function() {
            CPFeatureManager.updateFeatureCount();
        });

        // Smooth scroll for anchor links
        $(document).on('click', 'a[href^="#"]', function(e) {
            var target = $(this.hash);
            if (target.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 300);
            }
        });

        // Initialize tabs if present
        if ($('.nav-tab-wrapper').length) {
            var activeTab = CPPremiumAdmin.getQueryParam('tab') || $('.nav-tab-wrapper .nav-tab').first().data('tab');
            $('.cp-tab-content').hide();
            $('.cp-tab-content[data-tab="' + activeTab + '"]').show();
        }
    });

    // Expose to global scope if needed
    window.CPPremiumAdmin = CPPremiumAdmin;
    window.CPLicenseManager = CPLicenseManager;
    window.CPFeatureManager = CPFeatureManager;
    window.CPSystemStatus = CPSystemStatus;

    // Defensive fallback for cpPremium localization object. Some preview tools
    // or sandboxed frames may prevent `wp_localize_script` from running; this
    // prevents JS errors and provides reasonable defaults for local dev.
    if (typeof window.cpPremium === 'undefined') {
        window.cpPremium = {
            ajax_url: (typeof ajaxurl !== 'undefined') ? ajaxurl : (window.location.origin ? window.location.origin + '/wp-admin/admin-ajax.php' : '/wp-admin/admin-ajax.php'),
            nonce: '',
            strings: {
                validating: 'Validating license...',
                deactivating: 'Deactivating license...',
                success: 'Success!',
                error: 'An error occurred.',
                confirm_deactivate: 'Are you sure you want to deactivate your license?'
            }
        };
    }

})(jQuery);
