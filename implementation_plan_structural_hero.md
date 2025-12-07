# Implement Full-Width Hero (Structural)

The user rejected the "breakout" CSS approach and requires a robust full-width hero section.
The cleanest way to achieve this is to remove the specific width constraint (`.site-container`) from the main wrapper on the front page, allowing the Hero to naturally fill the screen. We then manually apply the constraint to the text content below the hero.

## User Review Required
None.

## Proposed Changes

### Template Files

#### [MODIFY] [header.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/header.php)
- Change `<div id="content" class="site-content site-container" role="main">` to logical PHP.
- IF `is_front_page()`, use `class="site-content"`.
- ELSE, use `class="site-content site-container"`.

#### [MODIFY] [front-page.php](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/front-page.php)
- Locate the loop where `<article>` is output.
- Wrap `<article ...> ... </article>` in `<div class="site-container"> ... </div>`.
- This ensures the Hero (which is above article) is full width, but the article content is centered/padded.

## Verification Plan

### Manual Verification
1.  **Front Page:** Verify Hero is full width (edge to edge). Verify text content below is centered/1200px max.
2.  **Inner Pages:** Verify they still have the `.site-container` applied from header (centered/padded).
