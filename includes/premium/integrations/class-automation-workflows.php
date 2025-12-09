<?php
/**
 * CampaignPress Automation Workflows
 *
 * Comprehensive campaign automation workflow system.
 * Trigger-based actions for email and SMS automation.
 *
 * @package CampaignPress
 * @subpackage Premium/Integrations
 * @since 2.0.0
 * @version 2.0.0
 *
 * Features:
 * - Visual workflow builder (trigger → conditions → actions)
 * - Email drip campaigns
 * - Welcome series for new volunteers
 * - Thank you emails for donations
 * - Event reminder automation
 * - Birthday/anniversary messages
 * - Re-engagement campaigns
 * - Conditional logic based on tags/segments
 * - A/B testing support
 * - Time delays and scheduling
 * - Multi-channel (email + SMS)
 *
 * Available Triggers:
 * - User registered
 * - Donation completed
 * - Volunteer signup
 * - Event registration
 * - Contact added
 * - Tag added/removed
 * - Segment changed
 * - Birthday/anniversary
 * - Inactivity period
 * - Custom date/time
 *
 * Available Actions:
 * - Send email
 * - Send SMS
 * - Add tag
 * - Remove tag
 * - Change segment
 * - Update custom field
 * - Create task
 * - Send webhook
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Automation Workflows Manager Class
 *
 * @since 2.0.0
 */
class CampaignPress_Automation_Workflows {

    /**
     * Singleton instance
     *
     * @var CampaignPress_Automation_Workflows
     */
    private static $instance = null;

    /**
     * Active workflows cache
     *
     * @var array
     */
    private $workflows = array();

    /**
     * Available triggers
     *
     * @var array
     */
    private $triggers = array();

    /**
     * Available actions
     *
     * @var array
     */
    private $actions = array();

    /**
     * Available conditions
     *
     * @var array
     */
    private $conditions = array();

    /**
     * Get singleton instance
     *
     * @return CampaignPress_Automation_Workflows
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * @since 2.0.0
     */
    private function __construct() {
        // Define available triggers, actions, and conditions
        $this->define_triggers();
        $this->define_actions();
        $this->define_conditions();

        // Load active workflows
        $this->load_workflows();

        // Initialize hooks
        $this->init_hooks();

        // Schedule cron tasks
        $this->schedule_tasks();
    }

