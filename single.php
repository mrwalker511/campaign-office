<?php
/**
 * The template for displaying all single posts
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        while ( have_posts() ) :
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <div class="entry-meta">
                        <?php
                        printf(
                            '<span class="posted-on">%s</span>',
                            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark"><time class="entry-date published" datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time></a>'
                        );

                        printf(
                            ' <span class="byline">%s <span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span></span>',
                            esc_html__( 'by', 'campaign-office' )
                        );
                        ?>
                    </div>

                    <h1 class="entry-title"><?php the_title(); ?></h1>

                    <?php if ( has_category() ) : ?>
                        <div class="post-categories">
                            <?php the_category( ', ' ); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail( 'large' ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php
                    the_content( sprintf(
                        wp_kses(
                            __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'campaign-office' ),
                            array(
                                'span' => array(
                                    'class' => array(),
                                ),
                            )
                        ),
                        get_the_title()
                    ) );

                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campaign-office' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div>

                <footer class="entry-footer">
                    <?php if ( has_tag() ) : ?>
                        <div class="post-tags">
                            <?php the_tags( '<strong>' . esc_html__( 'Tags:', 'campaign-office' ) . '</strong> ', ', ', '' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="post-navigation">
                        <?php
                        the_post_navigation( array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                        ) );
                        ?>
                    </div>
                </footer>
            </article>

            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>

        <?php endwhile; ?>

    </main>
</div>

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
