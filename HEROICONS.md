# Heroicons Integration

This document describes the integration of Heroicons into the CampaignPress theme.

## Overview

Heroicons are beautiful, hand-crafted SVG icons by the makers of Tailwind CSS. We've integrated 316 MIT-licensed icons into the theme to enhance the visual design and user experience.

## What's Been Updated

### 1. Icon Library Setup
- **Location**: `assets/icons/`
- Downloaded and organized Heroicons in multiple sizes:
  - 24px (outline and solid)
  - 20px (mini/solid)
  - 16px (micro/solid)
- Added custom social media brand icons in `assets/icons/social/`

### 2. Helper Functions
- **File**: `includes/free/heroicons.php`
- **Functions**:
  - `campaignpress_get_heroicon()` - Get any Heroicon SVG
  - `campaignpress_heroicon()` - Echo Heroicon SVG
  - `campaignpress_get_social_heroicon()` - Get social media icons
  - `campaignpress_get_status_badge()` - Get status badges with icons
  - `campaignpress_get_ui_icon()` - Get common UI icons
  - `campaignpress_ui_icon()` - Echo UI icons

### 3. CSS Styling
- **File**: `assets/css/heroicons.css`
- Includes:
  - Base Heroicon styles
  - Size variants (micro, mini, outline, solid, sm, md, lg, xl)
  - Icon positioning in buttons and links
  - Social media icon styles
  - Enhanced badge styles with icons
  - Status indicators
  - Action buttons with icons
  - Icon lists and grids
  - Responsive sizing
  - Dark mode support
  - Accessibility focus states

### 4. Template Updates

#### Social Media Links
- **File**: `includes/free/template-tags.php`
- **Function**: `campaignpress_social_links()`
- Replaced Dashicons with custom social media Heroicons
- Maintained accessibility with proper aria-labels

#### Event Details
- **File**: `includes/free/template-tags.php`
- **Function**: `campaignpress_event_details()`
- Updated calendar icon (calendar)
- Updated location icon (map-pin)

#### Custom Blocks
1. **Mission Control Block** (`blocks/mission-control/render.php`)
   - Weather icon updated to Heroicon cloud

2. **Policy Platform Block** (`blocks/policy-platform/render.php`)
   - Expand/collapse icon (chevron-down)
   - Support/thumbs-up icon (hand-thumb-up)
   - Download PDF icon (arrow-down-tray)

## Usage Examples

### Basic Icon

```php
// Get an outline icon
echo campaignpress_get_heroicon('calendar', 'outline', array(
    'class' => 'my-custom-class',
    'aria-label' => 'Calendar'
));

// Echo a solid icon
campaignpress_heroicon('heart', 'solid', array(
    'aria-hidden' => 'true'
));
```

### UI Icons

```php
// Common UI icons with preset mappings
echo campaignpress_get_ui_icon('calendar');    // calendar
echo campaignpress_get_ui_icon('location');    // map-pin
echo campaignpress_get_ui_icon('download');    // arrow-down-tray
echo campaignpress_get_ui_icon('user');        // user
echo campaignpress_get_ui_icon('settings');    // cog-6-tooth
```

### Social Media Icons

```php
// Social media icons
echo campaignpress_get_social_heroicon('facebook', array(
    'aria-hidden' => 'true',
    'width' => '24',
    'height' => '24'
));
```

### Status Badges

```php
// Badge with icon
echo campaignpress_get_status_badge('success', 'Active');
echo campaignpress_get_status_badge('warning', 'Pending');
echo campaignpress_get_status_badge('danger', 'Cancelled');
echo campaignpress_get_status_badge('info', 'Processing');

// Custom badge with specific icon
echo campaignpress_get_status_badge('success', 'Completed', 'check-badge');
```

## Icon Styles

Heroicons come in 4 styles:

1. **Outline** (24px, 1.5px stroke) - Default for UI elements
2. **Solid** (24px, filled) - Good for emphasis
3. **Mini** (20px, solid) - Perfect for badges and small spaces
4. **Micro** (16px, solid) - Tiny icons for dense UIs

