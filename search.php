<?php
/**
 * The template for displaying search results pages
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
                <?php
                printf(
                    esc_html__( 'Search Results for: %s', 'campaign-office' ),
                    '<span>' . get_search_query() . '</span>'
                );
                ?>
            </h1>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="search-results">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'search-result' ); ?>>
                        <header class="entry-header">
                            <?php
                            // Display post type badge
                            $post_type = get_post_type();
                            $post_type_obj = get_post_type_object( $post_type );
                            if ( $post_type_obj ) :
                                ?>
                                <span class="post-type-badge"><?php echo esc_html( $post_type_obj->labels->singular_name ); ?></span>
                            <?php endif; ?>

                            <h2 class="entry-title">
                                <a href="<?php the_permalink(); ?>" rel="bookmark">
                                    <?php the_title(); ?>
                                </a>
                            </h2>

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
                            <div class="search-result-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'thumbnail' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="entry-summary">
                            <?php the_excerpt(); ?>
                        </div>

                        <footer class="entry-footer">
                            <a href="<?php the_permalink(); ?>" class="read-more">
                                <?php esc_html_e( 'Read More', 'campaign-office' ); ?>
                                <span class="screen-reader-text"> <?php esc_html_e( 'about', 'campaign-office' ); ?> <?php the_title(); ?></span>
                            </a>
                        </footer>
                    </article>

                <?php endwhile; ?>
            </div>

            <?php
            the_posts_navigation( array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous Results', 'campaign-office' ) . '</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'More Results', 'campaign-office' ) . '</span>',
            ) );
            ?>

        <?php else : ?>

            <div class="no-results not-found">
                <header class="page-header">
                    <h2 class="page-title"><?php esc_html_e( 'Nothing Found', 'campaign-office' ); ?></h2>
                </header>

                <div class="page-content">
                    <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with different keywords.', 'campaign-office' ); ?></p>

                    <div class="search-form-wrapper">
                        <?php get_search_form(); ?>
                    </div>

                    <div class="search-suggestions">
                        <h3><?php esc_html_e( 'Browse by Category', 'campaign-office' ); ?></h3>
                        <ul>
                            <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_issue' ) ); ?>"><?php esc_html_e( 'Issues', 'campaign-office' ); ?></a></li>
                            <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_event' ) ); ?>"><?php esc_html_e( 'Events', 'campaign-office' ); ?></a></li>
                            <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_endorsement' ) ); ?>"><?php esc_html_e( 'Endorsements', 'campaign-office' ); ?></a></li>
                            <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_team' ) ); ?>"><?php esc_html_e( 'Team', 'campaign-office' ); ?></a></li>
                            <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_volunteer' ) ); ?>"><?php esc_html_e( 'Volunteer Opportunities', 'campaign-office' ); ?></a></li>
                        </ul>
                    </div>
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
