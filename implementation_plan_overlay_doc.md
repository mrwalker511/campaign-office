# Fix Video Hero Overlay Opacity

The video hero section has an overlay (`.hero-background::before`) with an opacity of `0.95`. This high opacity value makes the overlay nearly solid, effectively hiding the video background. The user cannot see their video.

## User Review Required
None.

## Proposed Changes

### CSS Layer

#### [MODIFY] [assets/css/design-system-wp69.css](file:///c:/Users/Matt%20Walker/Desktop/wp/campaign-office/assets/css/design-system-wp69.css)
- Change `opacity: 0.95;` to `opacity: 0.4;` in the `.hero-background::before` rule.
- This will make the overlay semi-transparent, allowing the video to show through.

## Verification Plan

### Manual Verification
- Since I cannot view the browser, I will verify the code change is applied correctly.
- I will rely on the user to visually confirm the fix.
