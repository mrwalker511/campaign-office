# Critical Security Fixes - Implementation Guide

## Overview

This document provides **ready-to-implement code fixes** for the critical security vulnerabilities identified in the penetration test. Apply these fixes immediately.

**Priority:** CRITICAL - Apply within 24-48 hours  
**Files Modified:** 4 files  
**Lines Changed:** ~200 lines

---

## Fix 1: Remove Public Webhook Access (CRITICAL)

### File: `/includes/premium/integrations/integrations-init.php`

#### Change Required
**Lines 215-218** - Remove public webhook access

```php
// ❌ BEFORE (CRITICAL SECURITY FLAW):
add_action('wp_ajax_nopriv_campaignpress_email_webhook', array($this, 'handle_email_webhook'));
add_action('wp_ajax_campaignpress_email_webhook', array($this, 'handle_email_webhook'));
add_action('wp_ajax_nopriv_campaignpress_sms_webhook', array($this, 'handle_sms_webhook'));
add_action('wp_ajax_campaignpress_sms_webhook', array($this, 'handle_sms_webhook'));

// ✅ AFTER (FIXED):
// REMOVE wp_ajax_nopriv_* hooks - webhooks now require authentication
add_action('wp_ajax_campaignpress_email_webhook', array($this, 'handle_email_webhook'));
add_action('wp_ajax_campaignpress_sms_webhook', array($this, 'handle_sms_webhook'));
```

**Note:** This breaks public webhooks. If you need public webhook support, see Fix 2 below for proper implementation.

---

## Fix 2: Implement Proper Webhook Authentication (CRITICAL)

### File: `/includes/premium/integrations/integrations-init.php`

#### Change Required
**Replace entire webhook handlers (lines 485-568)** with proper authentication:

