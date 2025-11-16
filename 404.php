<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <section class="error-404 not-found">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e( 'Page Not Found', 'campaignpress' ); ?></h1>
            </header>

            <div class="page-content">
                <p><?php esc_html_e( 'It looks like this page doesn\'t exist. The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'campaignpress' ); ?></p>

                <div class="search-form-wrapper">
                    <h2><?php esc_html_e( 'Try Searching', 'campaignpress' ); ?></h2>
                    <?php get_search_form(); ?>
                </div>

                <div class="error-404-navigation">
                    <h2><?php esc_html_e( 'Popular Pages', 'campaignpress' ); ?></h2>
                    <div class="popular-pages">
                        <ul>
                            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'campaignpress' ); ?></a></li>
                            <?php if ( get_post_type_archive_link( 'cp_issue' ) ) : ?>
                                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_issue' ) ); ?>"><?php esc_html_e( 'Issues & Positions', 'campaignpress' ); ?></a></li>
                            <?php endif; ?>
                            <?php if ( get_post_type_archive_link( 'cp_event' ) ) : ?>
                                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_event' ) ); ?>"><?php esc_html_e( 'Campaign Events', 'campaignpress' ); ?></a></li>
                            <?php endif; ?>
                            <?php if ( get_post_type_archive_link( 'cp_endorsement' ) ) : ?>
                                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_endorsement' ) ); ?>"><?php esc_html_e( 'Endorsements', 'campaignpress' ); ?></a></li>
                            <?php endif; ?>
                            <?php if ( get_post_type_archive_link( 'cp_team' ) ) : ?>
                                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_team' ) ); ?>"><?php esc_html_e( 'Meet Our Team', 'campaignpress' ); ?></a></li>
                            <?php endif; ?>
                            <?php if ( get_post_type_archive_link( 'cp_volunteer' ) ) : ?>
                                <li><a href="<?php echo esc_url( get_post_type_archive_link( 'cp_volunteer' ) ); ?>"><?php esc_html_e( 'Volunteer', 'campaignpress' ); ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <?php
                // Display recent posts
                $recent_posts = new WP_Query( array(
                    'posts_per_page' => 3,
                    'post_status' => 'publish',
                    'ignore_sticky_posts' => true,
                ) );

                if ( $recent_posts->have_posts() ) :
                    ?>
                    <div class="recent-posts-404">
                        <h2><?php esc_html_e( 'Recent Posts', 'campaignpress' ); ?></h2>
                        <ul>
                            <?php
                            while ( $recent_posts->have_posts() ) :
                                $recent_posts->the_post();
                                ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <span class="post-date"><?php echo esc_html( get_the_date() ); ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>
                    <?php
                    wp_reset_postdata();
                endif;
                ?>

                <div class="error-404-cta">
                    <?php
                    $donation_url = get_option( 'campaignpress_donation_url' );
                    $volunteer_url = get_option( 'campaignpress_volunteer_url' );

                    if ( $donation_url || $volunteer_url ) :
                        ?>
                        <h2><?php esc_html_e( 'Get Involved', 'campaignpress' ); ?></h2>
                        <div class="cta-buttons">
                            <?php if ( $donation_url ) : ?>
                                <a href="<?php echo esc_url( $donation_url ); ?>" class="button button-primary" target="_blank" rel="noopener">
                                    <?php esc_html_e( 'Donate', 'campaignpress' ); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ( $volunteer_url ) : ?>
                                <a href="<?php echo esc_url( $volunteer_url ); ?>" class="button button-secondary" target="_blank" rel="noopener">
                                    <?php esc_html_e( 'Volunteer', 'campaignpress' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </main>
</div>

<?php
get_footer();
