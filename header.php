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

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'campaign-office'); ?></a>

<div id="page" class="site">
    <header id="masthead" class="site-header" role="banner">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="site-container">
                <div class="site-branding navbar-brand">
                    <?php
                    the_custom_logo();
                    if (is_front_page() && is_home()) :
                        ?>
                        <h1 class="site-title mb-0"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="text-decoration-none"><?php bloginfo('name'); ?></a></h1>
                        <?php
                    else :
                        ?>
                        <p class="site-title mb-0"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home" class="text-decoration-none"><?php bloginfo('name'); ?></a></p>
                        <?php
                    endif;
                    $campaignpress_description = get_bloginfo('description', 'display');
                    if ($campaignpress_description || is_customize_preview()) :
                        ?>
                        <p class="site-description mb-0 small text-muted"><?php echo esc_html($campaignpress_description); ?></p>
                    <?php endif; ?>
                </div><!-- .site-branding -->

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle navigation', 'campaign-office'); ?>">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location'  => 'primary',
                            'container'       => false,
                            'menu_class'      => 'navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center',
                            'menu_id'         => 'primary-menu',
                            'fallback_cb'     => false,
                            'depth'           => 2,
                            'walker'          => new WP_Bootstrap_Navwalker(),
                        )
                    );
                    ?>

                    <?php if (campaignpress_get_donation_url()) : ?>
                        <div class="d-flex ms-lg-3">
                            <?php campaignpress_donation_button(array('class' => 'btn btn-primary cp-donation-button')); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header><!-- #masthead -->

    <div id="content" class="site-content <?php echo esc_attr(is_front_page() ? '' : 'site-container'); ?>" role="main">
