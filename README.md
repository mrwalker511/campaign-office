# CampaignPress - Political WordPress Theme

**Version:** 1.0.0
**Requires at least:** WordPress 6.4
**Tested up to:** WordPress 6.7
**Requires PHP:** 8.1
**License:** GPLv3 or later
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html

## Description

CampaignPress is a freemium political WordPress theme that transforms into a complete campaign operations platform. The free version provides custom Gutenberg blocks, event management, and donation integration. The premium version adds political CRM, field operations (canvassing, phone banking), GOTV tools, and FEC compliance automation - replacing expensive solutions like NationBuilder ($500-$1,000/mo) and NGP VAN ($1,500-$3,000/mo).

### Core Mission
Create an integrated campaign operations system disguised as a WordPress theme—shifting from "pretty website" to "campaign management system."

## Features

### Free Version Features

#### Custom Gutenberg Blocks (Political-Specific)
- **Donation Button** - Link to external processors (ActBlue, WinRed)
- **Campaign Progress Meter** - Visual fundraising goal tracker
- **Issue Card** - Showcase policy positions
- **Endorsement Grid** - Display endorsers with photos and quotes
- **Event Countdown** - Countdown to Election Day or events
- **Volunteer Sign-up CTA** - Call-to-action for volunteer recruitment
- **Social Media Follow Buttons** - Integrated social links

#### Custom Post Types
- **Issues** - Policy positions with categories (Healthcare, Education, Economy, etc.)
- **Events** - Campaign events with date, time, location, RSVP
- **Endorsements** - Endorser profiles with photos and testimonials
- **Team Members** - Campaign staff with roles and bios
- **Volunteer Opportunities** - Volunteer positions and requirements

#### Design System
- 3 homepage layouts (Classic Candidate, Modern Progressive, Conservative Traditional)
- 5 color presets (Democrat Blue, Republican Red, Independent Purple, Green Party, Neutral)
- Mobile-first responsive design
- Customizer integration for easy styling

#### Integrations
- Contact Form 7 support
- The Events Calendar integration
- MailChimp documentation
- ActBlue/WinRed setup guides
- Social media optimization (Open Graph, Twitter Cards)

### Premium Version Features (Coming Soon)

#### Political CRM
- Voter database management (50K+ contacts)
- Engagement scoring algorithm
- Contact interaction history
- CSV import/export (L2, TargetSmart, state files)
- Smart segmentation
- Duplicate detection and merging

#### Field Operations
- **Canvassing Tools**
  - Walk list generator with territory cutting
  - Mobile-responsive interface with offline mode (PWA)
  - GPS tracking and real-time sync
  - Survey question recording

- **Phone Banking**
  - Call list management with prioritization
  - Click-to-call integration
  - Call scripts with branching logic
  - Performance analytics and leaderboards

- **GOTV Features**
  - Early vote/absentee tracking
  - Election Day turnout dashboard
  - Voter transportation coordination

#### Compliance & Automation
- FEC contribution tracking
- Automatic limit monitoring
- Prohibited source detection
- Quarterly report generation
- Audit trail for financial transactions

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
- **The Events Calendar** - Advanced event management for rallies, town halls, and fundraisers
- **GiveWP** - Professional donation management with FEC-ready reporting

**Email & Analytics:**
- **MC4WP: Mailchimp for WordPress** - Email list building and newsletter management
- **MonsterInsights** - Google Analytics integration for campaign tracking

**Optimization & Security:**
- **Yoast SEO** - Search engine optimization for better visibility
- **Wordfence Security** - Security hardening for campaign websites
- **WP Fastest Cache** - Performance optimization for faster page loads
- **Really Simple SSL** - Automatic SSL/HTTPS enforcement

**Social Media:**
- **Social Warfare** - Social sharing optimization for viral content spread

All plugins are optional and can be installed individually or in bulk. You can dismiss the notice if you prefer to manage plugins manually.

### First-Time Setup

1. **Configure Theme Settings**
   - Go to **Appearance > Customize**
   - Set your color scheme under **Color Scheme**
   - Add candidate name and office seeking under **Campaign Information**
   - Add your donation URL (ActBlue, WinRed, etc.)
   - Add social media URLs under **Social Media Links**

