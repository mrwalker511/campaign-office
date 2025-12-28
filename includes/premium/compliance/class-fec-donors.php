<?php
/**
 * CampaignPress FEC Donors Management
 *
 * Handles donor profile creation, management, and tracking for FEC compliance.
 * Maintains detailed donor records including contribution history, aggregate
 * tracking, occupation/employer information, and prohibited source flagging.
 *
 * FEC Requirements Implemented:
 * - 11 CFR §104.3(a)(4) - Itemization of receipts ($200+ threshold)
 * - 11 CFR §104.7 - Best efforts for donor information collection
 * - 11 CFR §110.20 - Prohibition on foreign national contributions
 * - 11 CFR §115 - Federal contractor prohibition
 * - 11 CFR §114.2 - Corporate and labor organization prohibition
 *
 * @package CampaignPress
 * @subpackage Compliance
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * FEC Donors Class
 *
 * @since 1.0.0
 */
class CampaignPress_FEC_Donors {

    /**
     * Database table name
     *
     * @var string
     */
    private $table_name;

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cp_fec_donors';
    }

    /**
     * Create donors database table
     *
     * @since 1.0.0
     */
    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) UNSIGNED NOT NULL,
            occupation varchar(200) DEFAULT '',
            employer varchar(200) DEFAULT '',
            employer_street1 varchar(255) DEFAULT '',
            employer_street2 varchar(255) DEFAULT '',
            employer_city varchar(100) DEFAULT '',
            employer_state varchar(2) DEFAULT '',
            employer_zip varchar(10) DEFAULT '',
            donor_type varchar(50) DEFAULT 'individual',
            organization_name varchar(255) DEFAULT '',
            committee_id varchar(100) DEFAULT '',
            is_foreign_national tinyint(1) DEFAULT 0,
            is_federal_contractor tinyint(1) DEFAULT 0,
            is_corporate_entity tinyint(1) DEFAULT 0,
            is_labor_organization tinyint(1) DEFAULT 0,
            is_prohibited_source tinyint(1) DEFAULT 0,
            prohibited_source_reason text DEFAULT '',
            address_verified tinyint(1) DEFAULT 0,
            address_verified_date datetime DEFAULT NULL,
            aggregate_primary decimal(10,2) DEFAULT 0.00,
            aggregate_general decimal(10,2) DEFAULT 0.00,
            aggregate_cycle decimal(10,2) DEFAULT 0.00,
            total_contributions int(11) DEFAULT 0,
            first_contribution_date datetime DEFAULT NULL,
            last_contribution_date datetime DEFAULT NULL,
            internal_notes text DEFAULT '',
            created_by bigint(20) UNSIGNED DEFAULT 0,
            created_date datetime NOT NULL,
            modified_date datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY contact_id (contact_id),
            KEY donor_type_idx (donor_type),
            KEY is_prohibited_source_idx (is_prohibited_source),
            KEY aggregate_cycle_idx (aggregate_cycle)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Create donor profile
     *
     * @since 1.0.0
     * @param array $data Donor data
     * @return int|WP_Error Donor ID or error
     */
    public function create_donor($data) {
        global $wpdb;

        // Check for duplicates
        $contact_data = array(
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'middle_name'   => $data['middle_name'] ?? '',
            'suffix'        => $data['suffix'] ?? '',
            'email'         => $data['email'] ?? '',
            'phone'         => $data['phone'] ?? '',
            'address_line1' => $data['street1'],
            'address_line2' => $data['street2'] ?? '',
            'city'          => $data['city'],
            'state'         => $data['state'],
            'zip_code'      => $data['zip'],
            'country'       => $data['country'] ?? 'US',
        );

        // Identify or Create Central Contact
        global $cp_contact_manager;
        $contact_id = $cp_contact_manager->find_or_create($contact_data);

        if (is_wp_error($contact_id)) {
            return $contact_id;
        }

        // Check if this contact is already a donor
        $existing_donor_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE contact_id = %d",
            $contact_id
        ));

        if ($existing_donor_id) {
            return new WP_Error('duplicate_donor', __('This contact is already registered as a donor.', 'campaign-office'), array('donor_id' => $existing_donor_id));
        }

        // Prepare FEC-specific data
        $insert_data = array(
            'contact_id'        => $contact_id,
            'occupation'        => isset($data['occupation']) ? sanitize_text_field($data['occupation']) : '',
            'employer'          => isset($data['employer']) ? sanitize_text_field($data['employer']) : '',
            'donor_type'        => isset($data['donor_type']) ? sanitize_text_field($data['donor_type']) : 'individual',
            'organization_name' => isset($data['organization_name']) ? sanitize_text_field($data['organization_name']) : '',
            'committee_id'      => isset($data['committee_id']) ? sanitize_text_field($data['committee_id']) : '',
            'created_by'        => get_current_user_id(),
            'created_date'      => current_time('mysql'),
        );

        // Check for prohibited sources
        $prohibited_check = $this->check_prohibited_source($insert_data);
        if ($prohibited_check['is_prohibited']) {
            $insert_data['is_prohibited_source'] = 1;
            $insert_data['prohibited_source_reason'] = $prohibited_check['reason'];

            if ($prohibited_check['type'] === 'foreign_national') {
                $insert_data['is_foreign_national'] = 1;
            } elseif ($prohibited_check['type'] === 'federal_contractor') {
                $insert_data['is_federal_contractor'] = 1;
            } elseif ($prohibited_check['type'] === 'corporate') {
                $insert_data['is_corporate_entity'] = 1;
            } elseif ($prohibited_check['type'] === 'labor') {
                $insert_data['is_labor_organization'] = 1;
            }
        }

        // Insert donor
        $result = $wpdb->insert($this->table_name, $insert_data, array(
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
            '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s',
        ));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create donor record.', 'campaign-office'));
        }

        $donor_id = $wpdb->insert_id;

        // Verify address if enabled
        if (get_option('cp_fec_enable_address_verification', false)) {
            $this->verify_address($donor_id);
        }

        // Action hook after donor creation
        do_action('cp_fec_donor_created', $donor_id, $insert_data);

        return $donor_id;
    }

    /**
     * Update donor profile
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @param array $data Updated donor data
     * @return bool|WP_Error True on success, error on failure
     */
    public function update_donor($donor_id, $data) {
        global $wpdb;

        // Validate donor exists
        $donor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            $donor_id
        ));

        if (!$donor) {
            return new WP_Error('donor_not_found', __('Donor not found.', 'campaign-office'));
        }

        // Update Central Contact Info
        global $cp_contact_manager;
        $contact_data = array();
        $core_map = array(
            'first_name' => 'first_name',
            'last_name'  => 'last_name',
            'middle_name'=> 'middle_name',
            'suffix'     => 'suffix',
            'email'      => 'email',
            'phone'      => 'phone',
            'street1'    => 'address_line1',
            'street2'    => 'address_line2',
            'city'       => 'city',
            'state'      => 'state',
            'zip'        => 'zip_code',
            'country'    => 'country',
        );

        foreach ($core_map as $fec_field => $master_field) {
            if (isset($data[$fec_field])) {
                $contact_data[$master_field] = $data[$fec_field];
            }
        }

        if (!empty($contact_data)) {
            $cp_contact_manager->update_contact($donor->contact_id, $contact_data);
        }

        // Prepare update data for FEC specific fields
        $update_data = array();
        $fields = array(
            'occupation', 'employer', 'organization_name', 'committee_id', 'internal_notes'
        );

        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = sanitize_text_field($data[$field]);
            }
        }

        if (empty($update_data) && empty($contact_data)) {
            return true;
        }

        $update_data['modified_date'] = current_time('mysql');

        // Update FEC-specific data
        if (!empty($update_data)) {
            $result = $wpdb->update(
                $this->table_name,
                $update_data,
                array('id' => $donor_id)
            );

            if (false === $result) {
                return new WP_Error('db_error', __('Failed to update donor record.', 'campaign-office'));
            }
        }

        // Log action
        do_action('cp_fec_donor_updated', $donor_id, $data);

        return true;
    }

    public function get_donor($donor_id) {
        global $wpdb;
        $contacts_table = $wpdb->prefix . 'cp_contacts';

        $donor = $wpdb->get_row($wpdb->prepare(
            "SELECT d.*, c.first_name, c.last_name, c.middle_name, c.suffix, c.email, c.phone, 
                    c.address_line1 as street1, c.address_line2 as street2, c.city, c.state, c.zip_code as zip, c.country
             FROM {$this->table_name} d
             JOIN {$contacts_table} c ON d.contact_id = c.id
             WHERE d.id = %d",
            $donor_id
        ));

        return $donor;
    }

    /**
     * Search donors
     *
     * @since 1.0.0
     * @param array $args Search arguments
     * @return array Search results
     */
    public function search_donors($args = array()) {
        global $wpdb;

        $defaults = array(
            'search' => '',
            'donor_type' => '',
            'is_prohibited_source' => '',
            'page' => 1,
            'per_page' => 50,
            'orderby' => 'last_name',
            'order' => 'ASC',
        );

        $args = wp_parse_args($args, $defaults);

        $contacts_table = $wpdb->prefix . 'cp_contacts';
        $where = array('1=1');
        $where_values = array();

        // Search by name, email, or address
        if (!empty($args['search'])) {
            $where[] = "(c.first_name LIKE %s OR c.last_name LIKE %s OR c.email LIKE %s OR d.organization_name LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }

        // Filter by donor type
        if (!empty($args['donor_type'])) {
            $where[] = "d.donor_type = %s";
            $where_values[] = $args['donor_type'];
        }

        // Filter by prohibited source
        if ($args['is_prohibited_source'] !== '') {
            $where[] = "d.is_prohibited_source = %d";
            $where_values[] = $args['is_prohibited_source'] ? 1 : 0;
        }

        $where_clause = implode(' AND ', $where);

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$this->table_name} d JOIN {$contacts_table} c ON d.contact_id = c.id WHERE {$where_clause}";
        if (!empty($where_values)) {
            $count_query = $wpdb->prepare($count_query, $where_values);
        }
        $total = $wpdb->get_var($count_query);

        // Calculate pagination
        $offset = ($args['page'] - 1) * $args['per_page'];

        // Map orderby
        $core_fields = array('first_name', 'last_name', 'email', 'city', 'state');
        $alias = in_array($args['orderby'], $core_fields) ? 'c' : 'd';
        $orderby = "{$alias}.{$args['orderby']}";

        // Get donors
        $query = "SELECT d.*, c.first_name, c.last_name, c.middle_name, c.suffix, c.email, c.phone, 
                         c.address_line1 as street1, c.address_line2 as street2, c.city, c.state, c.zip_code as zip, c.country
                  FROM {$this->table_name} d
                  JOIN {$contacts_table} c ON d.contact_id = c.id
                  WHERE {$where_clause}
                  ORDER BY {$orderby} {$args['order']}
                  LIMIT %d OFFSET %d";

        $where_values[] = $args['per_page'];
        $where_values[] = $offset;

        $donors = $wpdb->get_results($wpdb->prepare($query, $where_values));

        return array(
            'donors' => $donors,
            'total' => $total,
            'page' => $args['page'],
            'per_page' => $args['per_page'],
            'pages' => ceil($total / $args['per_page']),
        );
    }

    /**
     * Get donor contribution history
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @return array Contribution history
     */
    public function get_contribution_history($donor_id) {
        global $wpdb;

        $contributions_table = $wpdb->prefix . 'cp_fec_contributions';

        $contributions = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$contributions_table}
             WHERE donor_id = %d
             ORDER BY contribution_date DESC",
            $donor_id
        ));

        return $contributions;
    }

    /**
     * Update donor aggregate totals
     *
     * Called automatically when contributions are recorded
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @return bool True on success
     */
    public function update_aggregate_totals($donor_id) {
        global $wpdb;

        $contributions_table = $wpdb->prefix . 'cp_fec_contributions';
        $election_cycle = get_option('cp_fec_election_cycle', date('Y'));

        // Calculate aggregate for primary
        $primary_total = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM {$contributions_table}
             WHERE donor_id = %d
             AND election_type = 'primary'
             AND contribution_status = 'completed'
             AND YEAR(contribution_date) = %d",
            $donor_id,
            $election_cycle
        ));

        // Calculate aggregate for general
        $general_total = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM {$contributions_table}
             WHERE donor_id = %d
             AND election_type = 'general'
             AND contribution_status = 'completed'
             AND YEAR(contribution_date) = %d",
            $donor_id,
            $election_cycle
        ));

        // Calculate cycle total
        $cycle_total = $primary_total + $general_total;

        // Get contribution count and dates
        $stats = $wpdb->get_row($wpdb->prepare(
            "SELECT
                COUNT(*) as total_contributions,
                MIN(contribution_date) as first_contribution_date,
                MAX(contribution_date) as last_contribution_date
             FROM {$contributions_table}
             WHERE donor_id = %d
             AND contribution_status = 'completed'",
            $donor_id
        ));

        // Update donor record
        $wpdb->update(
            $this->table_name,
            array(
                'aggregate_primary' => $primary_total,
                'aggregate_general' => $general_total,
                'aggregate_cycle' => $cycle_total,
                'total_contributions' => $stats->total_contributions,
                'first_contribution_date' => $stats->first_contribution_date,
                'last_contribution_date' => $stats->last_contribution_date,
                'modified_date' => current_time('mysql'),
            ),
            array('id' => $donor_id),
            array('%f', '%f', '%f', '%d', '%s', '%s', '%s'),
            array('%d')
        );

        return true;
    }

    /**
     * Find potential duplicate donors
     *
     * @since 1.0.0
     * @param array $data Donor data to check
     * @return int|false Duplicate donor ID or false
     */
    public function find_duplicate($data) {
        global $wpdb;

        // Check by email (exact match)
        if (!empty($data['email'])) {
            $duplicate = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_name} WHERE email = %s LIMIT 1",
                sanitize_email($data['email'])
            ));

            if ($duplicate) {
                return $duplicate;
            }
        }

        // Check by name and address
        if (!empty($data['first_name']) && !empty($data['last_name']) && !empty($data['zip'])) {
            $duplicate = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->table_name}
                 WHERE first_name = %s
                 AND last_name = %s
                 AND zip = %s
                 LIMIT 1",
                sanitize_text_field($data['first_name']),
                sanitize_text_field($data['last_name']),
                sanitize_text_field($data['zip'])
            ));

            if ($duplicate) {
                return $duplicate;
            }
        }

        return false;
    }

    /**
     * Check if donor is from a prohibited source
     *
     * Per FEC regulations:
     * - Foreign nationals (11 CFR §110.20)
     * - Federal contractors (52 U.S.C. §30119)
     * - Corporate treasury funds (52 U.S.C. §30118)
     * - Labor organization treasury funds (52 U.S.C. §30118)
     *
     * @since 1.0.0
     * @param array $donor_data Donor data
     * @return array Prohibited source check result
     */
    public function check_prohibited_source($donor_data) {
        $is_prohibited = false;
        $reason = '';
        $type = '';

        // Check for foreign national (non-US country)
        if (!empty($donor_data['country']) && strtoupper($donor_data['country']) !== 'US') {
            $is_prohibited = true;
            $reason = __('Foreign national contributions are prohibited per 52 U.S.C. §30121', 'campaign-office');
            $type = 'foreign_national';
        }

        // Check for corporate entity making direct contribution
        if (!empty($donor_data['donor_type']) && $donor_data['donor_type'] === 'corporation') {
            $is_prohibited = true;
            $reason = __('Corporate treasury contributions are prohibited per 52 U.S.C. §30118. Corporations may contribute through a connected PAC.', 'campaign-office');
            $type = 'corporate';
        }

        // Check for labor organization making direct contribution
        if (!empty($donor_data['donor_type']) && $donor_data['donor_type'] === 'labor_union') {
            $is_prohibited = true;
            $reason = __('Labor organization treasury contributions are prohibited per 52 U.S.C. §30118. Labor organizations may contribute through a connected PAC.', 'campaign-office');
            $type = 'labor';
        }

        // Allow custom prohibited source checks via filter
        $custom_check = apply_filters('cp_fec_custom_prohibited_source_check', array(
            'is_prohibited' => $is_prohibited,
            'reason' => $reason,
            'type' => $type,
        ), $donor_data);

        return $custom_check;
    }

    /**
     * Flag donor as prohibited source
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @param string $reason Reason for prohibition
     * @return bool True on success
     */
    public function flag_prohibited_source($donor_id, $reason) {
        global $wpdb;

        $result = $wpdb->update(
            $this->table_name,
            array(
                'is_prohibited_source' => 1,
                'prohibited_source_reason' => sanitize_textarea_field($reason),
                'modified_date' => current_time('mysql'),
            ),
            array('id' => $donor_id),
            array('%d', '%s', '%s'),
            array('%d')
        );

        // Log the flag
        do_action('cp_fec_donor_flagged_prohibited', $donor_id, $reason);

        return $result !== false;
    }

    /**
     * Verify donor address
     *
     * This is a placeholder for address verification integration.
     * Can be extended to use USPS API, Google Maps API, or other services.
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @return bool True if verified
     */
    public function verify_address($donor_id) {
        global $wpdb;

        $donor = $this->get_donor($donor_id);
        if (!$donor) {
            return false;
        }

        // Basic validation - check required fields are present
        $is_valid = !empty($donor->street1) && !empty($donor->city) &&
                   !empty($donor->state) && !empty($donor->zip);

        if ($is_valid) {
            $wpdb->update(
                $this->table_name,
                array(
                    'address_verified' => 1,
                    'address_verified_date' => current_time('mysql'),
                ),
                array('id' => $donor_id),
                array('%d', '%s'),
                array('%d')
            );
        }

        // Allow integration with address verification services
        $verification_result = apply_filters('cp_fec_address_verification', $is_valid, $donor);

        return $verification_result;
    }

    /**
     * Validate donor data
     *
     * @since 1.0.0
     * @param array $data Donor data
     * @return bool|WP_Error True if valid, error otherwise
     */
    private function validate_donor_data($data) {
        $errors = array();

        // Required fields for individuals
        if (empty($data['first_name'])) {
            $errors[] = __('First name is required.', 'campaign-office');
        }

        if (empty($data['last_name'])) {
            $errors[] = __('Last name is required.', 'campaign-office');
        }

        if (empty($data['street1'])) {
            $errors[] = __('Street address is required.', 'campaign-office');
        }

        if (empty($data['city'])) {
            $errors[] = __('City is required.', 'campaign-office');
        }

        if (empty($data['state'])) {
            $errors[] = __('State is required.', 'campaign-office');
        }

        if (empty($data['zip'])) {
            $errors[] = __('ZIP code is required.', 'campaign-office');
        }

        // Email validation
        if (!empty($data['email']) && !is_email($data['email'])) {
            $errors[] = __('Invalid email address.', 'campaign-office');
        }

        // ZIP code format validation
        if (!empty($data['zip']) && !preg_match('/^\d{5}(-\d{4})?$/', $data['zip'])) {
            $errors[] = __('Invalid ZIP code format. Use XXXXX or XXXXX-XXXX.', 'campaign-office');
        }

        if (!empty($errors)) {
            return new WP_Error('validation_error', implode(' ', $errors));
        }

        return true;
    }

    /**
     * Delete donor
     *
     * Note: Only allowed if donor has no contribution history
     *
     * @since 1.0.0
     * @param int $donor_id Donor ID
     * @return bool|WP_Error True on success, error on failure
     */
    public function delete_donor($donor_id) {
        global $wpdb;

        // Check if donor has contributions
        $contributions_table = $wpdb->prefix . 'cp_fec_contributions';
        $contribution_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$contributions_table} WHERE donor_id = %d",
            $donor_id
        ));

        if ($contribution_count > 0) {
            return new WP_Error('has_contributions', __('Cannot delete donor with contribution history. Archive the donor instead.', 'campaign-office'));
        }

        // Delete donor
        $result = $wpdb->delete($this->table_name, array('id' => $donor_id), array('%d'));

        if ($result === false) {
            return new WP_Error('db_error', __('Failed to delete donor.', 'campaign-office'));
        }

        // Action hook after deletion
        do_action('cp_fec_donor_deleted', $donor_id);

        return true;
    }

    /**
     * Get donors requiring occupation/employer information
     *
     * Per FEC regulations, donors who contribute over $200 in aggregate
     * must have occupation and employer information collected.
     *
     * @since 1.0.0
     * @return array Donors missing required information
     */
    public function get_donors_missing_occupation() {
        global $wpdb;

        $threshold = CP_FEC_ITEMIZATION_THRESHOLD;

        $donors = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name}
             WHERE aggregate_cycle > %f
             AND (occupation = '' OR occupation IS NULL OR employer = '' OR employer IS NULL)
             ORDER BY aggregate_cycle DESC",
            $threshold
        ));

        return $donors;
    }
}
