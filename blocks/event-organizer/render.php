<?php
/**
 * Event Organizer Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$title             = $attributes['title'] ?? __( 'Upcoming Events', 'campaign-office' );
$display_type      = $attributes['displayType'] ?? 'list';
$events_to_show    = absint( $attributes['eventsToShow'] ?? 5 );
$show_past_events  = $attributes['showPastEvents'] ?? false;
$show_rsvp_button  = $attributes['showRSVPButton'] ?? true;
$show_event_image  = $attributes['showEventImage'] ?? true;
$show_event_location = $attributes['showEventLocation'] ?? true;
$show_event_date   = $attributes['showEventDate'] ?? true;
$show_event_time   = $attributes['showEventTime'] ?? true;
$filter_by_category = $attributes['filterByCategory'] ?? '';
$order_by          = $attributes['orderBy'] ?? 'date';
$order             = $attributes['order'] ?? 'ASC';

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-event-organizer cp-events-' . esc_attr( $display_type ),
) );

// Query events (supports both cp_event CPT and tribe_events)
$event_post_type = post_type_exists( 'tribe_events' ) ? 'tribe_events' : 'cp_event';

$meta_query = array();
if ( ! $show_past_events ) {
    $meta_query[] = array(
        'key'     => '_event_date',
        'value'   => current_time( 'Y-m-d' ),
        'compare' => '>=',
        'type'    => 'DATE',
    );
}

$args = array(
    'post_type'      => $event_post_type,
    'posts_per_page' => $events_to_show,
    'orderby'        => 'meta_value',
    'meta_key'       => '_event_date',
    'order'          => $order,
    'meta_query'     => $meta_query,
);

if ( ! empty( $filter_by_category ) ) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'event_type',
            'field'    => 'slug',
            'terms'    => $filter_by_category,
        ),
    );
}

$events_query = new WP_Query( $args );
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ( $title ) : ?>
        <h2 class="cp-events-title"><?php echo esc_html( $title ); ?></h2>
    <?php endif; ?>

    <?php if ( $events_query->have_posts() ) : ?>
        <div class="cp-events-list">
            <?php while ( $events_query->have_posts() ) : $events_query->the_post();
                $event_date = get_post_meta( get_the_ID(), '_event_date', true );
                $event_time = get_post_meta( get_the_ID(), '_event_time', true );
                $event_location = get_post_meta( get_the_ID(), '_event_location', true );
                $event_rsvp_url = get_post_meta( get_the_ID(), '_event_rsvp_url', true );

                // Format date
                $formatted_date = '';
                if ( $event_date ) {
                    $date_obj = DateTime::createFromFormat( 'Y-m-d', $event_date );
                    if ( $date_obj ) {
                        $formatted_date = $date_obj->format( get_option( 'date_format' ) );
                    }
                }
            ?>
                <article class="cp-event-item">
                    <?php if ( $show_event_image && has_post_thumbnail() ) : ?>
                        <div class="cp-event-image">
                            <?php the_post_thumbnail( 'medium' ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="cp-event-content">
                        <h3 class="cp-event-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h3>

                        <div class="cp-event-meta">
                            <?php if ( $show_event_date && $formatted_date ) : ?>
                                <span class="cp-event-date">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    <?php echo esc_html( $formatted_date ); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ( $show_event_time && $event_time ) : ?>
                                <span class="cp-event-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    <?php echo esc_html( $event_time ); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ( $show_event_location && $event_location ) : ?>
                                <span class="cp-event-location">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <?php echo esc_html( $event_location ); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="cp-event-excerpt">
                            <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                        </div>

                        <?php if ( $show_rsvp_button ) : ?>
                            <div class="cp-event-actions">
                                <?php if ( $event_rsvp_url ) : ?>
                                    <a href="<?php echo esc_url( $event_rsvp_url ); ?>" class="cp-event-rsvp-btn" target="_blank" rel="noopener">
                                        <?php esc_html_e( 'RSVP', 'campaign-office' ); ?>
                                    </a>
                                <?php endif; ?>
                                <a href="<?php the_permalink(); ?>" class="cp-event-details-btn">
                                    <?php esc_html_e( 'Details', 'campaign-office' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p class="cp-no-events"><?php esc_html_e( 'No upcoming events scheduled.', 'campaign-office' ); ?></p>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</div>
