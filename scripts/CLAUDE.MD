# CampaignPress Development Guide

> **AI Coding Assistant Reference** — Comprehensive documentation for developing and maintaining the CampaignPress WordPress theme and Campaign Office Core plugin.

## Project Overview

**CampaignPress** is a professional WordPress theme designed specifically for political campaigns, candidates, and advocacy organizations. It works in tandem with the **Campaign Office Core** plugin which provides custom post types and campaign-specific functionality.

### Repository Structure

```
campaignpress/            # Theme (CampaignPress)
├── assets/               # CSS, JS, images, fonts
├── blocks/               # Custom Gutenberg blocks
├── includes/             # PHP functionality modules
│   ├── core/             # Core theme classes
│   ├── free/             # Free tier features
│   └── premium/          # Premium tier features (licensed)
├── parts/                # Block theme template parts
├── patterns/             # Block patterns
├── templates/            # Block theme templates
├── scripts/              # Build & optimization scripts
├── tests/                # Testing suite
└── docs/                 # Documentation

campaign-office-core/     # Plugin (Campaign Office Core)
├── includes/             # Plugin functionality
│   ├── contact-manager.php
│   ├── custom-post-types.php
│   ├── event-management.php
│   ├── event-calendar-enhancements.php
│   └── volunteer-management.php
└── assets/               # Plugin assets
```

---

## Technology Stack

| Category | Technology | Version |
|----------|------------|---------|
| **WordPress** | Block Theme (FSE) | 6.4+ (tested to 6.9) |
| **PHP** | Required | 7.4+ |
| **CSS Framework** | Tailwind CSS | v4.1.18 |
| **JS Framework** | WordPress Scripts + Vite | Latest |
| **CSS Integration** | Bootstrap 5 (CDN) | 5.3.0 |
| **Typography** | Bricolage Grotesque, Plus Jakarta Sans, JetBrains Mono | Self-hosted |
| **Icons** | Heroicons | SVG-based |
| **Testing** | PHPUnit, Jest, Playwright | See package.json |

---

## Design System

### Color Palette

The theme supports multiple political party color schemes:

| Scheme | Primary | Accent | CSS Class |
|--------|---------|--------|-----------|
| **Democrat Blue** (default) | `#0053c3` | `#ff8800` | `.color-scheme-democrat-blue` |
| **Republican Red** | `#e81b23` | `#002868` | `.color-scheme-republican-red` |
| **Independent Purple** | `#6554c0` | `#00b8d9` | `.color-scheme-independent-purple` |
| **Green Party** | `#228B22` | `#FFD700` | `.color-scheme-green-party` |
| **Neutral** | `#495057` | `#6c757d` | `.color-scheme-neutral` |

### CSS Variables

All design tokens are defined in `theme.json` and mapped to CSS variables in `style.css`:

```css
/* WordPress 6.9+ Design Tokens */
--wp--preset--color--primary
--wp--preset--color--accent
--wp--preset--spacing--4, --6, --10, --16
--wp--preset--font-size--xs, --sm, --base, --lg, --xl, --2-xl, --3-xl, --4-xl
--wp--preset--shadow--sm, --md, --lg, --xl, --2-xl

/* Legacy Mappings (for backward compatibility) */
--cp-primary, --cp-secondary, --cp-spacing-sm, --cp-spacing-md
```

### Typography Scale (Fluid)

```css
xs:   clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem)
sm:   clamp(0.875rem, 0.825rem + 0.25vw, 1rem)
base: clamp(1rem, 0.95rem + 0.25vw, 1.125rem)
lg:   clamp(1.125rem, 1.05rem + 0.375vw, 1.375rem)
xl:   clamp(1.25rem, 1.15rem + 0.5vw, 1.75rem)
2-xl: clamp(1.5rem, 1.3rem + 1vw, 2.25rem)
3-xl: clamp(2rem, 1.6rem + 2vw, 3.5rem)
4-xl: clamp(2.5rem, 1.8rem + 3.5vw, 5rem)
```

---

## Custom Post Types

Registered by the **Campaign Office Core** plugin:

