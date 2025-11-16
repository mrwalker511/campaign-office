<?php
/**
 * The header template
 *
 * @package CampaignPress
 * @since 1.0.0
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'campaignpress'); ?></a>

<div id="page" class="site">
    <header id="masthead" class="site-header" role="banner">
        <div class="site-container">
            <div class="site-branding">
                <?php
                the_custom_logo();
                if (is_front_page() && is_home()) :
                    ?>
                    <h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
                    <?php
                else :
                    ?>
                    <p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
                    <?php
                endif;
                $campaignpress_description = get_bloginfo('description', 'display');
                if ($campaignpress_description || is_customize_preview()) :
                    ?>
                    <p class="site-description"><?php echo esc_html($campaignpress_description); ?></p>
                <?php endif; ?>
            </div><!-- .site-branding -->

            <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Navigation', 'campaignpress' ); ?>">
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'campaignpress' ); ?>">
                    <span class="menu-toggle-text"><?php esc_html_e('Menu', 'campaignpress'); ?></span>
                    <span class="menu-toggle-icon" aria-hidden="true">
                        <span class="menu-bar"></span>
                        <span class="menu-bar"></span>
                        <span class="menu-bar"></span>
                    </span>
                </button>
                <?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'fallback_cb'    => false,
                    )
                );
                ?>
            </nav><!-- #site-navigation -->

            <?php if (campaignpress_get_donation_url()) : ?>
                <div class="header-donation-button">
                    <?php campaignpress_donation_button(array('class' => 'cp-button cp-button-primary')); ?>
                </div>
            <?php endif; ?>
        </div>
    </header><!-- #masthead -->

    <div id="content" class="site-content site-container" role="main">
