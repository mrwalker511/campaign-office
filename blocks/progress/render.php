<?php
/**
 * Render for Progress Block
 */
$attributes = $attributes ?? [];
$title = $attributes['title'] ?? 'Campaign Goal';
$goal = floatval($attributes['goalAmount'] ?? 10000);
$raised = floatval($attributes['raisedAmount'] ?? 0);
$show_percentage = $attributes['showPercentage'] ?? true;

$percentage = $goal > 0 ? min(100, ($raised / $goal) * 100) : 0;
$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'campaignpress-progress'
));
?>
<div <?php echo $wrapper_attributes; ?>>
    <?php if ($title): ?>
        <h3 class="campaignpress-progress-title"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="campaignpress-progress-stats">
        <span class="campaignpress-progress-raised">$<?php echo number_format($raised); ?></span>
        <span class="campaignpress-progress-goal"><?php printf(esc_html__('Goal: $%s', 'campaign-office'), number_format($goal)); ?></span>
    </div>

    <div class="campaignpress-progress-bar-container">
        <div class="campaignpress-progress-bar" style="width: 0%" data-percentage="<?php echo esc_attr($percentage); ?>">
            <?php if ($show_percentage): ?>
                <span class="campaignpress-progress-percentage">0%</span>
            <?php endif; ?>
        </div>
    </div>
</div>
