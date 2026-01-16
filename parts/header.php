<?php
/**
 * Classic PHP Header Template Part
 *
 * Uses wp_nav_menu() with registered menu locations.
 * Menus can be managed via Appearance → Menus in the WordPress admin.
 *
 * @package CampaignPress
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="cp-nav">
    <div class="cp-nav__inner">
        <!-- Site Branding -->
        <div class="cp-nav__brand">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="cp-nav__site-title" rel="home">
                    <?php bloginfo('name'); ?>
                </a>
            <?php endif; ?>

            <?php
            $description = get_bloginfo('description', 'display');
            if ($description || is_customize_preview()) :
            ?>
                <p class="cp-nav__tagline"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </div>

        <!-- Primary Navigation -->
        <nav class="cp-primary-navigation cp-nav__links" aria-label="<?php esc_attr_e('Primary Navigation', 'campaign-office'); ?>">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class'     => 'cp-nav__menu',
                'container'      => false,
                'depth'          => 2,
                'fallback_cb'    => 'campaignpress_nav_fallback',
            ));
            ?>
        </nav>

        <!-- CTA Button -->
        <div class="cp-nav__cta">
            <a href="<?php echo esc_url(get_theme_mod('donate_url', '/donate/')); ?>" class="cp-btn cp-btn--primary">
                <?php echo esc_html(get_theme_mod('donate_button_text', __('Donate', 'campaign-office'))); ?>
            </a>
        </div>

        <!-- Mobile Menu Toggle -->
        <button class="cp-nav__toggle" aria-controls="mobile-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle Menu', 'campaign-office'); ?>">
            <span class="cp-nav__toggle-bar"></span>
            <span class="cp-nav__toggle-bar"></span>
            <span class="cp-nav__toggle-bar"></span>
        </button>
    </div>
</div>
<?php

/**
 * Fallback menu when no menu is assigned
 */
function campaignpress_nav_fallback() {
    if (current_user_can('edit_theme_options')) {
        echo '<ul class="cp-nav__menu">';
        echo '<li class="menu-item"><a href="' . esc_url(admin_url('nav-menus.php')) . '">' . esc_html__('Set up menu', 'campaign-office') . '</a></li>';
        echo '</ul>';
    } else {
        wp_page_menu(array(
            'menu_class' => 'cp-nav__menu',
            'container'  => '',
        ));
    }
}
