# CampaignPress Technology Stack

**Version:** 2.0.0 | **Last Updated:** December 28, 2025

---

## Overview

CampaignPress is built on a modern, performance-oriented technology stack that leverages WordPress 6.9's capabilities while maintaining backward compatibility and extensibility.

---

## Core Technologies

### WordPress 6.9+
**Role:** Content Management System & Application Framework

**Why WordPress:**
- 40% of websites use WordPress
- Massive developer ecosystem
- Strong plugin/theme architecture
- Built-in user management
- Robust security model
- Active development community

**WordPress Features Used:**
- Custom Post Types & Taxonomies
- Block Editor (Gutenberg)
- REST API
- Customizer API
- Widget System
- Template Hierarchy
- Hooks & Filters
- Database Abstraction Layer ($wpdb)
- User Capabilities
- Internationalization (i18n)

---

## Frontend Stack

### HTML5
**Standards Compliance:**
- Semantic HTML elements
- ARIA attributes for accessibility
- Microdata for SEO
- Valid W3C markup

### CSS

**Frameworks:**
- **Bootstrap 5.3** - Responsive grid, components
- **WordPress 6.9 Design System** - Design tokens via theme.json
- **Custom CSS** - Theme-specific styles

**CSS Architecture:**
```
assets/css/
├── main.css                    # Primary theme styles
├── design-system-wp69.css      # WordPress 6.9 design tokens
├── blocks.css                  # Gutenberg block styles
├── elementor-widgets.css       # Elementor widget styles
├── admin-options.css           # Admin interface styles
├── premium-admin.css           # Premium admin styles
└── rtl.css                     # Right-to-left language support
```

**CSS Features:**
- CSS Custom Properties (CSS Variables)
- CSS Grid & Flexbox
- Media Queries (responsive)
- CSS Animations
- clamp() for fluid typography
- GPU-accelerated transforms

**WordPress CSS Variables:**
```css
var(--wp--preset--color--primary)
var(--wp--preset--font-family--display)
var(--wp--preset--font-size--2-xl)
var(--wp--preset--spacing--8)
var(--wp--preset--shadow--lg)
```

### JavaScript

**ES6+ Features:**
- Arrow functions
- Template literals
- Destructuring
- Async/await
- Promises
- Modules (import/export)
- Spread operator
- Optional chaining

**Libraries:**
- **jQuery 3.7** (included with WordPress)
- **Bootstrap 5.3 JS** - Interactive components
- **Chart.js** - Analytics visualizations
- **React 18** - Complex UI components (CRM, blocks)

**JavaScript Architecture:**
```
assets/js/
├── main.js                     # Primary theme scripts
├── blocks.js                   # Gutenberg block editor
├── customizer.js               # Customizer live preview
├── admin-options.js            # Admin interface
├── volunteer-admin.js          # Volunteer management
└── premium-admin.js            # Premium feature admin
```

### React (Optional Components)

**Used For:**
- Gutenberg block editor components
- CRM interface
- Complex admin dashboards
- Real-time data updates

**React Stack:**
- React 18.2
- React DOM
- JSX syntax
- Hooks (useState, useEffect, etc.)
- Context API for state management

**React Architecture:**
```
assets/react/
├── blocks/                     # Gutenberg blocks
│   ├── index.jsx
│   ├── DonationButton.jsx
│   ├── ProgressMeter.jsx
│   └── ...
├── crm/                        # CRM interface
│   ├── index.jsx
│   ├── ContactList.jsx
│   ├── ContactForm.jsx
│   └── ...
└── components/                 # Shared components
    ├── Button.jsx
    ├── Input.jsx
    └── ...
```

---

## Build Tools

### Vite 5.0
**Role:** Frontend build tool & development server

**Features:**
- Hot Module Replacement (HMR)
- Fast cold starts
- Optimized production builds
- Code splitting
- Tree shaking
- Asset handling

**Configuration:** `vite.config.js`
```javascript
export default {
  build: {
    rollupOptions: {
      input: {
        blocks: 'assets/react/blocks/index.jsx',
        crm: 'assets/react/crm/index.jsx',
        main: 'assets/js/main.js'
      },
      output: {
        dir: 'assets/dist',
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: 'css/[name].[ext]'
      }
    }
  }
}
```

### npm
**Role:** Package manager

