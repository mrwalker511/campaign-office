# Gutenberg Block Implementation Guide

**Complete guide to developing custom Gutenberg blocks for CampaignPress**

---

## Overview

This guide explains how to create custom Gutenberg blocks for CampaignPress. It covers both PHP-registered blocks with render callbacks and React-based blocks for the block editor.

---

## Block Architecture

### CampaignPress Block Structure

**Two-Part System:**
1. **PHP Registration** - Server-side block definition and rendering
2. **React Component** - Block editor interface (optional)

**File Locations:**
- `includes/free/gutenberg-blocks.php` - Block registration
- `assets/react/blocks/` - React editor components
- `assets/css/blocks.css` - Block styles
- `assets/dist/js/blocks.js` - Compiled editor JavaScript

---

## Creating a New Block

### Step 1: Plan Your Block

**Define:**
- Block name (e.g., `campaignpress/my-block`)
- Attributes (data the block stores)
- UI controls (what editors can customize)
- Output (what appears on the front-end)

**Example: Event Countdown Block**
- **Name:** `campaignpress/event-countdown`
- **Attributes:** eventDate, eventTitle, backgroundColor
- **Controls:** Date picker, text input, color picker
- **Output:** Live countdown timer

### Step 2: Register the Block (PHP)

**Location:** `includes/free/gutenberg-blocks.php`

```php
/**
 * Register Event Countdown block
 */
function campaignpress_register_event_countdown_block() {
    register_block_type('campaignpress/event-countdown', array(
        'editor_script'   => 'campaignpress-blocks-js',
        'editor_style'    => 'campaignpress-blocks-css',
        'style'           => 'campaignpress-blocks-frontend-css',
        'render_callback' => 'campaignpress_render_event_countdown',
        'attributes'      => array(
            'eventDate' => array(
                'type'    => 'string',
                'default' => ''
            ),
            'eventTitle' => array(
                'type'    => 'string',
                'default' => 'Upcoming Event'
            ),
            'backgroundColor' => array(
                'type'    => 'string',
                'default' => '#0053c3'
            )
        )
    ));
}
add_action('init', 'campaignpress_register_event_countdown_block');
```

### Step 3: Create Render Callback (PHP)

```php
/**
 * Render Event Countdown block on front-end
 *
 * @param array $attributes Block attributes
 * @return string HTML output
 */
function campaignpress_render_event_countdown($attributes) {
    $event_date = isset($attributes['eventDate']) ?
                  sanitize_text_field($attributes['eventDate']) : '';
    $event_title = isset($attributes['eventTitle']) ?
                   sanitize_text_field($attributes['eventTitle']) : 'Upcoming Event';
    $bg_color = isset($attributes['backgroundColor']) ?
                sanitize_hex_color($attributes['backgroundColor']) : '#0053c3';

    // Validate date
    if (empty($event_date)) {
        return '<p>Please select an event date.</p>';
    }

    // Enqueue countdown script
    wp_enqueue_script('campaignpress-countdown',
                      get_template_directory_uri() . '/assets/js/countdown.js',
                      array('jquery'), null, true);

    // Build HTML
    ob_start();
    ?>
    <div class="cp-event-countdown"
         style="background-color: <?php echo esc_attr($bg_color); ?>;"
         data-event-date="<?php echo esc_attr($event_date); ?>">

        <h3 class="cp-event-countdown__title">
            <?php echo esc_html($event_title); ?>
        </h3>

        <div class="cp-event-countdown__timer">
            <div class="cp-countdown-unit">
                <span class="cp-countdown-value" data-unit="days">00</span>
                <span class="cp-countdown-label">Days</span>
            </div>
            <div class="cp-countdown-unit">
                <span class="cp-countdown-value" data-unit="hours">00</span>
                <span class="cp-countdown-label">Hours</span>
            </div>
            <div class="cp-countdown-unit">
                <span class="cp-countdown-value" data-unit="minutes">00</span>
                <span class="cp-countdown-label">Minutes</span>
            </div>
            <div class="cp-countdown-unit">
                <span class="cp-countdown-value" data-unit="seconds">00</span>
                <span class="cp-countdown-label">Seconds</span>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
```

### Step 4: Create React Component (Editor UI)

**Location:** `assets/react/blocks/EventCountdown.jsx`

