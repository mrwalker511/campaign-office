<?php
/**
 * Theme.json Helper
 * 
 * Centralized functions for reading and managing theme.json data.
 * Provides consistent access to design tokens, fonts, colors, and other settings.
 * 
 * @package CampaignPress
 * @since 2.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CP_Theme_JSON_Helper Class
 * 
 * Static helper methods for accessing theme.json data throughout the theme.
 */
class CP_Theme_JSON_Helper {

    private static $theme_data = null;
    private static $settings = null;
    private static $colors = null;
    private static $fonts = null;

    /**
     * Initialize and cache theme.json data
     * 
     * @return array Theme settings
     */
    private static function init() {
        if (self::$settings === null) {
            try {
                $theme_data = WP_Theme_JSON_Resolver::get_theme_data();
                self::$theme_data = $theme_data;
                self::$settings = $theme_data->get_settings();
                
                // Parse colors into indexed array
                $palette = self::$settings['color']['palette']['theme'] ?? array();
                self::$colors = array();
                foreach ($palette as $color) {
                    self::$colors[$color['slug']] = $color['color'];
                    self::$colors[$color['slug'] . '_name'] = $color['name'];
                }
                
                // Parse fonts into indexed array
                $font_families = self::$settings['typography']['fontFamilies']['theme'] ?? array();
                self::$fonts = array();
                foreach ($font_families as $font) {
                    self::$fonts[$font['slug']] = array(
                        'fontFamily' => $font['fontFamily'],
                        'name' => $font['name'],
                        'slug' => $font['slug'],
                    );
                }
                
            } catch (Exception $e) {
                // Fallback to defaults if theme.json parsing fails
                self::$settings = array();
                self::$colors = self::get_default_colors();
                self::$fonts = self::get_default_fonts();
            }
        }
        
        return self::$settings;
    }

    /**
     * Get default colors when theme.json fails
     * 
     * @return array Default color palette
     */
    private static function get_default_colors() {
        return array(
            'primary-50' => '#e6f0ff',
            'primary-100' => '#b3d4ff',
            'primary-200' => '#80b8ff',
            'primary-300' => '#4d9dff',
            'primary-400' => '#1a81ff',
            'primary' => '#14213d',
            'primary-600' => '#101b32',
            'primary-700' => '#0d1527',
            'primary-800' => '#0a101d',
            'primary-900' => '#060a12',
            'primary-dark' => '#0d1527',
            'accent-50' => '#fff4e6',
            'accent-100' => '#ffe0b3',
            'accent-200' => '#ffcc80',
            'accent-300' => '#ffb84d',
            'accent-400' => '#ffa41a',
            'accent' => '#ff8800',
            'accent-600' => '#e67a00',
            'accent-700' => '#cc6c00',
            'accent-800' => '#995100',
            'accent-900' => '#331a00',
        );
    }

    /**
     * Get default fonts when theme.json fails
     * 
     * @return array Default font families
     */
    private static function get_default_fonts() {
        return array(
            'display' => array(
                'fontFamily' => "'Playfair Display', Georgia, 'Times New Roman', serif",
                'name' => 'Playfair Display',
                'slug' => 'display',
            ),
            'body' => array(
                'fontFamily' => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif",
                'name' => 'Inter',
                'slug' => 'body',
            ),
            'mono' => array(
                'fontFamily' => "ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, 'DejaVu Sans Mono', monospace",
                'name' => 'System Monospace',
                'slug' => 'mono',
            ),
        );
    }

    /**
     * Get a color value by slug
     * 
     * @param string $slug Color slug (e.g., 'primary', 'accent-500')
     * @param string $fallback Fallback hex color
     * @return string Hex color value
     */
    public static function get_color($slug, $fallback = '#0073aa') {
        self::init();
        return self::$colors[$slug] ?? $fallback;
    }

    /**
     * Get all colors as associative array
     * 
     * @return array All colors indexed by slug
     */
    public static function get_all_colors() {
        self::init();
        return self::$colors;
    }

    /**
     * Get a font by slug
     * 
     * @param string $slug Font slug (e.g., 'display', 'body', 'mono')
     * @return array Font data array with fontFamily, name, slug
     */
    public static function get_font($slug) {
        self::init();
        return self::$fonts[$slug] ?? self::$fonts['body'] ?? array(
            'fontFamily' => 'system-ui, -apple-system, sans-serif',
            'name' => 'System Font',
            'slug' => 'system',
        );
    }

    /**
     * Get all fonts as associative array
     * 
     * @return array All fonts indexed by slug
     */
    public static function get_all_fonts() {
        self::init();
        return self::$fonts;
    }

    /**
     * Get font CSS font-stack
     * 
     * @param string $slug Font slug
     * @return string CSS font-family value
     */
    public static function get_font_family($slug) {
        $font = self::get_font($slug);
        return $font['fontFamily'] ?? 'system-ui, -apple-system, sans-serif';
    }

    /**
     * Extract the primary font name from a CSS font stack
     * 
     * @param string $font_family CSS font-family value
     * @return string Primary font name
     */
    public static function extract_primary_font($font_family) {
        // Remove quotes and extract first font before comma
        $first_font = preg_replace('/^["\']?([^,"\']+)["\']?.*/', '$1', $font_family);
        return $first_font ?: 'system-ui';
    }

