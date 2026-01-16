<?php
/**
 * Heroicons Helper Functions
 *
 * Functions for rendering Heroicons SVG icons in the theme.
 *
 * @package CampaignPress
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Heroicon SVG content
 *
 * @param string $icon Icon name (e.g., 'calendar', 'map-pin', 'envelope').
 * @param string $style Icon style: 'outline', 'solid', 'mini', or 'micro'. Default 'outline'.
 * @param array  $args Additional arguments for the icon.
 * @return string SVG markup or empty string if icon not found.
 */
function campaignpress_get_heroicon( $icon, $style = 'outline', $args = array() ) {
	$defaults = array(
		'class'       => '',
		'aria-hidden' => 'true',
		'aria-label'  => '',
		'width'       => null,
		'height'      => null,
	);

	$args = wp_parse_args( $args, $defaults );

	// Map style to directory structure
	$size_map = array(
		'outline' => '24/outline',
		'solid'   => '24/solid',
		'mini'    => '20/solid',
		'micro'   => '16/solid',
	);

	if ( ! isset( $size_map[ $style ] ) ) {
		$style = 'outline';
	}

	$icon_path = get_template_directory() . '/assets/icons/' . $size_map[ $style ] . '/' . $icon . '.svg';

	if ( ! file_exists( $icon_path ) ) {
		return '';
	}

	$svg = file_get_contents( $icon_path );

	if ( ! $svg ) {
		return '';
	}

	// Add classes
	if ( ! empty( $args['class'] ) ) {
		$classes = 'heroicon heroicon-' . esc_attr( $style ) . ' ' . esc_attr( $args['class'] );
	} else {
		$classes = 'heroicon heroicon-' . esc_attr( $style );
	}

	// Add width and height attributes if specified
	$size_attrs = '';
	if ( $args['width'] ) {
		$size_attrs .= ' width="' . esc_attr( $args['width'] ) . '"';
	}
	if ( $args['height'] ) {
		$size_attrs .= ' height="' . esc_attr( $args['height'] ) . '"';
	}

	// Add aria attributes
	$aria_attrs = '';
	if ( ! empty( $args['aria-label'] ) ) {
		$aria_attrs .= ' aria-label="' . esc_attr( $args['aria-label'] ) . '"';
		$aria_attrs .= ' role="img"';
	} elseif ( $args['aria-hidden'] ) {
		$aria_attrs .= ' aria-hidden="true"';
	}

	// Replace the opening SVG tag with our customized version
	$svg = preg_replace(
		'/<svg([^>]*)>/',
		'<svg$1 class="' . $classes . '"' . $size_attrs . $aria_attrs . '>',
		$svg,
		1
	);

	return $svg;
}

/**
 * Echo Heroicon SVG
 *
 * @param string $icon Icon name.
 * @param string $style Icon style: 'outline', 'solid', 'mini', or 'micro'. Default 'outline'.
 * @param array  $args Additional arguments.
 */
function campaignpress_heroicon( $icon, $style = 'outline', $args = array() ) {
	echo wp_kses( campaignpress_get_heroicon( $icon, $style, $args ), campaignpress_get_allowed_svg_tags() );
}

/**
 * Get social media Heroicons
 *
 * Uses custom social media icons from assets/icons/social/.
 *
 * @param string $network Social network name (facebook, twitter, instagram, etc.).
 * @param array  $args Additional arguments.
 * @return string SVG markup.
 */
