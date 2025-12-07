# Implement Full-Width Hero Section

The user wants the hero section on the front page to be full width.
However, the recently added `.site-container` restricts all content, including the hero, to a maximum width of 1200px.
Since the hero is structurally inside this container, we need to use CSS to "break out" of the parent container constraints.

## User Review Required
None.

## Proposed Changes

### Stylesheet

#### [MODIFY] [style.css](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/style.css)
- Add a rule for `.campaign-hero` (or nested `.front-page .campaign-hero`) to force it to full viewport width.
- Use `width: 100vw` and `margin-left: calc(50% - 50vw)` to center and stretch it.

```css
.campaign-hero {
    width: 100vw;
    position: relative;
    left: 50%;
    right: 50%;
    margin-left: -50vw;
    margin-right: -50vw;
    /* Prevent horizontal scroll if scrollbar present (simple fix) */
    max-width: 100vw;
    margin-bottom: 0; /* Reset margin if needed */
}
```
Note: `margin-left: -50vw` can cause horizontal scrollbars on some OSs. Use `width: 100vw` with `calc` is safer or ensure `body { overflow-x: hidden; }`.
A safer modern approach:
```css
.campaign-hero {
    margin-left: calc(50% - 50vw);
    width: 100vw;
}
```

I will add this to the `10.0 - Political Components` section or a specific Hero section if one exists (the hero styles were in `design-system-wp69.css` but we can override or add here). The `design-system-wp69.css` is loaded *after* `style.css` in `functions.php`? Let's check `functions.php` again.
Wait, `style.css` is main theme css. `design-system-wp69.css` is enhanced.
Snippet from previous view:
```php
            wp_enqueue_style(
                'campaignpress-style',
                get_stylesheet_uri(),
                array('bootstrap'),
                CAMPAIGNPRESS_VERSION
            );
            wp_enqueue_style(
                'campaignpress-design-wp69',
                ...,
                array('campaignpress-style'),
                ...
            );
```
`design-system-wp69.css` loads AFTER. So modifying `style.css` might be overridden if the same selectors are used.
However, `design-system-wp69.css` defines `.campaign-hero` appearance (gradients, etc) but maybe not layout width.
I'll add the layout force-width to `style.css` with high specificity or check `design-system-wp69.css` again.
Actually, it's better to add the structural "full width" override in `style.css` or modify `design-system-wp69.css` if that's where the hero is primarily styled. The user edited `design-system-wp69.css` for opacity earlier.
Let's modify `style.css` as it's the "base" layout file now containing `.site-container`.

## Verification Plan

### Manual Verification
1.  **Visual:** Check front page. Hero should touch left and right edges of browser.
2.  **Scroll:** Verify no horizontal scrollbar appears (especially on Windows where scrollbars take space).