    /**
     * Get a spacing value by slug
     * 
     * @param string $slug Spacing slug (e.g., '4', '8')
     * @return string CSS spacing value
     */
    public static function get_spacing($slug) {
        $settings = self::init();
        $spacing_sizes = $settings['spacing']['spacingSizes']['theme'] ?? array();
        
        foreach ($spacing_sizes as $spacing) {
            if ($spacing['slug'] === $slug) {
                return $spacing['size'];
            }
        }
        
        // Fallback to 8px grid calculation
        $values = array(
            '1' => '0.25rem',   // 4px
            '2' => '0.5rem',    // 8px
            '3' => '0.75rem',   // 12px
            '4' => '1rem',      // 16px
            '5' => '1.25rem',   // 20px
            '6' => '1.5rem',    // 24px
            '8' => '2rem',      // 32px
            '10' => '2.5rem',   // 40px
            '12' => '3rem',     // 48px
            '16' => '4rem',     // 64px
            '20' => '5rem',     // 80px
            '24' => '6rem',     // 96px
        );
        
        return $values[$slug] ?? '1rem';
    }

    /**
     * Get all spacing sizes
     * 
     * @return array All spacing sizes indexed by slug
     */
    public static function get_all_spacing() {
        $settings = self::init();
        $spacing_sizes = $settings['spacing']['spacingSizes']['theme'] ?? array();
        $spacing_array = array();
        
        foreach ($spacing_sizes as $spacing) {
            $spacing_array[$spacing['slug']] = $spacing['size'];
        }
        
        return $spacing_array;
    }

    /**
     * Get a typography setting by slug
     * 
     * @param string $slug Typography slug (e.g., '2-xl')
     * @return array Font size data
     */
    public static function get_font_size($slug) {
        $settings = self::init();
        $font_sizes = $settings['typography']['fontSizes']['theme'] ?? array();
        
        foreach ($font_sizes as $size) {
            if ($size['slug'] === $slug) {
                return $size;
            }
        }
        
        return array(
            'name' => 'Base',
            'size' => '1rem',
            'slug' => 'base',
        );
    }

    /**
     * Get all font sizes
     * 
     * @return array All font sizes indexed by slug
     */
    public static function get_all_font_sizes() {
        $settings = self::init();
        $font_sizes = $settings['typography']['fontSizes']['theme'] ?? array();
        $sizes_array = array();
        
        foreach ($font_sizes as $size) {
            $sizes_array[$size['slug']] = $size['size'];
        }
        
        return $sizes_array;
    }

    /**
     * Get shadow preset by slug
     * 
     * @param string $slug Shadow slug (e.g., 'sm', 'md', 'lg')
     * @return string CSS shadow value
     */
    public static function get_shadow($slug) {
        $settings = self::init();
        $shadows = $settings['shadow']['presets'] ?? array();
        
        foreach ($shadows as $shadow) {
            if ($shadow['slug'] === $slug) {
                return $shadow['shadow'];
            }
        }
        
        // Fallback shadows
        $fallbacks = array(
            'sm' => '0 1px 2px 0 rgb(0 0 0 / 0.05)',
            'md' => '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
            'lg' => '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
            'xl' => '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
            '2-xl' => '0 25px 50px -12px rgb(0 0 0 / 0.25)',
        );
        
        return $fallbacks[$slug] ?? 'none';
    }

    /**
     * Get all shadow presets
     * 
     * @return array All shadows indexed by slug
     */
    public static function get_all_shadows() {
        $settings = self::init();
        $shadows = $settings['shadow']['presets'] ?? array();
        $shadows_array = array();
        
        foreach ($shadows as $shadow) {
            $shadows_array[$shadow['slug']] = $shadow['shadow'];
        }
        
        return $shadows_array;
    }

    /**
     * Get content width setting
     * 
     * @return string CSS width value
     */
    public static function get_content_width() {
        $settings = self::init();
        return $settings['layout']['contentSize'] ?? '800px';
    }

    /**
     * Get wide width setting
     * 
     * @return string CSS width value
     */
    public static function get_wide_width() {
        $settings = self::init();
        return $settings['layout']['wideSize'] ?? '1200px';
    }

    /**
     * Check if theme supports a feature
     * 
     * @param string $feature Feature to check
     * @return bool True if supported
     */
    public static function supports($feature) {
        $settings = self::init();
        
        switch ($feature) {
            case 'appearance-tools':
                return $settings['appearanceTools'] ?? false;
            case 'custom-colors':
                return $settings['color']['custom'] ?? false;
            case 'custom-fonts':
                return $settings['typography']['customFontSize'] ?? false;
            case 'custom-spacing':
                return $settings['spacing']['padding'] ?? false;
            default:
                return false;
        }
    }

    /**
     * Clear cached theme data
     * Useful when theme.json changes
     */
    public static function clear_cache() {
        self::$theme_data = null;
        self::$settings = null;
        self::$colors = null;
        self::$fonts = null;
    }
}
