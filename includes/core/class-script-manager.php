<?php
/**
 * Script Manager
 *
 * Centralized script management using WordPress built-in libraries.
 * This class provides helper methods to properly enqueue scripts with
 * WordPress dependencies, ensuring optimal performance and compatibility.
 *
 * @package CampaignPress\Core
 * @since 2.0.0
 */

namespace CampaignPress\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Script Manager Class
 */
class Script_Manager {

    /**
     * WordPress built-in script handles
     *
     * @var array
     */
    private static $wp_scripts = array(
        // Core Libraries
        'jquery',
        'backbone',
        'underscore',
        'lodash',
        'moment',
        'react',
        'react-dom',

        // jQuery UI Components
        'jquery-ui-core',
        'jquery-ui-accordion',
        'jquery-ui-autocomplete',
        'jquery-ui-button',
        'jquery-ui-datepicker',
        'jquery-ui-dialog',
        'jquery-ui-draggable',
        'jquery-ui-droppable',
        'jquery-ui-menu',
        'jquery-ui-mouse',
        'jquery-ui-progressbar',
        'jquery-ui-resizable',
        'jquery-ui-selectable',
        'jquery-ui-selectmenu',
        'jquery-ui-slider',
        'jquery-ui-sortable',
        'jquery-ui-spinner',
        'jquery-ui-tabs',
        'jquery-ui-tooltip',

        // jQuery Effects
        'jquery-effects-core',
        'jquery-effects-blind',
        'jquery-effects-bounce',
        'jquery-effects-clip',
        'jquery-effects-drop',
        'jquery-effects-explode',
        'jquery-effects-fade',
        'jquery-effects-fold',
        'jquery-effects-highlight',
        'jquery-effects-puff',
        'jquery-effects-pulsate',
        'jquery-effects-scale',
        'jquery-effects-shake',
        'jquery-effects-size',
        'jquery-effects-slide',
        'jquery-effects-transfer',

        // WordPress Packages
        'wp-element',
        'wp-components',
        'wp-blocks',
        'wp-block-editor',
        'wp-data',
        'wp-api-fetch',
        'wp-i18n',
        'wp-hooks',
        'wp-compose',
        'wp-date',
        'wp-dom-ready',
        'wp-url',
        'wp-api',
        'wp-color-picker',
        'wp-media',
        'wp-editor',
        'wp-plugins',
        'wp-edit-post',

        // Utilities
        'clipboard',
        'hoverIntent',
        'imagesloaded',
        'masonry',
        'mediaelement',
        'wp-mediaelement',
        'code-editor',
        'twemoji',
    );

    /**
     * Initialize the Script Manager
     */
    public static function init() {
        // No initialization needed for now
    }

    /**
     * Check if a script handle is provided by WordPress
     *
     * @param string $handle Script handle to check.
     * @return bool True if WordPress provides it, false otherwise.
     */
    public static function is_wp_script($handle) {
        return in_array($handle, self::$wp_scripts, true);
    }

    /**
     * Get all WordPress-provided script handles
     *
     * @return array Array of script handles.
     */
    public static function get_wp_scripts() {
        return self::$wp_scripts;
    }

    /**
     * Enqueue script with automatic WordPress dependency detection
     *
     * @param string       $handle    Script handle.
     * @param string       $src       Script source URL (relative to theme or absolute).
     * @param array        $deps      Array of dependencies.
     * @param string|false $ver       Script version.
     * @param bool         $in_footer Whether to enqueue in footer.
     */
    public static function enqueue_script($handle, $src, $deps = array(), $ver = false, $in_footer = true) {
        // Filter out dependencies that should use WordPress versions
        $deps = self::normalize_dependencies($deps);

        // Get full URL if relative path
        if (!preg_match('/^(https?:)?\/\//', $src)) {
            $src = get_template_directory_uri() . '/' . ltrim($src, '/');
        }

        // Use theme version if no version specified
        if ($ver === false && defined('CAMPAIGNPRESS_VERSION')) {
            $ver = CAMPAIGNPRESS_VERSION;
        }

        wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
    }

    /**
     * Register script with WordPress dependency detection
     *
     * @param string       $handle    Script handle.
     * @param string       $src       Script source URL.
     * @param array        $deps      Array of dependencies.
     * @param string|false $ver       Script version.
     * @param bool         $in_footer Whether to load in footer.
     */
    public static function register_script($handle, $src, $deps = array(), $ver = false, $in_footer = true) {
        $deps = self::normalize_dependencies($deps);

        if (!preg_match('/^(https?:)?\/\//', $src)) {
            $src = get_template_directory_uri() . '/' . ltrim($src, '/');
        }

        if ($ver === false && defined('CAMPAIGNPRESS_VERSION')) {
            $ver = CAMPAIGNPRESS_VERSION;
        }

        wp_register_script($handle, $src, $deps, $ver, $in_footer);
    }

    /**
     * Normalize dependencies to use WordPress versions where available
     *
     * @param array $deps Array of dependencies.
     * @return array Normalized dependencies.
     */
    private static function normalize_dependencies($deps) {
        $normalized = array();

        foreach ($deps as $dep) {
            // Check if this is an alias that should map to a WordPress script
            if (isset(self::get_dependency_map()[$dep])) {
                $normalized[] = self::get_dependency_map()[$dep];
            } else {
                $normalized[] = $dep;
            }
        }

        return array_unique($normalized);
    }

