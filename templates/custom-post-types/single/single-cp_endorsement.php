<?php
/**
 * The template for displaying single Endorsements
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
                    <h1 class="entry-title"><?php the_title(); ?></h1>

                    <div class="entry-meta">
                        <?php
                        printf(
                            '<span class="posted-on">%s</span>',
                            '<time class="entry-date published" datetime="' . esc_attr( get_the_date( DATE_W3C ) ) . '">' . esc_html( get_the_date() ) . '</time>'
                        );
                        ?>
                    </div>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail endorser-photo">
                        <?php the_post_thumbnail( 'campaignpress-candidate-headshot' ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php
                    the_content();

                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campaign-office' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div>

                <footer class="entry-footer">
                    <div class="endorsement-share">
                        <h3><?php esc_html_e( 'Share This Endorsement', 'campaign-office' ); ?></h3>
                        <div class="share-buttons">
                            <?php
                            $title = get_the_title();
                            $url = get_permalink();
                            $encoded_title = urlencode( $title );
                            $encoded_url = urlencode( $url );
                            ?>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr( $encoded_url ); ?>" target="_blank" rel="noopener" class="share-facebook">
                                <span class="dashicons dashicons-facebook"></span> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?text=<?php echo esc_attr( $encoded_title ); ?>&url=<?php echo esc_attr( $encoded_url ); ?>" target="_blank" rel="noopener" class="share-twitter">
                                <span class="dashicons dashicons-twitter"></span> Twitter
                            </a>
                        </div>
                    </div>

                    <div class="post-navigation">
                        <?php
                        the_post_navigation( array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous Endorsement:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next Endorsement:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                        ) );
                        ?>
                    </div>
                </footer>
            </article>

        <?php endwhile; ?>

    </main>
</div>

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
