<?php
/**
 * The template for displaying single Volunteer Opportunities
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div class="site-container">
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
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail( 'large' ); ?>
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
                    <div class="volunteer-cta">
                        <h3><?php esc_html_e( 'Ready to Make a Difference?', 'campaign-office' ); ?></h3>
                        <p><?php esc_html_e( 'Join our team of dedicated volunteers and help us build a better future.', 'campaign-office' ); ?></p>

                        <?php
                        $volunteer_url = get_option( 'campaignpress_volunteer_url' );
                        if ( $volunteer_url ) :
                            ?>
                            <a href="<?php echo esc_url( $volunteer_url ); ?>" class="button button-primary button-large" target="_blank" rel="noopener">
                                <?php esc_html_e( 'Sign Up to Volunteer', 'campaign-office' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="post-navigation">
                        <?php
                        the_post_navigation( array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous Opportunity:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next Opportunity:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                        ) );
                        ?>
                    </div>
                </footer>
            </article>

        <?php endwhile; ?>

    </main>
</div>
</div><!-- .site-container -->

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
