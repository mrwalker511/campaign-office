<?php
/**
 * The template for displaying Events Archive
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
                <?php esc_html_e( 'Campaign Events', 'campaign-office' ); ?>
            </h1>
            <?php
            $archive_description = get_the_archive_description();
            if ( $archive_description ) :
                ?>
                <div class="archive-description"><?php echo wp_kses_post( wpautop( $archive_description ) ); ?></div>
            <?php endif; ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="events-list">
                <?php
                while ( have_posts() ) :
                    the_post();
                    $datetime = campaignpress_get_event_datetime();
                    $location = campaignpress_get_event_location();
                    $rsvp_link = get_post_meta( get_the_ID(), '_cp_event_rsvp_link', true );
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'event-card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="event-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'campaignpress-event-hero' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="event-content">
                            <?php if ( $datetime['date'] ) : ?>
                                <div class="event-meta-date">
                                    <span class="dashicons dashicons-calendar-alt"></span>
                                    <time datetime="<?php echo esc_attr( $datetime['datetime'] ); ?>">
                                        <?php echo esc_html( $datetime['date'] ); ?>
                                        <?php if ( $datetime['time'] ) : ?>
                                            <?php esc_html_e( 'at', 'campaign-office' ); ?> <?php echo esc_html( $datetime['time'] ); ?>
                                        <?php endif; ?>
                                    </time>
                                </div>
                            <?php endif; ?>

                            <header class="entry-header">
                                <?php
                                $event_types = get_the_terms( get_the_ID(), 'event_type' );
                                if ( $event_types && ! is_wp_error( $event_types ) ) :
                                    ?>
                                    <div class="event-type">
                                        <?php foreach ( $event_types as $event_type ) : ?>
                                            <span class="type-badge"><?php echo esc_html( $event_type->name ); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <h2 class="entry-title">
                                    <a href="<?php the_permalink(); ?>" rel="bookmark">
                                        <?php the_title(); ?>
                                    </a>
                                </h2>
                            </header>

                            <?php if ( $location['name'] || $location['city'] ) : ?>
                                <div class="event-meta-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php
                                    if ( $location['name'] ) {
                                        echo esc_html( $location['name'] );
                                        if ( $location['city'] ) {
                                            echo ' - ';
                                        }
                                    }
                                    if ( $location['city'] ) {
                                        echo esc_html( $location['city'] );
                                        if ( $location['state'] ) {
                                            echo ', ' . esc_html( $location['state'] );
                                        }
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>

                            <div class="entry-summary">
                                <?php the_excerpt(); ?>
                            </div>

                            <footer class="entry-footer">
                                <div class="event-actions">
                                    <a href="<?php the_permalink(); ?>" class="button button-secondary">
                                        <?php esc_html_e( 'Event Details', 'campaign-office' ); ?>
                                    </a>
                                    <?php if ( $rsvp_link ) : ?>
                                        <a href="<?php echo esc_url( $rsvp_link ); ?>" class="button button-primary" target="_blank" rel="noopener">
                                            <?php esc_html_e( 'RSVP', 'campaign-office' ); ?>
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
                    <h1 class="page-title"><?php esc_html_e( 'No Events Found', 'campaign-office' ); ?></h1>
                </header>

                <div class="page-content">
                    <p><?php esc_html_e( 'No campaign events have been scheduled yet. Check back soon!', 'campaign-office' ); ?></p>
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
