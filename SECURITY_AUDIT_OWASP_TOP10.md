# CampaignPress Security Audit Report
## OWASP Top 10 (2021) Analysis

**Date:** January 18, 2026  
**Version:** 2.1.0  
**Auditor:** Claude Security Analysis  
**Status:** ⚠️ **MODERATE RISK** - 12 Issues Found (2 Critical, 4 High, 4 Medium, 2 Low)

---

## Executive Summary

This security audit evaluated CampaignPress v2.1.0 against the OWASP Top 10 (2021) security risks. The codebase demonstrates strong security practices in many areas (input sanitization, output escaping, prepared SQL statements), but has several critical vulnerabilities that must be addressed before production deployment.

### Risk Summary

| Severity | Count | Status |
|----------|-------|--------|
| 🔴 Critical | 2 | Must Fix |
| 🟠 High | 4 | Should Fix |
| 🟡 Medium | 4 | Recommended |
| 🟢 Low | 2 | Nice to Have |

---

## A01:2021 - Broken Access Control

### 🔴 CRITICAL: Webhook Authentication Bypass in Testing Mode

**Location:** 
- `/includes/premium/integrations/integrations-init.php` (lines 467-494)
- `/includes/premium/integrations/class-email-integrations.php` (handle_webhook method)
- `/includes/premium/integrations/class-sms-integrations.php` (handle_webhook method)

**Vulnerability:**
```php
// integrations-init.php lines 467-494
public function handle_email_webhook() {
    // Get platform from request
    $platform = sanitize_text_field($_GET['platform'] ?? '');
    
    if (empty($platform)) {
        wp_send_json_error(array('message' => 'Platform not specified'));
    }
    
    // Delegate to email integrations handler
    $this->email_integrations->handle_webhook($platform);
}
```

```php
// class-email-integrations.php
public function handle_webhook($platform) {
    $raw_data = file_get_contents('php://input');
    // ...
    $verified = $this->verify_webhook_signature($platform, $integration, $raw_data);
    if (!$verified && !campaignpress_integrations()->is_testing_mode()) {
        wp_send_json_error(array('message' => 'Invalid webhook signature'));
        return;
    }
    // Continue processing even if signature verification failed in testing mode
}
```

**Issue:** 
- Webhook endpoints are publicly accessible via `wp_ajax_nopriv_*` hooks
- Signature verification is bypassed when `testing_mode` is enabled
- No IP whitelisting or rate limiting on webhook endpoints
- Any attacker can send fake webhook data to manipulate the system

**Impact:**
- Attackers can inject fake email/SMS events
- Can mark valid emails as bounced/spam
- Can manipulate subscriber counts and analytics
- Can disrupt campaign operations

**Remediation:**
1. **Immediate:** Add IP whitelisting for webhook endpoints
2. **Immediate:** Remove testing mode bypass in production
3. **Short-term:** Implement webhook signature verification using HMAC
4. **Long-term:** Add rate limiting to prevent webhook abuse

**Priority:** 🔴 CRITICAL - Fix before production deployment

---

### 🟠 HIGH: Volunteer Portal Session Management

**Location:** `/includes/free/volunteer-portal.php` (lines 866-950)

**Vulnerability:**
```php
// Lines 898-903
// Create a long-lived session token
$session_token = $this->create_portal_token_record(absint($row->volunteer_id), 'session', 30 * DAY_IN_SECONDS);
if (is_wp_error($session_token)) {
    return $session_token;
}

$this->set_cookie('cp_volunteer_session', $session_token, $now + (30 * DAY_IN_SECONDS));
```

**Issue:**
- 30-day session expiration is too long for a political campaign system
- No session invalidation on password reset or security events
- No concurrent session limits
- Session tokens are stored in cookies without secure-only flag on HTTP

**Impact:**
- Compromised tokens give attackers access for 30 days
- No way to revoke compromised sessions
- Volunteers cannot force logout all devices

