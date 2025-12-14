# Long-Form Content Module

## Overview
A typography-optimized layout module for long-form articles, policy briefs, and detailed content. Features maximum readability with proper spacing, hierarchy, and callout boxes.

## Features

### Layout
- **Narrow & Centered**: `max-w-3xl mx-auto` for optimal reading width
- **Responsive**: Adapts typography sizes for mobile and desktop
- **Semantic HTML**: Proper `<article>`, `<header>`, `<aside>` structure

### Typography Optimization

#### Paragraphs
- **Font Size**: `text-lg` (18px) for body text
- **Line Height**: `leading-relaxed` (1.625) for readability
- **Bottom Margin**: `mb-6` for visual breathing room
- **Color**: `text-neutral-700` for reduced eye strain

#### Headings
- **H1**: `text-4xl md:text-5xl`, serif font, bold
- **H2**: `text-3xl md:text-4xl`, serif font, bold, `mt-12 mb-6`
- **H3**: Used in callout boxes, `text-lg`, bold

#### Intro Paragraph
- Larger size: `text-xl md:text-2xl`
- Lighter weight: `font-light`
- Extra spacing: `mb-8`

### Blockquotes
Styled with:
- Left border accent: `border-l-4 border-accent-500`
- Italic text: `italic`
- Larger size: `text-xl md:text-2xl`
- Padding: `pl-6 md:pl-8`
- Vertical spacing: `my-10`

### Callout Boxes

Three variants included:

#### 1. Important Note (Blue/Accent)
```php
<aside class="my-10 p-6 md:p-8 bg-accent-50 border-l-4 border-accent-500 rounded-r-xl">
    <!-- Icon + Content -->
</aside>
```

#### 2. Success Story (Brand Green)
```php
<aside class="my-10 p-6 md:p-8 bg-brand-50 border-l-4 border-brand-500 rounded-r-xl">
    <!-- Icon + Content -->
</aside>
```

#### 3. Urgent Action (Orange Warning)
```php
<aside class="my-10 p-6 md:p-8 bg-orange-50 border-l-4 border-orange-500 rounded-r-xl">
    <!-- Icon + Content -->
</aside>
```

Each callout includes:
- Icon container (10×10, rounded)
- Bold heading
- Descriptive text
- Proper color coordination

### Lists
Styled with checkmark bullets:
- Custom checkmark icons in accent color
- Proper spacing: `space-y-3`
- Aligned with flex layout
- Readable text size

## Usage

```php
<?php get_template_part('parts/organisms/content-longform'); ?>
```

### Customization

Replace the static content with WordPress functions:

```php
<h1><?php the_title(); ?></h1>
<time datetime="<?php echo get_the_date('c'); ?>"><?php echo get_the_date(); ?></time>
<?php the_content(); ?>
```

### Adding Custom Callouts

Copy the callout structure and modify colors:

```php
<aside class="my-10 p-6 md:p-8 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0 w-10 h-10 bg-purple-500 text-white rounded-lg flex items-center justify-center">
            <!-- Your SVG icon -->
        </div>
        <div>
            <h3 class="text-lg font-bold text-purple-900 mb-2">Your Title</h3>
            <p class="text-base text-purple-800 leading-relaxed">Your content</p>
        </div>
    </div>
</aside>
```

## Design System Compliance

✓ **Max Width**: `max-w-3xl` (no arbitrary values)  
✓ **Typography**: Design System scale (`text-lg`, `text-xl`, etc.)  
✓ **Line Heights**: `leading-relaxed`, `leading-tight`  
✓ **Colors**: `brand-*`, `accent-*`, `neutral-*`, `orange-*`  
✓ **Spacing**: Standard scale (`mb-6`, `my-10`, `p-6`, `md:p-8`)  
✓ **Breakpoints**: Mobile-first (`md:`)

## Accessibility

- Semantic HTML5 elements
- Proper heading hierarchy (H1 → H2 → H3)
- `<time>` element with datetime attribute
- ARIA labels where appropriate
- Sufficient color contrast ratios
- Focus states on interactive elements

## Reading Experience

Optimized for:
- **Line Length**: 60-80 characters per line (max-w-3xl)
- **Line Height**: 1.625 for body text
- **Font Size**: 18px base (comfortable reading)
- **Contrast**: Neutral-700 on white (reduces eye strain)
- **Spacing**: Generous margins between elements
