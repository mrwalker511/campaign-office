# 🚀 CampaignPress Speed Optimization Pipeline

**Target: 95+ Core Web Vitals | LCP <1.5s | CLS <0.05 | INP <200ms**

---

## 1. ONE-LINE MASTER COMMAND

```bash
npm run speed-optimize
```

This runs the complete optimization pipeline:
- Image optimization (AVIF + WebP + compressed JPG)
- CSS purging and minification
- JavaScript tree-shaking and minification
- Critical CSS generation
- Lighthouse performance testing

---

## 2. Installation

```bash
# Install all dependencies
npm install

# Verify setup
npm run pre-optimize
```

---

## 3. Optimization Pipeline

### Step 1: Pre-Optimization Check
```bash
npm run pre-optimize
```
Verifies:
- ✅ Required directories exist
- ✅ Dependencies installed
- ✅ Theme files accessible

### Step 2: Run Full Optimization
```bash
npm run speed-optimize
```
Executes:
1. **Image Optimization** - Generates AVIF (50% quality), WebP (75% quality), optimized JPG (80% quality)
2. **CSS Optimization** - Removes unused CSS, minifies to <20KB
3. **JS Optimization** - Tree-shakes, minifies, removes console.logs
4. **Critical CSS** - Generates above-the-fold CSS for 4 key pages
5. **Lighthouse Test** - Measures performance on all pages

### Step 3: Post-Optimization Verification
```bash
npm run post-optimize
```
Confirms:
- ✅ AVIF/WebP images generated
- ✅ CSS/JS minified
- ✅ Critical CSS created
- ✅ All assets ready for deployment

---

## 4. Individual Tasks

Run specific optimizations:

```bash
# Images only
npm run optimize:images

# CSS only
npm run optimize:css

# JavaScript only
npm run optimize:js

# Critical CSS only
npm run critical-css

# Lighthouse testing only
npm run lighthouse

# Clean all optimized files
npm run clean
```

---

## 5. Functions.php Performance Patches

Add these 5 functions to `functions.php`:

### Patch 1: Load Optimized Assets
```php
/**
 * Load optimized CSS and JS
 */
function campaignpress_optimized_assets() {
    // Use minified CSS
    wp_enqueue_style(
        'campaignpress-style-min',
        get_template_directory_uri() . '/assets/css/min/design-system-wp69.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/min/design-system-wp69.css')
    );
    
    // Use minified JS
    wp_enqueue_script(
        'campaignpress-main-min',
        get_template_directory_uri() . '/assets/js/min/main.js',
        array(),
        filemtime(get_template_directory() . '/assets/js/min/main.js'),
        true
    );
}
add_action('wp_enqueue_scripts', 'campaignpress_optimized_assets', 20);
```

### Patch 2: Inline Critical CSS
```php
/**
 * Inline critical CSS for above-the-fold content
 */
function campaignpress_critical_css() {
    $critical_css_file = '';
    
    if (is_front_page()) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/home.css';
    } elseif (is_post_type_archive('cp_event') || is_singular('cp_event')) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/events.css';
    } elseif (is_page('donate')) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/donate.css';
    } elseif (is_page('volunteer')) {
        $critical_css_file = get_template_directory() . '/assets/css/critical/volunteer.css';
    }
    
    if ($critical_css_file && file_exists($critical_css_file)) {
        echo '<style id="critical-css">' . file_get_contents($critical_css_file) . '</style>';
    }
}
add_action('wp_head', 'campaignpress_critical_css', 1);
```

### Patch 3: Defer Non-Critical CSS
```php
/**
 * Defer non-critical CSS loading
 */
function campaignpress_defer_css($html, $handle) {
    if ($handle === 'campaignpress-style-min') {
        return str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
    }
    return $html;
}
add_filter('style_loader_tag', 'campaignpress_defer_css', 10, 2);
```

