# CampaignPress Features

**Version:** 2.0.0 | **Status:** Freemium Political WordPress Theme

## Overview

CampaignPress is a comprehensive WordPress theme that transforms from a free political website builder into a full-featured campaign management platform. This document outlines all features available in the free version and premium tiers.

---

## Free Features

### Custom Post Types
- **Issues** - Policy positions and platform statements
- **Events** - Campaign events with RSVP system
- **Endorsements** - Supporter and organization endorsements
- **Team Members** - Campaign staff and leadership profiles
- **Volunteers** - Basic volunteer tracking and management

### Gutenberg Blocks (7 blocks)
1. **Donation Button** - Integration with ActBlue, WinRed, PayPal, Stripe, Square, Donorbox
2. **Progress Meter** - Animated fundraising goal tracker
3. **Issue Card** - Policy position display cards
4. **Endorsement Grid** - Grid layout for endorsements
5. **Event Countdown** - Live countdown timer for events
6. **Volunteer CTA** - Call-to-action with volunteer form
7. **Social Buttons** - Social media follow buttons

### Elementor Widgets (10 widgets)
- All 7 Gutenberg blocks available as Elementor widgets
- Team Member widget
- Event RSVP widget
- Testimonial widget

### Design System (WordPress 6.9+)
- **33 Color Tokens** - 9-shade palettes for Primary, Accent, Neutral
- **4 Party Color Schemes** - Democrat Blue, Republican Red, Independent Purple, Green Party
- **3 Font Families** - Bricolage Grotesque, Plus Jakarta Sans, JetBrains Mono
- **8 Fluid Font Sizes** - Automatic mobile-to-desktop scaling
- **12 Spacing Presets** - 8px grid system (4px to 96px)
- **6 Shadow Presets** - Consistent depth and elevation

### Volunteer Management
- Volunteer registration forms
- Basic volunteer database
- CSV export functionality
- Volunteer role assignment

### Event Management
- RSVP system with capacity limits
- Recurring events support
- Event calendar integration
- Email confirmations

### Donation Integration
- ActBlue integration
- WinRed integration
- PayPal support
- Stripe support
- Square support
- Donorbox support

### Accessibility
- WCAG 2.1 AA compliant
- Skip links and ARIA labels
- Keyboard navigation support
- Reduced motion support
- Screen reader optimizations

### Translation & RTL Support
- WPML compatible
- Polylang compatible
- TranslatePress compatible
- RTL (Right-to-Left) language support
- Translation-ready with .pot files

### Page Builder Integration
- Elementor compatibility
- Contact Form 7 integration
- The Events Calendar integration
- Mailchimp integration

### Customization
- WordPress Customizer integration
- Color scheme switcher
- Layout customization options
- Widget areas (sidebar, 3 footer areas)
- Custom navigation menus

### Demo Content
- Demo content generator
- Sample pages and posts
- Example configurations

---

## Premium Features

### License Tiers

#### Basic ($99/year)
- Automatic theme updates
- Developer Console access
- Priority email support

#### Professional ($299/year)
- Everything in Basic, plus:
- CRM system (50,000+ contacts)
- Field Operations tools
- Analytics dashboard
- Email/SMS integrations
- Priority support with 24-hour response

#### Enterprise ($999/year)
- Everything in Professional, plus:
- FEC Compliance module
- REST API access
- Webhook integrations
- White label options
- Dedicated account manager
- Custom development support

---

### Premium Modules

#### CRM System (Professional+)
**Database Capacity:** 50,000+ contacts

**Features:**
- Contact management with 50+ fields
- Voter data integration (voter ID, district, registration status)
- Engagement scoring (0-100 scale)
- Interaction history tracking (calls, texts, emails, door knocks)
- Tag system for contact segmentation
- Dynamic segments with query builder
- Household grouping
- Custom field definitions
- Advanced search and filtering
- Bulk operations (import, export, update)
- GDPR-compliant data export

**Database Tables:**
- Contacts (main database)
- Interactions (history log)
- Tags and contact-tag relationships
- Segments and segment-contact relationships
- Households and contact-household relationships
- Custom fields and values
- Engagement scores

#### Field Operations (Professional+)
**Tools for on-the-ground organizing**

**Features:**
- Canvassing routes and walk lists
- Phone banking call sheets
- Door-to-door organizing tools
- GOTV (Get Out The Vote) scheduling
- Volunteer shift management
- Territory assignment
- Real-time progress tracking
- Mobile-friendly interface

