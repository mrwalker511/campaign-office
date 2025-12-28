# Developer Reference

**Complete technical reference for CampaignPress development**

Version: 2.0.0 | Last Updated: December 28, 2025

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Architecture Overview](#architecture-overview)
3. [Technology Stack](#technology-stack)
4. [Development Workflow](#development-workflow)
5. [Block Development](#block-development)
6. [Premium Features](#premium-features)
7. [Database Schema](#database-schema)
8. [Security Guidelines](#security-guidelines)
9. [API Reference](#api-reference)

---

## Quick Start

### Setup

```bash
# Clone and install
git clone https://github.com/mrwalker511/campaign-office.git
cd campaign-office
npm install

# Start development
npm run dev          # Vite dev server with HMR
npm run build        # Production build
npm run watch        # Auto-rebuild
npm run lint         # Check code quality
```

### Requirements

- **WordPress:** 6.9+
- **PHP:** 8.1+ (minimum 7.4)
- **MySQL:** 8.0+ (minimum 5.7)
- **Node.js:** 18+
- **npm:** 9+

---

## Architecture Overview

### Layered Architecture

```
WordPress Root (templates, header, footer)
    ↓
Design System Layer (theme.json + CSS)
    ↓
Free Module Layer (18 modules - always loaded)
    ↓
Premium Feature Layer (9 modules - license-gated)
    ↓
Integration Layer (Elementor, WPML, third-party)
```

### Core Philosophy

**1. Modular Design**
- Self-contained modules
- Independent enable/disable
- Clear free/premium boundary

**2. Hook-Based Extensibility**
- WordPress actions/filters throughout
- Custom hooks for integrations
- Event-driven architecture

**3. Singleton Pattern**
- Major components (Premium, CRM, Field Ops)
- Prevents duplicate initialization

**4. MVC Separation**
- **Models:** Database classes
- **Views:** Templates, blocks, widgets
- **Controllers:** Init classes, AJAX, REST endpoints

### File Structure

```
campaign-office/
├── functions.php              # Theme entry point
├── theme.json                 # Design system tokens
├── includes/
│   ├── free/                  # 18 free modules
│   │   ├── custom-post-types.php
│   │   ├── gutenberg-blocks.php
│   │   ├── elementor-widgets.php
│   │   ├── volunteer-management.php
│   │   └── ... (14 more)
│   └── premium/               # 9 premium modules
│       ├── premium-init.php   # License system
│       ├── crm/               # CRM (5 files)
│       ├── field-operations/  # Field ops (5 files)
│       ├── compliance/        # FEC compliance (5 files)
│       ├── analytics/         # Analytics (3 files)
│       ├── api/               # REST API (3 files)
│       ├── integrations/      # Email/SMS (3 files)
│       └── developer-console/ # Dev tools (7 files)
├── assets/
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript
│   ├── react/                 # React components
│   └── dist/                  # Compiled assets
├── blocks/                    # Block templates
├── templates/                 # Page templates
└── tests/                     # Test files
```

### Initialization Sequence

1. `functions.php` loads theme constants & support
2. Bootstrap 5.3 + theme CSS/JS enqueued
3. All 18 free modules loaded via `require_once`
4. Premium system loads (if exists)
5. Premium validates license
6. Enabled premium modules loaded on `init` hook

---

## Technology Stack

### Frontend

**HTML/CSS:**
- HTML5 with semantic markup
- Bootstrap 5.3 responsive grid
- WordPress 6.9 design tokens
- CSS custom properties
- Flexbox & Grid layouts

**JavaScript:**
- ES6+ (arrow functions, async/await, modules)
- jQuery 3.7 (WordPress included)
- React 18 for complex components
- Chart.js for analytics
- Vanilla JS for theme scripts

**Build Tools:**
- **Vite 5.0** - Fast builds, HMR
- **npm** - Package management
- **ESLint** - Code quality
- **Prettier** - Code formatting

### Backend

**PHP 8.1+:**
- Object-oriented (classes, interfaces, traits)
- WordPress hooks system
- Database abstraction ($wpdb)
- Type declarations
- Security functions (sanitize, escape, prepare)

**Database:**
- MySQL 8.0+ / MariaDB 10.5+
- InnoDB storage engine
- 23 total tables (12 WordPress + 11 CRM custom)
- Optimized indexes
- Foreign key constraints

**WordPress Features:**
- Custom Post Types & Taxonomies
- Block Editor (Gutenberg)
- REST API
- Customizer API
- Widget System
- Template Hierarchy
- i18n/l10n

### Third-Party Integrations

**Payment Processors:**
ActBlue, WinRed, PayPal, Stripe, Square, Donorbox

**Email/SMS:**
Mailchimp, Twilio, SendGrid, Constant Contact

**WordPress Plugins:**
Contact Form 7, The Events Calendar, WPML, Polylang, Elementor

---

## Development Workflow

### Creating a Feature Branch

```bash
git checkout main
git pull origin main
git checkout -b feature/feature-name
```

### Development Cycle

1. **Read documentation** - CLAUDE.md, this file
2. **Review existing code** for patterns
3. **Write code** following standards
4. **Test locally** - functionality, responsive, accessibility
5. **Lint & format** - `npm run lint`, `npm run format`
6. **Build** - `npm run build`
7. **Commit** - Clear, descriptive messages
8. **Push** - `git push origin feature/feature-name`
9. **Pull Request** - Request review on GitHub

### Commit Message Format

```
type(scope): Brief description

Optional detailed explanation

Closes #issue-number
```

**Types:** feat, fix, docs, style, refactor, perf, test, chore

**Example:**
```
feat(crm): Add engagement scoring algorithm

Implemented RFM-based scoring:
- Recency of interactions
- Frequency of engagement
- Quality of responses

Closes #123
```

### Code Standards

**PHP (WordPress Standards):**
```php
// Indentation: Tabs
// Braces: Allman style
// Naming: snake_case

function campaignpress_my_function( $param1, $param2 )
{
    if ( $param1 === $param2 )
    {
        return true;
    }
    return false;
}
```

**JavaScript (ESLint):**
```javascript
// Indentation: 2 spaces
// Quotes: Single
// Semicolons: Required
// Naming: camelCase

const myFunction = (param1, param2) => {
  if (param1 === param2) {
    return true;
  }
  return false;
};
```

**CSS (BEM):**
```css
/* Block */
.cp-block-name { }

/* Element */
.cp-block-name__element { }

/* Modifier */
.cp-block-name--modifier { }

/* Always use WordPress design tokens */
.component {
    color: var(--wp--preset--color--primary);
    font-family: var(--wp--preset--font-family--display);
    padding: var(--wp--preset--spacing--8);
}
```

---

## Block Development

### Creating a Gutenberg Block

**Step 1: Register in PHP**

`includes/free/gutenberg-blocks.php`:
```php
register_block_type('campaignpress/my-block', array(
    'editor_script' => 'campaignpress-blocks-js',
    'editor_style' => 'campaignpress-blocks-css',
    'render_callback' => 'campaignpress_render_my_block',
    'attributes' => array(
        'title' => array('type' => 'string', 'default' => ''),
        'count' => array('type' => 'number', 'default' => 5)
    )
));

function campaignpress_render_my_block($attributes) {
    $title = esc_html($attributes['title']);
    $count = absint($attributes['count']);

    ob_start();
    ?>
    <div class="cp-my-block">
        <h3><?php echo $title; ?></h3>
        <p>Count: <?php echo $count; ?></p>
    </div>
    <?php
    return ob_get_clean();
}
```

**Step 2: Create React Component**

`assets/react/blocks/MyBlock.jsx`:
```jsx
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, RangeControl } from '@wordpress/components';

registerBlockType('campaignpress/my-block', {
    title: 'My Block',
    icon: 'star-filled',
    category: 'campaignpress',

    edit: ({ attributes, setAttributes }) => {
        const { title, count } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Settings">
                        <TextControl
                            label="Title"
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                        />
                        <RangeControl
                            label="Count"
                            value={count}
                            onChange={(value) => setAttributes({ count: value })}
                            min={1}
                            max={20}
                        />
                    </PanelBody>
                </InspectorControls>

                <div className="cp-my-block">
                    <h3>{title || 'Enter title...'}</h3>
                    <p>Count: {count}</p>
                </div>
            </>
        );
    },

    save: () => null  // Server-side rendering
});
```

**Step 3: Build**
```bash
npm run build
```

---

## Premium Features

### Adding a Premium Module

**Step 1: Define Feature**

`includes/premium/premium-init.php`:
```php
private $features = array(
    'my_feature' => array(
        'name' => 'My Feature',
        'description' => 'Feature description',
        'tier' => 'professional',  // basic, professional, enterprise
        'file' => 'my-feature/my-feature-init.php',
        'enabled' => true
    )
);
```

**Step 2: Create Module**

`includes/premium/my-feature/my-feature-init.php`:
```php
class CampaignPress_My_Feature {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function init() {
        // Feature initialization
    }

    public function admin_menu() {
        add_submenu_page(
            'campaignpress-pro',
            'My Feature',
            'My Feature',
            'manage_options',
            'campaignpress-my-feature',
            array($this, 'admin_page')
        );
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>My Feature</h1>
            <!-- Admin interface -->
        </div>
        <?php
    }
}

CampaignPress_My_Feature::get_instance();
```

---

## Database Schema

### Custom CRM Tables

**Main Contact Table:**
```sql
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
    INDEX idx_email (email),
    INDEX idx_engagement (engagement_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Interaction History:**
```sql
CREATE TABLE wp_cp_interactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contact_id BIGINT UNSIGNED,
    interaction_type VARCHAR(20),
    interaction_date DATETIME,
    notes TEXT,
    FOREIGN KEY (contact_id) REFERENCES wp_cp_contacts(id) ON DELETE CASCADE,
    INDEX idx_contact (contact_id),
    INDEX idx_date (interaction_date)
) ENGINE=InnoDB;
```

**Complete Schema:** 11 custom tables (contacts, interactions, tags, segments, households, custom fields)

### Database Operations

**Always use prepared statements:**
```php
global $wpdb;

// SELECT
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}cp_contacts WHERE email = %s",
    $email
));

// INSERT
$wpdb->insert(
    $wpdb->prefix . 'cp_contacts',
    array('email' => $email, 'first_name' => $name),
    array('%s', '%s')
);

// UPDATE
$wpdb->update(
    $wpdb->prefix . 'cp_contacts',
    array('engagement_score' => $score),
    array('id' => $id),
    array('%d'),
    array('%d')
);
```

---

## Security Guidelines

### Input Sanitization

```php
// Text
$clean = sanitize_text_field($_POST['field']);

// Email
$email = sanitize_email($_POST['email']);

// URL
$url = esc_url($_POST['url']);

// HTML
$html = wp_kses_post($_POST['content']);

// Number
$num = absint($_POST['number']);

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

### Nonce Verification

```php
// Generate
wp_nonce_field('action_name', 'nonce_field');

// Verify
if (!wp_verify_nonce($_POST['nonce_field'], 'action_name')) {
    wp_die('Security check failed');
}
```

### Capability Checks

```php
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions');
}
```

---

## API Reference (Enterprise)

### REST API Endpoints

**Base:** `/wp-json/campaignpress/v1/`

**Authentication:**
```
X-API-Key: your-api-key
```

**Contacts:**
```
GET    /contacts          List contacts
POST   /contacts          Create contact
GET    /contacts/{id}     Get contact
PUT    /contacts/{id}     Update contact
DELETE /contacts/{id}     Delete contact
```

**Interactions:**
```
POST   /interactions      Log interaction
GET    /interactions      List interactions
```

**Rate Limiting:** 1000 req/hour per API key

**Response Format:**
```json
{
  "success": true,
  "data": {...},
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20
  }
}
```

---

## Testing Checklist

### Before Committing

- [ ] Code follows WordPress standards
- [ ] All inputs sanitized
- [ ] All outputs escaped
- [ ] SQL uses prepared statements
- [ ] Nonces verified
- [ ] Capabilities checked
- [ ] No debug code (console.log, var_dump)
- [ ] Documentation updated
- [ ] CHANGELOG.md updated

### Accessibility

- [ ] Keyboard navigation
- [ ] ARIA labels
- [ ] Color contrast (WCAG 2.1 AA)
- [ ] Screen reader tested
- [ ] Reduced motion respected

### Browser Support

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile browsers

---

## Common Patterns

### WordPress Hooks

```php
// Action
add_action('init', 'my_function');

// Filter
add_filter('the_content', 'my_filter_function');

// Custom action
do_action('campaignpress_custom_action', $param);

// Custom filter
$value = apply_filters('campaignpress_custom_filter', $value);
```

### Enqueuing Assets

```php
wp_enqueue_style('my-style',
    get_template_directory_uri() . '/assets/css/style.css',
    array(),
    CAMPAIGNPRESS_VERSION
);

wp_enqueue_script('my-script',
    get_template_directory_uri() . '/assets/js/script.js',
    array('jquery'),
    CAMPAIGNPRESS_VERSION,
    true
);
```

### AJAX Handler

```php
// PHP
add_action('wp_ajax_my_action', 'my_ajax_handler');

function my_ajax_handler() {
    check_ajax_referer('my_nonce', 'nonce');

    $result = do_something();

    wp_send_json_success($result);
}

// JavaScript
jQuery.post(ajaxurl, {
    action: 'my_action',
    nonce: myData.nonce,
    data: 'value'
}, function(response) {
    if (response.success) {
        console.log(response.data);
    }
});
```

---

## Troubleshooting

### Build Issues

```bash
# Clear and reinstall
rm -rf node_modules package-lock.json
npm install

# Clear cache
npm cache clean --force
```

### WordPress Debug

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Check logs
tail -f wp-content/debug.log
```

### Database Issues

```sql
-- Check table status
SHOW TABLE STATUS LIKE 'wp_cp_%';

-- Repair table
REPAIR TABLE wp_cp_contacts;
```

---

## Resources

**Documentation:**
- [WordPress Developer Handbook](https://developer.wordpress.org/)
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)

**CampaignPress Files:**
- `docs/CLAUDE.md` - Architecture overview
- `docs/DESIGN-REFERENCE.md` - Design system guide
- `docs/PRODUCTION-REFERENCE.md` - Deployment guide
- `DEVELOPER-GUIDE.md` - Developer documentation
- `TESTING.md` - Testing guide

---

**Last Updated:** December 28, 2025
**Version:** 2.0.0