    /**
     * Define available triggers
     *
     * @since 2.0.0
     */
    private function define_triggers() {
        $this->triggers = array(
            'user_registered' => array(
                'name' => __('User Registered', 'campaign-office'),
                'description' => __('Triggered when a new user registers', 'campaign-office'),
                'category' => 'user',
                'data_fields' => array('user_id', 'email', 'name'),
                'icon' => 'dashicons-admin-users'
            ),
            'donation_completed' => array(
                'name' => __('Donation Completed', 'campaign-office'),
                'description' => __('Triggered when a donation is completed', 'campaign-office'),
                'category' => 'donation',
                'data_fields' => array('donation_id', 'amount', 'donor_email', 'donor_name'),
                'icon' => 'dashicons-heart'
            ),
            'volunteer_signup' => array(
                'name' => __('Volunteer Signup', 'campaign-office'),
                'description' => __('Triggered when someone signs up as a volunteer', 'campaign-office'),
                'category' => 'volunteer',
                'data_fields' => array('volunteer_id', 'email', 'name', 'interests'),
                'icon' => 'dashicons-groups'
            ),
            'event_registration' => array(
                'name' => __('Event Registration', 'campaign-office'),
                'description' => __('Triggered when someone registers for an event', 'campaign-office'),
                'category' => 'event',
                'data_fields' => array('event_id', 'registration_id', 'attendee_email', 'event_date'),
                'icon' => 'dashicons-calendar-alt'
            ),
            'contact_added' => array(
                'name' => __('Contact Added', 'campaign-office'),
                'description' => __('Triggered when a new contact is added to CRM', 'campaign-office'),
                'category' => 'contact',
                'data_fields' => array('contact_id', 'email', 'name', 'source'),
                'icon' => 'dashicons-id'
            ),
            'tag_added' => array(
                'name' => __('Tag Added', 'campaign-office'),
                'description' => __('Triggered when a tag is added to a contact', 'campaign-office'),
                'category' => 'contact',
                'data_fields' => array('contact_id', 'tag_id', 'tag_name'),
                'icon' => 'dashicons-tag'
            ),
            'tag_removed' => array(
                'name' => __('Tag Removed', 'campaign-office'),
                'description' => __('Triggered when a tag is removed from a contact', 'campaign-office'),
                'category' => 'contact',
                'data_fields' => array('contact_id', 'tag_id', 'tag_name'),
                'icon' => 'dashicons-tag'
            ),
            'segment_changed' => array(
                'name' => __('Segment Changed', 'campaign-office'),
                'description' => __('Triggered when a contact moves to a different segment', 'campaign-office'),
                'category' => 'contact',
                'data_fields' => array('contact_id', 'old_segment', 'new_segment'),
                'icon' => 'dashicons-networking'
            ),
            'birthday' => array(
                'name' => __('Birthday', 'campaign-office'),
                'description' => __('Triggered on contact\'s birthday', 'campaign-office'),
                'category' => 'date',
                'data_fields' => array('contact_id', 'email', 'name', 'birthdate'),
                'icon' => 'dashicons-smiley'
            ),
            'anniversary' => array(
                'name' => __('Anniversary', 'campaign-office'),
                'description' => __('Triggered on contact\'s anniversary (e.g., signup date)', 'campaign-office'),
                'category' => 'date',
                'data_fields' => array('contact_id', 'email', 'name', 'anniversary_date', 'years'),
                'icon' => 'dashicons-awards'
            ),
            'inactivity' => array(
                'name' => __('Inactivity Period', 'campaign-office'),
                'description' => __('Triggered after a period of contact inactivity', 'campaign-office'),
                'category' => 'engagement',
                'data_fields' => array('contact_id', 'email', 'days_inactive'),
                'icon' => 'dashicons-clock'
            ),
            'email_opened' => array(
                'name' => __('Email Opened', 'campaign-office'),
                'description' => __('Triggered when a contact opens an email', 'campaign-office'),
                'category' => 'engagement',
                'data_fields' => array('contact_id', 'email_id', 'campaign_id'),
                'icon' => 'dashicons-email-alt'
            ),
            'email_clicked' => array(
                'name' => __('Email Link Clicked', 'campaign-office'),
                'description' => __('Triggered when a contact clicks a link in an email', 'campaign-office'),
                'category' => 'engagement',
                'data_fields' => array('contact_id', 'email_id', 'campaign_id', 'url'),
                'icon' => 'dashicons-external'
            ),
            'sms_replied' => array(
                'name' => __('SMS Reply Received', 'campaign-office'),
                'description' => __('Triggered when a contact replies to an SMS', 'campaign-office'),
                'category' => 'engagement',
                'data_fields' => array('contact_id', 'phone', 'message'),
                'icon' => 'dashicons-smartphone'
            )
        );

        // Allow filtering of triggers
        $this->triggers = apply_filters('campaignpress_automation_triggers', $this->triggers);
    }