#### Analytics Dashboard (Professional+)
**Data-driven campaign insights**

**Features:**
- Fundraising metrics and trends
- Volunteer engagement statistics
- Event attendance tracking
- Website traffic analysis
- Email campaign performance
- Social media metrics
- Custom KPI dashboards
- Exportable reports

#### Compliance Module (Enterprise)
**FEC compliance and reporting**

**Features:**
- Automatic contribution tracking
- FEC reporting automation
- Contribution limit monitoring
- Donor verification
- Compliance alerts
- Audit trail logging
- Report generation (Schedule A, Schedule B)

#### REST API (Enterprise)
**Programmatic access to campaign data**

**Endpoints:**
- `/wp-json/campaignpress/v1/contacts`
- `/wp-json/campaignpress/v1/interactions`
- `/wp-json/campaignpress/v1/segments`
- `/wp-json/campaignpress/v1/events`

**Features:**
- X-API-Key authentication
- Rate limiting (1000 req/hour)
- Webhook delivery
- Request/response logging
- Comprehensive API documentation

#### Integrations (Professional+)
**Third-party service connections**

**Supported Services:**
- Mailchimp (email campaigns)
- Twilio (SMS messaging)
- Constant Contact
- SendGrid
- Action Network
- NGP VAN

**Features:**
- Two-way data sync
- Automated workflows
- Trigger-based actions
- Custom mapping

#### Developer Console (Basic+)
**Tools for developers and administrators**

**Features:**
- Database inspector with query builder
- System health monitoring
- API endpoint tester
- Error log viewer
- Performance profiling
- Data export tools
- Cache management
- Debug mode controls

---

## Feature Comparison Table

| Feature | Free | Basic | Professional | Enterprise |
|---------|------|-------|--------------|------------|
| **Custom Post Types** | 5 | 5 | 5 | 5 |
| **Gutenberg Blocks** | 7 | 7 | 7 | 7 |
| **Elementor Widgets** | 10 | 10 | 10 | 10 |
| **Design System** | ✓ | ✓ | ✓ | ✓ |
| **Party Color Schemes** | 4 | 4 | 4 | 4 |
| **Volunteer Management** | Basic | Basic | Advanced | Advanced |
| **Event Management** | ✓ | ✓ | ✓ | ✓ |
| **Donation Integration** | ✓ | ✓ | ✓ | ✓ |
| **Accessibility (WCAG 2.1 AA)** | ✓ | ✓ | ✓ | ✓ |
| **Translation Support** | ✓ | ✓ | ✓ | ✓ |
| **Auto Updates** | - | ✓ | ✓ | ✓ |
| **Developer Console** | - | ✓ | ✓ | ✓ |
| **CRM System** | - | - | 50K contacts | 50K contacts |
| **Field Operations** | - | - | ✓ | ✓ |
| **Analytics Dashboard** | - | - | ✓ | ✓ |
| **Email/SMS Integrations** | - | - | ✓ | ✓ |
| **FEC Compliance** | - | - | - | ✓ |
| **REST API** | - | - | - | ✓ |
| **White Label** | - | - | - | ✓ |
| **Support** | Community | Email | Priority (24h) | Dedicated |
| **Price** | Free | $99/year | $299/year | $999/year |

---

## Technical Requirements

### Minimum Requirements
- **WordPress:** 6.9 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.7 or higher (8.0 recommended)
- **Memory:** 128MB (256MB recommended for premium features)

### Recommended Requirements
- **WordPress:** Latest version
- **PHP:** 8.1 or higher
- **MySQL:** 8.0 or higher
- **Memory:** 512MB for optimal CRM performance
- **HTTPS:** Required for production sites

### Browser Support
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

---

## Roadmap

### Planned Features
- Mobile app for field operations
- SMS opt-in/opt-out management
- Advanced email templates
- Social media scheduling
- Peer-to-peer texting
- Relational organizing tools
- Predictive volunteer scoring
- AI-powered contact insights

---

## Support & Documentation

### Free Users
- [Documentation](docs/)
- [GitHub Issues](https://github.com/mrwalker511/campaign-office/issues)
- Community forums

### Premium Users
- Priority email support
- Video tutorials
- Live chat (Professional+)
- Dedicated account manager (Enterprise)
- Custom development consultation (Enterprise)

---

**Last Updated:** December 28, 2025
**Theme Version:** 2.0.0
**License:** GPLv3 or later
