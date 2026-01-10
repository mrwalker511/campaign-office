# CampaignPress System Architecture

**Version:** 2.0.0 | **Last Updated:** December 28, 2025

---

## Table of Contents

1. [High-Level Architecture](#high-level-architecture)
2. [Module System](#module-system)
3. [Database Architecture](#database-architecture)
4. [Design System Architecture](#design-system-architecture)
5. [Security Architecture](#security-architecture)
6. [Performance Architecture](#performance-architecture)

---

## High-Level Architecture

### Layered Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    WordPress Root                        │
│            (templates, header, footer, etc.)            │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                  Design System Layer                     │
│         (theme.json + design-system-wp69.css)           │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                   Free Module Layer                      │
│     (18 modules always loaded - CPTs, blocks, etc.)     │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                Premium Feature Layer                     │
│   (9 optional modules - CRM, field ops, compliance)     │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                  Integration Layer                       │
│      (Elementor, WPML, Mailchimp, third-party)          │
└─────────────────────────────────────────────────────────┘
```

### Core Philosophy

**1. Modular Design**
- Each feature is a self-contained module
- Modules can be enabled/disabled independently
- Clear boundaries between free and premium

**2. Hook-Based Extensibility**
- Uses WordPress action/filter hooks throughout
- Custom hooks for third-party integrations
- Event-driven architecture

**3. Singleton Pattern for Core Systems**
- Ensures single instance of major components
- Used for license system, CRM, field operations
- Prevents duplicate initialization

**4. MVC-Like Separation**
- Models: Database classes (CRM_Database, CRM_Contacts)
- Views: Templates, block renders, widget outputs
- Controllers: Init classes, AJAX handlers, REST endpoints

---

## Module System

### Initialization Flow

```
functions.php loads
      ↓
Theme setup (after_setup_theme hook)
      ↓
Enqueue assets (wp_enqueue_scripts hook)
      ↓
Load 18 free modules (require_once)
      ↓
Load premium system (if exists)
      ↓
Premium validates license
      ↓
Load enabled premium modules (init hook, priority 1)
      ↓
Register post types, taxonomies, blocks
      ↓
WordPress fully initialized
```

### Free Modules (18 modules)

**Location:** `includes/free/`

All modules are loaded directly in `functions.php`:

```php
// Core modules
require_once CAMPAIGNPRESS_THEME_DIR . '/includes/free/custom-post-types.php';
require_once CAMPAIGNPRESS_THEME_DIR . '/includes/free/gutenberg-blocks.php';
require_once CAMPAIGNPRESS_THEME_DIR . '/includes/free/elementor-widgets.php';
// ... 15 more modules
```

**Module Types:**
1. **Core Features** - CPTs, blocks, widgets
2. **Management** - Volunteers, events, donations
3. **Support** - Accessibility, translation, integrations
4. **Admin** - Customizer, admin pages, notices

### Premium Modules (9 modules)

**Location:** `includes/premium/`

**Entry Point:** `premium-init.php`

**Loading Strategy:** Dynamic based on license

```php
class CampaignPress_Premium {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'load_premium_modules'], 1);
    }

    public function load_premium_modules() {
        if ($this->is_license_valid()) {
            $tier = $this->get_license_tier();
            $this->load_modules_for_tier($tier);
        }
    }
}
```

**Module Dependencies:**

| Module | Requires | Files |
|--------|----------|-------|
| crm | Professional | 5 |
| field-operations | Professional | 5 |
| compliance | Enterprise | 5 |
| analytics | Professional | 3 |
| api | Enterprise | 3 |
| integrations | Professional | 3 |
| developer-console | Basic | 7 |
| admin-pages | All | 4 |

---

## Database Architecture

### WordPress Standard Tables

**Used For:**
- Posts (`wp_posts`) - Issues, Events, Endorsements, Team
- Post Meta (`wp_postmeta`) - Custom fields for CPTs
- Users (`wp_users`) - WordPress admin users
- Options (`wp_options`) - Settings, license keys
- Terms (`wp_terms`) - Taxonomies (categories, tags)

### Custom CRM Tables (11 tables)

**Prefix:** `wp_cp_`

**Schema:**

```sql
-- Main contact database
CREATE TABLE wp_cp_contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    phone VARCHAR(20),
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    voter_id VARCHAR(50),
    engagement_score INT,
    created_at DATETIME,
    updated_at DATETIME,
    -- 50+ more fields
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_voter_id (voter_id),
    INDEX idx_engagement (engagement_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Interaction history
CREATE TABLE wp_cp_interactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED,
    interaction_type VARCHAR(20),
    interaction_date DATETIME,
    user_id BIGINT UNSIGNED,
    notes TEXT,
    FOREIGN KEY (contact_id) REFERENCES wp_cp_contacts(id) ON DELETE CASCADE,
    INDEX idx_contact (contact_id),
    INDEX idx_type (interaction_type),
    INDEX idx_date (interaction_date)
) ENGINE=InnoDB;

-- Tag system
CREATE TABLE wp_cp_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(100),
    tag_slug VARCHAR(100),
    tag_type VARCHAR(20)
) ENGINE=InnoDB;

-- Contact-tag relationship
CREATE TABLE wp_cp_contact_tags (
    contact_id BIGINT UNSIGNED,
    tag_id BIGINT UNSIGNED,
    PRIMARY KEY (contact_id, tag_id),
    INDEX idx_contact (contact_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB;

-- Additional tables: segments, households, custom fields, etc.
```

**Performance Optimizations:**
- Indexes on frequently queried columns
- InnoDB engine for ACID compliance
- utf8mb4 for emoji and international characters
- Foreign keys for referential integrity
- Composite primary keys for junction tables

---

## Design System Architecture

### WordPress 6.9 Design Token System

**Central Configuration:** `theme.json`

```json
{
  "version": 2,
  "settings": {
    "color": {
      "palette": [
        { "name": "Primary 500", "slug": "primary", "color": "#0053c3" },
        { "name": "Primary 50", "slug": "primary-50", "color": "#e6f0ff" }
        // ... 31 more colors
      ]
    },
    "typography": {
      "fontFamilies": [
        { "name": "Display", "slug": "display", "fontFamily": "Bricolage Grotesque" },
        { "name": "Body", "slug": "body", "fontFamily": "Plus Jakarta Sans" },
        { "name": "Mono", "slug": "mono", "fontFamily": "JetBrains Mono" }
      ],
      "fontSizes": [
        { "name": "XS", "slug": "xs", "size": "clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem)" }
        // ... 7 more sizes
      ]
    },
    "spacing": {
      "spacingSizes": [
        { "name": "1", "slug": "1", "size": "4px" }
        // ... 11 more sizes
      ]
    }
  }
}
```

**CSS Variable Generation:**

WordPress automatically generates:
```css
:root {
  --wp--preset--color--primary: #0053c3;
  --wp--preset--color--primary-50: #e6f0ff;
  --wp--preset--font-family--display: "Bricolage Grotesque";
  --wp--preset--font-size--xs: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);
  --wp--preset--spacing--1: 4px;
}
```

**Design System Files:**
- `theme.json` - Token definitions
- `assets/css/design-system-wp69.css` - Enhanced CSS using tokens
- Block editor integration - Visual controls

---

## Security Architecture

### Input Validation

**Sanitization Functions:**
```php
// Text fields
$clean = sanitize_text_field($_POST['field']);

// Email
$email = sanitize_email($_POST['email']);

// URL
$url = esc_url($_POST['url']);

// Array
$array = array_map('sanitize_text_field', $_POST['array']);
```

### Output Escaping

```php
// HTML
echo esc_html($variable);

// Attributes
echo '<div class="' . esc_attr($class) . '">';

// URLs
echo '<a href="' . esc_url($url) . '">';

// JavaScript
echo '<script>var data = ' . wp_json_encode($data) . ';</script>';
```

### Database Security

```php
global $wpdb;

// ALWAYS use prepared statements
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM wp_cp_contacts WHERE email = %s",
    $email
));

// NEVER use direct concatenation
// BAD: "SELECT * FROM table WHERE id = " . $_GET['id']
```

### Nonce Verification

```php
// Generate nonce
wp_nonce_field('action_name', 'nonce_field_name');

// Verify nonce
if (!isset($_POST['nonce_field_name']) ||
    !wp_verify_nonce($_POST['nonce_field_name'], 'action_name')) {
    wp_die('Security check failed');
}
```

### Capability Checks

```php
// Check user permissions
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}
```

### Security Headers

```php
// In functions.php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