| Post Type | Slug | Purpose |
|-----------|------|---------|
| **Issues** | `cp_issue` | Policy positions and campaign platform |
| **Events** | `cp_event` | Campaign events with dates, locations, RSVP |
| **Endorsements** | `cp_endorsement` | Support from leaders and organizations |
| **Team Members** | `cp_team` | Campaign staff and volunteers |
| **Volunteer Opportunities** | `cp_volunteer` | Volunteer recruitment listings |

### Template Files

Templates are located in `/templates/` (block theme format):

- `single-cp_issue.html`, `archive-cp_issue.html`
- `single-cp_event.html`, `archive-cp_event.html`
- `single-cp_endorsement.html`, `archive-cp_endorsement.html`
- `single-cp_team.html`, `archive-cp_team.html`
- `single-cp_volunteer.html`, `archive-cp_volunteer.html`

---

## Custom Blocks

Located in `/blocks/`:

| Block | Namespace | Purpose |
|-------|-----------|---------|
| **Countdown** | `campaignpress/countdown` | Event countdown timer |
| **Donation Form** | `campaignpress/donation-form` | Campaign donation integration |
| **Event Organizer** | `campaignpress/event-organizer` | Event display and management |
| **Hero Commander** | `campaignpress/hero-commander` | Hero section builder |
| **Icon** | `campaignpress/icon` | Heroicons display |
| **Mission Control** | `campaignpress/mission-control` | Campaign dashboard |
| **Policy Platform** | `campaignpress/policy-platform` | Issues/policy display |
| **Progress** | `campaignpress/progress` | Progress indicators |
| **Section Wrapper** | `campaignpress/section-wrapper` | Section layout container |
| **Style Panel** | `campaignpress/style-panel` | Style control panel |
| **Volunteer Matcher** | `campaignpress/volunteer-matcher` | Volunteer opportunity matching |

---

## Theme Architecture

### Feature Tiers

```
FREE TIER (includes/free/)
├── custom-post-types.php       # Basic CPT registration
├── demo-content.php            # Demo content importer
├── volunteer-management.php    # Volunteer signup
├── event-management.php        # Event handling
├── customizer.php              # Theme customizer
├── heroicons.php               # Icon system
└── template-tags.php           # Template helpers

PREMIUM TIER (includes/premium/)
├── admin-pages/                # Advanced admin UI
├── analytics/                  # Campaign analytics
├── api/                        # REST API extensions
├── compliance/                 # FEC/legal compliance
├── crm/                        # Donor/contact management
├── design-studio/              # Advanced design tools
├── developer-console/          # Dev tools
├── field-operations/           # Canvassing tools
└── integrations/               # Third-party integrations
```

### Core Classes (`includes/core/`)

| Class | File | Purpose |
|-------|------|---------|
| `CP_Performance` | `class-performance.php` | Caching, optimization |
| `CP_Script_Manager` | `class-script-manager.php` | Asset loading |
| `CP_Template_Loader` | `class-template-loader.php` | Template hierarchy |
| `CP_Contact_Manager` | `class-contact-manager.php` | Contact database |
| `CP_Contact_Migration` | `class-contact-migration.php` | Data migration |

---

## Development Commands

### NPM Scripts (package.json)

```bash
# Development
npm run dev              # Start Vite dev server
npm run build            # Build production assets
npm run build:css        # Compile Tailwind CSS only
npm run watch:css        # Watch and compile CSS

# Testing
npm run test             # Run all tests
npm run test:all         # Lint + JS + PHP + theme check
npm run test:quick       # Quick lint + JS tests
npm run test:php         # PHPUnit tests (via Composer)
npm run test:js          # Jest JavaScript tests
npm run test:e2e         # Playwright E2E tests
npm run test:a11y        # Accessibility tests
npm run test:performance # Performance tests

# Linting
npm run lint:js          # ESLint JavaScript
npm run lint:css         # Stylelint CSS
npm run lint:js:fix      # Fix JS lint issues
npm run lint:css:fix     # Fix CSS lint issues

# Optimization
npm run speed-optimize   # Full optimization pipeline
npm run optimize:images  # Compress images
npm run optimize:css     # Purge/minify CSS
npm run optimize:js      # Minify JavaScript
npm run critical-css     # Generate critical CSS
npm run lighthouse       # Run Lighthouse tests
npm run pre-optimize     # Pre-optimization check
npm run post-optimize    # Post-optimization check
npm run clean            # Clean build artifacts
```

