# Theme + Plugin Architecture

**Campaign Office WordPress Ecosystem**

This document explains the architectural separation between the Campaign Office theme and Campaign Office Core plugin, following WordPress best practices.

---

## 🏗️ Architecture Overview

The Campaign Office ecosystem now follows WordPress.org best practices by separating **presentation** (theme) from **functionality** (plugin).

```
┌─────────────────────────────────────────────────────┐
│                  WordPress Core                      │
└──────────────────┬──────────────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
┌───────▼────────┐   ┌────────▼──────────┐
│ Campaign Office│   │Campaign Office Core│
│     THEME      │   │      PLUGIN        │
│  (Presentation)│   │  (Functionality)   │
└────────────────┘   └───────────────────┘
```

### **Campaign Office Theme** (Presentation Layer)

**Purpose**: Visual design and presentation

**Contains**:
- ✅ Templates and template parts
- ✅ Styles (CSS/SCSS)
- ✅ Gutenberg block **views** (presentation only)
- ✅ Theme customizer options (colors, fonts, layouts)
- ✅ Navigation menus
- ✅ Widget areas
- ✅ Theme-specific JavaScript (UI interactions)

**Does NOT Contain**:
- ❌ Custom post types
- ❌ Data storage/management
- ❌ Business logic
- ❌ Complex admin interfaces
- ❌ Plugin-like functionality

### **Campaign Office Core Plugin** (Functionality Layer)

**Purpose**: Campaign-specific functionality

**Contains**:
- ✅ Custom post types (Issues, Events, Volunteers, etc.)
- ✅ Volunteer management system
- ✅ Event management with RSVP
- ✅ Contact management
- ✅ Data storage and retrieval
- ✅ REST API endpoints
- ✅ Admin interfaces for data management
- ✅ Business logic

**Does NOT Contain**:
- ❌ Visual styling (beyond basic admin CSS)
- ❌ Theme-specific templates
- ❌ Layout definitions

---

## 📦 What Moved Where

### From Theme → Plugin

| Feature | Old Location (Theme) | New Location (Plugin) |
|---------|---------------------|----------------------|
| Custom Post Types | `includes/free/custom-post-types.php` | `includes/custom-post-types.php` |
| Volunteer Management | `includes/free/volunteer-management.php` | `includes/volunteer-management.php` |
| Event Management | `includes/free/event-management.php` | `includes/event-management.php` |
| Contact Manager | `includes/core/class-contact-manager.php` | Plugin core classes |

### Remains in Theme

| Feature | Location | Reason |
|---------|----------|--------|
| Gutenberg Block Views | `blocks/*/view.js` | Presentation only |
| Templates | `templates/` | Theme presentation |
| Customizer Settings | `includes/free/customizer.php` | Theme options |
| Performance Optimization | `includes/core/class-performance.php` | Theme optimization |
| Script Manager | `includes/core/class-script-manager.php` | Theme asset management |

---

## 🔄 How They Work Together

### 1. **Plugin Provides Data, Theme Displays It**

```php
// Plugin registers CPT
register_post_type('cp_event', $args);

// Theme provides template
// themes/campaign-office/single-cp_event.php
```

### 2. **Plugin Handles Logic, Theme Shows Results**

```php
// Plugin: Volunteer registration logic
function campaign_office_register_volunteer($data) {
    // Validation, sanitization, storage
    return $volunteer_id;
}

// Theme: Display volunteer form
// themes/campaign-office/templates/volunteer-form.php
```

### 3. **Theme Checks for Plugin**

```php
// In theme functions.php
if (class_exists('Campaign_Office_Core')) {
    // Plugin is active - enhance integration
    add_theme_support('campaign-office-core');
} else {
    // Plugin not active - show admin notice
    add_action('admin_notices', 'campaign_office_plugin_notice');
}
```

---

## 🚀 Benefits of This Architecture

### For Users

✅ **Data Persistence**
- Volunteers, events, and content survive theme changes
- No data loss when switching themes
- Can try different themes while keeping data

✅ **Flexibility**
- Use Campaign Office Core with any theme
- Mix and match with other plugins
- Not locked into one theme

