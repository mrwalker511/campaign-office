<?php
/**
 * The template for displaying the front page
 *
 * Enhanced homepage with improved layout and wider columns
 *
 * @package CampaignPress
 * @since 2.0.0
 */

get_header();

// Close the #content wrapper from header.php to allow full-width sections on front page
?>
</div><!-- Close #content from header.php -->

<?php
while ( have_posts() ) :
    the_post();

    // Check if we should display the campaign hero section
    $show_hero = get_post_meta( get_the_ID(), '_campaignpress_show_hero', true );
    if ( $show_hero !== '0' ) :
        $candidate_name = get_option( 'campaignpress_candidate_name', 'Your Name' );
        $office_seeking = get_option( 'campaignpress_office_seeking', 'for Office' );
        $tagline = get_option( 'campaignpress_campaign_tagline', 'Building a Better Future Together' );
        $donation_url = get_option( 'campaignpress_donation_url', '#donate' );
        $volunteer_url = get_option( 'campaignpress_volunteer_url', '#volunteer' );
        ?>
        
        <!-- Enhanced Hero Section -->
        <section class="campaign-hero-section">
                    <?php
                    // Check for video overlay option
                    $hero_video_url = get_post_meta( get_the_ID(), '_campaignpress_hero_video_url', true );
                    $hero_video_type = get_post_meta( get_the_ID(), '_campaignpress_hero_video_type', true );

                    if ( empty( $hero_video_url ) && get_option( 'campaignpress_enable_hero_video' ) ) {
                        $hero_video_url = get_option( 'campaignpress_hero_video_url' );
                        $hero_video_type = get_option( 'campaignpress_hero_video_type', 'video/mp4' );
                    }

                    if ( ! empty( $hero_video_url ) ) :
                        ?>
                        <div class="hero-media-wrapper">
                            <video autoplay muted loop playsinline class="hero-video">
                                <source src="<?php echo esc_url( $hero_video_url ); ?>" type="<?php echo esc_attr( $hero_video_type ); ?>">
                                <?php esc_html_e( 'Your browser does not support the video tag.', 'campaign-office' ); ?>
                            </video>
                            <div class="hero-overlay"></div>
                        </div>
                    <?php elseif ( has_post_thumbnail() ) : ?>
                        <div class="hero-media-wrapper">
                            <?php the_post_thumbnail( 'full', array( 'class' => 'hero-image' ) ); ?>
                            <div class="hero-overlay"></div>
                        </div>
                    <?php else : ?>
                        <div class="hero-background-gradient"></div>
                    <?php endif; ?>

                    <div class="hero-content-container">
                        <h1 class="hero-title">
                            <?php echo esc_html( $candidate_name ); ?>
                        </h1>

                        <p class="hero-office">
                            <?php echo esc_html( $office_seeking ); ?>
                        </p>

                        <p class="hero-tagline">
                            <?php echo esc_html( $tagline ); ?>
                        </p>

                        <div class="hero-cta-buttons">
                            <a href="<?php echo esc_url( $donation_url ); ?>" class="hero-cta-primary">
                                <span class="dashicons dashicons-heart"></span>
                                <?php esc_html_e( 'Donate Now', 'campaign-office' ); ?>
                            </a>

                            <a href="<?php echo esc_url( $volunteer_url ); ?>" class="hero-cta-secondary">
                                <span class="dashicons dashicons-groups"></span>
                                <?php esc_html_e( 'Get Involved', 'campaign-office' ); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Scroll Indicator -->
                    <div class="hero-scroll-indicator">
                        <svg class="scroll-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </div>
                </section>
            <?php endif; ?>
            
            <!-- Reopen #content wrapper for remaining content -->
            <div id="content" class="site-content" role="main">
            <div id="primary" class="content-area front-page">
                <main id="main" class="site-main">

            <!-- Page Content (if any) -->
            <?php if ( get_the_content() ) : ?>
                <div class="page-content-wrapper">
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="entry-content">
                            <?php
                            the_content();
                            wp_link_pages( array(
                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campaign-office' ),
                                'after'  => '</div>',
                            ) );
                            ?>
                        </div>
                    </article>
                </div>
            <?php endif; ?>

        <?php endwhile; ?>

        <?php
        // Display recent issues with improved layout
        $issues = new WP_Query( array(
            'post_type' => 'cp_issue',
            'posts_per_page' => 3,
            'orderby' => 'date',
            'order' => 'DESC',
        ) );

        if ( $issues->have_posts() ) :
            ?>
            <section class="front-page-section issues-section">
                <div class="section-container">
                    <div class="section-header">
                        <h2 class="section-title">
                            <?php esc_html_e( 'Key Issues', 'campaign-office' ); ?>
                        </h2>
                        <p class="section-description">
                            <?php esc_html_e( 'Our platform addresses the most pressing challenges facing our community', 'campaign-office' ); ?>
                        </p>
                    </div>

                    <div class="issues-grid">
                    <?php
                    while ( $issues->have_posts() ) :
                        $issues->the_post();
                        ?>
                        <article class="issue-card">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="issue-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'large', array( 'class' => 'issue-image' ) ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="issue-content">
                                <h3 class="issue-title">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>

                                <div class="issue-excerpt">
                                    <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                                </div>

                                <a href="<?php the_permalink(); ?>"
                                   class="issue-link"
                                   aria-label="<?php echo esc_attr( sprintf( __( 'Read more about %s', 'campaign-office' ), get_the_title() ) ); ?>">
                                    <?php esc_html_e( 'Learn More', 'campaign-office' ); ?>
                                    <svg class="link-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    </div>

                    <div class="section-footer">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'cp_issue' ) ); ?>" class="button-primary">
                            <?php esc_html_e( 'View All Issues', 'campaign-office' ); ?>
                            <svg class="button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        endif;

        // Display upcoming events with improved layout
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
                <div class="section-container">
                    <div class="section-header">
                        <h2 class="section-title">
                            <?php esc_html_e( 'Upcoming Events', 'campaign-office' ); ?>
                        </h2>
                        <p class="section-description">
                            <?php esc_html_e( 'Join us at our upcoming campaign events and town halls', 'campaign-office' ); ?>
                        </p>
                    </div>

                    <div class="events-grid">
                    <?php
                    while ( $events->have_posts() ) :
                        $events->the_post();
                        $datetime = campaignpress_get_event_datetime();
                        $location = campaignpress_get_event_location();
                        ?>
                        <article class="event-card">
                            <?php if ( $datetime['date'] ) : ?>
                                <div class="event-date-wrapper">
                                    <div class="event-date-icon">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                    </div>
                                    <div class="event-date-info">
                                        <time datetime="<?php echo esc_attr( $datetime['datetime'] ); ?>" class="event-date">
                                            <?php echo esc_html( $datetime['date'] ); ?>
                                        </time>
                                        <?php if ( $datetime['time'] ) : ?>
                                            <span class="event-time">
                                                <?php echo esc_html( $datetime['time'] ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <h3 class="event-title">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_title(); ?>
                                </a>
                            </h3>

                            <?php if ( $location['city'] ) : ?>
                                <div class="event-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <span><?php echo esc_html( $location['city'] ); ?><?php echo $location['state'] ? ', ' . esc_html( $location['state'] ) : ''; ?></span>
                                </div>
                            <?php endif; ?>

                            <a href="<?php the_permalink(); ?>"
                               class="event-link"
                               aria-label="<?php echo esc_attr( sprintf( __( 'Event details for %s', 'campaign-office' ), get_the_title() ) ); ?>">
                                <?php esc_html_e( 'Event Details', 'campaign-office' ); ?>
                                <svg class="link-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        </article>
                    <?php endwhile; ?>
                    </div>

                    <div class="section-footer">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'cp_event' ) ); ?>" class="button-primary">
                            <?php esc_html_e( 'View All Events', 'campaign-office' ); ?>
                            <svg class="button-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        endif;
        ?>

                </main>
            </div><!-- #primary -->

<?php
get_footer();
