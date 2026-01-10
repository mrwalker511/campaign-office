# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Campaign Office Architecture Overview

**Version:** 2.1.0 | **Status:** Premium Political WordPress Theme + Campaign Operations Platform

## Executive Summary

CampaignPress is a transformative WordPress theme that seamlessly transitions from a free political website builder into a comprehensive campaign management platform. The architecture is designed around a **clear free/premium boundary** with modular systems for CRM, field operations, compliance, analytics, and third-party integrations.

---

## COMMONLY USED COMMANDS

### Development
```bash
# Install dependencies
npm install

# Start development server (Vite with HMR)
npm run dev

# Build for production
npm run build

# Watch mode (auto-rebuild on changes)
npm run watch

# Lint JavaScript files
npm run lint

# Format code with Prettier
npm run format
```

### Build System
- **Build Tool:** Vite 5.0 with React plugin
- **Entry Points:**
  - `assets/react/blocks/index.jsx` → Gutenberg blocks (React)
  - `assets/react/crm/index.jsx` → CRM interface (React)
  - `assets/js/main.js` → Main theme JavaScript
- **Output:** `assets/dist/` directory
  - JS: `assets/dist/js/[name].js`
  - CSS: `assets/dist/css/[name].css`

### WordPress Development
```bash
# Flush rewrite rules (after CPT changes)
# Visit: /wp-admin/options-permalink.php and click Save

# Enable debug mode
# Add to wp-config.php:
# define('WP_DEBUG', true);
# define('WP_DEBUG_LOG', true);
# define('WP_DEBUG_DISPLAY', false);

# Check debug.log
tail -f wp-content/debug.log
```

---

## DESIGN SYSTEM (WordPress 6.9+)

**Status:** Production Ready | **Version:** 2.0.0 | **WordPress Required:** 6.9+

### Design System Architecture

CampaignPress uses a **WordPress 6.9-native design system** with centralized design tokens:

- **`theme.json`** - Central configuration for all design tokens (colors, typography, spacing)
- **`assets/css/design-system-wp69.css`** - Enhanced CSS using WordPress design tokens
- **Block Editor Integration** - Full Gutenberg compatibility with visual design controls

### Design Documentation

📚 **Complete documentation available in the `docs/` directory:**
- **`DESIGN-REFERENCE.md`** - Complete design system and style guide.
- **`GETTING-STARTED.md`** - General introduction and setup.
- **`QUICKSTART.md`** - 5-minute setup guide.
- **`DEVELOPER-GUIDE.md`** - Technical reference for developers.

### Design Tokens (theme.json)

All design tokens are managed in `theme.json` and automatically available in:
- Block editor (visual controls)
- CSS (via `--wp--preset--` variables)
- PHP (via WordPress functions)

**Color Palettes (33 colors):**
- Primary shades: `--wp--preset--color--primary-50` → `--wp--preset--color--primary-900`
- Accent shades: `--wp--preset--color--accent-50` → `--wp--preset--color--accent-900`
- Neutral shades: `--wp--preset--color--neutral-50` → `--wp--preset--color--neutral-900`
- Semantic: `--wp--preset--color--success`, `warning`, `error`, `info`

**Typography (3 font families):**
- Display: `--wp--preset--font-family--display` (Bricolage Grotesque - Headlines)
- Body: `--wp--preset--font-family--body` (Plus Jakarta Sans - Paragraphs)
- Mono: `--wp--preset--font-family--mono` (JetBrains Mono - Numbers/Stats)

**Font Sizes (8 fluid sizes):**
- `--wp--preset--font-size--xs` → `--wp--preset--font-size--4-xl`
- Uses CSS clamp() for automatic mobile-to-desktop scaling

**Spacing (12 preset sizes):**
- `--wp--preset--spacing--1` (4px) → `--wp--preset--spacing--24` (96px)
- Based on 8px grid system

**Shadows (6 presets):**
- `--wp--preset--shadow--sm` → `--wp--preset--shadow--2-xl`

### Party Color Schemes

Four political party themes available (switch via Customizer):

