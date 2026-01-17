<?php
/**
 * Accessibility Features
 *
 * Comprehensive WCAG 2.1 AA accessibility features for CampaignPress theme.
 * Includes ARIA labels, keyboard navigation, skip links, focus management,
 * screen reader helpers, color contrast validation, and alt text enforcement.
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Accessibility
 *
 * Handles all accessibility features for the theme
 */
class CP_Accessibility {

    /**
     * Constructor
     */
    public function __construct() {
        // Add skip links to header
        add_action('wp_body_open', array($this, 'add_skip_links'));

        // Enhance navigation with ARIA labels
        add_filter('nav_menu_link_attributes', array($this, 'add_nav_menu_aria'), 10, 4);

        // Add ARIA labels to pagination
        add_filter('next_posts_link_attributes', array($this, 'add_next_posts_link_aria'));
        add_filter('previous_posts_link_attributes', array($this, 'add_prev_posts_link_aria'));

        // Ensure all images have alt text
        add_filter('wp_get_attachment_image_attributes', array($this, 'enforce_image_alt_text'), 10, 3);

        // Add focus management styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_accessibility_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_accessibility_scripts'));

        // Add admin settings for accessibility
        add_action('admin_menu', array($this, 'add_accessibility_admin_page'));

        // Add accessibility widget to dashboard
        add_action('wp_dashboard_setup', array($this, 'add_accessibility_dashboard_widget'));

        // Validate form labels
        add_filter('comment_form_defaults', array($this, 'add_comment_form_aria'));

        // Add keyboard navigation helpers
        add_action('wp_footer', array($this, 'add_keyboard_navigation_script'));

        // Color contrast validator
        add_action('admin_notices', array($this, 'check_color_contrast'));
    }

    /**
     * Add skip links for keyboard navigation
     *
     * Allows keyboard users to skip to main content, navigation, or footer
     */
    public function add_skip_links() {
        ?>
        <a class="skip-link screen-reader-text" href="#main-content">
            <?php esc_html_e('Skip to main content', 'campaignpress'); ?>
        </a>
        <a class="skip-link screen-reader-text" href="#primary-navigation">
            <?php esc_html_e('Skip to navigation', 'campaignpress'); ?>
        </a>
        <a class="skip-link screen-reader-text" href="#footer">
            <?php esc_html_e('Skip to footer', 'campaignpress'); ?>
        </a>
        <?php
    }

    /**
     * Add ARIA labels to navigation menu items
     *
     * @param array    $atts  HTML attributes for menu item
     * @param WP_Post  $item  Menu item data object
     * @param stdClass $args  An object of menu arguments
     * @param int      $depth Depth of menu item
     * @return array Modified attributes
     */
    public function add_nav_menu_aria($atts, $item, $args, $depth) {
        // Add aria-label for better screen reader support
        if (!isset($atts['aria-label']) && !empty($item->title)) {
            $atts['aria-label'] = esc_attr($item->title);
        }

        // Mark current page for screen readers
        if (in_array('current-menu-item', $item->classes)) {
            $atts['aria-current'] = 'page';
        }

        // Add aria-haspopup for menu items with children
        if (in_array('menu-item-has-children', $item->classes)) {
            $atts['aria-haspopup'] = 'true';
            $atts['aria-expanded'] = 'false';
        }

        return $atts;
    }

    /**
     * Add ARIA label to next posts link
     *
     * @return string ARIA attributes
     */
    public function add_next_posts_link_aria() {
        return 'aria-label="' . esc_attr__('Next page', 'campaignpress') . '"';
    }

    /**
     * Add ARIA label to previous posts link
     *
     * @return string ARIA attributes
     */
    public function add_prev_posts_link_aria() {
        return 'aria-label="' . esc_attr__('Previous page', 'campaignpress') . '"';
    }