---

## Performance Architecture

### Asset Loading Strategy

**1. Conditional Loading**
```php
// Only load CRM assets on CRM pages
if (is_page('crm')) {
    wp_enqueue_script('campaignpress-crm');
}
```

**2. Minification & Bundling**
- Vite builds minified assets
- Separate bundles for blocks, CRM, main theme
- Tree-shaking removes unused code

**3. CDN for Bootstrap**
```php
wp_enqueue_style('bootstrap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
);
```

### Database Optimization

**1. Query Optimization**
```php
// Use indexes
SELECT * FROM wp_cp_contacts
WHERE email = 'test@example.com'  -- indexed column
LIMIT 1;

// Avoid SELECT *
SELECT id, first_name, last_name FROM wp_cp_contacts;

// Use prepared statements (cached)
$wpdb->prepare("SELECT...");
```

**2. Caching**
```php
// WordPress object cache
$contacts = wp_cache_get('all_contacts', 'campaignpress');
if (false === $contacts) {
    $contacts = get_all_contacts();
    wp_cache_set('all_contacts', $contacts, 'campaignpress', 3600);
}
```

**3. Pagination**
```php
// Limit result sets
$args = array(
    'post_type' => 'cp_event',
    'posts_per_page' => 10,
    'paged' => $paged
);
```

