# CampaignPress Block Implementation Guide

## 🎯 Overview

You have **3 production-ready blocks** built:
- ✅ **hero-commander** - Full hero section with typewriter, parallax, animations
- ✅ **donation-form** - Complete fundraising form with tiers, payment processors
- ✅ **progress** - Goal tracker with customizable colors

**7 blocks remaining** to implement using the same patterns.

---

## 📚 **React Block Template**

Use this template for all remaining blocks. Copy from `hero-commander/index.js` or `donation-form/index.js`:

```javascript
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import {
    InspectorControls,
    RichText,
    useBlockProps
} from '@wordpress/block-editor';
import {
    PanelBody,
    TextControl,
    ToggleControl,
    ColorPalette,
    BaseControl
} from '@wordpress/components';

registerBlockType('campaignpress/your-block', {
    edit: ({ attributes, setAttributes }) => {
        const { /* destructure attributes */ } = attributes;
        const blockProps = useBlockProps();

        return (
            <>
                {/* Settings Sidebar */}
                <InspectorControls>
                    <PanelBody title="Settings" initialOpen={true}>
                        {/* Add controls here */}
                    </PanelBody>
                </InspectorControls>

                {/* Block Preview */}
                <div {...blockProps}>
                    {/* Add editor preview here */}
                </div>
            </>
        );
    },
    save: () => null // Server-side rendering
});
```

---

## 🚀 **Remaining Blocks**

### **1. Countdown Block** (`blocks/countdown/`)

**Purpose:** Election day countdown timer

**block.json attributes:**
```json
{
    "targetDate": { "type": "string", "default": "2024-11-05" },
    "title": { "type": "string", "default": "Days Until Election" },
    "showDays": { "type": "boolean", "default": true },
    "showHours": { "type": "boolean", "default": true },
    "showMinutes": { "type": "boolean", "default": false },
    "textColor": { "type": "string", "default": "#ffffff" },
    "accentColor": { "type": "string", "default": "#ff8800" },
    "layout": { "type": "string", "default": "horizontal", "enum": ["horizontal", "vertical", "grid"] }
}
```

**Inspector Controls:**
- TextControl for `targetDate` (type="date")
- ToggleControl for show/hide each time unit
- ColorPalette for colors
- ButtonGroup for layout

**Editor Preview:**
- Display fake countdown (30 days, 12 hours, 45 minutes)
- Use grid/flex layout based on `layout` attribute

**view.js:**
- Calculate time difference
- Update every second
- Handle past dates gracefully

---

### **2. Event Organizer Block** (`blocks/event-organizer/`)

**Purpose:** Display and manage campaign events

**block.json attributes:**
```json
{
    "title": { "type": "string", "default": "Upcoming Events" },
    "layout": { "type": "string", "default": "grid", "enum": ["grid", "list", "calendar"] },
    "eventsToShow": { "type": "number", "default": 3 },
    "showDate": { "type": "boolean", "default": true },
    "showLocation": { "type": "boolean", "default": true },
    "showRSVP": { "type": "boolean", "default": true },
    "rsvpUrl": { "type": "string", "default": "" },
    "filterByCategory": { "type": "array", "default": [] }
}
```

**Inspector Controls:**
- NumberControl for `eventsToShow`
- SelectControl for `layout`
- Multiple ToggleControls for show/hide options
- TextControl for `rsvpUrl`

**render.php:**
- Query `cp_event` custom post type
- Use event meta fields (`_cp_event_date`, `_cp_event_location`)
- Display in selected layout
- Add RSVP button linking to URL

---

### **3. Volunteer Matcher Block** (`blocks/volunteer-matcher/`)

**Purpose:** Connect volunteers with opportunities

**block.json attributes:**
```json
{
    "heading": { "type": "string", "default": "Get Involved" },
    "description": { "type": "string", "default": "Find volunteer opportunities that match your skills" },
    "showSkillsFilter": { "type": "boolean", "default": true },
    "showLocationFilter": { "type": "boolean", "default": true },
    "showAvailabilityFilter": { "type": "boolean", "default": true },
    "submitUrl": { "type": "string", "default": "" },
    "opportunities": {
        "type": "array",
        "default": [
            { "title": "Phone Banking", "skills": ["communication"], "location": "remote" },
            { "title": "Door Knocking", "skills": ["outreach"], "location": "local" },
            { "title": "Social Media", "skills": ["marketing"], "location": "remote" }
        ]
    }
}
```

