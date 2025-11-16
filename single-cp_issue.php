<?php
/**
 * The template for displaying single Issues
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
                    <?php
                    $categories = get_the_terms( get_the_ID(), 'issue_category' );
                    if ( $categories && ! is_wp_error( $categories ) ) :
                        ?>
                        <div class="issue-category">
                            <?php foreach ( $categories as $category ) : ?>
                                <a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="category-badge">
                                    <?php echo esc_html( $category->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

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
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campaignpress' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div>

                <footer class="entry-footer">
                    <?php
                    $candidate_name = get_option( 'campaignpress_candidate_name' );
                    if ( $candidate_name ) :
                        ?>
                        <div class="issue-support-cta">
                            <h3><?php echo sprintf( esc_html__( 'Support %s on this Issue', 'campaignpress' ), esc_html( $candidate_name ) ); ?></h3>
                            <div class="cta-buttons">
                                <?php
                                $donation_url = get_option( 'campaignpress_donation_url' );
                                if ( $donation_url ) :
                                    ?>
                                    <a href="<?php echo esc_url( $donation_url ); ?>" class="button button-primary" target="_blank" rel="noopener">
                                        <?php esc_html_e( 'Donate', 'campaignpress' ); ?>
                                    </a>
                                <?php endif; ?>

                                <?php
                                $volunteer_url = get_option( 'campaignpress_volunteer_url' );
                                if ( $volunteer_url ) :
                                    ?>
                                    <a href="<?php echo esc_url( $volunteer_url ); ?>" class="button button-secondary" target="_blank" rel="noopener">
                                        <?php esc_html_e( 'Volunteer', 'campaignpress' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="post-navigation">
                        <?php
                        the_post_navigation( array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous Issue:', 'campaignpress' ) . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next Issue:', 'campaignpress' ) . '</span> <span class="nav-title">%title</span>',
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
