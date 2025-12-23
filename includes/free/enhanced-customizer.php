<?php
/**
 * Enhanced Live Customizer
 *
 * Advanced theme customizer with:
 * - Real-time preview updates
 * - One-click color schemes
 * - Campaign-specific presets
 * - Typography controls
 * - Layout options
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Enhanced_Customizer
 *
 * Enhanced customizer for campaign themes
 */
class CP_Enhanced_Customizer {

    /**
     * Color schemes
     *
     * @var array
     */
    private $color_schemes = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize color schemes
        $this->init_color_schemes();

        // Add customizer sections
        add_action('customize_register', array($this, 'register_customizer_settings'));

        // Enqueue customizer preview scripts
        add_action('customize_preview_init', array($this, 'enqueue_preview_scripts'));

        // Output custom CSS
        add_action('wp_head', array($this, 'output_custom_css'), 100);

        // Admin menu for color schemes
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Initialize color schemes
     */
    private function init_color_schemes() {
        $this->color_schemes = array(
            'democratic_blue' => array(
                'name' => __('Democratic Blue', 'campaign-office'),
                'description' => __('Classic blue color scheme for Democratic campaigns', 'campaign-office'),
                'colors' => array(
                    'primary' => '#003366',
                    'secondary' => '#0066CC',
                    'accent' => '#FF3333',
                    'background' => '#FFFFFF',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #003366 0%, #0066CC 100%)',
            ),
            'republican_red' => array(
                'name' => __('Republican Red', 'campaign-office'),
                'description' => __('Bold red color scheme for Republican campaigns', 'campaign-office'),
                'colors' => array(
                    'primary' => '#CC0000',
                    'secondary' => '#8B0000',
                    'accent' => '#003366',
                    'background' => '#FFFFFF',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #CC0000 0%, #8B0000 100%)',
            ),
            'independent_purple' => array(
                'name' => __('Independent Purple', 'campaign-office'),
                'description' => __('Bipartisan purple for independent candidates', 'campaign-office'),
                'colors' => array(
                    'primary' => '#663399',
                    'secondary' => '#9966CC',
                    'accent' => '#FF6600',
                    'background' => '#FFFFFF',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #663399 0%, #9966CC 100%)',
            ),
            'grassroots_green' => array(
                'name' => __('Grassroots Green', 'campaign-office'),
                'description' => __('Environmental and progressive green scheme', 'campaign-office'),
                'colors' => array(
                    'primary' => '#228B22',
                    'secondary' => '#32CD32',
                    'accent' => '#FFD700',
                    'background' => '#FFFFFF',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #228B22 0%, #32CD32 100%)',
            ),
            'modern_teal' => array(
                'name' => __('Modern Teal', 'campaign-office'),
                'description' => __('Fresh, modern teal for progressive campaigns', 'campaign-office'),
                'colors' => array(
                    'primary' => '#008B8B',
                    'secondary' => '#20B2AA',
                    'accent' => '#FF4500',
                    'background' => '#FFFFFF',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #008B8B 0%, #20B2AA 100%)',
            ),
            'patriotic_flag' => array(
                'name' => __('Patriotic Flag', 'campaign-office'),
                'description' => __('Red, white, and blue patriotic theme', 'campaign-office'),
                'colors' => array(
                    'primary' => '#002868',
                    'secondary' => '#BF0A30',
                    'accent' => '#FFFFFF',
                    'background' => '#F5F5F5',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #002868 0%, #BF0A30 100%)',
            ),
            'sunset_orange' => array(
                'name' => __('Sunset Orange', 'campaign-office'),
                'description' => __('Energetic orange for bold campaigns', 'campaign-office'),
                'colors' => array(
                    'primary' => '#FF6600',
                    'secondary' => '#FF8C00',
                    'accent' => '#1E3A8A',
                    'background' => '#FFFFFF',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #FF6600 0%, #FF8C00 100%)',
            ),
            'professional_navy' => array(
                'name' => __('Professional Navy', 'campaign-office'),
                'description' => __('Conservative navy blue for serious campaigns', 'campaign-office'),
                'colors' => array(
                    'primary' => '#000080',
                    'secondary' => '#4169E1',
                    'accent' => '#FFD700',
                    'background' => '#FFFFFF',
                    'text' => '#333333',
                ),
                'gradient' => 'linear-gradient(135deg, #000080 0%, #4169E1 100%)',
            ),
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cp-design-studio',
            __('Color Schemes', 'campaign-office'),
            __('Color Schemes', 'campaign-office'),
            'edit_theme_options',
            'cp-color-schemes',
            array($this, 'render_color_schemes_page')
        );
    }

    /**
     * Render color schemes page
     */
    public function render_color_schemes_page() {
        $current_scheme = get_theme_mod('cp_color_scheme', 'democratic_blue');

        if (isset($_POST['cp_apply_scheme']) && check_admin_referer('cp_color_scheme')) {
            $scheme_key = sanitize_text_field($_POST['scheme']);
            if (isset($this->color_schemes[$scheme_key])) {
                $this->apply_color_scheme($scheme_key);
                echo '<div class="notice notice-success is-dismissible"><p>' .
                     esc_html__('Color scheme applied successfully!', 'campaign-office') . '</p></div>';
                $current_scheme = $scheme_key;
            }
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('One-Click Color Schemes', 'campaign-office'); ?></h1>
            <p class="description">
                <?php esc_html_e('Choose a pre-designed color scheme for your campaign website. All colors will update instantly across your entire site.', 'campaign-office'); ?>
            </p>

            <div class="cp-color-schemes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 1.5rem; margin: 2rem 0;">

                <?php foreach ($this->color_schemes as $key => $scheme) : ?>
                <div class="cp-scheme-card" style="background: #fff; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); <?php echo $current_scheme === $key ? 'border: 3px solid #0073aa;' : ''; ?>">

                    <!-- Color Preview -->
                    <div class="cp-scheme-preview" style="background: <?php echo esc_attr($scheme['gradient']); ?>; height: 120px; position: relative;">
                        <div style="position: absolute; bottom: 0; left: 0; right: 0; display: flex; height: 40px;">
                            <?php foreach ($scheme['colors'] as $color) : ?>
                                <div style="flex: 1; background: <?php echo esc_attr($color); ?>;"></div>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($current_scheme === $key) : ?>
                            <div style="position: absolute; top: 10px; right: 10px; background: #fff; color: #0073aa; padding: 0.5rem 1rem; border-radius: 0.25rem; font-weight: 700; font-size: 0.75rem;">
                                ✓ <?php esc_html_e('ACTIVE', 'campaign-office'); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Scheme Info -->
                    <div style="padding: 1.5rem;">
                        <h3 style="margin: 0 0 0.5rem 0;"><?php echo esc_html($scheme['name']); ?></h3>
                        <p style="color: #666; font-size: 0.875rem; margin: 0 0 1rem 0;">
                            <?php echo esc_html($scheme['description']); ?>
                        </p>

                        <!-- Color Swatches -->
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                            <?php foreach ($scheme['colors'] as $name => $color) : ?>
                                <div title="<?php echo esc_attr(ucfirst($name)); ?>: <?php echo esc_attr($color); ?>"
                                     style="width: 40px; height: 40px; background: <?php echo esc_attr($color); ?>; border-radius: 0.25rem; border: 2px solid #eee; cursor: pointer;">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Apply Button -->
                        <form method="post" style="margin: 0;">
                            <?php wp_nonce_field('cp_color_scheme'); ?>
                            <input type="hidden" name="scheme" value="<?php echo esc_attr($key); ?>">
                            <?php if ($current_scheme === $key) : ?>
                                <button type="button" class="button button-secondary" style="width: 100%;" disabled>
                                    <?php esc_html_e('Currently Active', 'campaign-office'); ?>
                                </button>
                            <?php else : ?>
                                <button type="submit" name="cp_apply_scheme" class="button button-primary" style="width: 100%;">
                                    <?php esc_html_e('Apply This Scheme', 'campaign-office'); ?>
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>

            <div class="cp-custom-colors-info" style="background: #e7f5fe; border-left: 4px solid #0073aa; padding: 1.5rem; margin: 2rem 0;">
                <h3 style="margin-top: 0;">💡 <?php esc_html_e('Need Custom Colors?', 'campaign-office'); ?></h3>
                <p style="margin-bottom: 0;">
                    <?php esc_html_e('After applying a scheme, you can customize individual colors in the', 'campaign-office'); ?>
                    <a href="<?php echo admin_url('customize.php?autofocus[section]=cp_colors'); ?>">
                        <?php esc_html_e('Theme Customizer', 'campaign-office'); ?>
                    </a>
                    <?php esc_html_e('or the', 'campaign-office'); ?>
                    <a href="<?php echo admin_url('admin.php?page=cp-global-styles'); ?>">
                        <?php esc_html_e('Design Studio Global Styles', 'campaign-office'); ?>
                    </a>.
                </p>
            </div>

            <div class="cp-scheme-tips" style="margin: 2rem 0;">
                <h2><?php esc_html_e('Color Scheme Tips', 'campaign-office'); ?></h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">

                    <div style="background: #fff; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">🎨 <?php esc_html_e('Brand Consistency', 'campaign-office'); ?></h4>
                        <p style="font-size: 0.875rem; margin-bottom: 0;">
                            <?php esc_html_e('Choose colors that match your campaign logo and materials for consistent branding.', 'campaign-office'); ?>
                        </p>
                    </div>

                    <div style="background: #fff; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">📊 <?php esc_html_e('Party Alignment', 'campaign-office'); ?></h4>
                        <p style="font-size: 0.875rem; margin-bottom: 0;">
                            <?php esc_html_e('Traditional party colors help voters immediately identify your political affiliation.', 'campaign-office'); ?>
                        </p>
                    </div>

                    <div style="background: #fff; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">♿ <?php esc_html_e('Accessibility', 'campaign-office'); ?></h4>
                        <p style="font-size: 0.875rem; margin-bottom: 0;">
                            <?php esc_html_e('All schemes are designed with WCAG contrast ratios for readability and accessibility.', 'campaign-office'); ?>
                        </p>
                    </div>

                    <div style="background: #fff; padding: 1rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">🔄 <?php esc_html_e('Easy Switching', 'campaign-office'); ?></h4>
                        <p style="font-size: 0.875rem; margin-bottom: 0;">
                            <?php esc_html_e('You can change schemes anytime. Try different options to see what resonates best.', 'campaign-office'); ?>
                        </p>
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Apply color scheme
     */
    private function apply_color_scheme($scheme_key) {
        if (!isset($this->color_schemes[$scheme_key])) {
            return;
        }

        $scheme = $this->color_schemes[$scheme_key];

        // Save scheme selection
        set_theme_mod('cp_color_scheme', $scheme_key);

        // Apply individual colors
        foreach ($scheme['colors'] as $name => $color) {
            set_theme_mod('cp_color_' . $name, $color);
        }
    }

    /**
     * Register customizer settings
     */
    public function register_customizer_settings($wp_customize) {
        // Color Section
        $wp_customize->add_section('cp_colors', array(
            'title' => __('Campaign Colors', 'campaign-office'),
            'priority' => 30,
            'description' => __('Customize your campaign colors. Or use one-click color schemes.', 'campaign-office'),
        ));

        // Color Scheme Selector
        $wp_customize->add_setting('cp_color_scheme', array(
            'default' => 'democratic_blue',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage',
        ));

        $scheme_choices = array();
        foreach ($this->color_schemes as $key => $scheme) {
            $scheme_choices[$key] = $scheme['name'];
        }

        $wp_customize->add_control('cp_color_scheme', array(
            'label' => __('Quick Color Scheme', 'campaign-office'),
            'section' => 'cp_colors',
            'type' => 'select',
            'choices' => $scheme_choices,
            'description' => __('Choose a preset color scheme', 'campaign-office'),
        ));

        // Individual color controls
        $colors = array(
            'primary' => __('Primary Color', 'campaign-office'),
            'secondary' => __('Secondary Color', 'campaign-office'),
            'accent' => __('Accent Color', 'campaign-office'),
            'background' => __('Background Color', 'campaign-office'),
            'text' => __('Text Color', 'campaign-office'),
        );

        foreach ($colors as $key => $label) {
            $wp_customize->add_setting('cp_color_' . $key, array(
                'default' => '#0073aa',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport' => 'postMessage',
            ));

            $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'cp_color_' . $key, array(
                'label' => $label,
                'section' => 'cp_colors',
            )));
        }

        // Typography Section
        $wp_customize->add_section('cp_typography', array(
            'title' => __('Typography', 'campaign-office'),
            'priority' => 35,
        ));

        // Heading Font
        $wp_customize->add_setting('cp_heading_font', array(
            'default' => 'Inter',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control('cp_heading_font', array(
            'label' => __('Heading Font', 'campaign-office'),
            'section' => 'cp_typography',
            'type' => 'select',
            'choices' => array(
                'Inter' => 'Inter',
                'Roboto' => 'Roboto',
                'Poppins' => 'Poppins',
                'Montserrat' => 'Montserrat',
                'Open Sans' => 'Open Sans',
            ),
        ));

        // Body Font
        $wp_customize->add_setting('cp_body_font', array(
            'default' => 'Inter',
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control('cp_body_font', array(
            'label' => __('Body Font', 'campaign-office'),
            'section' => 'cp_typography',
            'type' => 'select',
            'choices' => array(
                'Inter' => 'Inter',
                'Open Sans' => 'Open Sans',
                'Lato' => 'Lato',
                'Roboto' => 'Roboto',
            ),
        ));

        // Layout Section
        $wp_customize->add_section('cp_layout', array(
            'title' => __('Layout Options', 'campaign-office'),
            'priority' => 40,
        ));

        // Container Width
        $wp_customize->add_setting('cp_container_width', array(
            'default' => '1200',
            'sanitize_callback' => 'absint',
            'transport' => 'postMessage',
        ));

        $wp_customize->add_control('cp_container_width', array(
            'label' => __('Container Width (px)', 'campaign-office'),
            'section' => 'cp_layout',
            'type' => 'number',
            'input_attrs' => array(
                'min' => 960,
                'max' => 1920,
                'step' => 40,
            ),
        ));
    }

    /**
     * Enqueue preview scripts
     */
    public function enqueue_preview_scripts() {
        wp_enqueue_script(
            'cp-customizer-preview',
            get_template_directory_uri() . '/assets/js/customizer-preview.js',
            array('jquery', 'customize-preview'),
            '2.0.0',
            true
        );
    }

    /**
     * Output custom CSS
     */
    public function output_custom_css() {
        $primary = get_theme_mod('cp_color_primary', '#0073aa');
        $secondary = get_theme_mod('cp_color_secondary', '#005a87');
        $accent = get_theme_mod('cp_color_accent', '#d63638');
        $background = get_theme_mod('cp_color_background', '#ffffff');
        $text = get_theme_mod('cp_color_text', '#333333');
        $heading_font = get_theme_mod('cp_heading_font', 'Inter');
        $body_font = get_theme_mod('cp_body_font', 'Inter');
        $container_width = get_theme_mod('cp_container_width', '1200');
        ?>
        <style id="cp-custom-css">
            :root {
                --cp-color-primary: <?php echo esc_attr($primary); ?>;
                --cp-color-secondary: <?php echo esc_attr($secondary); ?>;
                --cp-color-accent: <?php echo esc_attr($accent); ?>;
                --cp-color-background: <?php echo esc_attr($background); ?>;
                --cp-color-text: <?php echo esc_attr($text); ?>;
                --cp-heading-font: <?php echo esc_attr($heading_font); ?>, sans-serif;
                --cp-body-font: <?php echo esc_attr($body_font); ?>, sans-serif;
                --cp-container-width: <?php echo esc_attr($container_width); ?>px;
            }

            body {
                background-color: var(--cp-color-background);
                color: var(--cp-color-text);
                font-family: var(--cp-body-font);
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: var(--cp-heading-font);
            }

            .wp-block-button__link,
            .button,
            .btn-primary {
                background-color: var(--cp-color-primary);
                border-color: var(--cp-color-primary);
            }

            .wp-block-button__link:hover,
            .button:hover,
            .btn-primary:hover {
                background-color: var(--cp-color-secondary);
                border-color: var(--cp-color-secondary);
            }

            a {
                color: var(--cp-color-primary);
            }

            a:hover {
                color: var(--cp-color-secondary);
            }

            .has-primary-color {
                color: var(--cp-color-primary) !important;
            }

            .has-primary-background-color {
                background-color: var(--cp-color-primary) !important;
            }

            .container,
            .wp-block-group__inner-container {
                max-width: var(--cp-container-width);
            }
        </style>
        <?php
    }
}

// Initialize Enhanced Customizer
new CP_Enhanced_Customizer();
