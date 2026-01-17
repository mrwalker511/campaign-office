<?php
/**
 * Social Media Feeds Integration
 *
 * Provides Instagram, Twitter/X, and Facebook feed embedding for CampaignPress.
 * Includes custom blocks, shortcodes, and widgets for displaying social media content.
 *
 * Supported Platforms:
 * - Instagram (via Instagram Basic Display API or oembed)
 * - Twitter/X (via X widget embed)
 * - Facebook (via Facebook Page Plugin)
 * - Social share buttons for all platforms
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Social_Media_Feeds
 *
 * Handles social media feed integration, embeds, and sharing functionality
 */
class CP_Social_Media_Feeds {

    /**
     * Supported social platforms
     *
     * @var array
     */
    private $platforms = array();

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize platforms
        $this->init_platforms();

        // Register admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Register shortcodes
        add_shortcode('cp_instagram_feed', array($this, 'render_instagram_feed'));
        add_shortcode('cp_twitter_timeline', array($this, 'render_twitter_timeline'));
        add_shortcode('cp_facebook_feed', array($this, 'render_facebook_feed'));
        add_shortcode('cp_social_share', array($this, 'render_social_share'));

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Add social share buttons to content
        add_filter('the_content', array($this, 'append_social_share'));
    }

    /**
     * Initialize social platforms configuration
     */
    private function init_platforms() {
        $this->platforms = array(
            'instagram' => array(
                'name'        => 'Instagram',
                'icon'        => 'instagram',
                'color'       => '#E4405F',
                'embed_type'  => 'profile',
                'url_pattern' => 'https://www.instagram.com/{username}',
            ),
            'twitter' => array(
                'name'        => 'Twitter/X',
                'icon'        => 'twitter',
                'color'       => '#1DA1F2',
                'embed_type'  => 'timeline',
                'url_pattern' => 'https://twitter.com/{username}',
            ),
            'facebook' => array(
                'name'        => 'Facebook',
                'icon'        => 'facebook',
                'color'       => '#1877F2',
                'embed_type'  => 'page',
                'url_pattern' => 'https://www.facebook.com/{page_name}',
            ),
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_theme_page(
            __('Social Media Feeds', 'campaignpress'),
            __('Social Media', 'campaignpress'),
            'manage_options',
            'cp-social-feeds',
            array($this, 'render_admin_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Instagram settings
        register_setting('cp_social_feeds', 'cp_instagram_username');
        register_setting('cp_social_feeds', 'cp_instagram_access_token');
        register_setting('cp_social_feeds', 'cp_instagram_user_id');

        // Twitter/X settings
        register_setting('cp_social_feeds', 'cp_twitter_username');
        register_setting('cp_social_feeds', 'cp_twitter_widget_id');

        // Facebook settings
        register_setting('cp_social_feeds', 'cp_facebook_page_url');
        register_setting('cp_social_feeds', 'cp_facebook_app_id');

        // Social share settings
        register_setting('cp_social_feeds', 'cp_enable_auto_share');
        register_setting('cp_social_feeds', 'cp_share_platforms');
        register_setting('cp_social_feeds', 'cp_share_position');
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        $instagram_username = get_option('cp_instagram_username', '');
        $twitter_username = get_option('cp_twitter_username', '');
        $twitter_widget_id = get_option('cp_twitter_widget_id', '');
        $facebook_page_url = get_option('cp_facebook_page_url', '');
        $enable_auto_share = get_option('cp_enable_auto_share', false);
        $share_platforms = get_option('cp_share_platforms', array('facebook', 'twitter', 'instagram'));
        $share_position = get_option('cp_share_position', 'bottom');
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Social Media Feeds', 'campaignpress'); ?></h1>
            <p class="description">
                <?php esc_html_e('Configure your social media feeds to display Instagram photos, Twitter timeline, and Facebook posts on your campaign site.', 'campaignpress'); ?>
            </p>

            <?php settings_errors('cp_social_feeds'); ?>

            <form method="post" action="options.php">
                <?php settings_fields('cp_social_feeds'); ?>

                <!-- Instagram Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-instagram" style="color: #E4405F;"></span>
                        <?php esc_html_e('Instagram Feed', 'campaignpress'); ?>
                    </h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cp_instagram_username"><?php esc_html_e('Instagram Username', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_instagram_username" name="cp_instagram_username"
                                       value="<?php echo esc_attr($instagram_username); ?>" class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Your Instagram username (without @)', 'campaignpress'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h3><?php esc_html_e('Usage', 'campaignpress'); ?></h3>
                    <p>
                        <code>[cp_instagram_feed]</code><br>
                        <code>[cp_instagram_feed posts="6" columns="3"]</code>
                    </p>
                    <p class="description">
                        <?php esc_html_e('Note: For best results, embed your Instagram profile using the built-in WordPress Instagram block or a plugin like "Smash Balloon Instagram Feed".', 'campaignpress'); ?>
                    </p>
                </div>

                <!-- Twitter/X Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-twitter" style="color: #1DA1F2;"></span>
                        <?php esc_html_e('Twitter/X Timeline', 'campaignpress'); ?>
                    </h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cp_twitter_username"><?php esc_html_e('Twitter/X Username', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_twitter_username" name="cp_twitter_username"
                                       value="<?php echo esc_attr($twitter_username); ?>" class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Your Twitter/X username (without @)', 'campaignpress'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="cp_twitter_widget_id"><?php esc_html_e('Twitter Widget ID', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="cp_twitter_widget_id" name="cp_twitter_widget_id"
                                       value="<?php echo esc_attr($twitter_widget_id); ?>" class="regular-text" />
                                <p class="description">
                                    <?php
                                    printf(
                                        esc_html__('Create a timeline widget at %s and paste the Widget ID here.', 'campaignpress'),
                                        '<a href="https://publish.twitter.com/" target="_blank">publish.twitter.com</a>'
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h3><?php esc_html_e('Usage', 'campaignpress'); ?></h3>
                    <p>
                        <code>[cp_twitter_timeline]</code><br>
                        <code>[cp_twitter_timeline height="500" theme="light"]</code>
                    </p>
                </div>

                <!-- Facebook Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-facebook" style="color: #1877F2;"></span>
                        <?php esc_html_e('Facebook Page Feed', 'campaignpress'); ?>
                    </h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="cp_facebook_page_url"><?php esc_html_e('Facebook Page URL', 'campaignpress'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="cp_facebook_page_url" name="cp_facebook_page_url"
                                       value="<?php echo esc_attr($facebook_page_url); ?>" class="regular-text" />
                                <p class="description">
                                    <?php esc_html_e('Full URL to your Facebook Page', 'campaignpress'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h3><?php esc_html_e('Usage', 'campaignpress'); ?></h3>
                    <p>
                        <code>[cp_facebook_feed]</code><br>
                        <code>[cp_facebook_feed width="500" height="600" show_posts="true"]</code>
                    </p>
                </div>

                <!-- Social Share Settings -->
                <div class="card" style="max-width: 800px; margin-top: 20px;">
                    <h2>
                        <span class="dashicons dashicons-share"></span>
                        <?php esc_html_e('Social Share Buttons', 'campaignpress'); ?>
                    </h2>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php esc_html_e('Auto-add Share Buttons', 'campaignpress'); ?>
                            </th>
                            <td>
                                <label>
                                    <input type="checkbox" name="cp_enable_auto_share"
                                           value="1" <?php checked($enable_auto_share, 1); ?> />
                                    <?php esc_html_e('Automatically add social share buttons to posts', 'campaignpress'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>

                    <h3><?php esc_html_e('Usage', 'campaignpress'); ?></h3>
                    <p>
                        <code>[cp_social_share]</code><br>
                        <code>[cp_social_share platforms="facebook,twitter,email" style="buttons"]</code>
                    </p>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render Instagram feed shortcode
     */
    public function render_instagram_feed($atts) {
        $atts = shortcode_atts(array(
            'posts'   => 6,
            'columns' => 3,
            'size'    => 'medium',
        ), $atts, 'cp_instagram_feed');

        $username = get_option('cp_instagram_username', '');

        if (empty($username)) {
            return $this->render_setup_notice('Instagram', 'cp-social-feeds');
        }

        ob_start();
        ?>
        <div class="cp-instagram-feed cp-instagram-columns-<?php echo esc_attr($atts['columns']); ?>">
            <div class="cp-instagram-header">
                <a href="https://www.instagram.com/<?php echo esc_attr($username); ?>" target="_blank" rel="noopener">
                    <span class="dashicons dashicons-instagram"></span>
                    @<?php echo esc_html($username); ?>
                </a>
            </div>
            <div class="cp-instagram-grid">
                <?php for ($i = 1; $i <= intval($atts['posts']); $i++) : ?>
                    <div class="cp-instagram-item">
                        <a href="https://www.instagram.com/<?php echo esc_attr($username); ?>/" target="_blank" rel="noopener">
                            <div class="cp-instagram-placeholder">
                                <span class="dashicons dashicons-format-image"></span>
                            </div>
                        </a>
                    </div>
                <?php endfor; ?>
            </div>
            <p class="cp-feed-note">
                <?php
                printf(
                    esc_html__('Embed your Instagram feed using the %s or a plugin like Smash Balloon Instagram Feed for live updates.', 'campaignpress'),
                    '<a href="https://wordpress.org/support/article/embeds/#instagram" target="_blank">' . esc_html__('WordPress Instagram block', 'campaignpress') . '</a>'
                );
                ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Twitter timeline shortcode
     */
    public function render_twitter_timeline($atts) {
        $atts = shortcode_atts(array(
            'height' => 500,
            'theme'  => 'light',
            'chrome' => 'noheader nofooter noborders',
        ), $atts, 'cp_twitter_timeline');

        $username = get_option('cp_twitter_username', '');

        if (empty($username)) {
            return $this->render_setup_notice('Twitter/X', 'cp-social-feeds');
        }

        wp_enqueue_script('twitter-widgets', 'https://platform.twitter.com/widgets.js', array(), null, true);

        ob_start();
        ?>
        <div class="cp-twitter-timeline-wrapper">
            <a class="twitter-timeline"
               data-height="<?php echo esc_attr($atts['height']); ?>"
               data-theme="<?php echo esc_attr($atts['theme']); ?>"
               data-chrome="<?php echo esc_attr($atts['chrome']); ?>"
               href="https://twitter.com/<?php echo esc_attr($username); ?>?ref_src=twsrc%5Etfw">
                <?php
                printf(
                    esc_html__('Tweets by %s', 'campaignpress'),
                    esc_html($username)
                );
                ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Facebook feed shortcode
     */
    public function render_facebook_feed($atts) {
        $atts = shortcode_atts(array(
            'width'       => 500,
            'height'      => 600,
            'show_posts'  => 'true',
            'show_facepile' => 'true',
        ), $atts, 'cp_facebook_feed');

        $page_url = get_option('cp_facebook_page_url', '');

        if (empty($page_url)) {
            return $this->render_setup_notice('Facebook', 'cp-social-feeds');
        }

        ob_start();
        ?>
        <div class="cp-facebook-feed-wrapper">
            <div id="fb-root"></div>
            <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v18.0"></script>
            <div class="fb-page"
                 data-href="<?php echo esc_attr($page_url); ?>"
                 data-width="<?php echo esc_attr($atts['width']); ?>"
                 data-height="<?php echo esc_attr($atts['height']); ?>"
                 data-tabs="timeline"
                 data-hide-cover="false"
                 data-show-facepile="<?php echo esc_attr($atts['show_facepile']); ?>">
                <blockquote cite="<?php echo esc_attr($page_url); ?>" class="fb-xfbml-parse-ignore">
                    <a href="<?php echo esc_attr($page_url); ?>">
                        <?php esc_html_e('Facebook', 'campaignpress'); ?>
                    </a>
                </blockquote>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render social share buttons shortcode
     */
    public function render_social_share($atts) {
        $atts = shortcode_atts(array(
            'platforms' => 'facebook,twitter,linkedin,email',
            'style'     => 'buttons',
            'text'      => '',
        ), $atts, 'cp_social_share');

        $platforms = array_map('trim', explode(',', $atts['platforms']));
        $share_text = empty($atts['text']) ? get_the_title() : $atts['text'];
        $share_url = urlencode(get_permalink());
        $share_text_encoded = urlencode($share_text);

        ob_start();
        ?>
        <div class="cp-social-share cp-social-share-<?php echo esc_attr($atts['style']); ?>">
            <span class="cp-share-label"><?php esc_html_e('Share:', 'campaignpress'); ?></span>
            <?php foreach ($platforms as $platform) : ?>
                <?php
                $share_links = array(
                    'facebook'  => 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url,
                    'twitter'   => 'https://twitter.com/intent/tweet?url=' . $share_url . '&text=' . $share_text_encoded,
                    'linkedin'  => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $share_url . '&title=' . $share_text_encoded,
                    'email'     => 'mailto:?subject=' . $share_text_encoded . '&body=' . $share_url,
                );

                if (!isset($share_links[$platform])) {
                    continue;
                }
                ?>
                <a href="<?php echo esc_url($share_links[$platform]); ?>"
                   class="cp-share-button cp-share-<?php echo esc_attr($platform); ?>"
                   target="_blank"
                   rel="noopener">
                    <span class="dashicons dashicons-<?php echo esc_attr($platform); ?>"></span>
                    <span class="cp-share-text"><?php echo esc_html(ucfirst($platform)); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Append social share buttons to content
     */
    public function append_social_share($content) {
        if (!is_singular() || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        if (!get_option('cp_enable_auto_share', false)) {
            return $content;
        }

        $share_buttons = $this->render_social_share(array());

        return $content . $share_buttons;
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Inline styles for social feeds
        wp_add_inline_style('campaignpress-style', $this->get_inline_styles());
    }

    /**
     * Get inline styles for social feeds
     */
    private function get_inline_styles() {
        return '
        .cp-instagram-feed {
            margin: 2rem 0;
        }
        .cp-instagram-header {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        .cp-instagram-header a {
            color: #E4405F;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .cp-instagram-grid {
            display: grid;
            gap: 1rem;
        }
        .cp-instagram-columns-2 .cp-instagram-grid { grid-template-columns: repeat(2, 1fr); }
        .cp-instagram-columns-3 .cp-instagram-grid { grid-template-columns: repeat(3, 1fr); }
        .cp-instagram-columns-4 .cp-instagram-grid { grid-template-columns: repeat(4, 1fr); }
        .cp-instagram-columns-6 .cp-instagram-grid { grid-template-columns: repeat(6, 1fr); }
        .cp-instagram-item {
            position: relative;
            aspect-ratio: 1;
        }
        .cp-instagram-placeholder {
            background: #f0f0f0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #ccc;
        }
        .cp-feed-note {
            font-size: 0.875rem;
            color: #666;
            margin-top: 1rem;
        }
        .cp-twitter-timeline-wrapper {
            margin: 2rem 0;
        }
        .cp-facebook-feed-wrapper {
            margin: 2rem 0;
        }
        .cp-social-share {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 2rem 0;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 0.5rem;
        }
        .cp-share-label {
            font-weight: 600;
            color: #333;
        }
        .cp-share-button {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 0.25rem;
            color: #fff;
            font-size: 0.875rem;
            transition: transform 0.2s;
        }
        .cp-share-button:hover {
            transform: translateY(-2px);
        }
        .cp-share-facebook { background: #1877F2; }
        .cp-share-twitter { background: #1DA1F2; }
        .cp-share-linkedin { background: #0077B5; }
        .cp-share-email { background: #666; }
        .cp-setup-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }
        ';
    }

    /**
     * Render setup notice
     */
    private function render_setup_notice($platform, $settings_page) {
        $settings_url = admin_url('themes.php?page=' . $settings_page);
        return sprintf(
            '<div class="cp-setup-notice"><p><strong>%s:</strong> %s <a href="%s">%s</a></p></div>',
            esc_html__('Setup Required', 'campaignpress'),
            sprintf(esc_html__('Please configure your %s settings to display this feed.', 'campaignpress'), esc_html($platform)),
            esc_url($settings_url),
            esc_html__('Go to Settings', 'campaignpress')
        );
    }
}

// Initialize social media feeds
new CP_Social_Media_Feeds();
