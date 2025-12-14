# Feature Grid Components

## Overview
Flexible, reusable feature grid components built with CSS Grid and card molecules. Two variants available: base and hover-enhanced.

## Variants

### 1. Base Feature Grid (`feature-grid-base.php`)
**Use Case**: Standard content display, policy pages, service listings.

**Features**:
- Responsive CSS Grid: `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`
- Card molecules with icon, headline (H3), body text, and link
- 6 pre-configured SVG icons (education, healthcare, economy, environment, safety, housing)
- Accessible with ARIA labels and focus states
- Clean, minimal styling

**Usage**:
```php
<?php get_template_part('parts/organisms/feature-grid-base'); ?>
```

---

### 2. Hover-Enhanced Feature Grid (`feature-grid-hover.php`)
**Use Case**: Interactive pages, engagement-focused sections, modern UX.

**Features**:
- All base features PLUS:
- **Lift effect**: `-translate-y-1` on hover
- **Shadow enhancement**: `shadow-sm` → `shadow-xl`
- **Icon animation**: Scale + color change on hover
- **Arrow animation**: Slides right on hover
- Smooth transitions (300ms duration)

**Usage**:
```php
<?php get_template_part('parts/organisms/feature-grid-hover'); ?>
```

---

## Card Molecule Structure

Each card contains:

1. **Icon Container** (14×14, rounded-xl, brand colors)
2. **Headline** (H3, text-xl/2xl, bold)
3. **Body Text** (Paragraph, neutral-600)
4. **Learn More Link** (Accent color, arrow icon)

## Customization

### Adding Custom Icons

Edit the `get_feature_icon()` function:

```php
$icons = [
    'your_icon' => '<path d="..."></path>',
];
```

### Changing Data Source

Replace the `$features` array with:
- ACF Repeater Fields
- Custom Post Type query
- WordPress options

Example with ACF:
```php
$features = get_field('priority_features');
```

## Design System Compliance

✓ Uses `grid-cols-*` (no arbitrary grid values)  
✓ Spacing: `p-6`, `md:p-8`, `gap-6`, `md:gap-8`  
✓ Colors: `brand-*`, `accent-*`, `neutral-*`  
✓ Typography: Design System scale  
✓ Breakpoints: Mobile-first (`md:`, `lg:`)

## Accessibility

- Semantic HTML (`<article>`, `<h3>`)
- ARIA labels on links
- Focus states with ring utilities
- SVG icons marked `aria-hidden="true"`
- Sufficient color contrast ratios
