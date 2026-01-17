<?php
/**
 * FEC Reports View
 *
 * @package CampaignPress
 * @subpackage Compliance
 */

if (!defined('ABSPATH')) {
    exit;
}

$fec = cp_fec();
$committee_info = $fec->get_committee_info();

// Calculate quarter dates
$current_quarter = ceil(date('n') / 3);
$current_year = date('Y');
?>
<div class="wrap cp-fec-reports">
    <h1><?php esc_html_e('FEC Reports', 'campaignpress'); ?></h1>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
        <!-- Generate Report Card -->
        <div class="card">
            <h2><?php esc_html_e('Generate New Report', 'campaignpress'); ?></h2>
            <form id="cp-fec-generate-report">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="report_type"><?php esc_html_e('Report Type', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <select name="report_type" id="report_type" class="regular-text">
                                <option value="quarterly"><?php esc_html_e('Quarterly Report (FEC Form 3)', 'campaignpress'); ?></option>
                                <option value="year_end"><?php esc_html_e('Year-End Report', 'campaignpress'); ?></option>
                                <option value="pre_election"><?php esc_html_e('Pre-Election Report', 'campaignpress'); ?></option>
                                <option value="post_election"><?php esc_html_e('Post-Election Report', 'campaignpress'); ?></option>
                                <option value="48hour"><?php esc_html_e('48-Hour Notice', 'campaignpress'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="report_period"><?php esc_html_e('Reporting Period', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <select name="report_period" id="report_period" class="regular-text">
                                <?php for ($q = 1; $q <= 4; $q++): ?>
                                <option value="<?php echo esc_attr($current_year . '-Q' . $q); ?>" <?php selected($q, $current_quarter); ?>>
                                    <?php printf(esc_html__('Q%1$d %2$d', 'campaignpress'), $q, $current_year); ?>
                                </option>
                                <?php endfor; ?>
                                <?php for ($q = 1; $q <= 4; $q++): ?>
                                <option value="<?php echo esc_attr(($current_year - 1) . '-Q' . $q); ?>">
                                    <?php printf(esc_html__('Q%1$d %2$d', 'campaignpress'), $q, $current_year - 1); ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                </table>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Generate Report', 'campaignpress'); ?>
                    </button>
                </p>
            </form>
            <div id="cp-fec-report-result" style="margin-top: 15px;"></div>
        </div>

        <!-- Report Schedule Card -->
        <div class="card">
            <h2><?php esc_html_e('FEC Filing Schedule', 'campaignpress'); ?></h2>
            <table class="widefat" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Report', 'campaignpress'); ?></th>
                        <th><?php esc_html_e('Due Date', 'campaignpress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e('Q1 Report', 'campaignpress'); ?></td>
                        <td><?php echo esc_html(date_i18n('F j, Y', strtotime($current_year . '-04-15'))); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Q2 Report', 'campaignpress'); ?></td>
                        <td><?php echo esc_html(date_i18n('F j, Y', strtotime($current_year . '-07-15'))); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Q3 Report', 'campaignpress'); ?></td>
                        <td><?php echo esc_html(date_i18n('F j, Y', strtotime($current_year . '-10-15'))); ?></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Year-End Report', 'campaignpress'); ?></td>
                        <td><?php echo esc_html(date_i18n('F j, Y', strtotime(($current_year + 1) . '-01-31'))); ?></td>
                    </tr>
                </tbody>
            </table>
            <p class="description" style="margin-top: 10px;">
                <?php esc_html_e('Pre-election and post-election reports have specific deadlines based on election dates.', 'campaignpress'); ?>
            </p>
        </div>

        <!-- Export Options Card -->
        <div class="card">
            <h2><?php esc_html_e('Export Options', 'campaignpress'); ?></h2>
            <p><?php esc_html_e('Export your contribution data in FEC-compatible formats:', 'campaignpress'); ?></p>
            <ul style="margin: 15px 0; padding-left: 20px;">
                <li>
                    <a href="#" class="cp-fec-export" data-format="csv">
                        <span class="dashicons dashicons-media-spreadsheet" style="color: #00a32a;"></span>
                        <?php esc_html_e('CSV Export (FEC Filing Software)', 'campaignpress'); ?>
                    </a>
                </li>
                <li style="margin-top: 10px;">
                    <a href="#" class="cp-fec-export" data-format="fec">
                        <span class="dashicons dashicons-media-document" style="color: #0073aa;"></span>
                        <?php esc_html_e('FEC Form 3 Format', 'campaignpress'); ?>
                    </a>
                </li>
                <li style="margin-top: 10px;">
                    <a href="#" class="cp-fec-export" data-format="pdf">
                        <span class="dashicons dashicons-pdf" style="color: #d63638;"></span>
                        <?php esc_html_e('PDF Summary Report', 'campaignpress'); ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="card" style="margin-top: 20px;">
        <h2><?php esc_html_e('Recent Reports', 'campaignpress'); ?></h2>
        <p class="description"><?php esc_html_e('Previously generated reports will appear here.', 'campaignpress'); ?></p>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th><?php esc_html_e('Report Name', 'campaignpress'); ?></th>
                    <th><?php esc_html_e('Period', 'campaignpress'); ?></th>
                    <th><?php esc_html_e('Generated', 'campaignpress'); ?></th>
                    <th><?php esc_html_e('Actions', 'campaignpress'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 2rem; color: #666;">
                        <?php esc_html_e('No reports generated yet. Use the form above to create your first report.', 'campaignpress'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