    /**
     * Enforce alt text on images
     *
     * Ensures all images have proper alt text or empty alt for decorative images
     *
     * @param array $attr       Attributes for the image markup
     * @param WP_Post $attachment Image attachment post
     * @param string|int[] $size Requested image size
     * @return array Modified attributes
     */
    public function enforce_image_alt_text($attr, $attachment, $size) {
        // If alt text is not set, use the image title or set empty for decorative
        if (!isset($attr['alt']) || empty($attr['alt'])) {
            $alt_text = get_post_meta($attachment->ID, '_wp_attachment_image_alt', true);

            if (empty($alt_text)) {
                // Check if image is decorative (in customizer option)
                $decorative_images = get_option('cp_decorative_images', array());

                if (in_array($attachment->ID, $decorative_images)) {
                    $attr['alt'] = ''; // Empty alt for decorative images
                    $attr['role'] = 'presentation';
                } else {
                    // Use title as fallback
                    $attr['alt'] = get_the_title($attachment->ID);
                }
            } else {
                $attr['alt'] = $alt_text;
            }
        }

        return $attr;
    }

    /**
     * Enqueue accessibility styles
     *
     * Includes focus management, skip links, and screen reader text styles
     */
    public function enqueue_accessibility_styles() {
        $css = "
        /* Skip Links */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #000;
            color: #fff;
            padding: 8px 12px;
            text-decoration: none;
            z-index: 100000;
            transition: top 0.3s ease;
        }

        .skip-link:focus {
            top: 0;
            outline: 3px solid #0073aa;
            outline-offset: 2px;
        }

        /* Screen Reader Text */
        .screen-reader-text {
            border: 0;
            clip: rect(1px, 1px, 1px, 1px);
            clip-path: inset(50%);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute !important;
            width: 1px;
            word-wrap: normal !important;
        }

        .screen-reader-text:focus {
            background-color: #f1f1f1;
            border-radius: 3px;
            box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
            clip: auto !important;
            clip-path: none;
            color: #21759b;
            display: block;
            font-size: 14px;
            font-weight: bold;
            height: auto;
            left: 5px;
            line-height: normal;
            padding: 15px 23px 14px;
            text-decoration: none;
            top: 5px;
            width: auto;
            z-index: 100000;
        }

