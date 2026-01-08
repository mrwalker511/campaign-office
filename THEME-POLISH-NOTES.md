# Theme Polish & Pre-Release Checklist

This document tracks critical fixes needed before distribution.

## 🚨 CRITICAL - Must Fix Before Release

### 1. Font Files (0 bytes - BROKEN)
**Status:** ❌ NOT READY
**Location:** `/assets/fonts/`
**Issue:** All three font files are empty placeholder files (0 bytes)
**Impact:** Typography completely broken - falls back to system fonts
**Fix:** See `/assets/fonts/README.md` for detailed instructions
**Files Needed:**
- BricolageGrotesque-Variable.woff2
- PlusJakartaSans-Variable.woff2
- JetBrainsMono-Variable.woff2

### 2. Screenshot Wrong Size
**Status:** ❌ NOT READY
**Current:** 1024 × 1024 pixels
**Required:** 1200 × 900 pixels (4:3 ratio)
**Issue:** Does not meet WordPress.org requirements
**Fix:** See `/docs/SCREENSHOT-REQUIREMENTS.md` for instructions

### 3. CDN Dependencies (WordPress.org Blocker)
**Status:** ⚠️ DOCUMENTED
**Issue:** Bootstrap loaded from CDN (jsdelivr.net)
**Impact:** Cannot submit to WordPress.org
**Workaround:** Filters added for local hosting
**Fix for WordPress.org:**
1. Download Bootstrap 5.3.0
2. Place in `/assets/vendor/bootstrap/`
3. Use filters to switch to local files
**Fix for Commercial:** Keep CDN but offer local option via filters

### 4. Premium Analytics CDN Dependencies
**Status:** ⚠️ NEEDS BUNDLING
**Location:** `/includes/premium/analytics/analytics-init.php`
**Issue:** Chart.js and Leaflet loaded from CDN
**Files:**
- Chart.js (cdn.jsdelivr.net)
- Leaflet (unpkg.com)
**Fix:** Download and bundle locally in `/assets/vendor/`

## ✅ COMPLETED - Polish Improvements

### License Standardization
- ✅ Changed from GPL v3 to GPL v2 or later
- ✅ Updated `style.css` header
- ✅ Updated `README.md`
- ✅ Updated `readme.txt`
- ✅ All references now consistent

### Google Fonts Preconnect Removed
- ✅ Removed preconnect to fonts.googleapis.com
- ✅ Added documentation about self-hosted fonts
- ✅ Preserved commented code for easy switching

### Changelog & Dates Fixed
- ✅ Updated version 2.0.0 date to 2025-01-08
- ✅ Updated version 1.0.0 date to 2024-11-01
- ✅ Added upgrade notice for 2.0.0 in readme.txt
- ✅ Synchronized dates across README.md and readme.txt

### CDN Documentation Added
- ✅ Added comments about WordPress.org requirements
- ✅ Documented filter usage for local Bootstrap
- ✅ Added download link for Bootstrap

### Documentation Created
- ✅ QUICKSTART.md - 5-minute setup guide
- ✅ assets/fonts/README.md - Font installation guide
- ✅ docs/SCREENSHOT-REQUIREMENTS.md - Screenshot specs

## 📋 Distribution-Specific Checklists

### For WordPress.org Submission

**Required Before Submission:**
- [ ] Bundle Bootstrap locally (remove CDN)
- [ ] Bundle Chart.js locally
- [ ] Bundle Leaflet locally
- [ ] Fix screenshot to 1200 × 900
- [ ] Add actual font files
- [ ] Remove ALL premium/licensing features
- [ ] Remove upsell notices
- [ ] Verify 100% GPL compatibility
- [ ] Test with Theme Check plugin
- [ ] Run PHPCS with WordPress-Theme standard
- [ ] Generate updated .pot file
- [ ] Add font license files (OFL.txt)

**Commands to Run:**
```bash
# Test theme compliance
composer run phpcs:theme

# Generate translation file
wp i18n make-pot . languages/campaign-office.pot

# Run WordPress theme checker
# (install Theme Check plugin and scan)
```

### For Commercial Distribution (ThemeForest, etc.)