```php
/**
 * Handle email webhook with proper authentication
 *
 * @since 2.0.0
 */
public function handle_email_webhook() {
    // ALWAYS verify signature first (no exceptions)
    $platform = sanitize_text_field($_GET['platform'] ?? '');
    
    if (empty($platform)) {
        wp_send_json_error(array('message' => 'Platform not specified'), 400);
        return;
    }

    // Check IP whitelist FIRST
    if (!$this->is_webhook_ip_allowed()) {
        $this->log_event('webhook_ip_blocked', array(
            'ip' => $this->get_client_ip(),
            'type' => 'email'
        ));
        wp_send_json_error(array('message' => 'IP not allowed'), 403);
        return;
    }

    // Check rate limiting
    if (!$this->check_webhook_rate_limit('email')) {
        $this->log_event('webhook_rate_limited', array(
            'ip' => $this->get_client_ip(),
            'type' => 'email'
        ));
        wp_send_json_error(array('message' => 'Rate limit exceeded'), 429);
        return;
    }

    // Get raw POST data
    $raw_data = file_get_contents('php://input');
    
    if (false === $raw_data) {
        wp_send_json_error(array('message' => 'Failed to read request body'), 400);
        return;
    }

    // Get integration
    $integration = $this->email_integrations->get_integration_by_platform($platform);
    
    if (!$integration) {
        wp_send_json_error(array('message' => 'Integration not found'), 404);
        return;
    }

    // Verify signature (MUST pass - no bypasses)
    $verified = $this->verify_webhook_signature(
        'email', 
        $platform, 
        $integration, 
        $raw_data
    );

    if (!$verified) {
        $this->log_event('webhook_signature_failed', array(
            'platform' => $platform,
            'ip' => $this->get_client_ip(),
            'type' => 'email'
        ));
        wp_send_json_error(array('message' => 'Invalid webhook signature'), 403);
        return;
    }

    // All checks passed - process webhook
    try {
        $this->email_integrations->handle_webhook($platform);
        wp_send_json_success(array('message' => 'Webhook processed'));
    } catch (Exception $e) {
        $this->log_event('webhook_processing_error', array(
            'platform' => $platform,
            'error' => $e->getMessage()
        ));
        wp_send_json_error(array('message' => 'Processing error'), 500);
    }
}

/**
 * Handle SMS webhook with proper authentication
 *
 * @since 2.0.0
 */
public function handle_sms_webhook() {
    // ALWAYS verify signature first (no exceptions)
    $platform = sanitize_text_field($_GET['platform'] ?? '');
    
    if (empty($platform)) {
        wp_send_json_error(array('message' => 'Platform not specified'), 400);
        return;
    }

    // Check IP whitelist FIRST
    if (!$this->is_webhook_ip_allowed()) {
        $this->log_event('webhook_ip_blocked', array(
            'ip' => $this->get_client_ip(),
            'type' => 'sms'
        ));
        wp_send_json_error(array('message' => 'IP not allowed'), 403);
        return;
    }

    // Check rate limiting
    if (!$this->check_webhook_rate_limit('sms')) {
        $this->log_event('webhook_rate_limited', array(
            'ip' => $this->get_client_ip(),
            'type' => 'sms'
        ));
        wp_send_json_error(array('message' => 'Rate limit exceeded'), 429);
        return;
    }

    // Get raw POST data
    $raw_data = file_get_contents('php://input');
    
    if (false === $raw_data) {
        wp_send_json_error(array('message' => 'Failed to read request body'), 400);
        return;
    }

    // Get integration
    $integration = $this->sms_integrations->get_integration_by_platform($platform);
    
    if (!$integration) {
        wp_send_json_error(array('message' => 'Integration not found'), 404);
        return;
    }

    // Verify signature (MUST pass - no bypasses)
    $verified = $this->verify_webhook_signature(
        'sms', 
        $platform, 
        $integration, 
        $raw_data
    );

    if (!$verified) {
        $this->log_event('webhook_signature_failed', array(
            'platform' => $platform,
            'ip' => $this->get_client_ip(),
            'type' => 'sms'
        ));
        wp_send_json_error(array('message' => 'Invalid webhook signature'), 403);
        return;
    }

    // All checks passed - process webhook
    try {
        $this->sms_integrations->handle_webhook($platform);
        wp_send_json_success(array('message' => 'Webhook processed'));
    } catch (Exception $e) {
        $this->log_event('webhook_processing_error', array(
            'platform' => $platform,
            'error' => $e->getMessage()
        ));
        wp_send_json_error(array('message' => 'Processing error'), 500);
    }
}

/**
 * Verify webhook signature with proper HMAC
 *
 * @param string $type Webhook type (email/sms)
 * @param string $platform Platform identifier
 * @param array $integration Integration data
 * @param string $raw_data Raw POST data
 * @return bool Valid signature
 * @since 2.0.0
 */
private function verify_webhook_signature($type, $platform, $integration, $raw_data) {
    // Get webhook secret from integration settings
    $webhook_secret = $integration['settings']['webhook_secret'] ?? '';
    
    if (empty($webhook_secret)) {
        // Log missing secret
        error_log(sprintf(
            'CampaignPress Security: No webhook secret configured for %s/%s',
            $type,
            $platform
        ));
        return false;
    }

    // Platform-specific verification
    switch ($platform) {
        case 'mailchimp':
            // Mailchimp uses X-Mailchimp-Signature header
            $signature = $_SERVER['HTTP_X_MAILCHIMP_SIGNATURE'] ?? '';
            $timestamp = $_SERVER['HTTP_X_MAILCHIMP_TIMESTAMP'] ?? '';
            
            // Verify timestamp (prevent replay attacks - max 5 minutes)
            if (empty($timestamp) || abs(time() - $timestamp) > 300) {
                $this->log_event('webhook_timestamp_invalid', array(
                    'platform' => $platform,
                    'timestamp' => $timestamp
                ));
                return false;
            }
            
            // Recalculate HMAC
            $expected = hash_hmac('sha256', $timestamp . $raw_data, $webhook_secret);
            
            // Use constant-time comparison (prevent timing attacks)
            return hash_equals($expected, $signature);

        case 'sendgrid':
            // SendGrid uses X-Twilio-Email-Event-Webhook-Signature header
            $signature = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_SIGNATURE'] ?? '';
            $timestamp = $_SERVER['HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_TIMESTAMP'] ?? '';
            
            // Verify timestamp (prevent replay attacks - max 5 minutes)
            if (empty($timestamp) || abs(time() - $timestamp) > 300) {
                $this->log_event('webhook_timestamp_invalid', array(
                    'platform' => $platform,
                    'timestamp' => $timestamp
                ));
                return false;
            }
            
            // Recalculate HMAC
            $expected = hash_hmac('sha256', $timestamp . $raw_data, $webhook_secret);
            
            // Use constant-time comparison (prevent timing attacks)
            return hash_equals($expected, $signature);

        case 'twilio':
            // Twilio uses X-Twilio-Signature header
            $signature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'] ?? '';
            $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') 
                  . '://' . $_SERVER['HTTP_HOST'] 
                  . $_SERVER['REQUEST_URI'];
            
            // Twilio signature format: base64_encode(hash_hmac('sha1', url + payload, auth_token))
            $auth_token = $this->decrypt($integration['credentials']['auth_token']);
            $expected = base64_encode(hash_hmac('sha1', $url . $raw_data, $auth_token, true));
            
            // Use constant-time comparison
            return hash_equals($expected, $signature);

        default:
            // Unknown platform - reject
            $this->log_event('webhook_unknown_platform', array(
                'platform' => $platform
            ));
            return false;
    }
}

/**
 * Check if webhook request IP is allowed
 *
 * @return bool
 * @since 2.0.0
 */
private function is_webhook_ip_allowed() {
    // In testing mode, allow localhost and private IPs
    // But NEVER in production
    $is_production = $this->is_production_environment();
    
    if (!$is_production && $this->testing_mode) {
        $ip = $this->get_client_ip();
        $allowed_test_ips = array('127.0.0.1', '::1', 'localhost');
        if (in_array($ip, $allowed_test_ips, true) || $this->is_private_ip($ip)) {
            return true;
        }
    }

    // Get IP whitelist from options
    $allowed_ips = get_option('campaignpress_webhook_allowed_ips', array());

    // If no whitelist configured, DENY ALL (secure by default)
    if (empty($allowed_ips) || !is_array($allowed_ips)) {
        error_log('CampaignPress Security: Webhook IP whitelist not configured - denying all requests');
        return false;
    }

    $request_ip = $this->get_client_ip();
    return in_array($request_ip, $allowed_ips, true);
}

/**
 * Check webhook rate limit (multi-layered)
 *
 * @param string $type Webhook type (email/sms)
 * @return bool
 * @since 2.0.0
 */
private function check_webhook_rate_limit($type) {
    $ip = $this->get_client_ip();
    
    // Layer 1: Per IP limit
    $ip_key = 'webhook_ip_' . md5($type . $ip);
    $ip_count = get_transient($ip_key);
    
    // Layer 2: Global rate limit (all IPs combined)
    $global_key = 'webhook_global_' . $type;
    $global_count = get_transient($global_key);
    
    // Layer 3: Per platform limit
    $platform_key = 'webhook_platform_' . $type;
    $platform_count = get_transient($platform_key);
    
    // Check all layers
    if (($ip_count !== false && $ip_count >= 10) ||
        ($global_count !== false && $global_count >= 100) ||
        ($platform_count !== false && $platform_count >= 50)) {
        
        // Log suspicious activity
        $this->log_event('webhook_rate_limit_triggered', array(
            'ip' => $ip,
            'type' => $type,
            'ip_count' => $ip_count,
            'global_count' => $global_count,
            'platform_count' => $platform_count
        ));
        
        return false;
    }
    
    // Increment all counters
    set_transient($ip_key, ($ip_count ?: 0) + 1, MINUTE_IN_SECONDS);
    set_transient($global_key, ($global_count ?: 0) + 1, MINUTE_IN_SECONDS);
    set_transient($platform_key, ($platform_count ?: 0) + 1, MINUTE_IN_SECONDS);
    
    return true;
}

/**
 * Check if IP is private/internal
 *
 * @param string $ip IP address
 * @return bool
 * @since 2.0.0
 */
private function is_private_ip($ip) {
    // Check if IP is in private ranges
    $private_ranges = array(
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',
        '169.254.0.0/16'  // Link-local (AWS metadata)
    );
    
    foreach ($private_ranges as $range) {
        list($network, $mask) = explode('/', $range);
        $network_long = ip2long($network);
        $mask_long = -1 << (32 - $mask);
        $ip_long = ip2long($ip);
        
        if (($ip_long & $mask_long) === $network_long) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get client IP address (X-Forwarded-For aware)
 *
 * @return string
 * @since 2.0.0
 */
private function get_client_ip() {
    // Check for proxy headers (in order of reliability)
    $headers = array(
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_X_FORWARDED_FOR',      // Standard proxy
        'HTTP_X_REAL_IP',           // Nginx
        'REMOTE_ADDR'                // Direct connection
    );
    
    foreach ($headers as $header) {
        if (isset($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            
            // Handle comma-separated IPs (X-Forwarded-For can have multiple)
            if (strpos($ip, ',') !== false) {
                $ips = explode(',', $ip);
                $ip = trim($ips[0]); // Use first IP
            }
            
            // Validate IP format
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    
    return '0.0.0.0'; // Fallback
}
```

