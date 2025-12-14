# CampaignPress Architecture

> **Reading Time: 5 minutes**
> This document explains how the pieces fit together. Read this before adding new modules.

## 1. High-Level Philosophy
CampaignPress is a **Hybrid WordPress Theme** acting as a SaaS platform.
- **Base Layer**: Standard WordPress Theme (GPL).
- **Logic Layer**: Modular "Features" (Free vs Premium).
- **Design Layer**: `theme.json` driven (FSE-hybrid).

The goal is to avoid "Theme Bloat" by keeping the core light and loading features only when needed.

## 2. Directory Structure Strategy
The codebase is strictly divided.

```text
campaign-office/
├── theme.json                  <-- DESIGN TRUTH. Edit this for global styles.
├── functions.php               <-- Bootstrapper. Loads Includes.
├── assets/
│   ├── css/design-system-wp69.css <-- The "Engine" (Animations, Utils).
│   └── js/                     <-- Core Logic.
├── includes/
│   ├── free/                   <-- GPL Features (Always Loaded)
│   │   ├── custom-post-types.php  <-- Issues, Events, Team
│   │   ├── gutenberg-blocks.php   <-- Custom Blocks
│   │   └── volunteer-management.php
│   └── premium/                <-- Proprietary SaaS Features (License Gated)
│       ├── crm/                <-- Custom Tables & Contacts
│       ├── compliance/         <-- FEC Reporting
│       └── premium-init.php    <-- License Validator
└── templates/                  <-- HTML Output
```

## 3. The CRM Database (Premium)
When the Premium key is active, we bypass `wp_postmeta` for high-volume data and use custom tables:
1.  `wp_cp_contacts`: The master voter file (50k+ records).
2.  `wp_cp_interactions`: History (calls, emails, donations).
3.  `wp_cp_segments`: Saved complex queries.

**Rule**: Never write direct SQL queries in template files. Use the `CampaignPress_CRM` abstraction layer.

## 4. Initialization Flow
1.  **`functions.php`**:
    - Defines Constants.
    - Loads `includes/free/*.php`.
    - Checks for `includes/premium/premium-init.php`.
2.  **`includes/free/gutenberg-blocks.php`**:
    - Registers Blocks.
    - Enqueues Assets.
3.  **`includes/premium/premium-init.php`** (If exists):
    - Validates License API.
    - If valid -> Loads CRM, API, etc.

## 5. Templating Logic
We use a **Custom Template Loader** (`functions.php` -> `campaignpress_template_loader`).
- **Why?** To keep the root folder clean.
- **How**: It intercepts `single_template` and looks in `templates/custom-post-types/`.

## 6. Security & Performance
- **Headers**: We enforce security headers (X-Frame, X-XSS) via PHP.
- **Escaping**: All output must be escaped (`esc_html`, `esc_attr`).
- **Assets**: Scripts load in footer (`true` flag).
- **Fonts**: Self-hosted (GDPR compliant).

## 7. Design System Access
- **PHP**: Use `wp_get_global_settings()`.
- **JS**: Use `getComputedStyle(document.body).getPropertyValue('--wp--preset--color--primary')`.
- **CSS**: Use `var(--wp--preset--...)`.