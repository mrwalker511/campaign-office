<?php
/**
 * License Management Page
 *
 * Admin page for managing CampaignPress Premium license
 *
 * @package CampaignPress
 * @subpackage Premium
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get premium instance
$premium = CampaignPress_Premium::get_instance();
$license_data = $premium->get_license_data();
$is_active = $premium->is_premium_active();

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'license';

// Available tabs
$tabs = array(
    'license' => __('License Management', 'campaignpress'),
    'system' => __('System Information', 'campaignpress'),
);
?>

<div class="wrap campaignpress-premium-page">
    <h1>
        <?php _e('CampaignPress Premium', 'campaignpress'); ?>
        <?php if ($is_active): ?>
            <span class="cp-badge cp-badge-success"><?php _e('Active', 'campaignpress'); ?></span>
        <?php else: ?>
            <span class="cp-badge cp-badge-inactive"><?php _e('Inactive', 'campaignpress'); ?></span>
        <?php endif; ?>
    </h1>

    <nav class="nav-tab-wrapper wp-clearfix">
        <?php foreach ($tabs as $tab_key => $tab_label): ?>
            <a href="<?php echo esc_url(add_query_arg('tab', $tab_key, admin_url('admin.php?page=campaignpress-premium'))); ?>"
               class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html($tab_label); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="cp-tab-content">
        <?php if ($current_tab === 'license'): ?>
            <!-- License Management Tab -->
            <div class="cp-license-section">
                <?php if ($is_active && $license_data): ?>
                    <!-- Active License Display -->
                    <div class="cp-license-active-card">
                        <div class="cp-license-header">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <h2><?php _e('Your License is Active', 'campaignpress'); ?></h2>
                        </div>

                        <table class="cp-license-details">
                            <tr>
                                <th><?php _e('License Key:', 'campaignpress'); ?></th>
                                <td>
                                    <code><?php echo esc_html(substr($license_data['license_key'], 0, 20) . '...' . substr($license_data['license_key'], -8)); ?></code>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Email:', 'campaignpress'); ?></th>
                                <td><?php echo esc_html($license_data['license_email']); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('License Type:', 'campaignpress'); ?></th>
                                <td>
                                    <strong><?php echo esc_html(ucfirst($license_data['license_type'])); ?></strong>
                                    <?php if ($license_data['license_type'] === 'basic'): ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-upgrade')); ?>" class="button button-small">
                                            <?php _e('Upgrade', 'campaignpress'); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Status:', 'campaignpress'); ?></th>
                                <td>
                                    <?php if ($premium->is_license_expired()): ?>
                                        <span class="cp-status-badge cp-status-warning"><?php _e('Expired (Grace Period)', 'campaignpress'); ?></span>
                                    <?php else: ?>
                                        <span class="cp-status-badge cp-status-active"><?php _e('Active', 'campaignpress'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Expires:', 'campaignpress'); ?></th>
                                <td>
                                    <?php
                                    $expiry = $license_data['expiry_date'];
                                    $expiry_timestamp = strtotime($expiry);
                                    $days_until_expiry = ceil(($expiry_timestamp - time()) / DAY_IN_SECONDS);

                                    echo esc_html(date_i18n(get_option('date_format'), $expiry_timestamp));

                                    if ($days_until_expiry > 0 && $days_until_expiry <= 30) {
                                        echo ' <span class="cp-expires-soon">(' . sprintf(__('%d days remaining', 'campaignpress'), $days_until_expiry) . ')</span>';
                                    } elseif ($days_until_expiry <= 0) {
                                        echo ' <span class="cp-expired">(' . __('Expired', 'campaignpress') . ')</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Activated:', 'campaignpress'); ?></th>
                                <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($license_data['activated_date']))); ?></td>
                            </tr>
                            <tr>
                                <th><?php _e('Domain:', 'campaignpress'); ?></th>
                                <td><?php echo esc_html(home_url()); ?></td>
                            </tr>
                        </table>

                        <div class="cp-license-actions">
                            <button type="button" class="button" id="cp-check-license-btn">
                                <span class="dashicons dashicons-update"></span>
                                <?php _e('Check License Status', 'campaignpress'); ?>
                            </button>

                            <button type="button" class="button button-link-delete" id="cp-deactivate-license-btn">
                                <span class="dashicons dashicons-dismiss"></span>
                                <?php _e('Deactivate License', 'campaignpress'); ?>
                            </button>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- License Activation Form -->
                    <div class="cp-license-form-card">
                        <h2><?php _e('Activate Your License', 'campaignpress'); ?></h2>
                        <p><?php _e('Enter your license key to unlock premium features.', 'campaignpress'); ?></p>

                        <form id="cp-license-activation-form" class="cp-license-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="license_key"><?php _e('License Key', 'campaignpress'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text"
                                               id="license_key"
                                               name="license_key"
                                               class="regular-text"
                                               placeholder="XXXX-XXXX-XXXX-XXXX"
                                               value="<?php echo esc_attr(get_option('campaignpress_license_key')); ?>"
                                               required>
                                        <p class="description">
                                            <?php _e('Enter your CampaignPress Premium license key.', 'campaignpress'); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">
                                        <label for="license_email"><?php _e('License Email', 'campaignpress'); ?></label>
                                    </th>
                                    <td>
                                        <input type="email"
                                               id="license_email"
                                               name="license_email"
                                               class="regular-text"
                                               placeholder="your@email.com"
                                               value="<?php echo esc_attr(get_option('campaignpress_license_email', get_option('admin_email'))); ?>"
                                               required>
                                        <p class="description">
                                            <?php _e('The email address associated with your purchase.', 'campaignpress'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p class="submit">
                                <button type="submit" class="button button-primary button-large" id="cp-activate-license-btn">
                                    <span class="dashicons dashicons-admin-network"></span>
                                    <?php _e('Activate License', 'campaignpress'); ?>
                                </button>
                            </p>

                            <div id="cp-license-message" class="cp-message" style="display: none;"></div>
                        </form>

                        <div class="cp-license-help">
                            <h3><?php _e('Need a License?', 'campaignpress'); ?></h3>
                            <p>
                                <?php _e("Don't have a license key yet?", 'campaignpress'); ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-upgrade')); ?>" class="button button-secondary">
                                    <?php _e('View Pricing', 'campaignpress'); ?>
                                </a>
                            </p>

                            <h3><?php _e('Lost Your License?', 'campaignpress'); ?></h3>
                            <p>
                                <?php _e('Check your purchase confirmation email or', 'campaignpress'); ?>
                                <a href="https://campaignpress.com/my-account/" target="_blank">
                                    <?php _e('retrieve it from your account', 'campaignpress'); ?>
                                </a>.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($current_tab === 'system'): ?>
            <!-- System Information Tab -->
            <div class="cp-system-info">
                <h2><?php _e('System Information', 'campaignpress'); ?></h2>
                <p><?php _e('Use this information when contacting support.', 'campaignpress'); ?></p>

                <div class="cp-system-info-sections">
                    <!-- WordPress Environment -->
                    <div class="cp-info-section">
                        <h3><?php _e('WordPress Environment', 'campaignpress'); ?></h3>
                        <table class="widefat">
                            <tbody>
                                <tr>
                                    <td><?php _e('WordPress Version:', 'campaignpress'); ?></td>
                                    <td><strong><?php echo esc_html(get_bloginfo('version')); ?></strong></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Site URL:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(site_url()); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Home URL:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(home_url()); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Multisite:', 'campaignpress'); ?></td>
                                    <td><?php echo is_multisite() ? __('Yes', 'campaignpress') : __('No', 'campaignpress'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Debug Mode:', 'campaignpress'); ?></td>
                                    <td><?php echo WP_DEBUG ? __('Yes', 'campaignpress') : __('No', 'campaignpress'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Memory Limit:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(WP_MEMORY_LIMIT); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Language:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(get_locale()); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Theme Information -->
                    <div class="cp-info-section">
                        <h3><?php _e('Theme Information', 'campaignpress'); ?></h3>
                        <table class="widefat">
                            <tbody>
                                <tr>
                                    <td><?php _e('Theme Name:', 'campaignpress'); ?></td>
                                    <td><strong><?php echo esc_html(wp_get_theme()->get('Name')); ?></strong></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Theme Version:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(defined('CAMPAIGNPRESS_VERSION') ? CAMPAIGNPRESS_VERSION : 'Unknown'); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Premium Version:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(CampaignPress_Premium::VERSION); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Premium Status:', 'campaignpress'); ?></td>
                                    <td>
                                        <?php if ($is_active): ?>
                                            <span class="cp-status-badge cp-status-active"><?php _e('Active', 'campaignpress'); ?></span>
                                        <?php else: ?>
                                            <span class="cp-status-badge cp-status-inactive"><?php _e('Inactive', 'campaignpress'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php _e('Child Theme:', 'campaignpress'); ?></td>
                                    <td><?php echo is_child_theme() ? __('Yes', 'campaignpress') : __('No', 'campaignpress'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Server Environment -->
                    <div class="cp-info-section">
                        <h3><?php _e('Server Environment', 'campaignpress'); ?></h3>
                        <table class="widefat">
                            <tbody>
                                <tr>
                                    <td><?php _e('PHP Version:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(phpversion()); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Server Software:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('MySQL Version:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html($GLOBALS['wpdb']->db_version()); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('Max Upload Size:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(size_format(wp_max_upload_size())); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('PHP Post Max Size:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(ini_get('post_max_size')); ?></td>
                                </tr>
                                <tr>
                                    <td><?php _e('PHP Time Limit:', 'campaignpress'); ?></td>
                                    <td><?php echo esc_html(ini_get('max_execution_time')); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Active Plugins -->
                    <div class="cp-info-section">
                        <h3><?php _e('Active Plugins', 'campaignpress'); ?></h3>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th><?php _e('Plugin', 'campaignpress'); ?></th>
                                    <th><?php _e('Version', 'campaignpress'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $active_plugins = get_option('active_plugins');
                                foreach ($active_plugins as $plugin) {
                                    $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                                    ?>
                                    <tr>
                                        <td><?php echo esc_html($plugin_data['Name']); ?></td>
                                        <td><?php echo esc_html($plugin_data['Version']); ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="cp-system-actions">
                    <button type="button" class="button button-secondary" id="cp-copy-system-info">
                        <span class="dashicons dashicons-clipboard"></span>
                        <?php _e('Copy System Info', 'campaignpress'); ?>
                    </button>

                    <button type="button" class="button button-secondary" id="cp-download-system-info">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Download as Text', 'campaignpress'); ?>
                    </button>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<style>
.campaignpress-premium-page {
    max-width: 1200px;
}

.cp-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-left: 10px;
}

.cp-badge-success {
    background: #46b450;
    color: #fff;
}

.cp-badge-inactive {
    background: #dc3232;
    color: #fff;
}

.cp-tab-content {
    background: #fff;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.cp-license-active-card,
.cp-license-form-card {
    max-width: 800px;
}

.cp-license-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.cp-license-header .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #46b450;
    margin-right: 15px;
}

.cp-license-details {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.cp-license-details th,
.cp-license-details td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
}

.cp-license-details th {
    width: 200px;
    text-align: left;
    font-weight: 600;
}

.cp-status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
}

.cp-status-active {
    background: #d4edda;
    color: #155724;
}

.cp-status-warning {
    background: #fff3cd;
    color: #856404;
}

.cp-status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.cp-expires-soon {
    color: #856404;
    font-weight: 600;
}

.cp-expired {
    color: #dc3232;
    font-weight: 600;
}

.cp-license-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.cp-license-actions .button {
    margin-right: 10px;
}

.cp-license-help {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.cp-license-help h3 {
    margin-top: 20px;
}

.cp-message {
    margin-top: 20px;
    padding: 12px;
    border-left: 4px solid;
    border-radius: 3px;
}

.cp-message.success {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.cp-message.error {
    background: #f8d7da;
    border-color: #dc3232;
    color: #721c24;
}

.cp-system-info-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.cp-info-section {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
}

.cp-info-section h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #ddd;
}

.cp-info-section .widefat {
    background: #fff;
}

.cp-info-section .widefat td {
    padding: 8px;
}

.cp-system-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.button .dashicons {
    margin-right: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Activate license
    $('#cp-license-activation-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $('#cp-activate-license-btn');
        var $message = $('#cp-license-message');
        var originalText = $btn.html();
        var licenseKey = $('#license_key').val().trim();
        var licenseEmail = $('#license_email').val().trim();

        // Validation
        if (!licenseKey) {
            $message.removeClass('success').addClass('error')
                .html('<strong>' + cpPremium.strings.error + '</strong> License key is required.')
                .show();
            return false;
        }

        if (!licenseEmail) {
            $message.removeClass('success').addClass('error')
                .html('<strong>' + cpPremium.strings.error + '</strong> License email is required.')
                .show();
            return false;
        }

        // Validate email format
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(licenseEmail)) {
            $message.removeClass('success').addClass('error')
                .html('<strong>' + cpPremium.strings.error + '</strong> Please enter a valid email address.')
                .show();
            return false;
        }

        // Check if cpPremium object exists and has required properties
        if (typeof cpPremium === 'undefined' || !cpPremium.ajax_url || !cpPremium.nonce) {
            $message.removeClass('success').addClass('error')
                .html('<strong>' + cpPremium.strings.error + '</strong> Page configuration error. Please refresh and try again.')
                .show();
            return false;
        }

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> ' + cpPremium.strings.validating);
        $message.hide();

        $.ajax({
            url: cpPremium.ajax_url,
            type: 'POST',
            data: {
                action: 'cp_validate_license',
                nonce: cpPremium.nonce,
                license_key: licenseKey,
                license_email: licenseEmail
            },
            success: function(response) {
                if (response.success) {
                    $message.removeClass('error').addClass('success')
                        .html('<strong>' + cpPremium.strings.success + '</strong> ' + response.data.message)
                        .show();
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    $message.removeClass('success').addClass('error')
                        .html('<strong>' + cpPremium.strings.error + '</strong> ' + (response.data && response.data.message ? response.data.message : 'License validation failed.'))
                        .show();
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Failed to connect to server.';
                if (xhr.status === 403) {
                    errorMsg = 'Access denied. Please make sure you are logged in as an administrator.';
                } else if (xhr.status === 400) {
                    errorMsg = 'Invalid request. Please check your input and try again.';
                }
                $message.removeClass('success').addClass('error')
                    .html('<strong>' + cpPremium.strings.error + '</strong> ' + errorMsg)
                    .show();
                $btn.prop('disabled', false).html(originalText);
            }
        });
        
        return false;
    });

    // Deactivate license
    $('#cp-deactivate-license-btn').on('click', function(e) {
        e.preventDefault();

        if (!confirm(cpPremium.strings.confirm_deactivate)) {
            return;
        }

        var $btn = $(this);
        var originalText = $btn.html();

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> ' + cpPremium.strings.deactivating);

        $.ajax({
            url: cpPremium.ajax_url,
            type: 'POST',
            data: {
                action: 'cp_deactivate_license',
                nonce: cpPremium.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.data && response.data.message ? response.data.message : 'Deactivation failed. Please try again.');
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                alert('Failed to connect to server. Please try again.');
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Check license status
    $('#cp-check-license-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalText = $btn.html();

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update-alt"></span> Checking...');

        setTimeout(function() {
            window.location.reload();
        }, 500);
    });

    // Copy system info
    $('#cp-copy-system-info').on('click', function() {
        var systemInfo = '';
        $('.cp-info-section').each(function() {
            systemInfo += $(this).find('h3').text() + '\n';
            systemInfo += '='.repeat(50) + '\n';
            $(this).find('table tbody tr').each(function() {
                var label = $(this).find('td:first').text().trim();
                var value = $(this).find('td:last').text().trim();
                systemInfo += label + ' ' + value + '\n';
            });
            systemInfo += '\n';
        });

        navigator.clipboard.writeText(systemInfo).then(function() {
            alert('System information copied to clipboard!');
        });
    });

    // Download system info
    $('#cp-download-system-info').on('click', function() {
        var systemInfo = '';
        $('.cp-info-section').each(function() {
            systemInfo += $(this).find('h3').text() + '\n';
            systemInfo += '='.repeat(50) + '\n';
            $(this).find('table tbody tr').each(function() {
                var label = $(this).find('td:first').text().trim();
                var value = $(this).find('td:last').text().trim();
                systemInfo += label + ' ' + value + '\n';
            });
            systemInfo += '\n';
        });

        var blob = new Blob([systemInfo], { type: 'text/plain' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'campaignpress-system-info.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });
});
</script>
