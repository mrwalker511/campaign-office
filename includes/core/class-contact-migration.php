<?php
/**
 * CampaignPress Contact Migration Class
 *
 * Handles the migration of contact data from module-specific tables
 * to the centralized master contact table.
 *
 * @package CampaignPress
 * @subpackage Core
 * @since 1.0.0
 */

class CampaignPress_Contact_Migration {

    /**
     * wpdb instance
     */
    private $wpdb;

    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Run the full migration
     *
     * @return array Migration results
     */
    public function run_migration() {
        $results = array(
            'volunteers' => $this->migrate_volunteers(),
            'event_rsvps' => $this->migrate_event_rsvps(),
            'crm_contacts' => $this->migrate_crm_contacts(),
            'fec_donors' => $this->migrate_fec_donors(),
        );

        return $results;
    }

    /**
     * Migrate volunteers
     */
    private function migrate_volunteers() {
        $table = $this->wpdb->prefix . 'cp_volunteers';
        
        // Check if table exists and has old columns
        if (!$this->table_has_columns($table, array('first_name', 'last_name', 'email'))) {
            return array('status' => 'skipped', 'message' => 'Volunteers table already migrated or columns missing.');
        }

        $records = $this->wpdb->get_results("SELECT * FROM {$table} WHERE contact_id = 0 OR contact_id IS NULL");
        $count = 0;

        foreach ($records as $record) {
            $contact_id = $this->find_or_create_master_contact(array(
                'first_name' => $record->first_name,
                'last_name'  => $record->last_name,
                'email'      => $record->email,
                'phone'      => $record->phone ?? '',
                'address_line1' => $record->address ?? '',
                'city'       => $record->city ?? '',
                'state'      => $record->state ?? '',
                'zip_code'   => $record->zip ?? '',
            ));

            if ($contact_id) {
                $this->wpdb->update($table, array('contact_id' => $contact_id), array('id' => $record->id));
                $count++;
            }
        }

        return array('status' => 'success', 'migrated' => $count);
    }

    /**
     * Migrate event RSVPs
     */
    private function migrate_event_rsvps() {
        $table = $this->wpdb->prefix . 'cp_event_rsvps';
        
        if (!$this->table_has_columns($table, array('first_name', 'last_name', 'email'))) {
            return array('status' => 'skipped', 'message' => 'Event RSVPs table already migrated or columns missing.');
        }

        $records = $this->wpdb->get_results("SELECT * FROM {$table} WHERE contact_id = 0 OR contact_id IS NULL");
        $count = 0;

        foreach ($records as $record) {
            $contact_id = $this->find_or_create_master_contact(array(
                'first_name' => $record->first_name,
                'last_name'  => $record->last_name,
                'email'      => $record->email,
                'phone'      => $record->phone ?? '',
            ));

            if ($contact_id) {
                $this->wpdb->update($table, array('contact_id' => $contact_id), array('id' => $record->id));
                $count++;
            }
        }

        return array('status' => 'success', 'migrated' => $count);
    }

    /**
     * Migrate CRM contacts
     */
    private function migrate_crm_contacts() {
        $table = $this->wpdb->prefix . 'cp_crm_contacts';
        
        if (!$this->table_has_columns($table, array('first_name', 'last_name', 'email'))) {
            return array('status' => 'skipped', 'message' => 'CRM contacts table already migrated or columns missing.');
        }

        $records = $this->wpdb->get_results("SELECT * FROM {$table} WHERE contact_id = 0 OR contact_id IS NULL");
        $count = 0;

        foreach ($records as $record) {
            $contact_id = $this->find_or_create_master_contact(array(
                'first_name' => $record->first_name,
                'last_name'  => $record->last_name,
                'email'      => $record->email,
                'phone'      => $record->phone ?? '',
                'mobile_phone' => $record->mobile_phone ?? '',
                'address_line1' => $record->address_line1 ?? '',
                'address_line2' => $record->address_line2 ?? '',
                'city'       => $record->city ?? '',
                'state'      => $record->state ?? '',
                'zip_code'   => $record->zip_code ?? '',
                'external_id' => $record->external_id ?? '',
            ));

            if ($contact_id) {
                $this->wpdb->update($table, array('contact_id' => $contact_id), array('id' => $record->id));
                $count++;
            }
        }

        return array('status' => 'success', 'migrated' => $count);
    }

    /**
     * Migrate FEC donors
     */
    private function migrate_fec_donors() {
        $table = $this->wpdb->prefix . 'cp_fec_donors';
        
        // Note: FEC donors had some different column names
        if (!$this->table_has_columns($table, array('first_name', 'last_name'))) {
            return array('status' => 'skipped', 'message' => 'FEC donors table already migrated or columns missing.');
        }

        $records = $this->wpdb->get_results("SELECT * FROM {$table} WHERE contact_id = 0 OR contact_id IS NULL");
        $count = 0;

        foreach ($records as $record) {
            $contact_id = $this->find_or_create_master_contact(array(
                'first_name' => $record->first_name,
                'last_name'  => $record->last_name,
                'middle_name'=> $record->middle_name ?? '',
                'suffix'     => $record->suffix ?? '',
                'email'      => $record->email ?? '',
                'phone'      => $record->phone ?? '',
                'address_line1' => $record->street1 ?? '',
                'address_line2' => $record->street2 ?? '',
                'city'       => $record->city ?? '',
                'state'      => $record->state ?? '',
                'zip_code'   => $record->zip ?? '',
            ));

            if ($contact_id) {
                $this->wpdb->update($table, array('contact_id' => $contact_id), array('id' => $record->id));
                $count++;
            }
        }

        return array('status' => 'success', 'migrated' => $count);
    }

    /**
     * Helper to find or create master contact
     */
    private function find_or_create_master_contact($data) {
        global $cp_contact_manager;
        
        if (!$cp_contact_manager) {
            return false;
        }

        $contact_id = $cp_contact_manager->find_or_create($data);
        return is_wp_error($contact_id) ? false : $contact_id;
    }

    /**
     * Check if table has specific columns
     */
    private function table_has_columns($table, $columns) {
        $row = $this->wpdb->get_row("SELECT * FROM {$table} LIMIT 1", ARRAY_A);
        if (!$row) {
            // Table might be empty, but we can check columns via DESCRIBE
            $db_columns = $this->wpdb->get_col("DESCRIBE {$table}");
            foreach ($columns as $column) {
                if (!in_array($column, $db_columns)) {
                    return false;
                }
            }
            return true;
        }

        foreach ($columns as $column) {
            if (!array_key_exists($column, $row)) {
                return false;
            }
        }
        return true;
    }
}