1. **Democrat Blue** (default)
   - Primary: #0053c3, Accent: #ff8800
2. **Republican Red**
   - Primary: #e81b23, Accent: #ffd700
3. **Independent Purple**
   - Primary: #6b3fa0, Accent: #00d9ff
4. **Green Party**
   - Primary: #17aa5c, Accent: #ffeb3b

Apply via body class: `color-scheme-{party}`

### Using Design Tokens in Code

**In CSS:**
```css
.my-component {
  color: var(--wp--preset--color--primary);
  font-family: var(--wp--preset--font-family--display);
  font-size: var(--wp--preset--font-size--2-xl);
  padding: var(--wp--preset--spacing--8);
  box-shadow: var(--wp--preset--shadow--lg);
}
```

**In Block Editor:**
Users select from visual dropdowns (no coding required)

**In PHP:**
```php
// Get theme color
$primary = wp_get_global_settings()['color']['palette']['theme'][5]['color'];
```

### Design Principles

✅ **Distinctive Typography** - Avoid generic fonts (Inter, Roboto, Arial)
✅ **9-Shade Palettes** - Granular color control for hover states, backgrounds
✅ **Sophisticated Animations** - Staggered reveals, pulse effects, hover lifts
✅ **Accessibility First** - WCAG 2.1 AA compliant, reduced motion support
✅ **Performance Optimized** - WordPress handles font loading, GPU-accelerated CSS
✅ **Block Native** - Full integration with WordPress block editor

---

## 1. HIGH-LEVEL ARCHITECTURE

### 1.1 Core Philosophy

The theme follows a **layered modular architecture**:
- **WordPress Root Level** - Standard templates (header, footer, etc.)
- **Design System Layer** - theme.json + design-system-wp69.css (WordPress 6.9+)
- **Free Module Layer** - 18 modules always loaded (custom post types, blocks, widgets)
- **Premium Feature Layer** - 9 optional modules (license-gated CRM, field ops, compliance)
- **Integration Layer** - Third-party plugin support (Elementor, WPML, Mailchimp)

### 1.2 Initialization Sequence

1. **`functions.php`** loads:
   - Theme constants & supports
   - Bootstrap 5.3 + theme CSS/JS
   - Widget areas
   - All 15 free modules directly

2. **Free modules initialize** (always):
   - Custom post types
   - Gutenberg blocks & Elementor widgets
   - Volunteer & event management
   - Accessibility, translation, donations
   - And 10+ other modules

3. **Premium system loads** (if license valid):
   - Validates license key with API
   - Manages feature toggles
   - Loads enabled modules on `init` hook (priority 1)

---

## 2. FREE VS PREMIUM ORGANIZATION

### 2.1 Free Version: 18 Modules

**Location:** `includes/free/`

Core modules always loaded:

- **custom-post-types.php** - Issues, Events, Team, Volunteers, Endorsements CPTs
- **gutenberg-blocks.php** - 7 Gutenberg blocks with render callbacks
- **elementor-widgets.php** - 10 Elementor widgets for page builders
- **volunteer-management.php** - Volunteer database, CSV export, capture forms
- **event-management.php** - RSVP system, recurring events, capacity limits
- **donation-enhancements.php** - ActBlue, WinRed, PayPal, Stripe, Square, Donorbox
- **accessibility.php** - WCAG 2.1 AA compliance, skip links, ARIA labels
- **translation-support.php** - WPML, Polylang, TranslatePress, RTL support
- **customizer.php** - Color scheme, layout customization
- **template-functions.php** - Template helper functions
- **template-tags.php** - Template tag functions
- **campaign-widgets.php** - 7 dashboard widgets
- **admin-theme-options.php** - Admin UI for customization
- **admin-notices.php** - Admin dashboard notices
- **integrations.php** - CF7, Events Calendar, Mailchimp support
- **demo-content.php** - Demo content generator
- **class-bootstrap-navwalker.php** - Bootstrap 5 navigation walker
- **tgmpa-config.php** - Recommended plugins configuration

**Key Pattern:** All free features use procedural functions with WordPress hooks for extensibility.

