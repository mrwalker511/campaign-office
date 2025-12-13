# Implementation Plan - Political Studio Upgrade (Phase 1)

**Goal:** Transform CampaignPress into a "Political Studio" native editor, removing reliance on Elementor.

## 1. `theme.json` Architecture
We will upgrade `theme.json` to be the central brain of the design system.
*   **Palettes:** Add semantic palettes for `democrat`, `republican`, `independent`.
*   **Layout:** Ensure `contentSize` and `wideSize` facilitate full-width rows.
*   **Elements:** Style buttons, inputs, and headings globally.

## 2. Pattern Architecture (`/patterns`)
We will move from PHP-based pattern registration to the standard `patterns/` folder (HTML files). This allows easier editing and management.
*   **Action:** Create `patterns/` directory.
*   **Action:** Create 12 `.php` (or `.html` if header enabled) files for the patterns. (Using `.php` allows localization `<?php esc_html_e... ?>`).

## 3. The 12 Political Patterns
We will build these using standard Core blocks (Group, Columns, Image, Heading, Paragraph, Buttons).

| Pattern | Filename | Components |
| :--- | :--- | :--- |
| **Hero** | `hero-political.php` | Cover block, dual buttons (Donate/Volunteer). |
| **Donation** | `donation-tiers.php` | 4-col grid, price styling, shortcode block. |
| **Event Teaser** | `event-teaser.php` | Columns, Date badge styling, RSVP button. |
| **Policy Cards** | `policy-grid.php` | Query Loop or static Column grid with Icons. |
| **Volunteer** | `volunteer-form.php` | Form wrapper (Shortcode/HTML), Skill checkboxes visual. |
| **Press Grid** | `press-kit.php` | Query Loop (Press release), Downloadable Media section. |
| **Testimonials** | `testimonials.php` | Quotes block, Avatar image, Grid/Carousel layout. |
| **Progress** | `progress-tracker.php` | Group with gradient background, text stats. |
| **Countdown** | `countdown-timer.php` | Columns (Days/Hours), large typography. |
| **Petition** | `petition-form.php` | Text area + Form integration. |
| **News Ticker** | `news-ticker.php` | Marquee style group or latest posts list. |
| **Directory** | `staff-directory.php` | Grid of User/Team profiles (Image + Bio). |

## 4. Migration Strategy
*   We will keep `includes/block-patterns.php` only for backward compatibility or deprecate it in favor of the folder.
*   We will ensure the patterns use the classes defined in `design-system-wp69.css` (or migrate those styles to `theme.json`).

## 5. Verification
*   User can insert "Political Hero" from the "+" menu.
*   Colors switch correctly when palette is changed.
