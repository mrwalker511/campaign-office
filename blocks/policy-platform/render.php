<?php
/**
 * Render for Policy Platform Block
 */
$attributes = $attributes ?? [];
$policies = $attributes['policies'] ?? [];

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-policy-platform'
));
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="cp-policy-list">
        <?php foreach ($policies as $index => $policy): ?>
            <div class="cp-policy-item">
                <button class="cp-policy-header" aria-expanded="false" aria-controls="cp-policy-content-<?php echo $index; ?>">
                    <span class="cp-policy-title"><?php echo esc_html($policy['title']); ?></span>
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </button>
                <div id="cp-policy-content-<?php echo $index; ?>" class="cp-policy-content" hidden>
                    <div class="cp-policy-body">
                        <p class="cp-policy-summary"><?php echo esc_html($policy['summary']); ?></p>
                        <div class="cp-policy-full-text">
                            <?php echo wp_kses_post($policy['content']); ?>
                        </div>
                        
                        <div class="cp-policy-actions">
                            <button class="cp-vote-btn" data-policy-id="<?php echo $index; ?>">
                                <span class="dashicons dashicons-thumbs-up"></span> 
                                <span class="cp-vote-count"><?php echo esc_html($policy['votes']); ?></span> <?php esc_html_e('Support this', 'campaign-office'); ?>
                            </button>
                            <button class="cp-download-btn">
                                <span class="dashicons dashicons-pdf"></span> <?php esc_html_e('Download PDF', 'campaign-office'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
