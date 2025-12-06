# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# CampaignPress Architecture Overview

**Version:** 2.0.0 | **Status:** Freemium Political WordPress Theme + Campaign Operations Platform

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

## 1. HIGH-LEVEL ARCHITECTURE

### 1.1 Core Philosophy

The theme follows a **layered modular architecture**:
- **WordPress Root Level** - Standard templates (header, footer, etc.)
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
1. Install WordPress 6.4+
2. Activate theme in admin

### Free Module Development
- Modify `includes/free/*.php` directly
- Test Gutenberg blocks in block editor
- Test Elementor widgets with Elementor active
- Customize via Theme Customizer

### Premium Module Development
1. Add `includes/premium/premium-init.php` (if missing)
2. Create license key (or use CAMPAIGNPRESS_DEV_LICENSE_BYPASS constant)
3. Add feature definition to premium-init.php features array
4. Create module in `includes/premium/{module}/`
5. Test via admin: CampaignPress Pro → Features → Toggle feature

### Debugging
- Enable WP_DEBUG in wp-config.php
- Check `/wp-content/debug.log`
- Use Developer Console (if premium):
  - Database inspector with query builder
  - System health checks
  - API endpoint tester
  - GDPR data exporter

---

## CONCLUSION

CampaignPress is a **modular, license-gated WordPress theme** designed to transform political campaign websites into comprehensive operations platforms. The architecture maintains a clear free/premium boundary while enabling sophisticated features like CRM, field operations, compliance tracking, and analytics for premium users.

**Architectural Strengths:**
- Clear module separation with single responsibility principle
- Singleton pattern for major system components
- Hook-based extensibility throughout the codebase
- Built-in security (sanitization, escaping, nonces, prepared statements)
- Comprehensive database schema (11 custom CRM tables, optimized indexes)
- Developer tools (console, API tester, database inspector, data exporter)
- Backward compatible with WordPress 6.4+
- Freemium license system with multiple tiers (Basic, Professional, Enterprise)

**Version:** 2.0.0
**License:** GPLv3 or later
**Theme Slug:** campaign-office
**Last Updated:** December 6, 2025

DISTILLED_AESTHETICS_PROMPT = """
<frontend_aesthetics>
You tend to converge toward generic, "on distribution" outputs. In frontend design, this creates what users call the "AI slop" aesthetic. Avoid this: make creative, distinctive frontends that surprise and delight. Focus on:

Typography: Choose fonts that are beautiful, unique, and interesting. Avoid generic fonts like Arial and Inter; opt instead for distinctive choices that elevate the frontend's aesthetics.

Color & Theme: Commit to a cohesive aesthetic. Use CSS variables for consistency. Dominant colors with sharp accents outperform timid, evenly-distributed palettes. Draw from IDE themes and cultural aesthetics for inspiration.

Motion: Use animations for effects and micro-interactions. Prioritize CSS-only solutions for HTML. Use Motion library for React when available. Focus on high-impact moments: one well-orchestrated page load with staggered reveals (animation-delay) creates more delight than scattered micro-interactions.

Backgrounds: Create atmosphere and depth rather than defaulting to solid colors. Layer CSS gradients, use geometric patterns, or add contextual effects that match the overall aesthetic.

Avoid generic AI-generated aesthetics:
- Overused font families (Inter, Roboto, Arial, system fonts)
- Clichéd color schemes (particularly purple gradients on white backgrounds)
- Predictable layouts and component patterns
- Cookie-cutter design that lacks context-specific character

Interpret creatively and make unexpected choices that feel genuinely designed for the context. Vary between light and dark themes, different fonts, different aesthetics. You still tend to converge on common choices (Space Grotesk, for example) across generations. Avoid this: it is critical that you think outside the box!
</frontend_aesthetics>
"""