# Custom Icons Integration Guide

## Overview

Your CampaignPress theme now includes a complete custom icons integration system that allows you to easily add and use custom SVG icons throughout your campaign website. This system seamlessly integrates with your existing Heroicons infrastructure while providing dedicated functionality for your custom icons.

## **Quick Start**

### Step 1: Add Your Icons
Place your custom SVG icons in the `/assets/icons/custom/` directory:

```
/assets/icons/custom/
├── campaign-logo.svg
├── candidate-signature.svg
├── ballot-box.svg
└── victory-flag.svg
```

### Step 2: Access the Custom Icons Admin Page
1. Go to **Appearance > Custom Icons** in your WordPress admin
2. View all your custom icons in an organized grid
3. Click any icon to copy its PHP function code

### Step 3: Use Icons in Your Content

#### **In PHP Templates**
```php
<?php campaignpress_custom_icon( 'campaign-logo', array( 'class' => 'custom-icon-lg' ) ); ?>
```

#### **In Gutenberg Block Editor**
1. Add the "Custom Icon" block from the CampaignPress category
2. Select your icon from the dropdown
3. Choose size and styling options
4. The icon will render with proper accessibility attributes

#### **With Shortcode (if needed)**
```php
[custom_icon name="campaign-logo" size="lg"]
```

## **Integration Options**

### **Option 1: Direct PHP Integration (Recommended)**

Use the helper functions in any PHP template:

```php
// Basic usage
<?php campaignpress_custom_icon( 'your-icon-name' ); ?>

// With custom classes
<?php campaignpress_custom_icon( 'your-icon-name', array( 'class' => 'custom-icon-lg text-primary' ) ); ?>

// With accessibility
<?php campaignpress_custom_icon( 'your-icon-name', array( 
    'aria-label' => 'Campaign Information',
    'width' => '32',
    'height' => '32'
) ); ?>
```

### **Option 2: Gutenberg Block Integration**

The custom icons appear in the "Custom Icon" block with:
- Visual icon picker
- Size controls (sm, md, lg, xl)
- Custom CSS class field
- ARIA label field
- Live preview in editor

### **Option 3: Theme Customizer Integration**

You can add custom icons to your theme customizer options:

```php
// Add to customizer
$wp_customize->add_setting( 'custom_icon_choice', array(
    'default' => '',
    'sanitize_callback' => 'sanitize_text_field',
) );

$wp_customize->add_control( new WP_Customize_Control(
    $wp_customize,
    'custom_icon_choice',
    array(
        'label' => __( 'Select Custom Icon', 'campaignpress' ),
        'section' => 'your_section',
        'type' => 'select',
        'choices' => array_combine(
            campaignpress_get_custom_icons(),
            campaignpress_get_custom_icons()
        ),
    )
) );
```

## **Advanced Usage**

### **Icon Categories**

Your custom icons are automatically organized into categories in the admin browser:

```php
// Add icons to specific categories
function campaignpress_add_custom_icons_to_browser( $existing_categories ) {
    $existing_categories['campaign-specific'] = array(
        'name' => __( 'Campaign Specific', 'campaignpress' ),
        'icons' => array( 'ballot-box', 'victory-flag', 'campaign-logo' ),
    );
    
    $existing_categories['candidate'] = array(
        'name' => __( 'Candidate Icons', 'campaignpress' ),
        'icons' => array( 'candidate-signature', 'candidate-portrait' ),
    );
    
    return $existing_categories;
}
add_filter( 'cp_icons_browser_categories', 'campaignpress_add_custom_icons_to_browser' );
```

### **Dynamic Icon Loading**

Load icons dynamically based on conditions:

```php
// Load different icons based on user role
if ( current_user_can( 'manage_options' ) ) {
    campaignpress_custom_icon( 'admin-icon', array( 'class' => 'custom-icon-sm' ) );
} else {
    campaignpress_custom_icon( 'user-icon', array( 'class' => 'custom-icon-sm' ) );
}

// Load icons based on page context
if ( is_singular( 'cp_event' ) ) {
    campaignpress_custom_icon( 'event-icon', array( 'class' => 'custom-icon-md' ) );
}
```

### **Icon Variations**

Create icon variations with different styling:

```php
// Get icon HTML for manipulation
$icon_html = campaignpress_get_custom_icon( 'campaign-logo', array( 'class' => 'custom-icon-lg' ) );

// Modify the SVG directly if needed
$icon_html = str_replace( 'fill="currentColor"', 'fill="#your-color"', $icon_html );

echo $icon_html;
```

## **Styling Your Icons**

### **CSS Classes**

Available size classes:
- `.custom-icon-sm` - 16px × 16px
- `.custom-icon-md` - 24px × 24px (default)
- `.custom-icon-lg` - 32px × 32px
- `.custom-icon-xl` - 48px × 48px

