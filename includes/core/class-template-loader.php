<?php
/**
 * Template Loader
 *
 * Handles loading templates from the templates/ directory.
 *
 * @package CampaignPress
 * @subpackage Core
 * @since 2.0.0
 */

namespace CampaignPress\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Template_Loader {

    /**
     * Initialize template loader.
     */
    public static function init() {
        add_filter('template_include', [__CLASS__, 'load_template'], 99);
    }

    /**
     * Load template from subdirectories.
     *
     * @param string $template The path of the template to include.
     * @return string The path to the template file.
     */
    public static function load_template($template) {
        $new_template = '';

        // Check conditional tags to determine which template *should* be loaded
        // hierarchy order matters!

        if (is_embed()) {
            // embed template usually not customized in this theme, fall through
        } elseif (is_404() && ($t = self::get_template_path('404.php'))) {
            $new_template = $t;
        } elseif (is_search() && ($t = self::get_template_path('search.php'))) {
            $new_template = $t;
        } elseif (is_front_page()) {
            // Check for customizer homepage layout setting
            $homepage_layout = get_theme_mod('campaignpress_homepage_layout', 'modern');
            
            // Map layout names to template files
            $layout_templates = array(
                'classic'     => 'home-classic-statesman.html',
                'modern'      => 'home.html',
                'traditional' => 'home-grassroots.html',
            );
            
            // Get the template file for the selected layout
            $layout_template = isset($layout_templates[$homepage_layout]) 
                ? $layout_templates[$homepage_layout] 
                : 'home.html';
            
            // Try to load the layout-specific template
            if (($t = self::get_template_path($layout_template))) {
                $new_template = $t;
            } elseif (($t = self::get_template_path('front-page.php'))) {
                $new_template = $t;
            }
        } elseif (is_home() && ($t = self::get_template_path('home.php'))) {
            $new_template = $t;
        } elseif (is_post_type_archive() && ($t = self::get_template_path('archive-' . get_post_type() . '.php'))) {
            $new_template = $t;
        } elseif (is_tax() && ($t = self::get_template_path('taxonomy.php'))) {
            $new_template = $t;
        } elseif (is_attachment() && ($t = self::get_template_path('attachment.php'))) {
            $new_template = $t;
        } elseif (is_single()) {
            // Check for custom post type single
            $post_type = get_post_type();
            if (($t = self::get_template_path('single-' . $post_type . '.php'))) {
                $new_template = $t;
            } elseif (($t = self::get_template_path('single.php'))) {
                $new_template = $t;
            }
        } elseif (is_page()) {
            // Check for page templates (custom templates are usually handled by WP core via meta,
            // but if we moved standard page.php we need to handle it)
            // Note: Custom page templates selected in editor usually have full paths or relative paths.
            // If standard page, look for page.php
             if (($t = self::get_template_path('page.php'))) {
                $new_template = $t;
            }
        } elseif (is_category() && ($t = self::get_template_path('category.php'))) {
            $new_template = $t;
        } elseif (is_tag() && ($t = self::get_template_path('tag.php'))) {
            $new_template = $t;
        } elseif (is_author() && ($t = self::get_template_path('author.php'))) {
            $new_template = $t;
        } elseif (is_date() && ($t = self::get_template_path('date.php'))) {
            $new_template = $t;
        } elseif (is_archive() && ($t = self::get_template_path('archive.php'))) {
            $new_template = $t;
        }

        if ($new_template) {
            return $new_template;
        }

        // Fallback: If we haven't found a specific match, check if the *passed* template
        // exists in our legacy folder (e.g. if WP found something we didn't account for but we moved it)
        // This is risky if WP found index.php, so we rely on the conditionals above first.

        return $template;
    }

    /**
     * Helper to check template existence in organized folders.
     */
    private static function get_template_path($filename) {
        // Check templates/legacy/
        $legacy = CAMPAIGNPRESS_THEME_DIR . '/templates/legacy/' . $filename;
        if (file_exists($legacy)) {
            return $legacy;
        }

        // Check templates/
        $organized = CAMPAIGNPRESS_THEME_DIR . '/templates/' . $filename;
        if (file_exists($organized)) {
            return $organized;
        }

        // Check templates/custom-post-types/single/ (backward compatibility)
        if (strpos($filename, 'single-') === 0) {
             $cpt = CAMPAIGNPRESS_THEME_DIR . '/templates/custom-post-types/single/' . $filename;
             if (file_exists($cpt)) {
                 return $cpt;
             }
        }

         // Check templates/custom-post-types/archive/ (backward compatibility)
        if (strpos($filename, 'archive-') === 0) {
             $cpt = CAMPAIGNPRESS_THEME_DIR . '/templates/custom-post-types/archive/' . $filename;
             if (file_exists($cpt)) {
                 return $cpt;
             }
        }

        return false;
    }
}
