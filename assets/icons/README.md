# Heroicons Icon Library

## Overview

This directory contains the full [Heroicons](https://heroicons.com/) icon library by Tailwind Labs. This is an intentional design decision to support PHP-rendered blocks and the admin icon picker.

**Size**: 5.2MB (1,300+ SVG files)
**Versions**: 16px (micro), 20px (mini), 24px (outline/solid)

## Why Keep the Full Library?

### Design Decisions

1. **PHP Block Rendering** - PHP-rendered blocks need access to SVG files at render time
2. **Icon Block** - The `campaignpress/icon` block provides users with a comprehensive icon picker
3. **Icons Browser** - Admin UI allows developers/users to browse and select icons
4. **No Build Step** - PHP can read SVG files directly without JavaScript build tools
5. **Design System Consistency** - Heroicons provides a comprehensive, professionally-designed icon set
6. **Future-Proofing** - Having all icons available means users can use any icon without theme updates

### Dual Icon Strategy

The theme uses **two icon systems** for different contexts:

| System | Used In | Why |
|--------|---------|-----|
| **Heroicons (PHP)** | Gutenberg blocks, PHP templates, admin UI | Server-side rendering, no build step required |
| **Lucide React (JS)** | React components (CRM, admin interfaces) | Modern React components with TypeScript support |

This dual approach is intentional:
- **Heroicons**: Best for WordPress block editor and PHP contexts
- **Lucide React**: Best for React/JavaScript contexts with build tooling

## Directory Structure

```
assets/icons/
├── 16/
│   └── solid/          # 400+ micro icons (16×16px)
├── 20/
│   └── solid/          # 400+ mini icons (20×20px)
├── 24/
│   ├── outline/        # 300+ outline icons (24×24px)
│   └── solid/          # 300+ solid icons (24×24px)
├── social/             # Social media icons
└── custom/             # Custom campaign icons
```

## Usage

### In PHP (Blocks & Templates)

```php
// Get an icon SVG
$icon_svg = campaignpress_get_heroicon('calendar', 'outline', [
    'class' => 'w-6 h-6',
    'aria-label' => 'Calendar'
]);

echo $icon_svg;
```

### Style Options

- `outline` - 24px outline style (default)
- `solid` - 24px solid style
- `mini` - 20px solid style
- `micro` - 16px solid style

### In Gutenberg Blocks

The theme's custom blocks use the `campaignpress_get_heroicon()` helper function:

```php
// blocks/icon/render.php
$icon_svg = campaignpress_get_heroicon(
    $attributes['icon'],
    $attributes['iconStyle'],
    $icon_args
);
```

### In Admin UI

The Icons Browser (`includes/free/icons-browser.php`) provides:
- Visual icon picker
- Search functionality
- Copy icon name/SVG
- Category filtering

Access via: **Appearance → Icons Browser**

## Performance Considerations

### Why the Size is Acceptable

1. **Not Loaded on Frontend** - SVG files are read individually as needed, not all loaded at once
2. **Cached by WordPress** - File system reads are fast and can be cached
3. **Excluded from Production Builds** - Distribution packages can optionally exclude unused icons
4. **Minimal Runtime Impact** - Only requested icons are read from disk

### Distribution Optimization

For production distributions, you can optionally:

1. **Remove unused icon sizes** (if only using outline/solid)
2. **Use `.distignore`** to exclude icon directories from WordPress.org distribution
3. **CDN Alternative** - Serve icons from CDN (requires refactoring)

## File Organization

### Heroicons Library Files

- **PHP Helper**: `includes/free/heroicons.php` - Core functions for icon rendering
- **Admin Browser**: `includes/free/icons-browser.php` - Admin UI for icon selection
- **Icon Block**: `blocks/icon/` - Gutenberg block using icon system
- **Custom Icons**: `includes/free/custom-icons.php` - Custom campaign icons

## Alternative Approaches Considered

### Option A: Use Icon Font
❌ **Rejected**: Accessibility issues, harder to customize, larger initial load

### Option B: Use SVG Sprite
❌ **Rejected**: Requires build step, harder to dynamically access from PHP

### Option C: Use Lucide React Everywhere
❌ **Rejected**: Requires JavaScript build for PHP blocks, breaks server-side rendering

### Option D: Load Icons from CDN
❌ **Rejected**: External dependency, privacy concerns, requires internet connection

### ✅ Option E: Include Full Library (Current)
**Selected**: Best balance of developer experience, user experience, and maintainability

## Maintenance

### Updating Icons

To update to a new Heroicons version:

1. Download latest Heroicons release from https://github.com/tailwindlabs/heroicons
2. Extract SVG files to appropriate directories
3. Test icon picker functionality
4. Update version number in `includes/free/heroicons.php`

### Adding Custom Icons

Add custom SVGs to:
- `assets/icons/custom/` - For theme-specific icons
- Use same directory structure as Heroicons (16, 20, 24)
- Follow SVG optimization guidelines

## Documentation

- **Developer Guide**: See `docs/guides/DEVELOPER-GUIDE.md`
- **Custom Icons Guide**: See `docs/guides/CUSTOM_ICONS_GUIDE.md`
- **Icon Block Docs**: See `blocks/icon/README.md`
- **Heroicons Official**: https://heroicons.com/

## License

Heroicons is released under the MIT License by Tailwind Labs.
See: https://github.com/tailwindlabs/heroicons/blob/master/LICENSE

---

**Last Updated**: 2025-01-22
**CampaignPress Version**: 2.1.0
**Heroicons Version**: 2.x
