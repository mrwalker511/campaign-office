<?php
/**
 * Multi-Language and Translation Support
 *
 * Provides comprehensive translation support for CampaignPress including:
 * - WPML compatibility
 * - Polylang compatibility
 * - Language switcher widget
 * - RTL support
 * - Translation helpers
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Translation_Support
 *
 * Handles multi-language and translation functionality
 */
class CP_Translation_Support {

    /**
     * Supported translation plugins
     *
     * @var array
     */
    private $translation_plugins = array(
        'wpml' => 'WPML',
        'polylang' => 'Polylang',
        'translatepress' => 'TranslatePress',
    );

    /**
     * Constructor
     */
    public function __construct() {
        // Load text domain at 'init' to comply with WordPress 6.7.0+ requirements
        add_action('init', array($this, 'load_textdomain'), 1);

        // Register language switcher widget
        add_action('widgets_init', array($this, 'register_language_switcher_widget'));

        // WPML compatibility
        add_action('after_setup_theme', array($this, 'wpml_compatibility'));

        // Polylang compatibility
        add_action('after_setup_theme', array($this, 'polylang_compatibility'));

        // Add language switcher shortcode
        add_shortcode('cp_language_switcher', array($this, 'language_switcher_shortcode'));

        // RTL support
        add_action('wp_enqueue_scripts', array($this, 'enqueue_rtl_styles'));

        // Admin notice for translation plugins
        add_action('admin_notices', array($this, 'translation_plugin_notice'));

        // Add language menu item
        add_filter('wp_nav_menu_items', array($this, 'add_language_switcher_to_menu'), 10, 2);

        // Register custom post types with translation plugins
        add_action('init', array($this, 'register_cpt_for_translation'), 20);
    }

    /**
     * Load theme text domain for translations
     */
    public function load_textdomain() {
        // Load theme translations
        load_theme_textdomain('campaign-office', CAMPAIGNPRESS_THEME_DIR . '/languages');

        // Register theme with WPML
        if (function_exists('wpml_get_current_language')) {
            $this->setup_wpml_config();
        }
    }

    /**
     * Setup WPML configuration
     */
    private function setup_wpml_config() {
        // Register strings for translation in WPML
        $strings_to_register = array(
            'candidate_name' => get_theme_mod('campaignpress_candidate_name', ''),
            'campaign_tagline' => get_theme_mod('campaignpress_tagline', ''),
            'donation_url' => get_theme_mod('campaignpress_donation_url', ''),
        );

        foreach ($strings_to_register as $key => $value) {
            if (!empty($value)) {
                do_action('wpml_register_single_string', 'CampaignPress', $key, $value);
            }
        }
    }

    /**
     * WPML compatibility
     */
    public function wpml_compatibility() {
        if (!function_exists('wpml_get_current_language')) {
            return;
        }

        // Make custom post types translatable
        add_filter('wpml_post_edit_can_translate', array($this, 'wpml_make_cpt_translatable'), 10, 3);

        // Translate custom post type archives
        add_filter('wpml_ls_language_url', array($this, 'wpml_translate_cpt_archives'), 10, 2);

        // Register theme options for translation
        add_filter('wpml_register_theme_options', array($this, 'wpml_register_theme_options'));
    }

    /**
     * Make custom post types translatable in WPML
     */
    public function wpml_make_cpt_translatable($can_translate, $post_id, $post_type) {
        $translatable_cpts = array('cp_issue', 'cp_event', 'cp_endorsement', 'cp_team', 'cp_volunteer');

        if (in_array($post_type, $translatable_cpts, true)) {
            return true;
        }

        return $can_translate;
    }

    /**
     * Translate custom post type archives in WPML
     */
    public function wpml_translate_cpt_archives($url, $lang) {
        // This hook allows translation of CPT archive URLs
        return $url;
    }

    /**
     * Register theme options for WPML translation
     */
    public function wpml_register_theme_options($options) {
        $options[] = array(
            'name' => 'CampaignPress Options',
            'option_name' => 'campaignpress_options',
        );

        return $options;
    }

    /**
     * Polylang compatibility
     */
    public function polylang_compatibility() {
        if (!function_exists('pll_current_language')) {
            return;
        }

        // Register strings for translation in Polylang
        add_action('admin_init', array($this, 'polylang_register_strings'));

        // Make custom post types translatable
        add_filter('pll_get_post_types', array($this, 'polylang_register_cpt'));
        add_filter('pll_get_taxonomies', array($this, 'polylang_register_taxonomies'));
    }

    /**
     * Register strings for Polylang translation
     */
    public function polylang_register_strings() {
        if (!function_exists('pll_register_string')) {
            return;
        }

        // Register theme option strings
        pll_register_string('candidate_name', get_theme_mod('campaignpress_candidate_name', ''), 'CampaignPress');
        pll_register_string('campaign_tagline', get_theme_mod('campaignpress_tagline', ''), 'CampaignPress');
        pll_register_string('donation_button_text', __('Donate Now', 'campaign-office'), 'CampaignPress');
        pll_register_string('volunteer_button_text', __('Get Involved', 'campaign-office'), 'CampaignPress');
    }