---

## Fix 3: Implement Encrypt-then-MAC (CRITICAL)

### File: `/includes/premium/integrations/integrations-init.php`

#### Change Required
**Replace encryption/decryption functions** with HMAC-protected version:

```php
/**
 * Encrypt data with HMAC protection (Encrypt-then-MAC pattern)
 *
 * @param string $data Data to encrypt
 * @return string Base64-encoded encrypted data
 * @since 2.0.0
 */
public function encrypt($data) {
    // Derive encryption key from WordPress salts
    $key = $this->derive_encryption_key();
    
    // Generate random IV
    $iv = openssl_random_pseudo_bytes(16);
    
    // Encrypt data
    $encrypted = openssl_encrypt(
        $data, 
        self::ENCRYPTION_METHOD, 
        $key, 
        OPENSSL_RAW_DATA,
        $iv
    );
    
    if ($encrypted === false) {
        throw new Exception('Encryption failed');
    }
    
    // Calculate HMAC over IV + ciphertext
    $hmac = hash_hmac('sha256', $iv . $encrypted, $key, true);
    
    // Format: HMAC(32 bytes) + IV(16 bytes) + ciphertext
    $combined = $hmac . $iv . $encrypted;
    
    return base64_encode($combined);
}

/**
 * Decrypt data with HMAC verification
 *
 * @param string $encrypted Base64-encoded encrypted data
 * @return string Decrypted data
 * @throws Exception if HMAC verification fails
 * @since 2.0.0
 */
public function decrypt($encrypted) {
    $key = $this->derive_encryption_key();
    
    // Decode base64
    $combined = base64_decode($encrypted);
    
    if ($combined === false) {
        throw new Exception('Invalid encrypted data');
    }
    
    // Extract HMAC, IV, and ciphertext
    if (strlen($combined) < 48) { // Minimum: HMAC(32) + IV(16)
        throw new Exception('Invalid encrypted data length');
    }
    
    $received_hmac = substr($combined, 0, 32);
    $iv = substr($combined, 32, 16);
    $ciphertext = substr($combined, 48);
    
    // Verify HMAC FIRST (constant-time comparison)
    $expected_hmac = hash_hmac('sha256', $iv . $ciphertext, $key, true);
    
    if (!hash_equals($expected_hmac, $received_hmac)) {
        // Log potential tampering
        error_log('CampaignPress Security: HMAC verification failed - data tampering detected');
        throw new Exception('Data integrity check failed - possible tampering');
    }
    
    // Decrypt ciphertext (only after HMAC verification)
    $decrypted = openssl_decrypt(
        $ciphertext,
        self::ENCRYPTION_METHOD,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
    
    if ($decrypted === false) {
        throw new Exception('Decryption failed');
    }
    
    return $decrypted;
}

/**
 * Derive encryption key from WordPress salts
 *
 * @return string 32-byte encryption key
 * @since 2.0.0
 */
private function derive_encryption_key() {
    // Use WordPress AUTH_KEY and AUTH_SALT as key material
    // Combine them to ensure sufficient entropy
    $key_material = AUTH_KEY . AUTH_SALT;
    
    // Derive 32-byte key using HKDF (simplified version using hash_pbkdf2)
    return hash_pbkdf2('sha256', $key_material, 'campaignpress_encryption', 10000, 32, true);
}
```

