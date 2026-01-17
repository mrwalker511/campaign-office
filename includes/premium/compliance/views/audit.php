<?php
/**
 * FEC Audit Trail View
 *
 * @package CampaignPress
 * @subpackage Compliance
 */

if (!defined('ABSPATH')) {
    exit;
}

$fec = cp_fec();

// Get audit log entries
global $wpdb;
$audit_table = $wpdb->prefix . 'cp_fec_audit_log';

$audit_entries = $wpdb->get_results("
    SELECT *
    FROM {$audit_table}
    ORDER BY created_at DESC
    LIMIT 100
");
?>
<div class="wrap cp-fec-audit">
    <h1><?php esc_html_e('FEC Audit Trail', 'campaignpress'); ?></h1>

    <p class="description">
        <?php esc_html_e('Complete audit trail of all FEC compliance actions. This log is retained for the period specified in your settings to meet FEC record-keeping requirements.', 'campaignpress'); ?>
    </p>

    <!-- Filter Options -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <select id="cp-fec-audit-filter">
                <option value=""><?php esc_html_e('All Events', 'campaignpress'); ?></option>
                <option value="contribution"><?php esc_html_e('Contributions', 'campaignpress'); ?></option>
                <option value="donor"><?php esc_html_e('Donor Changes', 'campaignpress'); ?></option>
                <option value="report"><?php esc_html_e('Reports', 'campaignpress'); ?></option>
                <option value="limit"><?php esc_html_e('Limit Warnings', 'campaignpress'); ?></option>
                <option value="compliance"><?php esc_html_e('Compliance Checks', 'campaignpress'); ?></option>
            </select>
            <button class="button" id="cp-fec-audit-filter-btn"><?php esc_html_e('Filter', 'campaignpress'); ?></button>
        </div>
        <div class="alignright">
            <a href="#" class="button" id="cp-fec-export-audit">
                <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                <?php esc_html_e('Export Audit Log', 'campaignpress'); ?>
            </a>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 150px;"><?php esc_html_e('Timestamp', 'campaignpress'); ?></th>
                <th style="width: 120px;"><?php esc_html_e('Event Type', 'campaignpress'); ?></th>
                <th><?php esc_html_e('Description', 'campaignpress'); ?></th>
                <th style="width: 120px;"><?php esc_html_e('User', 'campaignpress'); ?></th>
                <th style="width: 100px;"><?php esc_html_e('IP Address', 'campaignpress'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($audit_entries)): ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 2rem;">
                    <?php esc_html_e('No audit entries recorded yet. Actions will be logged as you use the FEC compliance system.', 'campaignpress'); ?>
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($audit_entries as $entry): ?>
                <?php
                $event_colors = array(
                    'contribution_added' => '#00a32a',
                    'contribution_refunded' => '#d63638',
                    'donor_created' => '#0073aa',
                    'donor_updated' => '#9b51e0',
                    'limit_warning' => '#dba617',
                    'prohibited_source' => '#d63638',
                    'report_generated' => '#0073aa',
                    'daily_compliance_check' => '#666',
                );
                $color = $event_colors[$entry->event_type] ?? '#666';
                $user = $entry->user_id ? get_user_by('id', $entry->user_id) : null;
                ?>
                <tr>
                    <td>
                        <?php echo esc_html(date_i18n('M j, Y g:i a', strtotime($entry->created_at))); ?>
                    </td>
                    <td>
                        <span style="display: inline-block; padding: 2px 8px; background: <?php echo esc_attr($color); ?>; color: #fff; border-radius: 3px; font-size: 11px;">
                            <?php echo esc_html(str_replace('_', ' ', ucfirst($entry->event_type))); ?>
                        </span>
                    </td>
                    <td>
                        <?php echo esc_html($entry->description ?? ''); ?>
                        <?php if (!empty($entry->data)): ?>
                            <?php
                            $data = maybe_unserialize($entry->data);
                            if (is_array($data) && !empty($data['amount'])):
                            ?>
                            <br><small style="color: #666;">
                                <?php printf(esc_html__('Amount: $%s', 'campaignpress'), number_format($data['amount'], 2)); ?>
                            </small>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($user): ?>
                            <?php echo esc_html($user->display_name); ?>
                        <?php else: ?>
                            <span style="color: #999;"><?php esc_html_e('System', 'campaignpress'); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <code style="font-size: 11px;"><?php echo esc_html($entry->ip_address ?? '—'); ?></code>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="card" style="margin-top: 20px;">
        <h3><?php esc_html_e('Audit Retention Policy', 'campaignpress'); ?></h3>
        <p>
            <?php
            $retention_years = get_option('cp_fec_audit_retention_years', 3);
            printf(
                esc_html__('Audit logs are retained for %d years per FEC record-keeping requirements. You can adjust this in %s.', 'campaignpress'),
                $retention_years,
                '<a href="' . esc_url(admin_url('admin.php?page=cp-fec-settings')) . '">' . esc_html__('Settings', 'campaignpress') . '</a>'
            );
            ?>
        </p>
    </div>
</div>
