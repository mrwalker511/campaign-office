<?php
/**
 * Heroicon Block Render
 *
 * @package CampaignPress
 * @since 2.1.0
 */

$icon = $attributes['icon'] ?? 'star';
$icon_style = $attributes['iconStyle'] ?? 'outline';
$icon_size = $attributes['iconSize'] ?? 'md';
$custom_size = $attributes['customSize'] ?? 24;
$icon_color = $attributes['iconColor'] ?? '';
$link_url = $attributes['linkUrl'] ?? '';
$link_target = $attributes['linkTarget'] ?? '_self';
$aria_label = $attributes['ariaLabel'] ?? '';
$class_name = $attributes['className'] ?? '';

// Build wrapper classes
$wrapper_classes = array('wp-block-campaignpress-icon');
if (!empty($class_name)) {
	$wrapper_classes[] = $class_name;
}

// Prepare icon arguments
$icon_args = array(
	'class' => 'heroicon-' . $icon_size,
);

// Add custom size if specified
if ($icon_size === 'custom' && $custom_size) {
	$icon_args['width'] = $custom_size;
	$icon_args['height'] = $custom_size;
}

// Add aria-label if specified
if (!empty($aria_label)) {
	$icon_args['aria-label'] = $aria_label;
} else {
	$icon_args['aria-hidden'] = 'true';
}

// Add inline color style if specified
$inline_style = '';
if (!empty($icon_color)) {
	$inline_style = sprintf(' style="color: %s;"', esc_attr($icon_color));
}

$wrapper_attributes = get_block_wrapper_attributes(array(
	'class' => implode(' ', $wrapper_classes),
	'style' => !empty($icon_color) ? 'color: ' . esc_attr($icon_color) . ';' : '',
));

// Get the icon SVG
$icon_svg = campaignpress_get_heroicon($icon, $icon_style, $icon_args);

// If no icon found, show placeholder
if (empty($icon_svg)) {
	$icon_svg = campaignpress_get_heroicon('question-mark-circle', 'outline', $icon_args);
}
?>

<div <?php echo wp_kses_data($wrapper_attributes); ?>>
	<?php if (!empty($link_url)) : ?>
		<a href="<?php echo esc_url($link_url); ?>" target="<?php echo esc_attr($link_target); ?>" rel="noopener">
			<?php echo wp_kses($icon_svg, campaignpress_get_allowed_svg_tags()); ?>
		</a>
	<?php else : ?>
		<?php echo wp_kses($icon_svg, campaignpress_get_allowed_svg_tags()); ?>
	<?php endif; ?>
</div>
