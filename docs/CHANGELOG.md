# CampaignPress Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2025-12-28

### Major Release: WordPress 6.9 Design System & Documentation Consolidation

#### Added
- WordPress 6.9-native design system with centralized design tokens
- 33 color tokens (Primary, Accent, Neutral 9-shade palettes)
- 4 party color schemes (Democrat Blue, Republican Red, Independent Purple, Green Party)
- 3 distinctive font families (Bricolage Grotesque, Plus Jakarta Sans, JetBrains Mono)
- 8 fluid font sizes with automatic mobile-to-desktop scaling
- 12 spacing presets based on 8px grid system
- 6 shadow presets for depth and elevation
- `theme.json` as central design token configuration
- `assets/css/design-system-wp69.css` with enhanced WordPress CSS
- Development license testing system with mock server
- Consolidated markdown documentation (5 core files)
- Comprehensive documentation structure in `/docs/` folder

#### Changed
- Migrated to WordPress 6.9 design token system (from custom CSS)
- Updated all components to use WordPress CSS variables
- Reorganized documentation from scattered files to structured docs folder
- Improved accessibility compliance (WCAG 2.1 AA)
- Enhanced block editor integration with visual design controls
- Performance optimizations (WordPress-managed fonts, GPU-accelerated CSS)

#### Fixed
- Design token consistency across components
- Accessibility issues in navigation and forms
- Mobile responsiveness improvements
- Party color scheme switching

---

## [1.5.0] - 2025-11-15

### Premium Features Enhancement

#### Added
- Developer Console module (Basic tier)
- Database inspector with query builder
- API endpoint tester
- System health monitoring
- GDPR-compliant data export tools
- Error log viewer
- Performance profiling

#### Changed
- Improved CRM contact import performance (10x faster)
- Enhanced field operations mobile interface
- Updated analytics dashboard with new visualizations

#### Fixed
- CRM segment query builder edge cases
- Field operations territory assignment bugs
- Analytics date range selector issues

---

## [1.4.0] - 2025-10-01

### Field Operations & Analytics

#### Added
- Field Operations module (Professional tier)
  - Canvassing routes and walk lists
  - Phone banking call sheets
  - Door-to-door organizing tools
  - GOTV scheduling
  - Volunteer shift management
- Analytics Dashboard module (Professional tier)
  - Fundraising metrics
  - Volunteer engagement statistics
  - Event attendance tracking
  - Website traffic analysis

#### Changed
- CRM performance improvements for 50K+ contacts
- Updated engagement scoring algorithm
- Improved mobile responsiveness for CRM interface

---

## [1.3.0] - 2025-08-15

### CRM System Launch

#### Added
- Premium CRM module (Professional tier)
- Contact management (50,000+ capacity)
- Interaction history tracking
- Tag system for segmentation
- Dynamic segments with query builder
- Household grouping
- Custom field definitions
- Engagement scoring (0-100)
- Import/Export functionality
- 11 custom database tables

#### Changed
- Restructured premium modules for better organization
- Updated premium license system
- Improved admin interface for premium features

---

## [1.2.0] - 2025-06-01

### Premium System & Compliance

#### Added
- Premium licensing system (`premium-init.php`)
- Feature toggle management
- License tier support (Basic, Professional, Enterprise)
- FEC Compliance module (Enterprise tier)
- REST API module (Enterprise tier)
- API authentication with X-API-Key
- Webhook delivery system
- Rate limiting (1000 req/hour)

#### Changed
- Modularized premium features
- Updated activation/deactivation flows
- Improved admin menu structure

---

## [1.1.0] - 2025-04-15

### Elementor Integration & Accessibility

#### Added
- 10 Elementor widgets
- Elementor category for CampaignPress widgets
- Accessibility module (WCAG 2.1 AA compliance)
- Skip links and ARIA labels
- Keyboard navigation support
- Reduced motion support
- Screen reader optimizations

#### Changed
- Updated Gutenberg blocks for better editor experience
- Improved customizer interface
- Enhanced RTL support

#### Fixed
- Custom post type permalink issues
- Event RSVP capacity calculations
- Volunteer form validation

---

## [1.0.0] - 2025-02-01

### Initial Release

#### Added
- 5 Custom Post Types (Issues, Events, Endorsements, Team, Volunteers)
- 7 Gutenberg Blocks
- Bootstrap 5.3 integration
- Basic volunteer management
- Event management with RSVP
- Donation integration (ActBlue, WinRed, PayPal, Stripe, Square, Donorbox)
- Translation support (WPML, Polylang, TranslatePress)
- RTL language support
- WordPress Customizer integration
- Contact Form 7 integration
- The Events Calendar integration
- Mailchimp integration
- Demo content generator
- Bootstrap 5 navigation walker
- TGMPA recommended plugins

#### Theme Foundation
- `functions.php` theme initialization
- Template hierarchy
- Widget areas (sidebar, 3 footer areas)
- Navigation menus (primary, footer, social)
- Security headers
- Custom template loader

---

## [0.9.0] - 2025-01-15

### Beta Release

#### Added
- Initial theme structure
- Basic custom post types
- Simple event management
- Volunteer signup forms
- Theme customization options
- Basic security measures

#### Testing
- Alpha testing with 3 local campaigns
- Bug fixes and stability improvements

---

## Release Notes

### Version 2.0.0 Upgrade Guide

**Breaking Changes:**
- WordPress 6.9+ now required
- Old custom CSS may need updating to use new design tokens
- Party color schemes now use body classes instead of customizer-only

**Migration Steps:**
1. Backup your site
2. Update WordPress to 6.9+
3. Update theme to 2.0.0
4. Go to Appearance → Customize → Verify design settings
5. Clear all caches
6. Test party color scheme switcher

**New Features to Explore:**
- Block editor now has full design token integration
- Try the new font families in your content
- Explore 9-shade color palettes for sophisticated designs
- Check out staggered animations on hero sections

---

## Roadmap

### Planned for 2.1.0 (Q1 2026)
- Mobile app for field operations
- SMS opt-in/opt-out management
- Advanced email templates
- Social media scheduling integration

### Planned for 2.2.0 (Q2 2026)
- Peer-to-peer texting
- Relational organizing tools
- Predictive volunteer scoring
- AI-powered contact insights

### Planned for 3.0.0 (Q3 2026)
- Multilingual CRM interface
- Advanced FEC reporting
- Integration marketplace
- Campaign template library

---

## Support

For questions about this changelog or upgrade assistance:
- **Documentation:** `/docs/`
- **GitHub:** github.com/mrwalker511/campaign-office
- **Premium Support:** support@campaignpress.com

---

**Maintained by:** CampaignPress Development Team
**License:** GPLv3 or later