    /**
     * Register custom post types for Polylang
     */
    public function polylang_register_cpt($post_types) {
        $post_types['cp_issue'] = 'cp_issue';
        $post_types['cp_event'] = 'cp_event';
        $post_types['cp_endorsement'] = 'cp_endorsement';
        $post_types['cp_team'] = 'cp_team';
        $post_types['cp_volunteer'] = 'cp_volunteer';

        return $post_types;
    }

    /**
     * Register taxonomies for Polylang
     */
    public function polylang_register_taxonomies($taxonomies) {
        $taxonomies['issue_category'] = 'issue_category';
        $taxonomies['event_type'] = 'event_type';

        return $taxonomies;
    }

    /**
     * Register custom post types with all translation plugins
     */
    public function register_cpt_for_translation() {
        // WPML: Mark CPTs as translatable
        if (function_exists('wpml_get_current_language')) {
            global $sitepress;
            if ($sitepress) {
                $cpt_list = array('cp_issue', 'cp_event', 'cp_endorsement', 'cp_team', 'cp_volunteer');
                foreach ($cpt_list as $cpt) {
                    $sitepress->set_setting('custom_posts_sync_option', array($cpt => 1), true);
                    $sitepress->set_setting('custom_types_readonly_config', array($cpt => 0), true);
                }
            }
        }
    }

    /**
     * Register language switcher widget
     */
    public function register_language_switcher_widget() {
        register_widget('CP_Language_Switcher_Widget');
    }

    /**
     * Language switcher shortcode
     *
     * Usage: [cp_language_switcher type="dropdown"]
     */
    public function language_switcher_shortcode($atts) {
        $atts = shortcode_atts(array(
            'type' => 'dropdown', // dropdown or flags
            'show_names' => 'yes',
            'show_flags' => 'yes',
        ), $atts);

        $active_plugin = $this->get_active_translation_plugin();

        if (!$active_plugin) {
            return '<p class="cp-no-translation">' . esc_html__('No translation plugin detected.', 'campaign-office') . '</p>';
        }

        ob_start();

        switch ($active_plugin) {
            case 'wpml':
                $this->render_wpml_switcher($atts);
                break;
            case 'polylang':
                $this->render_polylang_switcher($atts);
                break;
            case 'translatepress':
                $this->render_translatepress_switcher($atts);
                break;
        }

        return ob_get_clean();
    }

    /**
     * Get active translation plugin
     */
    private function get_active_translation_plugin() {
        if (function_exists('wpml_get_current_language')) {
            return 'wpml';
        } elseif (function_exists('pll_current_language')) {
            return 'polylang';
        } elseif (class_exists('TRP_Translate_Press')) {
            return 'translatepress';
        }

        return false;
    }

    /**
     * Render WPML language switcher
     */
    private function render_wpml_switcher($atts) {
        $languages = apply_filters('wpml_active_languages', null);

        if (empty($languages)) {
            return;
        }

        ?>
        <div class="cp-language-switcher cp-wpml-switcher">
            <ul class="cp-language-list">
                <?php foreach ($languages as $lang) : ?>
                    <li class="<?php echo esc_attr($lang['active'] ? 'active' : ''); ?>">
                        <a href="<?php echo esc_url($lang['url']); ?>" hreflang="<?php echo esc_attr($lang['language_code']); ?>">
                            <?php if ($atts['show_flags'] === 'yes') : ?>
                                <img src="<?php echo esc_url($lang['country_flag_url']); ?>" alt="<?php echo esc_attr($lang['native_name']); ?>" height="12">
                            <?php endif; ?>
                            <?php if ($atts['show_names'] === 'yes') : ?>
                                <span><?php echo esc_html($lang['native_name']); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }

    /**
     * Render Polylang language switcher
     */
    private function render_polylang_switcher($atts) {
        if (!function_exists('pll_the_languages')) {
            return;
        }

        $args = array(
            'show_flags' => ($atts['show_flags'] === 'yes') ? 1 : 0,
            'show_names' => ($atts['show_names'] === 'yes') ? 1 : 0,
            'echo' => 0,
            'dropdown' => ($atts['type'] === 'dropdown') ? 1 : 0,
        );

        echo '<div class="cp-language-switcher cp-polylang-switcher">';
        echo wp_kses_post(pll_the_languages($args));
        echo '</div>';
    }

    /**
     * Render TranslatePress language switcher
     */
    private function render_translatepress_switcher($atts) {
        if (!function_exists('trp_custom_language_switcher')) {
            return;
        }

        echo '<div class="cp-language-switcher cp-translatepress-switcher">';
        // TranslatePress has built-in shortcode [language-switcher]
        echo do_shortcode('[language-switcher]');
        echo '</div>';
    }

    /**
     * Enqueue RTL styles if needed
     */
    public function enqueue_rtl_styles() {
        if (is_rtl()) {
            wp_enqueue_style(
                'campaignpress-rtl',
                CAMPAIGNPRESS_ASSETS_URI . '/css/rtl.css',
                array('campaignpress-main'),
                CAMPAIGNPRESS_VERSION
            );
        }
    }

