# CampaignPress Font Strategy

## ✅ System Fonts - No Files Needed!

**CampaignPress now uses system fonts** for optimal performance, privacy, and user experience.

## Why System Fonts?

### Performance Benefits
- **Zero load time** - Fonts are already on the user's device
- **No external requests** - Faster initial page load
- **No bandwidth usage** - Reduces data costs for mobile users
- **Instant rendering** - No FOUT (Flash of Unstyled Text)

### Privacy & Compliance
- **100% GDPR compliant** - No external font CDN requests
- **No tracking** - No third-party connections
- **User privacy first** - No data shared with font providers

### User Experience
- **Familiar typography** - Uses fonts users are accustomed to
- **Native feel** - Matches the operating system aesthetic
- **Better readability** - Optimized for each platform

## Font Stack Details

CampaignPress uses modern, cross-platform system font stacks:

### Display & Body Text
```css
-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
'Helvetica Neue', Arial, sans-serif,
'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'
```

**What users see:**
- **macOS/iOS:** San Francisco (Apple's native font)
- **Windows:** Segoe UI (Microsoft's modern font)
- **Android:** Roboto (Google's system font)
- **Linux:** System default or DejaVu Sans
- **Fallback:** Arial (universal)

### Monospace (Code Blocks)
```css
ui-monospace, 'Cascadia Code', 'Source Code Pro',
Menlo, Consolas, 'DejaVu Sans Mono', monospace
```

**What users see:**
- **macOS:** Menlo or SF Mono
- **Windows:** Cascadia Code or Consolas
- **Linux:** DejaVu Sans Mono or Liberation Mono
- **Modern browsers:** ui-monospace (automatic system choice)

## WordPress Best Practice

WordPress core and the default themes (Twenty Twenty-Four, etc.) use system fonts for:
- Performance
- Accessibility
- User familiarity
- Reduced complexity

CampaignPress follows this best practice.

## Need Custom Fonts?

If you want to use custom web fonts for brand consistency, you have options:

### Option 1: Use Google Fonts (via CDN)

**Pros:** Easy setup, extensive font library
**Cons:** GDPR concerns, external dependency

1. Uncomment the preconnect code in `/includes/free/font-preconnect.php`
2. Update `theme.json` fontFamilies to reference your chosen fonts
3. Optionally enqueue Google Fonts stylesheet

**Note:** Using Google Fonts CDN may not comply with WordPress.org requirements and GDPR regulations in some jurisdictions.

### Option 2: Self-Host Custom Fonts

**Pros:** GDPR compliant, full control, no external dependencies
**Cons:** Requires font file management, licensing consideration

1. Download fonts from [Google Fonts](https://fonts.google.com/) or [google-webfonts-helper](https://gwfh.mranftl.com/fonts)
2. Convert to WOFF2 format (if needed)
3. Place font files in this directory
4. Update `theme.json` fontFamilies with @font-face declarations or font URLs
5. Ensure font license allows redistribution (OFL, MIT, etc.)

### Option 3: Use WordPress Font Library (WP 6.5+)

WordPress 6.5+ includes a Font Library feature:

1. Go to **Appearance → Editor → Styles → Typography → Manage fonts**
2. Upload your font files
3. WordPress handles font management automatically
4. Fonts are stored in `/wp-content/uploads/fonts/`

This is the **recommended approach** for custom fonts in modern WordPress!

## Current Font Stack Reference

The theme currently uses these system font stacks (configured in `theme.json`):

**Display & Body:**
```
-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
'Helvetica Neue', Arial, sans-serif,
'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'
```

**Monospace:**
```
ui-monospace, 'Cascadia Code', 'Source Code Pro',
Menlo, Consolas, 'DejaVu Sans Mono', monospace
```

## Customization Example

To add your own brand fonts, edit `theme.json`:

```json
{
  "settings": {
    "typography": {
      "fontFamilies": [
        {
          "fontFamily": "'YourBrandFont', -apple-system, sans-serif",
          "slug": "display",
          "name": "Brand Display",
          "fontFace": [
            {
              "fontFamily": "YourBrandFont",
              "fontWeight": "400 700",
              "fontStyle": "normal",
              "fontStretch": "normal",
              "src": [ "file:./assets/fonts/your-font.woff2" ]
            }
          ]
        }
      ]
    }
  }
}
```

## For Theme Reviewers

✅ **Current Status:** Theme uses system fonts - no external font dependencies.

**WordPress.org Compliance:**
- ✅ No external font CDN requests
- ✅ No licensing concerns
- ✅ GDPR compliant
- ✅ Optimal performance
- ✅ No font files to review

---

**Last Updated:** 2025-01-08
**Theme Version:** 2.0.0
