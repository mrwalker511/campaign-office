# design System & Layout Modules

## Overview
We have implemented a scalable Design System using Tailwind CSS v4 and a library of PHP Layout Modules (Organisms). This setup allows for rapid page building while maintaining design consistency.

## Configuration
- **Tailwind Config**: `tailwind.config.js` defines the core tokens (Colors, Typography, Spacing).
- **CSS Entry**: `assets/css/app.css` (Imported via `assets/js/main.js`).
- **Build System**: Vite (via `npm run build`).

## Usage

### 1. Using Layout Modules (Organisms)
Layout modules are located in `parts/organisms/`. To use them in your theme templates:

```php
<?php get_template_part('parts/organisms/hero'); ?>
<?php get_template_part('parts/organisms/feature-grid'); ?>
<?php get_template_part('parts/organisms/content-section'); ?>
```

### 2. Design Tokens
Always use the defined tokens instead of arbitrary values.

**Colors:**
- `bg-brand-900` (Primary Dark)
- `text-accent-600` (Secondary Action)
- `bg-neutral-50` (Light Backgrounds)

**Typography:**
- `font-serif` (Headlines: Merriweather)
- `font-sans` (Body: Inter)

**Spacing:**
- Use standard Tailwind spacing (`p-4`, `m-8`) or the extended grid gaps.

### 3. Development
Run the development server:
```bash
npm run dev
```

Build for production:
```bash
npm run build
```

## Module Library

| Module | File | Purpose |
|--------|------|---------|
| **Hero** | `parts/organisms/hero.php` | Full-width impact section with dual CTAs. |
| **Feature Grid** | `parts/organisms/feature-grid.php` | 3-column grid for policies/features. |
| **Content** | `parts/organisms/content-section.php` | Text-heavy section with optional sidebar. |
| **CTA** | `parts/organisms/cta.php` | High-conversion action band. |
| **Testimonials** | `parts/organisms/testimonials.php` | Social proof grid. |
