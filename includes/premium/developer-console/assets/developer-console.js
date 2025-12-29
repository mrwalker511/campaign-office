/**
 * Developer Console JavaScript
 *
 * Handles all interactive functionality for the developer console
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

(function ($) {
    'use strict';

    var DevConsole = {
        init: function () {
            this.bindEvents();
            this.loadDashboard();
        },

        bindEvents: function () {
            // Tab switching
            $('.cp-dev-tabs .nav-tab').on('click', this.switchTab);

            // System health
            $('#refresh-health-btn').on('click', this.loadSystemHealth);

            // Database
            $('#execute-query-btn').on('click', this.executeQuery);
            $('#save-query-btn').on('click', this.saveQuery);
            $(document).on('click', '.load-saved-query', this.loadSavedQuery);
            $(document).on('click', '.view-table-structure', this.viewTableStructure);
            $(document).on('click', '.preview-table', this.previewTable);
            $(document).on('click', '.optimize-table', this.optimizeTable);
            $('#run-migration-btn').on('click', this.runContactMigration);

            // API Tester
            $('#test-api-btn').on('click', this.testAPI);
            $(document).on('click', '.cp-api-endpoint', this.selectAPIEndpoint);

            // Activity Logs
            $('#refresh-logs-btn').on('click', this.loadActivityLogs);
            $('#log-category-filter').on('change', this.loadActivityLogs);

            // Data Export
            $('#export-data-btn').on('click', this.exportData);
            $('#download-export-btn').on('click', this.downloadExport);

            // Users
            $('#refresh-users-btn').on('click', this.loadUsers);

            // Settings
            $('#security-settings-form').on('submit', this.saveSecuritySettings);
        },

        switchTab: function (e) {
            e.preventDefault();
            var tab = $(this).data('tab');

            // Update tab navigation
            $('.cp-dev-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Update tab content
            $('.cp-dev-tab-content').removeClass('cp-dev-tab-active');
            $('#tab-' + tab).addClass('cp-dev-tab-active');

            // Load tab-specific content
            switch (tab) {
                case 'system-health':
                    DevConsole.loadSystemHealth();
                    break;
                case 'database':
                    DevConsole.loadDatabaseTables();
                    DevConsole.loadSavedQueries();
                    DevConsole.loadCampaignPressStats();
                    break;
                case 'api-tester':
                    DevConsole.loadAPIEndpoints();
                    DevConsole.loadAPIStats();
                    break;
                case 'activity-logs':
                    DevConsole.loadActivityLogs();
                    break;
                case 'data-export':
                    DevConsole.loadExportHistory();
                    break;
                case 'users':
                    DevConsole.loadUsers();
                    break;
            }
        },

        loadDashboard: function () {
            this.loadSystemOverview();
            this.loadQuickStats();
            this.loadRecentActivity();
        },

        loadSystemOverview: function () {
            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_system_health',
                    nonce: cpDevConsole.nonce
                },
                success: function (response) {
                    if (response.success) {
                        var html = '<div class="cp-stat-item">';
                        html += '<span class="cp-stat-label">WordPress Version</span>';
                        html += '<span class="cp-stat-value">' + response.data.wordpress.version + '</span>';
                        html += '</div>';

                        html += '<div class="cp-stat-item">';
                        html += '<span class="cp-stat-label">PHP Version</span>';
                        html += '<span class="cp-stat-value">' + response.data.server.php_version + '</span>';
                        html += '</div>';

                        html += '<div class="cp-stat-item">';
                        html += '<span class="cp-stat-label">Database</span>';
                        html += '<span class="cp-stat-value">' + response.data.database.version + '</span>';
                        html += '</div>';

                        html += '<div class="cp-stat-item">';
                        html += '<span class="cp-stat-label">Memory Usage</span>';
                        html += '<span class="cp-stat-value">' + response.data.server.current_memory_usage + '</span>';
                        html += '</div>';

                        $('#system-overview-content').html(html);
                    }
                }
            });
        },

        loadQuickStats: function () {
            // Load CampaignPress stats
            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_execute_query',
                    nonce: cpDevConsole.nonce,
                    query: 'SELECT COUNT(*) as count FROM ' + cpDevConsole.tablePrefix + 'cp_crm_contacts'
                },
                success: function (response) {
                    if (response.success && response.results && response.results.length > 0) {
                        var html = '<div class="cp-stat-item">';
                        html += '<span class="cp-stat-label">Total CRM Contacts</span>';
                        html += '<span class="cp-stat-value">' + response.results[0].count + '</span>';
                        html += '</div>';
                        $('#quick-stats-content').html(html);
                    }
                }
            });
        },

        loadRecentActivity: function () {
            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_get_logs',
                    nonce: cpDevConsole.nonce,
                    limit: 5
                },
                success: function (response) {
                    if (response.success) {
                        DevConsole.renderActivityLogs(response.data.logs, '#recent-activity-content');
                    }
                }
            });
        },

        loadSystemHealth: function () {
            $('#system-health-content').html('<div class="cp-loading">Loading system health data...</div>');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_system_health',
                    nonce: cpDevConsole.nonce
                },
                success: function (response) {
                    if (response.success) {
                        DevConsole.renderSystemHealth(response.data);
                    } else {
                        $('#system-health-content').html('<div class="cp-dev-notice error">' + response.data.message + '</div>');
                    }
                },
                error: function () {
                    $('#system-health-content').html('<div class="cp-dev-notice error">Failed to load system health data</div>');
                }
            });
        },

        renderSystemHealth: function (data) {
            var html = '<div class="cp-dev-grid">';

            // WordPress Info
            html += '<div class="cp-dev-card">';
            html += '<h3>WordPress</h3>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Version</span><span class="cp-stat-value">' + data.wordpress.version + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Memory Limit</span><span class="cp-stat-value">' + data.wordpress.memory_limit + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Debug Mode</span><span class="cp-stat-value">' + (data.wordpress.debug_mode ? 'Enabled' : 'Disabled') + '</span></div>';
            html += '</div>';

            // Server Info
            html += '<div class="cp-dev-card">';
            html += '<h3>Server</h3>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">PHP Version</span><span class="cp-stat-value">' + data.server.php_version + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Memory Usage</span><span class="cp-stat-value">' + data.server.current_memory_usage + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Peak Memory</span><span class="cp-stat-value">' + data.server.peak_memory_usage + '</span></div>';
            html += '</div>';

            // Database Info
            html += '<div class="cp-dev-card">';
            html += '<h3>Database</h3>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Version</span><span class="cp-stat-value">' + data.database.version + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Total Size</span><span class="cp-stat-value">' + data.database.total_size + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Tables</span><span class="cp-stat-value">' + data.database.table_count + '</span></div>';
            html += '</div>';

            // Performance
            html += '<div class="cp-dev-card">';
            html += '<h3>Performance</h3>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Total Queries</span><span class="cp-stat-value">' + data.performance.total_queries + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Page Gen Time</span><span class="cp-stat-value">' + data.performance.page_generation_time + '</span></div>';
            html += '<div class="cp-stat-item"><span class="cp-stat-label">Cache</span><span class="cp-stat-value">' + (data.performance.cache.enabled ? 'Enabled' : 'Disabled') + '</span></div>';
            html += '</div>';

            html += '</div>';

            $('#system-health-content').html(html);
        },

        executeQuery: function () {
            var query = $('#db-query').val().trim();

            if (!query) {
                alert('Please enter a query');
                return;
            }

            var confirmed = $('#query-confirm-dangerous').is(':checked');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_execute_query',
                    nonce: cpDevConsole.nonce,
                    query: query,
                    confirmed: confirmed
                },
                success: function (response) {
                    if (response.requires_confirmation) {
                        if (confirm(response.warning + '\n\n' + response.message)) {
                            $('#query-confirm-dangerous').prop('checked', true);
                            DevConsole.executeQuery();
                        }
                    } else if (response.success) {
                        DevConsole.renderQueryResult(response);
                    } else {
                        alert('Query failed: ' + response.message);
                    }
                },
                error: function () {
                    alert('Failed to execute query');
                }
            });
        },

        renderQueryResult: function (data) {
            var html = '<div class="cp-result-meta">';
            html += '<strong>Query Type:</strong> ' + data.query_type + ' | ';
            html += '<strong>Execution Time:</strong> ' + data.execution_time + 's | ';

            if (data.affected_rows !== undefined) {
                html += '<strong>Affected Rows:</strong> ' + data.affected_rows;
            } else if (data.row_count !== undefined) {
                html += '<strong>Rows:</strong> ' + data.row_count;
            }

            html += '</div>';

            if (data.results && data.results.length > 0) {
                html += '<div class="cp-dev-table-wrapper">';
                html += '<table class="cp-result-table">';
                html += '<thead><tr>';

                data.columns.forEach(function (col) {
                    html += '<th>' + col + '</th>';
                });

                html += '</tr></thead><tbody>';

                data.results.forEach(function (row) {
                    html += '<tr>';
                    data.columns.forEach(function (col) {
                        html += '<td>' + (row[col] !== null ? row[col] : '<em>NULL</em>') + '</td>';
                    });
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
            } else if (data.query_type === 'SELECT') {
                html += '<p>No results found.</p>';
            } else {
                html += '<p>Query executed successfully.</p>';
            }

            $('#query-result').html(html);
            $('#query-result-container').show();
        },

        loadDatabaseTables: function () {
            var $container = $('#database-tables-content');
            $container.html('<div class="cp-loading">Loading database tables...</div>');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_get_tables',
                    nonce: cpDevConsole.nonce
                },
                success: function (response) {
                    if (response.success && response.data) {
                        var html = '<div class="cp-dev-table-wrapper">';
                        html += '<table class="cp-dev-table">';
                        html += '<thead><tr><th>Table Name</th><th>Rows</th><th>Size</th><th>Engine</th><th>Actions</th></tr></thead><tbody>';

                        response.data.forEach(function (table) {
                            var tableName = table.TABLE_NAME || table.table_name;
                            var tableRows = table.TABLE_ROWS || table.table_rows;
                            var engine = table.ENGINE || table.engine;
                            var sizeMg = table.size_mb || table.SIZE_MB || 0;

                            html += '<tr>';
                            html += '<td><code>' + tableName + '</code></td>';
                            html += '<td>' + (parseInt(tableRows) || 0).toLocaleString() + '</td>';
                            html += '<td>' + sizeMg + ' MB</td>';
                            html += '<td>' + engine + '</td>';
                            html += '<td class="cp-dev-table-actions">';
                            html += '<button class="button button-small preview-table" data-table="' + tableName + '">Preview</button>';
                            html += '<button class="button button-small view-table-structure" data-table="' + tableName + '">Structure</button>';
                            html += '<button class="button button-small optimize-table" data-table="' + tableName + '">Optimize</button>';
                            html += '</td></tr>';
                        });

                        html += '</tbody></table></div>';
                        $container.html(html);
                    } else {
                        $container.html('<div class="cp-dev-notice error">Failed to load tables</div>');
                    }
                }
            });
        },

        viewTableStructure: function () {
            var table = $(this).data('table');
            var $btn = $(this);
            $btn.prop('disabled', true).text('Loading...');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_get_table_structure',
                    nonce: cpDevConsole.nonce,
                    table: table
                },
                success: function (response) {
                    $btn.prop('disabled', false).text('Structure');
                    if (response.success) {
                        var html = '<h4>Structure: ' + table + '</h4>';
                        html += '<pre class="cp-result-json"><code>' + response.create_statement + '</code></pre>';

                        html += '<h5>Columns</h5><div class="cp-dev-table-wrapper"><table class="cp-result-table"><thead><tr>';
                        html += '<th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th>';
                        html += '</tr></thead><tbody>';

                        response.columns.forEach(function (col) {
                            html += '<tr><td>' + col.Field + '</td><td>' + col.Type + '</td><td>' + col.Null + '</td><td>' + col.Key + '</td><td>' + (col.Default || '') + '</td></tr>';
                        });
                        html += '</tbody></table></div>';

                        $('#query-result').html(html);
                        $('#query-result-container').show();
                        $('html, body').animate({ scrollTop: $("#query-result-container").offset().top - 100 }, 500);
                    } else {
                        alert('Failed: ' + response.message);
                    }
                }
            });
        },

        previewTable: function () {
            var table = $(this).data('table');
            var $btn = $(this);
            $btn.prop('disabled', true).text('Loading...');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_get_table_preview',
                    nonce: cpDevConsole.nonce,
                    table: table
                },
                success: function (response) {
                    $btn.prop('disabled', false).text('Preview');
                    if (response.success) {
                        DevConsole.renderQueryResult(response);
                        $('html, body').animate({ scrollTop: $("#query-result-container").offset().top - 100 }, 500);
                    } else {
                        alert('Failed: ' + response.message);
                    }
                }
            });
        },

        optimizeTable: function () {
            var table = $(this).data('table');
            var $btn = $(this);

            if (!confirm('Optimize table ' + table + '? This may take a moment.')) return;

            $btn.prop('disabled', true).text('Optimizing...');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_optimize_table',
                    nonce: cpDevConsole.nonce,
                    table: table
                },
                success: function (response) {
                    $btn.prop('disabled', false).text('Optimize');
                    if (response.success) {
                        alert(response.message);
                    } else {
                        alert('Optimization failed: ' + response.message);
                    }
                }
            });
        },

        loadSavedQueries: function () {
            $('#saved-queries-content').html('<div class="cp-loading">Loading saved queries...</div>');

            // Placeholder - would load from database
            $('#saved-queries-content').html('<p>No saved queries yet.</p>');
        },

        loadCampaignPressStats: function () {
            var $container = $('#cp-stats-content');
            $container.html('<div class="cp-loading">Loading statistics...</div>');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_get_cp_stats',
                    nonce: cpDevConsole.nonce
                },
                success: function (response) {
                    if (response.success && response.data) {
                        var html = '<div class="cp-stats-grid">';

                        // CRM
                        html += '<div class="cp-stat-card"><h4>CRM</h4>';
                        html += '<div class="cp-stat-item"><span class="cp-stat-label">Contacts</span><span class="cp-stat-value">' + response.data.crm.contacts + '</span></div>';
                        html += '<div class="cp-stat-item"><span class="cp-stat-label">Interactions</span><span class="cp-stat-value">' + response.data.crm.interactions + '</span></div>';
                        html += '</div>';

                        // FEC
                        html += '<div class="cp-stat-card"><h4>FEC Compliance</h4>';
                        html += '<div class="cp-stat-item"><span class="cp-stat-label">Donors</span><span class="cp-stat-value">' + response.data.fec.donors + '</span></div>';
                        html += '<div class="cp-stat-item"><span class="cp-stat-label">Contributions</span><span class="cp-stat-value">' + response.data.fec.contributions + '</span></div>';
                        html += '</div>';

                        // Content
                        html += '<div class="cp-stat-card"><h4>Content</h4>';
                        html += '<div class="cp-stat-item"><span class="cp-stat-label">Events</span><span class="cp-stat-value">' + response.data.content.events + '</span></div>';
                        html += '<div class="cp-stat-item"><span class="cp-stat-label">Volunteers</span><span class="cp-stat-value">' + response.data.content.volunteer_opportunities + '</span></div>';
                        html += '</div>';

                        html += '</div>';
                        $container.html(html);
                    }
                }
            });
        },

        runContactMigration: function () {
            if (!confirm('Run contact consolidation migration? This will merge duplicate contacts and link all module data to the master contact table.')) return;

            var $btn = $(this);
            $btn.prop('disabled', true).text('Migrating...');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_run_migration',
                    nonce: cpDevConsole.nonce
                },
                success: function (response) {
                    $btn.prop('disabled', false).text('Run Consolidation Migration');
                    if (response.success) {
                        var results = response.results;
                        var msg = 'Migration completed successfully!\n\n';
                        msg += 'Volunteers: ' + (results.volunteers.migrated || 0) + '\n';
                        msg += 'Event RSVPs: ' + (results.event_rsvps.migrated || 0) + '\n';
                        msg += 'CRM Contacts: ' + (results.crm_contacts.migrated || 0) + '\n';
                        msg += 'FEC Donors: ' + (results.fec_donors.migrated || 0) + '\n';
                        alert(msg);
                        DevConsole.loadDatabaseTables();
                        DevConsole.loadCampaignPressStats();
                    } else {
                        alert('Migration failed: ' + response.message);
                    }
                }
            });
        },

        testAPI: function () {
            var method = $('#api-method').val();
            var endpoint = $('#api-endpoint').val();
            var body = $('#api-body').val();

            var data = {
                action: 'cp_dev_test_api',
                nonce: cpDevConsole.nonce,
                method: method,
                endpoint: endpoint
            };

            if (body) {
                try {
                    data.body = JSON.parse(body);
                } catch (e) {
                    alert('Invalid JSON in request body');
                    return;
                }
            }

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: data,
                success: function (response) {
                    DevConsole.renderAPIResult(response);
                },
                error: function () {
                    alert('Failed to test API endpoint');
                }
            });
        },

        renderAPIResult: function (response) {
            var html = '<div class="cp-result-meta">';

            if (response.success) {
                html += '<strong>Status:</strong> ' + response.status_code + ' | ';
                html += '<strong>Time:</strong> ' + response.execution_time + 's';
                html += '</div>';

                html += '<h4>Response Body:</h4>';
                html += '<div class="cp-result-json">' + JSON.stringify(response.body, null, 2) + '</div>';

                html += '<h4>Request Details:</h4>';
                html += '<div class="cp-result-json">';
                html += '<strong>URL:</strong> ' + response.request_url + '\n';
                html += '<strong>Method:</strong> ' + response.request_method + '\n';
                html += '</div>';
            } else {
                html += '<strong>Status:</strong> Failed</div>';
                html += '<div class="cp-dev-notice error">' + response.message + '</div>';
            }

            $('#api-result').html(html);
            $('#api-result-container').show();
        },

        loadAPIEndpoints: function () {
            var endpoints = [
                { path: '/contacts', methods: ['GET', 'POST'], description: 'Manage CRM contacts' },
                { path: '/contacts/{id}', methods: ['GET', 'PUT', 'DELETE'], description: 'Individual contact' },
                { path: '/interactions', methods: ['GET', 'POST'], description: 'Manage interactions' },
                { path: '/walks', methods: ['GET', 'POST'], description: 'Canvassing walks' },
                { path: '/phone-calls', methods: ['GET', 'POST'], description: 'Phone banking calls' },
                { path: '/donors', methods: ['GET', 'POST'], description: 'FEC donors' },
                { path: '/contributions', methods: ['GET', 'POST'], description: 'FEC contributions' }
            ];

            var html = '';

            endpoints.forEach(function (endpoint) {
                html += '<div class="cp-api-endpoint" data-path="' + endpoint.path + '">';
                html += '<div class="cp-api-endpoint-path">' + endpoint.path + '</div>';
                html += '<div class="cp-api-endpoint-methods">';

                endpoint.methods.forEach(function (method) {
                    html += '<span class="cp-api-method ' + method + '">' + method + '</span>';
                });

                html += '</div>';
                html += '<div class="cp-api-endpoint-description">' + endpoint.description + '</div>';
                html += '</div>';
            });

            $('#api-endpoints-list').html(html);
        },

        loadAPIStats: function () {
            $('#api-stats-content').html('<div class="cp-stat-item"><span class="cp-stat-label">Total Requests</span><span class="cp-stat-value">0</span></div>');
        },

        selectAPIEndpoint: function () {
            var path = $(this).data('path');
            $('#api-endpoint').val(path);
        },

        loadActivityLogs: function () {
            var category = $('#log-category-filter').val() || 'all';

            $('#activity-logs-content').html('<div class="cp-loading">Loading activity logs...</div>');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_get_logs',
                    nonce: cpDevConsole.nonce,
                    category: category,
                    limit: 50
                },
                success: function (response) {
                    if (response.success) {
                        DevConsole.renderActivityLogs(response.data.logs, '#activity-logs-content');
                    }
                }
            });
        },

        renderActivityLogs: function (logs, container) {
            if (!logs || logs.length === 0) {
                $(container).html('<p>No activity logs found.</p>');
                return;
            }

            var html = '';

            logs.forEach(function (log) {
                html += '<div class="cp-log-item category-' + log.action_category + '">';
                html += '<div class="cp-log-header">';
                html += '<span class="cp-log-action cp-status-' + log.result_status + '">' + log.action_type + '</span>';
                html += '<span class="cp-log-timestamp">' + log.created_at + '</span>';
                html += '</div>';
                html += '<div class="cp-log-description">' + log.action_description + '</div>';

                if (log.action_details) {
                    html += '<div class="cp-log-details">' + log.action_details + '</div>';
                }

                if (log.error_message) {
                    html += '<div class="cp-dev-notice error" style="margin-top: 8px;">' + log.error_message + '</div>';
                }

                html += '</div>';
            });

            $(container).html(html);
        },

        exportData: function () {
            var exportType = $('#export-type').val();
            var format = $('#export-format').val();

            $('#export-data-btn').prop('disabled', true).text('Exporting...');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_export_data',
                    nonce: cpDevConsole.nonce,
                    export_type: exportType,
                    format: format
                },
                success: function (response) {
                    $('#export-data-btn').prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Export Data');

                    if (response.success) {
                        cpDevConsole.exportData = response;

                        var html = '<p><strong>Filename:</strong> ' + response.filename + '</p>';
                        html += '<p><strong>Size:</strong> ' + response.size + ' bytes</p>';
                        html += '<p><strong>Records:</strong> ' + response.records + '</p>';

                        $('#export-result').html(html);
                        $('#export-result-container').show();
                    } else {
                        alert('Export failed: ' + response.message);
                    }
                },
                error: function () {
                    $('#export-data-btn').prop('disabled', false).html('<span class="dashicons dashicons-download"></span> Export Data');
                    alert('Export failed');
                }
            });
        },

        downloadExport: function () {
            if (!cpDevConsole.exportData) {
                alert('No export data available');
                return;
            }

            var blob = new Blob([cpDevConsole.exportData.content], { type: cpDevConsole.exportData.mime_type });
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = cpDevConsole.exportData.filename;
            link.click();
        },

        loadExportHistory: function () {
            $('#export-history-content').html('<p>No recent exports.</p>');
        },

        loadUsers: function () {
            $('#users-content').html('<div class="cp-loading">Loading users...</div>');

            $.ajax({
                url: cpDevConsole.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_dev_manage_users',
                    nonce: cpDevConsole.nonce,
                    action_type: 'list'
                },
                success: function (response) {
                    if (response.success) {
                        DevConsole.renderUsers(response.data.users);
                    }
                }
            });
        },

        renderUsers: function (users) {
            if (!users || users.length === 0) {
                $('#users-content').html('<p>No users found.</p>');
                return;
            }

            var html = '';

            users.forEach(function (user) {
                html += '<div class="cp-user-item">';
                html += '<div class="cp-user-info">';
                html += '<div class="cp-user-name">' + user.login + '</div>';
                html += '<div class="cp-user-email">' + user.email + '</div>';
                html += '<div class="cp-user-roles">';

                user.roles.forEach(function (role) {
                    html += '<span class="cp-user-role">' + role + '</span>';
                });

                html += '</div></div></div>';
            });

            $('#users-content').html(html);
        },

        saveSecuritySettings: function (e) {
            e.preventDefault();
            alert('Security settings saved (placeholder)');
        },

        // Missing Method Stubs to prevent JS crash
        saveQuery: function () { alert('Save Query function coming soon.'); },
        loadSavedQuery: function () { alert('Load Saved Query function coming soon.'); },
        loadSavedQueries: function () { $('#saved-queries-content').html('<p>No saved queries yet.</p>'); },
        loadAPIEndpoints: function () {
            var endpoints = [
                { path: '/contacts', methods: ['GET', 'POST'], description: 'Manage CRM contacts' },
                { path: '/donors', methods: ['GET', 'POST'], description: 'FEC donors' }
            ];
            var html = '';
            endpoints.forEach(function (endpoint) {
                html += '<div class="cp-api-endpoint" data-path="' + endpoint.path + '"><strong>' + endpoint.path + '</strong> (' + endpoint.methods.join(', ') + ')</div>';
            });
            $('#api-endpoints-list').html(html);
        },
        loadAPIStats: function () { $('#api-stats-content').html('<p>API tracking initialized.</p>'); },
        loadExportHistory: function () { $('#export-history-content').html('<p>No recent exports found.</p>'); },
        selectAPIEndpoint: function () { var path = $(this).data('path'); $('#api-endpoint').val(path); }
    };

    $(document).ready(function () {
        DevConsole.init();
    });

})(jQuery);
