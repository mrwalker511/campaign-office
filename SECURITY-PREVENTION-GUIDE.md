# Security Prevention Guide: CampaignPress Lessons Learned

## Why Issues Weren't Caught Initially

### 1. **Speed vs. Thoroughness Tradeoff**
When building the initial foundation, I prioritized getting a working implementation with core functionality. This "feature-first" approach meant:
- Focus on **what** the code does rather than **how safely** it does it
- Relying on WordPress functions without validating their usage context
- Not implementing defense-in-depth validation strategies

### 2. **Contextual API Misunderstandings**
Several issues stemmed from not fully understanding WordPress API constraints:
- `register_activation_hook()` works in plugins but NOT themes (Issue #6)
- `send_headers` hook fires too late for security headers (Issue #11)
- `strtotime()` unreliable for time-only inputs without date context (Issue #7)

These require deep WordPress expertise that wasn't applied during initial development.

### 3. **Missing Security-First Mindset**
The initial implementation didn't systematically apply these security principles:
- **Input Validation**: Assuming WordPress handles all input sanitization
- **Output Escaping**: Not escaping every user-controlled value
- **Whitelist Validation**: Using general sanitizers instead of strict whitelists
- **Defense in Depth**: Single layer of protection instead of multiple

### 4. **Lack of Systematic Code Review Process**
No structured review process was applied to catch:
- N+1 database query patterns
- Missing error logging for failures
- Anonymous functions (harder to debug/remove)
- Inline JavaScript in PHP templates

---

## How to Prevent Security Bugs in Future Projects

### Phase 1: Project Kickoff - Security Requirements Specification

Include these requirements in your initial project brief:

```markdown
## Security & Quality Requirements

### Input Validation
- ALL user input (form fields, URL parameters, block attributes) must be validated
- Use whitelist validation for enumerated values (not just sanitize_text_field)
- Type-check ALL variables before use (is_string, is_numeric, is_bool)
- Validate data formats with regex (dates, times, URLs, emails)

### Output Escaping
- ALL dynamic output must use context-appropriate escaping:
  - HTML: esc_html()
  - Attributes: esc_attr()
  - URLs: esc_url()
  - JavaScript: esc_js()
  - Database: $wpdb->prepare()

### WordPress API Usage
- Research each WordPress function's proper context (plugins vs themes)
- Use correct hooks for timing-sensitive operations
- Never use deprecated or theme-incompatible functions

### Performance Requirements
- Minimize database queries (avoid N+1 patterns)
- Use caching for repeated expensive operations
- Single query to fetch all related meta data

### Error Handling
- Log ALL operation failures (wp_insert_post, wp_insert_term, etc.)
- Use error_log() for debugging
- Return meaningful error messages to users with permissions
- Never expose system details to non-admin users

### Code Quality
- Use named functions (not anonymous) for filters/actions
- Add strict comparison (true/false) to all in_array() calls
- Document all security decisions in comments
```

---

### Phase 2: Implementation - Security Checklist

Use this checklist during development for **every** feature:

#### ✅ Input Validation Checklist

**For Form Fields & Meta Boxes:**
```php
// ❌ BAD - No validation
$value = $_POST['field_name'];
update_post_meta($post_id, 'key', $value);

// ✅ GOOD - Whitelist + nonce + capability check
if (!isset($_POST['my_nonce']) || !wp_verify_nonce($_POST['my_nonce'], 'my_action')) {
    wp_die('Security check failed');
}

if (!current_user_can('edit_posts')) {
    wp_die('Insufficient permissions');
}

$allowed_callbacks = array('sanitize_text_field', 'esc_url_raw');
$callback = isset($_POST['callback']) ? $_POST['callback'] : '';

if (in_array($callback, $allowed_callbacks, true)) {
    $value = call_user_func($callback, $_POST['field_name']);
    update_post_meta($post_id, 'key', $value);
}
```

**For URL Parameters:**
```php
// ❌ BAD
if (isset($_GET['status'])) {
    echo $_GET['status']; // XSS vulnerability
}

// ✅ GOOD
if (!empty($_GET['status']) && sanitize_key($_GET['status']) === 'success') {
    echo esc_html__('Operation successful', 'textdomain');
}
```

**For Gutenberg Block Attributes:**
```php
// ❌ BAD
function render_block($attributes) {
    $url = $attributes['url'];
    $style = $attributes['style'];
    return '<a href="' . $url . '" class="' . $style . '">Link</a>';
}

// ✅ GOOD
function render_block($attributes) {
    // Type validation
    $url = isset($attributes['url']) && is_string($attributes['url'])
        ? $attributes['url']
        : '';

    // Format validation
    if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
        $url = '';
    }

    // Whitelist validation
    $style = isset($attributes['style']) && is_string($attributes['style'])
        ? $attributes['style']
        : 'default';
    $valid_styles = array('default', 'primary', 'secondary');
    if (!in_array($style, $valid_styles, true)) {
        $style = 'default';
    }

    // Context-appropriate escaping
    return sprintf(
        '<a href="%s" class="%s">%s</a>',
        esc_url($url),
        esc_attr($style),
        esc_html__('Link', 'textdomain')
    );
}
```

**For Customizer Settings:**
```php
// ❌ BAD
$wp_customize->add_setting('theme_layout', array(
    'sanitize_callback' => 'sanitize_text_field', // Too permissive
));

// ✅ GOOD
$wp_customize->add_setting('theme_layout', array(
    'sanitize_callback' => 'prefix_sanitize_layout',
));

function prefix_sanitize_layout($input) {
    $valid = array('default', 'wide', 'boxed');
    return in_array($input, $valid, true) ? $input : 'default';
}
```

#### ✅ Output Escaping Checklist

**HTML Content:**
```php
// ❌ BAD
<h1><?php echo $title; ?></h1>

// ✅ GOOD
<h1><?php echo esc_html($title); ?></h1>
```

**HTML Attributes:**
```php
// ❌ BAD
<div class="<?php echo $class; ?>">

// ✅ GOOD
<div class="<?php echo esc_attr($class); ?>">
```

**URLs:**
```php
// ❌ BAD
<a href="<?php echo $url; ?>">

// ✅ GOOD
<a href="<?php echo esc_url($url); ?>">
```

**JavaScript:**
```php
// ❌ BAD - Inline script with PHP variable
<script>
var message = '<?php echo $message; ?>';
</script>

// ✅ GOOD - wp_localize_script
wp_enqueue_script('my-script', ...);
wp_localize_script('my-script', 'myData', array(
    'message' => $message, // Automatically escaped
    'nonce' => wp_create_nonce('my_action'),
));
```

**NEVER use inline event handlers:**
```php
// ❌ BAD
<button onclick="doSomething('<?php echo $value; ?>')">

// ✅ GOOD
<button id="my-button">Click</button>
<script>
document.getElementById('my-button').addEventListener('click', function() {
    doSomething(myData.value); // From wp_localize_script
});
</script>
```

#### ✅ WordPress API Usage Checklist

**Theme vs Plugin Constraints:**
```php
// ❌ BAD - Never use in themes
register_activation_hook(__FILE__, 'my_activation_function');

// ✅ GOOD - Use theme-appropriate hooks
function my_theme_activation() {
    if (get_option('my_theme_activated')) {
        return;
    }

    // One-time setup
    flush_rewrite_rules();
    update_option('my_theme_activated', true);
}
add_action('after_setup_theme', 'my_theme_activation', 20);

// Clean up on theme switch
function my_theme_deactivation() {
    delete_option('my_theme_activated');
}
add_action('switch_theme', 'my_theme_deactivation');
```

**Security Headers:**
```php
// ❌ BAD - Wrong hook (fires too late)
function add_security_headers() {
    header('X-Content-Type-Options: nosniff');
}
add_action('send_headers', 'add_security_headers');

// ✅ GOOD - Correct filter
function add_security_headers($headers) {
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['X-Frame-Options'] = 'SAMEORIGIN';
    $headers['X-XSS-Protection'] = '1; mode=block';
    $headers['Referrer-Policy'] = 'strict-origin-when-cross-origin';
    return $headers;
}
add_filter('wp_headers', 'add_security_headers');
```

**Time Handling:**
```php
// ❌ BAD - strtotime() unreliable for time-only
$event_time = '14:30';
$timestamp = strtotime($event_time); // May fail or use wrong date

// ✅ GOOD - DateTime::createFromFormat()
$event_time = '14:30';
if (preg_match('/^\d{2}:\d{2}$/', $event_time)) {
    $time_obj = DateTime::createFromFormat('H:i', $event_time);
    if ($time_obj) {
        echo $time_obj->format(get_option('time_format'));
    }
}
```

**Date Handling:**
```php
// ❌ BAD - No timezone awareness
$event_date = '2024-12-25';
$timestamp = strtotime($event_date); // Uses server timezone

// ✅ GOOD - WordPress timezone aware
$event_date = '2024-12-25';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
    $timestamp = strtotime($event_date . ' midnight', current_time('timestamp'));
    echo date_i18n(get_option('date_format'), $timestamp);
}
```

#### ✅ Performance Optimization Checklist

**Database Queries:**
```php
// ❌ BAD - N+1 query problem
function display_event_meta() {
    $event_date = get_post_meta(get_the_ID(), '_event_date', true);
    $event_time = get_post_meta(get_the_ID(), '_event_time', true);
    $event_location = get_post_meta(get_the_ID(), '_event_location', true);
    // ... 5 more queries ...
}

// ✅ GOOD - Single query
function display_event_meta() {
    $post_id = get_the_ID();
    $all_meta = get_post_meta($post_id); // Single query

    $event_date = isset($all_meta['_event_date'][0]) ? $all_meta['_event_date'][0] : '';
    $event_time = isset($all_meta['_event_time'][0]) ? $all_meta['_event_time'][0] : '';
    $event_location = isset($all_meta['_event_location'][0]) ? $all_meta['_event_location'][0] : '';
}
```

**Caching Repeated Calls:**
```php
// ❌ BAD - Repeated database queries
function get_social_links() {
    echo '<a href="' . get_theme_mod('facebook_url') . '">FB</a>';
    echo '<a href="' . get_theme_mod('twitter_url') . '">TW</a>';
    echo '<a href="' . get_theme_mod('instagram_url') . '">IG</a>';
    // Each get_theme_mod() is a separate database query
}

// ✅ GOOD - Static caching
function get_social_urls() {
    static $social_urls = null;

    if ($social_urls === null) {
        $social_urls = array(
            'facebook' => get_theme_mod('facebook_url', ''),
            'twitter' => get_theme_mod('twitter_url', ''),
            'instagram' => get_theme_mod('instagram_url', ''),
        );
    }

    return $social_urls;
}
```

#### ✅ Error Handling Checklist

**Operation Failure Logging:**
```php
// ❌ BAD - Silent failure
$post_id = wp_insert_post($post_data);
if ($post_id) {
    $posts[] = $post_id;
}

// ✅ GOOD - Comprehensive error logging
$post_id = wp_insert_post($post_data);

if (is_wp_error($post_id)) {
    error_log('Theme: Post creation failed - ' . $post_id->get_error_message());
    continue;
}

if (!$post_id) {
    error_log('Theme: Post creation returned false - ' . $post_data['post_title']);
    continue;
}

$posts[] = $post_id;
```

**AJAX Error Responses:**
```php
// ❌ BAD - No error details
function ajax_handler() {
    // ... do work ...
    die();
}

// ✅ GOOD - Proper success/error responses
function ajax_handler() {
    check_ajax_referer('my_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array(
            'message' => __('Insufficient permissions', 'textdomain')
        ));
    }

    $result = do_operation();

    if (is_wp_error($result)) {
        wp_send_json_error(array(
            'message' => $result->get_error_message()
        ));
    }

    wp_send_json_success(array(
        'message' => __('Operation successful', 'textdomain'),
        'data' => $result
    ));
}
```

#### ✅ Code Quality Checklist

**Named vs Anonymous Functions:**
```php
// ❌ BAD - Anonymous function (can't be removed for debugging)
add_filter('some_filter', function($value) {
    return $value . ' modified';
});

// ✅ GOOD - Named function
function my_filter_callback($value) {
    return $value . ' modified';
}
add_filter('some_filter', 'my_filter_callback');
// Can be removed: remove_filter('some_filter', 'my_filter_callback');
```

**Strict Comparisons:**
```php
// ❌ BAD - Type coercion can cause bugs
if (in_array($needle, $haystack)) { ... }

// ✅ GOOD - Type-safe comparison
if (in_array($needle, $haystack, true)) { ... }
```

---

### Phase 3: Pre-Deployment - Security Audit

Before deploying or pushing code, run through this audit:

#### Security Audit Checklist

- [ ] **All user input validated** - Search for `$_GET`, `$_POST`, `$_REQUEST`, `$_SERVER`
- [ ] **All output escaped** - Search for `echo`, `print`, `<?=`
- [ ] **Nonces verified** - All forms and AJAX have nonce checks
- [ ] **Capability checks** - All admin functions check `current_user_can()`
- [ ] **No inline JavaScript** - All scripts enqueued, data via `wp_localize_script()`
- [ ] **SQL prepared** - All custom queries use `$wpdb->prepare()`
- [ ] **File uploads validated** - MIME type, extension, and size checks
- [ ] **Error logging added** - All failures logged with `error_log()`
- [ ] **No WordPress API misuse** - Check activation hooks, header hooks, etc.
- [ ] **Performance optimized** - No N+1 queries, caching implemented

#### Automated Security Scanning

Add these tools to your development workflow:

1. **PHP Code Sniffer with WordPress Standards:**
```bash
composer require --dev squizlabs/php_codesniffer
composer require --dev wp-coding-standards/wpcs
phpcs --standard=WordPress /path/to/theme
```

2. **Security-specific checks:**
```bash
# Check for common XSS patterns
grep -r "echo \$_" /path/to/theme
grep -r "<?= \$" /path/to/theme

# Check for SQL injection risks
grep -r "\$wpdb->" /path/to/theme | grep -v "prepare"

# Check for missing nonces
grep -r "wp_ajax_" /path/to/theme
# Verify each AJAX handler has check_ajax_referer()

# Check for capability bypasses
grep -r "update_option\|add_option\|delete_option" /path/to/theme
# Verify each has current_user_can() check
```

---

## Recommended Project Brief Template

Use this template for all future WordPress projects:

```markdown
# Project: [Name]

## Security & Quality Standards

### Required Security Practices
1. **Input Validation:** All user input must use whitelist validation where possible
2. **Output Escaping:** All dynamic output must use context-appropriate escaping
3. **CSRF Protection:** All state-changing operations must verify nonces
4. **Capability Checks:** All admin functions must check user capabilities
5. **Error Logging:** All operation failures must be logged

### Code Quality Standards
1. Use named functions for all WordPress hooks (no anonymous functions)
2. Add strict comparison (true) to all in_array() calls
3. Minimize database queries (avoid N+1 patterns)
4. Use static caching for repeated expensive operations
5. Document security decisions with inline comments

### Performance Requirements
1. No more than X database queries per page load
2. All theme_mod/option calls must be cached
3. All post meta fetched with single query when possible

### Testing Requirements
1. Run PHP Code Sniffer with WordPress standards before commit
2. Test all forms with XSS payloads (<script>alert('XSS')</script>)
3. Test all AJAX endpoints without nonces
4. Test all URLs with unexpected parameters
5. Enable WP_DEBUG and verify no errors/warnings

## Implementation Requirements
[Your specific feature requirements here]

## Acceptance Criteria
- [ ] All security practices implemented
- [ ] All code quality standards met
- [ ] All performance requirements met
- [ ] All tests passing
- [ ] Security audit checklist completed
```

---

## Key Takeaways

### For You (Project Owner)
1. **Be Explicit:** Always include security requirements in your initial brief
2. **Request Checklists:** Ask for a security checklist review before considering work complete
3. **Automated Scanning:** Request PHPCS with WordPress standards be run
4. **Ask "Why":** When reviewing code, ask "Why is this safe?" for any user input handling

### For Developers (AI or Human)
1. **Security First:** Think about security before writing the first line of code
2. **Validate Everything:** Never trust ANY input (GET, POST, attributes, etc.)
3. **Escape Everything:** Every dynamic value in output needs escaping
4. **Know WordPress:** Research the correct hooks/functions for your context
5. **Log Failures:** Every operation that can fail should log when it does

### Process Improvements
1. **Initial:** Include security requirements in project brief
2. **Development:** Use security checklist for each feature
3. **Pre-commit:** Run automated security scans
4. **Pre-deployment:** Complete full security audit
5. **Post-deployment:** Monitor error logs for failures

---

## Quick Reference: Security by Context

| Context | Validation | Escaping | Example |
|---------|-----------|----------|---------|
| Form Input | Nonce + Capability + Whitelist | N/A | `check_ajax_referer()` + `current_user_can()` + whitelist |
| URL Parameter | `sanitize_key()` | `esc_html()` | `sanitize_key($_GET['status'])` |
| Block Attribute | Type check + Whitelist/Regex | Context-specific | `is_string()` + `in_array(..., true)` |
| Customizer | Whitelist function | N/A | Custom `sanitize_callback` |
| HTML Output | N/A | `esc_html()` | `echo esc_html($text)` |
| Attribute Output | N/A | `esc_attr()` | `class="<?php echo esc_attr($class); ?>"` |
| URL Output | `filter_var()` | `esc_url()` | `href="<?php echo esc_url($url); ?>"` |
| JS Output | N/A | `wp_localize_script()` | Never inline PHP variables |
| SQL Query | `$wpdb->prepare()` | N/A | `$wpdb->prepare("SELECT * WHERE id = %d", $id)` |

---

## Additional Resources

### WordPress Security Documentation
- [WordPress Data Validation](https://developer.wordpress.org/plugins/security/data-validation/)
- [WordPress Escaping](https://developer.wordpress.org/plugins/security/securing-output/)
- [WordPress Nonces](https://developer.wordpress.org/plugins/security/nonces/)

### Security Tools
- [Theme Check Plugin](https://wordpress.org/plugins/theme-check/)
- [Plugin Check Plugin](https://wordpress.org/plugins/plugin-check/)
- [WPScan](https://wpscan.com/)

### Coding Standards
- [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)

---

**Remember:** Security is not a feature you add at the end - it's a practice you follow from the first line of code.