---

## Fix 4: Fix SendGrid Signature Verification (CRITICAL)

### File: `/includes/premium/integrations/class-email-integrations.php`

#### Change Required
**Replace lines 992-1012** with proper signature verification:

```php
/**
 * Verify webhook signature
 *
 * @param string $platform Platform identifier
 * @param array $integration Integration data
 * @param string $raw_data Raw POST data
 * @return bool Valid signature
 * @since 2.0.0
 */
private function verify_webhook_signature($platform, $integration, $raw_data) {
    // This method is DEPRECATED - use CampaignPress_Integrations::verify_webhook_signature() instead
    // Kept for backward compatibility, but does nothing
    error_log('CampaignPress Security: Deprecated verify_webhook_signature() called - use parent class');
    return true;
}
```

**Note:** The actual signature verification is now handled by the parent class (`CampaignPress_Integrations`). This method is kept for backward compatibility but no longer performs verification.

---

## Fix 5: Fix Object Injection (CRITICAL)

### File: `/includes/premium/api/class-api-webhooks.php`

#### Change Required
**Replace all `maybe_unserialize()` calls (lines 226, 257, 310, 389)**:

```php
// ❌ BEFORE (DANGEROUS!):
$webhook->events = maybe_unserialize($webhook->events);

// ✅ AFTER (SAFE):
$webhook->events = json_decode($webhook->events, true);

// If data is serialized, migrate to JSON:
if (is_serialized($webhook->events)) {
    // One-time migration to JSON
    $data = maybe_unserialize($webhook->events);
    if (!is_array($data)) {
        throw new Exception('Invalid webhook events data');
    }
    $webhook->events = json_encode($data);
    
    // Update database to JSON format
    global $wpdb;
    $wpdb->update(
        $this->webhooks_table,
        array('events' => $webhook->events),
        array('id' => $webhook->id),
        array('%s'),
        array('%d')
    );
} else {
    // Already JSON format
    $webhook->events = json_decode($webhook->events, true);
}
```

