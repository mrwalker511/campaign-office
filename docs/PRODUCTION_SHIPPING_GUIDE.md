# Production Theme Shipping Guide

## ❌ DO NOT INCLUDE (Reduce size by ~14MB)

### Development Tools & Dependencies
- `package.json`
- `package-lock.json` (1.1MB)
- `node_modules/` (if present)
- `composer.json`
- `composer.lock`
- `vendor/` (unless required for production)

### Build & Configuration Files
- `vite.config.js`
- `tailwind.config.js`
- `postcss.config.js`
- `.eslintrc.json`
- `build_log.txt`

### Git & Version Control
- `.git/` directory (4.2MB)
- `.gitignore`

### Documentation & Development Files
- `AUDIT_WALKTHROUGH.md`
- `BLOCK_IMPLEMENTATION_GUIDE.md`
- `CODE_REVIEW_REPORT.md`
- `DESIGN-ENHANCEMENTS.md`
- `TESTING-REPORT.md`
- `implementation_plan.md`
- `task.md`
- `test_permissions.txt`
- `playground-fix-plugin.php`
- `style-guide.html`
- `.claude/` directory
- `lighthouse-reports/`
- `docs/` directory (optional - include only essential docs)

### IDE & Editor Files
- `.vscode/`
- `.idea/`
- Any editor-specific files

---

## ✅ DO INCLUDE (Essential ~3-5MB)

### Required WordPress Theme Files
- `style.css` (with theme header)
- `functions.php`
- `index.php`
- `screenshot.png` (required, optimize to ~200-300KB)
- `readme.txt` (for theme repository listing)

### Template Files
- `header.php`
- `footer.php`
- `sidebar.php`
- `front-page.php`
- `searchform.php`
- `templates/` directory
- `parts/` directory
- `patterns/` directory

### Core Functionality
- `includes/` directory (1.8MB)
  - All premium features
  - All free features
  - Core functionality
  - Libraries (like TGMPA)

### Assets (Optimized)
- `assets/css/` - **Minified CSS only**
- `assets/js/` - **Minified JS only**
- `assets/fonts/` - **Web-optimized fonts only**
- `assets/images/` - **Optimized images only**
- `assets/vendor/` - **Only production dependencies**

### Block System
- `blocks/` directory
- `theme.json`

### Internationalization
- `languages/` directory (if applicable)

---

## 📦 RECOMMENDED PRODUCTION STRUCTURE

```
campaign-office/
├── style.css (required)
├── functions.php
├── index.php
├── header.php
├── footer.php
├── sidebar.php
├── front-page.php
├── searchform.php
├── screenshot.png (optimized)
├── readme.txt
├── theme.json
├── CHANGELOG.md (optional but recommended)
├── README.md (customer-facing, simplified)
├── LICENSE.txt (required for marketplaces)
│
├── assets/
│   ├── css/
│   │   ├── dist/ (Vite-built minified only)
│   │   └── critical/ (critical CSS)
│   ├── js/
│   │   └── dist/ (Vite-built minified only)
│   ├── fonts/ (web-optimized)
│   ├── images/
│   │   └── dist/ (optimized images)
│   └── vendor/ (production deps only)
│
├── blocks/
│   └── [all block files]
│
├── includes/
│   ├── core/
│   ├── free/
│   ├── premium/
│   └── lib/
│
├── templates/
│   └── [all template files]
│
├── parts/
│   └── [all template parts]
│
├── patterns/
│   └── [all block patterns]
│
├── languages/
│   └── [translation files]
│
└── scripts/ (if needed for production)
```

---

## 🎯 SIZE OPTIMIZATION RECOMMENDATIONS

### 1. Images & Screenshots
- **Current screenshot.png**: 918KB
- **Optimize to**: 200-300KB (1200x900px @ 80% quality)
- **Tool**: Use ImageOptim, TinyPNG, or similar

```bash
# Example optimization
convert screenshot.png -resize 1200x900 -quality 80 screenshot-optimized.png
```

### 2. CSS & JavaScript
- Include **only minified** versions
- Remove source maps (*.map files)
- Remove unminified versions

### 3. Vendor Dependencies
- Review `assets/vendor/bootstrap` (311KB)
- Include only components you actually use
- Consider tree-shaking unused Bootstrap components

### 4. Font Files
- Include only necessary font weights
- Use WOFF2 format only (best compression)
- Subset fonts to required characters

### 5. Premium Features
- Ensure `includes/premium/` (1.3MB) contains no debug code
- Remove development comments
- Minify inline code if applicable

---

## 📋 PRE-SHIPPING CHECKLIST

### Code Quality
- [ ] Remove all `console.log()` and debug statements
- [ ] Remove all TODO/FIXME comments
- [ ] Ensure all functions are properly documented
- [ ] Run code through linter/beautifier
- [ ] Test all features work without dev dependencies

### Security
- [ ] Remove any API keys or secrets
- [ ] Remove `.env` files
- [ ] Sanitize all database inputs
- [ ] Escape all outputs
- [ ] Validate nonces on form submissions

### Performance
- [ ] All CSS is minified
- [ ] All JS is minified
- [ ] Images are optimized
- [ ] No unused code included
- [ ] Critical CSS is inlined where beneficial

### WordPress Standards
- [ ] Theme passes WordPress.org theme check
- [ ] Follows WordPress coding standards
- [ ] All strings are translatable
- [ ] Proper enqueueing of scripts/styles
- [ ] No PHP errors or warnings

### Marketplace Requirements
- [ ] Include LICENSE.txt (GPL compatible)
- [ ] Include proper theme header in style.css
- [ ] Include customer-facing README
- [ ] Include CHANGELOG.md
- [ ] Screenshot meets requirements (1200x900px)
- [ ] Version number is set correctly

---

## 🚀 ESTIMATED FINAL SIZE

After removing development files and optimizing:
- **Target size**: 3-5MB (down from 17MB)
- **Maximum recommended**: 10MB
- **Industry standard**: Most premium themes are 2-8MB

---

## 📦 CREATING DISTRIBUTION PACKAGE

### Option 1: Manual Packaging
```bash
# Create a clean copy
mkdir campaign-office-production
rsync -av --exclude-from='.distignore' ./ campaign-office-production/

# Create zip
zip -r campaign-office-1.0.0.zip campaign-office-production/
```

### Option 2: Use Build Script
Create a `build-production.sh` script that:
1. Copies only necessary files
2. Optimizes images
3. Minifies assets
4. Creates versioned zip file

### .distignore File
Create a `.distignore` file listing all files to exclude from distribution:
```
.git
.gitignore
node_modules
package.json
package-lock.json
wp-cli.phar
gulpfile.js
... etc
```

---

## 💰 MARKETPLACE-SPECIFIC NOTES

### ThemeForest (Envato)
- Maximum 50MB (you're well under)
- Include documentation
- Include licensing information
- Follow their file structure requirements

### WordPress.org
- Must be GPL licensed
- No premium upsells in free version
- Must pass theme check plugin
- Usually smaller themes (under 5MB)

### TemplateMonster / Creative Market
- Professional documentation required
- Clean, organized code
- Customer support documentation

---

## 🔧 NEXT STEPS

1. Create `.distignore` file
2. Create `build-production.sh` script
3. Optimize screenshot.png
4. Review and minify all assets
5. Test theme with only production files
6. Create documentation for customers
7. Add LICENSE.txt
8. Package and test installation
9. Submit to marketplace(s)