2. **Create Your Homepage**
   - Create a new page called "Home"
   - Add CampaignPress blocks (Donation Button, Campaign Progress, etc.)
   - Go to **Settings > Reading** and set this page as your homepage

3. **Set Up Navigation**
   - Go to **Appearance > Menus**
   - Create a new menu and assign it to "Primary Menu"
   - Add pages like About, Issues, Events, Volunteer, Contact

4. **Add Content**
   - Add your campaign issues under **Issues**
   - Add upcoming events under **Events**
   - Add team members under **Team**
   - Add endorsements under **Endorsements**

## Development

### Requirements

- Node.js 18+ and npm
- PHP 8.1+
- WordPress 6.4+

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
├── style.css                    # Theme header and base styles
├── functions.php                # Main theme functions
├── index.php                    # Main template
├── header.php                   # Header template
├── footer.php                   # Footer template
├── sidebar.php                  # Sidebar template
├── includes/
│   ├── free/
│   │   ├── custom-post-types.php    # CPT registration
│   │   ├── gutenberg-blocks.php     # Block registration
│   │   ├── customizer.php           # Theme customizer
│   │   ├── template-functions.php   # Template helpers
│   │   ├── template-tags.php        # Template tags
│   │   ├── integrations.php         # Third-party integrations
│   │   └── tgmpa-config.php         # Plugin recommendations
│   ├── lib/
│   │   └── tgmpa/                   # TGM Plugin Activation library
│   └── premium/                     # Premium features (future)
├── assets/
│   ├── css/                         # Stylesheets
│   ├── js/                          # JavaScript
│   └── react/                       # React components (future)
├── templates/                       # Template parts
├── elementor-widgets/              # Elementor widgets (future)
└── languages/                       # Translation files
```

### Build System

CampaignPress uses Vite for modern, fast builds with React support:

- **Development:** `npm run dev` - Watch mode with hot reload
- **Production:** `npm run build` - Optimized production build
- **Preview:** `npm run preview` - Preview production build
- **Lint:** `npm run lint` - Check JavaScript code quality
- **Format:** `npm run format` - Auto-format code with Prettier

### Coding Standards

- **WordPress Coding Standards** - Follow WordPress PHP coding standards
- **Function Prefixing** - All functions prefixed with `campaignpress_` or `cp_`
- **Security** - Proper escaping (`esc_html()`, `esc_attr()`, `esc_url()`)
- **Sanitization** - All user input sanitized
- **Nonces** - Form submissions use nonces
- **Prepared Statements** - Database queries use prepared statements

## Support

- **Documentation:** [https://campaignpress.org/docs](https://campaignpress.org/docs)
- **Community Forum:** [https://campaignpress.org/forum](https://campaignpress.org/forum)
- **Issue Tracker:** [GitHub Issues](https://github.com/yourusername/campaignpress/issues)

## Roadmap

### Phase 1: Foundation (Current - Months 1-6)
- ✅ Free version with Gutenberg blocks
- ✅ Custom post types
- ✅ Theme customizer
- ⏳ Premium CRM foundation
- ⏳ Contact import/export

### Phase 2: Field Operations (Months 7-12)
- ⏳ Canvassing tools with offline mode
- ⏳ Phone banking interface
- ⏳ GOTV dashboard
- ⏳ Mobile PWA

### Phase 3: Compliance & Advanced (Months 13-18)
- ⏳ FEC compliance automation
- ⏳ Email automation
- ⏳ SMS integration
- ⏳ Petition tools
- ⏳ Volunteer scheduling

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

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

## Credits

- Built with [WordPress](https://wordpress.org/)
- React components powered by [@wordpress/element](https://www.npmjs.com/package/@wordpress/element)
- Build system: [Vite](https://vitejs.dev/)
- Icons: [Dashicons](https://developer.wordpress.org/resource/dashicons/)
- Plugin installation powered by [TGM Plugin Activation](http://tgmpluginactivation.com/)

## Changelog

### 1.0.0 - 2024-11-16
- Initial release
- Free version with custom Gutenberg blocks
- Custom post types (Issues, Events, Endorsements, Team, Volunteers)
- Theme customizer with color schemes
- Responsive design system
- Third-party plugin integrations
- One-click plugin installation via TGM Plugin Activation
- 10 recommended campaign-optimized plugins

---

**Built for campaigns, by campaign veterans.** 🗳️

Make your campaign website work as hard as your field team.
