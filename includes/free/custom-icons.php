<?php
/**
 * Custom Icons Integration
 *
 * Helper functions for custom campaign icons
 *
 * @package CampaignPress
 * @since 2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get Custom Campaign Icon
 *
 * @param string $icon Icon filename (without .svg extension).
 * @param array  $args Additional arguments for the icon.
 * @return string SVG markup or empty string if icon not found.
 */
function campaignpress_get_custom_icon( $icon, $args = array() ) {
    $defaults = array(
        'class'       => '',
        'aria-hidden' => 'true',
        'aria-label'  => '',
        'width'       => null,
        'height'      => null,
    );

    $args = wp_parse_args( $args, $defaults );

    // Check for custom icon in /assets/icons/custom/
    $icon_path = get_template_directory() . '/assets/icons/custom/' . $icon . '.svg';

    if ( ! file_exists( $icon_path ) ) {
        return '';
    }

    $svg = file_get_contents( $icon_path );

    if ( ! $svg ) {
        return '';
    }

    // Add classes
    if ( ! empty( $args['class'] ) ) {
        $classes = 'custom-icon ' . esc_attr( $args['class'] );
    } else {
        $classes = 'custom-icon';
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
 * Echo Custom Icon
 *
 * @param string $icon Icon filename.
 * @param array  $args Additional arguments.
 */
function campaignpress_custom_icon( $icon, $args = array() ) {
    echo wp_kses( campaignpress_get_custom_icon( $icon, $args ), campaignpress_get_allowed_svg_tags() );
}

/**
 * Get all available custom icons
 *
 * @return array Array of custom icon filenames.
 */
function campaignpress_get_custom_icons() {
    $icons = array();
    $icons_dir = get_template_directory() . '/assets/icons/custom/';

    if ( is_dir( $icons_dir ) ) {
        $files = glob( $icons_dir . '*.svg' );
        foreach ( $files as $file ) {
            $icon_name = basename( $file, '.svg' );
            $icons[] = $icon_name;
        }
        sort( $icons );
    }

    return $icons;
}

/**
 * Add custom icons to the icon browser
 *
 * This function can be used to extend the existing icon browser
 * with your custom icons.
 *
 * @param array $existing_categories Existing icon categories.
 * @return array Extended categories with custom icons.
 */
function campaignpress_add_custom_icons_to_browser( $existing_categories ) {
    // Add custom campaign icons category
    $existing_categories['custom-campaign'] = array(
        'name'  => __( 'Custom Campaign Icons', 'campaignpress' ),
        'icons' => campaignpress_get_custom_icons(),
    );

    return $existing_categories;
}
add_filter( 'cp_icons_browser_categories', 'campaignpress_add_custom_icons_to_browser' );

/**
 * Register custom icon styles
 *
 * Add CSS for your custom icons.
 */
function campaignpress_custom_icons_styles() {
    ?>
    <style>
    .custom-icon {
        display: inline-block;
        vertical-align: middle;
        fill: currentColor;
        flex-shrink: 0;
    }

    /* Size variants for custom icons */
    .custom-icon-sm {
        width: 16px;
        height: 16px;
    }

    .custom-icon-md {
        width: 24px;
        height: 24px;
    }

    .custom-icon-lg {
        width: 32px;
        height: 32px;
    }

    .custom-icon-xl {
        width: 48px;
        height: 48px;
    }

    /* Icon in buttons */
    .btn .custom-icon,
    .button .custom-icon {
        margin-right: 0.5rem;
    }

    .btn .custom-icon:last-child,
    .button .custom-icon:last-child {
        margin-right: 0;
        margin-left: 0.5rem;
    }

    /* Icon grid for custom icons */
    .custom-icon-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 1rem;
        padding: 1rem;
    }

    .custom-icon-grid-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        padding: 1rem;
        border: 1px solid #e5e5e5;
        border-radius: 0.5rem;
        text-align: center;
        transition: all 0.2s ease;
    }

    .custom-icon-grid-item:hover {
        background: #f9f9f9;
        border-color: #ccc;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .custom-icon-grid-item .custom-icon {
        width: 32px;
        height: 32px;
    }

    .custom-icon-grid-item span {
        font-size: 0.75rem;
        color: #666;
    }
    </style>
    <?php
}
add_action( 'wp_head', 'campaignpress_custom_icons_styles' );
add_action( 'admin_enqueue_scripts', 'campaignpress_custom_icons_admin_scripts' );

