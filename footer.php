<?php
/**
 * The footer template
 *
 * @package CampaignPress
 * @since 1.0.0
 */
?>

    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="site-container">
            <div class="footer-widgets">
                <div class="footer-widget-area">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                        <div class="footer-widget-column">
                            <?php dynamic_sidebar('footer-1'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-2')) : ?>
                        <div class="footer-widget-column">
                            <?php dynamic_sidebar('footer-2'); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (is_active_sidebar('footer-3')) : ?>
                        <div class="footer-widget-column">
                            <?php dynamic_sidebar('footer-3'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div><!-- .footer-widgets -->

            <div class="footer-bottom">
                <div class="site-info">
                    <?php
                    $candidate_name = get_theme_mod('campaignpress_candidate_name', '');
                    $office_seeking = get_theme_mod('campaignpress_office_seeking', '');

                    if ($candidate_name && $office_seeking) {
                        printf(
                            /* translators: 1: candidate name, 2: office seeking */
                            esc_html__('%1$s for %2$s', 'campaignpress'),
                            '<strong>' . esc_html($candidate_name) . '</strong>',
                            esc_html($office_seeking)
                        );
                        echo ' | ';
                    }
                    ?>
                    <a href="<?php echo esc_url(__('https://wordpress.org/', 'campaignpress')); ?>">
                        <?php
                        /* translators: %s: WordPress */
                        printf(esc_html__('Powered by %s', 'campaignpress'), 'WordPress');
                        ?>
                    </a>
                    <span class="sep"> | </span>
                    <?php
                    /* translators: 1: Theme name */
                    printf(esc_html__('Theme: %s', 'campaignpress'), 'CampaignPress');
                    ?>
                </div><!-- .site-info -->

                <?php campaignpress_social_links(); ?>

                <?php
                if (has_nav_menu('footer')) :
                    wp_nav_menu(
                        array(
                            'theme_location' => 'footer',
                            'menu_class'     => 'footer-menu',
                            'depth'          => 1,
                        )
                    );
                endif;
                ?>
            </div><!-- .footer-bottom -->
        </div>
    </footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
