<?php
/**
 * The template for displaying the front page
 *
 * This is the template that displays the home page by default.
 * If a static page is set as the front page, this template will be used.
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div id="primary" class="content-area front-page">
    <main id="main" class="site-main">

        <?php
        while ( have_posts() ) :
            the_post();
            ?>

            <?php
            // Check if we should display the campaign hero section
            $show_hero = get_post_meta( get_the_ID(), '_campaignpress_show_hero', true );
            if ( $show_hero !== '0' ) :
                $candidate_name = get_option( 'campaignpress_candidate_name' );
                $office_seeking = get_option( 'campaignpress_office_seeking' );
                $tagline = get_option( 'campaignpress_campaign_tagline' );
                $donation_url = get_option( 'campaignpress_donation_url' );
                $volunteer_url = get_option( 'campaignpress_volunteer_url' );
                ?>

                <section class="campaign-hero">
                    <?php
                    // Check for video overlay option - page-specific first, then theme default
                    $hero_video_url = get_post_meta( get_the_ID(), '_campaignpress_hero_video_url', true );
                    $hero_video_type = get_post_meta( get_the_ID(), '_campaignpress_hero_video_type', true );

                    // If no page-specific video, check theme options
                    if ( empty( $hero_video_url ) && get_option( 'campaignpress_enable_hero_video' ) ) {
                        $hero_video_url = get_option( 'campaignpress_hero_video_url' );
                        $hero_video_type = get_option( 'campaignpress_hero_video_type', 'video/mp4' );
                    }

                    if ( ! empty( $hero_video_url ) ) :
                        ?>
                        <div class="hero-background">
                            <video autoplay muted loop playsinline>
                                <?php if ( $hero_video_type ) : ?>
                                    <source src="<?php echo esc_url( $hero_video_url ); ?>" type="<?php echo esc_attr( $hero_video_type ); ?>">
                                <?php else : ?>
                                    <source src="<?php echo esc_url( $hero_video_url ); ?>" type="video/mp4">
                                <?php endif; ?>
                                <?php esc_html_e( 'Your browser does not support the video tag.', 'campaignpress' ); ?>
                            </video>
                        </div>
                    <?php elseif ( has_post_thumbnail() ) : ?>
                        <div class="hero-background">
                            <?php the_post_thumbnail( 'full' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="hero-content">
                        <?php if ( $candidate_name ) : ?>
                            <h1 class="hero-title"><?php echo esc_html( $candidate_name ); ?></h1>
                        <?php endif; ?>

                        <?php if ( $office_seeking ) : ?>
                            <p class="hero-subtitle"><?php echo esc_html( $office_seeking ); ?></p>
                        <?php endif; ?>

                        <?php if ( $tagline ) : ?>
                            <p class="hero-tagline"><?php echo esc_html( $tagline ); ?></p>
                        <?php endif; ?>

                        <div class="hero-cta">
                            <?php if ( $donation_url ) : ?>
                                <a href="<?php echo esc_url( $donation_url ); ?>" class="button button-primary button-large" target="_blank" rel="noopener">
                                    <?php esc_html_e( 'Donate', 'campaignpress' ); ?>
                                </a>
                            <?php endif; ?>

                            <?php if ( $volunteer_url ) : ?>
                                <a href="<?php echo esc_url( $volunteer_url ); ?>" class="button button-secondary button-large" target="_blank" rel="noopener">
                                    <?php esc_html_e( 'Volunteer', 'campaignpress' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <div class="site-container">
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="entry-content">
                    <?php
                    the_content();

                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campaignpress' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div><!-- .entry-content -->
            </article><!-- #post-<?php the_ID(); ?> -->
            </div><!-- .site-container -->

        <?php endwhile; ?>

        <?php
        // Display recent issues
        $issues = new WP_Query( array(
            'post_type' => 'cp_issue',
            'posts_per_page' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
        ) );

        if ( $issues->have_posts() ) :
            ?>
            <section class="front-page-section issues-section">
                <h2 class="section-title"><?php esc_html_e( 'Key Issues', 'campaignpress' ); ?></h2>
                <div class="issues-grid">
                    <?php
                    while ( $issues->have_posts() ) :
                        $issues->the_post();
                        ?>
                        <article class="issue-card">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="issue-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'medium' ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="issue-content">
                                <h3 class="issue-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <div class="issue-excerpt"><?php the_excerpt(); ?></div>
                                <a href="<?php the_permalink(); ?>" class="read-more"><?php esc_html_e( 'Read More', 'campaignpress' ); ?></a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                <div class="section-footer">
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'cp_issue' ) ); ?>" class="button button-secondary">
                        <?php esc_html_e( 'View All Issues', 'campaignpress' ); ?>
                    </a>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        endif;

        // Display upcoming events
        $events = new WP_Query( array(
            'post_type' => 'cp_event',
            'posts_per_page' => 3,
            'meta_key' => '_cp_event_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_cp_event_date',
                    'value' => current_time( 'Y-m-d' ),
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
            ),
        ) );

        if ( $events->have_posts() ) :
            ?>
            <section class="front-page-section events-section">
                <h2 class="section-title"><?php esc_html_e( 'Upcoming Events', 'campaignpress' ); ?></h2>
                <div class="events-list">
                    <?php
                    while ( $events->have_posts() ) :
                        $events->the_post();
                        $datetime = campaignpress_get_event_datetime();
                        $location = campaignpress_get_event_location();
                        ?>
                        <article class="event-card">
                            <div class="event-date">
                                <?php if ( $datetime['date'] ) : ?>
                                    <span class="dashicons dashicons-calendar-alt"></span>
                                    <time datetime="<?php echo esc_attr( $datetime['datetime'] ); ?>">
                                        <?php echo esc_html( $datetime['date'] ); ?>
                                        <?php if ( $datetime['time'] ) : ?>
                                            <?php esc_html_e( 'at', 'campaignpress' ); ?> <?php echo esc_html( $datetime['time'] ); ?>
                                        <?php endif; ?>
                                    </time>
                                <?php endif; ?>
                            </div>
                            <h3 class="event-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h3>
                            <?php if ( $location['city'] ) : ?>
                                <div class="event-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html( $location['city'] ); ?><?php echo $location['state'] ? ', ' . esc_html( $location['state'] ) : ''; ?>
                                </div>
                            <?php endif; ?>
                            <a href="<?php the_permalink(); ?>" class="read-more"><?php esc_html_e( 'Event Details', 'campaignpress' ); ?></a>
                        </article>
                    <?php endwhile; ?>
                </div>
                <div class="section-footer">
                    <a href="<?php echo esc_url( get_post_type_archive_link( 'cp_event' ) ); ?>" class="button button-secondary">
                        <?php esc_html_e( 'View All Events', 'campaignpress' ); ?>
                    </a>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        endif;
        ?>

    </main>
</div>

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
