<?php
/**
 * FEC Donors View
 *
 * @package CampaignPress
 * @subpackage Compliance
 */

if (!defined('ABSPATH')) {
    exit;
}

$fec = cp_fec();

// Get donors
global $wpdb;
$donors_table = $wpdb->prefix . 'cp_fec_donors';
$contributions_table = $wpdb->prefix . 'cp_fec_contributions';

$donors = $wpdb->get_results("
    SELECT d.*,
           COALESCE(SUM(c.amount), 0) as total_contributed,
           COUNT(c.id) as contribution_count
    FROM {$donors_table} d
    LEFT JOIN {$contributions_table} c ON d.id = c.donor_id AND c.status = 'completed'
    GROUP BY d.id
    ORDER BY total_contributed DESC
    LIMIT 100
");
?>
<div class="wrap cp-fec-donors">
    <h1>
        <?php esc_html_e('FEC Donors', 'campaignpress'); ?>
        <a href="#" class="page-title-action" id="cp-add-donor-btn">
            <?php esc_html_e('Add Donor', 'campaignpress'); ?>
        </a>
    </h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Name', 'campaignpress'); ?></th>
                <th><?php esc_html_e('Contact', 'campaignpress'); ?></th>
                <th><?php esc_html_e('Occupation/Employer', 'campaignpress'); ?></th>
                <th><?php esc_html_e('Total Contributed', 'campaignpress'); ?></th>
                <th><?php esc_html_e('# of Contributions', 'campaignpress'); ?></th>
                <th><?php esc_html_e('Limit Status', 'campaignpress'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($donors)): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 2rem;">
                    <?php esc_html_e('No donors recorded yet.', 'campaignpress'); ?>
                </td>
            </tr>
            <?php else: ?>
                <?php foreach ($donors as $donor): ?>
                <?php
                $limit = CP_FEC_INDIVIDUAL_LIMIT_CANDIDATE * 2; // Primary + General
                $percent = $limit > 0 ? ($donor->total_contributed / $limit) * 100 : 0;
                $limit_color = $percent >= 100 ? '#d63638' : ($percent >= 80 ? '#dba617' : '#00a32a');
                ?>
                <tr>
                    <td>
                        <strong><?php echo esc_html($donor->first_name . ' ' . $donor->last_name); ?></strong>
                    </td>
                    <td>
                        <?php if ($donor->email): ?>
                        <a href="mailto:<?php echo esc_attr($donor->email); ?>"><?php echo esc_html($donor->email); ?></a><br>
                        <?php endif; ?>
                        <?php if ($donor->phone): ?>
                        <small><?php echo esc_html($donor->phone); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($donor->occupation || $donor->employer): ?>
                            <?php echo esc_html($donor->occupation); ?>
                            <?php if ($donor->employer): ?>
                            <br><small><?php echo esc_html($donor->employer); ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #999;">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="color: #00a32a;">$<?php echo esc_html(number_format($donor->total_contributed, 2)); ?></strong>
                    </td>
                    <td><?php echo esc_html(number_format($donor->contribution_count)); ?></td>
                    <td>
                        <div style="background: #e0e0e0; border-radius: 3px; height: 20px; position: relative; width: 100px;">
                            <div style="background: <?php echo esc_attr($limit_color); ?>; border-radius: 3px; height: 100%; width: <?php echo esc_attr(min(100, $percent)); ?>%;"></div>
                        </div>
                        <small style="color: <?php echo esc_attr($limit_color); ?>;">
                            <?php echo esc_html(number_format($percent, 1)); ?>% of limit
                        </small>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