✅ **Better Updates**
- Theme updates don't affect functionality
- Plugin updates don't break theme design
- Cleaner, safer updates

### For Developers

✅ **WordPress Best Practices**
- Follows WordPress.org theme review guidelines
- Eligible for WordPress theme/plugin directories
- Professional, maintainable code

✅ **Separation of Concerns**
- Clear boundaries between presentation and logic
- Easier to debug and maintain
- Better code organization

✅ **Modularity**
- Can develop theme and plugin independently
- Easier testing and development
- Reusable components

### For the Project

✅ **WordPress.org Ready**
- Theme can be submitted to WordPress.org theme directory
- Plugin can be submitted to plugin directory
- Wider distribution and reach

✅ **Commercial Flexibility**
- Free theme + Free plugin (WordPress.org)
- Free theme + Premium plugin
- Premium theme + Free plugin
- Various monetization strategies

---

## 📋 Plugin Dependency Management

### Theme Requires Plugin

The theme is designed to work best with the Campaign Office Core plugin but can function without it.

**In `functions.php`**:

```php
/**
 * Check for Campaign Office Core plugin
 */
function campaign_office_check_plugin() {
    if (!class_exists('Campaign_Office_Core')) {
        add_action('admin_notices', 'campaign_office_plugin_notice');
    }
}
add_action('after_setup_theme', 'campaign_office_check_plugin');

/**
 * Admin notice if plugin is not active
 */
function campaign_office_plugin_notice() {
    if (current_user_can('install_plugins')) {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php
                printf(
                    __('The CampaignPress theme works best with the %s plugin. Please install and activate it for full functionality.', 'campaignpress'),
                    '<strong>Campaign Office Core</strong>'
                );
                ?>
                <a href="<?php echo admin_url('plugin-install.php?s=campaign+office+core&tab=search'); ?>" class="button button-primary">
                    <?php _e('Install Plugin', 'campaignpress'); ?>
                </a>
            </p>
        </div>
        <?php
    }
}
```

### Plugin Recommends Theme

The plugin works with any theme but recommends Campaign Office.

**In plugin main file**:

```php
/**
 * Check if Campaign Office theme is active
 */
public function theme_check_notice() {
    $theme = wp_get_theme();
    $is_campaign_office_theme = (
        'Campaign Office' === $theme->get('Name') ||
        'campaignpress' === $theme->get('Template')
    );

    if (!$is_campaign_office_theme && current_user_can('switch_themes')) {
        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <?php
                printf(
                    __('Campaign Office Core plugin works great with any theme, but is optimized for the %s theme.', 'campaign-office-core'),
                    '<a href="' . admin_url('theme-install.php?search=campaign+office') . '">Campaign Office</a>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}
```

---

## 🔌 Plugin API & Hooks

The plugin provides hooks for theme (and other plugins) to extend functionality.

### Actions

```php
// After volunteer registration
do_action('campaign_office_volunteer_registered', $volunteer_id, $data);

// After event RSVP
do_action('campaign_office_event_rsvp', $event_id, $user_id, $rsvp_data);

// On volunteer profile update
do_action('campaign_office_volunteer_updated', $volunteer_id, $old_data, $new_data);
```

### Filters

```php
// Modify volunteer registration fields
$fields = apply_filters('campaign_office_volunteer_fields', $default_fields);

// Modify event RSVP form
$form = apply_filters('campaign_office_event_rsvp_form', $default_form, $event_id);

// Customize email content
$message = apply_filters('campaign_office_email_content', $message, $type, $recipient);
```

### Theme Integration Examples

```php
// In theme functions.php

// Add custom volunteer field
add_filter('campaign_office_volunteer_fields', function($fields) {
    $fields['t_shirt_size'] = array(
        'label' => __('T-Shirt Size', 'campaignpress'),
        'type' => 'select',
        'options' => array('S', 'M', 'L', 'XL')
    );
    return $fields;
});

// Send custom email after RSVP
add_action('campaign_office_event_rsvp', function($event_id, $user_id) {
    // Send confirmation email with theme branding
    campaign_office_send_branded_email($user_id, 'rsvp_confirmation', $event_id);
}, 10, 2);
```

