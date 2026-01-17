<?php
/**
 * Admin Integrations Page View
 *
 * @package CampaignPress
 * @since 2.0.0
 */

defined('ABSPATH') || exit;
?>

<div class="wrap">
    <h1><?php echo esc_html__('Integrations', 'campaignpress'); ?></h1>
    <p><?php echo esc_html__('Manage your email and SMS integrations for campaign communications.', 'campaignpress'); ?></p>

    <div class="cp-integrations-container">
        <!-- Email Integrations -->
        <div class="cp-integrations-section">
            <h2><?php echo esc_html__('Email Integrations', 'campaignpress'); ?></h2>

            <?php if (!empty($email_platforms)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Platform', 'campaignpress'); ?></th>
                            <th><?php echo esc_html__('Status', 'campaignpress'); ?></th>
                            <th><?php echo esc_html__('Actions', 'campaignpress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($email_platforms as $platform_key => $platform_name) :
                            $is_connected = isset($email_integrations[$platform_key]) && !empty($email_integrations[$platform_key]['api_key']);
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($platform_name); ?></strong></td>
                            <td>
                                <?php if ($is_connected) : ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                    <?php echo esc_html__('Connected', 'campaignpress'); ?>
                                <?php else : ?>
                                    <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
                                    <?php echo esc_html__('Not Connected', 'campaignpress'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="button button-secondary cp-configure-integration"
                                        data-platform="<?php echo esc_attr($platform_key); ?>"
                                        data-type="email">
                                    <?php echo esc_html__('Configure', 'campaignpress'); ?>
                                </button>
                                <?php if ($is_connected) : ?>
                                    <button class="button cp-test-integration"
                                            data-platform="<?php echo esc_attr($platform_key); ?>"
                                            data-type="email">
                                        <?php echo esc_html__('Test Connection', 'campaignpress'); ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php echo esc_html__('No email platforms available.', 'campaignpress'); ?></p>
            <?php endif; ?>
        </div>

        <!-- SMS Integrations -->
        <div class="cp-integrations-section">
            <h2><?php echo esc_html__('SMS Integrations', 'campaignpress'); ?></h2>

            <?php if (!empty($sms_platforms)) : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Platform', 'campaignpress'); ?></th>
                            <th><?php echo esc_html__('Status', 'campaignpress'); ?></th>
                            <th><?php echo esc_html__('Actions', 'campaignpress'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sms_platforms as $platform_key => $platform_name) :
                            $is_connected = isset($sms_integrations[$platform_key]) && !empty($sms_integrations[$platform_key]['api_key']);
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($platform_name); ?></strong></td>
                            <td>
                                <?php if ($is_connected) : ?>
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                    <?php echo esc_html__('Connected', 'campaignpress'); ?>
                                <?php else : ?>
                                    <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
                                    <?php echo esc_html__('Not Connected', 'campaignpress'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="button button-secondary cp-configure-integration"
                                        data-platform="<?php echo esc_attr($platform_key); ?>"
                                        data-type="sms">
                                    <?php echo esc_html__('Configure', 'campaignpress'); ?>
                                </button>
                                <?php if ($is_connected) : ?>
                                    <button class="button cp-test-integration"
                                            data-platform="<?php echo esc_attr($platform_key); ?>"
                                            data-type="sms">
                                        <?php echo esc_html__('Test Connection', 'campaignpress'); ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p><?php echo esc_html__('No SMS platforms available.', 'campaignpress'); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <style>
        .cp-integrations-container {
            max-width: 1200px;
        }
        .cp-integrations-section {
            margin: 30px 0;
            padding: 20px;
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .cp-integrations-section h2 {
            margin-top: 0;
        }
        .cp-integrations-section .button {
            margin-right: 10px;
        }
    </style>
</div>
