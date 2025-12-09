<?php
/**
 * The template for displaying Volunteer Opportunities Archive
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title">
                <?php esc_html_e( 'Volunteer Opportunities', 'campaign-office' ); ?>
            </h1>
            <?php
            $archive_description = get_the_archive_description();
            if ( $archive_description ) :
                ?>
                <div class="archive-description"><?php echo wp_kses_post( wpautop( $archive_description ) ); ?></div>
            <?php endif; ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="volunteer-list">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'volunteer-card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="volunteer-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="volunteer-content">
                            <header class="entry-header">
                                <h2 class="entry-title">
                                    <a href="<?php the_permalink(); ?>" rel="bookmark">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                            </header>

                            <div class="entry-summary">
                                <?php the_excerpt(); ?>
                            </div>

                            <footer class="entry-footer">
                                <div class="volunteer-actions">
                                    <a href="<?php the_permalink(); ?>" class="button button-secondary">
                                        <?php esc_html_e( 'Learn More', 'campaign-office' ); ?>
                                    </a>
                                    <?php
                                    $volunteer_url = get_option( 'campaignpress_volunteer_url' );
                                    if ( $volunteer_url ) :
                                        ?>
                                        <a href="<?php echo esc_url( $volunteer_url ); ?>" class="button button-primary" target="_blank" rel="noopener">
                                            <?php esc_html_e( 'Sign Up to Volunteer', 'campaign-office' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </footer>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php
            the_posts_navigation( array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
            ) );
            ?>

        <?php else : ?>

            <div class="no-results not-found">
                <header class="page-header">
                    <h1 class="page-title"><?php esc_html_e( 'No Volunteer Opportunities Found', 'campaign-office' ); ?></h1>
                </header>

                <div class="page-content">
                    <p><?php esc_html_e( 'No volunteer opportunities are currently available. Check back soon!', 'campaign-office' ); ?></p>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
