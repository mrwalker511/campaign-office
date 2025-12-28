# CampaignPress Developer Guide

Complete guide for developers working on the CampaignPress theme.

---

## Table of Contents

- [Development Setup](#development-setup)
- [Project Structure](#project-structure)
- [Working with Claude Code](#working-with-claude-code)
- [Heroicons Integration](#heroicons-integration)
- [Block Development](#block-development)
- [Design System](#design-system)
- [License Testing](#license-testing)
- [Font Configuration](#font-configuration)
- [Volunteer Module](#volunteer-module)

---

## Development Setup

### Local Development

```bash
# Clone into WordPress themes directory
cd wp-content/themes
git clone https://github.com/yourusername/campaignpress.git
cd campaignpress

# Install dependencies
npm install

# Start development server
npm run dev

# Build for production
npm run build
```

### Tech Stack

- **Backend:** PHP 8.1+, WordPress 6.9+ APIs, MySQL/MariaDB
- **Frontend:** React (Gutenberg blocks), Modern CSS (no preprocessors), Vanilla JS
- **Build Tools:** Vite 5.0, Node.js 18+, Prettier, ESLint
- **Design System:** WordPress `theme.json` with design tokens

### Project Structure

```
campaign-office/
├── theme.json              # Design system configuration
├── functions.php           # Theme bootstrap
├── blocks/                 # Custom Gutenberg blocks (React)
│   ├── countdown/
│   ├── donation-form/
│   ├── hero-commander/
│   ├── icon/
│   ├── mission-control/
│   ├── policy-platform/
│   ├── progress/
│   ├── event-organizer/
│   ├── volunteer-matcher/
│   └── section-wrapper/
├── includes/
│   ├── free/              # GPL features
│   │   ├── heroicons.php
│   │   ├── icons-browser.php
│   │   ├── gutenberg-blocks.php
│   │   ├── template-tags.php
│   │   └── campaign-design-studio.php
│   └── premium/           # Licensed features
│       ├── crm/
│       ├── field-operations/
│       ├── compliance/
│       ├── analytics/
│       ├── api/
│       ├── integrations/
│       ├── developer-console/
│       └── design-studio/
├── assets/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript
│   ├── icons/             # Heroicons library
│   └── fonts/             # Custom fonts
├── templates/             # Block theme HTML templates
├── parts/                 # Template parts
└── patterns/              # Block patterns
```

---

## Working with Claude Code

### Best Practices for AI Development

When working with Claude Code on this theme:

1. **Be Specific** - Provide exact file paths and line numbers
2. **Context Matters** - Reference existing patterns before requesting changes
3. **Test Incrementally** - Test changes after each modification
4. **Follow Conventions** - Maintain existing naming and code patterns

### Common Tasks

#### Adding a New Feature

```
1. Check if similar feature exists
2. Identify where to add code (free/ or premium/)
3. Follow singleton pattern for classes
4. Use existing hooks and filters
5. Add proper documentation
6. Test thoroughly
```

#### Fixing Bugs

```
1. Reproduce the issue
2. Check error logs (WP_DEBUG mode)
3. Identify affected files
4. Make minimal changes
5. Test fix in multiple scenarios
6. Document the fix
```

#### Code Review Checklist

- [ ] Follows WordPress coding standards
- [ ] Uses proper escaping and sanitization
- [ ] Includes nonce verification for forms
- [ ] Has capability checks for admin functions
- [ ] Uses translation functions (`__()`, `_e()`)
- [ ] Includes inline documentation
- [ ] No PHP warnings or errors
- [ ] Tested in multiple browsers

---

## Heroicons Integration

### Overview

CampaignPress integrates 316 MIT-licensed Heroicons in 4 styles (outline, solid, mini 20px, micro 16px).

### Icon Library Location

```
assets/icons/
├── 16/solid/      # Micro icons (316 SVG files)
├── 20/solid/      # Mini icons (316 SVG files)
├── 24/outline/    # Outline icons (316 SVG files)
├── 24/solid/      # Solid icons (316 SVG files)
└── social/        # Custom social media icons
```

### Helper Functions

**File:** `includes/free/heroicons.php`

```php
// Get any Heroicon SVG
campaignpress_get_heroicon($icon, $style = 'outline', $args = array())

// Echo Heroicon SVG
campaignpress_heroicon($icon, $style = 'outline', $args = array())

// Get social media icons
campaignpress_get_social_heroicon($network, $args = array())

// Get status badges with icons
campaignpress_get_status_badge($status, $text, $icon = '')

// Get common UI icons
campaignpress_get_ui_icon($type, $args = array())
```

### Usage Examples

```php
// Basic icon
echo campaignpress_get_heroicon('calendar', 'outline', array(
    'class' => 'my-custom-class',
    'aria-label' => 'Calendar'
));

// UI icon with preset mapping
echo campaignpress_get_ui_icon('calendar');

// Social media icon
echo campaignpress_get_social_heroicon('facebook', array(
    'aria-hidden' => 'true',
    'width' => '24',
    'height' => '24'
));

// Status badge
echo campaignpress_get_status_badge('success', 'Active');
echo campaignpress_get_status_badge('warning', 'Pending');
echo campaignpress_get_status_badge('danger', 'Cancelled');
```

### Icon Styles

- **Outline** (24px, 1.5px stroke) - Default for UI elements
- **Solid** (24px, filled) - Good for emphasis
- **Mini** (20px, solid) - Perfect for badges and small spaces
- **Micro** (16px, solid) - Tiny icons for dense UIs

### CSS Classes

```css
/* Size classes */
.heroicon-micro   /* 16px */
.heroicon-mini    /* 20px */
.heroicon-outline /* 24px */
.heroicon-solid   /* 24px */
.heroicon-sm      /* 18px */
.heroicon-md      /* 24px */
.heroicon-lg      /* 32px */
.heroicon-xl      /* 48px */

/* Color classes */
.heroicon-primary
.heroicon-secondary
.heroicon-success
.heroicon-warning
.heroicon-danger
.heroicon-info
.heroicon-muted

/* Special effects */
.heroicon-spin    /* Rotating animation for loading states */
```

### Icon Browser

Admin page at **Appearance → Heroicons** provides:
- Search functionality
- Category filtering (Campaign, Arrows, Communication, UI, Files, Social, Status, Data, People)
- Style switching (Outline, Solid, Mini, Micro)
- One-click copy to clipboard
- Live preview

### Gutenberg Icon Block

Features:
- Icon picker with search and categories
- Style selection (Outline, Solid, Mini, Micro)
- Size options (Small, Medium, Large, XL, Custom)
- Color picker for custom colors
- Optional link URL with target options
- Alignment controls
- ARIA label for accessibility

Usage in Block Editor:
```
<!-- wp:campaignpress/icon {"icon":"star","iconStyle":"solid","iconSize":"lg","iconColor":"#f59e0b"} /-->
```

### Design Studio Icon Component

Available in UI Elements category with settings:
- Icon Name (browse in Appearance → Heroicons)
- Icon Style (Outline, Solid, Mini, Micro)
- Icon Size (Small, Medium, Large, Extra Large)
- Icon Color (color picker)
- Link URL (optional clickable icon)

---

## Block Development

### React Block Template

All blocks follow this structure:

```javascript
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    InspectorControls,
    RichText,
    useBlockProps
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    ToggleControl,
    ColorPalette,
    BaseControl
} from '@wordpress/components';

registerBlockType('campaignpress/your-block', {
    edit: ({ attributes, setAttributes }) => {
        const { /* destructure attributes */ } = attributes;
        const blockProps = useBlockProps();

        return (
            <>
                {/* Settings Sidebar */}
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        {/* Add controls here */}
                    </PanelBody>
                </InspectorControls>

                {/* Block Preview */}
                <div {...blockProps}>
                    {/* Add editor preview here */}
                </div>
            </>
        );
    },
    save: () => null // Server-side rendering
});
```

### block.json Structure

```json
{
    "$schema": "https://schemas.wp.org/trunk/block.json",
    "apiVersion": 3,
    "name": "campaignpress/block-name",
    "version": "1.0.0",
    "title": "Block Title",
    "category": "campaign-office",
    "icon": "star-filled",
    "description": "Block description",
    "supports": {
        "html": false,
        "anchor": true,
        "align": ["wide", "full"]
    },
    "attributes": {
        "attributeName": {
            "type": "string",
            "default": "value"
        }
    },
    "textdomain": "campaign-office",
    "editorScript": "file:./index.js",
    "editorStyle": "file:./editor.css",
    "style": "file:./style.css",
    "render": "file:./render.php"
}
```

### Server-Side Rendering (render.php)

```php
<?php
/**
 * Render callback for block
 *
 * @param array $attributes Block attributes
 * @param string $content Block content
 * @param WP_Block $block Block instance
 * @return string Rendered block output
 */

// Extract attributes
$attribute = $attributes['attributeName'] ?? 'default';

// Build CSS classes
$wrapper_classes = array('wp-block-campaignpress-block-name');
if (!empty($attributes['className'])) {
    $wrapper_classes[] = $attributes['className'];
}

// Prepare wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => implode(' ', $wrapper_classes),
));

// Output
ob_start();
?>
<div <?php echo $wrapper_attributes; ?>>
    <!-- Block output here -->
</div>
<?php
return ob_get_clean();
```

### Block Registration

**File:** `includes/free/gutenberg-blocks.php`

```php
// Register block
if (file_exists(CAMPAIGNPRESS_THEME_DIR . '/blocks/block-name/block.json')) {
    register_block_type(CAMPAIGNPRESS_THEME_DIR . '/blocks/block-name');
}
```

---

## Design System

### Tailwind CSS v4 Configuration

**Config:** `tailwind.config.js`  
**CSS Entry:** `assets/css/app.css`  
**Build System:** Vite (`npm run build`)

### Design Tokens

**Colors:**
```css
bg-brand-900    /* Primary Dark */
text-accent-600 /* Secondary Action */
bg-neutral-50   /* Light Backgrounds */
```

**Typography:**
```css
font-serif      /* Headlines: Merriweather */
font-sans       /* Body: Inter */
```

**Spacing:**
Use standard Tailwind spacing (`p-4`, `m-8`) or extended grid gaps.

### Layout Modules (Organisms)

Located in `parts/organisms/`:

| Module | File | Purpose |
|--------|------|---------|
| **Hero** | `parts/organisms/hero.php` | Full-width impact section with dual CTAs |
| **Feature Grid** | `parts/organisms/feature-grid.php` | 3-column grid for policies/features |
| **Content** | `parts/organisms/content-section.php` | Text-heavy section with optional sidebar |
| **CTA** | `parts/organisms/cta.php` | High-conversion action band |
| **Testimonials** | `parts/organisms/testimonials.php` | Social proof grid |

Usage in templates:
```php
<?php get_template_part('parts/organisms/hero'); ?>
<?php get_template_part('parts/organisms/feature-grid'); ?>
<?php get_template_part('parts/organisms/content-section'); ?>
```

### Development

```bash
# Run development server
npm run dev

# Build for production
npm run build
```

---

## License Testing

### Development Mode (Currently Active)

Development mode is **already enabled** in `functions.php` (line 24):

```php
define('CAMPAIGNPRESS_DEV_MODE', true);
```

This gives you:
- ✅ All premium features unlocked (Professional tier)
- ✅ No license validation required
- ✅ All modules enabled
- ✅ Perfect for development and testing

### Test License Keys

For testing the actual license activation flow:

**Starter License:**
```
License Key: CP-DEV-STARTER-2024-A1B2C3D4E5F6
Email: dev@campaignpress.test
```

**Professional License:**
```
License Key: CP-DEV-PROFESSIONAL-2024-X1Y2Z3W4V5U6
Email: dev@campaignpress.test
```

**Enterprise License:**
```
License Key: CP-DEV-ENTERPRISE-2024-Q1W2E3R4T5Y6
Email: dev@campaignpress.test
```

**Expired License (for testing):**
```
License Key: CP-DEV-EXPIRED-2024-M1N2O3P4Q5R6
Email: dev@campaignpress.test
```

**Invalid License (for testing):**
```
License Key: CP-DEV-INVALID-2024-FAKEFAKEFAKE
Email: dev@campaignpress.test
```

### Mock License Server

**File:** `dev-license-helper.php`

To enable the mock license server for testing the actual license flow:

1. Add to `wp-config.php`:
   ```php
   require_once __DIR__ . '/wp-content/themes/campaign-office/dev-license-helper.php';
   ```

2. Temporarily disable dev mode bypass in `functions.php`:
   ```php
   // define('CAMPAIGNPRESS_DEV_MODE', true);
   ```

3. Test license activation at **Appearance → License**

### Production Deployment

Before deploying to production:

1. Remove or comment out `CAMPAIGNPRESS_DEV_MODE` in functions.php
2. Remove the `require_once` for `dev-license-helper.php` from wp-config.php
3. Set up your actual license server
4. Update `LICENSE_SERVER` constant or use the `campaignpress_license_server_url` filter

---

## Font Configuration

### Current Status

The font files in `assets/fonts/` are currently **empty placeholder files (0 bytes)**.

### Required Fonts

CampaignPress uses three custom variable fonts:

1. **Bricolage Grotesque** (Display/Headings)
2. **Plus Jakarta Sans** (Body Text)
3. **JetBrains Mono** (Monospace/Code)

### How to Add Fonts

**Option 1: Download Free Fonts (Recommended)**

All three fonts are available for free from Google Fonts:

1. **Bricolage Grotesque**
   - Download from: https://fonts.google.com/specimen/Bricolage+Grotesque
   - Get the Variable font file
   - Rename to: `BricolageGrotesque-Variable.woff2`

2. **Plus Jakarta Sans**
   - Download from: https://fonts.google.com/specimen/Plus+Jakarta+Sans
   - Get the Variable font file
   - Rename to: `PlusJakartaSans-Variable.woff2`

3. **JetBrains Mono**
   - Download from: https://fonts.google.com/specimen/JetBrains+Mono
   - Get the Variable font file
   - Rename to: `JetBrainsMono-Variable.woff2`

**Option 2: Convert TTF to WOFF2**

If you download TTF files:

```bash
# Using online converter
https://cloudconvert.com/ttf-to-woff2

# Or using fonttools
pip install fonttools brotli
pyftsubset font.ttf --output-file=font.woff2 --flavor=woff2
```

### Installation Steps

1. Download the three font files as `.woff2` format
2. Place them in `/assets/fonts/` directory (replacing empty placeholders)
3. Ensure file names match exactly:
   - `BricolageGrotesque-Variable.woff2`
   - `PlusJakartaSans-Variable.woff2`
   - `JetBrainsMono-Variable.woff2`
4. Test by viewing your site - custom fonts should now load

### Verification

To verify fonts are loading:

1. Open website in browser
2. Open DevTools (F12) → Network tab
3. Reload the page
4. Filter by "Font" - you should see three .woff2 files loading
5. Check the size - each should be 50KB-200KB (not 0KB)

### Alternative: Use System Fonts

If you prefer system fonts (smaller file size, faster loading):

1. Open `theme.json`
2. Modify the `fontFamilies` section:
   ```json
   "fontFamilies": [
     {
       "fontFamily": "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
       "slug": "display",
       "name": "System Sans"
     }
   ]
   ```

### License Information

All three fonts are licensed under the **Open Font License (OFL)**:
- ✅ Free for personal and commercial use
- ✅ Can be bundled with software
- ✅ Can be modified
- ❌ Cannot be sold standalone

---

## Volunteer Module

### Database Schema

**Table:** `{prefix}cp_volunteers`

```sql
CREATE TABLE {prefix}cp_volunteers (
    id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20) DEFAULT NULL,
    zip_code varchar(10) DEFAULT NULL,
    interests text DEFAULT NULL,
    availability text DEFAULT NULL,
    skills text DEFAULT NULL,
    status varchar(20) DEFAULT 'new',
    source varchar(50) DEFAULT NULL,
    notes text DEFAULT NULL,
    created_at datetime NOT NULL,
    updated_at datetime DEFAULT NULL,
    PRIMARY KEY (id),
    KEY email (email),
    KEY status (status),
    KEY created_at (created_at)
);
```

### Class Structure

**File:** `includes/free/volunteer-management.php`

```php
class CP_Volunteer_Manager {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_cp_save_volunteer', array($this, 'ajax_save_volunteer'));
        add_action('wp_ajax_nopriv_cp_save_volunteer', array($this, 'ajax_save_volunteer'));
    }
}
```

### Shortcodes

**Volunteer Signup Form:**
```php
[cp_volunteer_form]
```

**Volunteer Portal:**
```php
[cp_volunteer_portal]
```

**Volunteer Leaderboard:**
```php
[cp_volunteer_leaderboard]
```

### Hooks & Filters

**Actions:**
```php
do_action('cp_volunteer_signup_success', $volunteer_id, $volunteer_data);
do_action('cp_volunteer_status_changed', $volunteer_id, $old_status, $new_status);
do_action('cp_volunteer_hours_logged', $volunteer_id, $hours, $activity);
```

**Filters:**
```php
apply_filters('cp_volunteer_form_fields', $fields);
apply_filters('cp_volunteer_interests', $interests);
apply_filters('cp_volunteer_availability_options', $options);
apply_filters('cp_volunteer_email_content', $content, $volunteer_id);
```

### Admin Interface

Located at: **Campaign Data → Volunteers**

Features:
- Search and filtering
- Bulk actions (update status, export, delete)
- Individual volunteer editing
- CSV export
- Status tracking
- Source tracking

### Frontend Forms

**Signup Form Features:**
- Name, email, phone collection
- ZIP code for location targeting
- Interest checkboxes (canvassing, phone banking, events, etc.)
- Availability selection (weekday/weekend times)
- Skills/experience textarea
- Source tracking via hidden field or URL parameter

**Volunteer Portal Features:**
- Login/registration
- Profile management
- View assigned tasks
- Log volunteer hours
- View leaderboard ranking
- Update availability

### Integration Points

**Email Notifications:**
```php
// Hook into volunteer signup
add_action('cp_volunteer_signup_success', 'my_volunteer_welcome_email', 10, 2);

function my_volunteer_welcome_email($volunteer_id, $data) {
    $volunteer = CP_Volunteer_Manager::get_instance()->get_volunteer($volunteer_id);
    
    wp_mail(
        $volunteer['email'],
        'Welcome to the Campaign!',
        'Thank you for volunteering...'
    );
}
```

**CRM Integration (Premium):**
```php
// Sync volunteers to CRM
add_action('cp_volunteer_signup_success', 'sync_volunteer_to_crm', 10, 2);

function sync_volunteer_to_crm($volunteer_id, $data) {
    if (function_exists('cp_crm')) {
        cp_crm()->contacts->create(array(
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'tags' => array('volunteer'),
            'source' => $data['source']
        ));
    }
}
```

---

## Coding Standards

### Naming Conventions

**Function Prefixes:**
- `campaignpress_` - Legacy functions, WordPress hooks
- `cp_` - Modern helper functions

**Class Prefixes:**
- `CampaignPress_` - Premium classes
- `CP_` - Free feature classes

**Custom Post Types:**
- All use `cp_` prefix (e.g., `cp_issue`, `cp_event`, `cp_volunteer`)

**Text Domain:**
- Always use `'campaign-office'`

### Security

**Input Validation:**
```php
// AJAX handlers
check_ajax_referer('cp_nonce_action', 'nonce');
current_user_can('manage_options');

// Input sanitization
$text = sanitize_text_field($_POST['text']);
$email = sanitize_email($_POST['email']);
$number = intval($_POST['number']);

// Database queries
$wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);
```

**Output Escaping:**
```php
echo esc_html($text);
echo esc_attr($attribute);
echo esc_url($url);
echo wp_kses_post($html);
echo esc_js($javascript);
```

---

## Contributing

When contributing to CampaignPress:

1. **Fork the repository**
2. **Create a feature branch** (`git checkout -b feature/amazing-feature`)
3. **Follow coding standards** (WordPress, PSR-2)
4. **Test your changes** thoroughly
5. **Document your code** with inline comments
6. **Commit your changes** (`git commit -m 'Add amazing feature'`)
7. **Push to the branch** (`git push origin feature/amazing-feature`)
8. **Open a Pull Request**

### Pull Request Checklist

- [ ] Follows WordPress coding standards
- [ ] Includes proper security measures
- [ ] Has been tested in multiple scenarios
- [ ] Includes inline documentation
- [ ] Updates relevant documentation files
- [ ] No PHP warnings or errors
- [ ] Tested in multiple browsers
- [ ] Backwards compatible (or documents breaking changes)

---

## Troubleshooting

### Common Issues

**Blocks not showing in editor:**
- Check that block registration is in `includes/free/gutenberg-blocks.php`
- Verify `block.json` exists and is valid JSON
- Run `npm run build` to compile block JavaScript
- Clear browser cache and WordPress object cache

**Heroicons not displaying:**
- Verify icons exist in `assets/icons/` directory
- Check file names match exactly (case-sensitive)
- Ensure CSS file is enqueued (`assets/css/heroicons.css`)
- Inspect browser console for 404 errors

**Premium features not loading:**
- Verify `CAMPAIGNPRESS_DEV_MODE` is `true` in functions.php
- Or check license is activated and valid
- Check `includes/premium/` files exist
- Enable `WP_DEBUG` to see errors

**Database tables not created:**
- Check WordPress database user has CREATE TABLE permission
- Look for errors in debug.log
- Manually run activation hooks
- Check table prefix is correct

### Debug Mode

Enable debugging in `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

Check logs at: `wp-content/debug.log`

---

## Resources

- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [theme.json Reference](https://developer.wordpress.org/themes/advanced-topics/theme-json/)
- [Heroicons Official Site](https://heroicons.com/)
- [Vite Documentation](https://vitejs.dev/)
- [React Documentation](https://react.dev/)

---

**Happy Coding!**
