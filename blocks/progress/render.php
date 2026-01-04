<?php
/**
 * Progress Tracker Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$title           = $attributes['title'] ?? __( 'Campaign Progress', 'campaign-office' );
$goal_type       = $attributes['goalType'] ?? 'fundraising';
$current_value   = absint( $attributes['currentValue'] ?? 0 );
$goal_value      = absint( $attributes['goalValue'] ?? 10000 );
$show_percentage = $attributes['showPercentage'] ?? true;
$show_numbers    = $attributes['showNumbers'] ?? true;
$currency_symbol = $attributes['currencySymbol'] ?? '$';
$bar_color       = $attributes['barColor'] ?? '#0053c3';
$bg_color        = $attributes['backgroundColor'] ?? '#e9ecef';
$bar_height      = $attributes['barHeight'] ?? '40px';
$animate         = $attributes['animateOnScroll'] ?? true;

// Calculate percentage
$percentage = $goal_value > 0 ? min( 100, ( $current_value / $goal_value ) * 100 ) : 0;

// Format values based on goal type
$formatted_current = $goal_type === 'fundraising'
    ? $currency_symbol . number_format( $current_value )
    : number_format( $current_value );

$formatted_goal = $goal_type === 'fundraising'
    ? $currency_symbol . number_format( $goal_value )
    : number_format( $goal_value );

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-progress-tracker',
    'data-animate' => $animate ? 'true' : 'false',
) );
?>

<div <?php echo $wrapper_attributes; ?>>
    <?php if ( $title ) : ?>
        <h3 class="cp-progress-title"><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>

    <?php if ( $show_numbers ) : ?>
        <div class="cp-progress-stats">
            <span class="cp-progress-current"><?php echo esc_html( $formatted_current ); ?></span>
            <span class="cp-progress-goal">
                <?php
                printf(
                    /* translators: %s: goal amount */
                    esc_html__( 'Goal: %s', 'campaign-office' ),
                    esc_html( $formatted_goal )
                );
                ?>
            </span>
        </div>
    <?php endif; ?>

    <div class="cp-progress-bar-container" style="background-color: <?php echo esc_attr( $bg_color ); ?>; height: <?php echo esc_attr( $bar_height ); ?>;">
        <div
            class="cp-progress-bar"
            style="width: <?php echo esc_attr( $percentage ); ?>%; background-color: <?php echo esc_attr( $bar_color ); ?>;"
            data-percentage="<?php echo esc_attr( $percentage ); ?>"
        >
            <?php if ( $show_percentage ) : ?>
                <span class="cp-progress-percentage"><?php echo esc_html( round( $percentage ) ); ?>%</span>
            <?php endif; ?>
        </div>
    </div>
</div>
