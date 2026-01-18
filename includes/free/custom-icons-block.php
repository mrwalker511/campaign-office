<?php
/**
 * Custom Icons Gutenberg Block
 *
 * Block for inserting custom campaign icons
 *
 * @package CampaignPress
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Icons Block
 */
function campaignpress_register_custom_icons_block() {
	// Check if block editor is available
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// Enqueue block assets
	wp_register_script(
		'campaignpress-custom-icons-block',
		get_template_directory_uri() . '/assets/js/custom-icons-block.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
		CAMPAIGNPRESS_VERSION
	);

	wp_register_style(
		'campaignpress-custom-icons-block-editor',
		get_template_directory_uri() . '/assets/css/custom-icons-block.css',
		array( 'wp-edit-blocks' ),
		CAMPAIGNPRESS_VERSION
	);

	wp_register_style(
		'campaignpress-custom-icons-block',
		get_template_directory_uri() . '/assets/css/custom-icons-block.css',
		array(),
		CAMPAIGNPRESS_VERSION
	);

	register_block_type( 'campaignpress/custom-icon', array(
		'editor_script'   => 'campaignpress-custom-icons-block',
		'editor_style'   => 'campaignpress-custom-icons-block-editor',
		'style'          => 'campaignpress-custom-icons-block',
		'render_callback' => 'campaignpress_render_custom_icon_block',
		'attributes'      => array(
			'iconName' => array(
				'type'    => 'string',
				'default' => '',
			),
			'size' => array(
				'type'    => 'string',
				'default' => 'md',
			),
			'className' => array(
				'type'    => 'string',
				'default' => '',
			),
			'ariaLabel' => array(
				'type'    => 'string',
				'default' => '',
			),
		),
		'category'        => 'campaignpress',
		'icon'           => 'star',
		'keywords'       => array( 'icon', 'custom', 'campaign', 'svg' ),
		'description'    => __( 'Insert custom campaign icons', 'campaignpress' ),
	) );
}
add_action( 'init', 'campaignpress_register_custom_icons_block' );

/**
 * Render Custom Icon Block
 *
 * @param array $attributes Block attributes.
 * @return string Block HTML output.
 */
function campaignpress_render_custom_icon_block( $attributes ) {
	$icon_name  = isset( $attributes['iconName'] ) ? $attributes['iconName'] : '';
	$size       = isset( $attributes['size'] ) ? $attributes['size'] : 'md';
	$class_name = isset( $attributes['className'] ) ? $attributes['className'] : '';
	$aria_label = isset( $attributes['ariaLabel'] ) ? $attributes['ariaLabel'] : '';

	if ( empty( $icon_name ) ) {
		return '<div class="wp-block-campaignpress-custom-icon"><p>' . __( 'Please select an icon', 'campaignpress' ) . '</p></div>';
	}

	$args = array();
	if ( ! empty( $class_name ) ) {
		$args['class'] = $class_name;
	}
	if ( ! empty( $aria_label ) ) {
		$args['aria-label'] = $aria_label;
	}

	// Add size class
	$size_classes = array(
		'sm' => 'custom-icon-sm',
		'md' => 'custom-icon-md',
		'lg' => 'custom-icon-lg',
		'xl' => 'custom-icon-xl',
	);

	if ( isset( $size_classes[ $size ] ) ) {
		if ( empty( $args['class'] ) ) {
			$args['class'] = $size_classes[ $size ];
		} else {
			$args['class'] .= ' ' . $size_classes[ $size ];
		}
	}

	$icon_html = campaignpress_get_custom_icon( $icon_name, $args );

	if ( empty( $icon_html ) ) {
		return '<div class="wp-block-campaignpress-custom-icon"><p>' . sprintf( __( 'Icon "%s" not found', 'campaignpress' ), esc_html( $icon_name ) ) . '</p></div>';
	}

	return sprintf(
		'<div class="wp-block-campaignpress-custom-icon">%s</div>',
		$icon_html
	);
}

/**
 * Enqueue block assets
 */
function campaignpress_custom_icons_block_assets() {
	if ( is_admin() ) {
		wp_enqueue_script( 'campaignpress-custom-icons-block' );
	}
}
add_action( 'enqueue_block_assets', 'campaignpress_custom_icons_block_assets' );