### Composer Scripts (composer.json)

```bash
composer test            # Run PHPUnit
composer test:unit       # Unit tests only
composer test:coverage   # Generate coverage report
composer phpcs           # Run PHP CodeSniffer
composer phpcbf          # Fix PHPCS issues
composer phpcs:theme     # WordPress Theme standards
composer lint            # PHP syntax check
```

### Build Scripts

```powershell
# Windows - Production build
.\build-production.ps1
.\build-production.ps1 -OutputDir "C:\releases" -Version "2.1.0"

# Windows - Testing build
.\build-testing.ps1
```

---

## Testing Environment

### Environment Configuration

Create `.env` in theme root:

```env
SITE_URL=http://localhost:8881
# or use named host
HOST_ENV=local
```

### Available Test Hosts (scripts/config.js)

```javascript
local:       'http://localhost:8881'
localwp:     'http://campaignpress.local'
staging:     'http://staging.campaignpress.com'
production:  'https://campaignpress.com'
```

### Test Structure

```
tests/
├── phpunit.xml.dist         # PHPUnit configuration
├── jest.config.js           # Jest configuration
├── playwright.config.js     # Playwright configuration
├── accessibility/           # A11y tests (Pa11y)
├── performance/             # Performance tests
└── theme-check.js           # WordPress theme standards
```

---

## Key Files Reference

### Configuration Files

| File | Purpose |
|------|---------|
| `theme.json` | WordPress design tokens, colors, typography, spacing |
| `style.css` | Theme metadata + CSS variable mappings + fallbacks |
| `functions.php` | Theme setup, enqueues, feature loading |
| `package.json` | NPM dependencies and scripts |
| `composer.json` | PHP dependencies and scripts |
| `scripts/config.js` | Build script configuration |
| `.env.example` | Environment variable template |
| `.distignore` | Files excluded from distribution |

### Asset Paths

```
assets/
├── css/
│   ├── app.css              # Tailwind source
│   ├── dist/tailwind.css    # Compiled CSS
│   ├── critical/            # Critical CSS per page
│   ├── editor.css           # Block editor styles
│   └── heroicons.css        # Icon styles
├── js/
│   ├── main.js              # Main theme JS
│   └── editor-overrides.js  # Block editor customizations
├── images/                  # Theme images
├── fonts/                   # Self-hosted fonts
└── dist/                    # Compiled assets
```

---

## Security Best Practices

### AJAX/Nonce Verification

```php
// Always verify nonces for AJAX handlers
if (!wp_verify_nonce($_POST['nonce'], 'campaignpress_nonce')) {
    wp_die('Security check failed');
}
```

### Rate Limiting

```php
// Use built-in rate limiting for public endpoints
if (campaignpress_is_rate_limited('volunteer_signup', 5, 3600)) {
    wp_send_json_error('Too many requests');
}
```

### Encrypted Options

```php
// Store sensitive data encrypted
campaignpress_update_encrypted_option('api_key', $key);
$key = campaignpress_get_encrypted_option('api_key');
```

### Security Headers