function campaignpress_get_social_heroicon( $network, $args = array() ) {
	$defaults = array(
		'class'       => '',
		'aria-hidden' => 'true',
		'aria-label'  => '',
		'width'       => '24',
		'height'      => '24',
	);

	$args = wp_parse_args( $args, $defaults );

	// Check for custom social media icon
	$icon_path = get_template_directory() . '/assets/icons/social/' . $network . '.svg';

	if ( ! file_exists( $icon_path ) ) {
		// Fallback to standard Heroicons for non-social icons
		$fallback_icons = array(
			'email' => 'envelope',
			'link'  => 'link',
			'rss'   => 'rss',
		);

		if ( isset( $fallback_icons[ $network ] ) ) {
			return campaignpress_get_heroicon( $fallback_icons[ $network ], 'outline', $args );
		}

		return '';
	}

	$svg = file_get_contents( $icon_path );

	if ( ! $svg ) {
		return '';
	}

	// Add classes
	if ( ! empty( $args['class'] ) ) {
		$classes = 'heroicon heroicon-social heroicon-' . esc_attr( $network ) . ' ' . esc_attr( $args['class'] );
	} else {
		$classes = 'heroicon heroicon-social heroicon-' . esc_attr( $network );
	}

	// Add size attributes
	$size_attrs = '';
	if ( $args['width'] ) {
		$size_attrs .= ' width="' . esc_attr( $args['width'] ) . '"';
	}
	if ( $args['height'] ) {
		$size_attrs .= ' height="' . esc_attr( $args['height'] ) . '"';
	}

	// Add aria attributes
	$aria_attrs = '';
	if ( ! empty( $args['aria-label'] ) ) {
		$aria_attrs .= ' aria-label="' . esc_attr( $args['aria-label'] ) . '"';
		$aria_attrs .= ' role="img"';
	} elseif ( $args['aria-hidden'] ) {
		$aria_attrs .= ' aria-hidden="true"';
	}

	// Replace the opening SVG tag with our customized version
	$svg = preg_replace(
		'/<svg([^>]*)>/',
		'<svg$1 class="' . $classes . '"' . $size_attrs . $aria_attrs . '>',
		$svg,
		1
	);

	return $svg;
}

/**
 * Get status badge with Heroicon
 *
 * @param string $status Status type (success, warning, danger, info).
 * @param string $text Badge text.
 * @param string $icon Optional icon name.
 * @return string Badge HTML markup.
 */
function campaignpress_get_status_badge( $status, $text, $icon = '' ) {
	$valid_statuses = array( 'success', 'warning', 'danger', 'info', 'active', 'pending', 'completed', 'cancelled', 'new', 'contacted' );

	if ( ! in_array( $status, $valid_statuses, true ) ) {
		$status = 'info';
	}

	// Default icons for status types
	$default_icons = array(
		'success'   => 'check-circle',
		'warning'   => 'exclamation-triangle',
		'danger'    => 'x-circle',
		'info'      => 'information-circle',
		'active'    => 'check-circle',
		'pending'   => 'clock',
		'completed' => 'check-badge',
		'cancelled' => 'x-circle',
		'new'       => 'sparkles',
		'contacted' => 'chat-bubble-left',
	);

	if ( empty( $icon ) && isset( $default_icons[ $status ] ) ) {
		$icon = $default_icons[ $status ];
	}

	$badge_html = '<span class="cp-badge cp-badge-' . esc_attr( $status ) . '">';

	if ( ! empty( $icon ) ) {
		$badge_html .= campaignpress_get_heroicon(
			$icon,
			'mini',
			array(
				'class'       => 'cp-badge-icon',
				'aria-hidden' => 'true',
			)
		);
	}

	$badge_html .= '<span class="cp-badge-text">' . esc_html( $text ) . '</span>';
	$badge_html .= '</span>';

	return $badge_html;
}

/**
 * Echo status badge with Heroicon
 *
 * @param string $status Status type.
 * @param string $text Badge text.
 * @param string $icon Optional icon name.
 */
function campaignpress_status_badge( $status, $text, $icon = '' ) {
	$allowed_tags = array_merge(
		campaignpress_get_allowed_svg_tags(),
		array(
			'span' => array(
				'class' => true,
			),
		)
	);
	echo wp_kses( campaignpress_get_status_badge( $status, $text, $icon ), $allowed_tags );
}

/**
 * Get common UI icons
 *
 * Helper function to get commonly used UI icons with consistent styling.
 *
 * @param string $type Icon type (calendar, location, download, upload, etc.).
 * @param array  $args Additional arguments.
 * @return string SVG markup.
 */
