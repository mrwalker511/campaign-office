<?php
/**
 * Render for Countdown Block
 */
$attributes = $attributes ?? [];
$target_date = $attributes['targetDate'] ?? '';
$headline = $attributes['headline'] ?? 'Election Day Countdown';

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'campaignpress-countdown',
    'data-date' => esc_attr($target_date)
));
?>
<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <?php if ($headline): ?>
        <h3 class="wp-block-campaignpress-countdown__title"><?php echo esc_html($headline); ?></h3>
    <?php endif; ?>

    <?php if (empty($target_date)): ?>
        <p class="campaignpress-countdown-placeholder"><?php esc_html_e('Please set a date in block settings.', 'campaignpress'); ?></p>
    <?php else: ?>
        <div class="campaignpress-countdown-timer">
            <div class="campaignpress-countdown-item">
                <span class="campaignpress-countdown-number" data-unit="days">00</span>
                <span class="campaignpress-countdown-label"><?php esc_html_e('Days', 'campaignpress'); ?></span>
            </div>
            <div class="campaignpress-countdown-item">
                <span class="campaignpress-countdown-number" data-unit="hours">00</span>
                <span class="campaignpress-countdown-label"><?php esc_html_e('Hours', 'campaignpress'); ?></span>
            </div>
            <div class="campaignpress-countdown-item">
                <span class="campaignpress-countdown-number" data-unit="minutes">00</span>
                <span class="campaignpress-countdown-label"><?php esc_html_e('Minutes', 'campaignpress'); ?></span>
            </div>
             <div class="campaignpress-countdown-item">
                <span class="campaignpress-countdown-number" data-unit="seconds">00</span>
                <span class="campaignpress-countdown-label"><?php esc_html_e('Seconds', 'campaignpress'); ?></span>
            </div>
        </div>
    <?php endif; ?>
</div>
