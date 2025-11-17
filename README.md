# CampaignPress - Political WordPress Theme

**Version:** 2.0.0
**Requires at least:** WordPress 6.4
**Tested up to:** WordPress 6.7
**Requires PHP:** 8.1
**License:** GPLv3 or later
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html

## Description

CampaignPress is a freemium political WordPress theme that transforms into a complete campaign operations platform. The **free version** provides enhanced volunteer management, advanced event RSVPs, multi-language support, accessibility compliance, and Elementor compatibility. The **premium version** adds a political CRM, field operations (canvassing, phone banking), GOTV tools, FEC compliance automation, and campaign automation - **replacing expensive solutions like NationBuilder ($500-$1,000/mo) and NGP VAN ($1,500-$3,000/mo)**.

### Core Mission
Create an integrated campaign operations system disguised as a WordPress theme—shifting from "pretty website" to "campaign management system."

---

## What's New in Version 2.0.0

**🎉 Major Release** - CampaignPress 2.0.0 represents a complete transformation from a simple political theme into a full-featured campaign management platform.

### Free Version Enhancements
- ✅ **Enhanced Volunteer Management** with data capture, contact database, and CSV export
- ✅ **Advanced Event Management** with RSVP system, recurring events, and capacity limits
- ✅ **Multi-Language Support** (WPML, Polylang, TranslatePress compatible)
- ✅ **Accessibility Compliance** (WCAG 2.1 AA standards)
- ✅ **Elementor Page Builder** with 10 custom campaign widgets
- ✅ **Enhanced Donation Handling** (6 payment processors, recurring donations)
- ✅ **Campaign Performance Widgets** (7 real-time dashboard widgets)
- ✅ **Comprehensive SEO/Security Guide** for political campaigns

### Premium Version Features
- ✅ **Political CRM System** (50K+ contacts, engagement scoring, voter database)
- ✅ **Field Operations** (door-to-door canvassing, phone banking, offline PWA support)
- ✅ **GOTV Dashboard** (turnout tracking, voter transportation, early vote monitoring)
- ✅ **FEC Compliance** (contribution tracking, automatic limits, quarterly reports)
- ✅ **Email/SMS Automation** (Mailchimp, Twilio, Action Network, automated workflows)
- ✅ **Advanced Analytics** (campaign metrics, KPI tracking, performance dashboards)
- ✅ **REST API** (full CRUD operations, webhook support, third-party integrations)
- ✅ **Volunteer Scheduling** (shift management, check-in/out, hours tracking)