### 2.2 Premium Version: 9 Modules

**Location:** `includes/premium/`

All modules are license-gated and loaded dynamically:

| Module | Files | License | Purpose |
|--------|-------|---------|---------|
| **crm** | 5 | Professional | Voter/contact database (50K+, engagement scoring) |
| **field-operations** | 5 | Professional | Canvassing, phone banking, GOTV, scheduling |
| **compliance** | 5 | Enterprise | FEC compliance, contribution tracking |
| **analytics** | 3 | Professional | Performance dashboards, KPI metrics |
| **api** | 3 | Enterprise | REST API, webhooks, external integrations |
| **integrations** | 3 | Professional | Email/SMS workflows (Mailchimp, Twilio) |
| **developer-console** | 7 | Basic | Database inspector, API tester, data export |
| **admin-pages** | 4 | Varies | License, features, system status, upgrade |
| **premium-init.php** | 1 | Core | License system & feature manager (1,344 lines) |

**License Tiers:**
- **Basic:** Auto-updates, Developer Console
- **Professional:** CRM, Field Ops, Analytics, Priority Support, Integrations
- **Enterprise:** All + Compliance, REST API, White Label

---

## 3. KEY INITIALIZATION FILES

### 3.1 `functions.php` (377 lines)

**Location:** Root directory

**Responsibilities:**
1. Define theme constants (`CAMPAIGNPRESS_VERSION`, `CAMPAIGNPRESS_THEME_DIR`, etc.)
2. Theme setup (`after_setup_theme` hook):
   - Translation support
   - Post thumbnails with custom sizes
   - Navigation menus (primary, footer, social)
   - HTML5 support
   - Custom logo
   - Editor color palette (Democrat Blue, Republican Red, etc.)
3. Enqueue Bootstrap 5.3 CSS/JS from CDN
4. Enqueue theme assets (main.css, main.js)
5. Register widget areas (sidebar, 3 footer areas)
6. Load all 18 free modules via `require_once`
7. Conditionally load Elementor widgets (if Elementor active)
8. Load premium system if exists
9. Custom template loader for organized CPT templates
10. Security headers (X-Content-Type-Options, X-Frame-Options, etc.)

**Load Order:**
```php
// Free modules (always loaded)
require_once .../free/class-bootstrap-navwalker.php
require_once .../free/custom-post-types.php
require_once .../free/gutenberg-blocks.php
require_once .../free/customizer.php
// ... 14 more free modules

// Premium (conditional)
if (file_exists(.../premium/premium-init.php)) {
    require_once .../premium/premium-init.php
}
```

### 3.2 `includes/premium/premium-init.php` (1,344 lines)

**Location:** `includes/premium/premium-init.php`

**Responsibilities:**
1. License validation system
2. Feature toggles and tier management
3. Dynamic module loading based on license
4. Admin menu structure for premium features
5. License activation/deactivation
6. Feature enable/disable controls
7. System status monitoring

**Class:** `CampaignPress_Premium` (Singleton pattern)

**Key Methods:**
- `get_instance()` - Singleton accessor
- `load_premium_modules()` - Dynamically load enabled modules
- `validate_license()` - Check license with remote API
- `is_feature_enabled($feature)` - Check if feature is available
- `get_license_tier()` - Return current tier (basic/professional/enterprise)

---

## 4. CUSTOM POST TYPES & TEMPLATES

### 4.1 Custom Post Types (5 CPTs)

**File:** `includes/free/custom-post-types.php`

All CPTs use prefix `cp_` and are registered on `init` hook:

1. **cp_issue** - Policy positions
   - Supports: title, editor, thumbnail, excerpt
   - Rewrite: `/issues/`
   - Hierarchical: No
   - Template: `templates/custom-post-types/single/single-cp_issue.php`

2. **cp_event** - Campaign events
   - Supports: title, editor, thumbnail, excerpt
   - Meta fields: event_date, event_time, event_location, event_capacity
   - Rewrite: `/events/`
   - Template: `templates/custom-post-types/single/single-cp_event.php`

