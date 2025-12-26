# CampaignPress Images Directory

## Purpose

This directory contains placeholder and default images used by the theme's block patterns and templates.

## Missing Placeholder Images

The following placeholder images are referenced by block patterns but are currently missing:

- `hero-placeholder.jpg` - Used by hero section patterns (recommended: 1920x1080px)
- `placeholder.jpg` - Generic placeholder for various patterns (recommended: 800x600px)

## Recommendations

### Option 1: Add Your Own Placeholders

Create simple placeholder images for development:

1. **hero-placeholder.jpg** (1920x1080px)
   - Solid color background with text overlay
   - Example: Blue background with "Your Campaign Hero" text
   - Use free tools like Canva or Photopea

2. **placeholder.jpg** (800x600px)
   - Generic placeholder for content areas
   - Simple gradient or solid color
   - Can include "Campaign Content" watermark

### Option 2: Use Free Stock Photos

Download free political/campaign-appropriate images from:
- Unsplash (https://unsplash.com) - Search "campaign", "rally", "community"
- Pexels (https://www.pexels.com) - Free license, no attribution required
- Pixabay (https://pixabay.com) - Public domain images

**License Requirements:**
- Ensure images are free for commercial use
- Check if attribution is required
- Verify redistribution is allowed

### Option 3: Generate Solid Color Placeholders

Quick command to generate simple placeholders (requires ImageMagick):

```bash
# Hero placeholder (blue gradient)
convert -size 1920x1080 gradient:#0053c3-#003275 hero-placeholder.jpg

# Generic placeholder (gray)
convert -size 800x600 xc:#e9ecef placeholder.jpg
```

### Option 4: Remove Placeholder References

If you don't want placeholder images, you can:
1. Edit block patterns to remove image references
2. Or images will fail gracefully (no image shown)

## Current Status

📁 Directory created: ✅
🖼️ Placeholder images: ❌ (Need to be added)

## Usage in Patterns

Patterns reference images like this:
```php
get_template_directory_uri() . '/assets/images/hero-placeholder.jpg'
```

When users insert patterns, they should replace these placeholders with their own campaign images.

---

**Note:** The theme will work without these images, but block patterns will show broken image icons until users replace them with actual content.
