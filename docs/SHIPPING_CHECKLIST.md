# 🚀 Theme Shipping Checklist

Quick reference checklist before shipping your theme to marketplaces.

## 📦 Build Process

- [ ] Run `./build-production.sh` to create distribution package
- [ ] Verify package size is under 10MB (ideally 3-5MB)
- [ ] Test installation from the generated zip file

## 🔍 Code Quality

- [ ] Remove all `console.log()` statements
- [ ] Remove all debug code and comments
- [ ] Remove TODO/FIXME comments
- [ ] No PHP errors or warnings (test with `WP_DEBUG` enabled)
- [ ] All functions are properly documented

## 🔒 Security

- [ ] No hardcoded API keys or secrets
- [ ] No `.env` files included
- [ ] All user inputs are sanitized
- [ ] All outputs are escaped
- [ ] Nonces verified on all form submissions
- [ ] File uploads validated properly

## ⚡ Performance

- [ ] All CSS files are minified
- [ ] All JavaScript files are minified
- [ ] Images optimized (screenshot.png < 300KB)
- [ ] No unused code included
- [ ] Database queries are optimized
- [ ] No N+1 query problems

## 📋 WordPress Standards

- [ ] Theme passes Theme Check plugin
- [ ] Follows WordPress Coding Standards
- [ ] All strings are translatable (`__()`, `_e()`, etc.)
- [ ] Text domain matches theme slug
- [ ] Proper enqueuing of scripts/styles (no hardcoded links)
- [ ] No deprecated functions used
- [ ] Supports required WordPress features

## 📄 Required Files

- [ ] `style.css` with proper theme header
- [ ] `index.php` exists
- [ ] `screenshot.png` (1200x900px, optimized)
- [ ] `readme.txt` with proper format
- [ ] `LICENSE.txt` (GPL v2 or later)
- [ ] `CHANGELOG.md` documenting version history
- [ ] `functions.php` properly structured

## 🎨 Theme Header (style.css)

Verify your theme header includes:
- [ ] Theme Name
- [ ] Theme URI
- [ ] Author
- [ ] Author URI
- [ ] Description
- [ ] Version number
- [ ] License: GNU General Public License v2 or later
- [ ] License URI: http://www.gnu.org/licenses/gpl-2.0.html
- [ ] Text Domain
- [ ] Tags (appropriate for your theme)

## 🧪 Testing

- [ ] Tested on latest WordPress version
- [ ] Tested with default content
- [ ] Tested with lots of content
- [ ] Tested with no content
- [ ] All blocks work correctly
- [ ] Responsive design works on all screen sizes
- [ ] Tested in major browsers (Chrome, Firefox, Safari, Edge)
- [ ] No JavaScript errors in console
- [ ] Forms submit correctly
- [ ] Search functionality works
- [ ] Navigation menus work
- [ ] Widgets work in all areas
- [ ] Theme customizer options work

## ♿ Accessibility

- [ ] Keyboard navigation works
- [ ] Skip links present
- [ ] Proper heading hierarchy
- [ ] Alt text on images
- [ ] ARIA labels where needed
- [ ] Color contrast meets WCAG AA standards
- [ ] Focus indicators visible

## 📱 Responsive Design

- [ ] Mobile (320px - 767px)
- [ ] Tablet (768px - 1024px)
- [ ] Desktop (1025px+)
- [ ] Images scale properly
- [ ] Touch targets are 44px minimum

## 🌍 Internationalization

- [ ] All text strings are wrapped in translation functions
- [ ] Text domain is consistent
- [ ] `.pot` file generated (if applicable)
- [ ] RTL stylesheet included (if applicable)

## 📚 Documentation

- [ ] Customer-facing README.md
- [ ] Installation instructions
- [ ] Feature documentation
- [ ] Customization guide
- [ ] Troubleshooting section
- [ ] Support contact information
- [ ] Credits for any third-party resources

## ⚖️ Legal & Licensing

- [ ] All code is GPL compatible
- [ ] Third-party libraries properly attributed
- [ ] License information for fonts
- [ ] License information for images
- [ ] No trademarked content without permission
- [ ] Privacy policy considerations documented

## 🎯 Marketplace Specific

### ThemeForest
- [ ] File structure matches requirements
- [ ] Documentation folder included
- [ ] Licensing folder included
- [ ] Preview images included
- [ ] Version number in theme header and readme match

### WordPress.org
- [ ] No premium features or upsells (free version)
- [ ] Theme Check plugin passes 100%
- [ ] Meets theme review requirements
- [ ] readme.txt follows required format

### TemplateMonster / Creative Market
- [ ] Professional presentation
- [ ] High-quality preview images
- [ ] Detailed feature list
- [ ] Support documentation

## 📊 Final Checks

- [ ] Version numbers consistent across all files
- [ ] No development files included (check `.distignore`)
- [ ] Package installs cleanly in fresh WordPress
- [ ] Theme activates without errors
- [ ] Demo content importable (if provided)
- [ ] All premium features work as advertised
- [ ] Refund/support policy documented

## 🚀 Launch

- [ ] Upload to marketplace(s)
- [ ] Set appropriate pricing
- [ ] Write compelling description
- [ ] Add preview screenshots/video
- [ ] Set up support channel
- [ ] Monitor initial reviews
- [ ] Prepare for customer questions

---

## Current Theme Size Analysis

Your current development directory: **17MB**

**Major size consumers:**
- wp-cli.phar: 6.9MB ❌ (remove)
- .git directory: 4.2MB ❌ (remove)
- includes/: 1.8MB ✅ (keep)
- package-lock.json: 1.1MB ❌ (remove)
- screenshot.png: 918KB ⚠️ (optimize to ~300KB)
- assets/: 728KB ✅ (keep, but ensure only minified)

**Expected production size: 3-5MB** ✅

---

## Build Command

```bash
./build-production.sh
```

This will create: `build/campaign-office-{version}.zip`

---

## Quick Test Installation

```bash
# After building
cd build
unzip campaign-office-*.zip
# Copy to WordPress themes directory and test
```
