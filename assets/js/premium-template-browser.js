/**
 * Premium Template Browser JavaScript
 *
 * Handles template browsing, filtering, preview, and application
 *
 * @package CampaignPress
 * @subpackage Premium/DesignStudio
 * @since 2.0.0
 */

(function ($) {
    'use strict';

    var TemplateBrowser = {
        currentFilter: {
            category: 'all',
            campaign_type: 'all',
            difficulty: 'all',
            search: ''
        },

        templates: [],
        filteredTemplates: [],

        /**
         * Initialize template browser
         */
        init: function () {
            this.loadTemplates();
            this.bindEvents();
        },

        /**
         * Bind UI events
         */
        bindEvents: function () {
            var self = this;

            // Filter changes
            $('#cp-filter-category').on('change', function () {
                self.currentFilter.category = $(this).val();
                self.applyFilters();
            });

            $('#cp-filter-campaign-type').on('change', function () {
                self.currentFilter.campaign_type = $(this).val();
                self.applyFilters();
            });

            $('#cp-filter-difficulty').on('change', function () {
                self.currentFilter.difficulty = $(this).val();
                self.applyFilters();
            });

            $('#cp-template-search').on('input', function () {
                self.currentFilter.search = $(this).val().toLowerCase();
                self.applyFilters();
            });

            // Template actions
            $(document).on('click', '.cp-template-card', function (e) {
                if (!$(e.target).is('button, .button')) {
                    var templateKey = $(this).data('template-key');
                    self.showPreview(templateKey);
                }
            });

            $(document).on('click', '.cp-btn-preview', function (e) {
                e.stopPropagation();
                var templateKey = $(this).closest('.cp-template-card').data('template-key');
                self.showPreview(templateKey);
            });

            $(document).on('click', '.cp-btn-apply', function (e) {
                e.stopPropagation();
                var templateKey = $(this).closest('.cp-template-card').data('template-key');
                self.applyTemplate(templateKey);
            });

            // Modal actions
            $(document).on('click', '.cp-modal-close, .cp-modal-cancel', function () {
                self.closeModal();
            });

            $(document).on('click', '.cp-modal-apply', function () {
                var templateKey = $('#cp-template-modal').data('template-key');
                self.closeModal();
                self.applyTemplate(templateKey);
            });

            // Close modal on escape
            $(document).on('keyup', function (e) {
                if (e.key === 'Escape' && $('#cp-template-modal').hasClass('active')) {
                    self.closeModal();
                }
            });

            // Close modal on backdrop click
            $('#cp-template-modal').on('click', function (e) {
                if ($(e.target).is('#cp-template-modal')) {
                    self.closeModal();
                }
            });
        },

        /**
         * Load templates via AJAX
         */
        loadTemplates: function () {
            var self = this;

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'cp_get_premium_templates',
                    nonce: cpPremiumTemplates.nonce
                },
                beforeSend: function () {
                    $('#cp-template-grid').html('<div class="cp-template-loading active"><div class="cp-spinner"></div><p>Loading templates...</p></div>');
                },
                success: function (response) {
                    if (response.success && response.data && response.data.templates) {
                        self.templates = response.data.templates;
                        self.filteredTemplates = self.templates;
                        self.renderTemplates();
                    } else {
                        self.showError('Failed to load templates.');
                    }
                },
                error: function () {
                    self.showError('Error connecting to server.');
                }
            });
        },

        /**
         * Apply current filters
         */
        applyFilters: function () {
            var self = this;

            this.filteredTemplates = this.templates.filter(function (template) {
                // Category filter
                if (self.currentFilter.category !== 'all' && template.category !== self.currentFilter.category) {
                    return false;
                }

                // Campaign type filter
                if (self.currentFilter.campaign_type !== 'all' && template.campaign_type !== 'all' && template.campaign_type !== self.currentFilter.campaign_type) {
                    return false;
                }

                // Difficulty filter
                if (self.currentFilter.difficulty !== 'all' && template.difficulty !== self.currentFilter.difficulty) {
                    return false;
                }

                // Search filter
                if (self.currentFilter.search) {
                    var searchLower = self.currentFilter.search;
                    var titleMatch = template.template_name.toLowerCase().indexOf(searchLower) !== -1;
                    var descMatch = (template.template_description || '').toLowerCase().indexOf(searchLower) !== -1;
                    var tagsMatch = (template.tags || '').toLowerCase().indexOf(searchLower) !== -1;

                    if (!titleMatch && !descMatch && !tagsMatch) {
                        return false;
                    }
                }

                return true;
            });

            this.renderTemplates();
        },

        /**
         * Render templates in grid
         */
        renderTemplates: function () {
            var self = this;
            var $grid = $('#cp-template-grid');

            if (this.filteredTemplates.length === 0) {
                $grid.html('<div class="cp-no-templates"><span class="dashicons dashicons-format-gallery"></span><h3>No templates found</h3><p>Try adjusting your filters or search terms.</p></div>');
                return;
            }

            var html = '';
            this.filteredTemplates.forEach(function (template) {
                html += self.renderTemplateCard(template);
            });

            $grid.html(html);
        },

        /**
         * Render single template card
         */
        renderTemplateCard: function (template) {
            var featuredClass = template.featured ? ' featured' : '';
            var difficultyClass = ' ' + (template.difficulty || 'beginner');

            return '<div class="cp-template-card' + featuredClass + '" data-template-key="' + template.template_key + '">' +
                '<div class="cp-template-preview">' +
                (template.preview_image ?
                    '<img src="' + template.preview_image + '" alt="' + this.escapeHtml(template.template_name) + '">' :
                    '<span class="dashicons dashicons-format-gallery placeholder"></span>') +
                '</div>' +
                '<div class="cp-template-info">' +
                '<h3 class="cp-template-title">' + this.escapeHtml(template.template_name) + '</h3>' +
                '<p class="cp-template-description">' + this.escapeHtml(template.template_description || '') + '</p>' +
                '<div class="cp-template-meta">' +
                '<span class="cp-template-badge cp-badge-difficulty' + difficultyClass + '">' + this.escapeHtml(template.difficulty || 'beginner') + '</span>' +
                (template.setup_time ? '<span class="cp-template-badge cp-badge-setup-time">' + this.escapeHtml(template.setup_time) + '</span>' : '') +
                '</div>' +
                '</div>' +
                '<div class="cp-template-actions">' +
                '<button class="button cp-btn-preview">Preview</button>' +
                '<button class="button button-primary cp-btn-apply">Apply Template</button>' +
                '</div>' +
                '</div>';
        },

        /**
         * Show template preview modal
         */
        showPreview: function (templateKey) {
            var template = this.templates.find(function (t) {
                return t.template_key === templateKey;
            });

            if (!template) {
                return;
            }

            var $modal = $('#cp-template-modal');

            $modal.find('.cp-modal-title').text(template.template_name);
            $modal.find('.cp-modal-description').html('<p>' + this.escapeHtml(template.template_description || '') + '</p>');

            if (template.preview_image) {
                $modal.find('.cp-modal-preview').html('<img src="' + template.preview_image + '" alt="' + this.escapeHtml(template.template_name) + '" class="cp-modal-preview">');
            } else {
                $modal.find('.cp-modal-preview').html('<div style="text-align:center; padding: 60px;"><span class="dashicons dashicons-format-gallery" style="font-size: 120px; color: #ccc;"></span></div>');
            }

            $modal.data('template-key', templateKey);
            $modal.addClass('active');
        },

        /**
         * Close preview modal
         */
        closeModal: function () {
            $('#cp-template-modal').removeClass('active');
        },

        /**
         * Apply template to current page
         */
        applyTemplate: function (templateKey) {
            var self = this;

            if (!confirm('This will replace your current design. Are you sure you want to continue?')) {
                return;
            }

            var template = this.templates.find(function (t) {
                return t.template_key === templateKey;
            });

            if (!template) {
                alert('Template not found.');
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cp_apply_premium_template',
                    nonce: cpPremiumTemplates.nonce,
                    template_key: templateKey,
                    post_id: cpPremiumTemplates.postId || 0
                },
                beforeSend: function () {
                    self.showLoading('Applying template...');
                },
                success: function (response) {
                    self.hideLoading();

                    if (response.success) {
                        alert('Template applied successfully!');

                        // Reload Design Studio if it exists
                        if (typeof window.CampaignDesignStudio !== 'undefined') {
                            window.location.reload();
                        }
                    } else {
                        alert(response.data.message || 'Failed to apply template.');
                    }
                },
                error: function () {
                    self.hideLoading();
                    alert('Error connecting to server.');
                }
            });
        },

        /**
         * Show loading overlay
         */
        showLoading: function (message) {
            var $overlay = $('<div class="cp-loading-overlay">' +
                '<div class="cp-loading-content">' +
                '<div class="cp-spinner"></div>' +
                '<p>' + this.escapeHtml(message || 'Loading...') + '</p>' +
                '</div>' +
                '</div>');

            $('body').append($overlay);
        },

        /**
         * Hide loading overlay
         */
        hideLoading: function () {
            $('.cp-loading-overlay').remove();
        },

        /**
         * Show error message
         */
        showError: function (message) {
            $('#cp-template-grid').html(
                '<div class="cp-no-templates">' +
                '<span class="dashicons dashicons-warning" style="color: #dc3232;"></span>' +
                '<h3>Error</h3>' +
                '<p>' + this.escapeHtml(message) + '</p>' +
                '</div>'
            );
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function (text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function (m) { return map[m]; });
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        // Ensure localization object exists
        if (typeof cpPremiumTemplates === 'undefined') {
            // Localization not loaded - skip initialization
            return;
        }

        if ($('#cp-template-grid').length) {
            TemplateBrowser.init();
        }
    });

    // Export to window for external access
    window.CPTemplateBrowser = TemplateBrowser;

})(jQuery);
