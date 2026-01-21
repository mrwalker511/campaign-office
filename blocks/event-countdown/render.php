<?php
/**
 * Event Countdown Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$event_date = $attributes['eventDate'] ?? '';
$event_title = $attributes['eventTitle'] ?? __( 'Election Day', 'campaignpress' );

// Validate date format (YYYY-MM-DD)
if ( empty( $event_date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $event_date ) ) {
    if ( current_user_can( 'edit_posts' ) ) {
        return sprintf(
            '<div class="cp-block-notice">%s</div>',
            esc_html__( 'Please set a valid event date (YYYY-MM-DD format).', 'campaignpress' )
        );
    }
    return '';
}

$target_timestamp = strtotime( $event_date . ' midnight', current_time( 'timestamp' ) );
if ( false === $target_timestamp ) {
    return '';
}

$current_timestamp = current_time( 'timestamp' );
$time_diff = $target_timestamp - $current_timestamp;

if ( $time_diff < 0 ) {
    return sprintf(
        '<div class="cp-event-countdown"><p>%s</p></div>',
        esc_html__( 'This event has passed.', 'campaignpress' )
    );
}

$days = floor( $time_diff / ( 60 * 60 * 24 ) );

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-event-countdown',
) );
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <h3 class="cp-countdown-title"><?php echo esc_html( $event_title ); ?></h3>
    <div class="cp-countdown-display">
        <span class="cp-countdown-number"><?php echo esc_html( (string) $days ); ?></span>
        <span class="cp-countdown-label"><?php echo esc_html( _n( 'Day', 'Days', $days, 'campaignpress' ) ); ?></span>
    </div>
</div>
