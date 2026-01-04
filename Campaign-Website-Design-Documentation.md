# Campaign Website Design Documentation

## Overview
This document describes the layout and design specifications for a political campaign website for a Georgetown City Council candidate. The design follows a Republican/conservative aesthetic with patriotic color schemes and professional typography.

---

## Design System

### Color Palette
- **Primary Red**: `#d32f2f` to `#E81B23` (Republican Red)
- **Primary Dark**: `#b71c1c` to `#c2161d` (Darker red for hover states)
- **Secondary Navy**: `#1a237e` to `#003057` (Patriotic Navy Blue)
- **Background Light**: `#f6f6f8` to `#fdfbfc`
- **Background Dark**: `#101622` to `#0a1120`

### Typography
- **Display Font**: Public Sans (bold weights: 700, 800, 900)
- **Body Font**: Noto Sans (weights: 400, 500, 700)
- **Headings**: Black weight (900), tight tracking
- **Body Text**: Regular weight (400), relaxed leading

### Border Radius
- Default: `0.25rem`
- Large: `0.5rem`
- XL: `0.75rem`
- Full: `9999px` (pills/badges)

### Spacing System
- Container Max Width: `1200px` to `1280px`
- Section Padding: `3rem` to `5rem` (vertical)
- Component Gap: `1rem` to `2rem`

---

## Global Components

### Navigation Header
**Layout**: Sticky header with backdrop blur
- **Height**: 64px (h-16)
- **Background**: White with 95% opacity, backdrop blur
- **Border**: Bottom border with subtle gray
- **Logo Section**:
  - Voting icon (Material Symbols "how_to_vote")
  - Campaign name in bold with red accent
- **Navigation Links** (Desktop):
  - Text: 14px, bold, gray
  - Hover: Primary red color
  - Current page: Red text, bold
- **Action Buttons**:
  - Volunteer: Gray background, bordered
  - Donate: Primary red, white text, shadow
- **Mobile**: Hamburger menu icon

### Footer
**Layout**: Dark background with multi-column grid
- **Background**: Dark navy/black (`#111318`)
- **Grid**: 4 columns on desktop, stacked on mobile
- **Sections**:
  1. Campaign branding + mission statement
  2. Campaign links (About, Platform, Events)
  3. Get Involved links (Volunteer, Donate)
  4. Contact information (email, phone, address)
- **Disclaimer**: "Paid for by [Candidate] Campaign" in bordered box
- **Social Media**: Icon links with hover effects

---

## Page-Specific Layouts

### 1. HOMEPAGE

#### Hero Section
**Layout**: Two-column grid (lg:grid-cols-2)
- **Left Column** (Order 2 on mobile):
  - Badge: "Republican Candidate for City Council" in red pill badge
  - H1: 4xl to 6xl, black weight
  - Main tagline: "Preserving Heritage. Planning Our Future."
  - Red accent on key phrase
  - Description paragraph: Gray text, relaxed leading
  - CTA buttons: Primary "Join Campaign" + Secondary "Watch Video"

- **Right Column** (Order 1 on mobile):
  - Large candidate photo (aspect-ratio 4/3)
  - Rounded corners (2xl)
  - Gradient glow effect behind image
  - Floating endorsement card (absolute positioned):
    - Police badge icon
    - "Endorsed by Local Police Association"
    - Shadow and border

#### Quote Section
**Layout**: Centered container with light gray background
- Max width: 960px
- Large pull quote (2xl to 3xl)
- Candidate signature image
- Vertical padding: 3rem

#### Key Priorities Section
**Layout**: Three-column grid on desktop
- **Section Header**:
  - Eyebrow text: "KEY PRIORITIES" in uppercase, red
  - H2: "Building a Stronger Community"

- **Priority Cards** (3 cards):
  - White background with shadow
  - Icon in red circle (48px size)
  - Title: Bold, xl size
  - Description: Gray text, small
  - Hover effect: Shadow increases, border turns red
  - Icons: account_balance, local_police, domain

#### Events Section
**Layout**: List view with light background
- **Section Header**: Title + "View Full Calendar" link
- **Event Cards** (2 shown):
  - Flexbox row layout
  - Date badge: Red background box (OCT 15)
  - Time with clock icon
  - Event title (bold)
  - Location (gray text)
  - RSVP button (outlined red)

#### Map/Location Section
**Layout**: Full-width background image with overlay
- Height: 400px
- Dark overlay (70% opacity)
- Centered content:
  - Location pin icon (48px)
  - H2: "Proudly Serving Georgetown"
  - Description text
  - "Find Your Precinct" button (white background)

