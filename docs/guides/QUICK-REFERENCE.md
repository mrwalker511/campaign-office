# CampaignPress Quick Reference

**One-page feature comparison and pricing guide**

---

## Pricing Tiers

| Tier | Price | Best For |
|------|-------|----------|
| **Free** | $0 | Small campaigns, local elections |
| **Basic** | $99/year | Growing campaigns needing updates |
| **Professional** | $299/year | Serious campaigns with field ops |
| **Enterprise** | $999/year | Statewide/federal campaigns |

---

## Feature Matrix

### Core Features (All Tiers)

✓ 5 Custom Post Types (Issues, Events, Endorsements, Team, Volunteers)
✓ 7 Gutenberg Blocks + 10 Elementor Widgets
✓ WordPress 6.9 Design System (33 colors, 3 fonts, 8 sizes)
✓ 4 Party Color Schemes (Democrat, Republican, Independent, Green)
✓ WCAG 2.1 AA Accessibility
✓ Translation Support (WPML, Polylang)
✓ Donation Integration (ActBlue, WinRed, PayPal, Stripe, Square, Donorbox)

### Premium Features

| Feature | Free | Basic | Professional | Enterprise |
|---------|:----:|:-----:|:------------:|:----------:|
| **Auto Updates** | - | ✓ | ✓ | ✓ |
| **Developer Console** | - | ✓ | ✓ | ✓ |
| **CRM (50K contacts)** | - | - | ✓ | ✓ |
| **Field Operations** | - | - | ✓ | ✓ |
| **Analytics Dashboard** | - | - | ✓ | ✓ |
| **Email/SMS Integration** | - | - | ✓ | ✓ |
| **FEC Compliance** | - | - | - | ✓ |
| **REST API** | - | - | - | ✓ |
| **White Label** | - | - | - | ✓ |
| **Support** | Community | Email | Priority 24h | Dedicated |

---

## Quick Start Commands

### Development
```bash
npm install          # Install dependencies
npm run dev          # Start dev server with HMR
npm run build        # Build for production
npm run watch        # Auto-rebuild on changes
```

### WordPress
- **Flush permalinks:** /wp-admin/options-permalink.php
- **Enable debug:** Add `WP_DEBUG` to wp-config.php
- **Check logs:** `wp-content/debug.log`

---

## Design Tokens (WordPress 6.9)

### Colors (33 total)
- Primary: `--wp--preset--color--primary-{50-900}`
- Accent: `--wp--preset--color--accent-{50-900}`
- Neutral: `--wp--preset--color--neutral-{50-900}`

### Fonts
- Display: `--wp--preset--font-family--display` (Bricolage Grotesque)
- Body: `--wp--preset--font-family--body` (Plus Jakarta Sans)
- Mono: `--wp--preset--font-family--mono` (JetBrains Mono)

### Sizes
- Font: `--wp--preset--font-size--{xs,sm,base,lg,xl,2-xl,3-xl,4-xl}`
- Spacing: `--wp--preset--spacing--{1-24}` (4px to 96px)

---

## Common Tasks

### Switch Party Color Scheme
Add body class: `color-scheme-democrat`, `color-scheme-republican`, `color-scheme-independent`, `color-scheme-green`

### Add New Block
1. Create block in `assets/react/blocks/`
2. Register in `includes/free/gutenberg-blocks.php`
3. Add render callback
4. Rebuild: `npm run build`

### Enable Premium Feature
1. Add license key in admin
2. Go to CampaignPress Pro → Features
3. Toggle feature on/off

---

## Support Channels

**Free Users:**
- Documentation: `/docs/`
- GitHub: github.com/mrwalker511/campaign-office

**Premium Users:**
- Email: support@campaignpress.com
- Response time: 24 hours (Professional), 4 hours (Enterprise)

---

## Technical Requirements

**Minimum:**
- WordPress 6.9+
- PHP 7.4+
- MySQL 5.7+

**Recommended:**
- WordPress (latest)
- PHP 8.1+
- MySQL 8.0+
- 512MB memory for CRM

---

**Version:** 2.0.0 | **Last Updated:** December 28, 2025