**Key Dependencies:**
```json
{
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "bootstrap": "^5.3.0",
    "chart.js": "^4.4.0"
  },
  "devDependencies": {
    "@vitejs/plugin-react": "^4.0.0",
    "vite": "^5.0.0",
    "eslint": "^8.50.0",
    "prettier": "^3.0.0"
  }
}
```

**Scripts:**
```bash
npm run dev          # Start dev server with HMR
npm run build        # Production build
npm run watch        # Auto-rebuild on changes
npm run lint         # ESLint code check
npm run format       # Prettier code formatting
```

---

## Backend Stack

### PHP 8.1+
**Minimum:** PHP 7.4 | **Recommended:** PHP 8.1+

**PHP Features Used:**
- Object-Oriented Programming (classes, interfaces, traits)
- Namespaces
- Type declarations
- Anonymous functions (closures)
- Array functions
- PDO/MySQLi (via WordPress $wpdb)

**PHP Extensions Required:**
- mysqli or pdo_mysql
- json
- mbstring
- curl
- gd or imagick (image processing)
- zip (for backups/imports)
- xml (for feeds)

### WordPress Database Abstraction ($wpdb)

**Usage:**
```php
global $wpdb;

// Prepared statements
$results = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}cp_contacts WHERE email = %s",
    $email
));

// Insert
$wpdb->insert(
    $wpdb->prefix . 'cp_contacts',
    array('email' => $email, 'first_name' => $first_name),
    array('%s', '%s')
);

// Update
$wpdb->update(
    $wpdb->prefix . 'cp_contacts',
    array('engagement_score' => $score),
    array('id' => $id),
    array('%d'),
    array('%d')
);
```

---

## Database

### MySQL 8.0 / MariaDB 10.5+
**Minimum:** MySQL 5.7 | **Recommended:** MySQL 8.0+

**Features Used:**
- InnoDB storage engine
- ACID compliance
- Foreign keys
- Indexes (B-tree, fulltext)
- utf8mb4 character set
- JSON column type (MySQL 5.7+)
- Prepared statements

**Database Schema:**
- **Standard WordPress tables:** 12 tables
- **Custom CRM tables:** 11 tables
- **Total tables:** 23 tables

**Optimization:**
- Indexes on frequently queried columns
- Query optimization with EXPLAIN
- Connection pooling
- Prepared statement caching

---

## Third-Party Integrations

### Payment Processors
- **ActBlue** - Democratic campaign donations
- **WinRed** - Republican campaign donations
- **PayPal** - General donations
- **Stripe** - Credit card processing
- **Square** - Point of sale
- **Donorbox** - Donation platform

### Email/SMS Services
- **Mailchimp** - Email marketing
- **Twilio** - SMS messaging
- **SendGrid** - Transactional email
- **Constant Contact** - Email campaigns

### Campaign Tools
- **NGP VAN** - Voter database (API)
- **Action Network** - Organizing platform
- **Google Analytics** - Website analytics
- **Facebook Pixel** - Ad tracking

### WordPress Plugins
- **Contact Form 7** - Form builder
- **The Events Calendar** - Event management
- **WPML** - Multilingual support
- **Polylang** - Translation
- **Elementor** - Page builder
- **Yoast SEO** - Search optimization

---

## Development Tools

### Version Control
- **Git** - Source control
- **GitHub** - Repository hosting
- **.gitignore** - Excludes node_modules, vendor, etc.

### Code Quality

**ESLint** - JavaScript linting
```json
{
  "extends": ["eslint:recommended", "plugin:react/recommended"],
  "rules": {
    "no-console": "warn",
    "semi": ["error", "always"]
  }
}
```

**Prettier** - Code formatting
```json
{
  "semi": true,
  "singleQuote": true,
  "tabWidth": 2,
  "printWidth": 100
}
```

**PHP_CodeSniffer** - PHP coding standards
```bash
phpcs --standard=WordPress includes/
```

### Testing

**PHPUnit** - PHP unit testing
```bash
vendor/bin/phpunit tests/
```

**Jest** - JavaScript testing (planned)

---

## Performance Stack

### Caching

**Object Cache:**
- WordPress transient API
- Redis (recommended)
- Memcached (alternative)

**Page Cache:**
- WP Super Cache
- W3 Total Cache
- LiteSpeed Cache
- Server-level caching (Nginx FastCGI)

