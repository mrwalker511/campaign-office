# TGM Plugin Activation Library

## Overview

This directory contains the TGM Plugin Activation (TGMPA) library version 2.6.1, which enables CampaignPress to recommend and facilitate easy installation of plugins that enhance campaign functionality.

## What is TGMPA?

TGM Plugin Activation is an industry-standard library used by professional WordPress themes to manage plugin dependencies. It provides:

- **Admin notices** when recommended plugins are not installed
- **One-click bulk installation** from WordPress.org repository
- **One-click bulk activation** of installed plugins
- **Update notifications** for installed plugins
- **Dismissible notices** that respect user preferences

## License

TGMPA is licensed under GPL-2.0+ and is maintained by Thomas Griffin, Gary Jones, and Juliette Reinders Folmer.

## Usage in CampaignPress

The library is integrated via `/includes/free/tgmpa-config.php` which defines:

1. **Recommended Plugins List** - Plugins that enhance CampaignPress functionality
2. **Configuration Settings** - Customized messaging and behavior
3. **Admin Interface** - User-friendly installation interface

## Recommended Plugins

### Essential Campaign Functionality

1. **Contact Form 7** - Contact forms for campaign inquiries
2. **The Events Calendar** - Advanced event management for rallies, town halls, fundraisers
3. **GiveWP** - Professional donation management with reporting

### Email & Marketing

4. **MC4WP: Mailchimp for WordPress** - Email list building and newsletter management
5. **MonsterInsights** - Google Analytics integration for campaign tracking

### Optimization & Security

6. **Yoast SEO** - Search engine optimization for better visibility
7. **Wordfence Security** - Security hardening for campaign websites
8. **WP Fastest Cache** - Performance optimization for faster loading
9. **Really Simple SSL** - SSL/HTTPS enforcement

### Social Engagement

10. **Social Warfare** - Social sharing optimization for viral content

## Developer Notes

### Adding New Plugins

To add additional recommended plugins, edit `/includes/free/tgmpa-config.php` and add to the `$plugins` array:

```php
array(
    'name'     => 'Plugin Name',
    'slug'     => 'plugin-slug',
    'required' => false, // or true for required plugins
    'version'  => '1.0', // minimum version (optional)
),
```

### External Plugins (Not from WordPress.org)

For premium or custom plugins, add the `source` parameter:

```php
array(
    'name'     => 'Premium Plugin',
    'slug'     => 'premium-plugin',
    'source'   => 'https://example.com/plugins/premium-plugin.zip',
    'required' => false,
),
```

### Bundled Plugins

To bundle plugins with the theme (not recommended for themes distributed on WordPress.org):

```php
array(
    'name'     => 'Bundled Plugin',
    'slug'     => 'bundled-plugin',
    'source'   => get_template_directory() . '/plugins/bundled-plugin.zip',
    'required' => false,
),
```

## WordPress.org Theme Guidelines

CampaignPress follows WordPress.org theme review guidelines:

- ✅ All plugins are **recommended**, not **required** (users can dismiss)
- ✅ No force activation or installation
- ✅ Only plugins from WordPress.org repository are included
- ✅ Users maintain full control over plugin decisions
- ✅ Dismissible notices that don't nag

## User Experience Flow

1. **Theme Activation** → Admin notice appears
2. **User clicks "Begin installing plugins"** → Taken to plugin installer
3. **User selects plugins** → Checkboxes for desired plugins
4. **User clicks "Install"** → Bulk installation begins
5. **User clicks "Activate"** → Bulk activation of installed plugins
6. **Done** → Notice disappears (or user can dismiss early)

## Technical Details

- **Location**: `/includes/lib/tgmpa/`
- **Main Class**: `class-tgm-plugin-activation.php`
- **Configuration**: `/includes/free/tgmpa-config.php`
- **Hook**: `tgmpa_register`
- **Function**: `campaignpress_register_required_plugins()`

## Updates

To update TGMPA to a newer version:

1. Download the latest version from: https://github.com/TGMPA/TGM-Plugin-Activation
2. Replace `class-tgm-plugin-activation.php` with the new version
3. Test thoroughly in a development environment
4. Check for any API changes in the changelog

## Resources

- **Official Site**: http://tgmpluginactivation.com/
- **GitHub Repository**: https://github.com/TGMPA/TGM-Plugin-Activation
- **Documentation**: http://tgmpluginactivation.com/configuration/
- **Support**: https://github.com/TGMPA/TGM-Plugin-Activation/issues

## Version

Current TGMPA version: **2.6.1**
