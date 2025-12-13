# CampaignPress Design System Guide

**Version 2.0** | Professional Political Campaign Theme Design

---

## 🎨 Design Philosophy

CampaignPress adopts a **bold, authoritative, yet modern** visual identity that:

- **Builds Trust** through professional typography and solid foundations
- **Conveys Energy** via sophisticated animations and dynamic gradients
- **Maintains Accessibility** with WCAG 2.1 AA compliance
- **Avoids Generic Aesthetics** by using distinctive fonts and creative effects

---

## 📐 Typography System

### Font Stack

We use **three distinct font families** to create visual hierarchy and interest:

#### 1. **Bricolage Grotesque** (Display/Headlines)
- **Usage**: Headlines (h1-h4), site title, buttons, navigation
- **Character**: Bold, authoritative, slightly geometric
- **Why**: Provides commanding presence without being traditional or stuffy
- **Weights**: 400, 500, 600, 700, 800

```css
font-family: 'Bricolage Grotesque', sans-serif;
```

#### 2. **Plus Jakarta Sans** (Body Text)
- **Usage**: Paragraphs, body text, descriptions
- **Character**: Warm, friendly, highly readable
- **Why**: Modern humanist sans-serif that feels approachable yet professional
- **Weights**: 300, 400, 500, 600, 700, 800

```css
font-family: 'Plus Jakarta Sans', sans-serif;
```

#### 3. **JetBrains Mono** (Monospace/Data)
- **Usage**: Statistics, donation amounts, countdown timers, data
- **Character**: Clean, technical, precise
- **Why**: Creates visual interest for numbers and makes data stand out
- **Weights**: 400, 500, 600, 700

```css
font-family: 'JetBrains Mono', monospace;
```

### Type Scale (Fluid/Responsive)

Uses CSS `clamp()` for fluid typography that scales smoothly:

```css
--cp-text-xs:   clamp(0.75rem, 0.7rem + 0.25vw, 0.875rem);     /* 12-14px */
--cp-text-sm:   clamp(0.875rem, 0.825rem + 0.25vw, 1rem);      /* 14-16px */
--cp-text-base: clamp(1rem, 0.95rem + 0.25vw, 1.125rem);       /* 16-18px */
--cp-text-lg:   clamp(1.125rem, 1.05rem + 0.375vw, 1.375rem);  /* 18-22px */
--cp-text-xl:   clamp(1.25rem, 1.15rem + 0.5vw, 1.75rem);      /* 20-28px */
--cp-text-2xl:  clamp(1.5rem, 1.3rem + 1vw, 2.25rem);          /* 24-36px */
--cp-text-3xl:  clamp(2rem, 1.6rem + 2vw, 3.5rem);             /* 32-56px */
--cp-text-4xl:  clamp(2.5rem, 1.8rem + 3.5vw, 5rem);           /* 40-80px */
```

**Benefit**: Text automatically adapts between mobile and desktop without breakpoints.

---

## 🎨 Color System

### Multi-Tier Palette

Each primary color has **9 shades** (50-900) for maximum flexibility:

#### Democrat Blue (Default)
```css
--cp-primary-50:  #e6eef9;  /* Lightest tint */
--cp-primary-100: #ccddf3;
--cp-primary-200: #99bae7;
--cp-primary-300: #6698db;
--cp-primary-400: #3375cf;
--cp-primary-500: #0053c3;  /* Main brand */
--cp-primary-600: #00429c;
--cp-primary-700: #003275;
--cp-primary-800: #00214e;
--cp-primary-900: #001127;  /* Darkest shade */
```

#### Accent Orange
```css
--cp-accent-500: #ff8800;  /* Vibrant, energetic */
```

### Party-Specific Themes

The theme adapts automatically with body classes:

```html
<body class="color-scheme-republican-red">
<body class="color-scheme-independent-purple">
<body class="color-scheme-green-party">
```

Each theme includes coordinated primary + accent colors.

---

## 🌊 Gradient Strategy

### Why Gradients?

- **Create Depth**: Adds dimensionality vs flat colors
- **Draw Attention**: Guides eye to CTAs and important elements
- **Brand Expression**: Combines primary + accent for cohesive identity