**Browser Cache:**
- HTTP cache headers
- ETags
- Service workers (planned)

### CDN
- CloudFlare
- StackPath
- KeyCDN
- BunnyCDN

**CDN Usage:**
```php
// Bootstrap from CDN
wp_enqueue_style('bootstrap',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css'
);
```

### Asset Optimization
- Minification (CSS, JS)
- Gzip compression
- Image optimization
- Lazy loading
- Code splitting

---

## Security Stack

### WordPress Security Features
- Nonce verification
- Capability checks
- Prepared statements
- Input sanitization
- Output escaping
- CSRF protection
- XSS prevention

### Security Headers
```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
```

### SSL/TLS
- HTTPS required for production
- Let's Encrypt for free certificates
- TLS 1.2+ minimum

### Authentication
- WordPress built-in authentication
- Custom X-API-Key for REST API (Enterprise)
- Rate limiting for API endpoints

---

## Hosting Requirements

### Recommended Hosting Stack

**Web Server:**
- Nginx 1.20+ (recommended)
- Apache 2.4+ with mod_rewrite

**PHP:**
- PHP-FPM 8.1+
- OPcache enabled
- 512MB memory limit

**Database:**
- MySQL 8.0+ or MariaDB 10.5+
- Dedicated database server for large sites

**Operating System:**
- Ubuntu 22.04 LTS
- Debian 11
- CentOS 8+

**Additional:**
- Redis for object caching
- Supervisor for background jobs
- Certbot for SSL certificates

### Managed WordPress Hosting
Compatible with:
- WP Engine
- Kinsta
- Flywheel
- SiteGround
- Cloudways

---

## Development Environment

### Local Development

**Options:**
- Local by Flywheel
- XAMPP
- MAMP
- Docker (WordPress official image)
- Valet (macOS)

**Requirements:**
- WordPress 6.9+
- PHP 8.1+
- MySQL 8.0+
- Node.js 18+
- npm 9+

**Setup:**
```bash
# Install dependencies
npm install
composer install

# Start dev server
npm run dev

# Build for production
npm run build
```

---

## Deployment Stack

### CI/CD (Optional)

**GitHub Actions:**
```yaml
name: Deploy
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - run: npm install && npm run build
      - run: rsync -avz . user@server:/path/to/wp-content/themes/campaign-office/
```

### Deployment Tools
- Git-based deployment
- SFTP/SSH
- WP-CLI for database migrations
- Composer for PHP dependencies

---

## Monitoring & Analytics

### Built-In Tools
- WordPress Debug Log
- Developer Console (CampaignPress Premium)
- System Health Checks

### External Tools
- Google Analytics
- New Relic (APM)
- Sentry (error tracking)
- UptimeRobot (uptime monitoring)

---

## Future Stack Considerations

### Planned Technologies
- **TypeScript** - Type-safe JavaScript
- **Jest** - JavaScript testing
- **Cypress** - End-to-end testing
- **GraphQL** - Alternative to REST API
- **Service Workers** - Offline support
- **WebSockets** - Real-time updates

---

## Technology Decision Rationale

### Why WordPress?
- **Ubiquity** - 40% market share, familiar to users
- **Ecosystem** - Thousands of plugins, themes, developers
- **Extensibility** - Hook-based architecture
- **Maturity** - 20+ years of development

### Why Bootstrap?
- **Speed** - Fast development
- **Responsive** - Mobile-first grid
- **Components** - Pre-built UI elements
- **Familiar** - Widely known framework

### Why React?
- **Component Model** - Reusable UI components
- **Performance** - Virtual DOM
- **Ecosystem** - Large library of packages
- **Developer Experience** - Hot module replacement, dev tools

### Why Vite?
- **Speed** - Faster than Webpack
- **Modern** - ESM-based
- **Simple** - Less configuration
- **DX** - Great developer experience

---

## Conclusion

CampaignPress uses a modern, performance-oriented tech stack that balances:
- **Familiarity** (WordPress, Bootstrap)
- **Performance** (Vite, React, caching)
- **Developer Experience** (HMR, linting, formatting)
- **Security** (prepared statements, sanitization, headers)
- **Scalability** (database optimization, CDN support)

---

**For More Information:**
- [Architecture Overview](ARCHITECTURE.md)
- [Development Workflow](WORKFLOW.md)
- [Developer Guide](../DEVELOPER-GUIDE.md)
