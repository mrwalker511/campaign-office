<?php
/**
 * Render for Hero Commander Block
 */
$attributes = $attributes ?? [];
$headline = $attributes['headline'] ?? 'Leading with Integrity';
$typewriter_text = $attributes['typewriterText'] ?? ['Vision', 'Courage', 'Values'];
$bg_image = $attributes['backgroundImage'] ?? '';

// Fallback image if none selected
if (empty($bg_image)) {
    $bg_image = CAMPAIGNPRESS_ASSETS_URI . '/images/patterns/hero-political.jpg'; 
}

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-hero-commander',
    'style' => 'background-image: url(' . esc_url($bg_image) . ')'
));

$json_words = json_encode($typewriter_text);
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="cp-hero-overlay"></div>
    <div class="cp-hero-content">
        <h1 class="cp-hero-headline"><?php echo esc_html($headline); ?></h1>
        <div class="cp-typewriter-container">
            <span class="cp-static-text"><?php esc_html_e('A Future Built On', 'campaign-office'); ?></span>
            <span class="cp-typewriter-dynamic" data-words="<?php echo esc_attr($json_words); ?>"></span>
            <span class="cp-cursor">|</span>
        </div>
        <div class="cp-hero-cta">
             <button class="cp-button is-style-primary"><?php esc_html_e('Join the Movement', 'campaign-office'); ?></button>
        </div>
    </div>
</div>