3. **cp_endorsement** - Endorsements
   - Supports: title, editor, thumbnail
   - Meta fields: endorser_title, endorser_organization
   - Rewrite: `/endorsements/`
   - Template: `templates/custom-post-types/single/single-cp_endorsement.php`

4. **cp_team** - Campaign team members
   - Supports: title, editor, thumbnail
   - Meta fields: team_position, team_email, team_phone, team_social
   - Rewrite: `/team/`
   - Template: `templates/custom-post-types/single/single-cp_team.php`

5. **cp_volunteer** - Volunteers
   - Supports: title (name only, managed in separate database)
   - Custom table: `wp_cp_volunteers`
   - Rewrite: `/volunteers/`
   - Template: `templates/custom-post-types/single/single-cp_volunteer.php`

### 4.2 Template Resolution System

**File:** `functions.php` (lines 307-327)

Custom filter `campaignpress_template_loader()` on hooks:
- `single_template` - Single CPT pages
- `archive_template` - Archive CPT pages

**Logic:**
1. Check if template name starts with `single-cp_` or `archive-cp_`
2. Look in `templates/custom-post-types/single/` or `templates/custom-post-types/archive/`
3. Return custom template if exists, otherwise fallback to default

**Taxonomy System:**
- **Issue Categories** - `cp_issue_category` (hierarchical)
- **Event Types** - `cp_event_type` (flat)
- **Team Departments** - `cp_team_department` (flat)

---

## 5. GUTENBERG BLOCKS & ELEMENTOR WIDGETS

### 5.1 Gutenberg Blocks (7 blocks)

**File:** `includes/free/gutenberg-blocks.php`

All blocks registered on `init` hook with `register_block_type()`:

1. **campaignpress/donation-button**
   - Attributes: processor, amounts, recurring, customAmount
   - Render: PHP callback with payment processor integration

2. **campaignpress/progress-meter**
   - Attributes: goal, raised, showPercentage
   - Render: Animated progress bar with Chart.js hooks

3. **campaignpress/issue-card**
   - Attributes: title, description, icon, link
   - Render: Policy position card with icon

4. **campaignpress/endorsement-grid**
   - Attributes: columns, category
   - Render: Grid of endorsement posts

5. **campaignpress/event-countdown**
   - Attributes: eventDate, eventTitle
   - Render: Live countdown timer (JavaScript)

6. **campaignpress/volunteer-cta**
   - Attributes: title, description, buttonText
   - Render: Call-to-action with volunteer form integration

7. **campaignpress/social-buttons**
   - Attributes: platforms (array)
   - Render: Social media follow buttons

**Block Registration Pattern:**
```php
register_block_type('campaignpress/block-name', [
    'editor_script' => 'campaignpress-blocks-js',
    'editor_style'  => 'campaignpress-blocks-css',
    'render_callback' => 'campaignpress_render_block_name',
    'attributes' => [/* ... */]
]);
```

### 5.2 Elementor Widgets (10 widgets)

**File:** `includes/free/elementor-widgets.php`

**Widget Class Hierarchy:**
```
\Elementor\Widget_Base
  └── CP_Elementor_Widget_[Name]
```

**Widgets:**
1. `CP_Elementor_Widget_Donation_Button`
2. `CP_Elementor_Widget_Progress_Meter`
3. `CP_Elementor_Widget_Issue_Card`
4. `CP_Elementor_Widget_Endorsement_Grid`
5. `CP_Elementor_Widget_Event_Countdown`
6. `CP_Elementor_Widget_Volunteer_CTA`
7. `CP_Elementor_Widget_Social_Buttons`
8. `CP_Elementor_Widget_Team_Member`
9. `CP_Elementor_Widget_Event_RSVP`
10. `CP_Elementor_Widget_Testimonial`

**Registration:**
Hooked to `elementor/widgets/register` action, widgets are registered with `register()` method.

**Widget Pattern:**
```php
class CP_Elementor_Widget_Name extends \Elementor\Widget_Base {
    public function get_name() { return 'cp-widget-name'; }
    public function get_title() { return 'Widget Title'; }
    public function get_icon() { return 'eicon-icon'; }
    public function get_categories() { return ['campaignpress']; }
    protected function register_controls() { /* ... */ }
    protected function render() { /* HTML output */ }
}
```