### Usage Patterns

#### 1. **Backgrounds** (Subtle, 135deg diagonal)
```css
background: linear-gradient(135deg, var(--cp-primary) 0%, var(--cp-primary-dark) 100%);
```

#### 2. **Text Gradients** (Headlines, stats)
```css
background: linear-gradient(135deg, var(--cp-primary), var(--cp-accent));
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;
background-clip: text;
```

#### 3. **Radial Gradients** (Atmospheric effects)
```css
background: radial-gradient(circle at 20% 30%, var(--cp-accent-500) 0%, transparent 40%);
```

---

## ✨ Animation Philosophy

### High-Impact, Purposeful Motion

**Focus Areas:**
1. **Page Load** - Staggered hero reveals (0.2s delays)
2. **Hover States** - Transform + shadow lift
3. **CTA Emphasis** - Subtle pulse animation
4. **Progress Bars** - Smooth fill + shine effect

### Key Animations

#### 1. **Hero Staggered Reveal**
```css
.hero-title    { animation: heroFadeInUp 1s ease 0s backwards; }
.hero-subtitle { animation: heroFadeInUp 1s ease 0.2s backwards; }
.hero-tagline  { animation: heroFadeInUp 1s ease 0.4s backwards; }
.hero-cta      { animation: heroFadeInUp 1s ease 0.6s backwards; }
```

**Effect**: Creates orchestrated entrance that builds anticipation.

#### 2. **Button Pulse** (CTA emphasis)
```css
@keyframes buttonPulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(0, 83, 195, 0.5); }
  50%      { box-shadow: 0 0 0 8px rgba(0, 83, 195, 0); }
}
```

**Effect**: Subtle breathing effect draws attention without distraction.

#### 3. **Progress Bar Shine**
```css
@keyframes progressShine {
  0%   { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}
```

**Effect**: Highlights fundraising progress with movement.

#### 4. **Hover Lift**
```css
.cp-issue-card:hover {
  transform: translateY(-8px) scale(1.01);
  box-shadow: var(--cp-shadow-2xl);
}
```

**Effect**: Creates tactile, interactive feel.

### Accessibility: Respecting User Preferences

