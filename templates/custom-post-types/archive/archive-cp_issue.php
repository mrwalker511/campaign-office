<?php
/**
 * The template for displaying Issues Archive
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
                <?php esc_html_e( 'Our Issues & Positions', 'campaign-office' ); ?>
            </h1>
            <?php
            $archive_description = get_the_archive_description();
            if ( $archive_description ) :
                ?>
                <div class="archive-description"><?php echo wp_kses_post( wpautop( $archive_description ) ); ?></div>
            <?php endif; ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="issues-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'issue-card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="issue-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="issue-content">
                            <header class="entry-header">
                                <?php
                                $categories = get_the_terms( get_the_ID(), 'issue_category' );
                                if ( $categories && ! is_wp_error( $categories ) ) :
                                    ?>
                                    <div class="issue-category">
                                        <?php foreach ( $categories as $category ) : ?>
                                            <span class="category-badge"><?php echo esc_html( $category->name ); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

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
                                <a href="<?php the_permalink(); ?>" class="read-more">
                                    <?php esc_html_e( 'Read Full Position', 'campaign-office' ); ?>
                                    <span class="screen-reader-text"> <?php esc_html_e( 'on', 'campaign-office' ); ?> <?php the_title(); ?></span>
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
                    <h1 class="page-title"><?php esc_html_e( 'No Issues Found', 'campaign-office' ); ?></h1>
                </header>

                <div class="page-content">
                    <p><?php esc_html_e( 'No campaign issues have been published yet. Check back soon!', 'campaign-office' ); ?></p>
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