**Remediation:**
```php
// Reduce session time to 7 days maximum
$session_token = $this->create_portal_token_record(
    absint($row->volunteer_id), 
    'session', 
    7 * DAY_IN_SECONDS  // Changed from 30 to 7 days
);

// Add invalidation method
public function invalidate_all_sessions($volunteer_id) {
    global $wpdb;
    $wpdb->update(
        $this->tokens_table,
        array('consumed' => 1),
        array(
            'volunteer_id' => absint($volunteer_id),
            'token_type' => 'session'
        )
    );
}
```

**Priority:** 🟠 HIGH - Fix before production deployment

---

### 🟠 HIGH: Magic Link Replay Attack Vulnerability

**Location:** `/includes/free/volunteer-portal.php` (lines 866-906)

**Vulnerability:**
```php
// Lines 894-895
// Mark login token consumed
$wpdb->update($this->tokens_table, array('consumed' => 1), array('id' => absint($row->id)));
```

**Issue:**
- Token is marked as consumed AFTER verification
- Race condition window exists between validation and consumption
- No unique constraint enforcement at database level
- Tokens can be replayed if attacker intercepts the link

**Impact:**
- Attackers can replay magic link tokens
- Unauthorized access to volunteer accounts
- Session hijacking through intercepted emails

**Remediation:**
1. Use atomic database operations:
```php
$result = $wpdb->query($wpdb->prepare(
    "UPDATE {$this->tokens_table} 
     SET consumed = 1, last_seen_at = %s 
     WHERE id = %d AND consumed = 0",
    gmdate('Y-m-d H:i:s', $now),
    absint($row->id)
));

if ($result === false || $result === 0) {
    return new WP_Error('token_already_used', __('Login link already used.', 'campaignpress'));
}
```

2. Add unique constraint on token_hash in database schema

**Priority:** 🟠 HIGH - Fix before production deployment

---

## A02:2021 - Cryptographic Failures

### 🟡 MEDIUM: Missing HMAC for Encrypted Data

**Location:** `/includes/premium/integrations/integrations-init.php` (lines 566-610)

**Vulnerability:**
```php
// Lines 566-583
public function encrypt($data) {
    // Generate encryption key from WordPress salts
    $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY);
    
    // Generate initialization vector
    $iv_length = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    // Encrypt data
    $encrypted = openssl_encrypt($data, self::ENCRYPTION_METHOD, $key, 0, $iv);
    
    // Combine IV and encrypted data
    return base64_encode($iv . $encrypted);
}
```

**Issue:**
- No HMAC for integrity verification
- Vulnerable to padding oracle attacks
- No way to detect if encrypted data was tampered with
- AES-256-CBC is susceptible to padding attacks without authentication

**Impact:**
- Attackers can manipulate encrypted data
- Potential exposure of API keys and sensitive information
- Padding oracle attacks could reveal plaintext

**Remediation:**
```php
public function encrypt($data) {
    $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY);
    $iv_length = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    // Encrypt data
    $encrypted = openssl_encrypt($data, self::ENCRYPTION_METHOD, $key, 0, $iv);
    
    // Generate HMAC for integrity
    $hmac = hash_hmac('sha256', $iv . $encrypted, $key);
    
    // Combine IV, encrypted data, and HMAC
    return base64_encode($iv . $encrypted . $hmac);
}

public function decrypt($data) {
    $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY);
    $data = base64_decode($data);
    
    $iv_length = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
    $iv = substr($data, 0, $iv_length);
    $hmac_length = 64; // SHA-256 output length
    $hmac_stored = substr($data, -$hmac_length);
    $encrypted = substr($data, $iv_length, -$hmac_length);
    
    // Verify HMAC first
    $hmac_calculated = hash_hmac('sha256', $iv . $encrypted, $key);
    if (!hash_equals($hmac_stored, $hmac_calculated)) {
        return false; // Data tampered with
    }
    
    // Decrypt data
    return openssl_decrypt($encrypted, self::ENCRYPTION_METHOD, $key, 0, $iv);
}
```

**Priority:** 🟡 MEDIUM - Recommended for production

---

### 🟢 LOW: Weak Key Derivation Function

**Location:** Same as above