Applied automatically via `campaignpress_security_headers()`:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`

---

## Accessibility Standards

Theme targets **WCAG 2.1 AA** compliance:

- **Minimum Touch Target Size**: 44×44px for buttons
- **Color Contrast**: AA compliant text colors (`--cp-primary-text`, `--cp-accent-text`)
- **Screen Reader Support**: `.sr-only` / `.screen-reader-text` classes
- **Focus Indicators**: Visible focus states on all interactive elements
- **Semantic HTML**: Proper heading hierarchy, ARIA labels

---

## License System

### Development Mode

```php
// Enable in wp-config.php for development
define('CAMPAIGNPRESS_DEV_MODE', true);
```

### Dev License Helper

When `dev-license-helper.php` exists (excluded from production builds):

```
Professional: CP-DEV-PROFESSIONAL-2024-X1Y2Z3W4V5U6
Free:         CP-DEV-FREE-2024-F1R2E3E4EEEE
Email:        dev@campaignpress.test
```

---

## Common Tasks

### Adding a New Block Pattern

1. Create PHP file in `/patterns/`
2. Add pattern registration in `/includes/block-patterns.php`
3. Use `campaignpress/block-name` category

### Creating a Custom Template

1. Add template file to `/templates/` (HTML format)
2. Register in `theme.json` under `customTemplates`
3. Assign appropriate `postTypes`

### Adding Theme Options

1. Add controls in `/includes/free/customizer.php`
2. Use `campaignpress_` prefix for option names
3. Apply via body class or inline CSS

### Modifying Color Schemes

1. Update palette in `theme.json` (settings.color.palette)
2. Add CSS fallbacks in `style.css`
3. Update customizer options if needed

---

## Plugin Dependencies

### Campaign Office Core (Required)

The theme displays a notice if the plugin is not active:
- Custom Post Types functionality
- Volunteer RSVP handling
- Event management features
- Contact database (`cp_contacts` table)

### Optional Integrations

- **Elementor** - Page builder compatibility
- **WooCommerce** - Fundraising store
- **Twilio** - SMS notifications (bulk SMS via cron)
- **Stripe/PayPal** - Donation processing

---

## Performance Optimization

### Built-in Optimizations

- Lazy loading images (except above-the-fold)
- Dashicons disabled for non-admins
- Transient caching for homepage queries
- Font preloading for Google Fonts
- Critical CSS generation pipeline

### Cache Clearing

```php
// Clears when issues/events are updated
campaignpress_clear_homepage_cache($post_id);

// Transients used:
// - campaignpress_homepage_issues
// - campaignpress_homepage_events_{date}
```

### Lighthouse Targets

```javascript
// From scripts/config.js
LCP:         2500ms
CLS:         0.1
TBT:         300ms
TargetScore: 95
```

---

## Coding Standards

### PHP

- Follow WordPress Coding Standards
- Use `campaignpress` text domain for all strings
- Prefix all functions with `campaignpress_`
- Prefix all options with `campaignpress_`
- Document functions with PHPDoc

### JavaScript

- ESLint with WordPress config
- Use `wp` global for WordPress APIs
- Localize scripts with `campaignpress_vars`

### CSS

- Stylelint with standard config
- Use CSS custom properties from theme.json
- Follow BEM naming for custom classes
- Use `cp-` prefix for component classes

---

## Deployment Checklist

- [ ] Update version in `style.css`
- [ ] Run `npm run build` for production assets
- [ ] Run `npm run test:all` for full test suite
- [ ] Check `composer install --no-dev --optimize-autoloader`
- [ ] Generate production zip with `.\build-production.ps1`
- [ ] Verify ZIP excludes dev files (check `.distignore`)
- [ ] Test on fresh WordPress installation
- [ ] Update changelog in `readme.txt`

---

## Troubleshooting

### Common Issues

| Issue | Solution |
|-------|----------|
| Templates not appearing | Check `theme.json` version is 3, verify `customTemplates` array |
| Premium features not loading | Verify license key or enable `CAMPAIGNPRESS_DEV_MODE` |
| Missing CPT archives | Activate Campaign Office Core plugin, flush permalinks |
| Tailwind not compiling | Run `npm run build:css`, check `build/postcss.config.js` |
| E2E tests failing | Verify `SITE_URL` in `.env`, ensure dev server running |

### Debug Mode

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

---

## Resources

- **Theme Docs**: `docs/` directory
- **Pattern Library**: `/patterns/` directory
- **Block Documentation**: `/blocks/*/README.md`
- **GitHub Issues**: https://github.com/mrwalker511/campaign-office/issues
- **Support**: https://campaignpress.com/support