```jsx
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ColorPicker } from '@wordpress/components';
import { useState } from '@wordpress/element';

registerBlockType('campaignpress/event-countdown', {
    title: 'Event Countdown',
    icon: 'clock',
    category: 'campaignpress',
    attributes: {
        eventDate: {
            type: 'string',
            default: ''
        },
        eventTitle: {
            type: 'string',
            default: 'Upcoming Event'
        },
        backgroundColor: {
            type: 'string',
            default: '#0053c3'
        }
    },

    edit: ({ attributes, setAttributes }) => {
        const { eventDate, eventTitle, backgroundColor } = attributes;

        return (
            <>
                <InspectorControls>
                    <PanelBody title="Event Settings">
                        <TextControl
                            label="Event Date"
                            type="datetime-local"
                            value={eventDate}
                            onChange={(value) => setAttributes({ eventDate: value })}
                        />
                        <TextControl
                            label="Event Title"
                            value={eventTitle}
                            onChange={(value) => setAttributes({ eventTitle: value })}
                        />
                        <ColorPicker
                            color={backgroundColor}
                            onChangeComplete={(color) =>
                                setAttributes({ backgroundColor: color.hex })
                            }
                            disableAlpha
                        />
                    </PanelBody>
                </InspectorControls>

                <div
                    className="cp-event-countdown"
                    style={{ backgroundColor }}
                >
                    <h3>{eventTitle}</h3>
                    <div className="cp-event-countdown__timer">
                        <div className="cp-countdown-unit">
                            <span className="cp-countdown-value">00</span>
                            <span className="cp-countdown-label">Days</span>
                        </div>
                        <div className="cp-countdown-unit">
                            <span className="cp-countdown-value">00</span>
                            <span className="cp-countdown-label">Hours</span>
                        </div>
                        <div className="cp-countdown-unit">
                            <span className="cp-countdown-value">00</span>
                            <span className="cp-countdown-label">Minutes</span>
                        </div>
                        <div className="cp-countdown-unit">
                            <span className="cp-countdown-value">00</span>
                            <span class Name="cp-countdown-label">Seconds</span>
                        </div>
                    </div>
                    {!eventDate && (
                        <p className="cp-block-notice">
                            Select an event date in the sidebar →
                        </p>
                    )}
                </div>
            </>
        );
    },

    save: () => {
        // Server-side rendering, return null
        return null;
    }
});
```

### Step 5: Add Styles

**Location:** `assets/css/blocks.css`

```css
/* Event Countdown Block */
.cp-event-countdown {
    padding: var(--wp--preset--spacing--12);
    border-radius: 8px;
    text-align: center;
    color: white;
}

.cp-event-countdown__title {
    font-family: var(--wp--preset--font-family--display);
    font-size: var(--wp--preset--font-size--2-xl);
    margin-bottom: var(--wp--preset--spacing--8);
}

.cp-event-countdown__timer {
    display: flex;
    justify-content: center;
    gap: var(--wp--preset--spacing--6);
}

.cp-countdown-unit {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cp-countdown-value {
    font-family: var(--wp--preset--font-family--mono);
    font-size: var(--wp--preset--font-size--4-xl);
    font-weight: 700;
    line-height: 1;
}

.cp-countdown-label {
    font-family: var(--wp--preset--font-family--body);
    font-size: var(--wp--preset--font-size--sm);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: var(--wp--preset--spacing--2);
}

/* Editor-only styles */
.cp-block-notice {
    background: rgba(255, 255, 255, 0.2);
    padding: var(--wp--preset--spacing--4);
    border-radius: 4px;
    margin-top: var(--wp--preset--spacing--6);
    font-size: var(--wp--preset--font-size--sm);
}
```

### Step 6: Build and Test

```bash
# Build React components
npm run build

# Start dev server for hot reloading
npm run dev

# Test in block editor
# 1. Create new post/page
# 2. Add "Event Countdown" block
# 3. Configure settings in sidebar
# 4. Preview/publish
# 5. Verify countdown works on front-end
```

---

## Block Patterns

### Dynamic vs. Static Blocks

**Dynamic Blocks (Recommended for CampaignPress):**
- PHP render callback
- Data fetched on page load
- React for editor UI only
- `save: () => null` in React

**Static Blocks:**
- HTML saved to post_content
- No server-side processing
- React for both editor and output
- More complex to update

### Using WordPress Design Tokens

**Always use WordPress CSS variables:**

```css
/* Good */
.my-block {
    color: var(--wp--preset--color--primary);
    font-family: var(--wp--preset--font-family--display);
    padding: var(--wp--preset--spacing--8);
}

/* Bad - don't hardcode */
.my-block {
    color: #0053c3;
    font-family: "Bricolage Grotesque";
    padding: 32px;
}
```

### Block Attributes Best Practices

**Type Safety:**
```php
'attributes' => array(
    'count' => array(
        'type' => 'number',        // or 'string', 'boolean', 'object', 'array'
        'default' => 10
    ),
    'title' => array(
        'type' => 'string',
        'default' => ''
    ),
    'isEnabled' => array(
        'type' => 'boolean',
        'default' => true
    ),
    'items' => array(
        'type' => 'array',
        'default' => array()
    )
)
```

### Security in Blocks