**Issue:**
- Uses `hash('sha256', AUTH_KEY . SECURE_AUTH_KEY)` for key derivation
- Should use PBKDF2, bcrypt, or Argon2 for key derivation

**Remediation:**
```php
// Use PBKDF2 for key derivation
$key = hash_pbkdf2('sha256', AUTH_KEY . SECURE_AUTH_KEY, 'campaignpress_salt', 10000, 32);
```

**Priority:** 🟢 LOW - Nice to have

---

## A03:2021 - Injection

### 🟡 MEDIUM: Unprepared SQL Queries for Demo Data Deletion

**Location:** `/includes/premium/premium-demo-content.php` (lines 930-970)

**Vulnerability:**
```php
// Lines 930-931
$wpdb->query("DELETE FROM {$wpdb->prefix}cp_segments WHERE is_demo = 1");
$wpdb->query("DELETE FROM {$wpdb->prefix}cp_interactions WHERE is_demo = 1");
```

**Issue:**
- Direct `$wpdb->query()` calls without prepared statements
- While the `is_demo = 1` condition is hardcoded, it's still a bad practice
- Sets a poor security example for developers

**Impact:**
- Low risk in this specific case (hardcoded condition)
- Establishes bad security patterns
- Potential for future vulnerabilities if condition becomes dynamic

**Remediation:**
```php
$wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->prefix}cp_segments WHERE is_demo = %d",
    1
));
```

**Priority:** 🟡 MEDIUM - Code quality/security best practice

---

**Note:** All other SQL queries in the codebase properly use `$wpdb->prepare()`, which is excellent.

---

## A04:2021 - Insecure Design

### 🟠 HIGH: Publicly Accessible Webhook Endpoints

**Location:** `/includes/premium/integrations/integrations-init.php` (lines 196-200)

**Vulnerability:**
```php
// Lines 196-200
// Webhook receivers (no authentication required)
add_action('wp_ajax_nopriv_campaignpress_email_webhook', array($this, 'handle_email_webhook'));
add_action('wp_ajax_campaignpress_email_webhook', array($this, 'handle_email_webhook'));
add_action('wp_ajax_nopriv_campaignpress_sms_webhook', array($this, 'handle_sms_webhook'));
add_action('wp_ajax_campaignpress_sms_webhook', array($this, 'handle_sms_webhook'));
```

**Issue:**
- Webhook endpoints are publicly accessible to anyone
- Relies solely on signature verification (which can be bypassed in testing mode)
- No IP whitelist or geographic restrictions
- No rate limiting on webhook submissions

**Impact:**
- DoS attacks through webhook spam
- Data manipulation through fake webhooks
- Disruption of campaign operations

**Remediation:**
```php
// 1. Add IP whitelist check
private function is_webhook_ip_allowed() {
    $allowed_ips = get_option('campaignpress_webhook_allowed_ips', array());
    if (empty($allowed_ips)) {
        return false; // Require explicit whitelist
    }
    
    $request_ip = $this->get_client_ip();
    return in_array($request_ip, $allowed_ips, true);
}

// 2. Add rate limiting
private function check_webhook_rate_limit($platform) {
    $key = 'webhook_' . md5($platform . $this->get_client_ip());
    $count = get_transient($key);
    
    if ($count === false) {
        set_transient($key, 1, MINUTE_IN_SECONDS);
        return true;
    }
    
    if ($count >= 10) { // Max 10 webhooks per minute per IP
        return false;
    }
    
    set_transient($key, $count + 1, MINUTE_IN_SECONDS);
    return true;
}

// 3. Update webhook handler
public function handle_email_webhook() {
    if (!$this->is_webhook_ip_allowed()) {
        wp_send_json_error(array('message' => 'IP not allowed'), 403);
    }
    
    if (!$this->check_webhook_rate_limit('email')) {
        wp_send_json_error(array('message' => 'Rate limit exceeded'), 429);
    }
    
    $platform = sanitize_text_field($_GET['platform'] ?? '');
    // ... rest of the code
}
```

**Priority:** 🟠 HIGH - Should fix before production

