# CampaignPress Documentation

**Complete documentation for CampaignPress theme development and usage**

Version: 2.0.0 | Last Updated: December 28, 2025

---

## 📚 Core Documentation

### For Users & Product Managers
- **[../README.md](../README.md)** - Project overview and quick start
- **[FEATURES.md](FEATURES.md)** - Complete feature breakdown (free vs premium)
- **[../GETTING-STARTED.md](../GETTING-STARTED.md)** - Installation and setup guide
- **[../CHANGELOG.md](../CHANGELOG.md)** - Version history and updates

### For Developers
- **[DEVELOPER-REFERENCE.md](DEVELOPER-REFERENCE.md)** - Complete technical reference
  - Architecture overview
  - Technology stack
  - Development workflow
  - Block development
  - Premium features
  - Database schema
  - Security guidelines
  - API reference

### For Designers
- **[DESIGN-REFERENCE.md](DESIGN-REFERENCE.md)** - Complete design system guide
  - WordPress 6.9 design tokens
  - Color system (33 colors)
  - Typography (3 font families)
  - Spacing & layout
  - Components & patterns
  - Animations
  - Style guide
  - Accessibility standards

### For DevOps & Site Admins
- **[PRODUCTION-REFERENCE.md](PRODUCTION-REFERENCE.md)** - Deployment & operations guide
  - Pre-launch checklist
  - Production deployment
  - Performance optimization
  - Security hardening
  - Monitoring & maintenance
  - Troubleshooting

### For Testers & QA
- **[TESTING.md](TESTING.md)** - Testing framework and practices
- **[TECHNICAL-REPORTS.md](TECHNICAL-REPORTS.md)** - Technical analysis and reports
- **[DEVELOPER-GUIDE.md](DEVELOPER-GUIDE.md)** - Legacy developer guide (see DEVELOPER-REFERENCE.md for latest)

---

## 🎯 Quick Navigation

### I want to...

**...understand the project**
→ Start with [../README.md](../README.md)

**...install and configure CampaignPress**
→ Read [../GETTING-STARTED.md](../GETTING-STARTED.md)

**...develop features or fix bugs**
→ Use [DEVELOPER-REFERENCE.md](DEVELOPER-REFERENCE.md)

**...customize the design**
→ Follow [DESIGN-REFERENCE.md](DESIGN-REFERENCE.md)

**...deploy to production**
→ Follow [PRODUCTION-REFERENCE.md](PRODUCTION-REFERENCE.md)

**...see what's changed**
→ Check [../CHANGELOG.md](../CHANGELOG.md)

**...understand the architecture**
→ Read [CLAUDE.md](CLAUDE.md) (comprehensive overview)

---

## 🏗️ Architecture Quick Reference

### System Layers
```
WordPress Root (templates, header, footer)
    ↓
Design System Layer (theme.json + CSS)
    ↓
Free Module Layer (18 modules - always loaded)
    ↓
Premium Feature Layer (9 modules - license-gated)
    ↓
Integration Layer (Elementor, WPML, third-party)
```

### Key Files
- `functions.php` - Theme entry point
- `theme.json` - Design system tokens
- `includes/free/` - Free modules (18 files)
- `includes/premium/` - Premium modules (9 directories)
- `assets/react/` - React components (blocks, CRM)
- `assets/dist/` - Compiled production assets

---

## 🎨 Design System Quick Reference

### WordPress 6.9 Design Tokens

**Colors:** 33 total (9-shade palettes)
- Primary: `--wp--preset--color--primary` (#0053c3)
- Accent: `--wp--preset--color--accent` (#ff8800)
- Neutral: `--wp--preset--color--neutral` (#6c757d)

**Fonts:** 3 families
- Display: `--wp--preset--font-family--display` (Bricolage Grotesque)
- Body: `--wp--preset--font-family--body` (Plus Jakarta Sans)
- Mono: `--wp--preset--font-family--mono` (JetBrains Mono)

**Sizes:** 8 fluid scales
- Font: `--wp--preset--font-size--{xs,sm,base,lg,xl,2-xl,3-xl,4-xl}`
- Spacing: `--wp--preset--spacing--{1-24}` (4px to 96px)

**Party Color Schemes:** 4 themes
- Democrat Blue (default)
- Republican Red (`body.color-scheme-republican`)
- Independent Purple (`body.color-scheme-independent`)
- Green Party (`body.color-scheme-green`)

---

## 💻 Development Quick Reference

### Setup
```bash
git clone https://github.com/mrwalker511/campaign-office.git
cd campaign-office
npm install
npm run dev
```

### Common Commands
```bash
npm run dev          # Start dev server with HMR
npm run build        # Production build
npm run watch        # Auto-rebuild
npm run lint         # Check code quality
npm run format       # Format code with Prettier
```

### Requirements
- WordPress 6.9+
- PHP 8.1+ (minimum 7.4)
- MySQL 8.0+ (minimum 5.7)
- Node.js 18+
- npm 9+

---

## 🚀 Production Quick Reference

### Pre-Launch Checklist
- [ ] Code quality (linting, standards)
- [ ] Security audit (sanitization, escaping)
- [ ] Performance (minification, caching)
- [ ] Functionality (all features tested)
- [ ] Accessibility (WCAG 2.1 AA)
- [ ] Compatibility (browsers, devices)
- [ ] SEO (meta tags, sitemap)

### Deployment
```bash
npm run build
zip -r campaign-office-2.0.0.zip . -x "*.git*" "node_modules/*"
# Upload to wp-content/themes/
# Activate in WordPress admin
```

---

## 📖 Additional Resources

### Sample Templates
- `sample-templates/conversion-maximizer.json` - High-conversion landing page
- `sample-templates/progressive-leader.json` - Progressive candidate template

### External Links
- [WordPress Developer Handbook](https://developer.wordpress.org/)
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)

---

## 🤝 Contributing

### Reporting Issues
Open an issue on [GitHub](https://github.com/mrwalker511/campaign-office/issues)

### Pull Requests
1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

### Documentation Updates
Documentation is just as important as code! If you find:
- Unclear instructions
- Missing information
- Outdated content
- Typos or errors

Please submit a PR or open an issue.

---

## 📄 License

CampaignPress is licensed under GPLv3 or later.

Premium features require a valid license key but the code remains GPL.

---

## 🆘 Support

### Free Users
- Documentation (this folder)
- [GitHub Issues](https://github.com/mrwalker511/campaign-office/issues)
- Community forums

### Premium Users
- Priority email support: support@campaignpress.com
- Response time: 24 hours (Professional), 4 hours (Enterprise)
- Live chat (Professional+)
- Dedicated account manager (Enterprise)

---

**Main Documentation Hub** | [Back to Main README](../README.md)
