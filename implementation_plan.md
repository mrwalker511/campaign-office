# Implementation Plan: Scalable Design System with Tailwind CSS

## Goal Description
Evolve the current Tailwind setup into a comprehensive, scalable Design System. This involves configuring Tailwind with strict tokens, setting up a mobile-first responsive strategy, and creating a library of "Layout Modules" (Organisms) for rapid page building.

## User Review Required
> [!NOTE]
> We are installing `tailwindcss`, `postcss`, and `autoprefixer` as dev dependencies. Ensure your Node environment matches the project requirements.

## Proposed Changes

### Configuration
#### [MODIFY] [package.json](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/package.json)
- Add `tailwindcss`, `postcss`, `autoprefixer` to `devDependencies`.
- Update `scripts` to include a `watch` or `dev` command that runs Tailwind.

#### [MODIFY] [tailwind.config.js](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/tailwind.config.js)
- Define `colors`, `fontFamily`, `spacing`, and `screens` (mobile-first) to avoid arbitrary values.
- Enable `jit` (Just-In-Time) mode if not default (it is in v3).

#### [NEW] [postcss.config.js](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/postcss.config.js)
- proper configuration for Tailwind and Autoprefixer.

### Assets
#### [NEW] [assets/css/app.css](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/assets/css/app.css)
- Tailwind directives: `@tailwind base; @tailwind components; @tailwind utilities;`

### Layout Modules (Organisms)
We will create a new directory `parts/organisms` to house these reusable PHP components.

#### [NEW] [parts/organisms/hero.php](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/parts/organisms/hero.php)
- Full-width hero section with headline, subheadline, CTA, and background image/color.

#### [NEW] [parts/organisms/feature-grid.php](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/parts/organisms/feature-grid.php)
- Responsive grid (1 col mobile, 3 col desktop) for features/benefits.

#### [NEW] [parts/organisms/content-section.php](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/parts/organisms/content-section.php)
- Standard text content section with container constraints and typography prose.

#### [NEW] [parts/organisms/cta.php](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/parts/organisms/cta.php)
- High-impact Call to Action band.

#### [NEW] [parts/organisms/testimonials.php](file:///C:/Users/Matt%20Walker/Desktop/wp/campaign-office/parts/organisms/testimonials.php)
- Carousel or Grid of testimonials.

## Verification Plan
### Automated Tests
- Run `npm run build` to ensure CSS is generated without errors.
- Check generated `style.css` (or output file) for Tailwind utility classes.
