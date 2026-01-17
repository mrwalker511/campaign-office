<?php
/**
 * Features Management Page
 *
 * Admin page for managing premium features
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
$premium_features = $premium->get_premium_features();
$enabled_features = $premium->get_enabled_features();
$license_data = $premium->get_license_data();
$current_license = $license_data ? $license_data['license_type'] : 'free';

// License hierarchy
$license_hierarchy = array('free' => 0, 'professional' => 1);
$current_license_level = isset($license_hierarchy[$current_license]) ? $license_hierarchy[$current_license] : 0;
?>

<div class="wrap campaignpress-features-page">
    <h1><?php _e('Premium Features Management', 'campaignpress'); ?></h1>
    <p class="description">
        <?php _e('Enable or disable premium features. Changes take effect immediately.', 'campaignpress'); ?>
    </p>

    <?php if ($license_data): ?>
        <div class="cp-license-info-banner">
            <div class="cp-license-type">
                <strong><?php _e('Your License:', 'campaignpress'); ?></strong>
                <span class="cp-license-badge cp-license-<?php echo esc_attr($current_license); ?>">
                    <?php echo esc_html(ucfirst($current_license)); ?>
                </span>
            </div>
            <?php if ($current_license !== 'professional'): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-upgrade')); ?>" class="button button-primary">
                    <?php _e('Upgrade License', 'campaignpress'); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="cp-features-grid">
        <?php foreach ($premium_features as $feature_key => $feature): ?>
            <?php
            // Check if feature is available for current license
            $required_license = isset($feature['required_license']) ? $feature['required_license'] : 'free';
            $required_level = isset($license_hierarchy[$required_license]) ? $license_hierarchy[$required_license] : 0;
            $is_available = $current_license_level >= $required_level;
            $is_enabled = $premium->is_feature_enabled($feature_key);
            $icon = isset($feature['icon']) ? $feature['icon'] : 'dashicons-admin-generic';
            ?>

            <div class="cp-feature-card <?php echo esc_attr($is_available ? '' : 'cp-feature-locked'); ?>">
                <div class="cp-feature-header">
                    <span class="<?php echo esc_attr($icon); ?> cp-feature-icon"></span>
                    <h3><?php echo esc_html($feature['name']); ?></h3>
                    <?php if (!$is_available): ?>
                        <span class="cp-feature-lock">
                            <span class="dashicons dashicons-lock"></span>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="cp-feature-body">
                    <p class="cp-feature-description">
                        <?php echo esc_html($feature['description']); ?>
                    </p>

                    <div class="cp-feature-meta">
                        <span class="cp-feature-license-required">
                            <?php
                            printf(
                                __('Requires: %s', 'campaignpress'),
                                '<strong>' . esc_html(ucfirst($required_license)) . '</strong>'
                            );
                            ?>
                        </span>
                    </div>
                </div>

                <div class="cp-feature-footer">
                    <?php if ($is_available): ?>
                        <label class="cp-toggle-switch">
                            <input type="checkbox"
                                   class="cp-feature-toggle"
                                   data-feature="<?php echo esc_attr($feature_key); ?>"
                                   <?php checked($is_enabled); ?>>
                            <span class="cp-toggle-slider"></span>
                        </label>
                        <span class="cp-feature-status">
                            <?php echo $is_enabled ? __('Enabled', 'campaignpress') : __('Disabled', 'campaignpress'); ?>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-upgrade')); ?>" class="button button-secondary">
                            <?php _e('Upgrade to Unlock', 'campaignpress'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="cp-features-help">
        <h2><?php _e('Feature Management Help', 'campaignpress'); ?></h2>
        <div class="cp-help-grid">
            <div class="cp-help-item">
                <h3><span class="dashicons dashicons-info"></span> <?php _e('Toggling Features', 'campaignpress'); ?></h3>
                <p><?php _e('Use the toggle switches to enable or disable features. Changes take effect immediately without requiring a page reload.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-help-item">
                <h3><span class="dashicons dashicons-shield-alt"></span> <?php _e('License Requirements', 'campaignpress'); ?></h3>
                <p><?php _e('Some features require a specific license level. Upgrade your license to unlock additional features.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-help-item">
                <h3><span class="dashicons dashicons-performance"></span> <?php _e('Performance', 'campaignpress'); ?></h3>
                <p><?php _e('Disable features you don\'t use to improve site performance and reduce resource usage.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-help-item">
                <h3><span class="dashicons dashicons-sos"></span> <?php _e('Need Help?', 'campaignpress'); ?></h3>
                <p>
                    <?php _e('Contact our support team for assistance:', 'campaignpress'); ?>
                    <a href="https://campaignpress.com/support/" target="_blank"><?php _e('Get Support', 'campaignpress'); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
.campaignpress-features-page {
    max-width: 1400px;
}

.cp-license-info-banner {
    background: #f0f6fc;
    border: 1px solid #0969da;
    border-radius: 6px;
    padding: 15px 20px;
    margin: 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cp-license-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    margin-left: 10px;
}

.cp-license-free {
    background: #666;
    color: #fff;
}

.cp-license-professional {
    background: #8250df;
    color: #fff;
}

.cp-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.cp-feature-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.cp-feature-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.cp-feature-locked {
    opacity: 0.6;
    background: #f9f9f9;
}

.cp-feature-locked:hover {
    transform: none;
}

.cp-feature-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    position: relative;
}

.cp-feature-icon {
    font-size: 32px;
    width: 32px;
    height: 32px;
    margin-right: 12px;
    color: #2271b1;
}

.cp-feature-header h3 {
    margin: 0;
    font-size: 16px;
    flex: 1;
}

.cp-feature-lock {
    position: absolute;
    top: 0;
    right: 0;
    color: #999;
}

.cp-feature-body {
    flex: 1;
    margin-bottom: 15px;
}

.cp-feature-description {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 12px;
}

.cp-feature-meta {
    font-size: 12px;
    color: #999;
    padding-top: 10px;
    border-top: 1px solid #eee;
}

.cp-feature-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.cp-toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    margin: 0;
}

.cp-toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.cp-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 24px;
}

.cp-toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .cp-toggle-slider {
    background-color: #2271b1;
}

input:checked + .cp-toggle-slider:before {
    transform: translateX(26px);
}

.cp-feature-status {
    font-size: 13px;
    font-weight: 600;
    color: #666;
}

.cp-features-help {
    margin-top: 40px;
    padding: 30px;
    background: #f9f9f9;
    border-radius: 8px;
}

.cp-features-help h2 {
    margin-top: 0;
    margin-bottom: 20px;
}

.cp-help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.cp-help-item {
    background: #fff;
    padding: 20px;
    border-radius: 6px;
    border: 1px solid #ddd;
}

.cp-help-item h3 {
    margin-top: 0;
    font-size: 15px;
    display: flex;
    align-items: center;
}

.cp-help-item h3 .dashicons {
    margin-right: 8px;
    color: #2271b1;
}

.cp-help-item p {
    margin-bottom: 0;
    color: #666;
    font-size: 14px;
    line-height: 1.6;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Feature toggle handler
    $('.cp-feature-toggle').on('change', function() {
        var $toggle = $(this);
        var $card = $toggle.closest('.cp-feature-card');
        var $status = $card.find('.cp-feature-status');
        var feature = $toggle.data('feature');
        var enabled = $toggle.is(':checked');

        // Disable toggle during request
        $toggle.prop('disabled', true);

        $.ajax({
            url: cpPremium.ajax_url,
            type: 'POST',
            data: {
                action: 'cp_toggle_feature',
                nonce: cpPremium.nonce,
                feature: feature,
                enabled: enabled
            },
            success: function(response) {
                if (response.success) {
                    // Update status text
                    $status.text(enabled ? '<?php echo esc_js(__('Enabled', 'campaignpress')); ?>' : '<?php echo esc_js(__('Disabled', 'campaignpress')); ?>');

                    // Show success notification
                    showNotification(response.data.message, 'success');
                } else {
                    // Revert toggle on error
                    $toggle.prop('checked', !enabled);
                    showNotification(response.data.message, 'error');
                }
                $toggle.prop('disabled', false);
            },
            error: function() {
                // Revert toggle on error
                $toggle.prop('checked', !enabled);
                showNotification('<?php echo esc_js(__('Failed to update feature. Please try again.', 'campaignpress')); ?>', 'error');
                $toggle.prop('disabled', false);
            }
        });
    });

    // Notification function
    function showNotification(message, type) {
        var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
        $('.campaignpress-features-page h1').after($notice);

        // Auto dismiss after 3 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>
