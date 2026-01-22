# CampaignPress Directory Structure

## Overview

This document explains the organization of the CampaignPress theme repository.

**Current Repository Size**: ~14MB
**WordPress Theme Version**: 2.1.0
**Last Updated**: 2025-01-22

---

## Root Directory Structure

```
campaign-office/                     [WordPress theme root]
├── .claude/                        # Local AI development tools (not in production)
├── .git/                           # Git repository
├── .github/                        # GitHub workflows & templates
├── assets/                         # Theme assets (6.5MB)
├── blocks/                         # Gutenberg block definitions (368KB)
├── build/                          # Build configuration files (44KB)
├── docs/                           # Documentation (880KB)
├── includes/                       # PHP includes (2.4MB)
├── languages/                      # Translation files
├── parts/                          # Template parts
├── patterns/                       # Block patterns
├── scripts/                        # Build and utility scripts (124KB)
├── templates/                      # Page templates (80KB)
├── tests/                          # Test suite (476KB)
├── tools/                          # Development tools
├── functions.php                   # Main theme file (40KB)
├── style.css                       # Theme metadata
├── theme.json                      # Design system (20KB)
├── package.json                    # Node dependencies
├── composer.json                   # PHP dependencies
└── [Standard WordPress template files]
```

---

## Directory Details

### 📁 `/assets/` (6.5MB)

Theme assets organized by type.

```
assets/
├── css/                           # Stylesheets (272KB)
│   ├── app.css                    # Main Tailwind CSS entry
│   ├── admin.css                  # Admin styles
│   ├── critical/                  # Critical CSS for performance
│   └── dist/                      # Compiled CSS output
│
├── fonts/                         # Web fonts (12KB)
│   ├── BricolageGrotesque-Variable.woff2
│   ├── PlusJakartaSans-Variable.woff2
│   └── JetBrainsMono-Variable.woff2
│
├── icons/                         # Heroicons SVG library (5.2MB) ⭐
│   ├── 16/solid/                  # Micro icons (16×16px) - 400+ icons
│   ├── 20/solid/                  # Mini icons (20×20px) - 400+ icons
│   ├── 24/                        # Standard icons (24×24px)
│   │   ├── outline/               # 300+ outline icons
│   │   └── solid/                 # 300+ solid icons
│   ├── social/                    # Social media icons
│   ├── custom/                    # Custom campaign icons
│   └── README.md                  # Icon system documentation
│
├── images/                        # Theme images (28KB)
│   └── [Campaign graphics, placeholders]
│
├── js/                            # JavaScript files (228KB)
│   ├── main.js                    # Main theme JS
│   ├── icons-browser.js           # Icon picker functionality
│   ├── admin-*.js                 # Admin interface scripts
│   └── [Other utility scripts]
│
├── react/                         # React components (120KB)
│   ├── blocks/                    # React-based blocks
│   ├── components/                # Reusable React components
│   ├── crm/                       # CRM admin interface
│   └── [React app files]
│
└── vendor/                        # Third-party libraries (692KB)
    ├── bootstrap/                 # Bootstrap 5.3 (312KB)
    ├── chartjs/                   # Chart.js (208KB)
    └── leaflet/                   # Leaflet maps (168KB)
```

**Why assets/icons is 5.2MB**: See `assets/icons/README.md` for full explanation. This is intentional to support PHP block rendering and icon picker functionality.

---

### 📁 `/blocks/` (368KB)

Modern WordPress blocks using block.json format.

```
blocks/
├── registration.php               # Central block registration
├── block-view-loader.php          # Dynamic block view loader
│
├── countdown/                     # Generic countdown timer
├── donation-button/               # Simple donation button
├── donation-form/                 # Full donation form
├── event-countdown/               # Event countdown widget
├── event-organizer/               # Event organization UI
├── hero-commander/                # Hero section builder
├── icon/                          # Icon block (uses Heroicons)
├── issue-card/                    # Policy position card
├── mission-control/               # Admin dashboard
├── policy-platform/               # Policy platform display
├── progress/                      # Progress meter
├── section-wrapper/               # Section container
├── style-panel/                   # Styling panel
├── volunteer-cta/                 # Volunteer call-to-action
└── volunteer-matcher/             # Volunteer matching UI
```

**Block Structure** (Modern WordPress 5.8+ format):
```
blocks/[block-name]/
├── block.json                     # Block metadata & settings
├── render.php                     # Server-side render callback
├── index.js                       # Block editor JavaScript
├── view.js                        # Frontend JavaScript (optional)
└── style.css                      # Block styles (optional)
```