## Available UI Icon Types

The `campaignpress_get_ui_icon()` function provides shortcuts for commonly used icons:

- **Calendar & Time**: calendar, clock
- **Location**: location, map
- **Actions**: download, upload, edit, delete, add, remove
- **Search & Filter**: search, filter
- **Settings**: settings
- **User**: user, users
- **Favorites**: heart, star, flag, bookmark
- **Sharing**: share, link
- **Communication**: phone, email, chat, notification
- **Files**: document, folder, image, video, audio
- **Analytics**: chart, dashboard
- **UI Controls**: menu, close, check, info, warning, error, success
- **Navigation**: expand, collapse, next, previous, refresh, external

## CSS Classes

### Size Classes
- `.heroicon-micro` - 16px
- `.heroicon-mini` - 20px
- `.heroicon-outline` - 24px
- `.heroicon-solid` - 24px
- `.heroicon-sm` - 18px
- `.heroicon-md` - 24px
- `.heroicon-lg` - 32px
- `.heroicon-xl` - 48px

### Color Classes
- `.heroicon-primary`
- `.heroicon-secondary`
- `.heroicon-success`
- `.heroicon-warning`
- `.heroicon-danger`
- `.heroicon-info`
- `.heroicon-muted`

### Special Effects
- `.heroicon-spin` - Rotating animation (for loading states)

## Badge Variants

Status badges come with pre-styled variants:

- `cp-badge-success` - Green (active, success states)
- `cp-badge-warning` - Orange (pending, caution states)
- `cp-badge-danger` - Red (error, cancelled states)
- `cp-badge-info` - Blue (informational states)
- `cp-badge-active` - Green (active items)
- `cp-badge-pending` - Orange (pending items)
- `cp-badge-completed` - Blue (completed items)
- `cp-badge-cancelled` - Red (cancelled items)
- `cp-badge-new` - Purple (new items)
- `cp-badge-contacted` - Green (contacted items)

## Future Enhancements

### Admin Area Updates (To Do)
The following admin areas still use Dashicons and can be updated to Heroicons:

1. **Premium Admin Pages**
   - System Status Page
   - Features Page
   - License Page
   - Upgrade Page

2. **Premium Modules**
   - Developer Console
   - Compliance Dashboard
   - Field Operations
   - Volunteer Management
   - CRM Interface
   - Analytics Dashboard
   - Design Studio

3. **Free Features**
   - Analytics Dashboard
   - Volunteer Portal
   - Donation Enhancements
   - Campaign Communications

### Implementation Guide for Admin Areas

To update admin areas:

1. Replace `<span class="dashicons dashicons-[icon-name]"></span>` with:
   ```php
   <?php echo campaignpress_get_heroicon('[icon-name]', 'outline', array('aria-hidden' => 'true')); ?>
   ```

2. For common icons, use the UI icon helper:
   ```php
   <?php echo campaignpress_get_ui_icon('[type]', array('aria-hidden' => 'true')); ?>
   ```

3. For status badges, replace manual HTML with:
   ```php
   <?php echo campaignpress_get_status_badge('success', 'Active'); ?>
   ```

## Accessibility

All Heroicons implementations include proper accessibility attributes:

- `aria-hidden="true"` for decorative icons
- `aria-label` with `role="img"` for meaningful icons
- Proper focus states for interactive elements

## Performance

- SVG icons are inlined (no additional HTTP requests)
- Icons are only loaded when needed
- No icon fonts to download
- Optimized SVG files (from Heroicons optimized build)

## Browser Support

Heroicons SVGs work in all modern browsers:
- Chrome/Edge 88+
- Firefox 78+
- Safari 14+
- All browsers with SVG support

## Resources

- [Heroicons Official Site](https://heroicons.com/)
- [Heroicons GitHub](https://github.com/tailwindlabs/heroicons)
- [Heroicons MIT License](https://github.com/tailwindlabs/heroicons/blob/master/LICENSE)