---

## A05:2021 - Security Misconfiguration

### 🟡 MEDIUM: Testing Mode Not Properly Restricted

**Location:** `/includes/premium/integrations/integrations-init.php` (lines 121-124, 95)

**Vulnerability:**
```php
// Lines 121-124
// Set testing mode from options
$this->testing_mode = get_option('campaignpress_integrations_testing_mode', false);
```

**Issue:**
- Testing mode can be enabled in production with a simple option change
- No safeguards to prevent testing mode in production environments
- No warning when testing mode is enabled on a live site

**Impact:**
- Webhook signature verification can be bypassed
- Security controls can be disabled
- Attackers can exploit testing mode if they gain admin access

**Remediation:**
```php
private function __construct() {
    // Detect environment
    $is_production = $this->is_production_environment();
    
    // Set testing mode with safety checks
    $testing_mode = get_option('campaignpress_integrations_testing_mode', false);
    
    if ($is_production && $testing_mode) {
        // Disable testing mode in production
        $testing_mode = false;
        update_option('campaignpress_integrations_testing_mode', false);
        
        // Log security violation
        error_log('CampaignPress: Testing mode disabled in production environment');
        
        // Notify administrators
        $this->notify_admin_of_testing_mode_violation();
    }
    
    $this->testing_mode = $testing_mode;
    // ...
}

private function is_production_environment() {
    // Check for production indicators
    if (defined('WP_ENV') && WP_ENV === 'production') {
        return true;
    }
    
    if (getenv('WP_ENVIRONMENT_TYPE') === 'production') {
        return true;
    }
    
    // Check domain
    $domain = parse_url(home_url(), PHP_URL_HOST);
    $production_domains = apply_filters('campaignpress_production_domains', array());
    
    return in_array($domain, $production_domains, true);
}
```

**Priority:** 🟡 MEDIUM - Recommended for production

---

### 🟡 MEDIUM: Debug Mode Exposure

**Location:** `/functions.php` (lines 497-499)

**Vulnerability:**
```php
// Lines 497-499
'debug'            => defined('WP_DEBUG') && WP_DEBUG,
```

**Issue:**
- Debug mode is exposed to frontend JavaScript
- No restrictions on which users can see debug information
- Could leak sensitive information to attackers

**Impact:**
- Attackers can determine if debug mode is enabled
- Additional information leakage if debug details are exposed
- Helps attackers plan attacks

**Remediation:**
```php
'debug' => (defined('WP_DEBUG') && WP_DEBUG && current_user_can('manage_options')),
```

**Priority:** 🟡 MEDIUM - Code quality issue

---

## A06:2021 - Vulnerable and Outdated Components

### 🟢 LOW: No Explicit Dependency Version Checks

**Location:** Throughout codebase (no version checks found)

**Issue:**
- No explicit checks for minimum WordPress version
- No checks for PHP version beyond what's in style.css
- No validation of third-party library versions

**Remediation:**
```php
// Add to functions.php
function campaignpress_check_dependencies() {
    global $wp_version;
    
    if (version_compare($wp_version, '6.4', '<')) {
        wp_die(
            sprintf(
                __('CampaignPress requires WordPress 6.4 or higher. You are running version %s.', 'campaignpress'),
                $wp_version
            )
        );
    }
    
    if (version_compare(PHP_VERSION, '8.0', '<')) {
        wp_die(
            sprintf(
                __('CampaignPress requires PHP 8.0 or higher. You are running version %s.', 'campaignpress'),
                PHP_VERSION
            )
        );
    }
}
add_action('admin_init', 'campaignpress_check_dependencies');
```

**Priority:** 🟢 LOW - Nice to have

---

## A07:2021 - Identification and Authentication Failures

### 🔴 CRITICAL: Email Enumeration Prevention Ineffective

**Location:** `/includes/free/volunteer-portal.php` (lines 1077-1082)