    /**
     * Define available actions
     *
     * @since 2.0.0
     */
    private function define_actions() {
        $this->actions = array(
            'send_email' => array(
                'name' => __('Send Email', 'campaign-office'),
                'description' => __('Send an email to the contact', 'campaign-office'),
                'category' => 'communication',
                'settings' => array(
                    'integration_id' => array(
                        'label' => __('Email Integration', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    ),
                    'template_id' => array(
                        'label' => __('Email Template', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    ),
                    'subject' => array(
                        'label' => __('Subject', 'campaign-office'),
                        'type' => 'text',
                        'required' => true,
                        'supports_merge_tags' => true
                    ),
                    'from_name' => array(
                        'label' => __('From Name', 'campaign-office'),
                        'type' => 'text',
                        'required' => false
                    ),
                    'from_email' => array(
                        'label' => __('From Email', 'campaign-office'),
                        'type' => 'email',
                        'required' => false
                    )
                ),
                'icon' => 'dashicons-email'
            ),
            'send_sms' => array(
                'name' => __('Send SMS', 'campaign-office'),
                'description' => __('Send an SMS message to the contact', 'campaign-office'),
                'category' => 'communication',
                'settings' => array(
                    'integration_id' => array(
                        'label' => __('SMS Integration', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    ),
                    'message' => array(
                        'label' => __('Message', 'campaign-office'),
                        'type' => 'textarea',
                        'required' => true,
                        'supports_merge_tags' => true,
                        'max_length' => 160
                    )
                ),
                'icon' => 'dashicons-smartphone'
            ),
            'add_tag' => array(
                'name' => __('Add Tag', 'campaign-office'),
                'description' => __('Add a tag to the contact', 'campaign-office'),
                'category' => 'contact',
                'settings' => array(
                    'tag_id' => array(
                        'label' => __('Tag', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    )
                ),
                'icon' => 'dashicons-tag'
            ),
            'remove_tag' => array(
                'name' => __('Remove Tag', 'campaign-office'),
                'description' => __('Remove a tag from the contact', 'campaign-office'),
                'category' => 'contact',
                'settings' => array(
                    'tag_id' => array(
                        'label' => __('Tag', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    )
                ),
                'icon' => 'dashicons-tag'
            ),
            'change_segment' => array(
                'name' => __('Change Segment', 'campaign-office'),
                'description' => __('Move contact to a different segment', 'campaign-office'),
                'category' => 'contact',
                'settings' => array(
                    'segment_id' => array(
                        'label' => __('Segment', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    )
                ),
                'icon' => 'dashicons-networking'
            ),
            'update_field' => array(
                'name' => __('Update Custom Field', 'campaign-office'),
                'description' => __('Update a custom field value', 'campaign-office'),
                'category' => 'contact',
                'settings' => array(
                    'field_name' => array(
                        'label' => __('Field Name', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    ),
                    'field_value' => array(
                        'label' => __('Field Value', 'campaign-office'),
                        'type' => 'text',
                        'required' => true,
                        'supports_merge_tags' => true
                    )
                ),
                'icon' => 'dashicons-edit'
            ),
            'create_task' => array(
                'name' => __('Create Task', 'campaign-office'),
                'description' => __('Create a task for a team member', 'campaign-office'),
                'category' => 'workflow',
                'settings' => array(
                    'task_title' => array(
                        'label' => __('Task Title', 'campaign-office'),
                        'type' => 'text',
                        'required' => true,
                        'supports_merge_tags' => true
                    ),
                    'task_description' => array(
                        'label' => __('Description', 'campaign-office'),
                        'type' => 'textarea',
                        'required' => false,
                        'supports_merge_tags' => true
                    ),
                    'assigned_to' => array(
                        'label' => __('Assign To', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    ),
                    'due_date' => array(
                        'label' => __('Due Date', 'campaign-office'),
                        'type' => 'date',
                        'required' => false
                    )
                ),
                'icon' => 'dashicons-yes'
            ),
            'send_webhook' => array(
                'name' => __('Send Webhook', 'campaign-office'),
                'description' => __('Send data to an external URL', 'campaign-office'),
                'category' => 'integration',
                'settings' => array(
                    'webhook_url' => array(
                        'label' => __('Webhook URL', 'campaign-office'),
                        'type' => 'url',
                        'required' => true
                    ),
                    'method' => array(
                        'label' => __('HTTP Method', 'campaign-office'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array('POST' => 'POST', 'GET' => 'GET', 'PUT' => 'PUT')
                    ),
                    'payload' => array(
                        'label' => __('Payload (JSON)', 'campaign-office'),
                        'type' => 'textarea',
                        'required' => false,
                        'supports_merge_tags' => true
                    )
                ),
                'icon' => 'dashicons-rest-api'
            ),
            'wait' => array(
                'name' => __('Wait/Delay', 'campaign-office'),
                'description' => __('Wait for a specified time before continuing', 'campaign-office'),
                'category' => 'workflow',
                'settings' => array(
                    'delay_value' => array(
                        'label' => __('Delay Amount', 'campaign-office'),
                        'type' => 'number',
                        'required' => true
                    ),
                    'delay_unit' => array(
                        'label' => __('Delay Unit', 'campaign-office'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array(
                            'minutes' => __('Minutes', 'campaign-office'),
                            'hours' => __('Hours', 'campaign-office'),
                            'days' => __('Days', 'campaign-office'),
                            'weeks' => __('Weeks', 'campaign-office')
                        )
                    )
                ),
                'icon' => 'dashicons-clock'
            )
        );

        // Allow filtering of actions
        $this->actions = apply_filters('campaignpress_automation_actions', $this->actions);
    }

    /**
     * Define available conditions
     *
     * @since 2.0.0
     */
    private function define_conditions() {
        $this->conditions = array(
            'has_tag' => array(
                'name' => __('Has Tag', 'campaign-office'),
                'description' => __('Contact has a specific tag', 'campaign-office'),
                'settings' => array(
                    'tag_id' => array(
                        'label' => __('Tag', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    )
                )
            ),
            'in_segment' => array(
                'name' => __('In Segment', 'campaign-office'),
                'description' => __('Contact is in a specific segment', 'campaign-office'),
                'settings' => array(
                    'segment_id' => array(
                        'label' => __('Segment', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    )
                )
            ),
            'field_equals' => array(
                'name' => __('Field Equals', 'campaign-office'),
                'description' => __('Custom field equals a value', 'campaign-office'),
                'settings' => array(
                    'field_name' => array(
                        'label' => __('Field Name', 'campaign-office'),
                        'type' => 'select',
                        'required' => true
                    ),
                    'field_value' => array(
                        'label' => __('Value', 'campaign-office'),
                        'type' => 'text',
                        'required' => true
                    )
                )
            ),
            'donation_amount' => array(
                'name' => __('Donation Amount', 'campaign-office'),
                'description' => __('Donation amount meets criteria', 'campaign-office'),
                'settings' => array(
                    'operator' => array(
                        'label' => __('Operator', 'campaign-office'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array(
                            'greater_than' => __('Greater Than', 'campaign-office'),
                            'less_than' => __('Less Than', 'campaign-office'),
                            'equals' => __('Equals', 'campaign-office')
                        )
                    ),
                    'amount' => array(
                        'label' => __('Amount', 'campaign-office'),
                        'type' => 'number',
                        'required' => true
                    )
                )
            ),
            'email_status' => array(
                'name' => __('Email Status', 'campaign-office'),
                'description' => __('Email subscription status', 'campaign-office'),
                'settings' => array(
                    'status' => array(
                        'label' => __('Status', 'campaign-office'),
                        'type' => 'select',
                        'required' => true,
                        'options' => array(
                            'subscribed' => __('Subscribed', 'campaign-office'),
                            'unsubscribed' => __('Unsubscribed', 'campaign-office'),
                            'bounced' => __('Bounced', 'campaign-office')
                        )
                    )
                )
            )
        );

        // Allow filtering of conditions
        $this->conditions = apply_filters('campaignpress_automation_conditions', $this->conditions);
    }

    /**
     * Initialize hooks
     *
     * @since 2.0.0
     */
    private function init_hooks() {
        // Listen for automation triggers
        add_action('campaignpress_trigger_automation', array($this, 'process_trigger'), 10, 2);

        // Scheduled workflow processing
        add_action('campaignpress_process_workflow_queue', array($this, 'process_workflow_queue'));

        // Date-based triggers
        add_action('campaignpress_check_birthday_triggers', array($this, 'check_birthday_triggers'));
        add_action('campaignpress_check_anniversary_triggers', array($this, 'check_anniversary_triggers'));
        add_action('campaignpress_check_inactivity_triggers', array($this, 'check_inactivity_triggers'));
    }

    /**
     * Load active workflows from database
     *
     * @since 2.0.0
     */
    private function load_workflows() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_automation_workflows';

        $results = $wpdb->get_results(
            "SELECT * FROM {$table_name} WHERE status = 'active'",
            ARRAY_A
        );

        foreach ($results as $row) {
            $this->workflows[$row['id']] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'trigger' => $row['trigger'],
                'conditions' => json_decode($row['conditions'], true),
                'actions' => json_decode($row['actions'], true),
                'settings' => json_decode($row['settings'], true),
                'created_at' => $row['created_at']
            );
        }
    }

    /**
     * Get all workflows
     *
     * @return array
     * @since 2.0.0
     */
    public function get_all_workflows() {
        return $this->workflows;
    }

    /**
     * Get available triggers
     *
     * @return array
     * @since 2.0.0
     */
    public function get_triggers() {
        return $this->triggers;
    }

    /**
     * Get available actions
     *
     * @return array
     * @since 2.0.0
     */
    public function get_actions() {
        return $this->actions;
    }

    /**
     * Get available conditions
     *
     * @return array
     * @since 2.0.0
     */
    public function get_conditions() {
        return $this->conditions;
    }

    /**
     * Save workflow
     *
     * @param int $workflow_id Workflow ID or 0 for new
     * @param string $name Workflow name
     * @param string $trigger Trigger type
     * @param array $conditions Conditions array
     * @param array $actions Actions array
     * @param array $settings Workflow settings
     * @return int|bool Workflow ID on success, false on failure
     * @since 2.0.0
     */
    public function save_workflow($workflow_id, $name, $trigger, $conditions, $actions, $settings) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_automation_workflows';

        $data = array(
            'name' => $name,
            'trigger' => $trigger,
            'conditions' => wp_json_encode($conditions),
            'actions' => wp_json_encode($actions),
            'settings' => wp_json_encode($settings),
            'status' => 'active'
        );

        if (empty($workflow_id)) {
            // Insert new workflow
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data);

            if ($result) {
                $workflow_id = $wpdb->insert_id;

                // Log creation
                campaignpress_integrations()->log_event('automation_workflow_created', array(
                    'workflow_id' => $workflow_id,
                    'trigger' => $trigger
                ));

                // Reload workflows
                $this->load_workflows();

                return $workflow_id;
            }
        } else {
            // Update existing workflow
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $workflow_id)
            );

            if ($result !== false) {
                // Log update
                campaignpress_integrations()->log_event('automation_workflow_updated', array(
                    'workflow_id' => $workflow_id,
                    'trigger' => $trigger
                ));

                // Reload workflows
                $this->load_workflows();

                return $workflow_id;
            }
        }

        return false;
    }

    /**
     * Delete workflow
     *
     * @param int $workflow_id Workflow ID
     * @return bool Success status
     * @since 2.0.0
     */
    public function delete_workflow($workflow_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_automation_workflows';

        $result = $wpdb->delete($table_name, array('id' => $workflow_id));

        if ($result) {
            // Log deletion
            campaignpress_integrations()->log_event('automation_workflow_deleted', array(
                'workflow_id' => $workflow_id
            ));

            // Reload workflows
            $this->load_workflows();

            return true;
        }

        return false;
    }

    /**
     * Process automation trigger
     *
     * @param string $trigger_type Trigger type
     * @param array $data Trigger data
     * @since 2.0.0
     */
    public function process_trigger($trigger_type, $data) {
        // Find workflows matching this trigger
        $matching_workflows = array();

        foreach ($this->workflows as $workflow) {
            if ($workflow['trigger'] === $trigger_type) {
                $matching_workflows[] = $workflow;
            }
        }

        if (empty($matching_workflows)) {
            return;
        }

        // Process each matching workflow
        foreach ($matching_workflows as $workflow) {
            $this->execute_workflow($workflow, $data);
        }
    }

    /**
     * Execute workflow
     *
     * @param array $workflow Workflow configuration
     * @param array $trigger_data Trigger data
     * @since 2.0.0
     */
    private function execute_workflow($workflow, $trigger_data) {
        // Check if conditions are met
        if (!$this->check_conditions($workflow['conditions'], $trigger_data)) {
            return;
        }

        // Get contact data
        $contact = $this->get_contact_from_trigger_data($trigger_data);

        if (!$contact) {
            return;
        }

        // Check A/B test split if configured
        if (!empty($workflow['settings']['ab_test'])) {
            if (!$this->check_ab_test($workflow, $contact)) {
                return;
            }
        }

        // Queue actions for execution
        $this->queue_workflow_actions($workflow['id'], $contact, $workflow['actions'], $trigger_data);

        // Log execution
        campaignpress_integrations()->log_event('automation_workflow_executed', array(
            'workflow_id' => $workflow['id'],
            'contact_id' => $contact['id'],
            'trigger_data' => $trigger_data
        ));
    }

    /**
     * Check if conditions are met
     *
     * @param array $conditions Conditions array
     * @param array $trigger_data Trigger data
     * @return bool True if all conditions are met
     * @since 2.0.0
     */
    private function check_conditions($conditions, $trigger_data) {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $type = $condition['type'] ?? '';
            $settings = $condition['settings'] ?? array();

            if (!$this->evaluate_condition($type, $settings, $trigger_data)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate single condition
     *
     * @param string $type Condition type
     * @param array $settings Condition settings
     * @param array $trigger_data Trigger data
     * @return bool True if condition is met
     * @since 2.0.0
     */
    private function evaluate_condition($type, $settings, $trigger_data) {
        $contact = $this->get_contact_from_trigger_data($trigger_data);

        if (!$contact) {
            return false;
        }

        switch ($type) {
            case 'has_tag':
                return $this->contact_has_tag($contact['id'], $settings['tag_id']);

            case 'in_segment':
                return $contact['segment_id'] == $settings['segment_id'];

            case 'field_equals':
                $field_value = $contact[$settings['field_name']] ?? '';
                return $field_value == $settings['field_value'];

            case 'donation_amount':
                $amount = $trigger_data['amount'] ?? 0;
                $operator = $settings['operator'] ?? 'equals';
                $compare_amount = $settings['amount'] ?? 0;

                switch ($operator) {
                    case 'greater_than':
                        return $amount > $compare_amount;
                    case 'less_than':
                        return $amount < $compare_amount;
                    case 'equals':
                        return $amount == $compare_amount;
                }
                break;

            case 'email_status':
                return ($contact['email_status'] ?? '') === $settings['status'];

            default:
                return apply_filters('campaignpress_evaluate_condition_' . $type, false, $settings, $trigger_data, $contact);
        }

        return false;
    }

    /**
     * Queue workflow actions
     *
     * @param int $workflow_id Workflow ID
     * @param array $contact Contact data
     * @param array $actions Actions array
     * @param array $trigger_data Trigger data
     * @since 2.0.0
     */
    private function queue_workflow_actions($workflow_id, $contact, $actions, $trigger_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_automation_queue';

        $scheduled_time = current_time('mysql');
        $delay_offset = 0;

        foreach ($actions as $index => $action) {
            $action_type = $action['type'] ?? '';
            $settings = $action['settings'] ?? array();

            // Calculate scheduled time with delays
            if ($action_type === 'wait') {
                $delay_offset += $this->calculate_delay($settings);
                continue; // Don't queue wait actions
            }

            $execute_at = date('Y-m-d H:i:s', strtotime($scheduled_time) + $delay_offset);

            // Insert into queue
            $wpdb->insert(
                $table_name,
                array(
                    'workflow_id' => $workflow_id,
                    'contact_id' => $contact['id'],
                    'action_type' => $action_type,
                    'action_settings' => wp_json_encode($settings),
                    'trigger_data' => wp_json_encode($trigger_data),
                    'execute_at' => $execute_at,
                    'status' => 'pending',
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        }
    }

    /**
     * Process workflow queue
     *
     * Processes queued workflow actions that are due.
     *
     * @since 2.0.0
     */
    public function process_workflow_queue() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_automation_queue';

        // Get pending actions that are due
        $actions = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                WHERE status = 'pending'
                AND execute_at <= %s
                ORDER BY execute_at ASC
                LIMIT 50",
                current_time('mysql')
            ),
            ARRAY_A
        );

        foreach ($actions as $queued_action) {
            $this->execute_action(
                $queued_action['action_type'],
                json_decode($queued_action['action_settings'], true),
                $queued_action['contact_id'],
                json_decode($queued_action['trigger_data'], true)
            );

            // Mark as completed
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'completed',
                    'executed_at' => current_time('mysql')
                ),
                array('id' => $queued_action['id']),
                array('%s', '%s'),
                array('%d')
            );
        }
    }

    /**
     * Execute workflow action
     *
     * @param string $action_type Action type
     * @param array $settings Action settings
     * @param int $contact_id Contact ID
     * @param array $trigger_data Trigger data
     * @return bool Success status
     * @since 2.0.0
     */
    private function execute_action($action_type, $settings, $contact_id, $trigger_data) {
        // Get contact data
        $contact = $this->get_contact($contact_id);

        if (!$contact) {
            return false;
        }

        // Replace merge tags in settings
        $settings = $this->replace_merge_tags($settings, $contact, $trigger_data);

        switch ($action_type) {
            case 'send_email':
                return $this->action_send_email($contact, $settings);

            case 'send_sms':
                return $this->action_send_sms($contact, $settings);

            case 'add_tag':
                return $this->action_add_tag($contact_id, $settings);

            case 'remove_tag':
                return $this->action_remove_tag($contact_id, $settings);

            case 'change_segment':
                return $this->action_change_segment($contact_id, $settings);

            case 'update_field':
                return $this->action_update_field($contact_id, $settings);

            case 'create_task':
                return $this->action_create_task($contact, $settings);

            case 'send_webhook':
                return $this->action_send_webhook($contact, $settings, $trigger_data);

            default:
                return apply_filters('campaignpress_execute_action_' . $action_type, false, $contact, $settings, $trigger_data);
        }
    }

    /**
     * Action: Send Email
     *
     * @param array $contact Contact data
     * @param array $settings Action settings
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_send_email($contact, $settings) {
        $integration_id = $settings['integration_id'] ?? '';
        $template_id = $settings['template_id'] ?? '';
        $subject = $settings['subject'] ?? '';

        // Get email integrations handler
        $email_integrations = campaignpress_integrations()->get_email_integrations();

        // Send email via integration
        // Implementation would call email platform API
        return true;
    }

    /**
     * Action: Send SMS
     *
     * @param array $contact Contact data
     * @param array $settings Action settings
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_send_sms($contact, $settings) {
        $integration_id = $settings['integration_id'] ?? '';
        $message = $settings['message'] ?? '';
        $phone = $contact['phone'] ?? '';

        if (empty($phone)) {
            return false;
        }

        // Get SMS integrations handler
        $sms_integrations = campaignpress_integrations()->get_sms_integrations();

        // Send SMS
        return $sms_integrations->send_sms($phone, $message, array('integration_id' => $integration_id));
    }

    /**
     * Action: Add Tag
     *
     * @param int $contact_id Contact ID
     * @param array $settings Action settings
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_add_tag($contact_id, $settings) {
        $tag_id = $settings['tag_id'] ?? 0;

        if (empty($tag_id)) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contact_tags';

        return $wpdb->insert(
            $table_name,
            array('contact_id' => $contact_id, 'tag_id' => $tag_id),
            array('%d', '%d')
        );
    }

    /**
     * Action: Remove Tag
     *
     * @param int $contact_id Contact ID
     * @param array $settings Action settings
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_remove_tag($contact_id, $settings) {
        $tag_id = $settings['tag_id'] ?? 0;

        if (empty($tag_id)) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contact_tags';

        return $wpdb->delete(
            $table_name,
            array('contact_id' => $contact_id, 'tag_id' => $tag_id),
            array('%d', '%d')
        );
    }

    /**
     * Action: Change Segment
     *
     * @param int $contact_id Contact ID
     * @param array $settings Action settings
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_change_segment($contact_id, $settings) {
        $segment_id = $settings['segment_id'] ?? 0;

        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contacts';

        return $wpdb->update(
            $table_name,
            array('segment_id' => $segment_id),
            array('id' => $contact_id),
            array('%d'),
            array('%d')
        );
    }

    /**
     * Action: Update Field
     *
     * @param int $contact_id Contact ID
     * @param array $settings Action settings
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_update_field($contact_id, $settings) {
        $field_name = $settings['field_name'] ?? '';
        $field_value = $settings['field_value'] ?? '';

        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contacts';

        return $wpdb->update(
            $table_name,
            array($field_name => $field_value),
            array('id' => $contact_id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * Action: Create Task
     *
     * @param array $contact Contact data
     * @param array $settings Action settings
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_create_task($contact, $settings) {
        // Create task in CRM or task management system
        // Implementation specific to task system
        return true;
    }

    /**
     * Action: Send Webhook
     *
     * @param array $contact Contact data
     * @param array $settings Action settings
     * @param array $trigger_data Trigger data
     * @return bool Success status
     * @since 2.0.0
     */
    private function action_send_webhook($contact, $settings, $trigger_data) {
        $webhook_url = $settings['webhook_url'] ?? '';
        $method = $settings['method'] ?? 'POST';
        $payload = $settings['payload'] ?? '';

        if (empty($webhook_url)) {
            return false;
        }

        // Parse JSON payload
        $data = json_decode($payload, true);
        if (!$data) {
            $data = array(
                'contact' => $contact,
                'trigger_data' => $trigger_data
            );
        }

        // Send webhook
        $response = wp_remote_request($webhook_url, array(
            'method' => $method,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode($data),
            'timeout' => 15
        ));

        return !is_wp_error($response);
    }

    /**
     * Calculate delay in seconds
     *
     * @param array $settings Delay settings
     * @return int Delay in seconds
     * @since 2.0.0
     */
    private function calculate_delay($settings) {
        $value = $settings['delay_value'] ?? 0;
        $unit = $settings['delay_unit'] ?? 'minutes';

        switch ($unit) {
            case 'minutes':
                return $value * 60;
            case 'hours':
                return $value * 3600;
            case 'days':
                return $value * 86400;
            case 'weeks':
                return $value * 604800;
            default:
                return 0;
        }
    }

    /**
     * Replace merge tags in content
     *
     * @param array|string $content Content with merge tags
     * @param array $contact Contact data
     * @param array $trigger_data Trigger data
     * @return array|string Content with tags replaced
     * @since 2.0.0
     */
    private function replace_merge_tags($content, $contact, $trigger_data) {
        if (is_array($content)) {
            foreach ($content as $key => $value) {
                $content[$key] = $this->replace_merge_tags($value, $contact, $trigger_data);
            }
            return $content;
        }

        // Replace contact merge tags
        $content = str_replace('{{contact.name}}', $contact['name'] ?? '', $content);
        $content = str_replace('{{contact.email}}', $contact['email'] ?? '', $content);
        $content = str_replace('{{contact.first_name}}', $contact['first_name'] ?? '', $content);
        $content = str_replace('{{contact.last_name}}', $contact['last_name'] ?? '', $content);

        // Replace trigger data merge tags
        foreach ($trigger_data as $key => $value) {
            if (is_scalar($value)) {
                $content = str_replace('{{trigger.' . $key . '}}', $value, $content);
            }
        }

        return $content;
    }

    /**
     * Check A/B test assignment
     *
     * @param array $workflow Workflow configuration
     * @param array $contact Contact data
     * @return bool True if contact should receive this variant
     * @since 2.0.0
     */
    private function check_ab_test($workflow, $contact) {
        $ab_settings = $workflow['settings']['ab_test'];
        $split_percentage = $ab_settings['split_percentage'] ?? 50;

        // Use contact ID to determine variant (consistent assignment)
        $hash = md5($contact['id'] . $workflow['id']);
        $number = hexdec(substr($hash, 0, 8));
        $percentage = ($number % 100) + 1;

        return $percentage <= $split_percentage;
    }

    /**
     * Check birthday triggers
     *
     * Scheduled task to check for birthdays today.
     *
     * @since 2.0.0
     */
    public function check_birthday_triggers() {
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        // Get contacts with birthdays today
        $today = date('m-d');
        $contacts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$contacts_table} WHERE DATE_FORMAT(birthdate, '%%m-%%d') = %s",
            $today
        ), ARRAY_A);

        foreach ($contacts as $contact) {
            do_action('campaignpress_trigger_automation', 'birthday', array(
                'contact_id' => $contact['id'],
                'email' => $contact['email'],
                'name' => $contact['name'],
                'birthdate' => $contact['birthdate']
            ));
        }
    }

    /**
     * Check anniversary triggers
     *
     * Scheduled task to check for anniversaries today.
     *
     * @since 2.0.0
     */
    public function check_anniversary_triggers() {
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        // Get contacts with anniversaries today (using created_at as signup anniversary)
        $today = date('m-d');
        $contacts = $wpdb->get_results($wpdb->prepare(
            "SELECT *, TIMESTAMPDIFF(YEAR, created_at, NOW()) as years
            FROM {$contacts_table}
            WHERE DATE_FORMAT(created_at, '%%m-%%d') = %s
            AND TIMESTAMPDIFF(YEAR, created_at, NOW()) > 0",
            $today
        ), ARRAY_A);

        foreach ($contacts as $contact) {
            do_action('campaignpress_trigger_automation', 'anniversary', array(
                'contact_id' => $contact['id'],
                'email' => $contact['email'],
                'name' => $contact['name'],
                'anniversary_date' => $contact['created_at'],
                'years' => $contact['years']
            ));
        }
    }

    /**
     * Check inactivity triggers
     *
     * Scheduled task to check for inactive contacts.
     *
     * @since 2.0.0
     */
    public function check_inactivity_triggers() {
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'campaignpress_crm_contacts';

        // Get contacts inactive for 30+ days
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-30 days'));
        $contacts = $wpdb->get_results($wpdb->prepare(
            "SELECT *, DATEDIFF(NOW(), last_activity_at) as days_inactive
            FROM {$contacts_table}
            WHERE last_activity_at < %s
            AND email_status = 'subscribed'",
            $cutoff_date
        ), ARRAY_A);

        foreach ($contacts as $contact) {
            do_action('campaignpress_trigger_automation', 'inactivity', array(
                'contact_id' => $contact['id'],
                'email' => $contact['email'],
                'days_inactive' => $contact['days_inactive']
            ));
        }
    }

    /**
     * Schedule cron tasks
     *
     * @since 2.0.0
     */
    private function schedule_tasks() {
        // Process workflow queue every 5 minutes
        if (!wp_next_scheduled('campaignpress_process_workflow_queue')) {
            wp_schedule_event(time(), 'five_minutes', 'campaignpress_process_workflow_queue');
        }

        // Check birthday triggers daily
        if (!wp_next_scheduled('campaignpress_check_birthday_triggers')) {
            wp_schedule_event(strtotime('today 8:00am'), 'daily', 'campaignpress_check_birthday_triggers');
        }

        // Check anniversary triggers daily
        if (!wp_next_scheduled('campaignpress_check_anniversary_triggers')) {
            wp_schedule_event(strtotime('today 8:00am'), 'daily', 'campaignpress_check_anniversary_triggers');
        }

        // Check inactivity triggers daily
        if (!wp_next_scheduled('campaignpress_check_inactivity_triggers')) {
            wp_schedule_event(strtotime('today 9:00am'), 'daily', 'campaignpress_check_inactivity_triggers');
        }
    }

    /**
     * Get contact from trigger data
     *
     * @param array $trigger_data Trigger data
     * @return array|null Contact data
     * @since 2.0.0
     */
    private function get_contact_from_trigger_data($trigger_data) {
        $contact_id = $trigger_data['contact_id'] ?? 0;
        $email = $trigger_data['email'] ?? '';

        if (!empty($contact_id)) {
            return $this->get_contact($contact_id);
        } elseif (!empty($email)) {
            return $this->get_contact_by_email($email);
        }

        return null;
    }

    /**
     * Get contact by ID
     *
     * @param int $contact_id Contact ID
     * @return array|null Contact data
     * @since 2.0.0
     */
    private function get_contact($contact_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contacts';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $contact_id
        ), ARRAY_A);
    }

    /**
     * Get contact by email
     *
     * @param string $email Email address
     * @return array|null Contact data
     * @since 2.0.0
     */
    private function get_contact_by_email($email) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contacts';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE email = %s",
            $email
        ), ARRAY_A);
    }

    /**
     * Check if contact has tag
     *
     * @param int $contact_id Contact ID
     * @param int $tag_id Tag ID
     * @return bool True if contact has tag
     * @since 2.0.0
     */
    private function contact_has_tag($contact_id, $tag_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_crm_contact_tags';

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} WHERE contact_id = %d AND tag_id = %d",
            $contact_id,
            $tag_id
        ));

        return $count > 0;
    }
}
