# Implementation Plan - Political Studio Phase 3 (Custom Blocks)

**Goal:** Implement true interactive blocks using `block.json` metadata (API v3) and native JavaScript/PHP rendering.

## 1. Architecture
*   **Location:** Create `blocks/` directory in the theme root.
*   **Loader:** Update `functions.php` (or `includes/gutenberg-blocks.php`) to auto-register blocks found in this directory.

## 2. Block Candidates
These features require interactivity that simple HTML Patterns cannot provide.

### Block A: Campaign Countdown (`campaignpress/countdown`)
*   **Functionality:** Real-time countdown to a specific date (Election Day).
*   **Attributes:** `targetDate` (string), `labelDays`, `labelHours`, etc.
*   **Frontend:** Lightweight Vanilla JS to update the numbers.
*   **Editor:** Date picker control.

### Block B: Donation Progress Bar (`campaignpress/progress`)
*   **Functionality:** Visual bar showing raised amount vs goal.
*   **Attributes:** `currentAmount` (number), `goalAmount` (number), `currency` (string).
*   **Frontend:** CSS-based width calculation.
*   **Editor:** Range controls for amounts.

## 3. Execution Steps
1.  **Setup Logic:** Create `campaignpress_register_blocks()` function to iterate over `blocks/` folders.
2.  **Scaffold Blocks:** Create folders `blocks/countdown` and `blocks/progress`.
3.  **Implement `block.json`:** Define metadata and attributes.
4.  **Implement Assets:** `style.css`, `view.js` (for countdown), `render.php` (for dynamic rendering).

## 4. Verification
*   Insert blocks into a page.
*   Test editing attributes (Goal change / Date change).
*   Verify frontend updates.