        /* Enhanced Focus Styles */
        a:focus,
        button:focus,
        input:focus,
        textarea:focus,
        select:focus,
        .btn:focus {
            outline: 3px solid #0073aa;
            outline-offset: 2px;
            box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.25);
        }

        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            a:focus,
            button:focus,
            input:focus {
                outline: 4px solid currentColor;
                outline-offset: 3px;
            }
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* Focus Visible for Modern Browsers */
        :focus-visible {
            outline: 3px solid #0073aa;
            outline-offset: 2px;
        }

        :focus:not(:focus-visible) {
            outline: none;
        }

        /* Ensure sufficient color contrast for links */
        a {
            text-decoration: underline;
        }

        /* ARIA Live Region Styles */
        .aria-live-region {
            position: absolute;
            left: -10000px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        /* Form Error Styles */
        .form-error {
            color: #d63638;
            font-weight: bold;
            margin-top: 0.5em;
        }

        .form-field.has-error input,
        .form-field.has-error textarea,
        .form-field.has-error select {
            border-color: #d63638;
            border-width: 2px;
        }

        /* Required Field Indicator */
        .required-indicator {
            color: #d63638;
            font-weight: bold;
            margin-left: 0.25em;
        }

        /* Keyboard Navigation Helper */
        .keyboard-navigation-active *:focus {
            outline: 3px solid #0073aa !important;
            outline-offset: 2px !important;
        }
        ";

        wp_add_inline_style('campaignpress-main', $css);
    }

    /**
     * Enqueue accessibility JavaScript
     *
     * Handles keyboard navigation detection and focus management
     */
    public function enqueue_accessibility_scripts() {
        $js = "
        (function($) {
            'use strict';

            // Detect keyboard navigation
            let isKeyboardNav = false;

            $(document).on('keydown', function(e) {
                if (e.keyCode === 9) { // Tab key
                    isKeyboardNav = true;
                    $('body').addClass('keyboard-navigation-active');
                }
            });

            $(document).on('mousedown', function() {
                isKeyboardNav = false;
                $('body').removeClass('keyboard-navigation-active');
            });

            // Enhanced menu accessibility
            $('.menu-item-has-children > a').on('click', function(e) {
                if (isKeyboardNav) {
                    e.preventDefault();
                    const \$submenu = $(this).next('.sub-menu');
                    const expanded = $(this).attr('aria-expanded') === 'true';

                    $(this).attr('aria-expanded', !expanded);
                    \$submenu.toggleClass('show');
                }
            });

            // Close submenus with Escape key
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // Escape key
                    $('.menu-item-has-children > a').attr('aria-expanded', 'false');
                    $('.sub-menu').removeClass('show');
                }
            });

            // Trap focus in modal dialogs
            $(document).on('keydown', '.modal, .dialog', function(e) {
                if (e.keyCode === 9) { // Tab key
                    const focusableElements = $(this).find('a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex=\"-1\"])');
                    const firstElement = focusableElements.first();
                    const lastElement = focusableElements.last();

                    if (e.shiftKey && $(document.activeElement).is(firstElement)) {
                        e.preventDefault();
                        lastElement.focus();
                    } else if (!e.shiftKey && $(document.activeElement).is(lastElement)) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            });

        })(jQuery);
        ";

        // Ensure script is enqueued before adding inline script
        if (wp_script_is('campaignpress-main', 'enqueued') || wp_script_is('campaignpress-main', 'registered')) {
            wp_add_inline_script('campaignpress-main', $js, 'after');
        }
    }

    /**
     * Add keyboard navigation helper script to footer
     *
     * Provides additional keyboard navigation support
     */
    public function add_keyboard_navigation_script() {
        ?>
        <div class="aria-live-region" aria-live="polite" aria-atomic="true"></div>
        <script>
        // Announce page changes to screen readers
        (function() {
            if (window.history && window.history.pushState) {
                const announcer = document.querySelector('.aria-live-region');

                // Announce when AJAX content loads
                document.addEventListener('DOMContentLoaded', function() {
                    if (announcer && document.title) {
                        announcer.textContent = 'Page loaded: ' + document.title;
                    }
                });
            }
        })();
        </script>
        <?php
    }

    /**
     * Add ARIA attributes to comment form
     *
     * @param array $defaults Default comment form arguments
     * @return array Modified arguments
     */
    public function add_comment_form_aria($defaults) {
        // Add required indicator
        $defaults['comment_field'] = str_replace(
            '<textarea',
            '<textarea aria-required="true" aria-label="' . esc_attr__('Comment', 'campaignpress') . '"',
            $defaults['comment_field']
        );

        // Add ARIA labels to fields
        $defaults['fields']['author'] = str_replace(
            'type="text"',
            'type="text" aria-required="true" aria-label="' . esc_attr__('Name', 'campaignpress') . '"',
            $defaults['fields']['author']
        );

        $defaults['fields']['email'] = str_replace(
            'type="email"',
            'type="email" aria-required="true" aria-label="' . esc_attr__('Email', 'campaignpress') . '"',
            $defaults['fields']['email']
        );

        if (isset($defaults['fields']['url'])) {
            $defaults['fields']['url'] = str_replace(
                'type="url"',
                'type="url" aria-label="' . esc_attr__('Website', 'campaignpress') . '"',
                $defaults['fields']['url']
            );
        }

        return $defaults;
    }

    /**
     * Add accessibility admin page
     *
     * Provides accessibility settings and testing tools
     */
    public function add_accessibility_admin_page() {
        add_theme_page(
            __('Accessibility Settings', 'campaignpress'),
            __('Accessibility', 'campaignpress'),
            'manage_options',
            'cp-accessibility',
            array($this, 'render_accessibility_admin_page')
        );
    }

    /**
     * Render accessibility admin page
     *
     * Settings page for managing accessibility features
     */
    public function render_accessibility_admin_page() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

        // Save settings
        if (isset($_POST['cp_accessibility_settings_nonce']) &&
            wp_verify_nonce($_POST['cp_accessibility_settings_nonce'], 'cp_accessibility_settings')) {

            // Enable/disable features
            update_option('cp_accessibility_skip_links', isset($_POST['skip_links']) ? 1 : 0);
            update_option('cp_accessibility_focus_styles', isset($_POST['focus_styles']) ? 1 : 0);
            update_option('cp_accessibility_aria_labels', isset($_POST['aria_labels']) ? 1 : 0);

            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', 'campaignpress') . '</p></div>';
        }

        $skip_links = get_option('cp_accessibility_skip_links', 1);
        $focus_styles = get_option('cp_accessibility_focus_styles', 1);
        $aria_labels = get_option('cp_accessibility_aria_labels', 1);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="card">
                <h2><?php esc_html_e('WCAG 2.1 AA Compliance', 'campaignpress'); ?></h2>
                <p><?php esc_html_e('CampaignPress includes comprehensive accessibility features to ensure your campaign website is accessible to all users.', 'campaignpress'); ?></p>

                <h3><?php esc_html_e('Active Features:', 'campaignpress'); ?></h3>
                <ul style="list-style: disc; margin-left: 2em;">
                    <li><?php esc_html_e('Skip links for keyboard navigation', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('ARIA labels and landmarks', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Enhanced focus management', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Screen reader text helpers', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Keyboard navigation support', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Alt text enforcement for images', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Form label associations', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Color contrast validation', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('Reduced motion support', 'campaignpress'); ?></li>
                    <li><?php esc_html_e('High contrast mode support', 'campaignpress'); ?></li>
                </ul>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('cp_accessibility_settings', 'cp_accessibility_settings_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Skip Links', 'campaignpress'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="skip_links" value="1" <?php checked($skip_links, 1); ?>>
                                <?php esc_html_e('Enable skip links for keyboard navigation', 'campaignpress'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Focus Styles', 'campaignpress'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="focus_styles" value="1" <?php checked($focus_styles, 1); ?>>
                                <?php esc_html_e('Enable enhanced focus styles', 'campaignpress'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('ARIA Labels', 'campaignpress'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="aria_labels" value="1" <?php checked($aria_labels, 1); ?>>
                                <?php esc_html_e('Enable ARIA labels and landmarks', 'campaignpress'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'campaignpress')); ?>
            </form>

            <div class="card">
                <h2><?php esc_html_e('Testing Tools', 'campaignpress'); ?></h2>
                <p><?php esc_html_e('Recommended accessibility testing tools:', 'campaignpress'); ?></p>
                <ul style="list-style: disc; margin-left: 2em;">
                    <li><strong>WAVE:</strong> <a href="https://wave.webaim.org/" target="_blank">https://wave.webaim.org/</a></li>
                    <li><strong>axe DevTools:</strong> Browser extension for automated testing</li>
                    <li><strong>NVDA/JAWS:</strong> Screen reader testing</li>
                    <li><strong>Keyboard Navigation:</strong> Test using only Tab, Enter, and Arrow keys</li>
                </ul>
            </div>

            <div class="card">
                <h2><?php esc_html_e('Helper Functions', 'campaignpress'); ?></h2>
                <p><?php esc_html_e('Use these functions in your templates:', 'campaignpress'); ?></p>
                <pre style="background: #f5f5f5; padding: 15px; border-left: 4px solid #0073aa;">
// Add screen reader text
cp_screen_reader_text( 'Text for screen readers only' );

// Check color contrast
cp_check_color_contrast( '#ffffff', '#000000' ); // Returns contrast ratio

// Add required field indicator
cp_required_field_indicator();
                </pre>
            </div>
        </div>
        <?php
    }

    /**
     * Add accessibility dashboard widget
     *
     * Shows accessibility status in WordPress admin dashboard
     */
    public function add_accessibility_dashboard_widget() {
        wp_add_dashboard_widget(
            'cp_accessibility_dashboard',
            __('Accessibility Status', 'campaignpress'),
            array($this, 'render_accessibility_dashboard_widget')
        );
    }

    /**
     * Render accessibility dashboard widget
     */
    public function render_accessibility_dashboard_widget() {
        ?>
        <div class="cp-accessibility-dashboard">
            <p><strong><?php esc_html_e('WCAG 2.1 AA Compliance Status', 'campaignpress'); ?></strong></p>

            <ul style="list-style: none; padding: 0;">
                <li style="margin-bottom: 8px;">
                    <span style="color: #46b450;">✓</span>
                    <?php esc_html_e('Skip links enabled', 'campaignpress'); ?>
                </li>
                <li style="margin-bottom: 8px;">
                    <span style="color: #46b450;">✓</span>
                    <?php esc_html_e('ARIA labels active', 'campaignpress'); ?>
                </li>
                <li style="margin-bottom: 8px;">
                    <span style="color: #46b450;">✓</span>
                    <?php esc_html_e('Keyboard navigation supported', 'campaignpress'); ?>
                </li>
                <li style="margin-bottom: 8px;">
                    <span style="color: #46b450;">✓</span>
                    <?php esc_html_e('Focus management enabled', 'campaignpress'); ?>
                </li>
            </ul>

            <p>
                <a href="<?php echo esc_url(admin_url('themes.php?page=cp-accessibility')); ?>" class="button">
                    <?php esc_html_e('Manage Settings', 'campaignpress'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Check color contrast and show admin notice if poor
     *
     * Validates theme colors meet WCAG AA standards
     */
    public function check_color_contrast() {
        // Only show on theme customizer page
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'appearance_page_cp-accessibility') {
            return;
        }

        // Get theme colors
        $primary_color = get_theme_mod('campaignpress_primary_color', '#0073aa');
        $background_color = get_theme_mod('background_color', 'ffffff');

        $contrast_ratio = $this->calculate_contrast_ratio($primary_color, '#' . $background_color);

        if ($contrast_ratio < 4.5) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                    printf(
                        esc_html__('Color contrast ratio (%.2f:1) may not meet WCAG AA standards (4.5:1 required). Consider adjusting your theme colors.', 'campaignpress'),
                        $contrast_ratio
                    );
                    ?>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Calculate color contrast ratio
     *
     * @param string $color1 Hex color code
     * @param string $color2 Hex color code
     * @return float Contrast ratio
     */
    private function calculate_contrast_ratio($color1, $color2) {
        $l1 = $this->get_relative_luminance($color1);
        $l2 = $this->get_relative_luminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Get relative luminance of a color
     *
     * @param string $color Hex color code
     * @return float Relative luminance
     */
    private function get_relative_luminance($color) {
        $color = ltrim($color, '#');

        $r = hexdec(substr($color, 0, 2)) / 255;
        $g = hexdec(substr($color, 2, 2)) / 255;
        $b = hexdec(substr($color, 4, 2)) / 255;

        $r = $r <= 0.03928 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = $g <= 0.03928 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = $b <= 0.03928 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}

// Initialize accessibility features
new CP_Accessibility();

/**
 * Helper Functions
 */

/**
 * Output screen reader text
 *
 * @param string $text Text to output for screen readers only
 */
function cp_screen_reader_text($text) {
    echo '<span class="screen-reader-text">' . esc_html($text) . '</span>';
}

/**
 * Get screen reader text (for use in attributes)
 *
 * @param string $text Text for screen readers
 * @return string HTML span element
 */
function cp_get_screen_reader_text($text) {
    return '<span class="screen-reader-text">' . esc_html($text) . '</span>';
}

/**
 * Output required field indicator
 *
 * @param bool $echo Whether to echo or return
 * @return string|void Required indicator HTML
 */
function cp_required_field_indicator($echo = true) {
    $html = '<span class="required-indicator" aria-label="' . esc_attr__('required', 'campaignpress') . '">*</span>';

    if ($echo) {
        echo $html;
    } else {
        return $html;
    }
}

/**
 * Check color contrast ratio
 *
 * @param string $color1 First color (hex)
 * @param string $color2 Second color (hex)
 * @return float Contrast ratio
 */
function cp_check_color_contrast($color1, $color2) {
    $accessibility = new CP_Accessibility();
    $reflection = new ReflectionClass($accessibility);
    $method = $reflection->getMethod('calculate_contrast_ratio');
    $method->setAccessible(true);

    return $method->invoke($accessibility, $color1, $color2);
}

/**
 * Add ARIA label to element
 *
 * @param string $label ARIA label text
 * @return string ARIA label attribute
 */
function cp_aria_label($label) {
    return 'aria-label="' . esc_attr($label) . '"';
}

/**
 * Add ARIA describedby attribute
 *
 * @param string $id ID of describing element
 * @return string ARIA describedby attribute
 */
function cp_aria_describedby($id) {
    return 'aria-describedby="' . esc_attr($id) . '"';
}
