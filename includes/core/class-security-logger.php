<?php
/**
 * CampaignPress Security Logger
 *
 * Centralized security event logging and monitoring system.
 * Logs authentication attempts, webhook events, and suspicious activities.
 *
 * @package CampaignPress
 * @subpackage Core
 * @since 2.1.0
 * @version 2.1.0
 *
 * Security Features:
 * - Centralized security event logging
 * - Automatic alert notifications
 * - Suspicious activity detection
 * - Log retention and cleanup
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security Logger Class
 *
 * Provides centralized security event logging and monitoring capabilities.
 *
 * @since 2.1.0
 */
class CampaignPress_Security_Logger {

    /**
     * Option name for storing security logs
     */
    const LOGS_OPTION = 'campaignpress_security_logs';

    /**
     * Maximum number of logs to keep
     */
    const MAX_LOGS = 10000;

    /**
     * Alert threshold for suspicious activity
     */
    const ALERT_THRESHOLD = 5;

    /**
     * Log retention in days
     */
    const RETENTION_DAYS = 90;

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Hook into WordPress actions for automatic logging
        add_action('wp_login_failed', array($this, 'log_failed_login'));
        add_action('wp_login', array($this, 'log_successful_login'), 10, 2);
        add_action('campaignpress_webhook_received', array($this, 'log_webhook_event'), 10, 4);
        add_action('campaignpress_suspicious_activity', array($this, 'log_suspicious_activity'), 10, 3);
        
