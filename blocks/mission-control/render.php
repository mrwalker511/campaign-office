<?php
/**
 * Render for Mission Control Block
 * WordPress dependencies: server-side rendering with validation
 * 
 * @package CampaignPress
 * @since 2.0.0
 */
$attributes = $attributes ?? [];
$election_date = $attributes['electionDate'] ?? '';
$city = $attributes['locationCity'] ?? 'Washington DC';

// Validate election date format (YYYY-MM-DD or ISO format)
$valid_date = false;
if (!empty($election_date)) {
    $timestamp = strtotime($election_date);
    if ($timestamp !== false && $timestamp > time()) {
        $valid_date = true;
    }
}

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-mission-control',
    'data-date' => $valid_date ? esc_attr($election_date) : ''
));
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="cp-mc-dashboard">
        <!-- Weather Module -->
        <div class="cp-mc-module cp-mc-weather" role="region" aria-label="<?php esc_attr_e('Weather Information', 'campaignpress'); ?>">
            <h4 class="cp-mc-label"><?php echo esc_html($city); ?></h4>
            <div class="cp-weather-display">
                <span class="dashicons dashicons-cloud" aria-hidden="true"></span>
                <span class="cp-temp">72°F</span>
            </div>
            <p class="cp-weather-desc"><?php esc_html_e('Perfect canvassing weather', 'campaignpress'); ?></p>
        </div>

        <!-- Countdown Module -->
        <div class="cp-mc-module cp-mc-countdown" role="region" aria-label="<?php esc_attr_e('Election Countdown', 'campaignpress'); ?>">
            <h4 class="cp-mc-label"><?php esc_html_e('Election Countdown', 'campaignpress'); ?></h4>
            <div class="cp-mc-timer">
                <div class="cp-mc-unit">
                    <span class="cp-mc-val" data-unit="days">--</span>
                    <span class="cp-mc-type"><?php esc_html_e('Days', 'campaignpress'); ?></span>
                </div>
                <div class="cp-mc-unit">
                    <span class="cp-mc-val" data-unit="hours">--</span>
                    <span class="cp-mc-type"><?php esc_html_e('Hrs', 'campaignpress'); ?></span>
                </div>
                <div class="cp-mc-unit">
                    <span class="cp-mc-val" data-unit="mins">--</span>
                    <span class="cp-mc-type"><?php esc_html_e('Mins', 'campaignpress'); ?></span>
                </div>
            </div>
            <?php if (!$valid_date): ?>
                <p class="cp-mc-error" role="alert"><?php esc_html_e('Please set a valid election date in block settings.', 'campaignpress'); ?></p>
            <?php endif; ?>
        </div>

        <!-- Momentum Module -->
        <div class="cp-mc-module cp-mc-momentum" role="region" aria-label="<?php esc_attr_e('Campaign Momentum', 'campaignpress'); ?>">
            <h4 class="cp-mc-label"><?php esc_html_e('Campaign Momentum', 'campaignpress'); ?></h4>
            <div class="cp-momentum-graph" role="img" aria-label="<?php esc_attr_e('Campaign momentum chart showing +15% growth this week', 'campaignpress'); ?>">
                <div class="cp-graph-bar" style="height: 40%"></div>
                <div class="cp-graph-bar" style="height: 55%"></div>
                <div class="cp-graph-bar" style="height: 45%"></div>
                <div class="cp-graph-bar" style="height: 70%"></div>
                <div class="cp-graph-bar" style="height: 60%"></div>
                <div class="cp-graph-bar active" style="height: 85%"></div>
            </div>
            <p class="cp-momentum-stat">+15% <span class="cp-momentum-period"><?php esc_html_e('this week', 'campaignpress'); ?></span></p>
        </div>
    </div>
</div>