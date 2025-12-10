<?php
/**
 * Admin Automation Workflows Page View
 *
 * @package CampaignPress
 * @since 2.0.0
 */

defined('ABSPATH') || exit;
?>

<div class="wrap">
    <h1><?php echo esc_html__('Automation Workflows', 'campaign-office'); ?></h1>
    <p><?php echo esc_html__('Create and manage automated email and SMS workflows for your campaign.', 'campaign-office'); ?></p>

    <div class="cp-automation-container">
        <div class="cp-automation-header">
            <button class="button button-primary" id="cp-create-workflow">
                <?php echo esc_html__('Create New Workflow', 'campaign-office'); ?>
            </button>
        </div>

        <?php if (!empty($workflows)) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Workflow Name', 'campaign-office'); ?></th>
                        <th><?php echo esc_html__('Trigger', 'campaign-office'); ?></th>
                        <th><?php echo esc_html__('Actions', 'campaign-office'); ?></th>
                        <th><?php echo esc_html__('Status', 'campaign-office'); ?></th>
                        <th><?php echo esc_html__('Manage', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($workflows as $workflow) :
                        $workflow_id = isset($workflow['id']) ? intval($workflow['id']) : 0;
                        $workflow_name = isset($workflow['name']) ? $workflow['name'] : __('Untitled Workflow', 'campaign-office');
                        $workflow_trigger = isset($workflow['trigger']) ? $workflow['trigger'] : __('Unknown', 'campaign-office');
                        $workflow_status = isset($workflow['status']) ? $workflow['status'] : 'inactive';
                        $action_count = isset($workflow['actions']) ? count($workflow['actions']) : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($workflow_name); ?></strong></td>
                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $workflow_trigger))); ?></td>
                        <td>
                            <?php
                            /* translators: %d: number of actions */
                            echo esc_html(sprintf(_n('%d action', '%d actions', $action_count, 'campaign-office'), $action_count));
                            ?>
                        </td>
                        <td>
                            <?php if ($workflow_status === 'active') : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                                <?php echo esc_html__('Active', 'campaign-office'); ?>
                            <?php else : ?>
                                <span class="dashicons dashicons-dismiss" style="color: #dc3232;"></span>
                                <?php echo esc_html__('Inactive', 'campaign-office'); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="button button-secondary cp-edit-workflow"
                                    data-workflow-id="<?php echo esc_attr($workflow_id); ?>">
                                <?php echo esc_html__('Edit', 'campaign-office'); ?>
                            </button>
                            <button class="button cp-toggle-workflow"
                                    data-workflow-id="<?php echo esc_attr($workflow_id); ?>"
                                    data-status="<?php echo esc_attr($workflow_status); ?>">
                                <?php echo $workflow_status === 'active' ? esc_html__('Deactivate', 'campaign-office') : esc_html__('Activate', 'campaign-office'); ?>
                            </button>
                            <button class="button cp-delete-workflow"
                                    data-workflow-id="<?php echo esc_attr($workflow_id); ?>">
                                <?php echo esc_html__('Delete', 'campaign-office'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="cp-no-workflows">
                <p><?php echo esc_html__('No workflows created yet.', 'campaign-office'); ?></p>
                <p><?php echo esc_html__('Create your first automation workflow to streamline your campaign communications.', 'campaign-office'); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .cp-automation-container {
            max-width: 1200px;
            margin-top: 20px;
        }
        .cp-automation-header {
            margin-bottom: 20px;
        }
        .cp-no-workflows {
            padding: 60px 20px;
            text-align: center;
            background: #fff;
            border: 1px solid #ccd0d4;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .cp-no-workflows p {
            font-size: 16px;
            color: #646970;
        }
        .wp-list-table td .button {
            margin-right: 5px;
        }
    </style>
</div>
