# Implementation Plan - Political Studio Phase 5 (Editor UX)

**Goal:** Transform the native Gutenberg experience to feel like a high-performance, visual page builder ("Elementor-like"), focusing on speed and usability.

## 1. Architecture
*   **Script:** `assets/js/editor-overrides.js` (Enqueued in block editor).
*   **Styles:** `assets/css/editor-overrides.css` (Visual styling for UI components).
*   **Dependencies:** `wp-plugins`, `wp-edit-post`, `wp-element`, `wp-components`, `wp-data`, `wp-compose`.

## 2. Key Features

### A. Block Toolbar Redesign (Visual Icons)
*   **Implementation:** CSS overrides to hide text labels and emphasize icons.
*   **JS:** Unregister standard block styles that clutter the UI if needed.
*   **UX:** Floating toolbar positioned closer to the block, high contrast.

### B. Template Library Modal
*   **Implementation:** A custom Button in the top toolbar (using `MainDashboardButton` or `PluginSidebarMoreMenuItem`).
*   **Feature:** Opens a Modal displaying our custom patterns (`patterns/*.php`).
*   **Action:** Clicking a pattern inserts it into the canvas.

### C. Inline Style Editor Popover
*   **Implementation:** Use `wp.hooks.addFilter` on `BlockControls`.
*   **Feature:** Add a "Paintbrush" icon to the floating toolbar.
*   **Interaction:** Opens a `Popover` with range sliders for:
    *   Padding/Margin
    *   Typography (Size)
    *   Block Gap

### D. Live Responsive Preview
*   **Implementation:** Add a "Device Toggle" segment to the top toolbar (Desktop | Tablet | Mobile).
*   **Action:** Triggers `wp.data.dispatch('core/edit-post').__experimentalSetPreviewDeviceType`.

### E. Visual Undo/Redo
*   **Implementation:** Enhance native buttons via CSS to look like a timeline or granular steps.

## 3. Execution Steps
1.  **Setup:** Create assets and enqueue in `functions.php`.
2.  **Styling:** Write CSS to "Elementor-ize" the UI (dark mode panels, vibrant active states).
3.  **Scripting:**
    *   Register `CampaignPressToolbar` plugin.
    *   Implement `StylePopover` component.
    *   Implement `PatternLibrary` modal logic.

## 4. Verification
*   Open Editor.
*   Check Toolbar appearance.
*   Open Style Popover and test slider application to block style.
*   Open Template Library and insert a pattern.
