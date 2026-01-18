# Customizer Homepage Layout Fix

## Problem
The Customizer had a "Homepage Layout" setting that allowed users to choose between:
- Classic Candidate
- Modern Progressive  
- Conservative Traditional

However, when users changed this setting, **nothing happened on the homepage**. The setting existed but wasn't actually connected to any code that would change the template.

## Root Cause
1. The customizer setting `campaignpress_homepage_layout` was registered in `/includes/free/customizer.php`
2. Multiple homepage templates existed (`home-classic-statesman.html`, `home.html`, `home-grassroots.html`)
3. **BUT** there was no code that actually read the customizer setting and loaded the appropriate template
4. WordPress was always loading the same default template regardless of the customizer selection

## Solution Implemented

### 1. Modified Template Loader (`/includes/core/class-template-loader.php`)
Added logic to check the customizer setting when loading the front page template:

```php
elseif (is_front_page()) {
    // Check for customizer homepage layout setting
    $homepage_layout = get_theme_mod('campaignpress_homepage_layout', 'modern');
    
    // Map layout names to template files
    $layout_templates = array(
        'classic'     => 'home-classic-statesman.html',
        'modern'      => 'home.html',
        'traditional' => 'home-grassroots.html',
    );
    
    // Get the template file for the selected layout
    $layout_template = isset($layout_templates[$homepage_layout]) 
        ? $layout_templates[$homepage_layout] 
        : 'home.html';
    
    // Try to load the layout-specific template
    if (($t = self::get_template_path($layout_template))) {
        $new_template = $t;
    } elseif (($t = self::get_template_path('front-page.php'))) {
        $new_template = $t;
    }
}
```

### 2. Updated Customizer Setting (`/includes/free/customizer.php`)
- Added `'transport' => 'refresh'` to make it explicit that the page needs to refresh
- Added helpful description text to inform users the page will refresh when they change this setting

### 3. Added Body Class (`/functions.php`)
Added a function to apply a body class based on the selected layout:

```php
function campaignpress_homepage_layout_body_class($classes) {
    if (is_front_page()) {
        $homepage_layout = get_theme_mod('campaignpress_homepage_layout', 'modern');
        
        if (!in_array($homepage_layout, array('classic', 'modern', 'traditional'), true)) {
            $homepage_layout = 'modern';
        }
        
        $classes[] = 'homepage-layout-' . sanitize_html_class($homepage_layout);
    }
    
    return $classes;
}
add_filter('body_class', 'campaignpress_homepage_layout_body_class');
```

## How It Works Now

1. User opens WordPress Customizer and navigates to **Layout Options → Homepage Layout**
2. User selects one of the three layout options
3. The page automatically refreshes (because of `transport => 'refresh'`)
4. WordPress calls the template loader, which:
   - Reads the `campaignpress_homepage_layout` theme mod
   - Maps it to the correct template file
   - Loads that template
5. The homepage now displays with the selected layout
6. A body class is added (e.g., `homepage-layout-classic`) for additional CSS styling if needed

## Template Mappings

| Customizer Option | Template File | Body Class |
|-------------------|---------------|------------|
| Classic Candidate | `home-classic-statesman.html` | `homepage-layout-classic` |
| Modern Progressive | `home.html` (default) | `homepage-layout-modern` |
| Conservative Traditional | `home-grassroots.html` | `homepage-layout-traditional` |

## Testing Instructions

1. Log into WordPress admin
2. Go to **Appearance → Customize**
3. Navigate to **Layout Options → Homepage Layout**
4. Change the dropdown to "Classic Candidate" - the page should refresh and show the classic layout
5. Change to "Conservative Traditional" - the page should refresh and show the grassroots layout
6. Change back to "Modern Progressive" - the page should refresh and show the modern layout

## Files Modified

1. `/includes/core/class-template-loader.php` - Added template switching logic
2. `/includes/free/customizer.php` - Updated setting to include transport and description
3. `/functions.php` - Added body class function for layout-specific styling

## Backward Compatibility

- Default layout is "modern" which uses `home.html` (the existing default)
- If an invalid layout is selected, it falls back to "modern"
- Existing sites will continue to use the modern layout unless changed
- No database migrations or data updates required
