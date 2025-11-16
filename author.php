<?php
/**
 * The template for displaying author archives
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php if ( have_posts() ) : ?>

            <header class="page-header author-header">
                <div class="author-info">
                    <div class="author-avatar">
                        <?php echo get_avatar( get_the_author_meta( 'ID' ), 120 ); ?>
                    </div>
                    <div class="author-details">
                        <h1 class="page-title">
                            <?php
                            printf(
                                /* translators: %s: author name */
                                esc_html__( 'Posts by %s', 'campaignpress' ),
                                '<span class="vcard">' . esc_html( get_the_author() ) . '</span>'
                            );
                            ?>
                        </h1>
                        <?php if ( get_the_author_meta( 'description' ) ) : ?>
                            <div class="author-description">
                                <?php echo wp_kses_post( wpautop( get_the_author_meta( 'description' ) ) ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'campaignpress' ) . '</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'campaignpress' ) . '</span>',
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
