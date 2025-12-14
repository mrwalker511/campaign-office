<?php
/**
 * The template for displaying single Events
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
                    <?php
                    $event_types = get_the_terms( get_the_ID(), 'event_type' );
                    if ( $event_types && ! is_wp_error( $event_types ) ) :
                        ?>
                        <div class="event-type">
                            <?php foreach ( $event_types as $event_type ) : ?>
                                <a href="<?php echo esc_url( get_term_link( $event_type ) ); ?>" class="type-badge">
                                    <?php echo esc_html( $event_type->name ); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail">
                        <?php the_post_thumbnail( 'campaignpress-event-hero' ); ?>
                    </div>
                <?php endif; ?>

                <div class="event-meta-box">
                    <?php campaignpress_display_event_details(); ?>
                </div>

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
                    <?php
                    $rsvp_link = get_post_meta( get_the_ID(), '_cp_event_rsvp_link', true );
                    if ( $rsvp_link ) :
                        ?>
                        <div class="event-rsvp-footer">
                            <h3><?php esc_html_e( 'Join Us at This Event', 'campaign-office' ); ?></h3>
                            <a href="<?php echo esc_url( $rsvp_link ); ?>" class="button button-primary button-large" target="_blank" rel="noopener">
                                <?php esc_html_e( 'RSVP Now', 'campaign-office' ); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="post-navigation">
                        <?php
                        the_post_navigation( array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous Event:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next Event:', 'campaign-office' ) . '</span> <span class="nav-title">%title</span>',
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