**Vulnerability:**
```php
// Lines 1077-1082
$volunteer = $this->find_volunteer_by_email($email);

// Always return a generic response to avoid email enumeration.
$generic_message = __('If that email address is in our system, we\'ll send a login link.', 'campaignpress');

if (!$volunteer) {
    wp_send_json_success(array('message' => $generic_message));
}
```

**Issue:**
- While the response is generic, an attacker can use timing attacks
- If volunteer exists, email is sent (takes longer)
- If volunteer doesn't exist, no email is sent (returns immediately)
- Also vulnerable to password reset timing attacks

**Impact:**
- Attackers can enumerate valid volunteer emails
- Privacy violation
- Targeted social engineering attacks

**Remediation:**
```php
public function ajax_volunteer_login() {
    check_ajax_referer('cp_volunteer_login', 'cp_volunteer_login_nonce');
    
    // Rate limiting: 5 login requests per hour per IP
    if (function_exists('campaignpress_is_rate_limited') && campaignpress_is_rate_limited('volunteer_portal_login', 5, 3600)) {
        wp_send_json_error(array('message' => __('Too many login attempts. Please try again later.', 'campaignpress')));
    }
    
    $email = isset($_POST['volunteer_email']) ? sanitize_email($_POST['volunteer_email']) : '';
    if (empty($email)) {
        wp_send_json_error(array('message' => __('Please enter a valid email address.', 'campaignpress')));
    }
    
    $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : '';
    $redirect_to = wp_validate_redirect($redirect_to, home_url('/'));
    
    $volunteer = $this->find_volunteer_by_email($email);
    
    // Use constant-time operation to prevent timing attacks
    $generic_message = __('If that email address is in our system, we\'ll send a login link.', 'campaignpress');
    
    // Always process in constant time
    if ($volunteer) {
        $login_token = $this->create_portal_token_record(absint($volunteer->id), 'login', 15 * MINUTE_IN_SECONDS);
        $login_url = add_query_arg('cpvp_token', $login_token, $redirect_to);
        
        $site_name = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $subject = sprintf(__('Your Volunteer Portal login link for %s', 'campaignpress'), $site_name);
        $message = sprintf(
            __("Use the link below to access your volunteer portal. This link expires in 15 minutes:\n\n%s\n\nIf you did not request this email, you can ignore it.", 'campaignpress'),
            esc_url_raw($login_url)
        );
        
        wp_mail($email, $subject, $message);
    }
    
    // Always send the same response after the same delay
    // Add artificial delay to prevent timing attacks
    usleep(rand(500000, 1500000)); // 0.5-1.5 second delay
    
    wp_send_json_success(array('message' => $generic_message));
}
```

**Priority:** 🔴 CRITICAL - Fix before production

---

### 🟠 HIGH: No Concurrent Session Limits

**Location:** `/includes/free/volunteer-portal.php` (throughout)

**Issue:**
- No limit on concurrent sessions per volunteer
- Multiple devices can be logged in simultaneously
- No way to view active sessions
- No way to revoke specific sessions

**Impact:**
- Harder to detect account compromise
- Cannot revoke compromised sessions selectively
- Insider threat risk

