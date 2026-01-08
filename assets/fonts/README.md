# CampaignPress Font Files

## ⚠️ IMPORTANT: Font Files Required

The font files in this directory are currently **placeholder files (0 bytes)** and must be replaced with actual font files before the theme will display correctly.

## Required Fonts

This theme uses three variable fonts for optimal performance and typography:

### 1. Bricolage Grotesque (Display Font)
- **File:** `BricolageGrotesque-Variable.woff2`
- **Usage:** Headings and display text
- **License:** SIL Open Font License 1.1
- **Download:** [Google Fonts](https://fonts.google.com/specimen/Bricolage+Grotesque)

### 2. Plus Jakarta Sans (Body Font)
- **File:** `PlusJakartaSans-Variable.woff2`
- **Usage:** Body text and UI elements
- **License:** SIL Open Font License 1.1
- **Download:** [Google Fonts](https://fonts.google.com/specimen/Plus+Jakarta+Sans)

### 3. JetBrains Mono (Monospace Font)
- **File:** `JetBrainsMono-Variable.woff2`
- **Usage:** Code blocks and technical content
- **License:** SIL Open Font License 1.1
- **Download:** [Google Fonts](https://fonts.google.com/specimen/JetBrains+Mono)

## How to Add Fonts

### Option 1: Download from Google Fonts (Recommended)

1. Visit each font's Google Fonts page (links above)
2. Click "Download family"
3. Extract the ZIP file
4. Convert TTF/OTF files to WOFF2 format using:
   - [CloudConvert](https://cloudconvert.com/ttf-to-woff2)
   - [Font Squirrel WebFont Generator](https://www.fontsquirrel.com/tools/webfont-generator)
5. For variable fonts, select the file ending in `-Variable.ttf`
6. Convert to WOFF2 and rename to match the filenames above
7. Replace the placeholder files in this directory

### Option 2: Use google-webfonts-helper

1. Visit [google-webfonts-helper](https://gwfh.mranftl.com/fonts)
2. Search for each font name
3. Select "modern browsers" charset
4. Select "Variable" if available
5. Download the WOFF2 file
6. Rename to match the required filename
7. Replace the placeholder files in this directory

### Option 3: Use System Fonts (Alternative)

If you prefer to avoid licensing concerns or want faster load times, you can modify `theme.json` to use system fonts:

```json
{
  "settings": {
    "typography": {
      "fontFamilies": [
        {
          "fontFamily": "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
          "slug": "display",
          "name": "System Display"
        },
        {
          "fontFamily": "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
          "slug": "body",
          "name": "System Body"
        },
        {
          "fontFamily": "'Courier New', Courier, monospace",
          "slug": "mono",
          "name": "System Mono"
        }
      ]
    }
  }
}
```

## Font Loading Performance

### Current Setup (Self-Hosted)
✅ GDPR compliant (no external requests)
✅ No dependency on third-party CDNs
✅ Complete control over caching
❌ Requires manual font file management

### Alternative: Google Fonts CDN
If you prefer to use Google Fonts CDN:

1. Remove the WOFF2 files from this directory
2. Uncomment the preconnect code in `/includes/free/font-preconnect.php`
3. Update `theme.json` to reference Google Fonts URLs
4. Note: This may not comply with WordPress.org theme requirements

## License Compliance

All three fonts are licensed under the **SIL Open Font License 1.1**, which allows:
- ✅ Free use in commercial projects
- ✅ Modification and redistribution
- ✅ Bundling with themes

**Important:** You must include the font license file with your theme distribution. Download the OFL.txt file from each font's repository.

## Troubleshooting

### Fonts not loading?

1. **Check file size:** Files should be ~50-200KB each, not 0 bytes
2. **Check file format:** Must be `.woff2` format
3. **Check permissions:** Files should be readable (chmod 644)
4. **Clear cache:** Clear WordPress cache and browser cache
5. **Check console:** Open browser DevTools and check for font loading errors

### Want to use different fonts?

1. Choose your fonts (must be GPL-compatible or OFL)
2. Update `theme.json` fontFamilies section
3. Update this README with new font information
4. Add the new WOFF2 files to this directory

## File Size Guidelines

- **Variable fonts:** 100-200KB each (recommended)
- **Static fonts:** 20-50KB each subset
- **Total fonts:** Should not exceed 500KB for performance

## For Theme Reviewers

⚠️ **Current Status:** Font files are placeholders (0 bytes) and must be replaced before theme distribution.

**Before submitting to WordPress.org or commercial marketplaces:**
1. Add actual font files (verify licensing)
2. Include font license files (OFL.txt)
3. Test typography rendering in theme demo
4. Verify GDPR compliance (self-hosted fonts)
5. Check total theme ZIP size remains under 10MB

---

**Last Updated:** 2025-01-08
**Theme Version:** 2.0.0
