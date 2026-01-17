<?php
/**
 * FEC Compliance Dashboard View
 *
 * @package CampaignPress
 * @subpackage Compliance
 */

if (!defined('ABSPATH')) {
    exit;
}

$fec = cp_fec();
$committee_info = $fec->get_committee_info();

// Get statistics
global $wpdb;
$donors_table = $wpdb->prefix . 'cp_fec_donors';
$contributions_table = $wpdb->prefix . 'cp_fec_contributions';

$total_donors = $wpdb->get_var("SELECT COUNT(*) FROM {$donors_table}") ?: 0;
$total_contributions = $wpdb->get_var("SELECT SUM(amount) FROM {$contributions_table} WHERE status = 'completed'") ?: 0;
$contribution_count = $wpdb->get_var("SELECT COUNT(*) FROM {$contributions_table} WHERE status = 'completed'") ?: 0;
$itemized_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$contributions_table} WHERE amount >= %d AND status = 'completed'",
    CP_FEC_ITEMIZATION_THRESHOLD
)) ?: 0;
?>
<div class="wrap cp-fec-dashboard">
    <h1><?php esc_html_e('FEC Compliance Dashboard', 'campaignpress'); ?></h1>

    <?php if (empty($committee_info['committee_id'])): ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php esc_html_e('Setup Required:', 'campaignpress'); ?></strong>
            <?php
            printf(
                esc_html__('Please %s to complete your FEC compliance setup.', 'campaignpress'),
                '<a href="' . esc_url(admin_url('admin.php?page=cp-fec-settings')) . '">' . esc_html__('configure your committee information', 'campaignpress') . '</a>'
            );
            ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div class="cp-fec-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
        <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #0073aa; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 32px; font-weight: bold; color: #0073aa;">
                $<?php echo esc_html(number_format($total_contributions, 2)); ?>
            </div>
            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Total Contributions', 'campaignpress'); ?></div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #00a32a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 32px; font-weight: bold; color: #00a32a;">
                <?php echo esc_html(number_format($contribution_count)); ?>
            </div>
            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Contribution Count', 'campaignpress'); ?></div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #9b51e0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 32px; font-weight: bold; color: #9b51e0;">
                <?php echo esc_html(number_format($total_donors)); ?>
            </div>
            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Total Donors', 'campaignpress'); ?></div>
        </div>

        <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #d63638; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 32px; font-weight: bold; color: #d63638;">
                <?php echo esc_html(number_format($itemized_count)); ?>
            </div>
            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Itemized Contributions', 'campaignpress'); ?></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
        <div class="card">
            <h2><?php esc_html_e('Contribution Management', 'campaignpress'); ?></h2>
            <ul style="margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-fec-contributions')); ?>">
                        <span class="dashicons dashicons-money-alt" style="color: #0073aa;"></span>
                        <?php esc_html_e('View All Contributions', 'campaignpress'); ?>
                    </a>
                </li>
                <li style="margin-bottom: 10px;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-fec-donors')); ?>">
                        <span class="dashicons dashicons-groups" style="color: #00a32a;"></span>
                        <?php esc_html_e('Manage Donors', 'campaignpress'); ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card">
            <h2><?php esc_html_e('FEC Reporting', 'campaignpress'); ?></h2>
            <ul style="margin: 0; padding-left: 20px;">
                <li style="margin-bottom: 10px;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-fec-reports')); ?>">
                        <span class="dashicons dashicons-media-document" style="color: #9b51e0;"></span>
                        <?php esc_html_e('Generate FEC Reports', 'campaignpress'); ?>
                    </a>
                </li>
                <li style="margin-bottom: 10px;">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=cp-fec-audit')); ?>">
                        <span class="dashicons dashicons-visibility" style="color: #d63638;"></span>
                        <?php esc_html_e('View Audit Trail', 'campaignpress'); ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card">
            <h2><?php esc_html_e('Current Limits (2024)', 'campaignpress'); ?></h2>
            <table class="widefat" style="margin-top: 10px;">
                <tbody>
                    <tr>
                        <td><?php esc_html_e('Individual to Candidate', 'campaignpress'); ?></td>
                        <td style="text-align: right;"><strong>$<?php echo esc_html(number_format(CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE)); ?></strong> / election</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Individual to PAC', 'campaignpress'); ?></td>
                        <td style="text-align: right;"><strong>$<?php echo esc_html(number_format(CP_FEC_INDIVIDUAL_LIMIT_PAC)); ?></strong> / year</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('PAC to Candidate', 'campaignpress'); ?></td>
                        <td style="text-align: right;"><strong>$<?php echo esc_html(number_format(CP_FEC_PAC_LIMIT_CANDIDATE)); ?></strong> / election</td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Itemization Threshold', 'campaignpress'); ?></td>
                        <td style="text-align: right;"><strong>$<?php echo esc_html(number_format(CP_FEC_ITEMIZATION_THRESHOLD)); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($committee_info['committee_name'])): ?>
    <div class="card" style="margin-top: 20px;">
        <h2><?php esc_html_e('Committee Information', 'campaignpress'); ?></h2>
        <table class="widefat">
            <tbody>
                <tr>
                    <td width="200"><strong><?php esc_html_e('Committee Name', 'campaignpress'); ?></strong></td>
                    <td><?php echo esc_html($committee_info['committee_name']); ?></td>
                </tr>
                <?php if (!empty($committee_info['committee_id'])): ?>
                <tr>
                    <td><strong><?php esc_html_e('FEC Committee ID', 'campaignpress'); ?></strong></td>
                    <td><?php echo esc_html($committee_info['committee_id']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($committee_info['treasurer_name'])): ?>
                <tr>
                    <td><strong><?php esc_html_e('Treasurer', 'campaignpress'); ?></strong></td>
                    <td><?php echo esc_html($committee_info['treasurer_name']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong><?php esc_html_e('Committee Type', 'campaignpress'); ?></strong></td>
                    <td><?php echo esc_html(ucfirst($committee_info['committee_type'])); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
