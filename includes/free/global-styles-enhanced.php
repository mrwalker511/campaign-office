<?php
/**
 * Enhanced Global Styles
 *
 * Global design system settings that apply across the entire website.
 * Works with Design Studio to provide consistent styling.
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Global_Styles_Enhanced
 */
class CP_Global_Styles_Enhanced {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'output_global_styles'), 100);
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cp-design-studio',
            __('Global Styles', 'campaign-office'),
            __('Global Styles', 'campaign-office'),
            'edit_pages',
            'cp-global-styles-enhanced',
            array($this, 'render_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Typography
        register_setting('cp_global_styles', 'cp_global_typography', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_typography'),
            'default' => array(
                'heading_font' => 'Inter',
                'body_font' => 'Inter',
            )
        ));

        // Colors
        register_setting('cp_global_styles', 'cp_global_colors', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_colors'),
            'default' => array(
                'primary' => '#0073aa',
                'secondary' => '#005a87',
                'accent' => '#d63638',
            )
        ));

        // Spacing
        register_setting('cp_global_styles', 'cp_global_spacing', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_spacing'),
            'default' => array(
                'container_width' => 1200,
                'section_padding' => 'standard',
            )
        ));
    }

    /**
     * Render global styles page
     */
    public function render_page() {
        // Check for form submission
        if (isset($_POST['cp_global_styles_submit']) &&
            check_admin_referer('cp_global_styles', 'cp_global_styles_nonce')) {

            // Save settings
            $typography = array(
                'heading_font' => sanitize_text_field($_POST['heading_font'] ?? 'Inter'),
                'body_font' => sanitize_text_field($_POST['body_font'] ?? 'Inter'),
            );

            $colors = array(
                'primary' => sanitize_hex_color($_POST['primary_color'] ?? '#0073aa'),
                'secondary' => sanitize_hex_color($_POST['secondary_color'] ?? '#005a87'),
                'accent' => sanitize_hex_color($_POST['accent_color'] ?? '#d63638'),
            );

            $spacing = array(
                'container_width' => absint($_POST['container_width'] ?? 1200),
                'section_padding' => sanitize_text_field($_POST['section_padding'] ?? 'standard'),
            );

            update_option('cp_global_typography', $typography);
            update_option('cp_global_colors', $colors);
            update_option('cp_global_spacing', $spacing);

            echo '<div class="notice notice-success is-dismissible"><p>' .
                 esc_html__('Global styles saved successfully!', 'campaign-office') . '</p></div>';
        }

        // Get current settings
        $typography = get_option('cp_global_typography', array(
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
        ));

        $colors = get_option('cp_global_colors', array(
            'primary' => '#0073aa',
            'secondary' => '#005a87',
            'accent' => '#d63638',
        ));

        $spacing = get_option('cp_global_spacing', array(
            'container_width' => 1200,
            'section_padding' => 'standard',
        ));
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Global Styles', 'campaign-office'); ?></h1>
            <p class="description"><?php esc_html_e('Define global design settings that apply across your entire website. These settings are used as defaults unless overridden on individual pages.', 'campaign-office'); ?></p>

            <form method="post" style="max-width: 800px; margin-top: 2rem;">
                <?php wp_nonce_field('cp_global_styles', 'cp_global_styles_nonce'); ?>

                <div class="card" style="margin-bottom: 2rem;">
                    <h2><?php esc_html_e('Typography', 'campaign-office'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e('Heading Font', 'campaign-office'); ?></th>
                            <td>
                                <select name="heading_font" class="regular-text">
                                    <option value="Inter" <?php selected($typography['heading_font'], 'Inter'); ?>>Inter</option>
                                    <option value="Roboto" <?php selected($typography['heading_font'], 'Roboto'); ?>>Roboto</option>
                                    <option value="Poppins" <?php selected($typography['heading_font'], 'Poppins'); ?>>Poppins</option>
                                    <option value="Montserrat" <?php selected($typography['heading_font'], 'Montserrat'); ?>>Montserrat</option>
                                    <option value="Open Sans" <?php selected($typography['heading_font'], 'Open Sans'); ?>>Open Sans</option>
                                </select>
                                <p class="description"><?php esc_html_e('Font used for all headings (H1-H6)', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Body Font', 'campaign-office'); ?></th>
                            <td>
                                <select name="body_font" class="regular-text">
                                    <option value="Inter" <?php selected($typography['body_font'], 'Inter'); ?>>Inter</option>
                                    <option value="Open Sans" <?php selected($typography['body_font'], 'Open Sans'); ?>>Open Sans</option>
                                    <option value="Lato" <?php selected($typography['body_font'], 'Lato'); ?>>Lato</option>
                                    <option value="Roboto" <?php selected($typography['body_font'], 'Roboto'); ?>>Roboto</option>
                                </select>
                                <p class="description"><?php esc_html_e('Font used for body text and paragraphs', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card" style="margin-bottom: 2rem;">
                    <h2><?php esc_html_e('Color Palette', 'campaign-office'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e('Primary Color', 'campaign-office'); ?></th>
                            <td>
                                <input type="text" name="primary_color" value="<?php echo esc_attr($colors['primary']); ?>" class="cp-global-color-picker" data-default-color="<?php echo esc_attr($colors['primary']); ?>">
                                <p class="description"><?php esc_html_e('Main brand color used for buttons, links, and accents', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Secondary Color', 'campaign-office'); ?></th>
                            <td>
                                <input type="text" name="secondary_color" value="<?php echo esc_attr($colors['secondary']); ?>" class="cp-global-color-picker" data-default-color="<?php echo esc_attr($colors['secondary']); ?>">
                                <p class="description"><?php esc_html_e('Used for hover states and secondary elements', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Accent Color', 'campaign-office'); ?></th>
                            <td>
                                <input type="text" name="accent_color" value="<?php echo esc_attr($colors['accent']); ?>" class="cp-global-color-picker" data-default-color="<?php echo esc_attr($colors['accent']); ?>">
                                <p class="description"><?php esc_html_e('Used for highlights, CTAs, and important elements', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="card" style="margin-bottom: 2rem;">
                    <h2><?php esc_html_e('Layout & Spacing', 'campaign-office'); ?></h2>
                    <table class="form-table">
                        <tr>
                            <th><?php esc_html_e('Container Width', 'campaign-office'); ?></th>
                            <td>
                                <select name="container_width" class="regular-text">
                                    <option value="1140" <?php selected($spacing['container_width'], 1140); ?>><?php esc_html_e('Narrow (1140px)', 'campaign-office'); ?></option>
                                    <option value="1200" <?php selected($spacing['container_width'], 1200); ?>><?php esc_html_e('Standard (1200px)', 'campaign-office'); ?></option>
                                    <option value="1320" <?php selected($spacing['container_width'], 1320); ?>><?php esc_html_e('Wide (1320px)', 'campaign-office'); ?></option>
                                    <option value="1440" <?php selected($spacing['container_width'], 1440); ?>><?php esc_html_e('Extra Wide (1440px)', 'campaign-office'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Maximum width of content containers', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Section Padding', 'campaign-office'); ?></th>
                            <td>
                                <select name="section_padding" class="regular-text">
                                    <option value="compact" <?php selected($spacing['section_padding'], 'compact'); ?>><?php esc_html_e('Compact (3rem)', 'campaign-office'); ?></option>
                                    <option value="standard" <?php selected($spacing['section_padding'], 'standard'); ?>><?php esc_html_e('Standard (4rem)', 'campaign-office'); ?></option>
                                    <option value="spacious" <?php selected($spacing['section_padding'], 'spacious'); ?>><?php esc_html_e('Spacious (6rem)', 'campaign-office'); ?></option>
                                    <option value="extra-spacious" <?php selected($spacing['section_padding'], 'extra-spacious'); ?>><?php esc_html_e('Extra Spacious (8rem)', 'campaign-office'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Padding above and below content sections', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <p class="submit">
                    <button type="submit" name="cp_global_styles_submit" class="button button-primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php esc_html_e('Save Global Styles', 'campaign-office'); ?>
                    </button>
                </p>
            </form>

            <div class="card" style="margin-top: 2rem;">
                <h2><?php esc_html_e('Quick Actions', 'campaign-office'); ?></h2>
                <p>
                    <a href="<?php echo admin_url('customize.php?autofocus[section]=cp_colors'); ?>" class="button">
                        <span class="dashicons dashicons-admin-appearance"></span>
                        <?php esc_html_e('Open WordPress Customizer', 'campaign-office'); ?>
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=cp-color-schemes'); ?>" class="button">
                        <span class="dashicons dashicons-art"></span>
                        <?php esc_html_e('View Color Schemes', 'campaign-office'); ?>
                    </a>
                </p>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Initialize color pickers for global styles
            if ($.fn.wpColorPicker) {
                $('.cp-global-color-picker').wpColorPicker();
            }
        });
        </script>
        <?php
    }

    /**
     * Output global styles to frontend
     */
    public function output_global_styles() {
        // Don't output in admin
        if (is_admin()) {
            return;
        }

        // Only output if we have global styles or page-specific styles
        $typography = get_option('cp_global_typography');
        $colors = get_option('cp_global_colors');
        $spacing = get_option('cp_global_spacing');

        if (!$typography && !$colors && !$spacing) {
            return;
        }

        // Set defaults
        $typography = wp_parse_args($typography, array(
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
        ));

        $colors = wp_parse_args($colors, array(
            'primary' => '#0073aa',
            'secondary' => '#005a87',
            'accent' => '#d63638',
        ));

        $spacing = wp_parse_args($spacing, array(
            'container_width' => 1200,
            'section_padding' => 'standard',
        ));

        // Map padding values to rem
        $padding_map = array(
            'compact' => '3rem',
            'standard' => '4rem',
            'spacious' => '6rem',
            'extra-spacious' => '8rem',
        );
        $section_padding_rem = $padding_map[$spacing['section_padding']] ?? '4rem';

        // Output styles
        ?>
        <style id="cp-global-styles-output">
            :root {
                --cp-heading-font: <?php echo esc_attr($typography['heading_font']); ?>, sans-serif;
                --cp-body-font: <?php echo esc_attr($typography['body_font']); ?>, sans-serif;

                --cp-global-primary: <?php echo esc_attr($colors['primary']); ?>;
                --cp-global-secondary: <?php echo esc_attr($colors['secondary']); ?>;
                --cp-global-accent: <?php echo esc_attr($colors['accent']); ?>;

                --cp-global-container-width: <?php echo esc_attr($spacing['container_width']); ?>px;
                --cp-global-section-padding: <?php echo esc_attr($section_padding_rem); ?>;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: var(--cp-heading-font);
            }

            body {
                font-family: var(--cp-body-font);
            }

            .site-container,
            .container,
            .wp-block-group__inner-container {
                max-width: var(--cp-global-container-width);
                margin-left: auto;
                margin-right: auto;
            }

            .wp-block-button__link,
            .button,
            .btn-primary {
                background-color: var(--cp-global-primary);
                border-color: var(--cp-global-primary);
            }

            .wp-block-button__link:hover,
            .button:hover,
            .btn-primary:hover {
                background-color: var(--cp-global-secondary);
                border-color: var(--cp-global-secondary);
            }

            .cp-section {
                padding: var(--cp-global-section-padding) 1rem;
            }
        </style>
        <?php
    }

    /**
     * Sanitize typography settings
     */
    public function sanitize_typography($input) {
        if (!is_array($input)) {
            return array();
        }

        $valid_fonts = array('Inter', 'Roboto', 'Poppins', 'Montserrat', 'Open Sans', 'Lato');

        return array(
            'heading_font' => in_array($input['heading_font'] ?? '', $valid_fonts) ? $input['heading_font'] : 'Inter',
            'body_font' => in_array($input['body_font'] ?? '', $valid_fonts) ? $input['body_font'] : 'Inter',
        );
    }

    /**
     * Sanitize color settings
     */
    public function sanitize_colors($input) {
        if (!is_array($input)) {
            return array();
        }

        return array(
            'primary' => sanitize_hex_color($input['primary'] ?? '#0073aa'),
            'secondary' => sanitize_hex_color($input['secondary'] ?? '#005a87'),
            'accent' => sanitize_hex_color($input['accent'] ?? '#d63638'),
        );
    }

    /**
     * Sanitize spacing settings
     */
    public function sanitize_spacing($input) {
        if (!is_array($input)) {
            return array();
        }

        $valid_padding = array('compact', 'standard', 'spacious', 'extra-spacious');

        return array(
            'container_width' => max(960, min(1920, absint($input['container_width'] ?? 1200))),
            'section_padding' => in_array($input['section_padding'] ?? '', $valid_padding) ? $input['section_padding'] : 'standard',
        );
    }
}

// Initialize
new CP_Global_Styles_Enhanced();