        // Clean up old logs daily
        add_action('wp_scheduled_delete', array($this, 'cleanup_old_logs'));
    }

    /**
     * Log a security event
     *
     * @param string $event_type Type of security event
     * @param string $description Event description
     * @param array $context Additional context data
     * @param string $severity Severity level (low, medium, high, critical)
     * @return void
     */
    public function log_event($event_type, $description, $context = array(), $severity = 'medium') {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event_type' => sanitize_text_field($event_type),
            'description' => sanitize_text_field($description),
            'context' => $this->sanitize_context($context),
            'severity' => sanitize_text_field($severity),
            'ip_address' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '',
            'user_id' => get_current_user_id(),
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? esc_url_raw($_SERVER['REQUEST_URI']) : '',
            'hash' => wp_hash($event_type . current_time('mysql') . wp_get_session_token())
        );

        // Get existing logs
        $logs = get_option(self::LOGS_OPTION, array());
        
        // Add new log entry
        array_unshift($logs, $log_entry);
        
        // Trim logs to maximum limit
        if (count($logs) > self::MAX_LOGS) {
            $logs = array_slice($logs, 0, self::MAX_LOGS);
        }
        
        // Save logs
        update_option(self::LOGS_OPTION, $logs);
        
        // Check if this event requires immediate attention
        if (in_array($severity, array('high', 'critical'), true)) {
            $this->send_security_alert($log_entry);
        }
        
        // Check for suspicious activity patterns
        $this->check_suspicious_patterns($event_type, $context);
    }

    /**
     * Log failed login attempt
     */
    public function log_failed_login($username) {
        $this->log_event(
            'failed_login',
            sprintf(__('Failed login attempt for username: %s', 'campaignpress'), $username),
            array(
                'username' => $username,
                'attempt_count' => $this->get_failed_login_count($username)
            ),
            'medium'
        );
    }

    /**
     * Log successful login
     */
    public function log_successful_login($user_login, $user) {
        $this->log_event(
            'successful_login',
            sprintf(__('Successful login for user: %s', 'campaignpress'), $user_login),
            array(
                'user_id' => $user->ID,
                'user_email' => $user->user_email
            ),
            'low'
        );
    }

    /**
     * Log webhook event
     */
    public function log_webhook_event($platform, $action, $success, $context = array()) {
        $status = $success ? 'success' : 'failure';
        
        $this->log_event(
            'webhook_' . $status,
            sprintf(__('Webhook event from %s: %s', 'campaignpress'), $platform, $action),
            array_merge($context, array(
                'platform' => $platform,
                'action' => $action
            )),
            $success ? 'low' : 'high'
        );
    }

    /**
     * Log suspicious activity
     */
    public function log_suspicious_activity($activity_type, $description, $context = array()) {
        $this->log_event(
            'suspicious_activity',
            $description,
            array_merge($context, array(
                'activity_type' => $activity_type
            )),
            'high'
        );
    }

    /**
     * Send security alert email
     */
    private function send_security_alert($log_entry) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(
            '[%s] Security Alert: %s',
            $site_name,
            $log_entry['event_type']
        );
        
        $message = sprintf(
            "Security Alert - %s\n\n" .
            "Event Type: %s\n" .
            "Severity: %s\n" .
            "Description: %s\n" .
            "Time: %s\n" .
            "IP Address: %s\n" .
            "User ID: %d\n" .
            "Request URI: %s\n\n" .
            "Please review this event immediately.\n\n" .
            "This is an automated security alert from %s.",
            $log_entry['severity'],
            $log_entry['event_type'],
            $log_entry['severity'],
            $log_entry['description'],
            $log_entry['timestamp'],
            $log_entry['ip_address'],
            $log_entry['user_id'],
            $log_entry['request_uri'],
            $site_name
        );
        
        wp_mail($admin_email, $subject, $message);
    }

    /**
     * Check for suspicious activity patterns
     */
    private function check_suspicious_patterns($event_type, $context) {
        $ip_address = $this->get_client_ip();
        
        switch ($event_type) {
            case 'failed_login':
                $this->detect_brute_force($ip_address);
                break;
                
            case 'webhook_failure':
                $this->detect_webhook_abuse($ip_address);
                break;
        }
    }

    /**
     * Detect brute force attacks
     */
    private function detect_brute_force($ip_address) {
        $recent_failures = $this->get_logs_by_type_and_ip('failed_login', $ip_address, 300); // Last 5 minutes
        
        if (count($recent_failures) >= self::ALERT_THRESHOLD) {
            $this->log_event(
                'brute_force_detected',
                sprintf(__('Possible brute force attack detected from IP: %s', 'campaignpress'), $ip_address),
                array(
                    'failed_attempts' => count($recent_failures),
                    'time_window' => '5 minutes'
                ),
                'critical'
            );
        }
    }

    /**
     * Detect webhook abuse
     */
    private function detect_webhook_abuse($ip_address) {
        $recent_failures = $this->get_logs_by_type_and_ip('webhook_failure', $ip_address, 300);
        
        if (count($recent_failures) >= 10) {
            $this->log_event(
                'webhook_abuse_detected',
                sprintf(__('Possible webhook abuse detected from IP: %s', 'campaignpress'), $ip_address),
                array(
                    'failed_webhooks' => count($recent_failures),
                    'time_window' => '5 minutes'
                ),
                'high'
            );
        }
    }

    /**
     * Get logs by type and IP address
     */
    private function get_logs_by_type_and_ip($event_type, $ip_address, $time_window_seconds) {
        $logs = get_option(self::LOGS_OPTION, array());
        $cutoff_time = current_time('timestamp') - $time_window_seconds;
        
        $filtered_logs = array();
        
        foreach ($logs as $log) {
            $log_timestamp = strtotime($log['timestamp']);
            
            if ($log['event_type'] === $event_type && 
                $log['ip_address'] === $ip_address && 
                $log_timestamp >= $cutoff_time) {
                $filtered_logs[] = $log;
            }
        }
        
        return $filtered_logs;
    }

    /**
     * Get failed login count for username
     */
    private function get_failed_login_count($username) {
        $logs = get_option(self::LOGS_OPTION, array());
        $count = 0;
        
        foreach ($logs as $log) {
            if ($log['event_type'] === 'failed_login' && 
                isset($log['context']['username']) && 
                $log['context']['username'] === $username) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        );
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Sanitize context data
     */
    private function sanitize_context($context) {
        if (!is_array($context)) {
            return array();
        }
        
        $sanitized = array();
        
        foreach ($context as $key => $value) {
            $sanitized_key = sanitize_text_field($key);
            
            if (is_string($value)) {
                $sanitized[$sanitized_key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                $sanitized[$sanitized_key] = is_float($value) ? (float) $value : (int) $value;
            } elseif (is_bool($value)) {
                $sanitized[$sanitized_key] = (bool) $value;
            } elseif (is_array($value)) {
                $sanitized[$sanitized_key] = $this->sanitize_context($value);
            } else {
                $sanitized[$sanitized_key] = '';
            }
        }
        
        return $sanitized;
    }

    /**
     * Clean up old logs
     */
    public function cleanup_old_logs() {
        $logs = get_option(self::LOGS_OPTION, array());
        $cutoff_time = current_time('timestamp') - (self::RETENTION_DAYS * DAY_IN_SECONDS);
        
        $filtered_logs = array();
        
        foreach ($logs as $log) {
            $log_timestamp = strtotime($log['timestamp']);
            
            if ($log_timestamp >= $cutoff_time) {
                $filtered_logs[] = $log;
            }
        }
        
        update_option(self::LOGS_OPTION, $filtered_logs);
    }

    /**
     * Get security logs (admin function)
     */
    public function get_logs($limit = 100, $severity = null, $event_type = null) {
        $logs = get_option(self::LOGS_OPTION, array());
        $filtered_logs = $logs;
        
        // Filter by severity
        if ($severity) {
            $filtered_logs = array_filter($filtered_logs, function($log) use ($severity) {
                return $log['severity'] === $severity;
            });
        }
        
        // Filter by event type
        if ($event_type) {
            $filtered_logs = array_filter($filtered_logs, function($log) use ($event_type) {
                return $log['event_type'] === $event_type;
            });
        }
        
        // Limit results
        return array_slice($filtered_logs, 0, $limit);
    }

    /**
     * Clear all security logs (admin function)
     */
    public function clear_logs() {
        delete_option(self::LOGS_OPTION);
        
        $this->log_event(
            'logs_cleared',
            __('All security logs were cleared by administrator', 'campaignpress'),
            array(),
            'high'
        );
    }

    /**
     * Export security logs (admin function)
     */
    public function export_logs($format = 'json') {
        $logs = get_option(self::LOGS_OPTION, array());
        
        switch ($format) {
            case 'csv':
                return $this->export_logs_as_csv($logs);
            case 'json':
            default:
                return wp_json_encode($logs, JSON_PRETTY_PRINT);
        }
    }

    /**
     * Export logs as CSV
     */
    private function export_logs_as_csv($logs) {
        $csv = "Timestamp,Event Type,Severity,Description,IP Address,User ID,User Agent,Request URI\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%d","%s","%s"' . "\n",
                $log['timestamp'],
                $log['event_type'],
                $log['severity'],
                str_replace('"', '""', $log['description']),
                $log['ip_address'],
                $log['user_id'],
                str_replace('"', '""', $log['user_agent']),
                $log['request_uri']
            );
        }
        
        return $csv;
    }
}

// Initialize the security logger
CampaignPress_Security_Logger::get_instance();