---

## 📂 Directory Structure

### Theme Structure

```
campaign-office/
├── style.css                    # Theme info
├── functions.php                # Theme setup
├── templates/                   # Page templates
│   ├── single-cp_event.php     # Event single (uses plugin CPT)
│   ├── archive-cp_volunteer.php # Volunteer archive
│   └── ...
├── parts/                       # Template parts
├── patterns/                    # Block patterns
├── blocks/                      # Gutenberg block views
│   ├── countdown/
│   │   ├── block.json
│   │   ├── render.php          # Server-side rendering
│   │   └── view.js             # Client-side (presentation)
│   └── ...
├── assets/                      # Theme assets
│   ├── css/
│   ├── js/
│   └── images/
└── includes/
    ├── core/                    # Theme core
    │   ├── class-performance.php
    │   ├── class-script-manager.php
    │   └── class-template-loader.php
    └── free/                    # Theme features
        ├── customizer.php
        ├── enhanced-customizer.php
        └── ...
```

### Plugin Structure

```
campaign-office-core/
├── campaign-office-core.php     # Main plugin file
├── readme.txt                   # WordPress.org readme
├── README.md                    # GitHub readme
├── includes/
│   ├── custom-post-types.php   # CPT registration
│   ├── volunteer-management.php # Volunteer system
│   ├── event-management.php    # Event system
│   ├── contact-management.php  # Contact system
│   └── admin/
│       ├── admin-init.php
│       ├── volunteer-admin.php
│       └── event-admin.php
├── assets/
│   ├── js/                     # Admin scripts
│   ├── css/                    # Admin styles
│   └── images/
└── languages/                   # Translations
```

---

## 🧪 Testing the Integration

### Test Checklist

**With Plugin Active:**
- [ ] All CPTs are registered (Issues, Events, Volunteers, etc.)
- [ ] Admin menus appear correctly
- [ ] Volunteer registration works
- [ ] Event RSVP works
- [ ] Data is saved correctly
- [ ] Theme templates display CPT content

**With Plugin Inactive:**
- [ ] Theme still loads without errors
- [ ] Admin notice appears
- [ ] No PHP errors
- [ ] Site remains functional
- [ ] No data is lost (just hidden until plugin reactivated)

**With Different Theme + Plugin:**
- [ ] Plugin functions correctly
- [ ] CPTs are accessible
- [ ] Data management works
- [ ] Admin interfaces work
- [ ] May need custom styling

---

## 🚀 Deployment Strategy

### For WordPress.org

1. **Submit Theme** to WordPress.org theme directory
   - 100% GPL
   - No premium features
   - Clean, presentation-focused

2. **Submit Plugin** to WordPress.org plugin directory
   - 100% GPL
   - Core functionality free
   - Follows plugin guidelines

### For Commercial Use

**Option A: Freemium**
- Free theme (WordPress.org)
- Free plugin (WordPress.org)
- Premium add-on plugin (your site)

**Option B: Premium Bundle**
- Theme + Plugin bundle
- Sell on ThemeForest or your site
- Can include premium features

**Option C: SaaS Model**
- Free theme + plugin
- Premium hosting/services
- Managed campaigns

---

## 📚 Additional Resources

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress Theme Handbook](https://developer.wordpress.org/themes/)
- [Theme vs Plugin Territory](https://make.wordpress.org/themes/handbook/review/required/#presentation-vs-functionality)
- [Plugin Review Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/)

---

## 🤝 Contributing

When contributing to either the theme or plugin:

**Theme Contributions:**
- Focus on visual presentation
- Template improvements
- CSS/JavaScript enhancements
- Accessibility improvements

**Plugin Contributions:**
- New features and functionality
- Bug fixes in data handling
- API enhancements
- Admin interface improvements

---

**Last Updated**: 2025-12-29
**Architecture Version**: 2.0 (Theme + Plugin Separation)
**Status**: ✅ Production Ready