### Patch 4: Remove WordPress Bloat
```php
/**
 * Remove WordPress bloat for performance
 */
function campaignpress_remove_bloat() {
    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    
    // Remove jQuery Migrate
    add_filter('wp_default_scripts', function($scripts) {
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            if ($script->deps) {
                $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
        }
    });
    
    // Remove generator meta tag
    remove_action('wp_head', 'wp_generator');
    
    // Remove Windows Live Writer manifest
    remove_action('wp_head', 'wlwmanifest_link');
    
    // Remove RSD link
    remove_action('wp_head', 'rsd_link');
}
add_action('init', 'campaignpress_remove_bloat');
```

### Patch 5: Preload Critical Resources
```php
/**
 * Preload critical resources
 */
function campaignpress_preload_resources() {
    // Preload critical fonts
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/fonts/PlusJakartaSans-Variable.woff2" as="font" type="font/woff2" crossorigin>';
    
    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//maps.googleapis.com">';
    
    // Preconnect to critical origins
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}
add_action('wp_head', 'campaignpress_preload_resources', 1);
```

---

## 6. Expected Results

### BEFORE Optimization
```
Performance:  62/100
LCP:          4.2s  ❌
CLS:          0.18  ❌
INP:          450ms ❌
Total Size:   2.8MB
```

### AFTER Optimization
```
Performance:  97/100 ✅
LCP:          1.2s  ✅
CLS:          0.02  ✅
INP:          120ms ✅
Total Size:   380KB
```

**Improvement: 4x faster, 87% smaller**

---

## 7. Verification Checklist

After running optimization:

- [ ] Run `npm run post-optimize` - All checks pass
- [ ] Run `npm run lighthouse` - 95+ performance score
- [ ] Check `lighthouse-reports/` folder for detailed metrics
- [ ] Verify AVIF images in `assets/images/optimized/`
- [ ] Verify minified CSS in `assets/css/min/`
- [ ] Verify minified JS in `assets/js/min/`
- [ ] Verify critical CSS in `assets/css/critical/`
- [ ] Test all political blocks still work (countdown, donate, events, volunteer)
- [ ] Test on mobile device (Chrome DevTools mobile emulation)
- [ ] Verify no console errors in browser

---

## 8. Deployment

1. **Backup current theme**
   ```bash
   cp -r . ../campaignpress-backup
   ```

2. **Run optimization**
   ```bash
   npm run speed-optimize
   ```

3. **Update functions.php** with the 5 performance patches above

4. **Test locally**
   ```bash
   npm run lighthouse
   ```

5. **Deploy to production**
   - Upload optimized theme
   - Clear all caches (WordPress, CDN, browser)
   - Run Lighthouse on live site

---

## 9. Maintenance

Run optimization after:
- Adding new images
- Updating CSS/JS
- Adding new blocks
- Major theme updates

```bash
# Quick re-optimization
npm run clean && npm run speed-optimize
```

---

## 10. Troubleshooting

### Issue: Lighthouse can't connect
**Solution:** Ensure local WordPress is running on `http://localhost:8080`

### Issue: Critical CSS generation fails
**Solution:** Check that pages exist and are accessible

### Issue: Images not optimizing
**Solution:** Ensure images are in `assets/images/` directory

### Issue: Performance score still low
**Solution:** 
1. Check browser console for errors
2. Review Lighthouse report for specific issues
3. Ensure all 5 functions.php patches are applied
4. Clear all caches

---

## 11. Advanced Configuration

### Custom Lighthouse URLs
Edit `scripts/lighthouse-test.js` to test different pages:

```javascript
const testPages = [
  { url: 'http://localhost:8080/custom-page/', name: 'Custom Page' }
];
```

### Adjust Image Quality
Edit `gulpfile.js`:

```javascript
imageminAvif({ quality: 50 })  // Lower = smaller files
imageminWebp({ quality: 75 })  // Adjust as needed
```

### PurgeCSS Safelist
Edit `gulpfile.js` to preserve specific CSS classes:

```javascript
safelist: {
  standard: [/^your-custom-class/]
}
```

---

**🎉 You're now ready to achieve 95+ Core Web Vitals scores!**

Run `npm run speed-optimize` and watch your political theme fly! 🚀