    /**
     * Add language switcher to navigation menu
     */
    public function add_language_switcher_to_menu($items, $args) {
        // Only add to primary menu if setting is enabled
        if ($args->theme_location !== 'primary') {
            return $items;
        }

        if (!get_theme_mod('campaignpress_menu_language_switcher', false)) {
            return $items;
        }

        $switcher = $this->language_switcher_shortcode(array('type' => 'flags'));
        if ($switcher) {
            $items .= '<li class="menu-item menu-item-language-switcher">' . $switcher . '</li>';
        }

        return $items;
    }

    /**
     * Admin notice for translation plugin recommendation
     */
    public function translation_plugin_notice() {
        $screen = get_current_screen();
        if ($screen->id !== 'themes') {
            return;
        }

        if ($this->get_active_translation_plugin()) {
            return;
        }

        // Check if user dismissed this notice
        if (get_user_meta(get_current_user_id(), 'cp_translation_notice_dismissed', true)) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible">
            <p>
                <strong><?php esc_html_e('CampaignPress Multi-Language Support', 'campaign-office'); ?></strong><br>
                <?php esc_html_e('Running a bilingual or multilingual campaign? CampaignPress supports WPML, Polylang, and TranslatePress for seamless translation.', 'campaign-office'); ?>
            </p>
            <p>
                <a href="<?php echo esc_url(admin_url('plugin-install.php?s=wpml&tab=search')); ?>" class="button"><?php esc_html_e('Install Translation Plugin', 'campaign-office'); ?></a>
                <a href="#" class="button-secondary cp-dismiss-translation-notice"><?php esc_html_e('Dismiss', 'campaign-office'); ?></a>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.cp-dismiss-translation-notice').on('click', function(e) {
                e.preventDefault();
                $.post(ajaxurl, {
                    action: 'cp_dismiss_translation_notice',
                    nonce: '<?php echo esc_js(wp_create_nonce('cp_dismiss_translation_notice')); ?>'
                });
                $(this).closest('.notice').fadeOut();
            });
        });
        </script>
        <?php
    }
}

/**
 * Language Switcher Widget
 */
class CP_Language_Switcher_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'cp_language_switcher',
            __('CampaignPress Language Switcher', 'campaign-office'),
            array('description' => __('Display language switcher for multilingual campaigns', 'campaign-office'))
        );
    }

    /**
     * Widget output
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . esc_html($instance['title']) . $args['after_title'];
        }

        $shortcode_atts = array(
            'type' => $instance['type'] ?? 'dropdown',
            'show_names' => $instance['show_names'] ?? 'yes',
            'show_flags' => $instance['show_flags'] ?? 'yes',
        );

        $translation_support = new CP_Translation_Support();
        echo $translation_support->language_switcher_shortcode($shortcode_atts);

        echo $args['after_widget'];
    }

    /**
     * Widget form
     */
    public function form($instance) {
        $title = isset($instance['title']) ? $instance['title'] : __('Languages', 'campaign-office');
        $type = isset($instance['type']) ? $instance['type'] : 'dropdown';
        $show_names = isset($instance['show_names']) ? $instance['show_names'] : 'yes';
        $show_flags = isset($instance['show_flags']) ? $instance['show_flags'] : 'yes';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'campaign-office'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('type')); ?>"><?php esc_html_e('Type:', 'campaign-office'); ?></label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('type')); ?>" name="<?php echo esc_attr($this->get_field_name('type')); ?>">
                <option value="dropdown" <?php selected($type, 'dropdown'); ?>><?php esc_html_e('Dropdown', 'campaign-office'); ?></option>
                <option value="flags" <?php selected($type, 'flags'); ?>><?php esc_html_e('Flags', 'campaign-office'); ?></option>
            </select>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_names')); ?>" name="<?php echo esc_attr($this->get_field_name('show_names')); ?>" value="yes" <?php checked($show_names, 'yes'); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_names')); ?>"><?php esc_html_e('Show language names', 'campaign-office'); ?></label>
        </p>
        <p>
            <input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show_flags')); ?>" name="<?php echo esc_attr($this->get_field_name('show_flags')); ?>" value="yes" <?php checked($show_flags, 'yes'); ?>>
            <label for="<?php echo esc_attr($this->get_field_id('show_flags')); ?>"><?php esc_html_e('Show flags', 'campaign-office'); ?></label>
        </p>
        <?php
    }

    /**
     * Update widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['type'] = (!empty($new_instance['type'])) ? sanitize_text_field($new_instance['type']) : 'dropdown';
        $instance['show_names'] = (!empty($new_instance['show_names'])) ? 'yes' : 'no';
        $instance['show_flags'] = (!empty($new_instance['show_flags'])) ? 'yes' : 'no';

        return $instance;
    }
}

/**
 * AJAX handler for dismissing translation notice
 */
add_action('wp_ajax_cp_dismiss_translation_notice', function() {
    check_ajax_referer('cp_dismiss_translation_notice', 'nonce');
    update_user_meta(get_current_user_id(), 'cp_translation_notice_dismissed', true);
    wp_send_json_success();
});

// Initialize translation support
new CP_Translation_Support();
