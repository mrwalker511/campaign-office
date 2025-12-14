<?php
/**
 * The template for displaying Team Members Archive
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div class="site-container">
<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title">
                <?php esc_html_e( 'Meet Our Team', 'campaign-office' ); ?>
            </h1>
            <?php
            $archive_description = get_the_archive_description();
            if ( $archive_description ) :
                ?>
                <div class="archive-description"><?php echo wp_kses_post( wpautop( $archive_description ) ); ?></div>
            <?php endif; ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="team-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'team-card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="team-photo">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'campaignpress-team-member' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="team-content">
                            <header class="entry-header">
                                <h2 class="entry-title">
                                    <a href="<?php the_permalink(); ?>" rel="bookmark">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                                <?php
                                $role = get_post_meta( get_the_ID(), '_cp_team_role', true );
                                if ( $role ) :
                                    ?>
                                    <div class="team-role"><?php echo esc_html( $role ); ?></div>
                                <?php endif; ?>
                            </header>

                            <div class="entry-summary">
                                <?php the_excerpt(); ?>
                            </div>

                            <footer class="entry-footer">
                                <a href="<?php the_permalink(); ?>" class="read-more">
                                    <?php esc_html_e( 'Read Bio', 'campaign-office' ); ?>
                                    <span class="screen-reader-text"> <?php esc_html_e( 'of', 'campaign-office' ); ?> <?php the_title(); ?></span>
                                </a>
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
                    <h1 class="page-title"><?php esc_html_e( 'No Team Members Found', 'campaign-office' ); ?></h1>
                </header>

                <div class="page-content">
                    <p><?php esc_html_e( 'No team members have been added yet.', 'campaign-office' ); ?></p>
                </div>
            </div>

        <?php endif; ?>

    </main>
</div>
</div><!-- .site-container -->

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