**Sanitize Inputs:**
```php
// Text
$title = sanitize_text_field($attributes['title']);

// HTML
$content = wp_kses_post($attributes['content']);

// URL
$link = esc_url($attributes['link']);

// Color
$color = sanitize_hex_color($attributes['color']);

// Number
$count = absint($attributes['count']);
```

**Escape Outputs:**
```php
<h3><?php echo esc_html($title); ?></h3>
<a href="<?php echo esc_url($link); ?>">Link</a>
<div style="color: <?php echo esc_attr($color); ?>">Content</div>
```

---

## Advanced Techniques

### Server-Side Data Fetching

```php
function campaignpress_render_events_block($attributes) {
    $count = isset($attributes['count']) ? absint($attributes['count']) : 5;

    $events = get_posts(array(
        'post_type' => 'cp_event',
        'posts_per_page' => $count,
        'meta_key' => 'event_date',
        'orderby' => 'meta_value',
        'order' => 'ASC'
    ));

    if (empty($events)) {
        return '<p>No upcoming events.</p>';
    }

    ob_start();
    ?>
    <div class="cp-events-list">
        <?php foreach ($events as $event): ?>
            <div class="cp-event-item">
                <h4><?php echo esc_html(get_the_title($event)); ?></h4>
                <p><?php echo esc_html(get_post_meta($event->ID, 'event_date', true)); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
```

### Block Variations

```jsx
import { registerBlockVariation } from '@wordpress/blocks';

registerBlockVariation('campaignpress/donation-button', {
    name: 'donation-button-recurring',
    title: 'Recurring Donation',
    attributes: {
        recurring: true,
        amounts: [10, 25, 50, 100]
    }
});

registerBlockVariation('campaignpress/donation-button', {
    name: 'donation-button-one-time',
    title: 'One-Time Donation',
    attributes: {
        recurring: false,
        amounts: [25, 50, 100, 250]
    }
});
```

### Inner Blocks

```jsx
import { InnerBlocks } from '@wordpress/block-editor';

edit: () => {
    return (
        <div className="cp-container">
            <InnerBlocks
                allowedBlocks={['core/heading', 'core/paragraph', 'campaignpress/issue-card']}
                template={[
                    ['core/heading', { level: 2, content: 'Our Issues' }],
                    ['campaignpress/issue-card', {}]
                ]}
            />
        </div>
    );
},

save: () => {
    return (
        <div className="cp-container">
            <InnerBlocks.Content />
        </div>
    );
}
```

---

## Testing Blocks

### Manual Testing Checklist

- [ ] Block appears in inserter
- [ ] Block icon displays correctly
- [ ] Controls function properly
- [ ] Preview updates in real-time
- [ ] Saved data persists correctly
- [ ] Front-end renders as expected
- [ ] Responsive on mobile
- [ ] Accessibility (keyboard nav, screen reader)
- [ ] Party color schemes apply correctly
- [ ] No console errors

### Automated Testing

```javascript
// Example Jest test (future implementation)
import { render, screen } from '@testing-library/react';
import EventCountdown from './EventCountdown';

test('renders event title', () => {
    const attributes = {
        eventTitle: 'Election Day',
        eventDate: '2024-11-05',
        backgroundColor: '#0053c3'
    };

    render(<EventCountdown attributes={attributes} />);

    expect(screen.getByText('Election Day')).toBeInTheDocument();
});
```

---

## Troubleshooting

### Block Not Appearing in Inserter

**Check:**
1. Block is registered in PHP
2. JavaScript is enqueued
3. Build completed: `npm run build`
4. No JavaScript errors in console
5. Block category exists

### Attributes Not Saving

**Check:**
1. Attribute types match in PHP and React
2. `setAttributes()` called correctly
3. No typos in attribute names
4. Database has space for data

### Render Callback Not Working

**Check:**
1. Function name matches registration
2. Function is defined before `add_action('init')`
3. PHP errors in debug.log
4. Correct sanitization/escaping

---

## Best Practices

### DO

✓ Use WordPress design tokens
✓ Sanitize all inputs
✓ Escape all outputs
✓ Use server-side rendering for dynamic data
✓ Provide editor preview
✓ Make blocks responsive
✓ Test accessibility
✓ Document attributes

### DON'T

✗ Hardcode colors or fonts
✗ Trust user input
✗ Output raw HTML
✗ Use inline styles (use CSS classes)
✗ Create overly complex blocks
✗ Ignore mobile users
✗ Skip accessibility testing

---

## Resources

### WordPress Documentation
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Block API Reference](https://developer.wordpress.org/block-editor/reference-guides/block-api/)
- [Components Reference](https://developer.wordpress.org/block-editor/reference-guides/components/)

### CampaignPress Files
- `includes/free/gutenberg-blocks.php` - Existing blocks
- `assets/react/blocks/` - React components
- `docs/DESIGN_SYSTEM.md` - Design tokens

---

**Last Updated:** December 28, 2025
**Version:** 2.0.0
