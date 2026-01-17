<?php
/**
 * FEC Compliance Settings View
 *
 * @package CampaignPress
 * @subpackage Compliance
 */

if (!defined('ABSPATH')) {
    exit;
}

$fec = cp_fec();
$committee_info = $fec->get_committee_info();
?>
<div class="wrap cp-fec-settings">
    <h1><?php esc_html_e('FEC Compliance Settings', 'campaignpress'); ?></h1>

    <?php settings_errors('cp_fec_settings'); ?>

    <form method="post" action="options.php">
        <?php settings_fields('cp_fec_settings'); ?>

        <!-- Committee Information -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php esc_html_e('Committee Information', 'campaignpress'); ?></h2>
            <p class="description"><?php esc_html_e('This information is required for FEC Form 3 and other official filings.', 'campaignpress'); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="committee_id"><?php esc_html_e('FEC Committee ID', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="committee_id" name="cp_fec_committee_info[committee_id]"
                               value="<?php echo esc_attr($committee_info['committee_id']); ?>" class="regular-text"
                               placeholder="C00123456">
                        <p class="description"><?php esc_html_e('Your 9-character FEC Committee ID (e.g., C00123456)', 'campaignpress'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="committee_name"><?php esc_html_e('Committee Name', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="committee_name" name="cp_fec_committee_info[committee_name]"
                               value="<?php echo esc_attr($committee_info['committee_name']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="committee_type"><?php esc_html_e('Committee Type', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <select id="committee_type" name="cp_fec_committee_info[committee_type]" class="regular-text">
                            <option value="candidate" <?php selected($committee_info['committee_type'], 'candidate'); ?>><?php esc_html_e('Candidate Committee', 'campaignpress'); ?></option>
                            <option value="pac" <?php selected($committee_info['committee_type'], 'pac'); ?>><?php esc_html_e('PAC', 'campaignpress'); ?></option>
                            <option value="party" <?php selected($committee_info['committee_type'], 'party'); ?>><?php esc_html_e('Party Committee', 'campaignpress'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="treasurer_name"><?php esc_html_e('Treasurer Name', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="treasurer_name" name="cp_fec_committee_info[treasurer_name]"
                               value="<?php echo esc_attr($committee_info['treasurer_name']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="street1"><?php esc_html_e('Street Address', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="street1" name="cp_fec_committee_info[street1]"
                               value="<?php echo esc_attr($committee_info['street1']); ?>" class="regular-text">
                        <br><br>
                        <input type="text" id="street2" name="cp_fec_committee_info[street2]"
                               value="<?php echo esc_attr($committee_info['street2']); ?>" class="regular-text"
                               placeholder="<?php esc_attr_e('Suite/Apt (optional)', 'campaignpress'); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="city"><?php esc_html_e('City', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="city" name="cp_fec_committee_info[city]"
                               value="<?php echo esc_attr($committee_info['city']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="state"><?php esc_html_e('State', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="state" name="cp_fec_committee_info[state]"
                               value="<?php echo esc_attr($committee_info['state']); ?>" style="width: 80px;" maxlength="2"
                               placeholder="CA">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="zip"><?php esc_html_e('ZIP Code', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="zip" name="cp_fec_committee_info[zip]"
                               value="<?php echo esc_attr($committee_info['zip']); ?>" style="width: 120px;">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Candidate Information (if candidate committee) -->
        <div class="card" style="max-width: 800px; margin-top: 20px;" id="candidate-info-section">
            <h2><?php esc_html_e('Candidate Information', 'campaignpress'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="candidate_name"><?php esc_html_e('Candidate Name', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="candidate_name" name="cp_fec_committee_info[candidate_name]"
                               value="<?php echo esc_attr($committee_info['candidate_name']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="candidate_office"><?php esc_html_e('Office Sought', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <select id="candidate_office" name="cp_fec_committee_info[candidate_office]" class="regular-text">
                            <option value="house" <?php selected($committee_info['candidate_office'], 'house'); ?>><?php esc_html_e('U.S. House', 'campaignpress'); ?></option>
                            <option value="senate" <?php selected($committee_info['candidate_office'], 'senate'); ?>><?php esc_html_e('U.S. Senate', 'campaignpress'); ?></option>
                            <option value="president" <?php selected($committee_info['candidate_office'], 'president'); ?>><?php esc_html_e('President', 'campaignpress'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="candidate_state"><?php esc_html_e('State', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="candidate_state" name="cp_fec_committee_info[candidate_state]"
                               value="<?php echo esc_attr($committee_info['candidate_state']); ?>" style="width: 80px;" maxlength="2">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="candidate_district"><?php esc_html_e('District', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="candidate_district" name="cp_fec_committee_info[candidate_district]"
                               value="<?php echo esc_attr($committee_info['candidate_district']); ?>" style="width: 80px;">
                        <p class="description"><?php esc_html_e('For House candidates only', 'campaignpress'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="election_cycle"><?php esc_html_e('Election Cycle', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="election_cycle" name="cp_fec_committee_info[election_cycle]"
                               value="<?php echo esc_attr($committee_info['election_cycle']); ?>" style="width: 100px;"
                               min="2020" max="2040">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Compliance Settings -->
        <div class="card" style="max-width: 800px; margin-top: 20px;">
            <h2><?php esc_html_e('Compliance Settings', 'campaignpress'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Automatic Limit Checking', 'campaignpress'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cp_fec_enable_auto_limits" value="1"
                                   <?php checked(get_option('cp_fec_enable_auto_limits', true)); ?>>
                            <?php esc_html_e('Automatically check contribution limits when recording donations', 'campaignpress'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Prohibited Source Detection', 'campaignpress'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cp_fec_enable_prohibited_source_check" value="1"
                                   <?php checked(get_option('cp_fec_enable_prohibited_source_check', true)); ?>>
                            <?php esc_html_e('Check for prohibited contribution sources (foreign nationals, corporations)', 'campaignpress'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('48-Hour Notice Alerts', 'campaignpress'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cp_fec_enable_48hour_alerts" value="1"
                                   <?php checked(get_option('cp_fec_enable_48hour_alerts', true)); ?>>
                            <?php esc_html_e('Send alerts for contributions requiring 48-hour notices', 'campaignpress'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cp_fec_alert_email"><?php esc_html_e('Alert Email', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="cp_fec_alert_email" name="cp_fec_alert_email"
                               value="<?php echo esc_attr(get_option('cp_fec_alert_email', get_option('admin_email'))); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Email address for compliance alerts and reminders', 'campaignpress'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cp_fec_audit_retention_years"><?php esc_html_e('Audit Log Retention', 'campaignpress'); ?></label>
                    </th>
                    <td>
                        <select id="cp_fec_audit_retention_years" name="cp_fec_audit_retention_years">
                            <option value="3" <?php selected(get_option('cp_fec_audit_retention_years', 3), 3); ?>><?php esc_html_e('3 years (FEC minimum)', 'campaignpress'); ?></option>
                            <option value="5" <?php selected(get_option('cp_fec_audit_retention_years', 3), 5); ?>><?php esc_html_e('5 years', 'campaignpress'); ?></option>
                            <option value="7" <?php selected(get_option('cp_fec_audit_retention_years', 3), 7); ?>><?php esc_html_e('7 years', 'campaignpress'); ?></option>
                            <option value="10" <?php selected(get_option('cp_fec_audit_retention_years', 3), 10); ?>><?php esc_html_e('10 years', 'campaignpress'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('FEC requires records be kept for at least 3 years after filing date', 'campaignpress'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    function toggleCandidateInfo() {
        if ($('#committee_type').val() === 'candidate') {
            $('#candidate-info-section').show();
        } else {
            $('#candidate-info-section').hide();
        }
    }
    toggleCandidateInfo();
    $('#committee_type').on('change', toggleCandidateInfo);
});
</script>