---

### 📁 `/build/` (44KB)

Build tool configuration files.

```
build/
├── babel.config.cjs               # Babel transpilation config
├── eslint.config.cjs              # ESLint code linting
├── .eslintrc.json                 # ESLint legacy format
├── phpcs.xml.dist                 # PHP CodeSniffer config
├── postcss.config.cjs             # PostCSS processor
├── stylelint.config.json          # CSS linting
├── .stylelintrc.json              # Stylelint legacy format
├── tailwind.config.cjs            # Tailwind CSS config
└── vite.config.js                 # Vite build tool config
```

**Note**: No symlinks - all configs reference `.cjs` files directly in package.json

---

### 📁 `/includes/` (2.4MB)

PHP functionality organized by feature set.

```
includes/
├── .claude/                       # Local AI settings (gitignored)
│
├── admin/                         # Admin UI customizations (40KB)
│   ├── admin-dashboard-fixes.php
│   └── admin-menu-reorganization.php
│
├── core/                          # Core infrastructure classes (92KB)
│   ├── class-contact-manager.php
│   ├── class-contact-migration.php
│   ├── class-performance.php
│   ├── class-script-manager.php
│   ├── class-security-logger.php
│   ├── class-template-loader.php
│   ├── class-url-validator.php
│   └── loader.php
│
├── free/                          # Always-loaded modules (584KB)
│   ├── accessibility.php
│   ├── admin-notices.php
│   ├── admin-theme-options.php
│   ├── campaign-widgets.php
│   ├── class-bootstrap-navwalker.php
│   ├── custom-icons.php
│   ├── customizer.php
│   ├── demo-content.php
│   ├── donation-enhancements.php
│   ├── event-management.php
│   ├── gutenberg-blocks.php
│   ├── heroicons.php             # Heroicons helper functions
│   ├── icons-browser.php         # Icon picker admin UI
│   ├── integrations.php
│   ├── template-functions.php
│   ├── template-tags.php
│   ├── tgmpa-config.php
│   ├── translation-support.php
│   └── volunteer-management.php
│
├── lib/                           # Third-party libraries (144KB)
│   └── tgmpa/                     # TGM Plugin Activation
│
└── premium/                       # License-gated features (1.6MB)
    ├── premium-init.php           # License system & feature manager
    ├── crm/                       # CRM system (5 files)
    ├── field-operations/          # Field ops (5 files)
    ├── compliance/                # FEC compliance (5 files)
    ├── analytics/                 # Analytics (3 files)
    ├── api/                       # REST API (3 files)
    ├── integrations/              # Email/SMS (3 files)
    ├── developer-console/         # Dev tools (7 files)
    └── admin-pages/               # Admin UI (4 files)
```

#### Directory Purpose