---

## 6. DATABASE ARCHITECTURE

### 6.1 WordPress Standard Tables
Uses default WP tables for:
- Posts (`wp_posts`) - Issues, Events, Endorsements, Team
- Post Meta (`wp_postmeta`) - Custom fields for CPTs
- Users (`wp_users`) - WordPress users
- Options (`wp_options`) - Theme settings, license keys

### 6.2 Custom Tables (Premium CRM - 11 tables)

**Prefix:** `wp_cp_` (follows WordPress table prefix)

**Tables:**

1. **`wp_cp_contacts`** - Main contact database
   - Primary key: `id` (BIGINT AUTO_INCREMENT)
   - Indexes: email, phone, last_name, zip_code, voter_id, engagement_score
   - Fields: 50+ (name, email, phone, address, voter data, demographics)
   - Charset: utf8mb4_unicode_ci
   - Engine: InnoDB
   - Optimized for 50,000+ records

2. **`wp_cp_interactions`** - Interaction history
   - Primary key: `id`
   - Foreign key: `contact_id` → `wp_cp_contacts.id`
   - Indexes: contact_id, interaction_type, interaction_date, user_id
   - Types: call, text, email, door_knock, event, donation, volunteer, meeting, note, other

3. **`wp_cp_tags`** - Tag definitions
   - Primary key: `id`
   - Indexes: tag_slug, tag_type
   - Types: system, custom

4. **`wp_cp_contact_tags`** - Many-to-many contact→tag relationship
   - Composite primary key: (contact_id, tag_id)
   - Indexes: contact_id, tag_id

5. **`wp_cp_segments`** - Saved search segments
   - Primary key: `id`
   - Types: dynamic (query-based), static (fixed list)
   - Stores JSON query criteria

6. **`wp_cp_segment_contacts`** - Many-to-many segment→contact
   - Composite primary key: (segment_id, contact_id)

7. **`wp_cp_households`** - Household groupings
   - Primary key: `id`
   - Indexes: address_hash (for duplicate detection)

8. **`wp_cp_contact_households`** - Many-to-many contact→household
   - Composite primary key: (contact_id, household_id)

9. **`wp_cp_custom_fields`** - Dynamic field definitions
   - Primary key: `id`
   - Field types: text, number, date, dropdown, checkbox

10. **`wp_cp_contact_custom_fields`** - Custom field values
    - Composite primary key: (contact_id, field_id)

11. **`wp_cp_engagement_scores`** - Calculated engagement metrics
    - Primary key: `contact_id` (1:1 with contacts)
    - Scores: recency_score, frequency_score, quality_score, response_rate_score, total_score (0-100)

**Database Schema Management:**
- Created on theme activation via `CRM_Database::create_tables()`
- Updates handled via version checking
- All queries use `$wpdb->prepare()` for security
- Indexes optimized for common query patterns

---

## 7. REST API (Premium - Enterprise)

**File:** `includes/premium/api/api-init.py`

**Base Endpoint:** `/wp-json/campaignpress/v1/`

**Features:**
- Contact CRUD operations
- Interaction logging
- Segment management
- Event operations
- Custom X-API-Key authentication
- Rate limiting (1000 req/hour per key)
- Webhook delivery with retries
- Request and response logging with audit trail

---

## 8. ARCHITECTURAL PATTERNS

### 8.1 Singleton Pattern

Used for major system components to ensure single instance:
- `CampaignPress_Premium` - License & feature management
- `CampaignPress_CRM_Init` - CRM system coordinator
- `CP_Field_Operations_Init` - Field operations coordinator
- `CampaignPress_Developer_Console` - Developer tools coordinator

Pattern:
```php
private static $instance = null;
public static function get_instance() {
    if (null === self::$instance) {
        self::$instance = new self();
    }
    return self::$instance;
}
private function __construct() { ... }
```

### 8.2 Feature Toggle Pattern