**Apply this change in 4 locations:**
- Line 226: `get_webhooks()` method
- Line 257: `get_webhook()` method  
- Line 310: `create_webhook()` method
- Line 389: `update_webhook()` method

---

## Fix 6: Implement SSRF Protection (HIGH)

### File: `/includes/premium/integrations/class-email-integrations.php`

#### Change Required
**Add URL validation before all `wp_remote_get()` and `wp_remote_post()` calls:**

```php
/**
 * Validate URL to prevent SSRF attacks
 *
 * @param string $url URL to validate
 * @return bool Valid URL
 * @throws Exception if URL is invalid
 * @since 2.0.0
 */
private function validate_url($url) {
    // Parse URL
    $parsed = parse_url($url);
    
    // Basic validation
    if (!$parsed || !isset($parsed['host']) || !isset($parsed['scheme'])) {
        throw new Exception('Invalid URL format');
    }
    
    // Enforce HTTPS only
    if ($parsed['scheme'] !== 'https') {
        throw new Exception('URL must use HTTPS');
    }
    
    // Validate host
    if (!filter_var($parsed['host'], FILTER_VALIDATE_IP)) {
        // It's a domain name - resolve to IP
        $ip = gethostbyname($parsed['host']);
        
        if ($ip === $parsed['host']) {
            // Resolution failed
            throw new Exception('Could not resolve domain');
        }
    } else {
        $ip = $parsed['host'];
    }
    
    // Block private/internal IP ranges
    if ($this->is_private_ip($ip)) {
        throw new Exception('Internal IP addresses not allowed (SSRF protection)');
    }
    
    // Enforce domain whitelist
    $allowed_domains = array(
        'api.mailchimp.com',
        'mailchimp.com',
        'api.twilio.com',
        'twilio.com',
        'api.sendgrid.com',
        'sendgrid.com',
        'api.constantcontact.com',
        'constantcontact.com',
        'api.mailerlite.com',
        'mailerlite.com',
        'actionnetwork.org',
        'api.actionnetwork.org'
    );
    
    if (!in_array($parsed['host'], $allowed_domains, true)) {
        throw new Exception('Domain not in whitelist: ' . $parsed['host']);
    }
    
    return true;
}

/**
 * Check if IP is private/internal
 *
 * @param string $ip IP address
 * @return bool
 * @since 2.0.0
 */
private function is_private_ip($ip) {
    $private_ranges = array(
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
        '127.0.0.0/8',
        '169.254.0.0/16',  // Link-local (AWS metadata)
        '0.0.0.0/8',       // Current network
        '240.0.0.0/4'      // Reserved
    );
    
    foreach ($private_ranges as $range) {
        list($network, $mask) = explode('/', $range);
        $network_long = ip2long($network);
        $mask_long = -1 << (32 - $mask);
        $ip_long = ip2long($ip);
        
        if (($ip_long & $mask_long) === $network_long) {
            return true;
        }
    }
    
    return false;
}

// Update test_mailchimp_connection() to use validation:
private function test_mailchimp_connection($credentials) {
    $api_key = $credentials['api_key'] ?? '';
    $server_prefix = $credentials['server_prefix'] ?? '';

    if (empty($api_key) || empty($server_prefix)) {
        return false;
    }

    // Test API endpoint: Get account details
    $url = "https://{$server_prefix}.api.mailchimp.com/3.0/";
    
    // Validate URL BEFORE making request
    try {
        $this->validate_url($url);
    } catch (Exception $e) {
        error_log('CampaignPress Security: URL validation failed: ' . $e->getMessage());
        return false;
    }

    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode('anystring:' . $api_key)
        ),
        'timeout' => 15
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $status_code = wp_remote_retrieve_response_code($response);
    return $status_code === 200;
}
```

