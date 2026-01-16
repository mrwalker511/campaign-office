# Design Reference

**The authoritative style guide for CampaignPress**

Version: 2.1.0 | Last Updated: January 2025

> **Source of Truth:** All design tokens are defined in `theme.json`. This document reflects those values.

---

## Table of Contents

1. [Design System Overview](#design-system-overview)
2. [Color System](#color-system)
3. [Typography](#typography)
4. [Spacing & Layout](#spacing--layout)
5. [Shadows](#shadows)
6. [Components](#components)
7. [Animations](#animations)
8. [Accessibility](#accessibility)
9. [Quick Reference](#quick-reference)

---

## Design System Overview

CampaignPress uses a **WordPress theme.json-native design system**. All tokens are defined in `theme.json` and automatically available in:

- **Block Editor** - Visual controls for colors, fonts, spacing
- **CSS** - Via `--wp--preset--*` variables
- **PHP** - Via `CP_Theme_JSON_Helper` class

### Design Philosophy

**Political Campaign Energy:**
- **Trust & Authority** - Navy primary, professional typography
- **Energy & Momentum** - Orange accents, dynamic animations
- **Accessibility** - WCAG AA compliant, reduced motion support
- **Professionalism** - Consistent system, polished details

---

## Color System

### Primary Palette (Navy Blue)

| Token | Hex | Usage |
|-------|-----|-------|
| `primary-50` | #e6f0ff | Light backgrounds |
| `primary-100` | #b3d4ff | Hover backgrounds |
| `primary-200` | #80b8ff | Borders |
| `primary-300` | #4d9dff | Accent borders |
| `primary-400` | #1a81ff | Links (light mode) |
| **`primary`** | **#14213d** | **Main brand color (Navy)** |
| `primary-600` | #101b32 | Hover states |
| `primary-700` | #0d1527 | Active states |
| `primary-800` | #0a101d | Dark backgrounds |
| `primary-900` | #060a12 | Darkest |

### Accent Palette (Orange)

| Token | Hex | Usage |
|-------|-----|-------|
| `accent-50` | #fff4e6 | Light backgrounds |
| `accent-100` | #ffe0b3 | Hover backgrounds |
| `accent-200` | #ffcc80 | Borders |
| `accent-300` | #ffb84d | Highlights |
| `accent-400` | #ffa41a | Secondary CTAs |
| **`accent`** | **#ff8800** | **CTAs, highlights** |
| `accent-600` | #e67a00 | Hover states |
| `accent-700` | #cc6c00 | Active states |
| `accent-800` | #995100 | Dark accents |
| `accent-900` | #331a00 | Darkest |

### Neutral Palette (Grays)

| Token | Hex | Usage |
|-------|-----|-------|
| `neutral-50` | #f8f9fa | Page backgrounds |
| `neutral-100` | #f1f3f5 | Card backgrounds |
| `neutral-200` | #e9ecef | Borders |
| `neutral-300` | #dee2e6 | Dividers |
| `neutral-400` | #ced4da | Disabled borders |
| `neutral-500` | #adb5bd | Placeholder text |
| `neutral-600` | #6c757d | Secondary text |
| `neutral-700` | #495057 | Body text |
| `neutral-800` | #343a40 | Headings |
| `neutral-900` | #212529 | Primary text |

### Semantic Colors

| Token | Hex | Usage |
|-------|-----|-------|
| `success` | #28a745 | Confirmations, completed |
| `warning` | #ffc107 | Alerts, cautions |
| `error` | #dc3545 | Errors, destructive |
| `info` | #17a2b8 | Information |

### Party Color Schemes

| Scheme | Primary | Secondary | Gradient |
|--------|---------|-----------|----------|
| **Democrat** | #14213d (Navy) | #ff8800 | `democrat-gradient` |
| **Republican** | #C4232C (Red) | #ffd700 | `republican-gradient` |
| **Independent** | #6554c0 (Purple) | #00b8d9 | `independent-gradient` |

### CSS Usage

```css
.my-component {
    color: var(--wp--preset--color--primary);
    background: var(--wp--preset--color--neutral-50);
    border-color: var(--wp--preset--color--accent);
}
```

---

## Typography

### Font Families

| Token | Font | Usage |
|-------|------|-------|
| `display` | Playfair Display | Headlines, hero text, H1-H3 |
| `body` | Inter | Body copy, UI text, forms |
| `mono` | System Monospace | Code, statistics, data |

**CSS Usage:**
```css
h1 { font-family: var(--wp--preset--font-family--display); }
p { font-family: var(--wp--preset--font-family--body); }
code { font-family: var(--wp--preset--font-family--mono); }
```

### Font Sizes (Fluid)

All sizes use `clamp()` for automatic mobile-to-desktop scaling:

| Token | Min | Max | Usage |
|-------|-----|-----|-------|
| `xs` | 0.75rem (12px) | 0.875rem (14px) | Fine print |
| `sm` | 0.875rem (14px) | 1rem (16px) | Captions |
| `base` | 1rem (16px) | 1.125rem (18px) | Body text |
| `lg` | 1.125rem (18px) | 1.375rem (22px) | Lead text |
| `xl` | 1.25rem (20px) | 1.75rem (28px) | H4-H5 |
| `2-xl` | 1.5rem (24px) | 2.25rem (36px) | H3 |
| `3-xl` | 2rem (32px) | 3.5rem (56px) | H2 |
| `4-xl` | 2.5rem (40px) | 5rem (80px) | H1 |

**CSS Usage:**
```css
h1 { font-size: var(--wp--preset--font-size--4-xl); }
h2 { font-size: var(--wp--preset--font-size--3-xl); }
h3 { font-size: var(--wp--preset--font-size--2-xl); }
p { font-size: var(--wp--preset--font-size--base); }
```

### Typography Scale

| Element | Font | Size | Weight | Line Height |
|---------|------|------|--------|-------------|
| H1 | Display | 4-xl | 800 | 1.2 |
| H2 | Display | 3-xl | 700 | 1.2 |
| H3 | Display | 2-xl | 700 | 1.2 |
| H4 | Display | xl | 600 | 1.2 |
| H5 | Display | lg | 600 | 1.2 |
| H6 | Display | base | 600 | 1.2 |
| Body | Body | base | 400 | 1.75 |

---

## Spacing & Layout

### Spacing Scale (8px Grid)

| Token | Size | Pixels | Usage |
|-------|------|--------|-------|
| `1` | 0.25rem | 4px | Tiny gaps |
| `2` | 0.5rem | 8px | Small spacing |
| `3` | 0.75rem | 12px | Compact |
| `4` | 1rem | 16px | Base unit |
| `5` | 1.25rem | 20px | Medium |
| `6` | 1.5rem | 24px | Section padding |
| `8` | 2rem | 32px | Large gaps |
| `10` | 2.5rem | 40px | Section spacing |
| `12` | 3rem | 48px | Large sections |
| `16` | 4rem | 64px | Hero padding |
| `20` | 5rem | 80px | Major sections |
| `24` | 6rem | 96px | Hero sections |

**CSS Usage:**
```css
.card {
    padding: var(--wp--preset--spacing--6);
    margin-bottom: var(--wp--preset--spacing--8);
    gap: var(--wp--preset--spacing--4);
}
```

### Layout Sizes

| Property | Value | Usage |
|----------|-------|-------|
| Content Width | 800px | Blog posts, articles |
| Wide Width | 1200px | Full-width sections |
| Block Gap | 1.5rem | Default spacing between blocks |

### Border Radius

| Token | Size | Usage |
|-------|------|-------|
| `sm` | 0.25rem | Small elements |
| `md` | 0.5rem | Buttons, inputs |
| `lg` | 0.75rem | Cards |
| `xl` | 1rem | Modals |
| `2xl` | 1.5rem | Large cards |
| `full` | 9999px | Pills, avatars |

---

## Shadows

| Token | Value | Usage |
|-------|-------|-------|
| `sm` | 0 1px 2px rgba(0,0,0,0.05) | Subtle elevation |
| `md` | 0 4px 6px rgba(0,0,0,0.1) | Cards |
| `lg` | 0 10px 15px rgba(0,0,0,0.1) | Hover states |
| `xl` | 0 20px 25px rgba(0,0,0,0.1) | Modals |
| `2-xl` | 0 25px 50px rgba(0,0,0,0.25) | Popovers |
| `inner` | inset 0 2px 4px rgba(0,0,0,0.05) | Pressed states |

**CSS Usage:**
```css
.card {
    box-shadow: var(--wp--preset--shadow--md);
}
.card:hover {
    box-shadow: var(--wp--preset--shadow--lg);
}
```

---

## Components

### Buttons

**Primary Button:**
```css
.btn-primary {
    background: var(--wp--preset--color--primary);
    color: var(--wp--preset--color--white);
    font-family: var(--wp--preset--font-family--body);
    font-size: var(--wp--preset--font-size--base);
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    padding: 1rem 2rem;
    border: none;
    cursor: pointer;
}

.btn-primary:hover {
    background: var(--wp--preset--color--primary-700);
}
```

**Accent Button:**
```css
.btn-accent {
    background: var(--wp--preset--color--accent);
    color: var(--wp--preset--color--neutral-900);
}
```

### Cards

```css
.card {
    background: var(--wp--preset--color--white);
    padding: var(--wp--preset--spacing--8);
    box-shadow: var(--wp--preset--shadow--md);
    transition: transform 250ms ease, box-shadow 250ms ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--wp--preset--shadow--lg);
}
```

### Forms

```css
.form-input {
    font-family: var(--wp--preset--font-family--body);
    font-size: var(--wp--preset--font-size--base);
    padding: var(--wp--preset--spacing--3) var(--wp--preset--spacing--4);
    border: 2px solid var(--wp--preset--color--neutral-300);
    transition: border-color 250ms ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--wp--preset--color--primary);
    box-shadow: 0 0 0 3px var(--wp--preset--color--primary-100);
}
```

---

## Animations

### Timing

| Token | Duration | Usage |
|-------|----------|-------|
| `fast` | 150ms | Micro-interactions |
| `base` | 250ms | Buttons, hovers |
| `slow` | 350ms | Modals, panels |
| `bounce` | 500ms | Playful effects |

All use `cubic-bezier(0.4, 0, 0.2, 1)` easing.

### Common Patterns

**Staggered Hero Reveal:**
```css
.hero-title { animation: fadeInUp 600ms ease-out 0ms both; }
.hero-subtitle { animation: fadeInUp 600ms ease-out 200ms both; }
.hero-cta { animation: fadeInUp 600ms ease-out 400ms both; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
```

**Card Hover:**
```css
.card {
    transition: transform 250ms ease-out, box-shadow 250ms ease-out;
}
.card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: var(--wp--preset--shadow--xl);
}
```

**Button Pulse:**
```css
.btn-pulse {
    animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 var(--wp--preset--color--accent); }
    50% { box-shadow: 0 0 0 10px transparent; }
}
```

### Reduced Motion

Always respect user preferences:
```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

---

## Accessibility

### Color Contrast

All color combinations meet WCAG 2.1 AA standards (4.5:1 minimum).

### Focus States

```css
:focus-visible {
    outline: 3px solid var(--wp--preset--color--accent);
    outline-offset: 2px;
}
```

### Screen Readers

```html
<button aria-label="Close dialog">
    <span aria-hidden="true">&times;</span>
</button>
```

### Keyboard Navigation

- All interactive elements focusable
- Logical tab order
- Skip links for navigation
- Visible focus indicators

---

## Quick Reference

### Most Common Patterns

**Section:**
```css
.section {
    padding: var(--wp--preset--spacing--16) 0;
}
```

**Heading:**
```css
h2 {
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--3-xl);
    color: var(--wp--preset--color--primary);
    margin-bottom: var(--wp--preset--spacing--6);
}
```

**Container:**
```css
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--wp--preset--spacing--6);
}
```

### PHP Helper Class

```php
// Get color
$primary = CP_Theme_JSON_Helper::get_color('primary');

// Get font family
$display_font = CP_Theme_JSON_Helper::get_font_family('display');

// Get spacing
$spacing = CP_Theme_JSON_Helper::get_spacing('8');

// Get all colors
$colors = CP_Theme_JSON_Helper::get_all_colors();
```

---

## Files

| File | Purpose |
|------|---------|
| `theme.json` | Source of truth for all design tokens |
| `assets/css/design-tokens.css` | CSS variable bridge (`--cp-*` aliases) |
| `assets/css/animations.css` | Animation keyframes and utilities |
| `includes/free/class-theme-json-helper.php` | PHP helper for accessing tokens |

---

## External Resources

- [WordPress Theme JSON Reference](https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Color Contrast Checker](https://webaim.org/resources/contrastchecker/)

---

**Version:** 2.1.0
**Last Updated:** January 2025
**Source of Truth:** `theme.json`
