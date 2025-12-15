<?php
/**
 * The template for displaying tag archives
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php if ( have_posts() ) : ?>

            <header class="page-header">
                <h1 class="page-title">
                    <?php
                    printf(
                        /* translators: %s: tag name */
                        esc_html__( 'Tag: %s', 'campaign-office' ),
                        '<span>' . single_tag_title( '', false ) . '</span>'
                    );
                    ?>
                </h1>
                <?php
                $tag_description = tag_description();
                if ( $tag_description ) :
                    ?>
                    <div class="archive-description"><?php echo wp_kses_post( $tag_description ); ?></div>
                <?php endif; ?>
            </header>

            <div class="posts-wrapper">
                <?php
                // Start the Loop.
                while ( have_posts() ) :
                    the_post();
                    get_template_part( 'templates/parts/content', get_post_type() );
                endwhile;
                ?>
            </div>

            <?php
            the_posts_navigation( array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'campaign-office' ) . '</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'campaign-office' ) . '</span>',
            ) );

        else :

            get_template_part( 'templates/parts/content', 'none' );

        endif;
        ?>

    </main>
</div>

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
