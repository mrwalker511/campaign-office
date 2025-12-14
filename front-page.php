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
?>

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
        
        // Close the #content wrapper to allow full-width hero
        ?>
        </div><!-- Close #content from header.php -->
        
        <!-- Enhanced Hero Section -->
        <section class="relative w-full min-h-screen flex items-center justify-center overflow-hidden bg-brand-900">
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
                        <div class="absolute inset-0 z-0">
                            <video autoplay muted loop playsinline class="w-full h-full object-cover">
                                <source src="<?php echo esc_url( $hero_video_url ); ?>" type="<?php echo esc_attr( $hero_video_type ); ?>">
                                <?php esc_html_e( 'Your browser does not support the video tag.', 'campaign-office' ); ?>
                            </video>
                            <div class="absolute inset-0 bg-gradient-to-b from-brand-900/80 via-brand-900/70 to-brand-900/90"></div>
                        </div>
                    <?php elseif ( has_post_thumbnail() ) : ?>
                        <div class="absolute inset-0 z-0">
                            <?php the_post_thumbnail( 'full', array( 'class' => 'w-full h-full object-cover' ) ); ?>
                            <div class="absolute inset-0 bg-gradient-to-b from-brand-900/80 via-brand-900/70 to-brand-900/90"></div>
                        </div>
                    <?php else : ?>
                        <div class="absolute inset-0 z-0 bg-gradient-to-br from-brand-800 to-brand-900"></div>
                    <?php endif; ?>

                    <div class="container mx-auto px-4 relative z-10 text-center">
                        <h1 class="font-serif text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-tight mb-6 animate-fade-in-up">
                            <?php echo esc_html( $candidate_name ); ?>
                        </h1>
                        
                        <p class="text-2xl md:text-3xl text-brand-200 mb-4 animate-fade-in-up animation-delay-200">
                            <?php echo esc_html( $office_seeking ); ?>
                        </p>
                        
                        <p class="text-xl md:text-2xl text-white/90 mb-10 max-w-3xl mx-auto animate-fade-in-up animation-delay-400">
                            <?php echo esc_html( $tagline ); ?>
                        </p>
                        
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center animate-fade-in-up animation-delay-600">
                            <a href="<?php echo esc_url( $donation_url ); ?>" 
                               class="inline-flex items-center justify-center py-4 px-10 bg-accent-600 hover:bg-accent-700 text-white text-lg font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-2xl focus:outline-none focus:ring-4 focus:ring-accent-500/50 min-w-[200px]">
                                <span class="dashicons dashicons-heart mr-2"></span>
                                <?php esc_html_e( 'Donate Now', 'campaign-office' ); ?>
                            </a>
                            
                            <a href="<?php echo esc_url( $volunteer_url ); ?>" 
                               class="inline-flex items-center justify-center py-4 px-10 bg-transparent border-2 border-white hover:bg-white/10 text-white text-lg font-bold rounded-lg transition-all focus:outline-none focus:ring-4 focus:ring-white/30 min-w-[200px]">
                                <span class="dashicons dashicons-groups mr-2"></span>
                                <?php esc_html_e( 'Get Involved', 'campaign-office' ); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Scroll Indicator -->
                    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 z-10 animate-bounce">
                        <svg class="w-6 h-6 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="container mx-auto px-4 py-16 max-w-5xl">
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="entry-content prose prose-lg max-w-none">
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
            <section class="py-20 md:py-28 bg-white">
                <div class="container mx-auto px-4 max-w-7xl">
                    <div class="text-center mb-16">
                        <h2 class="font-serif text-4xl md:text-5xl font-bold text-brand-900 mb-4">
                            <?php esc_html_e( 'Key Issues', 'campaign-office' ); ?>
                        </h2>
                        <p class="text-xl text-neutral-600 max-w-3xl mx-auto">
                            <?php esc_html_e( 'Our platform addresses the most pressing challenges facing our community', 'campaign-office' ); ?>
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    while ( $issues->have_posts() ) :
                        $issues->the_post();
                        ?>
                        <article class="group bg-white rounded-2xl border border-neutral-200 overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="aspect-[16/10] overflow-hidden">
                                    <a href="<?php the_permalink(); ?>">
                                        <?php the_post_thumbnail( 'large', array( 'class' => 'w-full h-full object-cover group-hover:scale-110 transition-transform duration-300' ) ); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-8">
                                <h3 class="text-2xl font-bold text-brand-900 mb-4 group-hover:text-brand-600 transition-colors">
                                    <a href="<?php the_permalink(); ?>" class="hover:underline">
                                        <?php the_title(); ?>
                                    </a>
                                </h3>
                                
                                <div class="text-neutral-600 leading-relaxed mb-6">
                                    <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                                </div>
                                
                                <a href="<?php the_permalink(); ?>" 
                                   class="inline-flex items-center text-accent-600 font-semibold hover:text-accent-700 transition-colors group/link"
                                   aria-label="<?php echo esc_attr( sprintf( __( 'Read more about %s', 'campaign-office' ), get_the_title() ) ); ?>">
                                    <?php esc_html_e( 'Learn More', 'campaign-office' ); ?>
                                    <svg class="w-5 h-5 ml-2 group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    </div>
                    
                    <div class="text-center mt-12">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'cp_issue' ) ); ?>" 
                           class="inline-flex items-center justify-center py-4 px-8 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-lg">
                            <?php esc_html_e( 'View All Issues', 'campaign-office' ); ?>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <section class="py-20 md:py-28 bg-neutral-50">
                <div class="container mx-auto px-4 max-w-7xl">
                    <div class="text-center mb-16">
                        <h2 class="font-serif text-4xl md:text-5xl font-bold text-brand-900 mb-4">
                            <?php esc_html_e( 'Upcoming Events', 'campaign-office' ); ?>
                        </h2>
                        <p class="text-xl text-neutral-600 max-w-3xl mx-auto">
                            <?php esc_html_e( 'Join us at our upcoming campaign events and town halls', 'campaign-office' ); ?>
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php
                    while ( $events->have_posts() ) :
                        $events->the_post();
                        $datetime = campaignpress_get_event_datetime();
                        $location = campaignpress_get_event_location();
                        ?>
                        <article class="bg-white rounded-2xl border border-neutral-200 p-8 hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                            <?php if ( $datetime['date'] ) : ?>
                                <div class="flex items-center gap-3 text-accent-600 mb-6 pb-6 border-b border-neutral-200">
                                    <div class="flex-shrink-0 w-14 h-14 bg-accent-100 rounded-xl flex items-center justify-center">
                                        <span class="dashicons dashicons-calendar-alt text-2xl"></span>
                                    </div>
                                    <div>
                                        <time datetime="<?php echo esc_attr( $datetime['datetime'] ); ?>" class="block font-bold text-lg">
                                            <?php echo esc_html( $datetime['date'] ); ?>
                                        </time>
                                        <?php if ( $datetime['time'] ) : ?>
                                            <span class="text-sm text-neutral-600">
                                                <?php echo esc_html( $datetime['time'] ); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <h3 class="text-2xl font-bold text-brand-900 mb-4">
                                <a href="<?php the_permalink(); ?>" class="hover:text-brand-600 transition-colors">
                                    <?php the_title(); ?>
                                </a>
                            </h3>
                            
                            <?php if ( $location['city'] ) : ?>
                                <div class="flex items-center gap-2 text-neutral-600 mb-6">
                                    <span class="dashicons dashicons-location text-accent-600"></span>
                                    <span><?php echo esc_html( $location['city'] ); ?><?php echo $location['state'] ? ', ' . esc_html( $location['state'] ) : ''; ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <a href="<?php the_permalink(); ?>" 
                               class="inline-flex items-center text-accent-600 font-semibold hover:text-accent-700 transition-colors group"
                               aria-label="<?php echo esc_attr( sprintf( __( 'Event details for %s', 'campaign-office' ), get_the_title() ) ); ?>">
                                <?php esc_html_e( 'Event Details', 'campaign-office' ); ?>
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        </article>
                    <?php endwhile; ?>
                    </div>
                    
                    <div class="text-center mt-12">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'cp_event' ) ); ?>" 
                           class="inline-flex items-center justify-center py-4 px-8 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-lg transition-all transform hover:-translate-y-1 hover:shadow-lg">
                            <?php esc_html_e( 'View All Events', 'campaign-office' ); ?>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            </div>
            </div><!-- Close #content wrapper -->

<style>
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fade-in-up 0.8s ease-out forwards;
}

.animation-delay-200 {
    animation-delay: 0.2s;
    opacity: 0;
}

.animation-delay-400 {
    animation-delay: 0.4s;
    opacity: 0;
}

.animation-delay-600 {
    animation-delay: 0.6s;
    opacity: 0;
}
</style>

<?php
get_footer();
