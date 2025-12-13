<?php
/**
 * Render for Mission Control Block
 */
$attributes = $attributes ?? [];
$election_date = $attributes['electionDate'] ?? '';
$city = $attributes['locationCity'] ?? 'Washington DC';

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-mission-control',
    'data-date' => esc_attr($election_date)
));
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="cp-mc-dashboard">
        <!-- Weather Module -->
        <div class="cp-mc-module cp-mc-weather">
            <h4 class="cp-mc-label"><?php echo esc_html($city); ?></h4>
            <div class="cp-weather-display">
                <span class="dashicons dashicons-cloud"></span>
                <span class="cp-temp">72°F</span>
            </div>
            <p class="cp-weather-desc"><?php esc_html_e('Perfect canvassing weather', 'campaign-office'); ?></p>
        </div>

        <!-- Countdown Module -->
        <div class="cp-mc-module cp-mc-countdown">
            <h4 class="cp-mc-label"><?php esc_html_e('Election Countdown', 'campaign-office'); ?></h4>
            <div class="cp-mc-timer">
                <div class="cp-mc-unit">
                    <span class="cp-mc-val" data-unit="days">--</span>
                    <span class="cp-mc-type"><?php esc_html_e('Days', 'campaign-office'); ?></span>
                </div>
                <div class="cp-mc-unit">
                    <span class="cp-mc-val" data-unit="hours">--</span>
                    <span class="cp-mc-type"><?php esc_html_e('Hrs', 'campaign-office'); ?></span>
                </div>
                <div class="cp-mc-unit">
                    <span class="cp-mc-val" data-unit="mins">--</span>
                    <span class="cp-mc-type"><?php esc_html_e('Mins', 'campaign-office'); ?></span>
                </div>
            </div>
        </div>

        <!-- Momentum Module -->
        <div class="cp-mc-module cp-mc-momentum">
            <h4 class="cp-mc-label"><?php esc_html_e('Campaign Momentum', 'campaign-office'); ?></h4>
            <div class="cp-momentum-graph">
                 <!-- Simple CSS Graph representation -->
                 <div class="cp-graph-bar" style="height: 40%"></div>
                 <div class="cp-graph-bar" style="height: 55%"></div>
                 <div class="cp-graph-bar" style="height: 45%"></div>
                 <div class="cp-graph-bar" style="height: 70%"></div>
                 <div class="cp-graph-bar" style="height: 60%"></div>
                 <div class="cp-graph-bar active" style="height: 85%"></div>
            </div>
            <p class="cp-momentum-stat">+15% <?php esc_html_e('this week', 'campaign-office'); ?></p>
        </div>
    </div>
</div>