**Remediation:**
```php
// Add session tracking
private function create_portal_token_record($volunteer_id, $token_type, $ttl_seconds) {
    if (!$this->tokens_table_exists()) {
        return new WP_Error('missing_tokens_table', __('Volunteer portal authentication is not available yet. Please try again later.', 'campaignpress'));
    }
    
    global $wpdb;
    
    // For session tokens, limit to 3 concurrent sessions
    if ($token_type === 'session') {
        $active_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->tokens_table}
             WHERE volunteer_id = %d AND token_type = 'session' AND consumed = 0 AND expires_at > NOW()",
            absint($volunteer_id)
        ));
        
        if ($active_sessions >= 3) {
            // Revoke oldest session
            $wpdb->query($wpdb->prepare(
                "UPDATE {$this->tokens_table} 
                 SET consumed = 1 
                 WHERE volunteer_id = %d AND token_type = 'session' AND consumed = 0 
                 ORDER BY created_at ASC LIMIT 1",
                absint($volunteer_id)
            ));
        }
    }
    
    $token = $this->generate_portal_token(64);
    $token_hash = hash('sha256', $token);
    
    // Store session metadata (user agent, IP)
    $session_data = array(
        'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        'ip_address' => $this->get_client_ip(),
    );
    
    $now = current_time('timestamp', true);
    $expires_at = gmdate('Y-m-d H:i:s', $now + absint($ttl_seconds));
    
    $result = $wpdb->insert($this->tokens_table, array(
        'volunteer_id' => absint($volunteer_id),
        'token_hash' => $token_hash,
        'token_type' => sanitize_text_field($token_type),
        'expires_at' => $expires_at,
        'consumed' => 0,
        'created_at' => gmdate('Y-m-d H:i:s', $now),
        'session_data' => wp_json_encode($session_data),
    ));
    
    if (false === $result) {
        return new WP_Error('token_create_failed', __('Unable to create login token. Please try again.', 'campaignpress'));
    }
    
    return $token;
}

// Add session management page
public function render_active_sessions_page($volunteer_id) {
    global $wpdb;
    
    $sessions = $wpdb->get_results($wpdb->prepare(
        "SELECT id, created_at, last_seen_at, expires_at, session_data
         FROM {$this->tokens_table}
         WHERE volunteer_id = %d AND token_type = 'session' AND consumed = 0 AND expires_at > NOW()
         ORDER BY last_seen_at DESC",
        absint($volunteer_id)
    ));
    
    // Render session management interface
    // ...
}
```

**Priority:** 🟠 HIGH - Recommended for production

---

## A08:2021 - Software and Data Integrity Failures

### 🟡 MEDIUM: No Source Verification for External Data

**Location:** `/includes/free/campaign-communications.php` (lines 768-800)

**Vulnerability:**
```php
// Lines 779-788
$response = wp_remote_post($url, array(
    'headers' => array(
        'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token),
    ),
    'body' => array(
        'To' => $to,
        'From' => $from_number,
        'Body' => $message,
    ),
));
```

**Issue:**
- No validation of response data from external APIs
- Trusts webhook data after signature verification only
- No verification that updates come from legitimate sources

**Impact:**
- Compromised third-party APIs could inject malicious data
- Webhook spoofing could manipulate campaign data
- Supply chain attacks

**Remediation:**
```php
// Validate API responses
$response = wp_remote_post($url, array(
    'timeout' => 15,
    'headers' => array(
        'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token),
    ),
    'body' => array(
        'To' => $to,
        'From' => $from_number,
        'Body' => $message,
    ),
));

if (is_wp_error($response)) {
    return array('success' => false, 'error' => $response->get_error_message());
}

$body = json_decode(wp_remote_retrieve_body($response), true);

// Validate response structure
if (!is_array($body) || !isset($body['sid'])) {
    return array('success' => false, 'error' => 'Invalid API response');
}

// Sanitize response data
$sid = sanitize_text_field($body['sid']);

// Verify expected format
if (!preg_match('/^SM[a-f0-9]{32}$/', $sid)) {
    return array('success' => false, 'error' => 'Invalid message ID format');
}
```

**Priority:** 🟡 MEDIUM - Recommended for production

---

## A09:2021 - Security Logging and Monitoring

### 🟡 MEDIUM: Insufficient Security Event Logging

**Location:** Throughout codebase

**Issue:**
- Some logging exists in integrations module
- No centralized security event logging
- No alerts for suspicious activities
- No audit trail for sensitive operations

**Impact:**
- Difficult to detect security breaches
- No forensic evidence for investigations
- Cannot track unauthorized access attempts

