<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @package CampaignPress
 * @since 1.0.0
 */
?>

    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php if (is_active_sidebar('footer-1')) : ?>
                        <div class="footer-widgets">
                            <?php dynamic_sidebar('footer-1'); ?>
                        </div>
                    <?php endif; ?>

                    <div class="site-info">
                        <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php _e('All rights reserved.', 'campaignpress'); ?></p>
                        <?php
                        if (has_nav_menu('footer')) {
                            wp_nav_menu(
                                array(
                                    'theme_location' => 'footer',
                                    'menu_class'     => 'footer-menu',
                                    'depth'          => 1,
                                )
                            );
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
