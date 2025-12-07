# Force Full-Width Layout on Homepage

The user reports the sidebar/layout issue persists despite removing `get_sidebar()`.
This implies either:
1.  CSS layout rules are reserving space for a sidebar column (e.g., Grid/Flexbox).
2.  The "Sidebar" content is actually coming from another source (e.g., Footer widgets appearing as sidebar, or `home.php` being used).

To definitively fix the layout, we will apply CSS that forces the main content area to 100% width on the homepage and hides any potential sidebar containers.

## User Review Required
None.

## Proposed Changes

### Stylesheet

#### [MODIFY] [style.css](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/style.css)
- Add rules to force full width for the front page content area.
- Add a fail-safe rule to hide any sidebar (`#secondary`) on the homepage, just in case.

```css
/* Force Homepage Full Width */
.front-page.content-area,
.home.content-area,
.front-page #primary,
.home #primary {
    width: 100% !important;
    max-width: 100% !important;
    flex: 0 0 100% !important;
    float: none !important;
    margin: 0 !important;
}

/* Hide Sidebar on Homepage (Fail-safe) */
.home #secondary,
.front-page #secondary {
    display: none !important;
}
```

## Verification Plan

### Manual Verification
1.  **Layout:** Verify the main content area spans the full width of the container/screen.
2.  **Sidebar:** Verify no sidebar column or content is visible on the homepage.
