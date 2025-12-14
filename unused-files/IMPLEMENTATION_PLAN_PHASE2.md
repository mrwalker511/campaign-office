# Implementation Plan - Political Studio Phase 2 (Templates)

**Goal:** Create specialized Block Templates to enable a true "Page Builder" experience without plugins.

## 1. Landing Page Template (`templates/landing-page.html`)
*   **Purpose:** High-conversion pages (Squeeze pages, Donation drives).
*   **Structure:**
    *   No global Header/Footer.
    *   Minimal content area centered.
    *   Ready for "Hero" or "Petition" patterns.
*   **Registration:** Add to `theme.json` customTemplates.

## 2. Block-Based Homepage (`templates/home.html`)
*   **Purpose:** A default starting point using our new patterns.
*   **Structure:**
    *   Header
    *   Hero-Political Pattern
    *   News Ticker Pattern
    *   Policy Grid Pattern
    *   Donation Tiers Pattern
    *   Footer
*   **Note:** Since we have `front-page.php` (Classic), we will register this as a custom template "Political Home (Block)" so the user can switch optionally, or we can instruct them to assign it.

## 3. Issues Archive (`templates/archive-cp_issue.html`)
*   **Purpose:** Display policy cards dynamically.
*   **Structure:**
    *   Header
    *   Archive Title
    *   Query Loop (using Post Template with Icon + Title)
    *   Footer

## 4. Header & Footer Parts (`parts/header.html`, `parts/footer.html`)
*   **Transition:** Create block-based versions of the PHP header/footer to allow Full Site Editing in the future.
*   **Header:** Row > Logo | Nav | Donate Button (Buttons Block).
*   **Footer:** Columns > Disclaimer (using the pattern or custom block) | Social | Nav.

## 5. Execution Steps
1.  Create `templates/` and `parts/` directories if missing.
2.  Register templates in `theme.json`.
3.  Create HTML files with block markup.
