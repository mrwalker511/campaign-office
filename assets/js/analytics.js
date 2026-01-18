/**
 * CampaignPress Analytics JavaScript
 * Handles data visualization, chart rendering, and analytics interactions
 * 
 * @package CampaignPress
 * @subpackage Analytics
 */

(function ($) {
    'use strict';

    const CampaignPressAnalytics = {
        charts: {},
        currentDateRange: 30,

        /**
         * Initialize analytics
         */
        init: function () {
            this.bindEvents();
            this.loadDashboardData();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            const self = this;

            // Date range selector
            $('#analytics-date-range').on('change', function () {
                self.currentDateRange = parseInt($(this).val());
                self.loadDashboardData();
            });

            // Export buttons
            $('#export-csv').on('click', function (e) {
                e.preventDefault();
                self.exportData('csv');
            });

            $('#export-pdf').on('click', function (e) {
                e.preventDefault();
                self.exportData('pdf');
            });

            // Report links
            $('.report-link').on('click', function (e) {
                e.preventDefault();
                const reportType = $(this).data('report');
                self.loadReport(reportType);
            });

            // Metric actions
            $('#add-new-metric').on('click', function (e) {
                e.preventDefault();
                self.showAddMetricModal();
            });

            $('#set-goals').on('click', function (e) {
                e.preventDefault();
                self.showGoalsModal();
            });
        },

        /**
         * Load dashboard data
         */
        loadDashboardData: function () {
            const self = this;

            $('#analytics-content').html('<div class="loading-spinner"><span class="spinner is-active"></span><p>' + campaignpressAnalytics.i18n.loading + '</p></div>');

            $.ajax({
                url: campaignpressAnalytics.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'campaignpress_get_analytics_data',
                    nonce: campaignpressAnalytics.nonce,
                    date_range: self.currentDateRange,
                    report_type: 'dashboard'
                },
                success: function (response) {
                    if (response.success) {
                        self.renderDashboard(response.data);
                    } else {
                        self.showError(response.data.message || campaignpressAnalytics.i18n.error);
                    }
                },
                error: function () {
                    self.showError(campaignpressAnalytics.i18n.error);
                }
            });
        },

        /**
         * Render dashboard
         */
        renderDashboard: function (data) {
            const metrics = data.key_metrics || {};
            const trends = data.trends || {};

            const html = `
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <h3>Total Raised</h3>
                        <div class="metric-value">$${this.formatNumber(metrics.total_raised || 0)}</div>
                        <div class="metric-change positive">
                            Campaign Total
                        </div>
                    </div>

                    <div class="analytics-card">
                        <h3>Total Volunteers</h3>
                        <div class="metric-value">${this.formatNumber(metrics.total_volunteers || 0)}</div>
                        <div class="metric-change positive">
                            ${this.formatNumber(metrics.volunteer_hours || 0)} Hours Logged
                        </div>
                    </div>

                    <div class="analytics-card">
                        <h3>Event Attendance</h3>
                        <div class="metric-value">${this.formatNumber(metrics.event_attendance || 0)}</div>
                        <div class="metric-change positive">
                            ${metrics.total_events || 0} Events
                        </div>
                    </div>

                    <div class="analytics-card">
                        <h3>Total Contacts</h3>
                        <div class="metric-value">${this.formatNumber(metrics.total_contacts || 0)}</div>
                        <div class="metric-change positive">
                            Avg Engagement: ${Math.round(metrics.engagement_score || 0)}%
                        </div>
                    </div>
                </div>

                <div class="charts-grid">
                    <div class="chart-container">
                        <h3>Fundraising Trend</h3>
                        <canvas id="fundraising-chart"></canvas>
                    </div>

                    <div class="chart-container">
                        <h3>Volunteer Growth</h3>
                        <canvas id="volunteer-chart"></canvas>
                    </div>

                    <div class="chart-container">
                        <h3>Event Participation</h3>
                        <canvas id="event-chart"></canvas>
                    </div>

                    <div class="chart-container">
                        <h3>Engagement Overview</h3>
                        <canvas id="engagement-chart"></canvas>
                    </div>
                </div>
            `;

            $('#analytics-content').html(html);

            // Render charts with actual data
            this.renderFundraisingChart(trends.fundraising || []);
            this.renderVolunteerChart(trends.volunteers || []);
            this.renderEventChart(trends.events || []);
            this.renderEngagementChart(trends.engagement || {});
        },

        /**
         * Render fundraising chart
         */
        renderFundraisingChart: function (data) {
            const ctx = document.getElementById('fundraising-chart');
            if (!ctx) return;

            if (this.charts.fundraising) {
                this.charts.fundraising.destroy();
            }

            this.charts.fundraising = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                        label: 'Donations',
                        data: data.map(d => d.amount),
                        borderColor: '#0053c3',
                        backgroundColor: 'rgba(0, 83, 195, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        },

        /**
         * Render volunteer chart
         */
        renderVolunteerChart: function (data) {
            const ctx = document.getElementById('volunteer-chart');
            if (!ctx) return;

            if (this.charts.volunteer) {
                this.charts.volunteer.destroy();
            }

            this.charts.volunteer = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                        label: 'New Volunteers',
                        data: data.map(d => d.count),
                        backgroundColor: '#ff8800',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        },

        /**
         * Render event chart
         */
        renderEventChart: function (data) {
            const ctx = document.getElementById('event-chart');
            if (!ctx) return;

            if (this.charts.event) {
                this.charts.event.destroy();
            }

            this.charts.event = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => d.date),
                    datasets: [{
                        label: 'Attendance',
                        data: data.map(d => d.attendance),
                        borderColor: '#14b8a6',
                        backgroundColor: 'rgba(20, 184, 166, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },

        /**
         * Render engagement chart
         */
        renderEngagementChart: function (data) {
            const ctx = document.getElementById('engagement-chart');
            if (!ctx) return;

            if (this.charts.engagement) {
                this.charts.engagement.destroy();
            }

            this.charts.engagement = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Email Opens', 'Website Visits', 'Social Shares', 'Event RSVPs'],
                    datasets: [{
                        data: [
                            data.email_opens || 0,
                            data.website_visits || 0,
                            data.social_shares || 0,
                            data.event_rsvps || 0
                        ],
                        backgroundColor: [
                            '#0053c3',
                            '#ff8800',
                            '#14b8a6',
                            '#f43f5e'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },

        /**
         * Load specific report
         */
        loadReport: function (reportType) {
            const self = this;

            $('#report-content').html('<div class="loading-spinner"><span class="spinner is-active"></span><p>' + campaignpressAnalytics.i18n.loading + '</p></div>');

            $.ajax({
                url: campaignpressAnalytics.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'campaignpress_get_analytics_data',
                    nonce: campaignpressAnalytics.nonce,
                    date_range: self.currentDateRange,
                    report_type: reportType
                },
                success: function (response) {
                    if (response.success) {
                        self.renderReport(reportType, response.data);
                    } else {
                        self.showError(response.data.message || campaignpressAnalytics.i18n.error);
                    }
                },
                error: function () {
                    self.showError(campaignpressAnalytics.i18n.error);
                }
            });
        },

        /**
         * Render report
         */
        renderReport: function (type, data) {
            // Report rendering logic based on type
            const html = `
                <div class="report-header">
                    <h2>${type.charAt(0).toUpperCase() + type.slice(1)} Report</h2>
                    <button class="button button-primary" onclick="window.print()">Print Report</button>
                </div>
                <div class="report-content">
                    <p>Report data for ${type} will be displayed here.</p>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;

            $('#report-content').html(html);
        },

        /**
         * Export data
         */
        exportData: function (format) {
            const self = this;

            $.ajax({
                url: campaignpressAnalytics.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'campaignpress_export_analytics',
                    nonce: campaignpressAnalytics.nonce,
                    format: format,
                    date_range: self.currentDateRange
                },
                success: function (response) {
                    if (response.success && response.data.file_url) {
                        window.location.href = response.data.file_url;
                        self.showSuccess(campaignpressAnalytics.i18n.exportSuccess);
                    } else {
                        self.showError(response.data.message || campaignpressAnalytics.i18n.exportError);
                    }
                },
                error: function () {
                    self.showError(campaignpressAnalytics.i18n.exportError);
                }
            });
        },

        /**
         * Show add metric modal
         */
        showAddMetricModal: function () {
            const self = this;
            const $modal = $('#add-metric-modal');

            // Reset form
            $('#add-metric-form')[0].reset();

            // Show modal
            $modal.fadeIn(200);

            // Bind modal events
            $modal.find('.cp-modal-close, .cp-modal-cancel, .cp-modal-overlay').off('click').on('click', function (e) {
                if (e.target === this) {
                    self.hideModal($modal);
                }
            });

            // Handle save
            $('#save-metric-btn').off('click').on('click', function () {
                self.saveMetric();
            });

            // Handle form submit (Enter key)
            $('#add-metric-form').off('submit').on('submit', function (e) {
                e.preventDefault();
                self.saveMetric();
            });

            // Close on Escape key
            $(document).on('keydown.metricModal', function (e) {
                if (e.key === 'Escape') {
                    self.hideModal($modal);
                }
            });
        },

        /**
         * Hide modal
         */
        hideModal: function ($modal) {
            $modal.fadeOut(200);
            $(document).off('keydown.metricModal');
        },

        /**
         * Save metric via AJAX
         */
        saveMetric: function () {
            const self = this;
            const $form = $('#add-metric-form');
            const $saveBtn = $('#save-metric-btn');

            // Validate form
            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }

            // Get form data
            const formData = {
                action: 'campaignpress_add_metric',
                nonce: campaignpressAnalytics.nonce,
                metric_key: $('#metric_key').val(),
                metric_name: $('#metric_name').val(),
                metric_type: $('#metric_type').val(),
                description: $('#metric_description').val(),
                unit: $('#metric_unit').val(),
                goal_value: $('#goal_value').val() || 0,
                target_value: $('#target_value').val() || 0,
                alert_threshold: $('#alert_threshold').val() || 0
            };

            // Disable button and show loading
            $saveBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: campaignpressAnalytics.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        self.hideModal($('#add-metric-modal'));
                        self.showSuccess(response.data.message || 'Metric added successfully');
                        // Reload dashboard to show new metric
                        self.loadDashboardData();
                    } else {
                        self.showError(response.data.message || 'Failed to add metric');
                    }
                },
                error: function () {
                    self.showError('Failed to add metric. Please try again.');
                },
                complete: function () {
                    $saveBtn.prop('disabled', false).text('Add Metric');
                }
            });
        },

        /**
         * Show goals modal
         */
        showGoalsModal: function () {
            const self = this;
            const $modal = $('#goals-modal');

            // Load current goals
            this.loadCurrentGoals();

            // Show modal
            $modal.fadeIn(200);

            // Bind modal events
            $modal.find('.cp-modal-close, .cp-modal-cancel, .cp-modal-overlay').off('click').on('click', function (e) {
                if (e.target === this) {
                    self.hideGoalsModal($modal);
                }
            });

            // Handle save
            $('#save-goals-btn').off('click').on('click', function () {
                self.saveGoals();
            });

            // Handle form submit (Enter key)
            $('#goals-form').off('submit').on('submit', function (e) {
                e.preventDefault();
                self.saveGoals();
            });

            // Close on Escape key
            $(document).on('keydown.goalsModal', function (e) {
                if (e.key === 'Escape') {
                    self.hideGoalsModal($modal);
                }
            });
        },

        /**
         * Hide goals modal
         */
        hideGoalsModal: function ($modal) {
            $modal.fadeOut(200);
            $(document).off('keydown.goalsModal');
        },

        /**
         * Load current goals
         */
        loadCurrentGoals: function () {
            $.ajax({
                url: campaignpressAnalytics.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'campaignpress_get_goals',
                    nonce: campaignpressAnalytics.nonce
                },
                success: function (response) {
                    if (response.success && response.data.goals) {
                        const goals = response.data.goals;
                        $('#goal_fundraising').val(goals.fundraising || 0);
                        $('#goal_volunteers').val(goals.volunteers || 0);
                        $('#goal_events').val(goals.events || 0);
                        $('#goal_contacts').val(goals.contacts || 0);
                    }
                }
            });
        },

        /**
         * Save goals via AJAX
         */
        saveGoals: function () {
            const self = this;
            const $saveBtn = $('#save-goals-btn');

            const formData = {
                action: 'campaignpress_save_goals',
                nonce: campaignpressAnalytics.nonce,
                fundraising: $('#goal_fundraising').val() || 0,
                volunteers: $('#goal_volunteers').val() || 0,
                events: $('#goal_events').val() || 0,
                contacts: $('#goal_contacts').val() || 0
            };

            // Disable button and show loading
            $saveBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: campaignpressAnalytics.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        self.hideGoalsModal($('#goals-modal'));
                        self.showSuccess(response.data.message || 'Goals saved successfully');
                        // Reload dashboard to reflect new goals
                        self.loadDashboardData();
                    } else {
                        self.showError(response.data.message || 'Failed to save goals');
                    }
                },
                error: function () {
                    self.showError('Failed to save goals. Please try again.');
                },
                complete: function () {
                    $saveBtn.prop('disabled', false).text('Save Goals');
                }
            });
        },

        /**
         * Format number with commas
         */
        formatNumber: function (num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },

        /**
         * Show error message
         */
        showError: function (message) {
            $('#analytics-content, #report-content, #metrics-content').html(
                '<div class="notice notice-error"><p>' + message + '</p></div>'
            );
        },

        /**
         * Show success message
         */
        showSuccess: function (message) {
            const notice = $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>');
            $('.wrap').prepend(notice);
            setTimeout(function () {
                notice.fadeOut(function () {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        if ($('.campaignpress-analytics-dashboard, .campaignpress-metrics-dashboard, .campaignpress-reports').length) {
            CampaignPressAnalytics.init();
        }
    });

})(jQuery);