**Inspector Controls:**
- RichText for `heading` and `description`
- Array editor for `opportunities` (similar to donation tiers)
- TextControl for `submitUrl`
- Multiple ToggleControls for filters

**Editor Preview:**
- Show opportunity cards with icons
- Display filter UI
- Style submit button

---

### **4. Policy Platform Block** (`blocks/policy-platform/`)

**Purpose:** Display policy positions with expand/collapse

**block.json attributes:**
```json
{
    "title": { "type": "string", "default": "Our Platform" },
    "policies": {
        "type": "array",
        "default": [
            { "title": "Healthcare", "summary": "...", "details": "...", "icon": "heart" },
            { "title": "Education", "summary": "...", "details": "...", "icon": "book" },
            { "title": "Economy", "summary": "...", "details": "...", "icon": "chart-line" }
        ]
    },
    "layout": { "type": "string", "default": "accordion", "enum": ["accordion", "tabs", "cards"] },
    "expandedByDefault": { "type": "boolean", "default": false }
}
```

**Inspector Controls:**
- Array editor for `policies`
- SelectControl for `layout`
- ToggleControl for `expandedByDefault`

**view.js:**
- Accordion/tabs functionality
- Smooth expand/collapse animations
- ARIA accessibility

---

### **5. Section Wrapper Block** (`blocks/section-wrapper/`) ⭐ **Important!**

**Purpose:** Container block with InnerBlocks for nested layouts

**block.json attributes:**
```json
{
    "maxWidth": { "type": "string", "default": "1200px" },
    "padding": { "type": "object", "default": { "top": "2rem", "bottom": "2rem" } },
    "backgroundColor": { "type": "string", "default": "#ffffff" },
    "columns": { "type": "number", "default": 1 },
    "gap": { "type": "string", "default": "2rem" },
    "verticalAlign": { "type": "string", "default": "start" }
}
```

**Special Feature - InnerBlocks:**
```javascript
import { InnerBlocks } from '@wordpress/block-editor';

// In your edit function:
<InnerBlocks
    allowedBlocks={[
        'core/heading',
        'core/paragraph',
        'core/image',
        'campaignpress/donation-form',
        'campaignpress/progress'
    ]}
    template={[
        ['core/heading', { placeholder: 'Section Title' }],
        ['core/paragraph', { placeholder: 'Add content...' }]
    ]}
    renderAppender={() => <InnerBlocks.ButtonBlockAppender />}
/>
```

**Inspector Controls:**
- UnitControl for `maxWidth`, `gap`
- BoxControl for `padding`
- ColorPalette for `backgroundColor`
- RangeControl for `columns`
- ButtonGroup for `verticalAlign`

---

### **6. Mission Control Block** (`blocks/mission-control/`)

**Purpose:** Admin dashboard widget for campaign managers

**block.json attributes:**
```json
{
    "title": { "type": "string", "default": "Campaign Dashboard" },
    "showDonations": { "type": "boolean", "default": true },
    "showVolunteers": { "type": "boolean", "default": true },
    "showEvents": { "type": "boolean", "default": true },
    "showPollNumbers": { "type": "boolean", "default": false },
    "refreshInterval": { "type": "number", "default": 300 }
}
```

**Inspector Controls:**
- Multiple ToggleControls for widgets to show
- NumberControl for `refreshInterval` (seconds)

**render.php:**
- Query donation totals
- Count volunteers
- Upcoming events count
- Display in dashboard cards

---

### **7. Style Panel Block** (`blocks/style-panel/`)

**Purpose:** Visual style customization panel (utility block)

**block.json attributes:**
```json
{
    "targetBlock": { "type": "string", "default": "" },
    "customCSS": { "type": "string", "default": "" },
    "customClasses": { "type": "array", "default": [] },
    "animations": { "type": "object", "default": {} },
    "responsiveSettings": { "type": "object", "default": {} }
}
```

**Purpose:**
- Add custom styling to other blocks
- Advanced users can add CSS
- Preset animation options
- Responsive overrides