#### Newsletter Section
**Layout**: Full-width red background
- Max width: 800px container
- Centered text content
- Email form:
  - Flexbox row (stacked on mobile)
  - Email input with white/10 background
  - Submit button (white background, red text)
- Privacy disclaimer text

---

### 2. ABOUT [CANDIDATE'S NAME]

#### Hero Section
**Layout**: Two-column grid
- **Left Column**:
  - Eyebrow: "DEDICATED TO SERVICE" with red line
  - H1: "A Voice for Georgetown Values"
  - H2 subtitle: Description of candidate
  - Two CTA buttons

- **Right Column**:
  - Portrait image (aspect 4/5 on mobile, square on desktop)
  - Decorative gradient blobs in background

#### Community Section
**Layout**: Light background, centered content
- **Intro Text**: Max width 3xl
- **Feature Grid**: 2x2 grid of cards
  - Each card:
    - Left border accent (4px, alternating red/navy)
    - Icon in colored circle (48px)
    - Title + description
    - Hover effect: Lift up slightly
  - Topics: Family First, Business Owner, Conservative Values, Community Servant

#### Quote Section
**Layout**: Full-width gradient (red to dark red)
- Centered quote with quotation marks
- White text, italic
- Attribution line

#### Experience Timeline
**Layout**: Vertical timeline with left border
- **Section**: "KEY MILESTONES"
- **Timeline Items**:
  - Border line on left (2px, red/20 opacity)
  - Year markers (red circles, absolute positioned)
  - Year + Title + Description
  - Final item has navy circle (current/2024)

#### Photo Gallery
**Layout**: Three-column grid
- Equal height images (h-64)
- Rounded corners
- Hover overlay with caption
- Captions: "Listening to residents", "4th of July Parade", "Supporting local growth"

#### Testimonials Section
**Layout**: Three-column grid
- **Cards**:
  - Top border accent (4px red or navy)
  - Quote icon (watermark style)
  - Quote text (italic)
  - Profile section:
    - Small circular photo
    - Name (bold)
    - Title (small, gray)

#### CTA Section
**Layout**: Dark background, horizontal layout
- Text on left, buttons on right
- Two buttons: "Volunteer" (red) + "Donate" (outlined)

---

### 3. CONTACT US

#### Hero Section
**Layout**: Centered text, light background
- Badge: "CONTACT THE CAMPAIGN"
- H1: "Let Your Voice Be Heard"
- Description paragraph
- Vertical padding: 3rem to 6rem

#### Main Content Section
**Layout**: Two-column grid (lg:grid-cols-2)

##### Left Column: Contact Form
**Card**: White background, rounded (2xl), shadowed
- **Form Fields**:
  - Grid layout for First/Last name (2 columns)
  - Full-width email
  - Grid for Zip/Phone (2 columns)
  - Textarea for message (h-32)
  - Checkbox: Newsletter subscription
  - Submit button (red, full width)

##### Right Column: Contact Information
**Sections**:
1. **Direct Contacts** (3 cards):
   - Email card (mail icon)
   - Phone card (call icon)
   - Address card (location icon)
   - Each card: White background, icon circle (red/10), hover shadow

2. **Map** (embedded image):
   - Rounded corners
   - Height: 256px
   - Border and shadow

3. **Social Media**:
   - Title: "Connect on Social"
   - Icon buttons (X, Facebook, Instagram)
   - Circular buttons with hover effects

---

### 4. ISSUES & VISION

#### Hero Banner
**Layout**: Full-width background image with gradient overlay
- Gradient: Red to dark (85% to 95% opacity)
- Min height: 480px
- **Content** (centered):
  - Badge: "Vision for Georgetown"
  - H1: "Preserving Our Values, Building Our Future"
  - Description text (white/95)

#### Main Content Layout
**Layout**: Sidebar + Main content

##### Sidebar (Desktop Only)
**Sticky positioning**, width 256px
- **Jump Links**:
  - Vertical nav with left border
  - Active link: Red border, red text
  - Hover: Border appears
  - Links: Fiscal, Growth, Safety, Liberty

- **CTA Card**:
  - Red/5 background
  - Border (red/10)
  - "Have a question?" prompt
  - Contact button

##### Main Content Area
**Sections** (scroll-mt-28 for anchor links):

1. **Fiscal Responsibility**:
   - Icon header (trending_up in red circle)
   - Description paragraph
   - Two-column card grid:
     - "The Problem" (gray background)
     - "My Solution" (red/5 background, red border)
   - "Read detailed proposal" link