**Required Before Submission:**
- [ ] Fix screenshot to 1200 × 900
- [ ] Add actual font files
- [ ] Consider bundling CDN resources locally (better performance)
- [ ] Create live demo site
- [ ] Create video demo/walkthrough
- [ ] Add 8-10 additional screenshots
- [ ] Create detailed documentation site
- [ ] Set up support system
- [ ] Add customer testimonials
- [ ] Create feature comparison table
- [ ] Set up licensing server (if using premium features)
- [ ] Test purchasing/activation flow

**Commercial Marketing Assets:**
- [ ] Promotional banner (590 × 300px)
- [ ] Multiple preview images
- [ ] Feature highlights graphics
- [ ] Video demo (60-90 seconds)
- [ ] Documentation PDF

## 🔧 Development Setup Notes

### Font Installation (Developers)

1. Download fonts from Google Fonts:
   - [Bricolage Grotesque](https://fonts.google.com/specimen/Bricolage+Grotesque)
   - [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans)
   - [JetBrains Mono](https://fonts.google.com/specimen/JetBrains+Mono)

2. Convert to WOFF2 format (use variable versions)
3. Place in `/assets/fonts/`
4. Verify file sizes (should be ~50-200KB each)

### Screenshot Generation

1. Set up local WordPress install
2. Import demo content
3. Set color scheme to "Democrat Blue"
4. Capture homepage at 1200 × 900
5. Replace `screenshot.png`

## 📊 File Size Targets

**Before optimization:**
- Total theme: ~2.5MB (with dependencies)

**After optimization (WordPress.org ready):**
- Total theme: < 10MB (hard limit)
- Recommended: < 5MB
- Screenshot: < 500KB
- Fonts: ~300-600KB total
- Bootstrap: ~150KB (minified)

**Current status:**
- Style.css: 5.3KB
- Functions.php: 23.7KB
- Assets/CSS: 226KB
- Assets/JS: 181KB
- Screenshot: 939KB (needs regeneration)
- Fonts: 0 bytes ❌ (needs files)

## 🎯 Priority Order

### Immediate (Before ANY distribution):
1. **Add actual font files** - Theme is broken without these
2. **Fix screenshot size** - Required for all marketplaces
3. **Test typography** - Ensure fonts load correctly

### Before WordPress.org:
4. **Bundle all CDN resources**
5. **Remove premium features**
6. **Run Theme Check plugin**
7. **Test accessibility**

### Before Commercial Release:
4. **Set up live demo**
5. **Create video demo**
6. **Write comprehensive docs**
7. **Create comparison table**

## 📝 Notes

### WordPress Core Scripts
WordPress includes these scripts in core (can use without bundling):
- jQuery
- jQuery UI
- Underscore.js
- Backbone.js
- Masonry
- MediaElement.js
- Plupload

**NOT in core (must bundle):**
- Bootstrap
- Chart.js
- Leaflet
- Custom theme scripts

### Font Licensing
All three fonts (Bricolage Grotesque, Plus Jakarta Sans, JetBrains Mono) are:
- Licensed under SIL Open Font License 1.1
- Free for commercial use
- Can be bundled and redistributed
- Require OFL.txt license file in distribution

### Testing Checklist
- [ ] Test on WordPress 6.4, 6.5, 6.9
- [ ] Test on PHP 7.4, 8.0, 8.1, 8.2
- [ ] Test with block editor
- [ ] Test with classic editor
- [ ] Test all custom post types
- [ ] Test demo content import
- [ ] Test color scheme switching
- [ ] Test on mobile devices
- [ ] Test accessibility (WAVE, axe)
- [ ] Test performance (GTmetrix, PageSpeed)
- [ ] Test with common plugins (Yoast, WooCommerce)

## 🔗 Useful Links

- [WordPress Theme Review Guidelines](https://make.wordpress.org/themes/handbook/review/)
- [Theme Unit Test Data](https://github.com/WPTT/theme-unit-test)
- [Theme Check Plugin](https://wordpress.org/plugins/theme-check/)
- [Query Monitor Plugin](https://wordpress.org/plugins/query-monitor/)
- [TinyPNG Image Optimization](https://tinypng.com/)
- [Google Fonts](https://fonts.google.com/)

---

**Last Updated:** 2025-01-08
**Theme Version:** 2.0.0
**Prepared by:** Theme polish review process