[See Full Changelog](#changelog)

---

## Features

### Free Version Features (v2.0.0)

#### Custom Gutenberg Blocks (Political-Specific)
- **Donation Button** - 6 payment processors (ActBlue, WinRed, PayPal, Stripe, Square, Donorbox)
- **Campaign Progress Meter** - Visual fundraising goal tracker
- **Issue Card** - Showcase policy positions with icons
- **Endorsement Grid** - Display endorsers with photos and quotes
- **Event Countdown** - Countdown to Election Day or events
- **Volunteer Sign-up CTA** - Enhanced call-to-action with data capture
- **Social Media Follow Buttons** - Integrated social links

#### Enhanced Volunteer Management **NEW**
- **Volunteer Database** - Capture name, email, phone, address, skills, interests, availability
- **Admin Management Interface** - Search, filter, bulk actions, status tracking (new, contacted, active)
- **CSV Export** - Export volunteer data for external tools
- **Shortcode System** - `[cp_volunteer_form]` for custom placement
- **Email Integration** - Hooks for automated volunteer communications
- **Capacity Planning** - Track volunteer recruitment goals

#### Advanced Event Management **NEW**
- **RSVP System** - Built-in RSVP forms with capacity limits
- **Recurring Events** - Daily, weekly, bi-weekly, monthly patterns
- **Event Capacity** - Set maximum capacity, track current RSVPs
- **RSVP Deadline** - Automated RSVP cutoff dates
- **Dietary Restrictions** - Optional dietary preference collection
- **RSVP Admin Dashboard** - View and export all event RSVPs
- **Shortcode** - `[cp_event_rsvp event_id="123"]` for custom RSVP forms

#### Multi-Language & Translation Support **NEW**
- **WPML Compatible** - Full WPML integration for multilingual campaigns
- **Polylang Compatible** - Complete Polylang support
- **TranslatePress Ready** - TranslatePress integration
- **Language Switcher Widget** - Dropdown or flag-based switcher
- **RTL Support** - Right-to-left language styling (Arabic, Hebrew, Persian, Urdu)
- **Shortcode** - `[cp_language_switcher]` for custom placement
- **Custom Post Type Translation** - All CPTs translatable

#### Accessibility Compliance **NEW**
- **WCAG 2.1 AA Standards** - Comprehensive accessibility features
- **Skip Links** - Keyboard navigation shortcuts
- **ARIA Labels** - Screen reader support throughout
- **Focus Management** - Visible focus indicators
- **Color Contrast Checker** - Built-in contrast validation
- **Form Label Associations** - Proper form accessibility
- **Alt Text Enforcement** - Image accessibility helpers
- **Keyboard Navigation** - Full keyboard operability

#### Elementor Page Builder Integration **NEW**
10 custom CampaignPress widgets for Elementor:
1. **Donation Button** - Full payment processor integration
2. **Campaign Progress Meter** - Fundraising goals
3. **Issue Card** - Policy position cards
4. **Endorsement Grid** - 2-4 column endorsement grids
5. **Event Countdown** - Live countdown timer
6. **Volunteer Signup CTA** - Call-to-action blocks
7. **Social Follow Buttons** - Social media integration
8. **Team Member Card** - Staff profile cards
9. **Event RSVP Form** - Inline RSVP forms
10. **Testimonial/Quote** - Quote blocks

#### Enhanced Donation System **NEW**
- **6 Payment Processors**:
  - ActBlue (Democratic campaigns)
  - WinRed (Republican campaigns)
  - PayPal (general donations)
  - Stripe (credit card processing)
  - Square (payment processing)
  - Donorbox (donation platform)
- **Quick Amount Buttons** - $25, $50, $100, $250, $500, Custom
- **Recurring Donations** - One-time, Monthly, Quarterly, Annually
- **FEC Compliance Notices** - Contribution limit warnings
- **Google Analytics Integration** - Donation event tracking
- **Shortcode** - `[cp_donation_button processor="actblue" amounts="25,50,100"]`

#### Campaign Performance Widgets **NEW**
7 real-time dashboard widgets with demo data:
1. **Fundraising Progress** - Total raised, donor count, average donation
2. **Volunteer Engagement** - Active volunteers, hours logged, new signups
3. **Event Attendance** - Upcoming events, total attendees, RSVP rate
4. **Endorsements** - Total endorsements, recent additions
5. **Social Media Reach** - Follower growth, engagement metrics
6. **Election Countdown** - Days remaining, key milestones
7. **Campaign Statistics** - Comprehensive overview dashboard

#### Design System
- 3 homepage layouts (Classic Candidate, Modern Progressive, Conservative Traditional)
- 5 color presets (Democrat Blue, Republican Red, Independent Purple, Green Party, Neutral)
- Mobile-first responsive design with Bootstrap 5.3 framework
- Hero section with optional video overlay support
- Customizer integration for easy styling
- Global theme options panel for centralized configuration

#### Integrations & Documentation
- Contact Form 7 support
- The Events Calendar integration
- MailChimp documentation
- **Comprehensive SEO Guide** (Yoast, Rank Math, All in One SEO) **NEW**
- **Security Hardening Guide** (Wordfence, Sucuri, iThemes Security) **NEW**
- **Performance Optimization** (WP Fastest Cache, WP Rocket, Cloudflare) **NEW**
- Social media optimization (Open Graph, Twitter Cards)

#### Demo Content & Quick Start
- One-click demo content importer
- Auto-generates sample pages (Home, About, Issues, Events, Team, Endorsements, Volunteer, Contact)
- Pre-configured navigation menus
- Sample posts and custom post type entries
- Reduces setup time from hours to minutes

---

### Premium Version Features (v2.0.0)

#### Political CRM System **NEW**
**Complete voter/contact database with 50K+ capacity**

**Features:**
- **Contact Database** - 50+ fields per contact (demographics, political data, location)
- **Engagement Scoring** - Algorithmic scoring based on recency, frequency, quality, response rate
- **Interaction History** - Track 11 types of interactions (calls, texts, door knocks, emails, events, donations)
- **Smart Segmentation** - Dynamic and static segments with advanced filtering
- **Tagging System** - Unlimited custom tags, system tags, bulk tagging
- **Duplicate Detection** - Automatic duplicate identification and merging
- **Household Grouping** - Group contacts by household/address
- **Custom Fields** - Flexible custom field system for voter data
- **Advanced Search** - Multi-criteria search across 20+ fields
- **Bulk Operations** - Update, delete, tag hundreds of contacts at once
- **CSV Import/Export** - L2 Political, TargetSmart, NGP VAN, Generic CSV formats
- **REST API Access** - Full CRUD operations via API

**Database Tables:**
- Contacts (50K+ optimized with 50+ indexes)
- Interactions (unlimited history)
- Tags & Segments
- Households
- Duplicate groups
- Engagement scores
- Custom fields

#### Field Operations Management **NEW**
**Complete ground game operations platform**

**Canvassing Module:**
- Walk list generator with territory cutting
- Mobile-responsive canvassing interface
- Offline data collection (PWA-ready)
- Survey question builder (multiple question types)
- GPS tracking placeholders
- Real-time sync when online
- Door knock logging (answered, not home, refused, moved)
- Results recording with timestamps
- Canvasser leaderboards
- Progress tracking and metrics

**Phone Banking Module:**
- Call list management with prioritization
- Call script builder with branching logic
- Click-to-call integration (Twilio, CallHub)
- Call disposition tracking (answered, voicemail, no answer, busy, wrong number, DNC)
- Call-back scheduling with reminders
- Call duration logging
- Performance leaderboards
- Shift scheduling
- Auto-dialer integration hooks
- Real-time dashboard

**GOTV (Get Out The Vote) Module:**
- Early vote/absentee ballot tracking
- Election Day turnout dashboard
- Voter transportation coordination (ride requests)
- Poll location lookup (Google Civic API hooks)
- Voter pledge tracking
- Turnout goals by precinct/region
- Real-time reporting
- GOTV universe management
- Public turnout widget shortcode

**Volunteer Scheduling Module:**
- Flexible shift creation (canvassing, phone banking, events, general)
- Volunteer availability tracking (recurring weekly patterns)
- Automated shift reminders (email/SMS hooks)
- Check-in/check-out system
- Hours tracking and approval workflow
- No-show management
- Recurring shift support
- Shift calendar (month view)
- Volunteer leaderboards
- Fill rate analytics

#### FEC Compliance & Reporting **NEW**
**Complete FEC compliance automation**

**Features:**
- **Contribution Tracking** - Record all donations with FEC-required fields
- **Automatic Limit Enforcement** - Individual ($3,300/election), PAC ($5,000), Party ($10,000)
- **Prohibited Source Detection** - Foreign nationals, federal contractors, corporate/union treasury
- **Itemization Automation** - Auto-itemizes contributions over $200
- **48-Hour Notices** - Automatic detection of reportable contributions
- **Quarterly Reports** - Q1, Q2, Q3, Q4 with automatic calculations
- **Pre-Election Reports** - 12-day pre-election filing
- **Post-General Reports** - 30-day post-general filing
- **FEC Form 3 Export** - CSV format compatible with FEC filing software
- **Donor Management** - Occupation/employer requirements, aggregate tracking
- **In-Kind Contributions** - Non-monetary contribution tracking
- **Refund Processing** - Complete refund workflow
- **Audit Trail** - 3+ year retention, complete transaction logging
- **Compliance Dashboard** - Real-time compliance alerts

#### Email & SMS Automation **NEW**
**Complete multi-channel campaign automation**

**Email Platform Integrations:**
- **Mailchimp** - List sync, campaigns, automation
- **Action Network** - Activist sync, email blasts, petitions
- **Constant Contact** - Contact sync, email campaigns
- **SendGrid** - Transactional and marketing emails
- **MailerLite** - Subscriber sync, automation
- **Generic SMTP** - Custom email servers

**SMS Platform Integrations:**
- **Twilio** - SMS/MMS sending, conversation tracking
- **Hustle** - Peer-to-peer texting
- **CallHub** - SMS campaigns, click-to-text
- **RumbleUp** - P2P texting platform

**Automation Workflows:**
- **14 Trigger Types** - User registered, donation, volunteer signup, event registration, tags, segments, birthdays, inactivity, email/SMS events
- **9 Action Types** - Send email, send SMS, add/remove tags, change segments, update fields, create tasks, webhooks, delays
- **5 Condition Types** - Has tag, in segment, field equals, donation amount, email status
- **Features**:
  - Email drip campaigns
  - Welcome series for new volunteers
  - Thank you emails for donations
  - Event reminder automation
  - Birthday/anniversary messages
  - Re-engagement campaigns
  - A/B testing support
  - Merge tag system

**Compliance:**
- TCPA compliance (SMS opt-in/opt-out)
- CAN-SPAM compliance (email unsubscribes)
- Automated compliance footers
- Consent tracking

#### Advanced Analytics **NEW**
**Comprehensive campaign metrics and reporting**

**Features:**
- **Dashboard Metrics** - Funds raised, volunteers, events, contacts with trends
- **Fundraising Analytics**:
  - Total raised, donation counts, averages
  - Donor retention and acquisition
  - Donations by source, amount range, date
  - Time-series fundraising timeline
  - Top donors leaderboard
  - Goal progress tracking
- **Volunteer Analytics**:
  - Recruitment and retention rates
  - Total hours and activity tracking
  - Activity by type, day, time
  - Top volunteers leaderboard
- **Event Analytics**:
  - Attendance tracking, RSVP conversion
  - Events by type and location
  - Popular events ranking
- **Engagement Analytics**:
  - Contact engagement scoring (0-100)
  - Engagement distribution and trends
  - At-risk contacts identification
- **Geographic Distribution** - Heat map data by location
- **Performance Metrics** - KPI tracking with goals and alerts
- **Export Capabilities** - CSV export, PDF placeholders

#### REST API & Integrations **NEW**
**Full REST API for third-party integrations**

**API Endpoints:**
- **Contacts** - GET, POST, PUT, DELETE with pagination, search, filtering
- **Events** - Full CRUD operations
- **Volunteers** - Create and retrieve volunteer data
- **Donations** - Webhook receiver for payment processors
- **Field Operations** - Submit canvassing and phone banking results
- **Analytics** - Retrieve campaign metrics
- **Third-Party Sync** - NationBuilder, NGP VAN, Action Network

**Features:**
- API key authentication system
- Rate limiting (configurable per key)
- Request logging
- Webhook management with retry logic
- HMAC-SHA256 signature verification
- Comprehensive documentation

---

## Installation

### Basic Installation

1. Download the CampaignPress theme package
2. In WordPress admin, go to **Appearance > Themes > Add New > Upload Theme**
3. Upload the `.zip` file and click **Install Now**
4. Click **Activate** to activate the theme
5. Go to **Appearance > Customize** to configure your theme settings

### Automatic Plugin Installation

Upon activating CampaignPress, you'll see an admin notice offering to install recommended plugins. Simply click **"Begin installing plugins"** to install all recommended plugins with one click!

**Recommended plugins include:**

**Essential Campaign Tools:**
- **Contact Form 7** - Contact forms for campaign inquiries
- **The Events Calendar** - Advanced event management
- **GiveWP** - Professional donation management

**Email & Analytics:**
- **MC4WP: Mailchimp for WordPress** - Email list building
- **MonsterInsights** - Google Analytics integration

**Optimization & Security:**
- **Yoast SEO** - Search engine optimization
- **Wordfence Security** - Security hardening
- **WP Fastest Cache** - Performance optimization
- **Really Simple SSL** - Automatic SSL/HTTPS

**Social Media:**
- **Social Warfare** - Social sharing optimization

---

## Upgrading from v1.0.0 to v2.0.0

### Before You Upgrade

**⚠️ IMPORTANT - Backup Your Site**
```bash
1. Backup your database via phpMyAdmin or backup plugin
2. Backup your /wp-content/themes/campaignpress folder
3. Export existing Issues, Events, Endorsements, Team, Volunteers
```

### Upgrade Process

**Automatic Upgrade (Recommended):**
1. WordPress will notify you of the update in **Appearance > Themes**
2. Click **Update Now**
3. The theme will automatically upgrade with no data loss

**Manual Upgrade:**
1. Download CampaignPress v2.0.0 from your account
2. Delete the old version via **Appearance > Themes**
3. Upload and activate the new version
4. Go to **Appearance > CampaignPress Options** to verify settings

### After Upgrading

**Required Steps:**
1. **Database Upgrade** - New tables will be created automatically on first load
2. **Review Settings** - Visit **Appearance > CampaignPress Options** to review new settings
3. **Test Functionality** - Test volunteer forms, event RSVPs, and donation buttons
4. **Review Campaign Widgets** - Check **Dashboard** for 7 new performance widgets
5. **Elementor Users** - Update Elementor to v3.18+ for best compatibility

**Optional Steps:**
1. Enable desired features in **Dashboard > Volunteer Signups** and **Events > RSVPs**
2. Configure multi-language support if running bilingual campaign
3. Review accessibility features at **Appearance > Accessibility Settings**
4. Read the new **SEO & Security Integration Guide** in the `/docs` folder

### Premium Upgrade

**Upgrading to Premium:**
1. Purchase CampaignPress Premium license
2. Install premium plugin/add-on from your account
3. Navigate to **CampaignPress Premium > License**
4. Enter your license key and activate
5. Enable desired premium features (CRM, Field Ops, FEC Compliance, etc.)

**License Tiers:**
- **Basic** ($99/year) - CRM (10K contacts), Analytics, API
- **Professional** ($299/year) - CRM (50K contacts), Field Ops, FEC Compliance, Automation
- **Enterprise** ($599/year) - Unlimited contacts, White Label, Priority Support, Automatic Updates

---

## Technologies

CampaignPress is built with modern web technologies for performance, security, and extensibility:

### Backend Technologies
- **PHP 8.1+** - Modern PHP with strict typing and improved performance
- **WordPress Core APIs** - Custom Post Types, Customizer API, Settings API, REST API
- **WordPress Hooks** - Actions and filters for extensibility
- **MySQL/MariaDB** - Optimized database schema with 50+ indexes

### Frontend Technologies
- **Bootstrap 5.3** - Responsive CSS framework via CDN
- **Gutenberg Block Editor** - Native WordPress block system
- **Chart.js Ready** - Data prepared for Chart.js visualizations
- **Leaflet Map Ready** - Geographic data for map rendering
- **jQuery** - Legacy support for WordPress compatibility
- **CSS3** - Custom properties and modern CSS features

### Build Tools
- **Vite 5.0** - Lightning-fast build system with hot module replacement
- **Node.js 18+** - JavaScript runtime for build tools
- **npm** - Package manager
- **ESLint** - JavaScript code quality
- **Prettier** - Automatic code formatting

### WordPress Integration
- **TGM Plugin Activation** - One-click plugin installation
- **Theme Customizer API** - Live preview customization
- **Settings API** - Admin options panel
- **REST API** - Full REST endpoints
- **WP-Cron** - Scheduled task automation

### Security & Performance
- **Escaping Functions** - All output properly escaped (esc_html, esc_attr, esc_url)
- **Nonce Verification** - CSRF protection on all forms
- **Prepared Statements** - SQL injection prevention
- **Security Headers** - X-Content-Type-Options, X-Frame-Options, X-XSS-Protection
- **Lazy Loading** - Optimized asset loading
- **Database Optimization** - 50+ indexes for query performance
- **Transient Caching** - Cached queries for performance

---

## Development

### Requirements

- Node.js 18+ and npm
- PHP 8.1+
- WordPress 6.4+
- MySQL 5.7+ or MariaDB 10.3+

### Local Development Setup

1. Clone the repository into your WordPress themes directory:
   ```bash
   cd wp-content/themes
   git clone https://github.com/yourusername/campaignpress.git
   cd campaignpress
   ```

2. Install dependencies:
   ```bash
   npm install
   ```

3. Start the development server:
   ```bash
   npm run dev
   ```

4. Build for production:
   ```bash
   npm run build
   ```

### File Structure

```
campaignpress/
├── Configuration Files
│   ├── style.css                    # Theme header and base styles
│   ├── functions.php                # Main theme setup (374 lines)
│   ├── vite.config.js               # Build configuration
│   ├── package.json                 # Node.js dependencies
│   └── .gitignore                   # Git ignore rules
│
├── Template Files
│   ├── header.php                   # Main header with Bootstrap navbar
│   ├── footer.php                   # Main footer
│   ├── front-page.php               # Homepage with hero section
│   ├── index.php                    # Main template fallback
│   ├── single.php                   # Single post template
│   ├── page.php                     # Single page template
│   ├── archive.php                  # Archive pages
│   ├── 404.php                      # 404 error page
│   └── sidebar.php                  # Sidebar widget area
│
├── includes/
│   ├── free/
│   │   ├── custom-post-types.php        # CPT registration (490 lines)
│   │   ├── gutenberg-blocks.php         # Block registration (417 lines)
│   │   ├── volunteer-management.php     # Volunteer system (NEW - 860 lines)
│   │   ├── event-management.php         # Event RSVP system (NEW - 640 lines)
│   │   ├── accessibility.php            # WCAG compliance (NEW - 745 lines)
│   │   ├── campaign-widgets.php         # Dashboard widgets (NEW - 1,230 lines)
│   │   ├── translation-support.php      # Multi-language (NEW - 480 lines)
│   │   ├── donation-enhancements.php    # Enhanced donations (NEW - 1,138 lines)
│   │   ├── elementor-widgets.php        # Elementor integration (NEW - 1,351 lines)
│   │   ├── customizer.php               # Theme customizer (311 lines)
│   │   ├── template-functions.php       # Template helpers (468 lines)
│   │   ├── integrations.php             # Plugin integrations (164 lines)
│   │   ├── admin-theme-options.php      # Options panel (870 lines)
│   │   └── demo-content.php             # Demo importer (1,779 lines)
│   │
│   └── premium/
│       ├── premium-init.php             # Premium activation (NEW - 1,184 lines)
│       ├── crm/
│       │   ├── crm-init.php             # CRM initialization (773 lines)
│       │   ├── class-crm-database.php   # Database schema (669 lines)
│       │   ├── class-crm-contacts.php   # Contact management (1,084 lines)
│       │   ├── class-crm-interactions.php   # Interaction history (841 lines)
│       │   ├── class-crm-segments.php   # Segmentation (1,004 lines)
│       │   └── class-crm-import-export.php  # CSV import/export (855 lines)
│       ├── field-operations/
│       │   ├── field-ops-init.php       # Field ops coordinator (650 lines)
│       │   ├── class-canvassing.php     # Canvassing module (1,420 lines)
│       │   ├── class-phone-banking.php  # Phone banking (1,520 lines)
│       │   ├── class-gotv.php           # GOTV module (1,375 lines)
│       │   └── class-volunteer-scheduling.php   # Scheduling (1,250 lines)
│       ├── compliance/
│       │   ├── compliance-init.php      # FEC compliance init (868 lines)
│       │   ├── class-fec-donors.php     # Donor management (739 lines)
│       │   ├── class-fec-contributions.php  # Contribution tracking (926 lines)
│       │   ├── class-fec-reports.php    # FEC reporting (903 lines)
│       │   └── class-fec-audit-trail.php    # Audit logging (787 lines)
│       ├── analytics/
│       │   ├── analytics-init.php       # Analytics init (320 lines)
│       │   ├── class-campaign-analytics.php # Campaign metrics (1,425 lines)
│       │   └── class-performance-metrics.php    # KPI tracking (625 lines)
│       ├── api/
│       │   ├── api-init.php             # REST API init (600 lines)
│       │   ├── class-api-endpoints.php  # API endpoints (1,000 lines)
│       │   └── class-api-webhooks.php   # Webhook system (625 lines)
│       └── integrations/
│           ├── integrations-init.php    # Integration coordinator (796 lines)
│           ├── class-email-integrations.php # Email platforms (1,402 lines)
│           ├── class-sms-integrations.php   # SMS platforms (1,404 lines)
│           └── class-automation-workflows.php   # Automation (1,465 lines)
│
├── assets/
│   ├── css/
│   │   ├── main.css                     # Main theme styles
│   │   ├── blocks.css                   # Block editor styles
│   │   ├── volunteer-admin.css          # Volunteer admin (NEW)
│   │   ├── elementor-widgets.css        # Elementor styles (NEW - 682 lines)
│   │   ├── premium-admin.css            # Premium admin (NEW - 585 lines)
│   │   └── rtl.css                      # RTL language support (NEW)
│   └── js/
│       ├── main.js                      # Frontend JavaScript
│       ├── blocks.js                    # Block registration
│       ├── volunteer-admin.js           # Volunteer admin (NEW)
│       └── premium-admin.js             # Premium admin (NEW - 579 lines)
│
└── docs/
    ├── README.md                        # This file
    ├── CHANGELOG.md                     # Version history
    ├── SEO-SECURITY-INTEGRATION.md      # SEO/Security guide (NEW - 12,500 words)
    ├── DEMO-CONTENT.md                  # Demo content guide
    ├── OPTIMIZATION-REPORT.md           # Performance analysis
    └── SECURITY-PREVENTION-GUIDE.md     # Security documentation
```

**Total Codebase Statistics:**
- **Free Version:** ~12,000 lines of PHP (up from 5,261)
- **Premium Version:** ~22,000 lines of PHP
- **Total:** ~34,000 lines of production code
- **50+ Database tables** (11 free, 40+ premium)
- **100+ REST API endpoints**
- **7 Dashboard widgets**
- **10 Elementor widgets**

---

## Support

- **Documentation:** [https://campaignpress.org/docs](https://campaignpress.org/docs)
- **Community Forum:** [https://campaignpress.org/forum](https://campaignpress.org/forum)
- **Issue Tracker:** [GitHub Issues](https://github.com/yourusername/campaignpress/issues)
- **Premium Support:** support@campaignpress.org (Premium license holders)

---

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## License

CampaignPress is licensed under the GNU General Public License v3 or later.

```
CampaignPress WordPress Theme
Copyright (C) 2024 CampaignPress Team

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## Credits

- Built with [WordPress](https://wordpress.org/)
- Bootstrap 5.3 by Twitter
- Build system: [Vite](https://vitejs.dev/)
- Icons: [Dashicons](https://developer.wordpress.org/resource/dashicons/)
- Plugin installation powered by [TGM Plugin Activation](http://tgmpluginactivation.com/)

---

## Changelog

[See CHANGELOG.md for detailed version history](CHANGELOG.md)

### 2.0.0 - 2024-11-17

**Major Release - Complete Campaign Management Platform**

#### Free Version Enhancements
- ✅ Enhanced volunteer management with database, admin UI, CSV export
- ✅ Advanced event management with RSVP system and recurring events
- ✅ Multi-language support (WPML, Polylang, TranslatePress)
- ✅ WCAG 2.1 AA accessibility compliance
- ✅ Elementor page builder with 10 custom widgets
- ✅ Enhanced donations supporting 6 payment processors
- ✅ 7 real-time campaign performance dashboard widgets
- ✅ Comprehensive SEO and security integration guide

#### Premium Version Features
- ✅ Political CRM (50K+ contacts, engagement scoring, voter database)
- ✅ Field operations (canvassing, phone banking, offline PWA)
- ✅ GOTV dashboard (turnout tracking, voter transportation)
- ✅ FEC compliance (contribution tracking, quarterly reports)
- ✅ Email/SMS automation (Mailchimp, Twilio, workflow builder)
- ✅ Advanced analytics (campaign metrics, KPI tracking)
- ✅ REST API (full CRUD, webhooks, third-party integrations)
- ✅ Volunteer scheduling (shift management, hours tracking)

**Full Details:** [See CHANGELOG.md](CHANGELOG.md)

### 1.0.0 - 2024-11-17
- Initial release
- 7 custom Gutenberg blocks
- 5 custom post types
- Hero section with video overlay
- Global theme options panel
- Demo content importer
- WordPress 6.4+ compatibility

---

**Built for campaigns, by campaign veterans.** 🗳️

Make your campaign website work as hard as your field team.
