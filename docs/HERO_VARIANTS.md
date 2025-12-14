# Hero Section Variants

## Overview
Three distinct hero section variants built with the Design System tokens. All variants are fully responsive and accessible.

## Variants

### 1. Center Aligned (`hero-center.php`)
**Use Case**: Maximum impact landing pages, campaign announcements.

**Features**:
- Full-screen height with centered content
- Background image with gradient overlay
- Large serif headline with accent color span
- Dual CTA buttons (primary/secondary)
- Scroll indicator animation
- Mobile-first responsive typography

**Usage**:
```php
<?php get_template_part('parts/organisms/hero-center'); ?>
```

---

### 2. Split Screen (`hero-split.php`)
**Use Case**: Storytelling, candidate introduction, detailed messaging.

**Features**:
- 50/50 split layout (text left, image right)
- Stacks vertically on mobile (image first)
- Social proof stats below CTAs
- Floating event badge on image
- Responsive aspect ratios

**Usage**:
```php
<?php get_template_part('parts/organisms/hero-split'); ?>
```

---

### 3. Immersive (`hero-immersive.php`)
**Use Case**: High-energy campaigns, video-first storytelling.

**Features**:
- Full-screen video background (MP4/WebM)
- Glassmorphism card (bottom-left positioning)
- Backdrop blur effects
- Live event indicator with pulse animation
- Mute toggle button
- Gradient overlay for readability

**Usage**:
```php
<?php get_template_part('parts/organisms/hero-immersive'); ?>
```

**Video Requirements**:
- Place videos in `assets/videos/`
- Provide both `.mp4` and `.webm` formats
- Recommended: 1920x1080, 30fps, compressed

---

## Design Tokens Used

All variants strictly use Design System tokens:

- **Colors**: `brand-*`, `accent-*`, `neutral-*`
- **Typography**: `font-serif`, `font-sans`
- **Spacing**: Standard Tailwind scale
- **Breakpoints**: `md:`, `lg:` (mobile-first)

## Accessibility

✓ ARIA labels on all interactive elements  
✓ Focus states with ring utilities  
✓ Semantic HTML (`<section>`, `<h1>`)  
✓ Alt text on images  
✓ Keyboard navigation support