2. **Managed Growth**:
   - Icon header (traffic)
   - Description
   - Card with two columns:
     - Left: Text content with icon bullets
     - Right: Background image with gradient overlay
     - Badge overlay on image

3. **Pull Quote Section**:
   - Full-width red background
   - Centered quote (italic, 2xl to 3xl)
   - Quotation mark icon
   - Decorative dot pattern (10% opacity)

4. **Public Safety**:
   - 2/3 + 1/3 grid layout
   - Left: Description + policy cards
   - Right: Background image with overlay
   - Policy cards with icons and descriptions

5. **Economic Liberty**:
   - Single card with gradient background
   - Two columns: Text + Stat callout
   - Stat: "200+ Local Businesses" in large red text
   - Bulleted list with red markers

#### Vision Section
**Layout**: Full-width white/light background
- Centered content (max-w-960px)
- Title: "Georgetown 2030"
- Four-column icon grid:
  - Icons: account_balance, family_restroom, park, verified_user
  - Labels under each

#### Final CTA
**Layout**: Dark background with gradient blobs
- Centered text
- Two buttons (Donate + Volunteer)
- Decorative blur effects in corners

---

### 5. NEWS & UPDATES

#### Hero Section
**Layout**: Full-width gradient background image
- Gradient overlay (red to dark, 90% to 95%)
- Min height: 400px
- **Content** (centered):
  - Badge with icon: "OFFICIAL CAMPAIGN UPDATES"
  - H1: "News from the Trail"
  - Description
  - Newsletter signup form (inline)

#### Filter Bar
**Layout**: Sticky below header (top-[65px])
- Horizontal scrolling pill buttons
- Active filter: Red background
- Inactive: Gray background with hover effect
- Filters: All, Press Releases, Events, Interviews, Policy Briefs

#### Featured Story
**Layout**: Large horizontal card
- Two columns (3:2 ratio on desktop)
- Left: Background image (min-h-300px)
  - Badge overlay: "PRESS RELEASE"
- Right: Content padding (2.5rem)
  - Date with icon
  - H3 title (hover turns red)
  - Description
  - "Read Full Story" link with arrow

#### Main Content Grid
**Layout**: Two-column structure

##### Left Column: News Grid
**Grid**: 2 columns on desktop
- **News Cards** (4 shown):
  - Image top (h-48)
  - Content padding
  - Category badge (red, uppercase)
  - Date (gray, right aligned)
  - Title (hover turns red)
  - Description (line-clamp-3)
  - "Read More" text
  - Hover: Shadow increases

- **Load More Button**: Centered below grid

##### Right Column: Sidebar
**Sections**:

1. **Get Involved Card**:
   - Red/5 background
   - Red/20 border
   - CTA button (full width, red)

2. **Upcoming Events**:
   - Title with icon
   - Event list (3 items):
     - Date box (bordered square)
     - Title + time/location
     - Hover: Title turns red

3. **Social Media**:
   - Title with icon
   - Circular icon buttons (3)
   - Hover: Background turns red

---

### 6. GET INVOLVED (VOLUNTEER)

#### Hero Section
**Layout**: Full-width background image with gradient
- Gradient: Red to dark (90% to 85% opacity)
- Vertical padding: 5rem to 8rem
- **Content** (max-w-3xl):
  - Badge: "Volunteer with Team Smith"
  - H1: "Let's Keep Georgetown Strong"
  - Description paragraph
  - Two CTA buttons:
    - "Sign Up Now" (white background)
    - "Donate $25" (transparent with border)

#### Main Content Layout
**Layout**: Two-column grid (7:5 ratio)

##### Left Column: Information
**Sections**:

1. **Introduction**:
   - H2: "Your Time Makes a Difference"
   - Description paragraph
   - Stats bar (3 columns):
     - "2,500+ Doors Knocked"
     - "450 Volunteers"
     - "100% Committed"
   - Border top/bottom

2. **Ways to Help**:
   - H3 title
   - 2x2 grid of help cards:
     - Icon (48px circle, red/50 background)
     - Title (bold)
     - Description (small, gray)
     - Hover: Border turns red/50
   - Activities: Block Walking, Phone Banking, Yard Signs, Host Coffee

3. **Upcoming Events**:
   - Title with "View All" link
   - Event cards in bordered container:
     - Date badge (red/50 background)
     - Event title + location
     - RSVP button
   - Hover: Background lightens

##### Right Column: Volunteer Form
**Sticky Card** (top-24)
- **Form Header**:
  - H3: "Join the Team"
  - Helper text