/**
 * Enqueue admin scripts for custom icons
 */
function campaignpress_custom_icons_admin_scripts( $hook ) {
    if ( 'appearance_page_custom-icons' !== $hook ) {
        return;
    }

    wp_localize_script( 'wp-api', 'cpIconsBrowser', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'cp_icons_browser' ),
    ) );
}

add_action( 'wp_ajax_campaignpress_get_custom_icons', 'campaignpress_ajax_get_custom_icons' );
add_action( 'wp_ajax_nopriv_campaignpress_get_custom_icons', 'campaignpress_ajax_get_custom_icons' );

/**
 * AJAX handler to get custom icons
 */
function campaignpress_ajax_get_custom_icons() {
    // Verify nonce
    $nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( $_REQUEST['nonce'] ) : '';
    if ( ! wp_verify_nonce( $nonce, 'cp_icons_browser' ) ) {
        wp_send_json_error( __( 'Security check failed', 'campaignpress' ) );
    }

    $custom_icons = campaignpress_get_custom_icons();
    wp_send_json_success( $custom_icons );
}

/**
 * Add custom icons admin page
 */
function campaignpress_add_custom_icons_admin_menu() {
    add_submenu_page(
        'themes.php',
        __( 'Custom Icons', 'campaignpress' ),
        __( 'Custom Icons', 'campaignpress' ),
        'edit_theme_options',
        'custom-icons',
        'campaignpress_custom_icons_admin_page'
    );
}
add_action( 'admin_menu', 'campaignpress_add_custom_icons_admin_menu' );

/**
 * Render custom icons admin page
 */
function campaignpress_custom_icons_admin_page() {
    $custom_icons = campaignpress_get_custom_icons();
    ?>
    <div class="wrap">
        <h1><?php _e( 'Custom Campaign Icons', 'campaignpress' ); ?></h1>
        <p class="description">
            <?php _e( 'Your custom campaign icons are ready to use! Click any icon to copy its function call.', 'campaignpress' ); ?>
        </p>

        <?php if ( empty( $custom_icons ) ) : ?>
            <div class="notice notice-info">
                <p>
                    <?php
                    printf(
                        __( 'No custom icons found. Add your SVG icons to the <code>%s</code> directory.', 'campaignpress' ),
                        '/assets/icons/custom/'
                    );
                    ?>
                </p>
            </div>
        <?php else : ?>
            <div class="custom-icon-grid">
                <?php foreach ( $custom_icons as $icon ) : ?>
                    <div class="custom-icon-grid-item" data-icon="<?php echo esc_attr( $icon ); ?>">
                        <?php campaignpress_custom_icon( $icon, array( 'class' => 'custom-icon-md' ) ); ?>
                        <span><?php echo esc_html( $icon ); ?></span>
                        <button class="button" onclick="copyIconCode('<?php echo esc_js( $icon ); ?>')">
                            <?php _e( 'Copy Code', 'campaignpress' ); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="custom-icons-usage">
                <h2><?php _e( 'Usage Examples', 'campaignpress' ); ?></h2>
                <div class="cp-usage-examples">
                    <div class="cp-usage-example">
                        <h3><?php _e( 'In PHP Templates', 'campaignpress' ); ?></h3>
                        <code>&lt;?php campaignpress_custom_icon( '<?php echo esc_js( $custom_icons[0] ?? 'your-icon' ); ?>', array( 'class' => 'custom-icon-lg' ) ); ?&gt;</code>
                    </div>
                    <div class="cp-usage-example">
                        <h3><?php _e( 'With Custom Size', 'campaignpress' ); ?></h3>
                        <code>&lt;?php echo campaignpress_get_custom_icon( '<?php echo esc_js( $custom_icons[0] ?? 'your-icon' ); ?>', array( 'width' => '32', 'height' => '32' ) ); ?&gt;</code>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function copyIconCode(iconName) {
        // Construct the PHP string to be copied
        const code = '<?php echo "<?php campaignpress_custom_icon( \'"; ?>' + iconName + '<?php echo "\', array( \'class\' => \'custom-icon-md\' ) ); ?>"; ?>';
        
        navigator.clipboard.writeText(code).then(function() {
            alert('<?php _e( 'Code copied to clipboard!', 'campaignpress' ); ?>');
        });
    }
    </script>
    <?php
} // <--- THIS is the closing bracket you were missing!