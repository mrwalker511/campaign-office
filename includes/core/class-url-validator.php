<?php
/**
 * CampaignPress URL Validator
 *
 * Server-Side Request Forgery (SSRF) protection for all external URLs.
 * Validates URLs, blocks private/internal IPs, and enforces domain whitelisting.
 *
 * @package CampaignPress
 * @subpackage Core
 * @since 2.1.0
 * @version 2.1.0
 *
 * Security Features:
 * - SSRF attack prevention
 * - Private IP blocking
 * - Domain whitelist enforcement
 * - URL format validation
 *
 * @author CampaignPress Team
 * @license GPL-2.0+
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * URL Validator Class
 *
 * Provides SSRF protection and URL validation for external requests.
 *
 * @since 2.1.0
 */
class CampaignPress_URL_Validator {

    /**
     * Default allowed domains
     */
    private static $default_allowed_domains = array(
        'api.mailchimp.com',
        'hooks.zapier.com',
        'api.twilio.com',
        'api.sendgrid.com',
        'api.constantcontact.com',
        'mailerlite.com',
        'smtp.gmail.com',
        'smtp.sendgrid.net',
        'smtp.mailgun.org',
        'api.actionnetwork.org',
        'hustle.com',
        'callhub.com',
        'rumbleup.com',
        'actblue.com',
        'winred.com',
        'paypal.com',
        'stripe.com',
        'squareup.com',
        'donorbox.org'
    );

