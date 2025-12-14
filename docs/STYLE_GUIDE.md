# Style Guide Documentation

## Overview
A comprehensive, single-page HTML reference showcasing all Design System components built for the Campaign Office theme.

## File Location
`style-guide.html` (root directory)

## How to Use

### Viewing the Style Guide
1. Open `style-guide.html` in any web browser
2. No build process required—uses Tailwind CDN
3. Fully responsive and interactive

### Navigation
- Sticky navigation at top
- Jump links to sections:
  - Buttons
  - Heroes
  - Feature Grids
  - Content Modules
  - Color Palette

### Copy-Paste Workflow
1. Browse to the component you need
2. Click "View Code" to expand code block
3. Copy the PHP template part or full HTML
4. Paste into your WordPress template

## Sections Included

### 1. Buttons
- Primary Button (brand-600)
- Secondary Button (outlined)
- Accent Button (CTA with icon)

### 2. Hero Sections
- Center-Aligned Hero
- Split-Screen Hero
- Immersive Hero (video background)

Each includes:
- Visual preview
- Description
- Code snippet
- Link to full PHP file

### 3. Feature Grids
- Base Feature Grid
- Hover-Enhanced Grid

Includes live examples with interactive hover states.

### 4. Content Modules
- Long-Form Content layout
- Typography examples
- Blockquote styling
- Callout box variants

### 5. Color Palette
Visual swatches for:
- Brand colors (Teal)
- Accent colors (Rose)
- Neutral colors (Slate)

Each swatch shows the Tailwind class name.

## Features

### Self-Contained
- Uses Tailwind CDN (no build required)
- Google Fonts loaded via CDN
- Inline Tailwind config matches your `tailwind.config.js`

### Interactive
- Expandable code blocks
- Live hover demonstrations
- Smooth scroll navigation

### Responsive
- Mobile-friendly layout
- Responsive component previews
- Touch-friendly navigation

## Customization

### Adding New Components
1. Add a new section following the existing pattern
2. Include visual preview
3. Add code block with syntax highlighting
4. Update navigation links

### Updating Colors
The Tailwind config is inline in the `<script>` tag. Update to match any changes to `tailwind.config.js`.

## Maintenance

Keep this file updated when:
- Adding new components
- Modifying existing components
- Changing Design System tokens
- Adding new color scales

## Benefits

✓ **Quick Reference**: All components in one place  
✓ **Copy-Paste Ready**: Code snippets for every component  
✓ **Visual Documentation**: See components rendered  
✓ **No Build Required**: Open directly in browser  
✓ **Shareable**: Send to team members or clients