function campaignpress_get_ui_icon( $type, $args = array() ) {
	$ui_icons = array(
		'calendar'   => 'calendar',
		'clock'      => 'clock',
		'location'   => 'map-pin',
		'map'        => 'map',
		'download'   => 'arrow-down-tray',
		'upload'     => 'arrow-up-tray',
		'edit'       => 'pencil',
		'delete'     => 'trash',
		'add'        => 'plus',
		'remove'     => 'minus',
		'search'     => 'magnifying-glass',
		'filter'     => 'funnel',
		'settings'   => 'cog-6-tooth',
		'user'       => 'user',
		'users'      => 'user-group',
		'heart'      => 'heart',
		'star'       => 'star',
		'flag'       => 'flag',
		'bookmark'   => 'bookmark',
		'share'      => 'share',
		'link'       => 'link',
		'phone'      => 'phone',
		'email'      => 'envelope',
		'chat'       => 'chat-bubble-left-right',
		'notification' => 'bell',
		'document'   => 'document',
		'folder'     => 'folder',
		'image'      => 'photo',
		'video'      => 'video-camera',
		'audio'      => 'musical-note',
		'chart'      => 'chart-bar',
		'dashboard'  => 'squares-2x2',
		'menu'       => 'bars-3',
		'close'      => 'x-mark',
		'check'      => 'check',
		'info'       => 'information-circle',
		'warning'    => 'exclamation-triangle',
		'error'      => 'x-circle',
		'success'    => 'check-circle',
		'expand'     => 'chevron-down',
		'collapse'   => 'chevron-up',
		'next'       => 'chevron-right',
		'previous'   => 'chevron-left',
		'refresh'    => 'arrow-path',
		'external'   => 'arrow-top-right-on-square',
	);

	$icon = isset( $ui_icons[ $type ] ) ? $ui_icons[ $type ] : $type;
	$style = isset( $args['style'] ) ? $args['style'] : 'outline';

	return campaignpress_get_heroicon( $icon, $style, $args );
}

/**
 * Echo UI icon
 *
 * @param string $type Icon type.
 * @param array  $args Additional arguments.
 */
function campaignpress_ui_icon( $type, $args = array() ) {
	echo wp_kses( campaignpress_get_ui_icon( $type, $args ), campaignpress_get_allowed_svg_tags() );
}

/**
 * Get allowed SVG tags for wp_kses() escaping
 *
 * Returns an array of allowed HTML tags and attributes for SVG elements.
 * Used for safely escaping SVG output from Heroicons.
 *
 * @return array Allowed SVG tags and attributes.
 */
function campaignpress_get_allowed_svg_tags() {
	return array(
		'svg'      => array(
			'class'           => true,
			'aria-hidden'     => true,
			'aria-label'      => true,
			'role'            => true,
			'xmlns'           => true,
			'width'           => true,
			'height'          => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'data-slot'       => true,
		),
		'path'     => array(
			'd'               => true,
			'fill'            => true,
			'fill-rule'       => true,
			'clip-rule'       => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		),
		'circle'   => array(
			'cx'              => true,
			'cy'              => true,
			'r'               => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
		),
		'ellipse'  => array(
			'cx'              => true,
			'cy'              => true,
			'rx'              => true,
			'ry'              => true,
			'fill'            => true,
			'stroke'          => true,
		),
		'line'     => array(
			'x1'              => true,
			'y1'              => true,
			'x2'              => true,
			'y2'              => true,
			'stroke'          => true,
			'stroke-width'    => true,
		),
		'polygon'  => array(
			'points'          => true,
			'fill'            => true,
			'stroke'          => true,
		),
		'polyline' => array(
			'points'          => true,
			'fill'            => true,
			'stroke'          => true,
		),
		'rect'     => array(
			'x'               => true,
			'y'               => true,
			'width'           => true,
			'height'          => true,
			'rx'              => true,
			'ry'              => true,
			'fill'            => true,
			'stroke'          => true,
		),
		'g'        => array(
			'fill'            => true,
			'stroke'          => true,
			'transform'       => true,
		),
		'defs'     => array(),
		'clippath' => array(
			'id'              => true,
		),
		'use'      => array(
			'href'            => true,
			'xlink:href'      => true,
		),
	);
}