Premium features can be:
- Disabled entirely (module not initialized)
- Available but disabled (can toggle in admin)
- Limited by license tier (professional/enterprise)

### 8.3 Hook-Based Extensibility

All major systems use WordPress hooks:
- **Actions:** `after_setup_theme`, `init`, `admin_menu`, `rest_api_init`, `wp_enqueue_scripts`
- **Filters:** `campaignpress_*` prefixed filters throughout
- **Module-specific hooks** for integrations and customization

### 8.4 MVC-Like Separation

- **Model:** Database classes (CRM_Database, CRM_Contacts, CRM_Segments)
- **View:** Template files, block render callbacks, Elementor widget render()
- **Controller:** Init classes, AJAX handlers, REST endpoints

---

## 9. SECURITY & COMPLIANCE

### 9.1 Built-In Security

1. Remove WordPress version from head tag
2. Security headers:
   - X-Content-Type-Options: nosniff
   - X-Frame-Options: SAMEORIGIN
   - X-XSS-Protection: 1; mode=block
   - Referrer-Policy: strict-origin-when-cross-origin

3. Content Security Policy (relaxed on localhost for development)

### 9.2 Input/Output Protection

- Nonce verification on all AJAX endpoints
- Capability checks (manage_options for admin features)
- Input sanitization (sanitize_text_field, sanitize_email)
- Output escaping (esc_html, esc_url, esc_attr)
- Prepared statements for all database queries

### 9.3 Compliance Features

- **GDPR Ready:** Data exporter in Developer Console
- **WCAG 2.1 AA:** Accessibility compliance module
- **FEC Compliance:** Automatic contribution tracking (premium feature)

---

## 10. FILE ORGANIZATION

```
campaign-office/
├── functions.php                       [Theme entry point]
├── style.css                           [Theme metadata]
├── readme.txt                          [Theme description]
│
├── assets/
│   ├── css/                            [Stylesheets]
│   │   ├── main.css, blocks.css, elementor-widgets.css
│   │   ├── admin-options.css, volunteer-admin.css
│   │   └── premium-admin.css, rtl.css
│   ├── js/                             [JavaScript files]
│   │   ├── main.js, blocks.js, customizer.js
│   │   ├── admin-notices.js, admin-options.js
│   │   └── volunteer-admin.js, premium-admin.js
│   └── react/                          [React components (optional)]
│       ├── crm/
│       ├── blocks/
│       └── components/
│
├── includes/
│   ├── free/                           [18 modules - always loaded]
│   │   ├── custom-post-types.php
│   │   ├── gutenberg-blocks.php
│   │   ├── elementor-widgets.php
│   │   ├── volunteer-management.php
│   │   ├── event-management.php
│   │   ├── donation-enhancements.php
│   │   ├── accessibility.php
│   │   ├── translation-support.php
│   │   ├── customizer.php
│   │   ├── template-functions.php
│   │   ├── template-tags.php
│   │   ├── campaign-widgets.php
│   │   ├── admin-theme-options.php
│   │   ├── admin-notices.php
│   │   ├── integrations.php
│   │   ├── demo-content.php
│   │   ├── class-bootstrap-navwalker.php
│   │   └── tgmpa-config.php
│   │
│   └── premium/                        [9 modules - license-gated]
│       ├── premium-init.php            [Main license system]
│       ├── crm/                        [CRM module - 5 files]
│       │   ├── crm-init.php
│       │   ├── class-crm-database.php
│       │   ├── class-crm-contacts.php
│       │   ├── class-crm-interactions.php
│       │   ├── class-crm-segments.php
│       │   └── class-crm-import-export.php
│       ├── field-operations/           [Field ops - 5 files]
│       ├── compliance/                 [FEC compliance - 5 files]
│       ├── analytics/                  [Analytics - 3 files]
│       ├── api/                        [REST API - 3 files]
│       ├── integrations/               [Email/SMS - 3 files]
│       ├── developer-console/          [Dev tools - 7 files]
│       └── admin-pages/                [Admin UI - 4 files]
│
├── templates/                          [Template files]
│   ├── custom-post-types/
│   │   ├── single/                     [single-cp_*.php]
│   │   └── archive/                    [archive-cp_*.php]
│   ├── page-templates/                 [Page layouts]
│   └── parts/                          [Template parts]
│
├── vite.config.js                      [Build configuration]
├── package.json                        [NPM dependencies]
│
└── [Root theme templates]
    ├── header.php, footer.php
    ├── front-page.php, index.php
    ├── single.php, page.php
    ├── archive.php, category.php, tag.php, author.php
    ├── search.php, 404.php
    ├── comments.php, searchform.php
    └── sidebar.php
```

