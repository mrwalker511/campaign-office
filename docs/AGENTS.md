# AGENTS.md - Master Instructions

> **"System 2" Thinking Required**
> Before writing code, READ this file. It contains the map, the rules, and the strategy.

## 1. Executive Summary & Mission
**CampaignPress** is a hybrid WordPress theme that bridges the gap between a standard display theme and a Campaign Management System (monolithic app).
- **Goal**: Empower political campaigns with a beautifully designed, high-performance, and feature-rich platform.
- **Core Value**: "Elementor-like power with native Gutenberg speed."
- **Key Constraint**: strict Free vs. Premium code separation.

## 2. The Quick Map
| File/Dir | Purpose |
| :--- | :--- |
| **`docs/ARCHITECTURE.md`** | **READ FIRST.** The system design, Free/Premium split, and Logic. |
| **`docs/STYLEGUIDE.md`** | **STRICT.** The Design System. Use these variables. No magic numbers. |
| **`docs/TECH_STACK.md`** | Versions, dependencies, and environment details. |
| `includes/free/` | **GPL Code.** Always loaded. Basic features (Volunteers, Events). |
| `includes/premium/` | **Proprietary Code.** License-gated. complex systems (CRM, API). |
| `theme.json` | **The Truth.** All design tokens live here. |

## 3. The Golden Rules
1.  **Respect the Gitignore**: Do not touch `includes/premium/` unless you are specifically working on a Pro feature.
2.  **No Vendor Bloat**: We removed Bootstrap CSS/JS. Use `theme.json` variables and native WordPress blocks.
3.  **Strict Typing (Mental)**: While PHP is loose, write defensively. Assume `strict_types=1` logic.
4.  **Accessibility First**: Every interactive element must have focus states and ARIA labels.
5.  **Performance**:
    - No large libraries (jQuery is the only exception, and minimize it).
    - Use `wp_enqueue_script` with `true` (footer) for all JS.
6.  **Design System**:
    - **NEVER** write `color: #0053c3`.
    - **ALWAYS** write `var(--wp--preset--color--primary)`.

## 4. Common Workflows

### A. Creating a New Block
1.  **Register** in `includes/free/gutenberg-blocks.php`.
2.  **Render Callback**: Create the PHP function to output HTML.
3.  **Style**: Use `assets/css/design-system-wp69.css` variables.
4.  **JS**: If needed, add to `assets/js/main.js` (maintain modularity).

### B. Adding a Premium Feature
1.  **Check License**: Wrape code in `if ( CampaignPress_Premium::get_instance()->is_feature_enabled('foo') )`.
2.  **Locate**: Place files in `includes/premium/foo/`.
3.  **Hook**: Initialize via `premium-init.php`.

### C. Styling Updates
1.  **Global**: Edit `theme.json`.
2.  **Specific**: Edit `assets/css/design-system-wp69.css`.
3.  **Critical**: Check `functions.php` -> `campaignpress_inline_critical_css`.

## 5. Agent Protocol
- **Start**: Read `AGENTS.md` and `ARCHITECTURE.md`.
- **Plan**: Create/Update `implementation_plan.md` (or similar) before large changes.
- **Verify**: Check `error_log` or `debug.log` after fundamental PHP changes.