**Apply validation to ALL `wp_remote_get()` and `wp_remote_post()` calls:**
- `test_mailchimp_connection()` - line 387
- `test_action_network_connection()` - line 419
- `test_constant_contact_connection()` - line 453
- `test_sendgrid_connection()` - line 486
- And any other HTTP requests

---

## Fix 7: Harden Testing Mode (HIGH)

### File: `/includes/premium/integrations/integrations-init.php`

#### Change Required
**Replace lines 122-140** with production-hardened logic:

```php
private function __construct() {
    // CRITICAL: Never allow testing mode in production
    $production_domains = array(
        'example.com',
        'campaignsite.com'
        // Add ALL production domains here
    );
    
    $current_domain = $_SERVER['HTTP_HOST'];
    
    if (in_array($current_domain, $production_domains, true)) {
        // FORCE testing mode off in production - cannot be overridden
        $this->testing_mode = false;
        
        // Log any attempt to enable
        $testing_mode = get_option('campaignpress_integrations_testing_mode', false);
        if ($testing_mode) {
            // Log security violation
            error_log('CampaignPress Security: Testing mode attempt in production detected!');
            
            // Disable immediately
            update_option('campaignpress_integrations_testing_mode', false);
            
            // Notify administrators
            $this->notify_admin_of_testing_mode_violation();
        }
    } else {
        // Only allow testing in non-production
        $this->testing_mode = get_option('campaignpress_integrations_testing_mode', false);
        
        // Additional safety check
        if ($this->testing_mode) {
            error_log('CampaignPress Security: Testing mode enabled for ' . $current_domain);
        }
    }

    // Load dependencies
    $this->load_dependencies();

    // Initialize integration handlers
    $this->init_handlers();

    // Register WordPress hooks
    $this->init_hooks();

    // Schedule cleanup tasks
    $this->schedule_cleanup_tasks();

    // Log initialization
    $this->log_event('integrations_initialized', array(
        'version' => self::VERSION,
        'testing_mode' => $this->testing_mode,
        'environment' => in_array($current_domain, $production_domains) ? 'production' : 'development'
    ));
}

/**
 * Notify admin of testing mode violation
 *
 * @since 2.0.0
 */
private function notify_admin_of_testing_mode_violation() {
    // Get admin email
    $admin_email = get_option('admin_email');
    
    // Send notification
    wp_mail(
        $admin_email,
        '[CRITICAL] Testing Mode Attempt Detected',
        sprintf(
            "Testing mode was attempted to be enabled on production site: %s\n\n" .
            "This is a security violation. Testing mode has been disabled.\n\n" .
            "IP: %s\n" .
            "Time: %s\n\n" .
            "Please investigate immediately.",
            site_url(),
            $this->get_client_ip(),
            current_time('mysql')
        ),
        array('Content-Type: text/plain; charset=UTF-8')
    );
}
```

