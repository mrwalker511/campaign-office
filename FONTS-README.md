# CampaignPress Font Files - Important Information

## ⚠️ CRITICAL: Font Files Required

The CampaignPress theme uses three custom variable fonts for its distinctive design:

1. **Bricolage Grotesque** (Display/Headings)
2. **Plus Jakarta Sans** (Body Text)
3. **JetBrains Mono** (Monospace/Code)

## Current Status

The font files in `assets/fonts/` are currently **empty placeholder files (0 bytes)**. This means:

- ❌ Custom fonts will not load
- ❌ Theme will fall back to system fonts
- ❌ Design will look significantly different from screenshots
- ❌ Professional typography will be missing

## Why Are Fonts Missing?

Due to licensing considerations, the actual font files cannot be included in the repository. You must obtain them separately.

## How to Add Fonts

### Option 1: Download Free Fonts (Recommended)

All three fonts are available for free:

1. **Bricolage Grotesque**
   - Download from: https://fonts.google.com/specimen/Bricolage+Grotesque
   - Select "Download family" → Get the Variable font file
   - Rename to: `BricolageGrotesque-Variable.woff2`

2. **Plus Jakarta Sans**
   - Download from: https://fonts.google.com/specimen/Plus+Jakarta+Sans
   - Select "Download family" → Get the Variable font file
   - Rename to: `PlusJakartaSans-Variable.woff2`

3. **JetBrains Mono**
   - Download from: https://fonts.google.com/specimen/JetBrains+Mono
   - Select "Download family" → Get the Variable font file
   - Rename to: `JetBrainsMono-Variable.woff2`

### Option 2: Convert TTF to WOFF2

If you download TTF files:

1. Use an online converter: https://cloudconvert.com/ttf-to-woff2
2. Or use a local tool like `fonttools`:
   ```bash
   pip install fonttools brotli
   pyftsubset font.ttf --output-file=font.woff2 --flavor=woff2
   ```

### Installation Steps

1. Download the three font files as `.woff2` format
2. Place them in `/assets/fonts/` directory (replacing the empty placeholders)
3. Ensure file names match exactly:
   - `BricolageGrotesque-Variable.woff2`
   - `PlusJakartaSans-Variable.woff2`
   - `JetBrainsMono-Variable.woff2`
4. Test by viewing your site - custom fonts should now load

## Verification

To verify fonts are loading:

1. Open your website in a browser
2. Open DevTools (F12) → Network tab
3. Reload the page
4. Filter by "Font" - you should see three .woff2 files loading
5. Check the size - each should be 50KB-200KB (not 0KB)

## Alternative: Use System Fonts

If you prefer to use system fonts (smaller file size, faster loading):

1. Open `theme.json`
2. Modify the `fontFamilies` section to use system font stacks:
   ```json
   "fontFamilies": [
     {
       "fontFamily": "-apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
       "slug": "display",
       "name": "System Sans"
     }
   ]
   ```

## License Information

All three fonts are licensed under the **Open Font License (OFL)**:
- ✅ Free for personal and commercial use
- ✅ Can be bundled with software
- ✅ Can be modified
- ❌ Cannot be sold standalone

Always verify license terms before distribution.

## Need Help?

If you encounter issues:
- Check font file sizes (should not be 0 bytes)
- Verify file names match exactly (case-sensitive)
- Clear browser cache after adding fonts
- Check browser DevTools Console for error messages

For support: https://campaignpress.com/support
