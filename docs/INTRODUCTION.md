# CampaignPress Introduction

**A transformative WordPress theme for political campaigns**

---

## What is CampaignPress?

CampaignPress is a freemium WordPress theme designed specifically for political campaigns. It seamlessly transforms from a free political website builder into a comprehensive campaign management platform with CRM, field operations, compliance tracking, and analytics.

---

## Key Value Proposition

### For Small Campaigns (Free Tier)
Build a professional campaign website with:
- Policy positions and issue tracking
- Event management with RSVP
- Volunteer signup and basic tracking
- Donation integration with major platforms
- Professional design with party color schemes
- Full accessibility compliance

### For Growing Campaigns (Professional Tier)
Add sophisticated organizing tools:
- Contact database (50,000+ records)
- Field operations management
- Phone banking and canvassing tools
- Analytics and performance tracking
- Email/SMS integration
- Advanced volunteer management

### For Major Campaigns (Enterprise Tier)
Get enterprise-grade features:
- FEC compliance automation
- REST API for custom integrations
- White label options
- Dedicated support
- Custom development assistance

---

## Why CampaignPress?

### Purpose-Built for Politics
Unlike generic WordPress themes, CampaignPress understands political campaigns:
- FEC compliance built-in
- Voter data integration
- Canvassing and phone banking tools
- Endorsement management
- Issue position tracking
- Party color scheme support

### Freemium Model Benefits
- **Start Free:** No upfront costs for small campaigns
- **Grow Gradually:** Add features as your campaign grows
- **Predictable Pricing:** No hidden fees or per-user charges
- **No Vendor Lock-in:** Keep your data if you cancel

### WordPress Foundation
Built on WordPress means:
- Familiar admin interface
- Thousands of compatible plugins
- Hosting freedom (use any WordPress host)
- Active development community
- GPL open source license

---

## Who Should Use CampaignPress?

### Perfect For
- Local election candidates (city council, mayor, school board)
- State legislative campaigns
- Congressional campaigns
- Statewide campaigns (governor, senator)
- Political advocacy organizations
- Issue-based campaigns
- Party organizations

### Not Ideal For
- Personal blogs (use standard WordPress themes)
- Corporate websites (use business themes)
- E-commerce stores (use WooCommerce themes)

---

## Core Philosophy

### 1. Clear Free/Premium Boundary
Free features are genuinely useful for small campaigns. Premium features unlock professional campaign management tools without crippling the free version.

### 2. Accessibility First
WCAG 2.1 AA compliance is standard, not optional. Every voter should be able to access your campaign website.

### 3. Data Ownership
Your campaign data belongs to you. Export anytime, no lock-in.

### 4. Security by Default
Built with WordPress security best practices:
- Input sanitization and output escaping
- Prepared SQL statements
- Nonce verification
- Capability checks
- Security headers

### 5. Performance Optimized
- Modular architecture (only load what you need)
- Lazy loading for images
- Minified assets
- Database query optimization
- Caching support

---

## Design System

CampaignPress 2.0 features a **WordPress 6.9-native design system** with:

### Distinctive Typography
- **Bricolage Grotesque** - Headlines with authority
- **Plus Jakarta Sans** - Body text with warmth
- **JetBrains Mono** - Numbers and statistics

### Sophisticated Color Palettes
- 9-shade color systems (50-900)
- 33 total color tokens
- 4 party themes (Democrat Blue, Republican Red, Independent Purple, Green Party)

### Fluid Sizing
- Automatic mobile-to-desktop scaling
- 8 font sizes with CSS clamp()
- 12 spacing presets (8px grid)

### Professional Animations
- Staggered hero reveals
- Button pulse effects
- Card hover lifts
- Progress bar animations

---

## Architecture Overview

### File Structure
```
campaign-office/
├── functions.php              # Theme initialization
├── theme.json                 # Design system tokens
├── includes/
│   ├── free/                  # 18 free modules
│   └── premium/               # 9 premium modules
├── blocks/                    # Gutenberg blocks
├── templates/                 # Page templates
└── assets/                    # CSS, JS, images
```

### Technology Stack
- **Frontend:** Bootstrap 5.3, WordPress 6.9 design system
- **Build Tool:** Vite 5.0 with React plugin
- **JavaScript:** Modern ES6+, React for complex components
- **CSS:** WordPress CSS variables, custom properties
- **Database:** WordPress tables + 11 custom CRM tables
- **API:** WordPress REST API + custom endpoints

---

## Getting Started

### 1. Install WordPress 6.9+
Download from wordpress.org and install on your hosting.

### 2. Install CampaignPress
Upload theme files to `/wp-content/themes/campaign-office/`

### 3. Activate Theme
Go to Appearance → Themes → Activate CampaignPress

### 4. Configure Settings
- Appearance → Customize → Select party color scheme
- Settings → Permalinks → Save to flush rewrite rules
- CampaignPress → Theme Options → Configure site settings

### 5. Add Content
- Create issues (policy positions)
- Add events with RSVP
- Build team member profiles
- Set up donation pages
- Create volunteer signup forms

### 6. Optional: Upgrade to Premium
- Purchase license key
- Enter in CampaignPress Pro → License
- Enable desired features

---

## Next Steps

### Essential Documentation
- [Features Overview](../FEATURES.md) - Complete feature breakdown
- [Getting Started Guide](../GETTING-STARTED.md) - Detailed setup instructions
- [Developer Guide](../DEVELOPER-GUIDE.md) - Development documentation
- [Testing Guide](../TESTING.md) - Testing framework

### Design Resources
- [Design System](DESIGN_SYSTEM.md) - Design tokens and guidelines
- [Style Guide](STYLEGUIDE.md) - Writing and visual style guide

### Technical Documentation
- [Architecture](ARCHITECTURE.md) - System architecture
- [Tech Stack](TECH_STACK.md) - Technology details
- [API Documentation](../includes/premium/api/) - REST API reference (Enterprise)

---

## Support & Community

### Free Users
- Documentation at `/docs/`
- GitHub Issues: github.com/mrwalker511/campaign-office/issues
- Community forums

### Premium Users
- Priority email support: support@campaignpress.com
- Video tutorials and training
- Live chat (Professional+)
- Dedicated account manager (Enterprise)

---

## License

CampaignPress is licensed under GPLv3 or later. This means:
- Free to use, modify, and distribute
- Open source code available on GitHub
- No restrictions on commercial use
- Must maintain GPL license in derivatives

Premium features require a valid license key but the code remains GPL.

---

**Version:** 2.0.0
**Release Date:** December 2025
**Minimum WordPress:** 6.9
**License:** GPLv3 or later

---

**Ready to build your campaign website?** Continue to [Getting Started Guide](../GETTING-STARTED.md)