```css
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

## 🏔️ Atmospheric Backgrounds

### Hero Section: Layered Complexity

The hero uses **5 visual layers** for depth:

1. **Base Image/Video** (z-index: 1)
2. **Dark Gradient Overlay** (z-index: 2) - Ensures text contrast
3. **Animated Radial Gradients** (z-index: 3) - Subtle color movement
4. **Geometric Pattern** (z-index: 4) - Adds texture
5. **Content** (z-index: 10) - Text and CTAs

```css
.hero-background::after {
  background:
    radial-gradient(circle at 20% 30%, var(--cp-accent-500) 0%, transparent 40%),
    radial-gradient(circle at 80% 70%, var(--cp-primary-500) 0%, transparent 50%);
  animation: heroGradientShift 20s ease-in-out infinite;
}
```

**Result**: Rich, atmospheric background that feels premium and dynamic.

---

## 🎯 Component Showcase

### 1. Progress Meter

**Design Features:**
- Monospace font for dollar amounts (visual consistency)
- Gradient fill with animated shine overlay
- Shimmer effect on container
- Rounded pill shape (approachable)

**Psychology**: Creates excitement around fundraising goals.

### 2. Issue Cards

**Design Features:**
- Gradient color bar on hover (left edge)
- Scale + lift transform
- Icon bounce effect
- Background tint overlay

**Psychology**: Makes policy positions feel dynamic and engaging.

### 3. Buttons

**Design Features:**
- Gradient backgrounds (depth)
- Pulse animation on primary CTAs
- Lift transform on hover
- Glass morphism on secondary buttons

**Psychology**: Creates irresistible call-to-action.

---

## 📏 Spacing System

8px base grid system:

```css
--cp-space-1:  0.25rem;  /* 4px  */
--cp-space-2:  0.5rem;   /* 8px  */
--cp-space-3:  0.75rem;  /* 12px */
--cp-space-4:  1rem;     /* 16px */
--cp-space-6:  1.5rem;   /* 24px */
--cp-space-8:  2rem;     /* 32px */
--cp-space-10: 2.5rem;   /* 40px */
--cp-space-12: 3rem;     /* 48px */
--cp-space-16: 4rem;     /* 64px */
--cp-space-20: 5rem;     /* 80px */
--cp-space-24: 6rem;     /* 96px */
```

**Usage**: Consistent spacing creates visual rhythm and professionalism.

---

## 🛡️ Implementation Guide

### Step 1: Load Enhanced CSS

> **Note:** The current WordPress 6.9+ implementation uses `design-system-wp69.css`
> which is already enqueued in `functions.php`. See `WP69_IMPLEMENTATION.md` for details.

In your `functions.php`, enqueue the enhanced design system:

```php
function campaignpress_enqueue_enhanced_styles() {
    wp_enqueue_style(
        'campaignpress-design-system',
        get_template_directory_uri() . '/assets/css/design-system-wp69.css',
        array('campaignpress-main'),
        '2.0.0'
    );
}
add_action('wp_enqueue_scripts', 'campaignpress_enqueue_enhanced_styles');
```

### Step 2: Add Color Scheme Body Class

Allow users to select party theme in Customizer:

```php
function campaignpress_body_classes($classes) {
    $color_scheme = get_theme_mod('campaignpress_color_scheme', 'democrat-blue');
    $classes[] = 'color-scheme-' . $color_scheme;
    return $classes;
}
add_filter('body_class', 'campaignpress_body_classes');
```

### Step 3: Update Template Markup

Ensure templates use semantic HTML with proper classes:

```html
<div class="campaign-hero">
    <div class="hero-background">
        <img src="hero.jpg" alt="">
        <div class="hero-pattern"></div>
    </div>
    <div class="hero-content">
        <h1 class="hero-title">Campaign 2026</h1>
        <p class="hero-subtitle">Fighting for Our Future</p>
        <p class="hero-tagline">Together, we can build a better tomorrow for all Americans.</p>
        <div class="hero-cta">
            <a href="#donate" class="button button-primary button-large">Donate Now</a>
            <a href="#volunteer" class="button button-secondary button-large">Get Involved</a>
        </div>
    </div>
