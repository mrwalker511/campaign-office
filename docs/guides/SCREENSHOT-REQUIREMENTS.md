# Screenshot Requirements for CampaignPress Theme

## Current Status

⚠️ **IMPORTANT:** The current screenshot (`screenshot.png`) is **1024x1024** which does not meet WordPress.org requirements.

## WordPress.org Requirements

### Dimensions
- **Required Size:** 1200 × 900 pixels (4:3 aspect ratio)
- **File Format:** PNG or JPEG
- **File Size:** Under 1MB (recommended under 500KB)
- **Filename:** `screenshot.png` or `screenshot.jpg`

### Content Guidelines

The screenshot should:
1. **Show the actual theme** (not a mockup or marketing material)
2. **Represent the default theme appearance** (with demo content)
3. **Be clear and readable** at thumbnail size
4. **Not include browser chrome** (no browser window frames)
5. **Not include WordPress admin UI** (frontend only)
6. **Not contain text like "DEMO" or watermarks**

## What to Show

### Recommended Content for CampaignPress Screenshot

Your screenshot should feature:

1. **Homepage Hero Section** (top portion)
   - Campaign logo
   - Compelling headline
   - Call-to-action button
   - Background image or color

2. **Key Content Sections** (middle/bottom)
   - Featured issues (2-3 cards)
   - Upcoming events preview
   - Donate/volunteer CTAs
   - Team member showcase

### Layout Suggestion

```
┌─────────────────────────────────────┐
│  [LOGO]    NAVIGATION MENU          │ ← Header
│                                      │
│  COMPELLING CAMPAIGN HEADLINE        │
│  Subheadline about the candidate    │ ← Hero Section
│  [Donate Button] [Learn More]       │
│                                      │
├─────────────────────────────────────┤
│  OUR KEY ISSUES                      │
│  [Issue 1]  [Issue 2]  [Issue 3]    │ ← Issues Preview
│                                      │
├─────────────────────────────────────┤
│  UPCOMING EVENTS                     │
│  • Town Hall Meeting - Jan 15       │ ← Events Preview
│  • Volunteer Phone Bank - Jan 18    │
│                                      │
└─────────────────────────────────────┘
```

## How to Create the Screenshot

### Option 1: Full-Page Screenshot Tool

1. **Set up demo site:**
   - Install WordPress locally
   - Activate CampaignPress theme
   - Import demo content
   - Choose "Democrat Blue" color scheme

2. **Capture screenshot:**
   - Use browser extension: [Full Page Screen Capture](https://chrome.google.com/webstore/detail/full-page-screen-capture/)
   - Or use [Firefox Screenshot](https://screenshots.firefox.com/)
   - Navigate to your homepage
   - Take full-page screenshot

3. **Edit in image editor:**
   - Open in Photoshop, GIMP, or [Photopea](https://www.photopea.com/)
   - Crop to show best content (hero + 2-3 sections)
   - Resize canvas to 1200 × 900 pixels
   - Use "Content Aware Fill" or smart cropping
   - Adjust quality to ~70-80% for file size

### Option 2: Browser DevTools

1. **Open browser DevTools** (F12)
2. **Set responsive mode** to 1200 × 900
3. **Hide scrollbars** (CSS: `body { overflow: hidden; }`)
4. **Take screenshot:**
   - Chrome: Cmd/Ctrl + Shift + P → "Capture screenshot"
   - Firefox: Right-click → "Take Screenshot"
5. **Save as PNG**

### Option 3: Online Screenshot Service

1. Visit [Screely](https://screely.com/) or [Screenshot.rocks](https://screenshot.rocks/)
2. Upload a browser screenshot of your homepage
3. Remove browser chrome
4. Adjust to 1200 × 900
5. Download PNG

## Best Practices

### DO:
- ✅ Show actual rendered theme with content
- ✅ Use high-quality demo images
- ✅ Demonstrate key theme features
- ✅ Use the default color scheme (Democrat Blue)
- ✅ Ensure text is readable
- ✅ Show responsive, professional design
- ✅ Include branding elements (logo, colors)

### DON'T:
- ❌ Use lorem ipsum text
- ❌ Show broken images or layouts
- ❌ Include browser UI elements
- ❌ Add marketing text overlays
- ❌ Show admin dashboard
- ❌ Use copyrighted images without permission
- ❌ Exceed 1MB file size

## File Optimization

After creating the screenshot:

1. **Optimize PNG:**
   - Use [TinyPNG](https://tinypng.com/)
   - Use [ImageOptim](https://imageoptim.com/) (Mac)
   - Use `pngquant` CLI tool

2. **Check file size:**
   ```bash
   ls -lh screenshot.png
   ```
   Should be under 1MB (preferably 300-500KB)

3. **Verify dimensions:**
   ```bash
   file screenshot.png
   ```
   Should show: `PNG image data, 1200 x 900`

## Example Screenshots

For inspiration, view these well-rated WordPress theme screenshots:
- [Twenty Twenty-Four](https://wordpress.org/themes/twentytwentyfour/)
- [Astra](https://wordpress.org/themes/astra/)
- [GeneratePress](https://wordpress.org/themes/generatepress/)

## Commercial Marketplaces

If submitting to ThemeForest or other commercial marketplaces, you may need:

- **Additional screenshots** (8-10 images showing different pages/features)
- **Promotional banner** (590 × 300 pixels)
- **Preview images** (multiple sizes)

See each marketplace's specific requirements.

## WordPress.org Validation

Before submitting, verify your screenshot:

```bash
# Check file size
stat -f%z screenshot.png

# Check dimensions (requires ImageMagick)
identify screenshot.png
```

## Submission Checklist

Before submitting theme:
- [ ] Screenshot is exactly 1200 × 900 pixels
- [ ] File size is under 1MB
- [ ] Shows actual theme with real content (not mockup)
- [ ] No browser chrome or UI elements
- [ ] No watermarks or "DEMO" text
- [ ] Image is optimized and compressed
- [ ] Represents default theme appearance
- [ ] Text is readable at thumbnail size

## Current Screenshot Status

**File:** `screenshot.png`
**Current Size:** 1024 × 1024 pixels ❌
**Required Size:** 1200 × 900 pixels
**Status:** Needs to be regenerated

## Action Required

1. Set up local demo site with CampaignPress
2. Import demo content
3. Capture homepage at 1200 × 900 pixels
4. Replace existing `screenshot.png`
5. Verify file is under 1MB
6. Test theme listing appearance

---

**Last Updated:** 2025-01-08
**Related Files:** `screenshot.png`, `README.md`, `readme.txt`