### **Custom Styling**

```css
/* Your custom icon styles */
.custom-icon {
    transition: all 0.3s ease;
}

.custom-icon:hover {
    transform: scale(1.1);
    color: var(--wp--preset--color--primary);
}

/* Icon in buttons */
.btn .custom-icon {
    margin-right: 0.5rem;
}

/* Icon grid layouts */
.icon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
}
```

### **Color Customization**

Icons inherit color from their parent element:

```php
// Icon will be blue
<div class="text-primary">
    <?php campaignpress_custom_icon( 'your-icon' ); ?>
</div>

// Icon will be red
<div class="text-danger">
    <?php campaignpress_custom_icon( 'your-icon' ); ?>
</div>

// Icon with explicit color
<?php campaignpress_custom_icon( 'your-icon', array( 'style' => 'color: #your-color;' ) ); ?>
```

## **Accessibility Features**

### **ARIA Labels**

Always provide meaningful ARIA labels:

```php
// Good - descriptive
<?php campaignpress_custom_icon( 'ballot-box', array( 'aria-label' => 'Cast your vote' ) ); ?>

// Good - contextual
<?php campaignpress_custom_icon( 'candidate-portrait', array( 'aria-label' => 'Candidate ' . get_the_title() ) ); ?>

// Automated ARIA labels
<?php 
$aria_label = sprintf( __( 'Campaign icon: %s', 'campaignpress' ), $icon_name );
campaignpress_custom_icon( $icon_name, array( 'aria-label' => $aria_label ) ); 
?>
```

### **Screen Reader Support**

Icons are automatically marked as decorative by default (`aria-hidden="true"`). For meaningful icons:

```php
// For decorative icons (automatically handled)
campaignpress_custom_icon( 'decoration-icon' ); // aria-hidden="true"

// For meaningful icons
campaignpress_custom_icon( 'home-icon', array( 'aria-label' => 'Home page' ) );
```

## **Performance Considerations**

### **Efficient Loading**

The system is optimized for performance:

- Icons are loaded on-demand
- No external dependencies
- Minimal CSS overhead
- Cached file operations

### **Optimization Tips**

```php
// Store frequently used icons in variables
$logo_icon = campaignpress_get_custom_icon( 'campaign-logo' );

// Use in multiple places without re-processing
echo $logo_icon;
echo $logo_icon;
echo $logo_icon;

// Conditional loading
if ( ! empty( $show_icon ) ) {
    campaignpress_custom_icon( 'your-icon' );
}
```

## **Troubleshooting**

### **Common Issues**

1. **Icon not displaying**
   - Check file path: `/assets/icons/custom/your-icon.svg`
   - Verify SVG file is valid
   - Check file permissions

2. **AJAX not working in block editor**
   - Ensure `cpIconsBrowser` is localized
   - Check nonce verification
   - Verify admin-ajax.php is accessible

3. **Styling issues**
   - Clear cache if using caching plugins
   - Check for CSS conflicts
   - Verify custom-icon classes are loaded

### **Debug Mode**

Enable debug mode to see what's happening:

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// Check logs
tail -f wp-content/debug.log
```

## **Migration from Other Icon Systems**

### **From Font Awesome**

```php
// Before
<i class="fa fa-star"></i>

// After
<?php campaignpress_custom_icon( 'star' ); ?>
```

### **From Dashicons**

```php
// Before
<span class="dashicons dashicons-admin-users"></span>

// After
<?php campaignpress_custom_icon( 'users' ); ?>
```

### **From Custom SVG Files**

```php
// Before
<img src="<?php echo get_template_directory_uri(); ?>/images/icon.svg" alt="Icon">

// After
<?php campaignpress_custom_icon( 'icon', array( 'aria-label' => 'Icon' ) ); ?>
```

## **Best Practices**

1. **File Naming**
   - Use lowercase letters
   - Use hyphens for multi-word names
   - Be descriptive: `campaign-logo.svg` not `logo1.svg`

2. **SVG Optimization**
   - Remove unnecessary metadata
   - Use viewBox for scalability
   - Optimize path data

3. **Accessibility**
   - Always provide ARIA labels for meaningful icons
   - Use `aria-hidden="true"` for decorative icons
   - Test with screen readers

4. **Performance**
   - Don't include unused icons
   - Use appropriate icon sizes
   - Cache frequently used icons

5. **Consistency**
   - Follow naming conventions
   - Use consistent styling
   - Organize icons by category

## **Support and Updates**

The custom icons system is designed to be:
- **Future-proof** - Works with WordPress updates
- **Extensible** - Easy to add new features
- **Maintainable** - Clean, documented code
- **Performance-optimized** - Minimal overhead

For additional help or feature requests, refer to the theme documentation or contact support.