**Remediation:**
```php
// Create centralized security logger
class CampaignPress_Security_Logger {
    
    public static function log_security_event($event_type, $data = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event_type' => $event_type,
            'ip_address' => self::get_client_ip(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'user_id' => get_current_user_id(),
            'data' => wp_json_encode($data),
        );
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'campaignpress_security_logs';
        
        $wpdb->insert($table_name, $log_entry);
        
        // Check for suspicious patterns
        self::check_for_suspicious_activity($event_type, $data);
    }
    
    private static function check_for_suspicious_activity($event_type, $data) {
        // Multiple failed logins from same IP
        $recent_failures = self::get_recent_failures();
        if ($recent_failures >= 5) {
            self::send_security_alert('Multiple failed login attempts detected');
        }
        
        // Unusual admin activity
        if (in_array($event_type, array('admin_login', 'sensitive_operation'))) {
            // Check if login from unusual location
            // ...
        }
    }
    
    private static function send_security_alert($message) {
        $admin_email = get_option('admin_email');
        wp_mail(
            $admin_email,
            '[CampaignPress] Security Alert',
            $message,
            array('Content-Type: text/html; charset=UTF-8')
        );
    }
}

// Log security events
CampaignPress_Security_Logger::log_security_event('volunteer_login_attempt', array(
    'email' => $email,
    'success' => $volunteer !== false,
));

CampaignPress_Security_Logger::log_security_event('webhook_received', array(
    'platform' => $platform,
    'ip' => self::get_client_ip(),
));
```

**Priority:** 🟡 MEDIUM - Recommended for production

---

## A10:2021 - Server-Side Request Forgery (SSRF)

### 🟡 MEDIUM: No URL Validation for External Requests

**Location:** Throughout codebase (wp_remote_post, wp_remote_get calls)

**Issue:**
- No validation of URLs before making external requests
- Potential for SSRF attacks through user-controlled URLs
- No restriction of allowed domains

**Impact:**
- Attackers could make requests to internal network resources
- Port scanning and service discovery
- Access to internal AWS metadata services (if hosted on cloud)

**Remediation:**
```php
// Create URL validator
class CampaignPress_URL_Validator {
    
    public static function is_safe_url($url) {
        $parsed = parse_url($url);
        
        // Must have scheme
        if (!isset($parsed['scheme'])) {
            return false;
        }
        
        // Only allow HTTP/HTTPS
        if (!in_array($parsed['scheme'], array('http', 'https'), true)) {
            return false;
        }
        
        // Block private IP addresses
        $host = $parsed['host'];
        if (self::is_private_ip($host)) {
            return false;
        }
        
        // Block localhost
        if (in_array($host, array('localhost', '127.0.0.1', '::1'), true)) {
            return false;
        }
        
        // Check against whitelist
        $allowed_domains = apply_filters('campaignpress_allowed_external_domains', array(
            'api.twilio.com',
            'api.mailchimp.com',
            'secure.actblue.com',
            'winred.com',
            'api.stripe.com',
            'api.paypal.com',
            'squareup.com',
            'donorbox.org',
            'actionnetwork.org',
        ));
        
        $domain = self::extract_domain($host);
        return in_array($domain, $allowed_domains, true);
    }
    
    private static function is_private_ip($host) {
        $ip = gethostbyname($host);
        
        // IPv4 private ranges
        $private_ranges = array(
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '127.0.0.0/8',
            '169.254.0.0/16',
        );
        
        foreach ($private_ranges as $range) {
            if (self::ip_in_range($ip, $range)) {
                return true;
            }
        }
        
        return false;
    }
}

// Use validator before making requests
function send_sms($to, $message) {
    $url = "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json";
    
    // Validate URL
    if (!CampaignPress_URL_Validator::is_safe_url($url)) {
        return array('success' => false, 'error' => 'Invalid API URL');
    }
    
    $response = wp_remote_post($url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode($account_sid . ':' . $auth_token),
        ),
        'body' => array(
            'To' => $to,
            'From' => $from_number,
            'Body' => $message,
        ),
    ));
    
    // ...
}
```

**Priority:** 🟡 MEDIUM - Recommended for production

---

## Additional Security Concerns

### Content Security Policy (CSP)

**Location:** `/functions.php` (lines 877-883)

**Current State:**
```php
function campaignpress_security_headers($headers) {
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['X-Frame-Options'] = 'SAMEORIGIN';
    $headers['X-XSS-Protection'] = '1; mode=block';
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    return $headers;
}
add_filter('wp_headers', 'campaignpress_security_headers');
```

