# Recommended Patches (P0 & P1)

These patches address the critical "P0" gaps identified in the Audit Report.

## 1. Accessibility: Fix "Read More" Context
**Issue:** Screen readers announce "Read More" without telling the user *what* they are reading about.
**Fix:** Add `aria-label` with the post title.
**File:** `front-page.php` (lines 142 & 206)

```excerpt
<<<< SOURCE
<a href="<?php the_permalink(); ?>" class="read-more"><?php esc_html_e( 'Read More', 'campaign-office' ); ?></a>
====
<a href="<?php the_permalink(); ?>" class="read-more" aria-label="<?php echo esc_attr( sprintf( __( 'Read more about %s', 'campaign-office' ), get_the_title() ) ); ?>"><?php esc_html_e( 'Read More', 'campaign-office' ); ?></a>
>>>>
```

*Note: You should also apply this pattern to `archive.php`, `index.php`, and `search.php` if they use similar loops.*

## 2. Legal: "Paid for by" Disclaimer
**Issue:** No standardized way to display the mandatory "Paid for by" text.
**Fix:** Add a Customizer setting and output it in the footer.

### Step A: Add Setting in `functions.php`
Add this to the `campaignpress_customize_color_scheme` function or a new one:

```php
function campaignpress_customize_disclaimer($wp_customize) {
    $wp_customize->add_setting('campaignpress_disclaimer_text', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));

    $wp_customize->add_control('campaignpress_disclaimer_text', array(
        'label' => __('"Paid for by" Disclaimer', 'campaign-office'),
        'description' => __('e.g. Paid for by Friends of Candidate', 'campaign-office'),
        'section' => 'title_tagline',
        'type' => 'text',
    ));
}
add_action('customize_register', 'campaignpress_customize_disclaimer');
```

### Step B: Display in `footer.php`
Insert this before the `site-info` div (around line 37):

```php
<div class="site-disclaimer" style="text-align:center; padding: 1rem; border: 1px solid #ccc; margin-bottom: 1rem;">
    <?php
    $disclaimer = get_theme_mod('campaignpress_disclaimer_text');
    if ($disclaimer) {
        echo esc_html($disclaimer);
    }
    ?>
</div>
```

## 3. Privacy: Localize Google Fonts
**Issue:** `theme.json` loads fonts from Google, which leaks IP addresses.
**Fix:** Download fonts to `assets/fonts/` and update `theme.json`.

**File:** `theme.json`

```json
<<<< SOURCE
"src": ["https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"]
====
"src": ["file:./assets/fonts/PlusJakartaSans-Variable.woff2"]
>>>>
```
*(Repeat for Bricolage Grotesque and JetBrains Mono)*.