    /**
     * Get dependency mapping for common aliases
     *
     * @return array Dependency mapping.
     */
    private static function get_dependency_map() {
        return array(
            'react'        => 'wp-element',
            'react-dom'    => 'wp-element',
            'jquery-ui'    => 'jquery-ui-core',
            '_'            => 'underscore',
            'lodash'       => 'lodash',
            'moment'       => 'moment',
            'backbone'     => 'backbone',
        );
    }

    /**
     * Enqueue React-based script using WordPress React (wp-element)
     *
     * @param string       $handle    Script handle.
     * @param string       $src       Script source URL.
     * @param array        $extra_deps Additional dependencies beyond wp-element.
     * @param string|false $ver       Script version.
     */
    public static function enqueue_react_script($handle, $src, $extra_deps = array(), $ver = false) {
        $deps = array_merge(array('wp-element', 'wp-i18n'), $extra_deps);
        self::enqueue_script($handle, $src, $deps, $ver, true);
    }

    /**
     * Enqueue block editor script with proper WordPress dependencies
     *
     * @param string       $handle    Script handle.
     * @param string       $src       Script source URL.
     * @param array        $extra_deps Additional dependencies.
     * @param string|false $ver       Script version.
     */
    public static function enqueue_block_script($handle, $src, $extra_deps = array(), $ver = false) {
        $deps = array_merge(
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            $extra_deps
        );
        self::enqueue_script($handle, $src, $deps, $ver, true);
    }

    /**
     * Enqueue admin script with common WordPress admin dependencies
     *
     * @param string       $handle    Script handle.
     * @param string       $src       Script source URL.
     * @param array        $extra_deps Additional dependencies.
     * @param string|false $ver       Script version.
     */
    public static function enqueue_admin_script($handle, $src, $extra_deps = array(), $ver = false) {
        $deps = array_merge(array('jquery', 'wp-api'), $extra_deps);
        self::enqueue_script($handle, $src, $deps, $ver, true);
    }

    /**
     * Enqueue frontend script with jQuery
     *
     * @param string       $handle    Script handle.
     * @param string       $src       Script source URL.
     * @param array        $extra_deps Additional dependencies.
     * @param string|false $ver       Script version.
     * @param bool         $in_footer Whether to load in footer.
     */
    public static function enqueue_frontend_script($handle, $src, $extra_deps = array(), $ver = false, $in_footer = true) {
        $deps = array_merge(array('jquery'), $extra_deps);
        self::enqueue_script($handle, $src, $deps, $ver, $in_footer);
    }

    /**
     * Self-host external CDN library
     *
     * This method provides a pattern for self-hosting libraries that are
     * currently loaded from CDNs.
     *
     * @param string $handle  Script handle.
     * @param string $local_path Path to local file relative to theme.
     * @param string $cdn_url Fallback CDN URL.
     * @param array  $deps    Dependencies.
     * @param string $ver     Version.
     */
    public static function enqueue_selfhosted_or_cdn($handle, $local_path, $cdn_url, $deps = array(), $ver = '1.0.0') {
        $local_file = get_template_directory() . '/' . ltrim($local_path, '/');

        if (file_exists($local_file)) {
            // Use local version
            $src = get_template_directory_uri() . '/' . ltrim($local_path, '/');
        } else {
            // Fallback to CDN
            $src = $cdn_url;

            // Log warning in debug mode
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    'Campaign Office: Local file not found for %s, using CDN. Download and place at: %s',
                    $handle,
                    $local_file
                ));
            }
        }

        wp_enqueue_script($handle, $src, $deps, $ver, true);
    }

    /**
     * Get optimization recommendations for current script usage
     *
     * @return array Array of recommendations.
     */
    public static function get_optimization_recommendations() {
        global $wp_scripts;

        $recommendations = array();

        if (!isset($wp_scripts->queue)) {
            return $recommendations;
        }

        foreach ($wp_scripts->queue as $handle) {
            if (!isset($wp_scripts->registered[$handle])) {
                continue;
            }

            $script = $wp_scripts->registered[$handle];

            // Check for CDN usage
            if (isset($script->src) && preg_match('/^https?:\/\/cdn\.|unpkg\.com|jsdelivr\.net/', $script->src)) {
                $recommendations[] = array(
                    'type'    => 'cdn',
                    'handle'  => $handle,
                    'src'     => $script->src,
                    'message' => "Consider self-hosting {$handle} for better performance and GDPR compliance.",
                );
            }

            // Check for missing WordPress dependencies
            if (isset($script->deps)) {
                foreach ($script->deps as $dep) {
                    if (!self::is_wp_script($dep) && !isset($wp_scripts->registered[$dep])) {
                        $recommendations[] = array(
                            'type'    => 'missing_dep',
                            'handle'  => $handle,
                            'dep'     => $dep,
                            'message' => "Script {$handle} depends on {$dep} which is not registered.",
                        );
                    }
                }
            }
        }

        return $recommendations;
    }

    /**
     * Print optimization recommendations (for debugging)
     */
    public static function print_recommendations() {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        add_action('wp_footer', function() {
            $recommendations = self::get_optimization_recommendations();

            if (empty($recommendations)) {
                return;
            }

            echo '<!-- Script Optimization Recommendations -->';
            echo '<script>';
            echo 'if (window.console && console.groupCollapsed) {';
            echo '    console.groupCollapsed("Script Optimization Recommendations");';
            foreach ($recommendations as $rec) {
                echo '    console.log(' . wp_json_encode($rec) . ');';
            }
            echo '    console.groupEnd();';
            echo '}';
            echo '</script>';
        }, 999);
    }
}

// Initialize
Script_Manager::init();