**Recommendation:** Add Content Security Policy header

```php
function campaignpress_security_headers($headers) {
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['X-Frame-Options'] = 'SAMEORIGIN';
    $headers['X-XSS-Protection'] = '1; mode=block';
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    
    // Add CSP header
    $headers['Content-Security-Policy'] = "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
        "img-src 'self' data: https:; " .
        "font-src 'self' data:; " .
        "connect-src 'self'; " .
        "frame-src 'self'; " .
        "object-src 'none'; " .
        "base-uri 'self'; " .
        "form-action 'self';";
    
    return $headers;
}
```

---

## Positive Security Findings

The following security practices were implemented correctly and should be maintained:

1. ✅ **Input Sanitization** - Consistent use of `sanitize_text_field()`, `sanitize_email()`, `absint()`
2. ✅ **Output Escaping** - Proper use of `esc_html()`, `esc_url()`, `esc_attr()`, `wp_kses_post()`
3. ✅ **Nonce Verification** - All AJAX handlers check nonces
4. ✅ **Capability Checks** - Admin functions check `current_user_can('manage_options')`
5. ✅ **Prepared SQL Statements** - Most queries use `$wpdb->prepare()`
6. ✅ **Rate Limiting** - Implemented for volunteer portal actions
7. ✅ **Cookie Security** - HttpOnly and SameSite flags set
8. ✅ **Security Headers** - X-Content-Type-Options, X-Frame-Options, etc.
9. ✅ **WordPress Version Hidden** - Removed from head tag
10. ✅ **Encrypted Storage** - API keys encrypted using AES-256-CBC

---

## Remediation Priority Matrix

| Priority | Issue | Effort | Impact | Timeline |
|----------|-------|--------|--------|----------|
| 🔴 CRITICAL | Webhook Authentication Bypass | Medium | High | Immediate |
| 🔴 CRITICAL | Email Enumeration Timing Attack | Low | High | Immediate |
| 🟠 HIGH | Volunteer Portal Session Management | Low | Medium | 1 week |
| 🟠 HIGH | Magic Link Replay Attack | Medium | Medium | 1 week |
| 🟠 HIGH | Publicly Accessible Webhooks | Medium | High | 1 week |
| 🟠 HIGH | No Concurrent Session Limits | Medium | Medium | 2 weeks |
| 🟡 MEDIUM | Missing HMAC for Encryption | Low | Medium | 1 month |
| 🟡 MEDIUM | Unprepared SQL Queries | Low | Low | 1 month |
| 🟡 MEDIUM | Testing Mode Restrictions | Medium | Medium | 1 month |
| 🟡 MEDIUM | Security Logging | High | Medium | 2 months |
| 🟡 MEDIUM | SSRF Protection | Medium | Medium | 2 months |
| 🟡 MEDIUM | No Source Verification | Medium | Medium | 1 month |
| 🟢 LOW | Debug Mode Exposure | Low | Low | Ongoing |
| 🟢 LOW | Dependency Version Checks | Low | Low | Ongoing |

---

## Conclusion

CampaignPress demonstrates a strong foundation of security practices with proper input sanitization, output escaping, and SQL injection prevention. However, there are several critical vulnerabilities that must be addressed before production deployment:

### Must Fix Before Production:
1. 🔴 Webhook authentication bypass in testing mode
2. 🔴 Email enumeration via timing attacks
3. 🟠 Magic link replay attacks
4. 🟠 Publicly accessible webhook endpoints

### Recommended Before Production:
5. 🟠 Volunteer session management improvements
6. 🟠 Concurrent session limits
7. 🟡 HMAC for encrypted data integrity
8. 🟡 Security event logging
9. 🟡 SSRF protection

### Timeline:
- **Immediate (1-2 days):** Critical vulnerabilities
- **1 week:** High-priority issues
- **1 month:** Medium-priority issues
- **2 months:** Complete security hardening

---

**Report Generated:** January 18, 2026  
**Auditor:** Claude Security Analysis  
**Next Review:** After all critical and high-priority fixes are implemented