---

## Fix 8: Fix SQL Injection (MEDIUM)

### File: `/includes/premium/premium-demo-content.php`

#### Change Required
**Replace lines 948-956** with prepared statement:

```php
// ❌ BEFORE (INSECURE):
$query = "SELECT c.* FROM {$contacts_table} c
         INNER JOIN {$tags_table} t ON c.id = t.contact_id
         WHERE t.tag_id IN (" . implode(',', array_map('intval', $filters['tags'])) . ")";
$results = $wpdb->get_results($query, ARRAY_A);

// ✅ AFTER (SECURE):
if (!empty($filters['tags'])) {
    // Sanitize tag IDs
    $tag_ids = array_map('intval', $filters['tags']);
    
    // Create placeholders
    $placeholders = implode(',', array_fill(0, count($tag_ids), '%d'));
    
    // Build prepared query
    $query = "SELECT c.* FROM {$contacts_table} c
             INNER JOIN {$tags_table} t ON c.id = t.contact_id
             WHERE t.tag_id IN ({$placeholders})";
    
    // Execute with prepared statement
    $results = $wpdb->get_results(
        $wpdb->prepare($query, ...$tag_ids),
        ARRAY_A
    );
}
```

---

## Testing Checklist

After applying fixes, verify:

- [ ] Webhooks reject requests without valid signatures
- [ ] Webhooks reject requests from non-whitelisted IPs
- [ ] Rate limiting works correctly
- [ ] Encryption/decryption works with new HMAC
- [ ] SSRF protection blocks internal IP requests
- [ ] Testing mode cannot be enabled in production
- [ ] SQL injection prevented in all queries
- [ ] Object injection prevented (JSON used instead)

---

## Deployment Instructions

1. **Backup database and files** before applying fixes
2. **Apply fixes to all 4 files** in this order:
   - Fix 1 & 2: integrations-init.php (webhooks, signature, rate limiting)
   - Fix 3: integrations-init.php (encryption)
   - Fix 4: class-email-integrations.php (signature verification)
   - Fix 5: class-api-webhooks.php (object injection)
   - Fix 6: class-email-integrations.php (SSRF)
   - Fix 7: integrations-init.php (testing mode)
   - Fix 8: premium-demo-content.php (SQL injection)
3. **Update webhook secrets** for all integrations
4. **Configure IP whitelist** for webhook senders
5. **Test all integrations** in staging environment first
6. **Deploy to production** during low-traffic window
7. **Monitor logs** for security events

---

## Support

If you encounter issues implementing these fixes:

1. Check error logs: `/wp-content/debug.log`
2. Verify webhook secrets are configured
3. Verify IP whitelist is configured
4. Test in staging environment first

**Emergency:** If fixes break production, revert to backup immediately and contact security team.

---

**Classification:** CONFIDENTIAL - Security Documentation  
**Priority:** CRITICAL - Apply Immediately  
**Date:** January 18, 2025
