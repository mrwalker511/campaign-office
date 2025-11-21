<?php
/**
 * Developer Console Data Exporter Class
 *
 * Handles data export and import operations
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CampaignPress_Developer_Data_Exporter {

    /**
     * Export data
     *
     * @param array $request Request data
     * @return array Result
     */
    public function export($request) {
        $export_type = isset($request['export_type']) ? sanitize_text_field($request['export_type']) : '';
        $format = isset($request['format']) ? sanitize_text_field($request['format']) : 'json';

        switch ($export_type) {
            case 'contacts':
                return $this->export_contacts($format);

            case 'interactions':
                return $this->export_interactions($format);

            case 'donors':
                return $this->export_donors($format);

            case 'contributions':
                return $this->export_contributions($format);

            case 'settings':
                return $this->export_settings($format);

            case 'full_backup':
                return $this->export_full_backup($format);

            case 'logs':
                return $this->export_logs($format);

            default:
                return array(
                    'success' => false,
                    'message' => 'Invalid export type'
                );
        }
    }

    /**
     * Export CRM contacts
     *
     * @param string $format Export format
     * @return array
     */
    private function export_contacts($format) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_crm_contacts';
        $contacts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);

        return $this->format_export($contacts, 'contacts', $format);
    }

    /**
     * Export interactions
     *
     * @param string $format Export format
     * @return array
     */
    private function export_interactions($format) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_crm_interactions';
        $interactions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY interaction_date DESC", ARRAY_A);

        return $this->format_export($interactions, 'interactions', $format);
    }

    /**
     * Export FEC donors
     *
     * @param string $format Export format
     * @return array
     */
    private function export_donors($format) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_fec_donors';
        $donors = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);

        return $this->format_export($donors, 'donors', $format);
    }

    /**
     * Export FEC contributions
     *
     * @param string $format Export format
     * @return array
     */
    private function export_contributions($format) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_fec_contributions';
        $contributions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY contribution_date DESC", ARRAY_A);

        return $this->format_export($contributions, 'contributions', $format);
    }

    /**
     * Export all settings
     *
     * @param string $format Export format
     * @return array
     */
    private function export_settings($format) {
        global $wpdb;

        // Get all CampaignPress options
        $options = $wpdb->get_results(
            "SELECT option_name, option_value
             FROM {$wpdb->options}
             WHERE option_name LIKE 'campaignpress_%'
             ORDER BY option_name",
            ARRAY_A
        );

        $settings = array();
        foreach ($options as $option) {
            $settings[$option['option_name']] = maybe_unserialize($option['option_value']);
        }

        return $this->format_export($settings, 'settings', $format);
    }

    /**
     * Export developer console logs
     *
     * @param string $format Export format
     * @return array
     */
    private function export_logs($format) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_logs';
        $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 1000", ARRAY_A);

        return $this->format_export($logs, 'developer_logs', $format);
    }

    /**
     * Export full backup
     *
     * @param string $format Export format
     * @return array
     */
    private function export_full_backup($format) {
        $backup_data = array(
            'metadata' => array(
                'export_date' => current_time('mysql'),
                'site_url' => site_url(),
                'wp_version' => get_bloginfo('version'),
                'campaignpress_version' => CAMPAIGNPRESS_VERSION
            ),
            'contacts' => $this->get_table_data('cp_crm_contacts'),
            'interactions' => $this->get_table_data('cp_crm_interactions'),
            'donors' => $this->get_table_data('cp_fec_donors'),
            'contributions' => $this->get_table_data('cp_fec_contributions'),
            'walks' => $this->get_table_data('cp_canvassing_walks'),
            'phone_calls' => $this->get_table_data('cp_phone_calls'),
            'settings' => $this->get_all_settings()
        );

        return $this->format_export($backup_data, 'full_backup', $format);
    }

    /**
     * Get table data
     *
     * @param string $table_suffix Table suffix (without prefix)
     * @return array
     */
    private function get_table_data($table_suffix) {
        global $wpdb;

        $table_name = $wpdb->prefix . $table_suffix;

        // Check if table exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = %s AND table_name = %s",
            DB_NAME,
            $table_name
        ));

        if (!$exists) {
            return array();
        }

        return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    }

    /**
     * Get all CampaignPress settings
     *
     * @return array
     */
    private function get_all_settings() {
        global $wpdb;

        $options = $wpdb->get_results(
            "SELECT option_name, option_value
             FROM {$wpdb->options}
             WHERE option_name LIKE 'campaignpress_%'",
            ARRAY_A
        );

        $settings = array();
        foreach ($options as $option) {
            $settings[$option['option_name']] = maybe_unserialize($option['option_value']);
        }

        return $settings;
    }

    /**
     * Format export data
     *
     * @param mixed $data Data to export
     * @param string $name Export name
     * @param string $format Export format
     * @return array
     */
    private function format_export($data, $name, $format) {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "campaignpress_{$name}_{$timestamp}";

        switch ($format) {
            case 'json':
                $content = json_encode($data, JSON_PRETTY_PRINT);
                $filename .= '.json';
                $mime_type = 'application/json';
                break;

            case 'csv':
                $content = $this->convert_to_csv($data);
                $filename .= '.csv';
                $mime_type = 'text/csv';
                break;

            case 'xml':
                $content = $this->convert_to_xml($data, $name);
                $filename .= '.xml';
                $mime_type = 'application/xml';
                break;

            default:
                return array(
                    'success' => false,
                    'message' => 'Unsupported export format'
                );
        }

        $this->log_export($name, $format, strlen($content));

        return array(
            'success' => true,
            'message' => 'Export completed successfully',
            'filename' => $filename,
            'content' => $content,
            'mime_type' => $mime_type,
            'size' => strlen($content),
            'records' => is_array($data) ? count($data) : 1
        );
    }

    /**
     * Convert data to CSV
     *
     * @param array $data Data array
     * @return string CSV content
     */
    private function convert_to_csv($data) {
        if (empty($data)) {
            return '';
        }

        // Handle associative arrays (settings)
        if (!isset($data[0])) {
            $csv_data = array();
            foreach ($data as $key => $value) {
                $csv_data[] = array(
                    'key' => $key,
                    'value' => is_array($value) ? json_encode($value) : $value
                );
            }
            $data = $csv_data;
        }

        $output = fopen('php://temp', 'r+');

        // Add headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));

            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Convert data to XML
     *
     * @param mixed $data Data to convert
     * @param string $root_name Root element name
     * @return string XML content
     */
    private function convert_to_xml($data, $root_name) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $root_name . '/>');

        $this->array_to_xml($data, $xml);

        return $xml->asXML();
    }

    /**
     * Convert array to XML recursively
     *
     * @param array $data Data array
     * @param SimpleXMLElement $xml XML element
     */
    private function array_to_xml($data, &$xml) {
        foreach ($data as $key => $value) {
            // If key is numeric, use 'item' as element name
            if (is_numeric($key)) {
                $key = 'item';
            }

            // Sanitize key for XML
            $key = preg_replace('/[^a-z0-9_]/i', '_', $key);

            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                $this->array_to_xml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars((string)$value));
            }
        }
    }

    /**
     * Log export operation
     *
     * @param string $export_type Export type
     * @param string $format Export format
     * @param int $size File size
     */
    private function log_export($export_type, $format, $size) {
        $console = CampaignPress_Developer_Console::get_instance();

        $console->log_activity(
            'data',
            'data_exported',
            "Data exported: {$export_type} ({$format})",
            array(
                'export_type' => $export_type,
                'format' => $format,
                'size' => $size
            ),
            'success'
        );
    }

    /**
     * Get export statistics
     *
     * @return array
     */
    public function get_export_stats() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cp_dev_console_logs';

        $total_exports = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE action_type = 'data_exported'"
        );

        $recent_exports = $wpdb->get_results(
            "SELECT action_description, created_at
             FROM $table_name
             WHERE action_type = 'data_exported'
             ORDER BY created_at DESC
             LIMIT 10",
            ARRAY_A
        );

        return array(
            'total_exports' => $total_exports,
            'recent_exports' => $recent_exports
        );
    }
}