    /**
     * Private IP ranges to block
     */
    private static $private_ip_ranges = array(
        '10.0.0.0/8',        // Class A private
        '172.16.0.0/12',      // Class B private
        '192.168.0.0/16',     // Class C private
        '127.0.0.0/8',        // Loopback
        '169.254.0.0/16',     // Link-local
        '::1/128',            // IPv6 loopback
        'fc00::/7',           // IPv6 unique local
        'fe80::/10'           // IPv6 link-local
    );

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
        // Hook into HTTP requests for validation
        add_filter('pre_http_request', array($this, 'validate_http_request'), 10, 3);
        add_filter('http_request_args', array($this, 'filter_http_request_args'), 10, 2);
    }

    /**
     * Validate URL before making HTTP request
     *
     * @param array $args HTTP request arguments
     * @param string $url Target URL
     * @return array|WP_Error Validated arguments or WP_Error on validation failure
     */
    public function validate_url($url) {
        // Parse the URL
        $parsed_url = parse_url($url);
        
        if (!$parsed_url || !isset($parsed_url['host'])) {
            return new WP_Error('invalid_url', __('Invalid URL format', 'campaignpress'));
        }

        // Validate URL scheme
        if (!in_array($parsed_url['scheme'], array('http', 'https'), true)) {
            return new WP_Error('invalid_scheme', __('Only HTTP and HTTPS protocols are allowed', 'campaignpress'));
        }

        // Validate domain
        $domain = strtolower($parsed_url['host']);
        if (!$this->is_domain_allowed($domain)) {
            return new WP_Error('domain_not_allowed', 
                sprintf(__('Domain not allowed: %s', 'campaignpress'), $domain));
        }

        // Resolve and validate IP address
        if (!$this->is_ip_allowed($domain)) {
            return new WP_Error('ip_not_allowed', 
                sprintf(__('IP address not allowed: %s', 'campaignpress'), $domain));
        }

        // Check for suspicious patterns
        if ($this->has_suspicious_patterns($url)) {
            return new WP_Error('suspicious_url', __('URL contains suspicious patterns', 'campaignpress'));
        }

        return true;
    }

    /**
     * Validate HTTP request before execution
     */
    public function validate_http_request($preempt, $args, $url) {
        $validation = $this->validate_url($url);
        
        if (is_wp_error($validation)) {
            // Log the blocked request
            if (class_exists('CampaignPress_Security_Logger')) {
                CampaignPress_Security_Logger::get_instance()->log_event(
                    'ssrf_blocked',
                    sprintf(__('SSRF attack blocked: %s', 'campaignpress'), $validation->get_error_message()),
                    array(
                        'blocked_url' => $url,
                        'reason' => $validation->get_error_code()
                    ),
                    'high'
                );
            }
            
            return $validation;
        }
        
        return $preempt;
    }

    /**
     * Filter HTTP request arguments for additional security
     */
    public function filter_http_request_args($args, $url) {
        // Add timeout to prevent slowloris attacks
        if (!isset($args['timeout'])) {
            $args['timeout'] = 15; // 15 seconds max
        }

        // Add user agent to avoid being blocked
        if (!isset($args['user-agent'])) {
            $args['user-agent'] = 'CampaignPress WordPress Plugin';
        }

        // Disable SSL verification only for known hosts (not recommended but sometimes necessary)
        $parsed_url = parse_url($url);
        if ($parsed_url && isset($parsed_url['host'])) {
            $host = strtolower($parsed_url['host']);
            
            // Only disable SSL verification for specific development/testing hosts
            $unsafe_ssl_hosts = apply_filters('campaignpress_unsafe_ssl_hosts', array(
                'localhost',
                '127.0.0.1',
                'test.local',
                'dev.local'
            ));
            
            if (in_array($host, $unsafe_ssl_hosts, true)) {
                $args['sslverify'] = false;
            }
        }

        return $args;
    }

    /**
     * Check if domain is in the allowed list
     */
    private function is_domain_allowed($domain) {
        // Get custom allowed domains from options
        $custom_domains = get_option('campaignpress_allowed_domains', array());
        $allowed_domains = array_merge(self::$default_allowed_domains, $custom_domains);
        
        // Apply filter for custom domain lists
        $allowed_domains = apply_filters('campaignpress_allowed_domains', $allowed_domains);
        
        // Remove duplicates and normalize
        $allowed_domains = array_unique(array_map('strtolower', $allowed_domains));
        
        // Check exact match
        if (in_array($domain, $allowed_domains, true)) {
            return true;
        }
        
        // Check subdomain match
        foreach ($allowed_domains as $allowed_domain) {
            if ($domain === $allowed_domain || substr($domain, -(strlen($allowed_domain) + 1)) === '.' . $allowed_domain) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if IP address is allowed
     */
    private function is_ip_allowed($domain) {
        // If domain is allowed, resolve and check IP
        $ip = gethostbyname($domain);
        
        if ($ip === $domain) {
            // DNS resolution failed or domain doesn't exist
            return false;
        }
        
        // Validate IP address format
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        
        // Block private IP ranges
        foreach (self::$private_ip_ranges as $range) {
            if ($this->ip_in_range($ip, $range)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ip_in_range($ip, $range) {
        // Handle IPv6
        if (strpos($ip, ':') !== false) {
            return $this->ipv6_in_range($ip, $range);
        }
        
        // Handle IPv4
        return $this->ipv4_in_range($ip, $range);
    }

    /**
     * Check if IPv4 is in range
     */
    private function ipv4_in_range($ip, $range) {
        list($range_ip, $netmask) = explode('/', $range, 2);
        
        $range_decimal = ip2long($range_ip);
        $ip_decimal = ip2long($ip);
        $netmask_decimal = -1 << (32 - (int) $netmask);
        
        return ($ip_decimal & $netmask_decimal) === ($range_decimal & $netmask_decimal);
    }

    /**
     * Check if IPv6 is in range
     */
    private function ipv6_in_range($ip, $range) {
        // Simple IPv6 range check (can be improved for full IPv6 support)
        if ($range === '::1/128' && $ip === '::1') {
            return true;
        }
        
        // For other IPv6 ranges, we'll use a simple check
        // In production, consider using a library like Symfony's IP utils
        return false;
    }

    /**
     * Check for suspicious URL patterns
     */
    private function has_suspicious_patterns($url) {
        $suspicious_patterns = array(
            // Localhost variations
            '/localhost/i',
            '/127\.0\.0\.1/i',
            '/0\.0\.0\.0/i',
            '/169\.254\./i',
            
            // Private IP ranges
            '/^https?:\/\/10\./i',
            '/^https?:\/\/172\.(1[6-9]|2[0-9]|3[01])\./i',
            '/^https?:\/\/192\.168\./i',
            
            // Internal network patterns
            '/internal/i',
            '/intranet/i',
            '/private/i',
            '/local/i',
            
            // Admin/management interfaces
            '/admin/i',
            '/manager/i',
            '/cpanel/i',
            '/phpmyadmin/i',
            
            // File system access attempts
            '/file:\/\//i',
            '/file:\/\///i',
            
            // Protocol abuse
            '/ftp:\/\//i',
            '/sftp:\/\//i',
            '/ssh:\/\//i',
            '/telnet:\/\//i'
        );
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Add domain to allowed list
     */
    public function add_allowed_domain($domain) {
        $domain = strtolower(trim($domain));
        
        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            return new WP_Error('invalid_domain', __('Invalid domain format', 'campaignpress'));
        }
        
        $allowed_domains = get_option('campaignpress_allowed_domains', array());
        
        if (!in_array($domain, $allowed_domains, true)) {
            $allowed_domains[] = $domain;
            update_option('campaignpress_allowed_domains', $allowed_domains);
        }
        
        return true;
    }

    /**
     * Remove domain from allowed list
     */
    public function remove_allowed_domain($domain) {
        $domain = strtolower(trim($domain));
        $allowed_domains = get_option('campaignpress_allowed_domains', array());
        
        $key = array_search($domain, $allowed_domains, true);
        if ($key !== false) {
            unset($allowed_domains[$key]);
            update_option('campaignpress_allowed_domains', array_values($allowed_domains));
        }
        
        return true;
    }

    /**
     * Get allowed domains list
     */
    public function get_allowed_domains() {
        $custom_domains = get_option('campaignpress_allowed_domains', array());
        return array_merge(self::$default_allowed_domains, $custom_domains);
    }

    /**
     * Reset to default allowed domains
     */
    public function reset_allowed_domains() {
        delete_option('campaignpress_allowed_domains');
        
        // Log the reset
        if (class_exists('CampaignPress_Security_Logger')) {
            CampaignPress_Security_Logger::get_instance()->log_event(
                'allowed_domains_reset',
                __('Allowed domains list reset to defaults', 'campaignpress'),
                array(),
                'medium'
            );
        }
        
        return true;
    }

    /**
     * Validate URL and return error details
     */
    public function get_url_validation_report($url) {
        $validation = $this->validate_url($url);
        
        if (is_wp_error($validation)) {
            return array(
                'valid' => false,
                'error' => $validation->get_error_message(),
                'error_code' => $validation->get_error_code(),
                'suggested_action' => $this->get_suggested_action($validation->get_error_code())
            );
        }
        
        $parsed_url = parse_url($url);
        
        return array(
            'valid' => true,
            'domain' => isset($parsed_url['host']) ? strtolower($parsed_url['host']) : '',
            'scheme' => isset($parsed_url['scheme']) ? $parsed_url['scheme'] : '',
            'ip_resolved' => gethostbyname($parsed_url['host'] ?? ''),
            'security_score' => $this->calculate_security_score($url)
        );
    }

    /**
     * Get suggested action for error code
     */
    private function get_suggested_action($error_code) {
        $suggestions = array(
            'invalid_url' => __('Check URL format and ensure it includes http:// or https://', 'campaignpress'),
            'invalid_scheme' => __('Only HTTP and HTTPS protocols are allowed', 'campaignpress'),
            'domain_not_allowed' => __('Contact administrator to add this domain to the allowed list', 'campaignpress'),
            'ip_not_allowed' => __('This IP address is blocked for security reasons', 'campaignpress'),
            'suspicious_url' => __('URL contains potentially malicious patterns', 'campaignpress')
        );
        
        return isset($suggestions[$error_code]) ? $suggestions[$error_code] : __('Contact administrator', 'campaignpress');
    }

    /**
     * Calculate security score for URL
     */
    private function calculate_security_score($url) {
        $score = 100;
        $parsed_url = parse_url($url);
        
        if (!$parsed_url) {
            return 0;
        }
        
        // Penalize HTTP over HTTPS
        if (($parsed_url['scheme'] ?? '') === 'http') {
            $score -= 30;
        }
        
        // Check domain reputation (simplified)
        $domain = strtolower($parsed_url['host'] ?? '');
        
        // Known good domains get bonus
        $reputable_domains = array(
            'google.com', 'github.com', 'wordpress.org', 'twilio.com', 'stripe.com'
        );
        
        if (in_array($domain, $reputable_domains, true)) {
            $score += 10;
        }
        
        // Penalize unusual ports
        if (isset($parsed_url['port']) && !in_array($parsed_url['port'], array(80, 443), true)) {
            $score -= 20;
        }
        
        // Penalize long URLs (potential obfuscation)
        if (strlen($url) > 500) {
            $score -= 10;
        }
        
        return max(0, min(100, $score));
    }
}

// Initialize the URL validator
CampaignPress_URL_Validator::get_instance();
