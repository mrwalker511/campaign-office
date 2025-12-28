<?php
/**
 * Developer Console Admin Page
 *
 * Main UI for the developer console
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$console = CampaignPress_Developer_Console::get_instance();
$current_user = wp_get_current_user();
?>

<div class="wrap cp-developer-console">
    <h1 class="cp-dev-header">
        <span class="dashicons dashicons-code-standards"></span>
        CampaignPress Developer Console
        <span class="cp-dev-version">v<?php echo esc_html(CampaignPress_Developer_Console::VERSION); ?></span>
    </h1>

    <div class="cp-dev-welcome-banner">
        <p><strong>Welcome, <?php echo esc_html($current_user->display_name); ?></strong></p>
        <p>You are accessing the CampaignPress Developer Console. This is a powerful tool with full system access. Use with caution.</p>
        <p class="cp-dev-security-notice">
            <span class="dashicons dashicons-shield-alt"></span>
            Security Level: <strong><?php echo esc_html(strtoupper($console->settings->security_level ?? 'HIGH')); ?></strong> |
            Last Access: <?php echo esc_html($console->settings->last_access_at ?? 'Never'); ?> from <?php echo esc_html($console->settings->last_access_ip ?? 'N/A'); ?>
        </p>
    </div>

    <nav class="nav-tab-wrapper cp-dev-tabs">
        <a href="#dashboard" class="nav-tab nav-tab-active" data-tab="dashboard">
            <span class="dashicons dashicons-dashboard"></span> Dashboard
        </a>
        <a href="#system-health" class="nav-tab" data-tab="system-health">
            <span class="dashicons dashicons-heart"></span> System Health
        </a>
        <a href="#database" class="nav-tab" data-tab="database">
            <span class="dashicons dashicons-database"></span> Database
        </a>
        <a href="#api-tester" class="nav-tab" data-tab="api-tester">
            <span class="dashicons dashicons-rest-api"></span> API Tester
        </a>
        <a href="#activity-logs" class="nav-tab" data-tab="activity-logs">
            <span class="dashicons dashicons-list-view"></span> Activity Logs
        </a>
        <a href="#data-export" class="nav-tab" data-tab="data-export">
            <span class="dashicons dashicons-download"></span> Data Export
        </a>
        <a href="#users" class="nav-tab" data-tab="users">
            <span class="dashicons dashicons-admin-users"></span> Users
        </a>
        <a href="#settings" class="nav-tab" data-tab="settings">
            <span class="dashicons dashicons-admin-settings"></span> Settings
        </a>
    </nav>

    <!-- Dashboard Tab -->
    <div id="tab-dashboard" class="cp-dev-tab-content cp-dev-tab-active">
        <div class="cp-dev-grid">
            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-admin-site"></span> System Overview</h2>
                <div id="system-overview-content">
                    <div class="cp-loading">Loading system overview...</div>
                </div>
            </div>

            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-chart-line"></span> Quick Stats</h2>
                <div id="quick-stats-content">
                    <div class="cp-loading">Loading statistics...</div>
                </div>
            </div>

            <div class="cp-dev-card cp-dev-full-width">
                <h2><span class="dashicons dashicons-clock"></span> Recent Activity</h2>
                <div id="recent-activity-content">
                    <div class="cp-loading">Loading recent activity...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health Tab -->
    <div id="tab-system-health" class="cp-dev-tab-content">
        <div class="cp-dev-card">
            <div class="cp-dev-card-header">
                <h2><span class="dashicons dashicons-heart"></span> System Health Check</h2>
                <button class="button button-primary" id="refresh-health-btn">
                    <span class="dashicons dashicons-update"></span> Refresh
                </button>
            </div>
            <div id="system-health-content">
                <div class="cp-loading">Loading system health data...</div>
            </div>
        </div>
    </div>

    <!-- Database Tab -->
    <div id="tab-database" class="cp-dev-tab-content">
        <div class="cp-dev-grid">
            <div class="cp-dev-card cp-dev-full-width">
                <h2><span class="dashicons dashicons-database"></span> Database Query Tool</h2>
                <div class="cp-dev-query-form">
                    <label for="db-query">SQL Query:</label>
                    <textarea id="db-query" rows="8" class="large-text code" placeholder="SELECT * FROM wp_posts WHERE post_type = 'post' LIMIT 10"></textarea>

                    <div class="cp-dev-query-actions">
                        <button class="button button-primary" id="execute-query-btn">
                            <span class="dashicons dashicons-controls-play"></span> Execute Query
                        </button>
                        <button class="button" id="save-query-btn">
                            <span class="dashicons dashicons-saved"></span> Save Query
                        </button>
                        <label>
                            <input type="checkbox" id="query-confirm-dangerous"> Confirm dangerous operations
                        </label>
                    </div>

                    <div id="query-result-container" style="display:none;">
                        <h3>Query Result:</h3>
                        <div id="query-result"></div>
                    </div>
                </div>
            </div>

            <div class="cp-dev-card cp-dev-full-width">
                <div class="cp-dev-card-header">
                    <h2><span class="dashicons dashicons-list-view"></span> Database Tables</h2>
                    <button class="button button-secondary" id="run-migration-btn">
                        <span class="dashicons dashicons-groups"></span> Run Consolidation Migration
                    </button>
                </div>
                <div id="database-tables-content">
                    <div class="cp-loading">Loading database tables...</div>
                </div>
            </div>

            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-saved"></span> Saved Queries</h2>
                <div id="saved-queries-content">
                    <div class="cp-loading">Loading saved queries...</div>
                </div>
            </div>

            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-chart-area"></span> CampaignPress Stats</h2>
                <div id="cp-stats-content">
                    <div class="cp-loading">Loading CampaignPress statistics...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- API Tester Tab -->
    <div id="tab-api-tester" class="cp-dev-tab-content">
        <div class="cp-dev-grid">
            <div class="cp-dev-card cp-dev-full-width">
                <h2><span class="dashicons dashicons-rest-api"></span> API Endpoint Tester</h2>
                <div class="cp-dev-api-form">
                    <div class="cp-dev-form-group">
                        <label for="api-method">Method:</label>
                        <select id="api-method">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>

                    <div class="cp-dev-form-group">
                        <label for="api-endpoint">Endpoint:</label>
                        <input type="text" id="api-endpoint" class="large-text" placeholder="/contacts" value="/contacts">
                    </div>

                    <div class="cp-dev-form-group">
                        <label for="api-body">Request Body (JSON):</label>
                        <textarea id="api-body" rows="6" class="large-text code" placeholder='{"first_name": "John", "last_name": "Doe"}'></textarea>
                    </div>

                    <button class="button button-primary" id="test-api-btn">
                        <span class="dashicons dashicons-controls-play"></span> Send Request
                    </button>

                    <div id="api-result-container" style="display:none;">
                        <h3>Response:</h3>
                        <div id="api-result"></div>
                    </div>
                </div>
            </div>

            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-info"></span> Available Endpoints</h2>
                <div id="api-endpoints-list">
                    <div class="cp-loading">Loading endpoints...</div>
                </div>
            </div>

            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-chart-bar"></span> API Statistics</h2>
                <div id="api-stats-content">
                    <div class="cp-loading">Loading API statistics...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Logs Tab -->
    <div id="tab-activity-logs" class="cp-dev-tab-content">
        <div class="cp-dev-card">
            <div class="cp-dev-card-header">
                <h2><span class="dashicons dashicons-list-view"></span> Activity Logs</h2>
                <div class="cp-dev-filters">
                    <select id="log-category-filter">
                        <option value="all">All Categories</option>
                        <option value="auth">Authentication</option>
                        <option value="database">Database</option>
                        <option value="api">API</option>
                        <option value="system">System</option>
                        <option value="user">User</option>
                        <option value="data">Data</option>
                        <option value="security">Security</option>
                        <option value="settings">Settings</option>
                    </select>
                    <button class="button" id="refresh-logs-btn">
                        <span class="dashicons dashicons-update"></span> Refresh
                    </button>
                </div>
            </div>
            <div id="activity-logs-content">
                <div class="cp-loading">Loading activity logs...</div>
            </div>
        </div>
    </div>

    <!-- Data Export Tab -->
    <div id="tab-data-export" class="cp-dev-tab-content">
        <div class="cp-dev-grid">
            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-download"></span> Export Data</h2>
                <div class="cp-dev-export-form">
                    <div class="cp-dev-form-group">
                        <label for="export-type">Data Type:</label>
                        <select id="export-type">
                            <option value="contacts">CRM Contacts</option>
                            <option value="interactions">Interactions</option>
                            <option value="donors">FEC Donors</option>
                            <option value="contributions">FEC Contributions</option>
                            <option value="settings">All Settings</option>
                            <option value="logs">Developer Logs</option>
                            <option value="full_backup">Full Backup (All Data)</option>
                        </select>
                    </div>

                    <div class="cp-dev-form-group">
                        <label for="export-format">Format:</label>
                        <select id="export-format">
                            <option value="json">JSON</option>
                            <option value="csv">CSV</option>
                            <option value="xml">XML</option>
                        </select>
                    </div>

                    <button class="button button-primary" id="export-data-btn">
                        <span class="dashicons dashicons-download"></span> Export Data
                    </button>
                </div>

                <div id="export-result-container" style="display:none; margin-top: 20px;">
                    <h3>Export Completed:</h3>
                    <div id="export-result"></div>
                    <button class="button button-primary" id="download-export-btn">
                        <span class="dashicons dashicons-download"></span> Download File
                    </button>
                </div>
            </div>

            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-clock"></span> Export History</h2>
                <div id="export-history-content">
                    <div class="cp-loading">Loading export history...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Tab -->
    <div id="tab-users" class="cp-dev-tab-content">
        <div class="cp-dev-card">
            <div class="cp-dev-card-header">
                <h2><span class="dashicons dashicons-admin-users"></span> User Management</h2>
                <button class="button button-primary" id="refresh-users-btn">
                    <span class="dashicons dashicons-update"></span> Refresh
                </button>
            </div>
            <div id="users-content">
                <div class="cp-loading">Loading users...</div>
            </div>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="tab-settings" class="cp-dev-tab-content">
        <div class="cp-dev-grid">
            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-admin-settings"></span> Security Settings</h2>
                <form id="security-settings-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="security-level">Security Level:</label>
                            </th>
                            <td>
                                <select id="security-level" name="security_level">
                                    <option value="standard">Standard</option>
                                    <option value="high" selected>High</option>
                                    <option value="maximum">Maximum</option>
                                </select>
                                <p class="description">Higher security levels add more verification steps.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="session-timeout">Session Timeout (seconds):</label>
                            </th>
                            <td>
                                <input type="number" id="session-timeout" name="session_timeout" value="3600" min="300" max="86400">
                                <p class="description">How long before automatic logout (300-86400 seconds).</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="ip-whitelist">IP Whitelist:</label>
                            </th>
                            <td>
                                <textarea id="ip-whitelist" name="ip_whitelist" rows="4" class="large-text"></textarea>
                                <p class="description">One IP address per line. Leave empty to allow all IPs. Supports CIDR notation.</p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-saved"></span> Save Security Settings
                        </button>
                    </p>
                </form>
            </div>

            <div class="cp-dev-card">
                <h2><span class="dashicons dashicons-info"></span> Console Information</h2>
                <table class="widefat">
                    <tr>
                        <td><strong>Console Version:</strong></td>
                        <td><?php echo esc_html(CampaignPress_Developer_Console::VERSION); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Creator User ID:</strong></td>
                        <td><?php echo esc_html($console->settings->creator_user_id ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Creator Email:</strong></td>
                        <td><?php echo esc_html($console->settings->creator_email ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Console Status:</strong></td>
                        <td>
                            <?php if ($console->is_enabled()): ?>
                                <span class="cp-status-enabled">Enabled</span>
                            <?php else: ?>
                                <span class="cp-status-disabled">Disabled</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Failed Login Attempts:</strong></td>
                        <td><?php echo esc_html($console->settings->failed_login_attempts ?? 0); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Access:</strong></td>
                        <td><?php echo esc_html($console->settings->last_access_at ?? 'Never'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Access IP:</strong></td>
                        <td><?php echo esc_html($console->settings->last_access_ip ?? 'N/A'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Hidden export download form -->
<form id="export-download-form" method="post" style="display:none;">
    <input type="hidden" id="export-content" name="export_content">
    <input type="hidden" id="export-filename" name="export_filename">
    <input type="hidden" id="export-mimetype" name="export_mimetype">
</form>

<style>
    .cp-stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .cp-stat-card { background: #f9f9f9; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; }
    .cp-stat-card h4 { margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px; }
    .cp-dev-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .cp-dev-card-header h2 { margin-bottom: 0; }
    .cp-status-enabled { color: #46b450; font-weight: bold; }
    .cp-status-disabled { color: #dc3232; font-weight: bold; }
    .cp-dev-table-actions .button { margin-right: 5px; }
    .cp-result-json { background: #23282d; color: #fff; padding: 15px; border-radius: 4px; overflow-x: auto; }
</style>

<script type="text/javascript">
    var cpDevConsole = {
        nonce: '<?php echo wp_create_nonce('cp_dev_console_nonce'); ?>',
        ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
        exportData: null
    };
</script>
