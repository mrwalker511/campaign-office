# Implement Responsive Layout

The user reports that content floats to the edge of the screen.
Analysis shows that `header.php` uses a `.site-container` class for the main content wrapper, but this class is **not defined** in the theme's CSS files (`style.css` or `design-system-wp69.css`).
The `style.css` file has a Table of Contents listing "8.0 - Content", but this section is missing from the file body.

## User Review Required
None.

## Proposed Changes

### Stylesheet

#### [MODIFY] [style.css](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/style.css)
- Insert a new section `/* 8.0 - Content */` before the `10.0 - Political Components` section (or at an appropriate location).
- Define `.site-container`:
    ```css
    .site-container {
        width: 100%;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 1.5rem; /* Responsive padding */
        padding-right: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .site-container {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }
    ```
- This will constrain the content width on large screens and provide breathing room (padding) on mobile devices.

## Verification Plan

### Manual Verification
1.  **Desktop:** Resize browser window. Verify content is centered and max-width is 1200px (not touching edges on wide screens).
2.  **Mobile:** Shrink browser to mobile width. Verify content has padding (1rem) from the edges.
