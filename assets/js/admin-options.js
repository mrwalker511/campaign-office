/**
 * CampaignPress Theme Options - Admin JavaScript
 *
 * Interactive features for the theme options panel
 *
 * @package CampaignPress
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        initColorPickers();
        initColorSchemePresets();
        initFormValidation();
        initUnsavedChanges();
        initResetButton();
        initTooltips();
    });

    /**
     * Initialize Color Pickers
     */
    function initColorPickers() {
        if (typeof $.fn.wpColorPicker !== 'undefined') {
            $('.cp-color-picker').wpColorPicker({
                change: function(event, ui) {
                    // Update color scheme to custom when manually changing colors
                    $('#campaignpress_color_scheme').val('custom');
                }
            });
        }
    }

    /**
     * Handle Color Scheme Preset Changes
     */
    function initColorSchemePresets() {
        $('#campaignpress_color_scheme').on('change', function() {
            const scheme = $(this).val();
            const colorPresets = {
                'democrat-blue': {
                    primary: '#0066cc',
                    secondary: '#333333',
                    accent: '#ff6b35'
                },
                'republican-red': {
                    primary: '#e81b23',
                    secondary: '#333333',
                    accent: '#0052a3'
                },
                'independent-purple': {
                    primary: '#6b46c1',
                    secondary: '#2d3748',
                    accent: '#ed8936'
                },
                'green-party': {
                    primary: '#17a05d',
                    secondary: '#2d3748',
                    accent: '#fbb040'
                },
                'neutral': {
                    primary: '#4a5568',
                    secondary: '#2d3748',
                    accent: '#3182ce'
                }
            };

            if (scheme !== 'custom' && colorPresets[scheme]) {
                const colors = colorPresets[scheme];

                // Update color picker values
                $('#campaignpress_primary_color').wpColorPicker('color', colors.primary);
                $('#campaignpress_secondary_color').wpColorPicker('color', colors.secondary);
                $('#campaignpress_accent_color').wpColorPicker('color', colors.accent);
            }
        });
    }

    /**
     * Form Validation
     */
    function initFormValidation() {
        $('.campaignpress-form').on('submit', function(e) {
            let isValid = true;
            const errors = [];

            // Validate URLs
            $(this).find('input[type="url"]').each(function() {
                const value = $(this).val().trim();
                if (value && !isValidUrl(value)) {
                    isValid = false;
                    errors.push('Please enter a valid URL for ' + $(this).closest('tr').find('label').text());
                    $(this).css('border-color', '#d63638');
                } else {
                    $(this).css('border-color', '');
                }
            });

            // Validate email addresses
            $(this).find('input[type="email"]').each(function() {
                const value = $(this).val().trim();
                if (value && !isValidEmail(value)) {
                    isValid = false;
                    errors.push('Please enter a valid email address');
                    $(this).css('border-color', '#d63638');
                } else {
                    $(this).css('border-color', '');
                }
            });

            // Validate numeric fields
            $(this).find('input[type="number"]').each(function() {
                const value = parseInt($(this).val());
                const min = parseInt($(this).attr('min'));
                const max = parseInt($(this).attr('max'));

                if (value < min || value > max) {
                    isValid = false;
                    errors.push($(this).closest('tr').find('label').text() + ' must be between ' + min + ' and ' + max);
                    $(this).css('border-color', '#d63638');
                } else {
                    $(this).css('border-color', '');
                }
            });

            if (!isValid) {
                e.preventDefault();
                showNotice('error', errors.join('<br>'));
                $('html, body').animate({ scrollTop: 0 }, 300);
                return false;
            }

            // Show loading state
            $(this).addClass('is-loading');
        });
    }

    /**
     * Track Unsaved Changes
     */
    function initUnsavedChanges() {
        let formChanged = false;

        $('.campaignpress-form :input').on('change', function() {
            formChanged = true;
        });

        $(window).on('beforeunload', function(e) {
            if (formChanged) {
                const message = 'You have unsaved changes. Are you sure you want to leave this page?';
                e.returnValue = message;
                return message;
            }
        });

        $('.campaignpress-form').on('submit', function() {
            formChanged = false;
        });
    }

    /**
     * Reset to Defaults
     */
    function initResetButton() {
        $('.campaignpress-reset-btn').on('click', function(e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
                return;
            }

            // Reset to default values
            const defaults = {
                'campaignpress_primary_color': '#0066cc',
                'campaignpress_secondary_color': '#333333',
                'campaignpress_accent_color': '#ff6b35',
                'campaignpress_color_scheme': 'democrat-blue',
                'campaignpress_homepage_layout': 'classic-candidate',
                'campaignpress_layout': 'sidebar-right',
                'campaignpress_logo_width': 200,
                'campaignpress_font_size_base': 16,
                'campaignpress_heading_font': 'system-ui',
                'campaignpress_body_font': 'system-ui',
                'campaignpress_show_footer_widgets': 1,
                'campaignpress_enable_sticky_header': 1
            };

            $.each(defaults, function(name, value) {
                const $input = $('[name="' + name + '"]');

                if ($input.length) {
                    if ($input.attr('type') === 'checkbox') {
                        $input.prop('checked', value == 1);
                    } else if ($input.hasClass('cp-color-picker')) {
                        $input.wpColorPicker('color', value);
                    } else {
                        $input.val(value);
                    }
                }
            });

            // Clear text fields that should be empty by default
            $('input[type="url"], input[type="text"]').not('[name^="campaignpress_color"]').not('[name="campaignpress_logo_width"]').not('[name="campaignpress_font_size_base"]').val('');
            $('textarea').not('.wp-editor-area').val('');

            showNotice('success', 'Settings have been reset to defaults. Click "Save Changes" to apply.');
        });
    }

    /**
     * Initialize Tooltips
     */
    function initTooltips() {
        // Add tooltips to form fields with descriptions
        $('.form-table .description').each(function() {
            $(this).closest('td').find('input, select, textarea').first().attr('title', $(this).text());
        });
    }

    /**
     * Validate URL
     */
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }

    /**
     * Validate Email
     */
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    /**
     * Show Notice
     */
    function showNotice(type, message) {
        const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
        const notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p></div>');

        $('.campaignpress-options-wrap').prepend(notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Live Preview (for future enhancement)
     */
    function initLivePreview() {
        // This could be enhanced to show a live preview of color/font changes
        // Similar to the WordPress Customizer
    }

})(jQuery);
