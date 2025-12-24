<?php
/**
 * FEC Contributions View
 *
 * @package CampaignPress
 * @subpackage Compliance
 */

if (!defined('ABSPATH')) {
    exit;
}

$fec = cp_fec();

// Get contributions
global $wpdb;
$contributions_table = $wpdb->prefix . 'cp_fec_contributions';
$donors_table = $wpdb->prefix . 'cp_fec_donors';

$contributions = $wpdb->get_results("
    SELECT c.*, d.first_name, d.last_name, d.email, d.occupation, d.employer
    FROM {$contributions_table} c
    LEFT JOIN {$donors_table} d ON c.donor_id = d.id
    ORDER BY c.contribution_date DESC
    LIMIT 100
");
?>
<div class="wrap cp-fec-contributions">
    <h1>
        <?php esc_html_e('FEC Contributions', 'campaign-office'); ?>
        <a href="#" class="page-title-action" id="cp-add-contribution-btn">
            <?php esc_html_e('Add Contribution', 'campaign-office'); ?>
        </a>
    </h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'campaign-office'); ?></th>
                <th><?php esc_html_e('Donor', 'campaign-office'); ?></th>
                <th><?php esc_html_e('Amount', 'campaign-office'); ?></th>
                <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                <th><?php esc_html_e('Election', 'campaign-office'); ?></th>
                <th><?php esc_html_e('Status', 'campaign-office'); ?></th>
                <th><?php esc_html_e('Occupation/Employer', 'campaign-office'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($contributions)): ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 2rem;">
                    <?php esc_html_e('No contributions recorded yet.', 'campaign-office'); ?>
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($contributions as $contribution): ?>
                <tr>
                    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($contribution->contribution_date))); ?></td>
                    <td>
                        <strong><?php echo esc_html($contribution->first_name . ' ' . $contribution->last_name); ?></strong>
                        <?php if ($contribution->email): ?>
                        <br><small><?php echo esc_html($contribution->email); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="color: #00a32a;">$<?php echo esc_html(number_format($contribution->amount, 2)); ?></strong>
                        <?php if ($contribution->amount >= CP_FEC_ITEMIZATION_THRESHOLD): ?>
                        <br><span class="dashicons dashicons-flag" title="<?php esc_attr_e('Itemized', 'campaign-office'); ?>" style="color: #d63638; font-size: 14px;"></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo esc_html(ucfirst($contribution->contribution_type ?? 'monetary')); ?></td>
                    <td><?php echo esc_html(ucfirst($contribution->election_type ?? 'primary')); ?></td>
                    <td>
                        <?php
                        $status_colors = array(
                            'completed' => '#00a32a',
                            'pending' => '#dba617',
                            'refunded' => '#d63638',
                            'failed' => '#999',
                        );
                        $status = $contribution->status ?? 'pending';
                        $color = $status_colors[$status] ?? '#999';
                        ?>
                        <span style="display: inline-block; padding: 2px 8px; background: <?php echo esc_attr($color); ?>; color: #fff; border-radius: 3px; font-size: 11px;">
                            <?php echo esc_html(ucfirst($status)); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($contribution->occupation || $contribution->employer): ?>
                            <?php echo esc_html($contribution->occupation); ?>
                            <?php if ($contribution->employer): ?>
                            <br><small><?php echo esc_html($contribution->employer); ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #999;">&mdash;</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