---

## 11. DEVELOPER QUICK START

### Setup
1. Install WordPress 6.9+
2. Activate theme in admin
3. Verify `theme.json` and `design-system-wp69.css` are present

### Design System Development
- **Read:** `docs/DESIGN-REFERENCE.md` (complete style guide)
- **Quick Start:** `docs/QUICKSTART.md` (5 minutes)
- **Implementation:** `docs/DEVELOPER-GUIDE.md` (technical details)
- **Edit Design Tokens:** Modify `theme.json` for global changes
- **Custom CSS:** Use WordPress CSS variables (`--wp--preset--*`)
- **Block Patterns:** Create reusable layouts for campaigns
- **Color Schemes:** Add/modify party themes in theme.json

### Free Module Development
- Modify `includes/free/*.php` directly
- Test Gutenberg blocks in block editor
- Test Elementor widgets with Elementor active
- Customize via Theme Customizer
- **Use design tokens** from theme.json in all new CSS

### Premium Module Development
1. Add `includes/premium/premium-init.php` (if missing)
2. Create license key (or use CAMPAIGNPRESS_DEV_LICENSE_BYPASS constant)
3. Add feature definition to premium-init.php features array
4. Create module in `includes/premium/{module}/`
5. Test via admin: CampaignPress Pro → Features → Toggle feature
6. **Use design system** for consistent UI styling

### Debugging
- Enable WP_DEBUG in wp-config.php
- Check `/wp-content/debug.log`
- **Design Tokens:** Use browser DevTools to inspect CSS variables
- **Block Editor:** Check if design tokens appear in color/typography pickers
- Use Developer Console (if premium):
  - Database inspector with query builder
  - System health checks
  - API endpoint tester
  - GDPR data exporter

---

## CONCLUSION

CampaignPress is a **modular, license-gated WordPress theme** designed to transform political campaign websites into comprehensive operations platforms. The architecture maintains a clear free/premium boundary while enabling sophisticated features like CRM, field operations, compliance tracking, and analytics for premium users.

**Architectural Strengths:**
- **WordPress 6.9-Native Design System** with theme.json and centralized design tokens
- **Distinctive Professional Design** with Bricolage Grotesque, Plus Jakarta Sans, JetBrains Mono
- **Block Editor Integration** - Full Gutenberg compatibility with visual design controls
- **9-Shade Color Palettes** - 33 total colors for granular control
- **4 Party Color Schemes** - Democrat Blue, Republican Red, Independent Purple, Green Party
- **Accessibility Compliant** - WCAG 2.1 AA with reduced motion support
- Clear module separation with single responsibility principle
- Singleton pattern for major system components
- Hook-based extensibility throughout the codebase
- Built-in security (sanitization, escaping, nonces, prepared statements)
- Comprehensive database schema (11 custom CRM tables, optimized indexes)
- Developer tools (console, API tester, database inspector, data exporter)
- Freemium license system with multiple tiers (Basic, Professional, Enterprise)

**Design System Features:**
- Fluid typography (automatic mobile-to-desktop scaling)
- Sophisticated animations (staggered reveals, pulse effects, hover lifts)
- Atmospheric backgrounds (layered gradients, animated effects)
- Performance optimized (WordPress-managed fonts, GPU-accelerated CSS)
- User-friendly (point-and-click design in block editor)

**Version:** 2.0.0
**WordPress Required:** 6.9+
**License:** GPLv3 or later
**Theme Slug:** campaign-office
**Last Updated:** December 6, 2025

