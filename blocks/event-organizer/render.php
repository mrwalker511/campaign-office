<?php
/**
 * Render for Event Organizer Block
 */
$attributes = $attributes ?? [];
$show_map = $attributes['showMap'] ?? true;
$rsvp_enabled = $attributes['rsvpEnabled'] ?? true;

// Mock data if no events selected (Phase 4 placeholder logic)
// In a real scenario, this would query the 'cp_event' post type.
$events = [
    [
        'title' => 'Town Hall Meeting',
        'date' => date('M d, Y', strtotime('+2 days')),
        'time' => '6:00 PM',
        'location' => 'City Library',
        'rsvp_count' => 124
    ],
    [
        'title' => 'Fundraising Dinner',
        'date' => date('M d, Y', strtotime('+1 week')),
        'time' => '7:00 PM',
        'location' => 'Grand Hotel',
        'rsvp_count' => 85
    ]
];

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-event-organizer'
));
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="cp-events-header">
        <h3 class="cp-events-title"><?php esc_html_e('Upcoming Events', 'campaign-office'); ?></h3>
        <a href="#" class="cp-view-all-link"><?php esc_html_e('View Calendar', 'campaign-office'); ?> &rarr;</a>
    </div>

    <div class="cp-events-list">
        <?php foreach ($events as $index => $event): ?>
            <div class="cp-event-card">
                <div class="cp-event-date-box">
                    <span class="cp-event-month"><?php echo date('M', strtotime($event['date'])); ?></span>
                    <span class="cp-event-day"><?php echo date('d', strtotime($event['date'])); ?></span>
                </div>
                <div class="cp-event-details">
                    <h4 class="cp-event-name"><?php echo esc_html($event['title']); ?></h4>
                    <p class="cp-event-meta">
                        <span class="dashicons dashicons-clock"></span> <?php echo esc_html($event['time']); ?>
                        <span class="dashicons dashicons-location"></span> <?php echo esc_html($event['location']); ?>
                    </p>
                    
                    <?php if ($rsvp_enabled): ?>
                        <div class="cp-rsvp-section">
                            <div class="cp-rsvp-count">
                                <span class="dashicons dashicons-groups"></span> <?php echo esc_html($event['rsvp_count']); ?> <?php esc_html_e('Attending', 'campaign-office'); ?>
                            </div>
                            <button class="cp-rsvp-btn" data-event-id="<?php echo $index; ?>">
                                <?php esc_html_e('RSVP Now', 'campaign-office'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($show_map): ?>
        <div class="cp-events-map-placeholder">
            <span class="dashicons dashicons-location-alt"></span>
            <p><?php esc_html_e('Interactive Map Loading...', 'campaign-office'); ?></p>
        </div>
    <?php endif; ?>
</div>
