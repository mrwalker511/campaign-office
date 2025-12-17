<?php
/**
 * The template for displaying archive pages
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div class="site-container">
<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php if ( have_posts() ) : ?>

            <header class="page-header">
                <?php
                the_archive_title( '<h1 class="page-title">', '</h1>' );
                the_archive_description( '<div class="archive-description">', '</div>' );
                ?>
            </header>

            <div class="posts-wrapper">
                <?php
                // Start the Loop.
                while ( have_posts() ) :
                    the_post();

                    /*
                     * Include the Post-Type-specific template for the content.
                     * If you want to override this in a child theme, then include a file
                     * called content-___.php (where ___ is the Post Type name) and that will be used instead.
                     */
                    get_template_part( 'templates/parts/content', get_post_type() );

                endwhile;
                ?>
            </div>

            <?php
            the_posts_navigation( array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
            ) );

        else :

            get_template_part( 'templates/parts/content', 'none' );

        endif;
        ?>

    </main>
</div>
</div><!-- .site-container -->

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
