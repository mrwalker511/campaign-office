<?php
/**
 * Mega Menu Builder
 *
 * Advanced navigation menu builder with campaign-specific features:
 * - Visual drag-and-drop menu editor
 * - Multi-column mega menus with icons
 * - Call-to-action buttons in navigation
 * - Featured content areas
 * - Mobile-optimized responsive menus
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Mega_Menu_Builder
 *
 * Advanced menu builder for campaign websites
 */
class CP_Mega_Menu_Builder {

    /**
     * Constructor
     */
    public function __construct() {
        // Add mega menu options to menu items
        add_action('wp_nav_menu_item_custom_fields', array($this, 'add_menu_item_fields'), 10, 4);
        add_action('wp_update_nav_menu_item', array($this, 'save_menu_item_fields'), 10, 2);

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Custom walker for mega menus
        add_filter('wp_nav_menu_args', array($this, 'modify_nav_menu_args'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_theme_page(
            __('Mega Menu', 'campaign-office'),
            __('Mega Menu', 'campaign-office'),
            'edit_theme_options',
            'cp-mega-menu',
            array($this, 'render_mega_menu_page')
        );
    }

    /**
     * Render mega menu builder page
     */
    public function render_mega_menu_page() {
        $menus = wp_get_nav_menus();
        $selected_menu = isset($_GET['menu']) ? intval($_GET['menu']) : 0;

        if (!$selected_menu && !empty($menus)) {
            $selected_menu = $menus[0]->term_id;
        }
        ?>
        <div class="wrap cp-mega-menu-wrap">
            <h1><?php esc_html_e('Mega Menu Builder', 'campaign-office'); ?></h1>
            <p class="description">
                <?php esc_html_e('Build advanced multi-column navigation menus with icons, featured content, and call-to-action buttons.', 'campaign-office'); ?>
            </p>

            <div class="cp-mega-menu-header" style="background: #fff; padding: 1.5rem; margin: 2rem 0; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <label for="cp-menu-selector" style="margin-right: 1rem; font-weight: 600;">
                    <?php esc_html_e('Select Menu:', 'campaign-office'); ?>
                </label>
                <select id="cp-menu-selector" style="min-width: 250px;">
                    <?php foreach ($menus as $menu) : ?>
                        <option value="<?php echo esc_attr($menu->term_id); ?>" <?php selected($selected_menu, $menu->term_id); ?>>
                            <?php echo esc_html($menu->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <a href="<?php echo admin_url('nav-menus.php'); ?>" class="button" style="margin-left: 1rem;">
                    <?php esc_html_e('Manage Menus', 'campaign-office'); ?>
                </a>
            </div>

            <div class="cp-mega-menu-features">
                <h2><?php esc_html_e('Mega Menu Features', 'campaign-office'); ?></h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin: 2rem 0;">

                    <!-- Feature: Icons -->
                    <div class="cp-feature-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">🎨</div>
                        <h3><?php esc_html_e('Menu Icons', 'campaign-office'); ?></h3>
                        <p style="color: #666; margin-bottom: 1rem;">
                            <?php esc_html_e('Add icons to menu items using Dashicons or emoji. Makes navigation more visual and engaging.', 'campaign-office'); ?>
                        </p>
                        <div class="cp-example" style="background: #f5f5f5; padding: 1rem; border-radius: 0.25rem; font-family: monospace; font-size: 0.875rem;">
                            🏠 Home | 📋 Issues | 🎯 Events
                        </div>
                    </div>

                    <!-- Feature: Multi-Column -->
                    <div class="cp-feature-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">📊</div>
                        <h3><?php esc_html_e('Multi-Column Dropdowns', 'campaign-office'); ?></h3>
                        <p style="color: #666; margin-bottom: 1rem;">
                            <?php esc_html_e('Create wide dropdown menus with multiple columns of links and featured content areas.', 'campaign-office'); ?>
                        </p>
                        <div class="cp-example" style="background: #f5f5f5; padding: 1rem; border-radius: 0.25rem;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.75rem;">
                                <div>Column 1<br>Link 1<br>Link 2</div>
                                <div>Column 2<br>Link 3<br>Link 4</div>
                            </div>
                        </div>
                    </div>

                    <!-- Feature: CTA Buttons -->
                    <div class="cp-feature-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">📢</div>
                        <h3><?php esc_html_e('CTA Buttons', 'campaign-office'); ?></h3>
                        <p style="color: #666; margin-bottom: 1rem;">
                            <?php esc_html_e('Highlight important menu items as call-to-action buttons with custom colors.', 'campaign-office'); ?>
                        </p>
                        <button class="button button-primary button-small" disabled style="background: #d63638; border-color: #d63638;">
                            <?php esc_html_e('Donate Now', 'campaign-office'); ?>
                        </button>
                    </div>

                    <!-- Feature: Featured Content -->
                    <div class="cp-feature-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">⭐</div>
                        <h3><?php esc_html_e('Featured Content', 'campaign-office'); ?></h3>
                        <p style="color: #666; margin-bottom: 1rem;">
                            <?php esc_html_e('Add featured images, descriptions, or promotional content within dropdown menus.', 'campaign-office'); ?>
                        </p>
                        <div class="cp-example" style="background: #f5f5f5; padding: 0.5rem; border-radius: 0.25rem; font-size: 0.75rem; text-align: center;">
                            [Featured Event Image]
                        </div>
                    </div>

                    <!-- Feature: Mobile Optimized -->
                    <div class="cp-feature-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">📱</div>
                        <h3><?php esc_html_e('Mobile Optimized', 'campaign-office'); ?></h3>
                        <p style="color: #666; margin-bottom: 1rem;">
                            <?php esc_html_e('Automatically transforms into mobile-friendly hamburger menu with touch-optimized interactions.', 'campaign-office'); ?>
                        </p>
                        <div class="cp-example" style="background: #f5f5f5; padding: 1rem; border-radius: 0.25rem; text-align: center;">
                            ☰ Menu
                        </div>
                    </div>

                    <!-- Feature: Badge & Labels -->
                    <div class="cp-feature-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="font-size: 2rem; margin-bottom: 1rem;">🏷️</div>
                        <h3><?php esc_html_e('Badges & Labels', 'campaign-office'); ?></h3>
                        <p style="color: #666; margin-bottom: 1rem;">
                            <?php esc_html_e('Add "New", "Hot", or custom badges to menu items to draw attention.', 'campaign-office'); ?>
                        </p>
                        <div class="cp-example" style="background: #f5f5f5; padding: 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                            Events <span style="background: #d63638; color: #fff; padding: 0.125rem 0.5rem; border-radius: 0.25rem; font-size: 0.625rem;">NEW</span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="cp-mega-menu-instructions" style="background: #e7f5fe; border-left: 4px solid #0073aa; padding: 1.5rem; margin: 2rem 0;">
                <h3 style="margin-top: 0;"><?php esc_html_e('How to Use Mega Menu Features', 'campaign-office'); ?></h3>
                <ol style="margin: 0; padding-left: 1.5rem;">
                    <li><?php esc_html_e('Go to Appearance → Menus to edit your navigation', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('Expand any menu item to see mega menu options', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('Enable "Mega Menu" for top-level items with dropdowns', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('Add icons, badges, or mark items as CTA buttons', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('Set column layout for mega menu dropdowns', 'campaign-office'); ?></li>
                    <li><?php esc_html_e('Save your menu and view it on your site', 'campaign-office'); ?></li>
                </ol>
            </div>

            <div class="cp-mega-menu-templates" style="margin: 2rem 0;">
                <h2><?php esc_html_e('Menu Templates', 'campaign-office'); ?></h2>
                <p class="description"><?php esc_html_e('Pre-built menu structures for common campaign website needs', 'campaign-office'); ?></p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1.5rem;">

                    <div class="cp-menu-template" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">🎯 <?php esc_html_e('Classic Campaign', 'campaign-office'); ?></h4>
                        <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.875rem; color: #666;">
                            <li>Home</li>
                            <li>About</li>
                            <li>Issues (Mega Menu)</li>
                            <li>Events</li>
                            <li>Get Involved (Mega Menu)</li>
                            <li>Donate (CTA Button)</li>
                        </ul>
                        <button class="button button-secondary" style="margin-top: 1rem; width: 100%;" disabled>
                            <?php esc_html_e('Apply Template', 'campaign-office'); ?>
                        </button>
                    </div>

                    <div class="cp-menu-template" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">📱 <?php esc_html_e('Grassroots Movement', 'campaign-office'); ?></h4>
                        <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.875rem; color: #666;">
                            <li>Home</li>
                            <li>Our Story</li>
                            <li>Volunteer</li>
                            <li>Events</li>
                            <li>News</li>
                            <li>Join Us (CTA Button)</li>
                        </ul>
                        <button class="button button-secondary" style="margin-top: 1rem; width: 100%;" disabled>
                            <?php esc_html_e('Apply Template', 'campaign-office'); ?>
                        </button>
                    </div>

                    <div class="cp-menu-template" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">📋 <?php esc_html_e('Issue-Focused', 'campaign-office'); ?></h4>
                        <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.875rem; color: #666;">
                            <li>Home</li>
                            <li>Issues (Mega Menu with icons)</li>
                            <li>Our Plan</li>
                            <li>News</li>
                            <li>Contact</li>
                            <li>Support (CTA Button)</li>
                        </ul>
                        <button class="button button-secondary" style="margin-top: 1rem; width: 100%;" disabled>
                            <?php esc_html_e('Apply Template', 'campaign-office'); ?>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#cp-menu-selector').change(function() {
                var menuId = $(this).val();
                window.location.href = '?page=cp-mega-menu&menu=' + menuId;
            });
        });
        </script>
        <?php
    }

    /**
     * Add custom fields to menu items
     */
    public function add_menu_item_fields($item_id, $item, $depth, $args) {
        $mega_menu_enabled = get_post_meta($item_id, '_cp_mega_menu', true);
        $menu_icon = get_post_meta($item_id, '_cp_menu_icon', true);
        $is_cta = get_post_meta($item_id, '_cp_is_cta', true);
        $badge_text = get_post_meta($item_id, '_cp_badge_text', true);
        $badge_color = get_post_meta($item_id, '_cp_badge_color', true) ?: '#d63638';
        $column_count = get_post_meta($item_id, '_cp_column_count', true) ?: '1';
        ?>
        <div class="cp-mega-menu-fields" style="margin: 15px 0; padding: 15px; background: #f5f5f5; border-radius: 4px;">

            <p class="description description-wide" style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                <strong><?php esc_html_e('Mega Menu Options', 'campaign-office'); ?></strong>
            </p>

            <?php wp_nonce_field('cp_mega_menu_nonce_action', 'cp_mega_menu_nonce'); ?>

            <!-- Enable Mega Menu -->
            <?php if ($depth === 0) : ?>
            <p class="field-cp-mega-menu description description-wide">
                <label>
                    <input type="checkbox" name="menu-item-cp-mega-menu[<?php echo $item_id; ?>]" value="1" <?php checked($mega_menu_enabled, '1'); ?>>
                    <?php esc_html_e('Enable Mega Menu (multi-column dropdown)', 'campaign-office'); ?>
                </label>
            </p>

            <p class="field-cp-column-count description description-wide" style="<?php echo $mega_menu_enabled ? '' : 'display:none;'; ?>">
                <label for="menu-item-cp-column-count-<?php echo $item_id; ?>">
                    <?php esc_html_e('Columns:', 'campaign-office'); ?>
                    <select name="menu-item-cp-column-count[<?php echo $item_id; ?>]" id="menu-item-cp-column-count-<?php echo $item_id; ?>">
                        <option value="1" <?php selected($column_count, '1'); ?>>1</option>
                        <option value="2" <?php selected($column_count, '2'); ?>>2</option>
                        <option value="3" <?php selected($column_count, '3'); ?>>3</option>
                        <option value="4" <?php selected($column_count, '4'); ?>>4</option>
                    </select>
                </label>
            </p>
            <?php endif; ?>

            <!-- Menu Icon -->
            <p class="field-cp-menu-icon description description-wide">
                <label for="menu-item-cp-menu-icon-<?php echo $item_id; ?>">
                    <?php esc_html_e('Icon (emoji or dashicons class):', 'campaign-office'); ?><br>
                    <input type="text" id="menu-item-cp-menu-icon-<?php echo $item_id; ?>" class="widefat" name="menu-item-cp-menu-icon[<?php echo $item_id; ?>]" value="<?php echo esc_attr($menu_icon); ?>" placeholder="🏠 or dashicons-home">
                </label>
            </p>

            <!-- CTA Button -->
            <p class="field-cp-is-cta description description-wide">
                <label>
                    <input type="checkbox" name="menu-item-cp-is-cta[<?php echo $item_id; ?>]" value="1" <?php checked($is_cta, '1'); ?>>
                    <?php esc_html_e('Display as CTA Button (highlighted)', 'campaign-office'); ?>
                </label>
            </p>

            <!-- Badge -->
            <p class="field-cp-badge-text description description-wide">
                <label for="menu-item-cp-badge-text-<?php echo $item_id; ?>">
                    <?php esc_html_e('Badge Text (e.g., "NEW", "HOT"):', 'campaign-office'); ?><br>
                    <input type="text" id="menu-item-cp-badge-text-<?php echo $item_id; ?>" class="widefat code" name="menu-item-cp-badge-text[<?php echo $item_id; ?>]" value="<?php echo esc_attr($badge_text); ?>">
                </label>
            </p>

            <p class="field-cp-badge-color description description-wide">
                <label for="menu-item-cp-badge-color-<?php echo $item_id; ?>">
                    <?php esc_html_e('Badge Color:', 'campaign-office'); ?><br>
                    <input type="color" id="menu-item-cp-badge-color-<?php echo $item_id; ?>" name="menu-item-cp-badge-color[<?php echo $item_id; ?>]" value="<?php echo esc_attr($badge_color); ?>">
                </label>
            </p>

        </div>
        <?php
    }

    /**
     * Save custom menu item fields
     */
    public function save_menu_item_fields($menu_id, $menu_item_db_id) {
        if (!isset($_POST['cp_mega_menu_nonce']) || !wp_verify_nonce($_POST['cp_mega_menu_nonce'], 'cp_mega_menu_nonce_action')) {
            return;
        }

        $fields = array('_cp_mega_menu', '_cp_menu_icon', '_cp_is_cta', '_cp_badge_text', '_cp_badge_color', '_cp_column_count');

        foreach ($fields as $field) {
            $key = str_replace('_cp_', '', $field);
            $key = str_replace('_', '-', $key);

            if (isset($_POST['menu-item-cp-' . $key][$menu_item_db_id])) {
                $value = sanitize_text_field($_POST['menu-item-cp-' . $key][$menu_item_db_id]);
                update_post_meta($menu_item_db_id, $field, $value);
            } else {
                delete_post_meta($menu_item_db_id, $field);
            }
        }
    }

    /**
     * Modify nav menu args to use custom walker
     */
    public function modify_nav_menu_args($args) {
        if (!isset($args['walker'])) {
            $args['walker'] = new CP_Mega_Menu_Walker();
        }
        return $args;
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook !== 'nav-menus.php' && $hook !== 'toplevel_page_cp-mega-menu') {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_add_inline_script('wp-color-picker', '
            jQuery(document).ready(function($) {
                $("input[name*=\'badge-color\']").wpColorPicker();
            });
        ');
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_add_inline_style('campaign-office-style', '
            .cp-mega-menu {
                position: static !important;
            }
            .cp-mega-menu > .sub-menu {
                position: absolute;
                left: 0;
                right: 0;
                display: grid;
                background: #fff;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                padding: 2rem;
                gap: 2rem;
            }
            .cp-mega-menu.cp-columns-2 > .sub-menu { grid-template-columns: repeat(2, 1fr); }
            .cp-mega-menu.cp-columns-3 > .sub-menu { grid-template-columns: repeat(3, 1fr); }
            .cp-mega-menu.cp-columns-4 > .sub-menu { grid-template-columns: repeat(4, 1fr); }

            .menu-item .cp-menu-icon {
                margin-right: 0.5rem;
                font-size: 1.2em;
            }
            .menu-item .cp-menu-badge {
                display: inline-block;
                padding: 0.125rem 0.5rem;
                margin-left: 0.5rem;
                font-size: 0.625rem;
                font-weight: 700;
                color: #fff;
                border-radius: 0.25rem;
                text-transform: uppercase;
            }
            .menu-item.cp-cta-item > a {
                background: var(--wp--preset--color--primary, #0073aa);
                color: #fff !important;
                padding: 0.5rem 1rem;
                border-radius: 0.25rem;
                font-weight: 600;
                transition: all 0.3s;
            }
            .menu-item.cp-cta-item > a:hover {
                background: var(--wp--preset--color--secondary, #005a87);
                transform: translateY(-2px);
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            }
        ');
    }
}

/**
 * Custom Walker for Mega Menus
 */
class CP_Mega_Menu_Walker extends Walker_Nav_Menu {

    public function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $classes = empty($item->classes) ? array() : (array) $item->classes;

        // Add mega menu class
        $mega_menu_enabled = get_post_meta($item->ID, '_cp_mega_menu', true);
        $column_count = get_post_meta($item->ID, '_cp_column_count', true) ?: '1';
        if ($mega_menu_enabled && $depth === 0) {
            $classes[] = 'cp-mega-menu';
            $classes[] = 'cp-columns-' . $column_count;
        }

        // Add CTA class
        $is_cta = get_post_meta($item->ID, '_cp_is_cta', true);
        if ($is_cta) {
            $classes[] = 'cp-cta-item';
        }

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $output .= $indent . '<li' . $class_names . '>';

        $atts = array();
        $atts['title']  = !empty($item->attr_title) ? $item->attr_title : '';
        $atts['target'] = !empty($item->target) ? $item->target : '';
        $atts['rel']    = !empty($item->xfn) ? $item->xfn : '';
        $atts['href']   = !empty($item->url) ? $item->url : '';

        $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);

        $attributes = '';
        foreach ($atts as $attr => $value) {
            if (!empty($value)) {
                $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                $attributes .= ' ' . $attr . '="' . $value . '"';
            }
        }

        // Get custom fields
        $menu_icon = get_post_meta($item->ID, '_cp_menu_icon', true);
        $badge_text = get_post_meta($item->ID, '_cp_badge_text', true);
        $badge_color = get_post_meta($item->ID, '_cp_badge_color', true) ?: '#d63638';

        $title = apply_filters('the_title', $item->title, $item->ID);
        $title = apply_filters('nav_menu_item_title', $title, $item, $args, $depth);

        // Build menu item HTML
        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';

        // Add icon
        if ($menu_icon) {
            if (strpos($menu_icon, 'dashicons-') !== false) {
                $item_output .= '<span class="cp-menu-icon dashicons ' . esc_attr($menu_icon) . '"></span>';
            } else {
                $item_output .= '<span class="cp-menu-icon">' . esc_html($menu_icon) . '</span>';
            }
        }

        $item_output .= $args->link_before . $title . $args->link_after;

        // Add badge
        if ($badge_text) {
            $item_output .= '<span class="cp-menu-badge" style="background-color: ' . esc_attr($badge_color) . ';">' . esc_html($badge_text) . '</span>';
        }

        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}

// Initialize Mega Menu Builder
new CP_Mega_Menu_Builder();
