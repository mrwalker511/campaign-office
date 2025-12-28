# Design Reference

**Complete design system and style guide for CampaignPress**

Version: 2.0.0 | Last Updated: December 28, 2025

---

## Table of Contents

1. [Design System Overview](#design-system-overview)
2. [WordPress 6.9 Design Tokens](#wordpress-69-design-tokens)
3. [Color System](#color-system)
4. [Typography](#typography)
5. [Spacing & Layout](#spacing--layout)
6. [Components](#components)
7. [Style Guide](#style-guide)
8. [Design Enhancements](#design-enhancements)

---

## Design System Overview

CampaignPress 2.0 uses a **WordPress 6.9-native design system** with centralized design tokens in `theme.json`. This provides:

- **Consistency** across all components
- **Block editor integration** with visual controls
- **Theme switching** for party color schemes
- **Performance** through WordPress-managed fonts
- **Accessibility** built into the core system

### Design Philosophy

**Distinctive, Not Generic:**
- Avoid overused fonts (Inter, Roboto, Arial)
- Use bold, memorable color palettes
- Create sophisticated animations
- Layer backgrounds for depth
- Think political campaign energy

**Context-Specific:**
CampaignPress designs should convey:
- **Trust & Authority** - Bold typography, professional colors
- **Energy & Momentum** - Animations, gradients, dynamic effects
- **Accessibility** - WCAG AA compliance, reduced motion support
- **Professionalism** - Consistent system, polished details

---

## WordPress 6.9 Design Tokens

### Central Configuration: theme.json

All design tokens are defined in `theme.json` and automatically available throughout WordPress:

**Benefits:**
- Change once, update everywhere
- Block editor visual controls
- CSS variables auto-generated
- PHP access via WordPress functions
- No build step required

### Accessing Tokens

**In CSS:**
```css
.my-component {
    color: var(--wp--preset--color--primary);
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--2-xl);
    padding: var(--wp--preset--spacing--8);
    box-shadow: var(--wp--preset--shadow--lg);
}
```

**In Block Editor:**
Users select from visual dropdowns - no coding required

**In PHP:**
```php
$primary_color = wp_get_global_settings()['color']['palette']['theme'][5]['color'];
```

---

## Color System

### 33 Color Tokens (9-Shade Palettes)

**Primary Colors (Democrat Blue - Default):**
- `primary-50`: #e6f0ff (lightest)
- `primary-100`: #b3d4ff
- `primary-200`: #80b8ff
- `primary-300`: #4d9dff
- `primary-400`: #1a81ff
- `primary` (500): #0053c3 ⭐ Main brand color
- `primary-600`: #004399
- `primary-700`: #003270
- `primary-800`: #002247
- `primary-900`: #00111d (darkest)

**Accent Colors (Orange):**
- `accent-50` → `accent-900`: #fff4e6 → #331a00
- `accent` (500): #ff8800 ⭐ Calls-to-action, highlights

**Neutral Colors (Grays):**
- `neutral-50` → `neutral-900`: #f8f9fa → #111111
- `neutral` (500): #6c757d ⭐ Text, borders

**Semantic Colors:**
- `success`: #28a745
- `warning`: #ffc107
- `error`: #dc3545
- `info`: #17a2b8

### Party Color Schemes

Switch entire site color scheme with body class:

**1. Democrat Blue (Default)**
```css
/* No class needed */
Primary: #0053c3
Accent: #ff8800
```

**2. Republican Red**
```css
body.color-scheme-republican
Primary: #e81b23
Accent: #ffd700
```

**3. Independent Purple**
```css
body.color-scheme-independent
Primary: #6b3fa0
Accent: #00d9ff
```

**4. Green Party**
```css
body.color-scheme-green
Primary: #17aa5c
Accent: #ffeb3b
```

### Color Usage Guidelines

**Primary:**
- Headers and navigation
- Buttons and CTAs (primary style)
- Links and accents
- Hero sections background

**Accent:**
- Highlights and emphasis
- Secondary CTAs
- Hover states
- Important metrics/stats

**Neutral:**
- Body text (900)
- Backgrounds (50-100)
- Borders (300-400)
- Disabled states (500)

**Semantic:**
- `success` - Completed actions, confirmations
- `warning` - Important notices, alerts
- `error` - Form errors, critical issues
- `info` - Informational messages

---

## Typography

### Font Families (3 Distinctive Fonts)

**Display: Bricolage Grotesque**
- **Usage:** Headlines, hero text, CTAs
- **Character:** Bold, geometric, authoritative
- **Example:** H1-H3 headings

```css
font-family: var(--wp--preset--font-family--display);
```

**Body: Plus Jakarta Sans**
- **Usage:** Paragraphs, UI text, forms
- **Character:** Modern, warm, highly readable
- **Example:** Body copy, descriptions

```css
font-family: var(--wp--preset--font-family--body);
```

**Mono: JetBrains Mono**
- **Usage:** Numbers, statistics, code
- **Character:** Clean, precise, technical
- **Example:** Donation amounts, vote counts

```css
font-family: var(--wp--preset--font-family--mono);
```

### Font Sizes (8 Fluid Sizes)

Automatically scale from mobile to desktop using CSS `clamp()`:

- `xs`: clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem) - 12-14px
- `sm`: clamp(0.875rem, 0.825rem + 0.25vw, 1rem) - 14-16px
- `base`: clamp(1rem, 0.95rem + 0.25vw, 1.125rem) - 16-18px
- `lg`: clamp(1.125rem, 1.05rem + 0.375vw, 1.25rem) - 18-20px
- `xl`: clamp(1.25rem, 1.15rem + 0.5vw, 1.5rem) - 20-24px
- `2-xl`: clamp(1.5rem, 1.35rem + 0.75vw, 1.875rem) - 24-30px
- `3-xl`: clamp(1.875rem, 1.65rem + 1.125vw, 2.25rem) - 30-36px
- `4-xl`: clamp(2.25rem, 1.95rem + 1.5vw, 3rem) - 36-48px

**Usage:**
```css
h1 { font-size: var(--wp--preset--font-size--4-xl); }
h2 { font-size: var(--wp--preset--font-size--3-xl); }
h3 { font-size: var(--wp--preset--font-size--2-xl); }
p { font-size: var(--wp--preset--font-size--base); }
small { font-size: var(--wp--preset--font-size--sm); }
```

### Typography Scale

```css
/* Hero Section */
.hero-title {
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--4-xl);
    font-weight: 700;
    line-height: 1.1;
}

/* Subheadings */
.section-title {
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--2-xl);
    font-weight: 600;
}

/* Body Text */
.body-text {
    font-family: var(--wp--preset--font-family--body);
    font-size: var(--wp--preset--font-size--base);
    line-height: 1.6;
}

/* Statistics */
.stat-number {
    font-family: var(--wp--preset--font-family--mono);
    font-size: var(--wp--preset--font-size--3-xl);
    font-weight: 700;
}
```

---

## Spacing & Layout

### Spacing Scale (12 Presets)

Based on 8px grid system:

- `1`: 4px - Tiny gaps
- `2`: 8px - Small spacing
- `3`: 12px
- `4`: 16px - Base unit
- `5`: 20px
- `6`: 24px - Section padding
- `8`: 32px
- `10`: 40px
- `12`: 48px - Large sections
- `16`: 64px
- `20`: 80px
- `24`: 96px - Hero sections

**Usage:**
```css
.card {
    padding: var(--wp--preset--spacing--6);
    margin-bottom: var(--wp--preset--spacing--8);
    gap: var(--wp--preset--spacing--4);
}
```

### Shadow System (6 Presets)

- `sm`: 0 1px 2px rgba(0,0,0,0.05)
- `base`: 0 1px 3px rgba(0,0,0,0.1)
- `md`: 0 4px 6px rgba(0,0,0,0.1)
- `lg`: 0 10px 15px rgba(0,0,0,0.1)
- `xl`: 0 20px 25px rgba(0,0,0,0.1)
- `2-xl`: 0 25px 50px rgba(0,0,0,0.25)

**Usage:**
```css
.card {
    box-shadow: var(--wp--preset--shadow--md);
}

.card:hover {
    box-shadow: var(--wp--preset--shadow--lg);
}

.modal {
    box-shadow: var(--wp--preset--shadow--2-xl);
}
```

### Layout Patterns

**Container:**
```css
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--wp--preset--spacing--6);
}
```

**Grid:**
```css
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--wp--preset--spacing--8);
}
```

**Flexbox:**
```css
.flex-row {
    display: flex;
    gap: var(--wp--preset--spacing--4);
    align-items: center;
}
```

---

## Components

### Buttons

**Primary Button:**
```css
.btn-primary {
    background: var(--wp--preset--color--primary);
    color: white;
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--base);
    font-weight: 600;
    padding: var(--wp--preset--spacing--4) var(--wp--preset--spacing--8);
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 250ms ease;
}

.btn-primary:hover {
    background: var(--wp--preset--color--primary-600);
    transform: translateY(-2px);
    box-shadow: var(--wp--preset--shadow--lg);
}
```

**Accent Button:**
```css
.btn-accent {
    background: var(--wp--preset--color--accent);
    color: var(--wp--preset--color--neutral-900);
    /* Same structure as primary */
}
```

### Cards

```css
.card {
    background: white;
    border-radius: 12px;
    padding: var(--wp--preset--spacing--8);
    box-shadow: var(--wp--preset--shadow--md);
    transition: all 250ms ease;
}

.card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: var(--wp--preset--shadow--xl);
}

.card-title {
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--xl);
    margin-bottom: var(--wp--preset--spacing--4);
}
```

### Forms

```css
.form-input {
    font-family: var(--wp--preset--font-family--body);
    font-size: var(--wp--preset--font-size--base);
    padding: var(--wp--preset--spacing--3) var(--wp--preset--spacing--4);
    border: 2px solid var(--wp--preset--color--neutral-300);
    border-radius: 6px;
    transition: border-color 250ms ease;
}

.form-input:focus {
    outline: none;
    border-color: var(--wp--preset--color--primary);
    box-shadow: 0 0 0 3px var(--wp--preset--color--primary-100);
}
```

### Progress Bars

```css
.progress-bar {
    background: var(--wp--preset--color--neutral-200);
    border-radius: 999px;
    height: 24px;
    overflow: hidden;
    position: relative;
}

.progress-bar-fill {
    background: linear-gradient(
        90deg,
        var(--wp--preset--color--primary) 0%,
        var(--wp--preset--color--primary-400) 100%
    );
    height: 100%;
    transition: width 500ms ease;
    position: relative;
}

.progress-bar-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255,255,255,0.3),
        transparent
    );
    animation: shine 2s infinite;
}

@keyframes shine {
    to { left: 100%; }
}
```

---

## Animations

### Timing

**Standard Durations:**
- `fast`: 150ms - Micro-interactions
- `base`: 250ms - Buttons, hovers
- `slow`: 350ms - Modals, transitions
- `slower`: 500ms - Page transitions

**Easing Functions:**
- `ease-out`: Fast start, slow end (hover in)
- `ease-in`: Slow start, fast end (hover out)
- `ease-in-out`: Smooth both ends (modals)

### Animation Patterns

**Staggered Hero Reveal:**
```css
.hero-title {
    animation: fadeInUp 600ms ease-out 0ms both;
}

.hero-subtitle {
    animation: fadeInUp 600ms ease-out 200ms both;
}

.hero-cta {
    animation: fadeInUp 600ms ease-out 400ms both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

**Button Pulse:**
```css
.btn-pulse {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 var(--wp--preset--color--accent);
    }
    50% {
        box-shadow: 0 0 0 10px transparent;
    }
}
```

**Card Hover:**
```css
.card {
    transition: transform 250ms ease-out, box-shadow 250ms ease-out;
}

.card:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: var(--wp--preset--shadow--xl);
}
```

### Reduced Motion

Always respect user preferences:

```css
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

---

## Style Guide

### Writing Style

**Headlines:**
- Active voice
- Action-oriented
- 60 characters or less
- Examples: "Join Our Movement", "Make Your Voice Heard"

**Body Copy:**
- Clear and concise
- Conversational but professional
- Short paragraphs (2-3 sentences)
- Bulleted lists for scanability

**CTAs:**
- Command verbs
- Create urgency
- Specific actions
- Examples: "Donate Now", "Get Involved", "Sign Up Today"

### Accessibility

**Color Contrast:**
- WCAG 2.1 AA minimum (4.5:1 for text)
- AAA preferred (7:1 for text)
- Test all color combinations

**Focus States:**
```css
:focus-visible {
    outline: 3px solid var(--wp--preset--color--accent);
    outline-offset: 2px;
}
```

**Screen Readers:**
```html
<button aria-label="Close modal">
    <span aria-hidden="true">×</span>
</button>
```

**Keyboard Navigation:**
- All interactive elements focusable
- Logical tab order
- Skip links for navigation

---

## Design Enhancements

### Planned Improvements

**Phase 1: Component Library (Q1 2026)**
- Standardized component documentation
- Storybook integration
- Reusable React components
- Design token browser

**Phase 2: Advanced Animations (Q2 2026)**
- Scroll-triggered animations
- Parallax effects
- Loading state animations
- Transition choreography

**Phase 3: Theme Customization (Q3 2026)**
- Visual theme builder
- Custom color scheme creator
- Font pairing suggestions
- Layout template library

**Phase 4: Accessibility Enhancements (Q4 2026)**
- High contrast mode
- Dyslexia-friendly fonts
- Adjustable font sizes
- Enhanced keyboard navigation

---

## Quick Reference

### Most Common Patterns

**Section Spacing:**
```css
.section {
    padding: var(--wp--preset--spacing--16) 0;
}
```

**Heading:**
```css
h2 {
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--3-xl);
    color: var(--wp--preset--color--primary);
    margin-bottom: var(--wp--preset--spacing--6);
}
```

**Button:**
```css
.btn {
    background: var(--wp--preset--color--accent);
    color: white;
    font-family: var(--wp--preset--font-family--display);
    font-weight: 600;
    padding: var(--wp--preset--spacing--4) var(--wp--preset--spacing--8);
    border-radius: 8px;
    transition: all 250ms ease-out;
}
```

**Card:**
```css
.card {
    background: white;
    padding: var(--wp--preset--spacing--8);
    border-radius: 12px;
    box-shadow: var(--wp--preset--shadow--md);
}
```

---

## Resources

**Files:**
- `theme.json` - Design token definitions
- `assets/css/design-system-wp69.css` - Enhanced CSS
- `docs/CLAUDE.md` - Architecture overview with design system section

**External:**
- [WordPress Theme JSON Reference](https://developer.wordpress.org/block-editor/reference-guides/theme-json-reference/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Color Contrast Checker](https://webaim.org/resources/contrastchecker/)

---

**Last Updated:** December 28, 2025
**Version:** 2.0.0