| Directory | Purpose | Loaded When |
|-----------|---------|-------------|
| **admin/** | WordPress admin customizations | Admin dashboard only |
| **core/** | Core infrastructure (performance, security, loaders) | Always |
| **free/** | Free theme features (blocks, CPTs, widgets) | Always |
| **lib/** | Third-party PHP libraries | Always |
| **premium/** | License-gated premium features | When license valid |

---

### 📁 `/docs/` (880KB)

Comprehensive documentation for developers and users.

```
docs/
├── guides/
│   ├── DEVELOPER-GUIDE.md         # Developer documentation
│   ├── DESIGN-REFERENCE.md        # Design system guide
│   ├── CUSTOM_ICONS_GUIDE.md      # Icon usage guide
│   └── [Other guides]
│
├── technical/
│   ├── DEPLOYMENT.md              # Deployment instructions
│   ├── TESTING.md                 # Testing procedures
│   └── [Technical specs]
│
└── archive/
    └── [Historical documentation]
```

---

### 📁 `/scripts/` (124KB)

Build and utility scripts.

```
scripts/
├── build-production.sh            # Production build script
├── build-production.ps1           # PowerShell version
├── build-testing.sh               # Testing build
├── clean.js                       # Cleanup script
├── optimize-images.js             # Image optimization
├── optimize-css.js                # CSS optimization
├── optimize-js.js                 # JS optimization
├── generate-critical-css.js       # Critical CSS extraction
└── lighthouse-test.js             # Performance testing
```

---

### 📁 `/templates/` (80KB)

Custom page templates.

```
templates/
├── custom-post-types/
│   ├── single/                    # Single post templates
│   └── archive/                   # Archive templates
└── page-templates/                # Custom page layouts
```

---

### 📁 `/tests/` (476KB)

Comprehensive test suite.

```
tests/
├── accessibility/                 # A11y tests (Pa11y, Axe)
├── e2e/                          # End-to-end tests (Playwright)
├── integration/                   # Integration tests
├── performance/                   # Performance tests (Lighthouse)
├── unit/                         # Unit tests (Jest, PHPUnit)
├── jest.config.js                # Jest configuration
├── playwright.config.js          # Playwright configuration
└── theme-check.js                # WordPress theme checks
```

---

## Key Configuration Files

### Root Configuration Files

| File | Purpose | Size |
|------|---------|------|
| `functions.php` | Main theme initialization | 40KB |
| `theme.json` | Design system (colors, typography, spacing) | 20KB |
| `style.css` | Theme metadata (required by WordPress) | <1KB |
| `package.json` | Node.js dependencies & scripts | 4KB |
| `composer.json` | PHP dependencies | 2KB |
| `.gitignore` | Git exclusions | 1KB |
| `.distignore` | WordPress.org distribution exclusions | 1KB |
| `CLAUDE.md` | AI/Developer documentation | 25KB |
| `README.md` | Theme overview | 9KB |
| `TESTING.md` | Testing guide | 14KB |

---

## Build Outputs & Ignored Files

### Generated/Ignored Directories

These directories are **generated** and **not committed** to Git:

```
node_modules/                      # npm packages (ignored)
vendor/                            # Composer packages (ignored)
assets/dist/                       # Compiled assets (ignored)
assets/css/dist/                   # Compiled CSS (ignored)
coverage/                          # Test coverage (ignored)
.cache/                           # Build cache (ignored)
playwright-report/                 # Test reports (ignored)
test-results/                      # Test output (ignored)
```

### Local Development Only

These directories/files are local only:

```
.claude/                          # AI development tools
.vscode/                          # VS Code settings
.idea/                            # PHPStorm settings
*.code-workspace                   # VS Code workspace
```

---

## Theme Loading Order

1. **functions.php** - Theme initialization
2. **includes/core/loader.php** - Core classes
3. **includes/free/*.php** - Free modules (always loaded)
4. **includes/premium/premium-init.php** - Premium system (if license valid)
5. **blocks/registration.php** - Block registration

---

## Design System Files

The theme's design system is centralized in:

| File | Purpose |
|------|---------|
| `theme.json` | WordPress design tokens (colors, typography, spacing) |
| `assets/css/app.css` | Tailwind CSS with design token integration |
| `docs/guides/DESIGN-REFERENCE.md` | Complete style guide |

---

## Size Optimization Notes

### Current Size: ~14MB

#### Large Items:
- ✅ **5.2MB** - Heroicons library (intentional, documented)
- ✅ **2.4MB** - PHP includes (premium features are modular)
- ✅ **1.2MB** - package-lock.json (necessary for npm)
- ✅ **920KB** - screenshot.png (could be optimized)
- ✅ **880KB** - docs/ (valuable documentation)
- ✅ **692KB** - vendor/ assets (Bootstrap, Chart.js, Leaflet)
- ✅ **476KB** - tests/ (development only, excluded from distribution)

#### Distribution Exclusions

When packaged for WordPress.org or production, the following are **excluded** via `.distignore`:

- Development tools: `node_modules/`, `vendor/`, `build/`, `scripts/`, `tools/`
- Tests: `tests/`, `coverage/`
- Documentation: `docs/`, `.md` files (except README.md)
- AI tools: `.claude/`
- Git files: `.git/`, `.github/`, `.gitignore`

**Production package size**: ~3-4MB (optimized)

---

## Maintenance

### Regular Cleanup

```bash
# Clean build artifacts
npm run clean

# Remove node_modules
rm -rf node_modules/
npm install

# Remove composer vendor
rm -rf vendor/
composer install
```

### Directory Audits

- **Quarterly**: Review assets/icons/ for unused icons
- **Before releases**: Run production build script to test .distignore
- **After major updates**: Update this documentation

---

## Additional Documentation

- **Icon System**: See `assets/icons/README.md`
- **Design System**: See `docs/guides/DESIGN-REFERENCE.md`
- **Developer Guide**: See `docs/guides/DEVELOPER-GUIDE.md`
- **Architecture**: See `CLAUDE.md`
- **Cleanup Plan**: See `REPOSITORY_CLEANUP_PLAN.md`

---

**Questions?**

File issues at: https://github.com/your-org/campaignpress/issues

**Last Updated**: 2025-01-22 | **Theme Version**: 2.1.0