### Front-End Performance

**1. Lazy Loading**
```html
<img src="image.jpg" loading="lazy">
```

**2. Critical CSS**
- Inline critical CSS in `<head>`
- Defer non-critical CSS

**3. JavaScript Optimization**
- Defer non-critical scripts
- Async loading where appropriate
- Module bundling with code splitting

---

## API Architecture (Enterprise)

### REST API Structure

**Base:** `/wp-json/campaignpress/v1/`

**Endpoints:**
- `GET /contacts` - List contacts
- `POST /contacts` - Create contact
- `GET /contacts/{id}` - Get contact
- `PUT /contacts/{id}` - Update contact
- `DELETE /contacts/{id}` - Delete contact
- `POST /interactions` - Log interaction
- `GET /segments` - List segments
- `POST /segments` - Create segment

**Authentication:**
```
X-API-Key: your-api-key-here
```

**Rate Limiting:**
- 1000 requests/hour per API key
- 429 response when exceeded

**Response Format:**
```json
{
  "success": true,
  "data": { ... },
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20
  }
}
```

---

## Deployment Architecture

### Recommended Stack

**Production:**
- **Web Server:** Nginx or Apache with mod_rewrite
- **PHP:** 8.1+ with OPcache enabled
- **Database:** MySQL 8.0+ or MariaDB 10.5+
- **Caching:** Redis or Memcached for object cache
- **CDN:** CloudFlare or similar for assets

**Development:**
- **Local:** Local WP, XAMPP, or MAMP
- **Build:** Node 18+, npm 9+
- **Version Control:** Git

---

## Scalability Considerations

### Horizontal Scaling
- Stateless design (no session storage)
- External object cache (Redis/Memcached)
- Database replication for read replicas
- CDN for static assets

### Vertical Scaling
- Database indexing
- Query optimization
- Asset minification
- Caching strategies

### Limits
- **CRM:** Optimized for 50,000 contacts per install
- **API:** 1000 req/hour per key (adjustable)
- **File uploads:** WordPress defaults (can be increased)

---

## Monitoring & Observability

### Built-In Tools

**Developer Console (Basic+):**
- Database query inspector
- Error log viewer
- Performance profiling
- System health checks

**Debug Mode:**
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

## Conclusion

CampaignPress architecture prioritizes:
1. **Modularity** - Clean separation of concerns
2. **Security** - Defense in depth
3. **Performance** - Optimized for speed
4. **Scalability** - Grows with campaigns
5. **Extensibility** - Hook-based customization

---

**For More Information:**
- [Tech Stack Details](TECH_STACK.md)
- [Development Workflow](WORKFLOW.md)
- [Security Best Practices](../DEVELOPER-GUIDE.md)