- **Form Fields**:
  - Grid: First/Last name (2 columns)
  - Full-width: Email
  - Grid: Phone/Zip (2 columns)
  - Checkbox group: Volunteer interests
    - Walking Neighborhoods
    - Making Phone Calls
    - Hosting Meet & Greet
    - Placing Yard Sign
  - Submit button (red, full width)
  - Privacy disclaimer (small text)

---

## Component Patterns

### Buttons
**Primary Button**:
- Background: Red
- Text: White, bold
- Padding: `px-6 py-3`
- Border radius: `lg`
- Hover: Darker red, scale-105
- Shadow: Medium

**Secondary Button**:
- Background: Light gray or transparent
- Border: 1px solid gray/red
- Text: Dark gray or red
- Hover: Background lightens

### Cards
**Standard Card**:
- Background: White
- Border: 1px gray
- Border radius: `xl`
- Padding: `p-6`
- Shadow: `sm`
- Hover: Shadow increases to `md`

**Feature Card**:
- Border-left accent (4px)
- Icon circle (48px)
- Title + description
- Hover: Lift transform

### Icons
**Size**: 24px to 48px
**Source**: Material Symbols Outlined
**Common Icons**:
- how_to_vote, local_police, account_balance
- trending_up, traffic, storefront
- mail, call, location_on, event

### Forms
**Input Fields**:
- Height: 44px to 48px
- Border: 1px gray
- Border radius: `lg`
- Padding: `px-4`
- Focus: Red ring, red border

**Labels**:
- Font: Bold, 14px
- Color: Dark gray
- Margin bottom: 0.375rem

---

## Responsive Breakpoints

### Mobile (< 768px)
- Single column layouts
- Stacked navigation (hamburger menu)
- Full-width buttons
- Reduced padding
- Smaller typography scales

### Tablet (768px - 1024px)
- Two-column grids where applicable
- Condensed spacing
- Medium typography

### Desktop (> 1024px)
- Multi-column layouts (3-4 columns)
- Sidebar layouts appear
- Maximum container width enforced
- Full navigation visible
- Hover states active

---

## Dark Mode Support

All pages include dark mode variants using Tailwind's `dark:` prefix:
- **Backgrounds**: Switch to dark navy/black
- **Text**: White or light gray
- **Borders**: Darker gray variants
- **Cards**: Dark gray backgrounds with subtle borders
- **Maintains**: Red accent color remains consistent

---

## Image Specifications

### Hero Images
- Aspect ratio: 16:9 or 4:3
- Resolution: Minimum 1920x1080
- Format: JPEG or WebP
- Optimization: Compressed for web

### Portrait Photos
- Aspect ratio: 4:5 or 1:1
- Resolution: Minimum 800x1000
- Format: JPEG or WebP

### Thumbnail Images
- Aspect ratio: 16:9
- Resolution: 800x450
- Format: JPEG or WebP

### Icons
- Source: Material Symbols Outlined
- CDN: Google Fonts
- Fill: 0, Weight: 400

---

## WordPress Implementation Notes

### Recommended Plugins
- **Page Builder**: Elementor or Gutenberg blocks
- **Forms**: Contact Form 7 or WPForms
- **Events**: The Events Calendar
- **Newsletter**: Mailchimp for WordPress
- **SEO**: Yoast SEO

### Custom Post Types
1. **Events**
   - Title, Date, Time, Location
   - RSVP functionality
   - Featured image

2. **News/Updates**
   - Title, Content, Excerpt
   - Category taxonomy (Press Release, Town Hall, etc.)
   - Featured image
   - Date

3. **Issues**
   - Title, Description
   - Icon selection
   - Problem/Solution sections

### Theme Customization
- Custom color scheme in theme customizer
- Typography options for fonts
- Header/footer template parts
- Reusable block patterns for sections

---

## Accessibility Considerations

- **Color Contrast**: Meets WCAG AA standards
- **Focus States**: Visible keyboard focus rings
- **Semantic HTML**: Proper heading hierarchy
- **Alt Text**: All images have descriptive alt attributes
- **ARIA Labels**: Screen reader text for icon buttons
- **Form Labels**: Properly associated with inputs
- **Skip Links**: Available for keyboard navigation

---

## Performance Optimization

- **Images**: Lazy loading, responsive images
- **Fonts**: Preconnect to Google Fonts, font-display: swap
- **CSS**: Tailwind with PurgeCSS for production
- **JavaScript**: Minimal, defer non-critical scripts
- **Caching**: Browser caching headers
- **CDN**: Serve static assets from CDN

---

## Browser Support

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)
- Mobile Safari (iOS 12+)
- Chrome Mobile (Android 8+)

---

*End of Documentation*