---

## 🛠️ **Implementation Steps for Each Block**

### **Step 1: Update block.json**
- Copy from hero-commander or donation-form
- Add your attributes with proper types and defaults
- Set proper icon and description

### **Step 2: Create React Component (index.js)**
1. Import necessary components
2. Destructure attributes
3. Create `InspectorControls` with panels
4. Build editor preview
5. Add `save: () => null` for SSR

### **Step 3: Update render.php**
- Extract attributes with `??` null coalescing
- Add proper escaping (`esc_attr`, `esc_html`, `esc_url`)
- Use `get_block_wrapper_attributes()`
- Output semantic HTML

### **Step 4: Style with CSS (style.css)**
- BEM naming convention (`.block-name__element`)
- Mobile-first responsive design
- Accessibility (@media prefers-reduced-motion)
- High contrast mode support

### **Step 5: Add JavaScript if needed (view.js)**
- Countdown timers
- Accordion/tabs
- Form interactions
- API calls

---

## 📦 **Build & Test**

```bash
# Build all blocks
npm run build:blocks

# Watch mode during development
npm run watch:blocks

# Build entire theme
npm run build
```

**Testing Checklist:**
- [ ] Block appears in inserter
- [ ] All inspector controls work
- [ ] Preview matches frontend
- [ ] Responsive on mobile
- [ ] Accessible (keyboard navigation)
- [ ] No console errors
- [ ] Works with FSE templates

---

## 🎨 **Common Controls Reference**

### **Inspector Controls You'll Use Most:**

```javascript
import {
    TextControl,           // Text input
    ToggleControl,        // On/off switch
    SelectControl,        // Dropdown
    RangeControl,         // Slider
    ColorPalette,         // Color picker
    MediaUpload,          // Image/video upload
    RichText,            // Editable text in preview
    InnerBlocks,         // Nested blocks
    __experimentalNumberControl as NumberControl,
    __experimentalUnitControl as UnitControl,
    __experimentalBoxControl as BoxControl
} from '@wordpress/components';
```

### **Common Patterns:**

**Array of Items (like tiers):**
```javascript
{items.map((item, index) => (
    <div key={index}>
        <TextControl
            value={item.title}
            onChange={(v) => {
                const newItems = [...items];
                newItems[index] = { ...newItems[index], title: v };
                setAttributes({ items: newItems });
            }}
        />
    </div>
))}
<Button onClick={() => setAttributes({ items: [...items, { title: '' }] })}>
    Add Item
</Button>
```

**Conditional Controls:**
```javascript
{showAdvanced && (
    <PanelBody title="Advanced">
        {/* More controls */}
    </PanelBody>
)}
```

---

## 🚢 **Ready to Ship?**

Once all 10 blocks are complete:

1. **Test everything:**
   ```bash
   npm run build
   ```

2. **Create block variations** (next level):
   - Hero: video, split, minimal, bold
   - Donation: single-tier, recurring-only, crypto-focused

3. **Build block patterns:**
   - Landing page pattern
   - Donation campaign pattern
   - Event showcase pattern

4. **Document for users:**
   - Screenshot each block
   - Write usage guides
   - Create video tutorials

---

## 💡 **Pro Tips**

1. **Copy, Don't Rewrite:** Use hero-commander/donation-form as templates
2. **Test Incrementally:** Build one panel at a time
3. **Use Placeholders:** Static previews are fine during development
4. **Commit Often:** Small commits are easier to debug
5. **Check Console:** React errors show in browser console
6. **Use React DevTools:** Install browser extension

---

## 📞 **Need Help?**

**Common Issues:**

- **Block doesn't appear:** Check `blocks/registration.php` includes it
- **Inspector controls broken:** Check imports and component syntax
- **Preview looks wrong:** Check inline styles and class names
- **Build fails:** Run `npm install` first, check for syntax errors

**Resources:**
- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Gutenberg Components](https://wordpress.github.io/gutenberg/)
- Your own `hero-commander/index.js` - best reference!

---

You now have everything you need to complete all 10 blocks! 🎉

Start with **section-wrapper** (it's a container block that makes the others more useful), then **countdown**, then the rest.
