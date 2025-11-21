<?php
/**
 * Developer Console API Tester Class
 *
 * Tests CampaignPress REST API endpoints
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CampaignPress_Developer_API_Tester {

    /**
     * Test API endpoint
     *
     * @param array $request Request data
     * @return array Result
     */
    public function test_endpoint($request) {
        $endpoint = isset($request['endpoint']) ? sanitize_text_field($request['endpoint']) : '';
        $method = isset($request['method']) ? strtoupper(sanitize_text_field($request['method'])) : 'GET';
        $body = isset($request['body']) ? $request['body'] : array();
        $headers = isset($request['headers']) ? $request['headers'] : array();

        if (empty($endpoint)) {
            return array(
                'success' => false,
                'message' => 'Endpoint is required'
            );
        }

        // Ensure endpoint starts with /
        if (strpos($endpoint, '/') !== 0) {
            $endpoint = '/' . $endpoint;
        }

        // Build full URL
        $base_url = rest_url('campaignpress/v1');
        $url = $base_url . $endpoint;

        // Prepare request arguments
        $args = array(
            'method' => $method,
            'headers' => array_merge(
                array(
                    'Content-Type' => 'application/json'
                ),
                $headers
            ),
            'timeout' => 30
        );

        // Add body for POST/PUT/PATCH
        if (in_array($method, array('POST', 'PUT', 'PATCH')) && !empty($body)) {
            if (is_string($body)) {
                $args['body'] = $body;
            } else {
                $args['body'] = json_encode($body);
            }
        }

        // Add authentication (current user)
        $current_user = wp_get_current_user();
        if ($current_user->ID) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->generate_temp_token($current_user);
        }

        $start_time = microtime(true);

        // Make request
        $response = wp_remote_request($url, $args);

        $execution_time = microtime(true) - $start_time;

        // Check for errors
        if (is_wp_error($response)) {
            $this->log_api_test($endpoint, $method, 'failure', $execution_time, $response->get_error_message());

            return array(
                'success' => false,
                'message' => 'Request failed: ' . $response->get_error_message(),
                'execution_time' => round($execution_time, 4)
            );
        }

        // Get response data
        $status_code = wp_remote_retrieve_response_code($response);
        $response_headers = wp_remote_retrieve_headers($response);
        $response_body = wp_remote_retrieve_body($response);

        // Try to decode JSON
        $decoded_body = json_decode($response_body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $response_body = $decoded_body;
        }

        $this->log_api_test($endpoint, $method, 'success', $execution_time, null, $status_code);

        return array(
            'success' => true,
            'message' => 'Request completed',
            'status_code' => $status_code,
            'execution_time' => round($execution_time, 4),
            'headers' => $response_headers->getAll(),
            'body' => $response_body,
            'request_url' => $url,
            'request_method' => $method,
            'request_headers' => $args['headers'],
            'request_body' => isset($args['body']) ? $args['body'] : null
        );
    }

    /**
     * Generate temporary authentication token
     *
     * @param WP_User $user User object
     * @return string Token
     */
    private function generate_temp_token($user) {
        // This is a simplified token - in production, use proper JWT or OAuth
        return base64_encode($user->ID . ':' . wp_hash_password($user->user_login . time()));
    }

    /**
     * Log API test
     *
     * @param string $endpoint Endpoint
     * @param string $method HTTP method
     * @param string $status Status
     * @param float $execution_time Execution time
     * @param string $error Error message
     * @param int $status_code HTTP status code
     */
    private function log_api_test($endpoint, $method, $status, $execution_time, $error = null, $status_code = null) {
        $console = CampaignPress_Developer_Console::get_instance();

        $console->log_activity(
            'api',
            'endpoint_tested',
            "API endpoint tested: {$method} {$endpoint}",
            array(
                'endpoint' => $endpoint,
                'method' => $method,
                'status_code' => $status_code,
                'execution_time' => $execution_time
            ),
            $status,
            $error
        );
    }

    /**
     * Get available API endpoints
     *
     * @return array
     */
    public function get_available_endpoints() {
        $endpoints = array(
            array(
                'path' => '/contacts',
                'methods' => array('GET', 'POST'),
                'description' => 'Manage CRM contacts'
            ),
            array(
                'path' => '/contacts/{id}',
                'methods' => array('GET', 'PUT', 'DELETE'),
                'description' => 'Manage individual contact'
            ),
            array(
                'path' => '/interactions',
                'methods' => array('GET', 'POST'),
                'description' => 'Manage interactions'
            ),
            array(
                'path' => '/walks',
                'methods' => array('GET', 'POST'),
                'description' => 'Manage canvassing walks'
            ),
            array(
                'path' => '/phone-calls',
                'methods' => array('GET', 'POST'),
                'description' => 'Manage phone banking calls'
            ),
            array(
                'path' => '/donors',
                'methods' => array('GET', 'POST'),
                'description' => 'Manage FEC donors'
            ),
            array(
                'path' => '/contributions',
                'methods' => array('GET', 'POST'),
                'description' => 'Manage FEC contributions'
            ),
            array(
                'path' => '/analytics/summary',
                'methods' => array('GET'),
                'description' => 'Get analytics summary'
            ),
            array(
                'path' => '/webhooks',
                'methods' => array('GET', 'POST'),
                'description' => 'Manage webhooks'
            )
        );

        return $endpoints;
    }

    /**
     * Get API statistics
     *
     * @return array
     */
    public function get_api_stats() {
        global $wpdb;

        // Get API request logs from developer console logs
        $table_name = $wpdb->prefix . 'cp_dev_console_logs';

        $total_requests = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE action_category = 'api'"
        );

        $successful_requests = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE action_category = 'api' AND result_status = 'success'"
        );

        $failed_requests = $wpdb->get_var(
            "SELECT COUNT(*) FROM $table_name WHERE action_category = 'api' AND result_status = 'failure'"
        );

        $avg_execution_time = $wpdb->get_var(
            "SELECT AVG(execution_time) FROM $table_name WHERE action_category = 'api' AND execution_time IS NOT NULL"
        );

        return array(
            'total_requests' => $total_requests,
            'successful_requests' => $successful_requests,
            'failed_requests' => $failed_requests,
            'success_rate' => $total_requests > 0 ? round(($successful_requests / $total_requests) * 100, 2) : 0,
            'avg_execution_time' => round($avg_execution_time, 4)
        );
    }
}