---

## DESIGN AESTHETICS & PHILOSOPHY

**CampaignPress Design System Example:**

This theme implements the design philosophy outlined below. When making design decisions or adding features, follow these patterns established in the design system (see `DESIGN_SYSTEM.md` for complete details).

### Typography Pattern (Already Implemented)
✅ **Distinctive Font Choices:**
- **Bricolage Grotesque** (headlines) - Bold, geometric, authoritative
- **Plus Jakarta Sans** (body) - Modern, warm, readable
- **JetBrains Mono** (data/stats) - Clean, precise, technical

❌ **Avoid:** Inter, Roboto, Arial, Space Grotesk, system fonts

### Color Pattern (Already Implemented)
✅ **9-Shade Palettes:**
- Primary: 50 → 900 (lightest to darkest)
- Accent: 50 → 900 (complementary energy)
- Neutral: 50 → 900 (grays for structure)
- Dominant colors with sharp accents

❌ **Avoid:** Flat single colors, purple gradients on white, timid palettes

### Motion Pattern (Already Implemented)
✅ **High-Impact Orchestrated Animations:**
- **Staggered hero reveals** (0.2s delays: title → subtitle → tagline → CTA)
- **Button pulse** (2s breathing animation)
- **Card hover lifts** (translateY + scale transforms)
- **Progress bar shine** (animated shimmer effect)

❌ **Avoid:** Scattered micro-interactions, simultaneous animations

### Background Pattern (Already Implemented)
✅ **Layered Atmospheric Depth:**
- **5 visual layers** on hero (image, gradient overlay, radial gradients, patterns, content)
- **Animated gradient meshes** (20s subtle shift)
- **Geometric patterns** (diagonal lines for texture)

❌ **Avoid:** Solid flat colors, single gradients

### WordPress 6.9 Design Token System
When adding new components or features:

1. **Use existing design tokens** from theme.json:
   ```css
   color: var(--wp--preset--color--primary);
   font-family: var(--wp--preset--font-family--display);
   font-size: var(--wp--preset--font-size--2-xl);
   padding: var(--wp--preset--spacing--8);
   box-shadow: var(--wp--preset--shadow--lg);
   ```

2. **Maintain consistency** with established patterns:
   - Headings use Display font (Bricolage Grotesque)
   - Body text uses Body font (Plus Jakarta Sans)
   - Numbers/stats use Mono font (JetBrains Mono)
   - Colors follow 9-shade system
   - Spacing uses 8px grid (1-24)

3. **Enhance, don't replace** existing design:
   - Add new gradients to theme.json gradients array
   - Add new colors as shades (50-900 pattern)
   - Follow animation timing (fast: 150ms, base: 250ms, slow: 350ms)

### General Design Principles

**Do:**
- Choose distinctive, contextual fonts (avoid generic choices)
- Use CSS variables for consistency (WordPress `--wp--preset--*` pattern)
- Create cohesive color palettes with dominant + accent pattern
- Orchestrate animations with purpose and timing
- Layer backgrounds for atmospheric depth
- Think outside the box - make unexpected, delightful choices

**Don't:**
- Use overused fonts (Inter, Roboto, Arial, system fonts, Space Grotesk)
- Default to clichéd color schemes (purple gradients on white)
- Create predictable, cookie-cutter layouts
- Add scattered animations without purpose
- Use flat, solid color backgrounds

**Context-Specific Design:**
CampaignPress is a **political campaign theme** - designs should convey:
- **Trust & Authority** (bold typography, professional colors)
- **Energy & Momentum** (animations, gradients, dynamic effects)
- **Accessibility** (WCAG AA, reduced motion support)
- **Professionalism** (consistent design system, polished details)

---

**When working with CampaignPress frontend code, always:**
1. Reference the design system documentation (`DESIGN_SYSTEM.md`)
2. Use WordPress 6.9 design tokens (`theme.json`)
3. Maintain the distinctive aesthetic (avoid generic AI design patterns)
4. Test accessibility and performance
5. Follow the established patterns for typography, color, motion, and backgrounds