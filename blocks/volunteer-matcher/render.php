<?php
/**
 * Render for Volunteer Matcher Block
 */
$attributes = $attributes ?? [];
$roles = $attributes['roles'] ?? [];
$show_radius = $attributes['showRadius'] ?? true;

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-volunteer-matcher'
));
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="cp-matcher-header">
        <h3><?php esc_html_e('Find Your Role', 'campaign-office'); ?></h3>
        <p><?php esc_html_e('Select your skills to see where you fit in.', 'campaign-office'); ?></p>
    </div>

    <div class="cp-matcher-filters">
        <div class="cp-skill-selector">
            <select id="cp-skill-select">
                <option value="all"><?php esc_html_e('All Skills', 'campaign-office'); ?></option>
                <option value="communication"><?php esc_html_e('Communication', 'campaign-office'); ?></option>
                <option value="fitness"><?php esc_html_e('Physical Activity', 'campaign-office'); ?></option>
                <option value="typing"><?php esc_html_e('Computer Skills', 'campaign-office'); ?></option>
            </select>
        </div>
        
        <?php if ($show_radius): ?>
            <div class="cp-radius-filter">
                <span class="dashicons dashicons-location"></span>
                <input type="range" min="5" max="50" step="5" value="10" id="cp-radius-range">
                <span id="cp-radius-value">10 mi</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="cp-roles-grid">
        <?php foreach ($roles as $role): ?>
            <div class="cp-role-card" data-skills="<?php echo esc_attr(implode(' ', $role['skills'])); ?>">
                <h4 class="cp-role-title"><?php echo esc_html($role['title']); ?></h4>
                <div class="cp-role-tags">
                    <?php foreach ($role['skills'] as $skill): ?>
                        <span class="cp-skill-tag"><?php echo esc_html($skill); ?></span>
                    <?php endforeach; ?>
                </div>
                <div class="cp-role-capacity">
                    <div class="cp-capacity-bar">
                        <div class="cp-capacity-fill" style="width: <?php echo ($role['filled'] / $role['capacity']) * 100; ?>%"></div>
                    </div>
                    <span class="cp-capacity-text"><?php echo esc_html($role['filled']); ?>/<?php echo esc_html($role['capacity']); ?> <?php esc_html_e('Volunteers', 'campaign-office'); ?></span>
                </div>
                <button class="cp-apply-btn"><?php esc_html_e('Apply Now', 'campaign-office'); ?></button>
            </div>
        <?php endforeach; ?>
    </div>
</div>