</div>
```

---

## 🎭 Design Principles

### 1. **Avoid Generic AI Aesthetics**

❌ **Don't:**
- Use Inter, Roboto, Arial, or system fonts exclusively
- Default to purple gradients on white
- Create predictable card layouts
- Use flat, single colors

✅ **Do:**
- Choose distinctive fonts (Bricolage Grotesque, Plus Jakarta Sans)
- Use contextual color schemes (political party colors)
- Layer gradients, patterns, and effects for depth
- Create purposeful motion that enhances experience

### 2. **Typography Hierarchy**

Every page should have clear visual hierarchy:

- **Level 1**: Hero title (4xl, 800 weight, gradient text)
- **Level 2**: Section headings (3xl, 700 weight)
- **Level 3**: Subsections (2xl, 700 weight)
- **Level 4**: Component titles (xl, 600 weight)
- **Body**: Paragraphs (base, 400 weight)

### 3. **Color Usage**

- **Primary**: Brand identity, CTAs, navigation active states
- **Accent**: Secondary CTAs, highlights, stats
- **Neutral**: Text, borders, backgrounds
- **Semantic**: Success, warning, error, info states

### 4. **Motion Purpose**

Every animation should have a reason:

- **Entrance**: Establishes hierarchy, guides attention
- **Hover**: Provides feedback, indicates interactivity
- **Loading**: Communicates progress, reduces perceived wait
- **Emphasis**: Draws attention to conversion points

---

## 📱 Responsive Strategy

### Breakpoints

```css
/* Mobile First */
@media (max-width: 768px)  { /* Mobile */ }
@media (max-width: 991px)  { /* Tablet */ }
@media (min-width: 992px)  { /* Desktop */ }
@media (min-width: 1200px) { /* Large Desktop */ }
```

### Fluid Typography

No need for font-size media queries - `clamp()` handles it automatically:

```css
/* Automatically scales from 40px (mobile) to 80px (desktop) */
font-size: var(--cp-text-4xl);
```

---

## ♿ Accessibility Features

### 1. **Focus States**

```css
.cp-button:focus-visible {
    outline: 3px solid var(--cp-accent);
    outline-offset: 3px;
}
```

### 2. **Color Contrast**

All text meets WCAG AA standards (4.5:1 for body, 3:1 for large text).

### 3. **Reduced Motion**

Respects user's `prefers-reduced-motion` setting.

### 4. **Semantic HTML**

Proper heading hierarchy (h1 → h2 → h3) for screen readers.

---

## 🚀 Performance Optimization

### Font Loading Strategy

```html
<!-- Preconnect to Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<!-- Load fonts with display=swap to prevent FOIT -->
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;700;800&family=Plus+Jakarta+Sans:wght@400;600;700&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
```

### CSS Optimization

- Use CSS custom properties (variables) for consistency
- Minimize animation complexity on mobile
- Use `will-change` sparingly for performance
- Leverage GPU acceleration with `transform` and `opacity`

---

## 🎨 Color Scheme Examples

### Democrat Blue Theme
- **Primary**: Deep authoritative blue (#0053c3)
- **Accent**: Energetic orange (#ff8800)
- **Feel**: Professional, trustworthy, forward-thinking

### Republican Red Theme
- **Primary**: Bold confident red (#e81b23)
- **Accent**: Classic gold (#ffd700)
- **Feel**: Strong, traditional, patriotic

### Independent Purple Theme
- **Primary**: Balanced purple (#6b3fa0)
- **Accent**: Modern cyan (#00d9ff)
- **Feel**: Independent, innovative, inclusive

### Green Party Theme
- **Primary**: Natural green (#17aa5c)
- **Accent**: Bright yellow (#ffeb3b)
- **Feel**: Environmental, grassroots, optimistic

---

## 📋 Component Checklist

When creating new components, ensure:

- [ ] Uses design system fonts (display, body, or mono)
- [ ] Implements proper spacing scale (space-1 through space-24)
- [ ] Includes hover states with transform + shadow
- [ ] Has focus-visible styles for accessibility
- [ ] Uses CSS custom properties for colors
- [ ] Includes reduced-motion alternative
- [ ] Works responsively on mobile
- [ ] Meets WCAG AA contrast ratios
- [ ] Uses semantic HTML elements

---

## 🎯 Brand Guidelines

### Voice & Tone
- **Confident** but not arrogant
- **Optimistic** but realistic
- **Professional** but approachable
- **Urgent** but not panicked

### Visual Personality
- **Bold typography** - Commands attention
- **Dynamic motion** - Conveys momentum
- **Rich colors** - Expresses passion
- **Clean layouts** - Builds trust

---

## 📚 Resources

### Design Inspiration
- **Political Campaigns**: Obama 2008, Biden 2020 campaign sites
- **Modern SaaS**: Linear, Vercel, Stripe (for clean professionalism)
- **News Media**: NYT, Washington Post (for typography)

### Tools
- **Color Palette**: [Coolors.co](https://coolors.co)
- **Typography**: [Typescale.com](https://typescale.com)
- **Accessibility**: [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
- **Performance**: [Google PageSpeed Insights](https://pagespeed.web.dev)

---

## 🔄 Version History

**v2.0.0** - December 2025
- Complete design system overhaul
- Distinctive typography (Bricolage Grotesque, Plus Jakarta Sans, JetBrains Mono)
- Enhanced color system with 9-shade palettes
- Sophisticated animations and transitions
- Atmospheric backgrounds and gradients
- Improved accessibility features

**v1.0.0** - Initial release
- Basic Bootstrap styling
- System fonts
- Simple color schemes

---

## 💡 Pro Tips

1. **Start with type**: Get typography right, everything else follows
2. **Layer effects**: Combine gradients, shadows, patterns for depth
3. **Animate purposefully**: Every motion should enhance UX
4. **Test accessibility**: Use keyboard nav and screen readers
5. **Performance matters**: Optimize fonts, minimize animations on mobile
6. **Stay consistent**: Use design tokens (CSS variables) religiously

---

**Questions or feedback?** Open an issue on GitHub or contact the CampaignPress team.

*Designed with care for the future of political engagement.*
