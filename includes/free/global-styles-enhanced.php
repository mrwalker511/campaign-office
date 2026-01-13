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
                'heading_font' => __('Inter', 'campaign-office'),
                'body_font' => __('Inter', 'campaign-office'),
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
                'element_spacing' => 'standard',
            )
        ));

        // Version tracking for cache busting
        register_setting('cp_global_styles', 'cp_global_styles_version', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1.0.0',
        ));

        // Global styles enabled toggle
        register_setting('cp_global_styles', 'cp_global_styles_enabled', array(
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ));
    }

    /**
     * Render global styles page
     */
    public function render_page() {
        // Check for form submission
        if (isset($_POST['cp_global_styles_submit'])) {
            // Verify nonce
            if (!isset($_POST['cp_global_styles_nonce']) || 
                !wp_verify_nonce($_POST['cp_global_styles_nonce'], 'cp_global_styles')) {
                echo '<div class="notice notice-error is-dismissible"><p>' .
                     esc_html__('Security verification failed. Please try again.', 'campaign-office') . '</p></div>';
                return;
            }

            // Check user capabilities
            if (!current_user_can('edit_theme_options')) {
                echo '<div class="notice notice-error is-dismissible"><p>' .
                     esc_html__('You do not have permission to modify global styles.', 'campaign-office') . '</p></div>';
                return;
            }

            // Verify all required fields are present
            if (!isset($_POST['heading_font']) || !isset($_POST['body_font']) ||
                !isset($_POST['primary_color']) || !isset($_POST['secondary_color']) ||
                !isset($_POST['accent_color']) || !isset($_POST['container_width']) ||
                !isset($_POST['section_padding']) || !isset($_POST['element_spacing'])) {
                echo '<div class="notice notice-error is-dismissible"><p>' .
                     esc_html__('Missing required fields. Please fill out all settings.', 'campaign-office') . '</p></div>';
                return;
            }

            // Save settings using proper sanitization
            $typography = array(
                'heading_font' => sanitize_text_field(wp_unslash($_POST['heading_font'])),
                'body_font' => sanitize_text_field(wp_unslash($_POST['body_font'])),
            );

            $colors = array(
                'primary' => sanitize_hex_color(wp_unslash($_POST['primary_color'])),
                'secondary' => sanitize_hex_color(wp_unslash($_POST['secondary_color'])),
                'accent' => sanitize_hex_color(wp_unslash($_POST['accent_color'])),
            );

            $spacing = array(
                'container_width' => absint($_POST['container_width']),
                'section_padding' => sanitize_text_field(wp_unslash($_POST['section_padding'])),
                'element_spacing' => sanitize_text_field(wp_unslash($_POST['element_spacing'])),
            );

            // Update options
            $updated = true;
            $updated = $updated && update_option('cp_global_typography', $typography);
            $updated = $updated && update_option('cp_global_colors', $colors);
            $updated = $updated && update_option('cp_global_spacing', $spacing);

            if ($updated) {
                // Clear cache to regenerate CSS
                delete_transient('cp_global_styles_css');
                delete_post_meta_by_key('_cp_page_styles_cache');
                
                // Update version for cache busting
                $this->increment_version();

                echo '<div class="notice notice-success is-dismissible"><p>' .
                     esc_html__('Global styles saved successfully!', 'campaign-office') . '</p></div>';
            } else {
                echo '<div class="notice notice-warning is-dismissible"><p>' .
                     esc_html__('No changes were detected or there was an error saving.', 'campaign-office') . '</p></div>';
            }
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
                                <?php
                                $available_fonts = $this->get_available_fonts();
                                echo $this->render_font_dropdown('heading_font', $typography['heading_font'], $available_fonts);
                                ?>
                                <p class="description"><?php esc_html_e('Font used for all headings (H1-H6)', 'campaign-office'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e('Body Font', 'campaign-office'); ?></th>
                            <td>
                                <?php
                                echo $this->render_font_dropdown('body_font', $typography['body_font'], $available_fonts);
                                ?>
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
                        <tr>
                            <th><?php esc_html_e('Element Spacing', 'campaign-office'); ?></th>
                            <td>
                                <select name="element_spacing" class="regular-text">
                                    <option value="tight" <?php selected($spacing['element_spacing'], 'tight'); ?>><?php esc_html_e('Tight (8px)', 'campaign-office'); ?></option>
                                    <option value="standard" <?php selected($spacing['element_spacing'], 'standard'); ?>><?php esc_html_e('Standard (16px)', 'campaign-office'); ?></option>
                                    <option value="relaxed" <?php selected($spacing['element_spacing'], 'relaxed'); ?>><?php esc_html_e('Relaxed (24px)', 'campaign-office'); ?></option>
                                </select>
                                <p class="description"><?php esc_html_e('Spacing between elements inside sections', 'campaign-office'); ?></p>
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
     * Output global styles to frontend with caching
     */
    public function output_global_styles() {
        // Don't output in admin
        if (is_admin()) {
            return;
        }

        // Only output if we have global styles enabled
        $global_styles_enabled = get_option('cp_global_styles_enabled', true);
        if (!$global_styles_enabled) {
            return;
        }

        // Get current version for cache busting
        $version = get_option('cp_global_styles_version', '1.0');
        
        // Try to get cached CSS
        $cache_key = 'cp_global_styles_css_' . $version;
        $css = get_transient($cache_key);

        // Generate CSS if cache is empty or stale
        if (false === $css) {
            $css = $this->generate_global_styles_css();
            if (!empty($css)) {
                // Cache for 24 hours
                set_transient($cache_key, $css, DAY_IN_SECONDS);
            }
        }

        // Output CSS
        if (!empty($css)) {
            echo '<style id="cp-global-styles-output" type="text/css">' . $css . '</style>';
        }
    }

    /**
     * Generate global styles CSS programmatically
     * 
     * @return string Generated CSS rules
     */
    private function generate_global_styles_css() {
        // Get saved settings
        $typography = get_option('cp_global_typography', array());
        $colors = get_option('cp_global_colors', array());
        $spacing = get_option('cp_global_spacing', array());

        // Merge with defaults from theme.json using the helper
        $typography = wp_parse_args($typography, array(
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
        ));

        $colors = wp_parse_args($colors, array(
            'primary' => CP_Theme_JSON_Helper::get_color('primary', '#0073aa'),
            'secondary' => CP_Theme_JSON_Helper::get_color('primary-700', '#005a87'),
            'accent' => CP_Theme_JSON_Helper::get_color('accent', '#d63638'),
        ));

        $spacing = wp_parse_args($spacing, array(
            'container_width' => 1200,
            'section_padding' => 'standard',
        ));

        // Map semantic spacing values to rem units
        $section_padding_rems = $this->map_padding_to_rems($spacing['section_padding']);
        $element_spacing_rems = $this->map_element_spacing_to_rems($spacing['element_spacing'] ?? 'standard');

        // Generate CSS using theme's variables
        $css_rules = array(
            ':root' => array(
                '--cp-global-primary' => esc_attr($colors['primary']),
                '--cp-global-secondary' => esc_attr($colors['secondary']),
                '--cp-global-accent' => esc_attr($colors['accent']),
                '--cp-global-container-width' => absint($spacing['container_width']) . 'px',
                '--cp-global-section-padding' => esc_attr($section_padding_rems),
                '--cp-global-element-spacing' => esc_attr($element_spacing_rems),
            ),
            'h1, h2, h3, h4, h5, h6' => array(
                'font-family' => 'var(--cp-heading-font, ' . esc_attr($typography['heading_font']) . ')',
            ),
            'body' => array(
                'font-family' => 'var(--cp-body-font, ' . esc_attr($typography['body_font']) . ')',
            ),
            '.site-container, .container, .wp-block-group__inner-container' => array(
                'max-width' => 'var(--cp-global-container-width, ' . absint($spacing['container_width']) . 'px)',
                'margin-left' => 'auto',
                'margin-right' => 'auto',
            ),
            '.wp-block-button__link, .button, .btn-primary, .cp-button-primary' => array(
                'background-color' => 'var(--cp-global-primary, ' . esc_attr($colors['primary']) . ')',
                'border-color' => 'var(--cp-global-primary, ' . esc_attr($colors['primary']) . ')',
            ),
            '.wp-block-button__link:hover, .button:hover, .btn-primary:hover, .cp-button-primary:hover' => array(
                'background-color' => 'var(--cp-global-secondary, ' . esc_attr($colors['secondary']) . ')',
                'border-color' => 'var(--cp-global-secondary, ' . esc_attr($colors['secondary']) . ')',
            ),
            '.cp-section, section.wp-block-group' => array(
                'padding-top' => 'var(--cp-global-section-padding, 4rem)',
                'padding-bottom' => 'var(--cp-global-section-padding, 4rem)',
            ),
        );

        // Convert array to CSS string
        $css = '';
        foreach ($css_rules as $selector => $properties) {
            $css .= $selector . ' {' . "\n";
            foreach ($properties as $property => $value) {
                $css .= '    ' . $property . ': ' . $value . ';' . "\n";
            }
            $css .= '}' . "\n";
        }

        // Allow themes/plugins to add custom global styles
        return apply_filters('cp_global_styles_css', $css, $typography, $colors, $spacing);
    }

    /**
     * Map semantic padding names to rem values
     * 
     * @param string $padding Semantic padding name
     * @return string CSS rem value
     */
    private function map_padding_to_rems($padding) {
        // Use centralized mapping via Theme JSON Helper
        $spacing_map = array(
            'compact' => '3rem',
            'standard' => '4rem',
            'spacious' => '6rem',
            'extra-spacious' => '8rem',
            'tight' => '2rem',
            'relaxed' => '5rem',
        );
        
        return $spacing_map[$padding] ?? '4rem';
    }

    /**
     * Map element spacing names to rem values
     * 
     * @param string $spacing Semantic spacing name
     * @return string CSS rem value
     */
    private function map_element_spacing_to_rems($spacing) {
        // Use centralized mapping
        $element_map = array(
            'tight' => '0.5rem',       /* 8px */
            'standard' => '1rem',      /* 16px */
            'relaxed' => '1.5rem',     /* 24px */
            'compact' => '0.75rem',    /* 12px */
        );
        
        return $element_map[$spacing] ?? '1rem';
    }

    /**
     * Increment the global styles version for cache busting
     */
    private function increment_version() {
        $version = get_option('cp_global_styles_version', '1.0');
        $version_parts = explode('.', $version);
        $patch = isset($version_parts[2]) ? (int)$version_parts[2] + 1 : 1;
        $new_version = $version_parts[0] . '.' . $version_parts[1] . '.' . $patch;
        update_option('cp_global_styles_version', $new_version);
    }

    /**
     * Get available fonts from theme.json
     * 
     * @return array Array of font families with 'value' and 'label'
     */
    private function get_available_fonts() {
        // Use the centralized Theme.json Helper
        $all_fonts = CP_Theme_JSON_Helper::get_all_fonts();
        $fonts = array();
        
        foreach ($all_fonts as $slug => $font_data) {
            $fonts[] = array(
                'value' => CP_Theme_JSON_Helper::extract_primary_font($font_data['fontFamily']),
                'label' => $font_data['name'],
                'slug' => $slug,
            );
        }
        
        return $fonts;
    }

    /**
     * Render font selection dropdown
     * 
     * @param string $name Input name
     * @param string $current Current value
     * @param array $fonts Available fonts
     * @return string HTML select element
     */
    private function render_font_dropdown($name, $current, $fonts) {
        $html = '<select name="' . esc_attr($name) . '" class="regular-text">';
        
        foreach ($fonts as $font) {
            $html .= '<option value="' . esc_attr($font['value']) . '" ' . 
                     selected($current, $font['value'], false) . '>' . 
                     esc_html($font['label']) . '</option>';
        }
        
        $html .= '</select>';
        return $html;
    }

    /**
     * Sanitize typography settings (uses theme.json fonts)
     */
    public function sanitize_typography($input) {
        if (!is_array($input)) {
            return array();
        }

        // Get valid fonts from theme.json via helper
        $all_fonts = CP_Theme_JSON_Helper::get_all_fonts();
        $valid_fonts = array();
        
        foreach ($all_fonts as $slug => $font_data) {
            $valid_fonts[] = CP_Theme_JSON_Helper::extract_primary_font($font_data['fontFamily']);
        }
        
        // Add common fallbacks
        $valid_fonts = array_merge($valid_fonts, array('Inter', 'Roboto', 'Open Sans', 'system-ui'));
        $valid_fonts = array_unique($valid_fonts);

        return array(
            'heading_font' => in_array($input['heading_font'] ?? '', $valid_fonts, true) ? $input['heading_font'] : 'Inter',
            'body_font' => in_array($input['body_font'] ?? '', $valid_fonts, true) ? $input['body_font'] : 'Inter',
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
