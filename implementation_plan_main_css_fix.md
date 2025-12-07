# Fix Sidebar Layout Stubbornness

The user reports the sidebar persists. Removing `get_sidebar()` should have removed the element, so the "sidebar" is likely "sidebar *space*" reserved by the layout grid.
I suspect `assets/css/main.css` contains the grid definitions that lock `#primary` to a specific width (e.g., 70% or fixed width) regardless of the sidebar's existence.

## User Review Required
None.

## Proposed Changes

### Stylesheet

#### [MODIFY] [assets/css/main.css](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/assets/css/main.css)
- Locate the layout definitions for `#primary` and `#secondary`.
- Add specific overrides for `.front-page #primary` to force `width: 100%` and reset margins.
- If the layout uses a flex container on `#content` or `.site-content`, ensure it allows the child to expand.

```css
body.front-page #primary,
body.home #primary {
    width: 100%;
    max-width: 100%;
    flex: 0 0 100%;
    border: none; /* Remove any border separators */
    margin: 0;
}
```

## Verification Plan

### Manual Verification
1.  **Browser:** Verify the homepage main content takes up the entire available width.
