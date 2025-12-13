# CampaignPress Design System (v2.0)

**Compatibility:** WordPress 6.9+ | **Architecture:** `theme.json` Native
**Status:** Production Ready

---

## 1. Typography

We use a distinct tri-font stack to convey authority and modernity.

### Font Families
| Role | Font Name | CSS Variable | Weights | Usage |
| :--- | :--- | :--- | :--- | :--- |
| **Display** | **Bricolage Grotesque** | `var(--wp--preset--font-family--display)` | 400, 500, 600, 700, 800 | Headings (H1-H6), Hero, Buttons |
| **Body** | **Plus Jakarta Sans** | `var(--wp--preset--font-family--body)` | 300, 400, 500, 600, 700, 800 | Paragraphs, UI Text, Metadata |
| **Mono** | **JetBrains Mono** | `var(--wp--preset--font-family--mono)` | 400, 500, 600, 700 | Stats, Countdowns, Data tables |

### Fluid Type Scale
*Text scales automatically between mobile and desktop using `clamp()`.*

| Size Name | CSS Variable | Mobile | Desktop | Use Case |
| :--- | :--- | :--- | :--- | :--- |
| **xs** | `var(--wp--preset--font-size--xs)` | 12px | 14px | Captions, Disclaimers |
| **sm** | `var(--wp--preset--font-size--sm)` | 14px | 16px | Meta data, UI labels |
| **base** | `var(--wp--preset--font-size--base)` | 16px | 18px | Standard Body Text |
| **lg** | `var(--wp--preset--font-size--lg)` | 18px | 22px | Lead paragraphs, Intros |
| **xl** | `var(--wp--preset--font-size--xl)` | 20px | 28px | H4, Subheads |
| **2xl** | `var(--wp--preset--font-size--2-xl)` | 24px | 36px | H3, Card Titles |
| **3xl** | `var(--wp--preset--font-size--3-xl)` | 32px | 56px | H2, Section Headers |
| **4xl** | `var(--wp--preset--font-size--4-xl)` | 40px | 80px | H1, Hero Titles |

### Rules & Readability
* **Max Reading Width:** 65ch (`var(--cp-max-reading-width)`)
* **Line Height:** 1.75 for body (`--cp-leading-relaxed`)
* **Text Wrap:** Use `text-wrap: pretty` for body and `text-wrap: balance` for headings.

---

## 2. Color System

Colors are managed via `theme.json`. Never use hex codes directly in CSS.

### Primary Palette (Democrat Blue Default)
*Used for branding, links, and backgrounds.*

| Shade | Variable | Hex (Ref) |
| :--- | :--- | :--- |
| **50** | `var(--wp--preset--color--primary-50)` | #e6eef9 |
| **500 (Main)**| `var(--wp--preset--color--primary)` | **#0053c3** |
| **900** | `var(--wp--preset--color--primary-900)` | #001127 |

### Accent Palette (Orange)
*Used for CTAs, highlights, and gradients.*

| Shade | Variable | Hex (Ref) |
| :--- | :--- | :--- |
| **500 (Main)**| `var(--wp--preset--color--accent)` | **#ff8800** |
| **Text** | `var(--wp--preset--color--accent-text)` | #cc6d00 |

### Semantic Colors
| State | Variable | Purpose |
| :--- | :--- | :--- |
| **Success** | `var(--wp--preset--color--success)` | Form success, wins |
| **Warning** | `var(--wp--preset--color--warning)` | Alerts, notices |
| **Error** | `var(--wp--preset--color--error)` | Validation errors |
| **Info** | `var(--wp--preset--color--info)` | Contextual help |

### Party Themes (Body Classes)
The site changes color personality based on the body class:
1.  `color-scheme-democrat-blue` (Default)
2.  `color-scheme-republican-red`
3.  `color-scheme-independent-purple`
4.  `color-scheme-green-party`

---

## 3. Spacing & Layout

We use an **8px grid system**. All padding and margins must use these tokens.

| Step | Variable | Size |
| :--- | :--- | :--- |
| **1** | `var(--wp--preset--spacing--1)` | 4px |
| **2** | `var(--wp--preset--spacing--2)` | 8px |
| **4** | `var(--wp--preset--spacing--4)` | 16px |
| **6** | `var(--wp--preset--spacing--6)` | 24px |
| **8** | `var(--wp--preset--spacing--8)` | 32px |
| **12** | `var(--wp--preset--spacing--12)` | 48px |
| **16** | `var(--wp--preset--spacing--16)` | 64px |
| **24** | `var(--wp--preset--spacing--24)` | 96px |

### Layout Containers
* **Content Size:** 800px (Blog posts, text)
* **Wide Size:** 1200px (Grids, standard sections)
* **Full Width:** 100% (Hero, colored backgrounds)

---

## 4. UI Components

### Buttons
Buttons use gradient backgrounds and sophisticated hover lifts.

* **Primary Button:**
    * Class: `.wp-block-button__link` or `.button-primary`
    * Bg: `var(--wp--preset--gradient--primary-gradient)`
    * Hover: `translateY(-3px)`, `shadow-xl`
* **Animation:** Use `buttonPulse` for primary CTAs.

### Cards (Issues/Features)
* **Bg:** `var(--wp--preset--color--white)`
* **Border:** 1px solid `var(--wp--preset--color--neutral-200)`
* **Radius:** `var(--cp-border-radius-lg)` (12px)
* **Hover:** `translateY(-4px)`, add `var(--wp--preset--shadow--xl)`

### Progress Meter
* **Text:** `var(--wp--preset--font-family--mono)`
* **Bar:** Gradient `primary` to `accent`.
* **Effect:** Animated shine overlay.

---

## 5. Effects & Animation

### Shadows
| Size | Variable |
| :--- | :--- |
| **SM** | `var(--wp--preset--shadow--sm)` |
| **MD** | `var(--wp--preset--shadow--md)` |
| **LG** | `var(--wp--preset--shadow--lg)` |
| **XL** | `var(--wp--preset--shadow--xl)` |
| **2XL** | `var(--wp--preset--shadow--2-xl)` |

### Animation Keyframes
* `heroFadeInUp`: Staggered entrance for hero text (0.2s delay per item).
* `buttonPulse`: Subtle breathing shadow effect for CTAs.
* `progressShine`: Moving light reflection on progress bars.
* `heroGradientShift`: Slow background movement for atmospheric depth.

*Note: All animations must respect `prefers-reduced-motion`.*

---

## 6. Implementation Checklist

When creating new blocks or templates:
1.  [ ] **Use Variables:** Never write `color: #0053c3`. Use `var(--wp--preset--color--primary)`.
2.  [ ] **Use Clamps:** Never write `font-size: 40px`. Use `var(--wp--preset--font-size--4-xl)`.
3.  [ ] **Accessibility:** Ensure focus states use `outline: 3px solid var(--wp--preset--color--accent)`.
4.  [ ] **Dark Mode:** Use `var(--cp-background)` and `var(--cp-text)` abstractions where possible to support future toggles.