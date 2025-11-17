<?php
/**
 * Theme Options Admin Panel
 *
 * Comprehensive theme options panel for CampaignPress theme customization.
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Theme Options Page
 */
function campaignpress_add_theme_options_page() {
    add_menu_page(
        __( 'CampaignPress Options', 'campaignpress' ),
        __( 'CampaignPress', 'campaignpress' ),
        'manage_options',
        'campaignpress-options',
        'campaignpress_render_theme_options_page',
        'dashicons-megaphone',
        59
    );
}
add_action( 'admin_menu', 'campaignpress_add_theme_options_page' );

/**
 * Register Settings
 */
function campaignpress_register_theme_options() {
    // General Settings
    register_setting( 'campaignpress_general_options', 'campaignpress_candidate_name', 'sanitize_text_field' );
    register_setting( 'campaignpress_general_options', 'campaignpress_office_seeking', 'sanitize_text_field' );
    register_setting( 'campaignpress_general_options', 'campaignpress_campaign_tagline', 'sanitize_text_field' );
    register_setting( 'campaignpress_general_options', 'campaignpress_campaign_year', 'sanitize_text_field' );
    register_setting( 'campaignpress_general_options', 'campaignpress_election_date', 'sanitize_text_field' );
    register_setting( 'campaignpress_general_options', 'campaignpress_donation_url', 'esc_url_raw' );
    register_setting( 'campaignpress_general_options', 'campaignpress_volunteer_url', 'esc_url_raw' );

    // Design Settings
    register_setting( 'campaignpress_design_options', 'campaignpress_color_scheme', 'sanitize_text_field' );
    register_setting( 'campaignpress_design_options', 'campaignpress_primary_color', 'sanitize_hex_color' );
    register_setting( 'campaignpress_design_options', 'campaignpress_secondary_color', 'sanitize_hex_color' );
    register_setting( 'campaignpress_design_options', 'campaignpress_accent_color', 'sanitize_hex_color' );
    register_setting( 'campaignpress_design_options', 'campaignpress_homepage_layout', 'sanitize_text_field' );
    register_setting( 'campaignpress_design_options', 'campaignpress_layout', 'sanitize_text_field' );
    register_setting( 'campaignpress_design_options', 'campaignpress_logo_width', 'absint' );
    register_setting( 'campaignpress_design_options', 'campaignpress_enable_sticky_header', 'absint' );
    register_setting( 'campaignpress_design_options', 'campaignpress_hero_video_url', 'esc_url_raw' );
    register_setting( 'campaignpress_design_options', 'campaignpress_hero_video_type', 'sanitize_text_field' );
    register_setting( 'campaignpress_design_options', 'campaignpress_enable_hero_video', 'absint' );

    // Social Media Settings
    register_setting( 'campaignpress_social_options', 'campaignpress_facebook_url', 'esc_url_raw' );
    register_setting( 'campaignpress_social_options', 'campaignpress_twitter_url', 'esc_url_raw' );
    register_setting( 'campaignpress_social_options', 'campaignpress_instagram_url', 'esc_url_raw' );
    register_setting( 'campaignpress_social_options', 'campaignpress_youtube_url', 'esc_url_raw' );
    register_setting( 'campaignpress_social_options', 'campaignpress_linkedin_url', 'esc_url_raw' );
    register_setting( 'campaignpress_social_options', 'campaignpress_tiktok_url', 'esc_url_raw' );

    // Typography Settings
    register_setting( 'campaignpress_typography_options', 'campaignpress_heading_font', 'sanitize_text_field' );
    register_setting( 'campaignpress_typography_options', 'campaignpress_body_font', 'sanitize_text_field' );
    register_setting( 'campaignpress_typography_options', 'campaignpress_font_size_base', 'absint' );

    // Footer Settings
    register_setting( 'campaignpress_footer_options', 'campaignpress_footer_text', 'wp_kses_post' );
    register_setting( 'campaignpress_footer_options', 'campaignpress_disclaimer_text', 'wp_kses_post' );
    register_setting( 'campaignpress_footer_options', 'campaignpress_show_footer_widgets', 'absint' );

    // Advanced Settings
    register_setting( 'campaignpress_advanced_options', 'campaignpress_custom_css', 'wp_strip_all_tags' );
    register_setting( 'campaignpress_advanced_options', 'campaignpress_google_analytics_id', 'sanitize_text_field' );
    register_setting( 'campaignpress_advanced_options', 'campaignpress_facebook_pixel_id', 'sanitize_text_field' );
    register_setting( 'campaignpress_advanced_options', 'campaignpress_enable_maintenance_mode', 'absint' );
}
add_action( 'admin_init', 'campaignpress_register_theme_options' );

/**
 * Get Default Options
 */
function campaignpress_get_default_options() {
    return array(
        'campaignpress_primary_color' => '#0066cc',
        'campaignpress_secondary_color' => '#333333',
        'campaignpress_accent_color' => '#ff6b35',
        'campaignpress_color_scheme' => 'democrat-blue',
        'campaignpress_homepage_layout' => 'classic-candidate',
        'campaignpress_layout' => 'sidebar-right',
        'campaignpress_logo_width' => 200,
        'campaignpress_font_size_base' => 16,
        'campaignpress_heading_font' => 'system-ui',
        'campaignpress_body_font' => 'system-ui',
        'campaignpress_show_footer_widgets' => 1,
        'campaignpress_enable_sticky_header' => 1,
    );
}

/**
 * Render Theme Options Page
 */
function campaignpress_render_theme_options_page() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Get active tab
    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

    // Save settings message
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error(
            'campaignpress_messages',
            'campaignpress_message',
            __( 'Settings Saved Successfully!', 'campaignpress' ),
            'updated'
        );
    }

    settings_errors( 'campaignpress_messages' );
    ?>

    <div class="wrap campaignpress-options-wrap">
        <div class="campaignpress-header">
            <div class="campaignpress-header-content">
                <h1>
                    <span class="dashicons dashicons-megaphone"></span>
                    <?php echo esc_html( get_admin_page_title() ); ?>
                </h1>
                <p class="campaignpress-subtitle"><?php esc_html_e( 'Customize your campaign website', 'campaignpress' ); ?></p>
            </div>
            <div class="campaignpress-header-actions">
                <button type="button" class="button button-secondary campaignpress-reset-btn" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all settings to defaults?', 'campaignpress' ); ?>');">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php esc_html_e( 'Reset to Defaults', 'campaignpress' ); ?>
                </button>
            </div>
        </div>

        <nav class="nav-tab-wrapper campaignpress-nav-tab-wrapper">
            <a href="?page=campaignpress-options&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-admin-generic"></span>
                <?php esc_html_e( 'General', 'campaignpress' ); ?>
            </a>
            <a href="?page=campaignpress-options&tab=design" class="nav-tab <?php echo $active_tab === 'design' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-art"></span>
                <?php esc_html_e( 'Design', 'campaignpress' ); ?>
            </a>
            <a href="?page=campaignpress-options&tab=typography" class="nav-tab <?php echo $active_tab === 'typography' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-editor-textcolor"></span>
                <?php esc_html_e( 'Typography', 'campaignpress' ); ?>
            </a>
            <a href="?page=campaignpress-options&tab=social" class="nav-tab <?php echo $active_tab === 'social' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-share"></span>
                <?php esc_html_e( 'Social Media', 'campaignpress' ); ?>
            </a>
            <a href="?page=campaignpress-options&tab=footer" class="nav-tab <?php echo $active_tab === 'footer' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-editor-insertmore"></span>
                <?php esc_html_e( 'Footer', 'campaignpress' ); ?>
            </a>
            <a href="?page=campaignpress-options&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e( 'Advanced', 'campaignpress' ); ?>
            </a>
        </nav>

        <div class="campaignpress-content">
            <form method="post" action="options.php" class="campaignpress-form">
                <?php
                switch ( $active_tab ) {
                    case 'general':
                        settings_fields( 'campaignpress_general_options' );
                        campaignpress_render_general_tab();
                        break;
                    case 'design':
                        settings_fields( 'campaignpress_design_options' );
                        campaignpress_render_design_tab();
                        break;
                    case 'typography':
                        settings_fields( 'campaignpress_typography_options' );
                        campaignpress_render_typography_tab();
                        break;
                    case 'social':
                        settings_fields( 'campaignpress_social_options' );
                        campaignpress_render_social_tab();
                        break;
                    case 'footer':
                        settings_fields( 'campaignpress_footer_options' );
                        campaignpress_render_footer_tab();
                        break;
                    case 'advanced':
                        settings_fields( 'campaignpress_advanced_options' );
                        campaignpress_render_advanced_tab();
                        break;
                }
                ?>

                <div class="campaignpress-actions">
                    <?php submit_button( __( 'Save Changes', 'campaignpress' ), 'primary large', 'submit', false ); ?>
                </div>
            </form>
        </div>
    </div>
    <?php
}

/**
 * Render General Tab
 */
function campaignpress_render_general_tab() {
    ?>
    <div class="campaignpress-tab-content">
        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-businessman"></span>
                <?php esc_html_e( 'Campaign Information', 'campaignpress' ); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_candidate_name"><?php esc_html_e( 'Candidate Name', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_candidate_name" name="campaignpress_candidate_name" value="<?php echo esc_attr( get_option( 'campaignpress_candidate_name' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Enter the full name of the candidate', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_office_seeking"><?php esc_html_e( 'Office Seeking', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_office_seeking" name="campaignpress_office_seeking" value="<?php echo esc_attr( get_option( 'campaignpress_office_seeking' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'e.g., "State Senate District 12" or "City Council"', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_campaign_tagline"><?php esc_html_e( 'Campaign Tagline', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_campaign_tagline" name="campaignpress_campaign_tagline" value="<?php echo esc_attr( get_option( 'campaignpress_campaign_tagline' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Your campaign slogan or tagline', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_campaign_year"><?php esc_html_e( 'Campaign Year', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_campaign_year" name="campaignpress_campaign_year" value="<?php echo esc_attr( get_option( 'campaignpress_campaign_year', date( 'Y' ) ) ); ?>" class="small-text">
                        <p class="description"><?php esc_html_e( 'Year of the election', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_election_date"><?php esc_html_e( 'Election Date', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="date" id="campaignpress_election_date" name="campaignpress_election_date" value="<?php echo esc_attr( get_option( 'campaignpress_election_date' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Date of the election', 'campaignpress' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-money-alt"></span>
                <?php esc_html_e( 'Action URLs', 'campaignpress' ); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_donation_url"><?php esc_html_e( 'Donation Page URL', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_donation_url" name="campaignpress_donation_url" value="<?php echo esc_url( get_option( 'campaignpress_donation_url' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Link to ActBlue, WinRed, or your donation platform', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_volunteer_url"><?php esc_html_e( 'Volunteer Signup URL', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_volunteer_url" name="campaignpress_volunteer_url" value="<?php echo esc_url( get_option( 'campaignpress_volunteer_url' ) ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'Link to volunteer signup form', 'campaignpress' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Render Design Tab
 */
function campaignpress_render_design_tab() {
    $defaults = campaignpress_get_default_options();
    ?>
    <div class="campaignpress-tab-content">
        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-admin-appearance"></span>
                <?php esc_html_e( 'Color Scheme', 'campaignpress' ); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_color_scheme"><?php esc_html_e( 'Preset Color Scheme', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <select id="campaignpress_color_scheme" name="campaignpress_color_scheme" class="regular-text">
                            <option value="democrat-blue" <?php selected( get_option( 'campaignpress_color_scheme', $defaults['campaignpress_color_scheme'] ), 'democrat-blue' ); ?>><?php esc_html_e( 'Democrat Blue', 'campaignpress' ); ?></option>
                            <option value="republican-red" <?php selected( get_option( 'campaignpress_color_scheme' ), 'republican-red' ); ?>><?php esc_html_e( 'Republican Red', 'campaignpress' ); ?></option>
                            <option value="independent-purple" <?php selected( get_option( 'campaignpress_color_scheme' ), 'independent-purple' ); ?>><?php esc_html_e( 'Independent Purple', 'campaignpress' ); ?></option>
                            <option value="green-party" <?php selected( get_option( 'campaignpress_color_scheme' ), 'green-party' ); ?>><?php esc_html_e( 'Green Party', 'campaignpress' ); ?></option>
                            <option value="neutral" <?php selected( get_option( 'campaignpress_color_scheme' ), 'neutral' ); ?>><?php esc_html_e( 'Neutral', 'campaignpress' ); ?></option>
                            <option value="custom" <?php selected( get_option( 'campaignpress_color_scheme' ), 'custom' ); ?>><?php esc_html_e( 'Custom', 'campaignpress' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose a preset color scheme or select Custom to use your own colors', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_primary_color"><?php esc_html_e( 'Primary Color', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_primary_color" name="campaignpress_primary_color" value="<?php echo esc_attr( get_option( 'campaignpress_primary_color', $defaults['campaignpress_primary_color'] ) ); ?>" class="cp-color-picker">
                        <p class="description"><?php esc_html_e( 'Main brand color used for headers, buttons, and accents', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_secondary_color"><?php esc_html_e( 'Secondary Color', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_secondary_color" name="campaignpress_secondary_color" value="<?php echo esc_attr( get_option( 'campaignpress_secondary_color', $defaults['campaignpress_secondary_color'] ) ); ?>" class="cp-color-picker">
                        <p class="description"><?php esc_html_e( 'Secondary color for text and backgrounds', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_accent_color"><?php esc_html_e( 'Accent Color', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_accent_color" name="campaignpress_accent_color" value="<?php echo esc_attr( get_option( 'campaignpress_accent_color', $defaults['campaignpress_accent_color'] ) ); ?>" class="cp-color-picker">
                        <p class="description"><?php esc_html_e( 'Accent color for call-to-action elements', 'campaignpress' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-layout"></span>
                <?php esc_html_e( 'Layout Options', 'campaignpress' ); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_homepage_layout"><?php esc_html_e( 'Homepage Layout', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <select id="campaignpress_homepage_layout" name="campaignpress_homepage_layout" class="regular-text">
                            <option value="classic-candidate" <?php selected( get_option( 'campaignpress_homepage_layout', $defaults['campaignpress_homepage_layout'] ), 'classic-candidate' ); ?>><?php esc_html_e( 'Classic Candidate', 'campaignpress' ); ?></option>
                            <option value="modern-progressive" <?php selected( get_option( 'campaignpress_homepage_layout' ), 'modern-progressive' ); ?>><?php esc_html_e( 'Modern Progressive', 'campaignpress' ); ?></option>
                            <option value="conservative-traditional" <?php selected( get_option( 'campaignpress_homepage_layout' ), 'conservative-traditional' ); ?>><?php esc_html_e( 'Conservative Traditional', 'campaignpress' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Choose the homepage layout style', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_layout"><?php esc_html_e( 'Site Layout', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <select id="campaignpress_layout" name="campaignpress_layout" class="regular-text">
                            <option value="sidebar-right" <?php selected( get_option( 'campaignpress_layout', $defaults['campaignpress_layout'] ), 'sidebar-right' ); ?>><?php esc_html_e( 'Sidebar Right', 'campaignpress' ); ?></option>
                            <option value="sidebar-left" <?php selected( get_option( 'campaignpress_layout' ), 'sidebar-left' ); ?>><?php esc_html_e( 'Sidebar Left', 'campaignpress' ); ?></option>
                            <option value="no-sidebar" <?php selected( get_option( 'campaignpress_layout' ), 'no-sidebar' ); ?>><?php esc_html_e( 'No Sidebar (Full Width)', 'campaignpress' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Default layout for pages and posts', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_logo_width"><?php esc_html_e( 'Logo Width (px)', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="campaignpress_logo_width" name="campaignpress_logo_width" value="<?php echo esc_attr( get_option( 'campaignpress_logo_width', $defaults['campaignpress_logo_width'] ) ); ?>" class="small-text" min="50" max="500">
                        <p class="description"><?php esc_html_e( 'Maximum width of your logo in pixels', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_enable_sticky_header"><?php esc_html_e( 'Sticky Header', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="campaignpress_enable_sticky_header" name="campaignpress_enable_sticky_header" value="1" <?php checked( get_option( 'campaignpress_enable_sticky_header', $defaults['campaignpress_enable_sticky_header'] ), 1 ); ?>>
                            <?php esc_html_e( 'Enable sticky header (header stays visible when scrolling)', 'campaignpress' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-video-alt3"></span>
                <?php esc_html_e( 'Hero Video Overlay', 'campaignpress' ); ?>
            </h2>
            <p class="section-description"><?php esc_html_e( 'Set a default video overlay for hero sections. This can be overridden on individual pages.', 'campaignpress' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_enable_hero_video"><?php esc_html_e( 'Enable Hero Video', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="campaignpress_enable_hero_video" name="campaignpress_enable_hero_video" value="1" <?php checked( get_option( 'campaignpress_enable_hero_video' ), 1 ); ?>>
                            <?php esc_html_e( 'Use video overlay for hero sections by default', 'campaignpress' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_hero_video_url"><?php esc_html_e( 'Default Hero Video URL', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_hero_video_url" name="campaignpress_hero_video_url" value="<?php echo esc_url( get_option( 'campaignpress_hero_video_url' ) ); ?>" class="regular-text" placeholder="https://example.com/video.mp4">
                        <p class="description"><?php esc_html_e( 'URL to your default hero video. Recommended formats: MP4, WebM. Keep file size under 5MB for best performance.', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_hero_video_type"><?php esc_html_e( 'Video Format', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <select id="campaignpress_hero_video_type" name="campaignpress_hero_video_type" class="regular-text">
                            <option value="video/mp4" <?php selected( get_option( 'campaignpress_hero_video_type', 'video/mp4' ), 'video/mp4' ); ?>><?php esc_html_e( 'MP4 (video/mp4)', 'campaignpress' ); ?></option>
                            <option value="video/webm" <?php selected( get_option( 'campaignpress_hero_video_type' ), 'video/webm' ); ?>><?php esc_html_e( 'WebM (video/webm)', 'campaignpress' ); ?></option>
                            <option value="video/ogg" <?php selected( get_option( 'campaignpress_hero_video_type' ), 'video/ogg' ); ?>><?php esc_html_e( 'OGG (video/ogg)', 'campaignpress' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Select the video MIME type that matches your video file format.', 'campaignpress' ); ?></p>
                    </td>
                </tr>
            </table>

            <div class="notice notice-info inline" style="margin: 20px 0;">
                <p>
                    <strong><?php esc_html_e( 'Note:', 'campaignpress' ); ?></strong>
                    <?php esc_html_e( 'Individual pages can override this default by setting their own video in the "Hero Video Overlay" meta box when editing the page.', 'campaignpress' ); ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Typography Tab
 */
function campaignpress_render_typography_tab() {
    $defaults = campaignpress_get_default_options();
    ?>
    <div class="campaignpress-tab-content">
        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-editor-textcolor"></span>
                <?php esc_html_e( 'Font Settings', 'campaignpress' ); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_heading_font"><?php esc_html_e( 'Heading Font', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <select id="campaignpress_heading_font" name="campaignpress_heading_font" class="regular-text">
                            <option value="system-ui" <?php selected( get_option( 'campaignpress_heading_font', $defaults['campaignpress_heading_font'] ), 'system-ui' ); ?>><?php esc_html_e( 'System UI (Fast & Modern)', 'campaignpress' ); ?></option>
                            <option value="Georgia, serif" <?php selected( get_option( 'campaignpress_heading_font' ), 'Georgia, serif' ); ?>><?php esc_html_e( 'Georgia (Classic Serif)', 'campaignpress' ); ?></option>
                            <option value="'Times New Roman', serif" <?php selected( get_option( 'campaignpress_heading_font' ), "'Times New Roman', serif" ); ?>><?php esc_html_e( 'Times New Roman (Traditional)', 'campaignpress' ); ?></option>
                            <option value="Arial, sans-serif" <?php selected( get_option( 'campaignpress_heading_font' ), 'Arial, sans-serif' ); ?>><?php esc_html_e( 'Arial (Clean Sans-Serif)', 'campaignpress' ); ?></option>
                            <option value="'Helvetica Neue', sans-serif" <?php selected( get_option( 'campaignpress_heading_font' ), "'Helvetica Neue', sans-serif" ); ?>><?php esc_html_e( 'Helvetica (Professional)', 'campaignpress' ); ?></option>
                            <option value="'Montserrat', sans-serif" <?php selected( get_option( 'campaignpress_heading_font' ), "'Montserrat', sans-serif" ); ?>><?php esc_html_e( 'Montserrat (Bold & Modern)', 'campaignpress' ); ?></option>
                            <option value="'Playfair Display', serif" <?php selected( get_option( 'campaignpress_heading_font' ), "'Playfair Display', serif" ); ?>><?php esc_html_e( 'Playfair Display (Elegant)', 'campaignpress' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Font family for headings (H1, H2, H3, etc.)', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_body_font"><?php esc_html_e( 'Body Font', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <select id="campaignpress_body_font" name="campaignpress_body_font" class="regular-text">
                            <option value="system-ui" <?php selected( get_option( 'campaignpress_body_font', $defaults['campaignpress_body_font'] ), 'system-ui' ); ?>><?php esc_html_e( 'System UI (Fast & Modern)', 'campaignpress' ); ?></option>
                            <option value="Georgia, serif" <?php selected( get_option( 'campaignpress_body_font' ), 'Georgia, serif' ); ?>><?php esc_html_e( 'Georgia (Classic Serif)', 'campaignpress' ); ?></option>
                            <option value="Arial, sans-serif" <?php selected( get_option( 'campaignpress_body_font' ), 'Arial, sans-serif' ); ?>><?php esc_html_e( 'Arial (Clean Sans-Serif)', 'campaignpress' ); ?></option>
                            <option value="'Helvetica Neue', sans-serif" <?php selected( get_option( 'campaignpress_body_font' ), "'Helvetica Neue', sans-serif" ); ?>><?php esc_html_e( 'Helvetica (Professional)', 'campaignpress' ); ?></option>
                            <option value="'Open Sans', sans-serif" <?php selected( get_option( 'campaignpress_body_font' ), "'Open Sans', sans-serif" ); ?>><?php esc_html_e( 'Open Sans (Readable)', 'campaignpress' ); ?></option>
                            <option value="'Lato', sans-serif" <?php selected( get_option( 'campaignpress_body_font' ), "'Lato', sans-serif" ); ?>><?php esc_html_e( 'Lato (Friendly)', 'campaignpress' ); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e( 'Font family for body text', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_font_size_base"><?php esc_html_e( 'Base Font Size (px)', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="number" id="campaignpress_font_size_base" name="campaignpress_font_size_base" value="<?php echo esc_attr( get_option( 'campaignpress_font_size_base', $defaults['campaignpress_font_size_base'] ) ); ?>" class="small-text" min="12" max="24">
                        <p class="description"><?php esc_html_e( 'Base font size in pixels (recommended: 16)', 'campaignpress' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Render Social Media Tab
 */
function campaignpress_render_social_tab() {
    ?>
    <div class="campaignpress-tab-content">
        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-share"></span>
                <?php esc_html_e( 'Social Media Links', 'campaignpress' ); ?>
            </h2>
            <p class="section-description"><?php esc_html_e( 'Add your social media profile URLs. Leave blank to hide icons.', 'campaignpress' ); ?></p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_facebook_url">
                            <span class="dashicons dashicons-facebook"></span>
                            <?php esc_html_e( 'Facebook', 'campaignpress' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_facebook_url" name="campaignpress_facebook_url" value="<?php echo esc_url( get_option( 'campaignpress_facebook_url' ) ); ?>" class="regular-text" placeholder="https://facebook.com/yourpage">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_twitter_url">
                            <span class="dashicons dashicons-twitter"></span>
                            <?php esc_html_e( 'Twitter/X', 'campaignpress' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_twitter_url" name="campaignpress_twitter_url" value="<?php echo esc_url( get_option( 'campaignpress_twitter_url' ) ); ?>" class="regular-text" placeholder="https://twitter.com/yourhandle">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_instagram_url">
                            <span class="dashicons dashicons-instagram"></span>
                            <?php esc_html_e( 'Instagram', 'campaignpress' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_instagram_url" name="campaignpress_instagram_url" value="<?php echo esc_url( get_option( 'campaignpress_instagram_url' ) ); ?>" class="regular-text" placeholder="https://instagram.com/yourhandle">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_youtube_url">
                            <span class="dashicons dashicons-video-alt3"></span>
                            <?php esc_html_e( 'YouTube', 'campaignpress' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_youtube_url" name="campaignpress_youtube_url" value="<?php echo esc_url( get_option( 'campaignpress_youtube_url' ) ); ?>" class="regular-text" placeholder="https://youtube.com/@yourchannel">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_linkedin_url">
                            <span class="dashicons dashicons-linkedin"></span>
                            <?php esc_html_e( 'LinkedIn', 'campaignpress' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_linkedin_url" name="campaignpress_linkedin_url" value="<?php echo esc_url( get_option( 'campaignpress_linkedin_url' ) ); ?>" class="regular-text" placeholder="https://linkedin.com/in/yourprofile">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_tiktok_url">
                            <span class="dashicons dashicons-video-alt2"></span>
                            <?php esc_html_e( 'TikTok', 'campaignpress' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="url" id="campaignpress_tiktok_url" name="campaignpress_tiktok_url" value="<?php echo esc_url( get_option( 'campaignpress_tiktok_url' ) ); ?>" class="regular-text" placeholder="https://tiktok.com/@yourhandle">
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Render Footer Tab
 */
function campaignpress_render_footer_tab() {
    $defaults = campaignpress_get_default_options();
    ?>
    <div class="campaignpress-tab-content">
        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-editor-insertmore"></span>
                <?php esc_html_e( 'Footer Settings', 'campaignpress' ); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_show_footer_widgets"><?php esc_html_e( 'Footer Widgets', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="campaignpress_show_footer_widgets" name="campaignpress_show_footer_widgets" value="1" <?php checked( get_option( 'campaignpress_show_footer_widgets', $defaults['campaignpress_show_footer_widgets'] ), 1 ); ?>>
                            <?php esc_html_e( 'Show footer widget areas', 'campaignpress' ); ?>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_footer_text"><?php esc_html_e( 'Footer Text', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <?php
                        wp_editor(
                            get_option( 'campaignpress_footer_text' ),
                            'campaignpress_footer_text',
                            array(
                                'textarea_name' => 'campaignpress_footer_text',
                                'textarea_rows' => 5,
                                'media_buttons' => false,
                                'teeny' => true,
                            )
                        );
                        ?>
                        <p class="description"><?php esc_html_e( 'Custom text to display in the footer', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_disclaimer_text"><?php esc_html_e( 'Legal Disclaimer', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <?php
                        wp_editor(
                            get_option( 'campaignpress_disclaimer_text' ),
                            'campaignpress_disclaimer_text',
                            array(
                                'textarea_name' => 'campaignpress_disclaimer_text',
                                'textarea_rows' => 3,
                                'media_buttons' => false,
                                'teeny' => true,
                            )
                        );
                        ?>
                        <p class="description"><?php esc_html_e( 'Legal disclaimer required by campaign finance laws (e.g., "Paid for by...")', 'campaignpress' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Render Advanced Tab
 */
function campaignpress_render_advanced_tab() {
    ?>
    <div class="campaignpress-tab-content">
        <div class="campaignpress-section">
            <h2 class="section-title">
                <span class="dashicons dashicons-admin-tools"></span>
                <?php esc_html_e( 'Advanced Settings', 'campaignpress' ); ?>
            </h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="campaignpress_custom_css"><?php esc_html_e( 'Custom CSS', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <textarea id="campaignpress_custom_css" name="campaignpress_custom_css" rows="10" class="large-text code"><?php echo esc_textarea( get_option( 'campaignpress_custom_css' ) ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Add custom CSS to override theme styles', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_google_analytics_id"><?php esc_html_e( 'Google Analytics ID', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_google_analytics_id" name="campaignpress_google_analytics_id" value="<?php echo esc_attr( get_option( 'campaignpress_google_analytics_id' ) ); ?>" class="regular-text" placeholder="G-XXXXXXXXXX">
                        <p class="description"><?php esc_html_e( 'Google Analytics 4 Measurement ID', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_facebook_pixel_id"><?php esc_html_e( 'Facebook Pixel ID', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="campaignpress_facebook_pixel_id" name="campaignpress_facebook_pixel_id" value="<?php echo esc_attr( get_option( 'campaignpress_facebook_pixel_id' ) ); ?>" class="regular-text" placeholder="123456789012345">
                        <p class="description"><?php esc_html_e( 'Facebook Pixel ID for conversion tracking', 'campaignpress' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="campaignpress_enable_maintenance_mode"><?php esc_html_e( 'Maintenance Mode', 'campaignpress' ); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="campaignpress_enable_maintenance_mode" name="campaignpress_enable_maintenance_mode" value="1" <?php checked( get_option( 'campaignpress_enable_maintenance_mode' ), 1 ); ?>>
                            <?php esc_html_e( 'Enable maintenance mode (only admins can view the site)', 'campaignpress' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Enqueue Admin Styles and Scripts
 */
function campaignpress_enqueue_admin_scripts( $hook ) {
    // Only load on our theme options page
    if ( 'toplevel_page_campaignpress-options' !== $hook ) {
        return;
    }

    // Enqueue WordPress color picker
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );

    // Enqueue custom admin styles
    wp_enqueue_style(
        'campaignpress-admin-options',
        get_template_directory_uri() . '/assets/css/admin-options.css',
        array(),
        '1.0.0'
    );

    // Enqueue custom admin scripts
    wp_enqueue_script(
        'campaignpress-admin-options',
        get_template_directory_uri() . '/assets/js/admin-options.js',
        array( 'jquery', 'wp-color-picker' ),
        '1.0.0',
        true
    );
}
add_action( 'admin_enqueue_scripts', 'campaignpress_enqueue_admin_scripts' );

/**
 * Output Custom CSS in Frontend
 */
function campaignpress_output_custom_css() {
    $custom_css = get_option( 'campaignpress_custom_css' );

    if ( ! empty( $custom_css ) ) {
        echo '<style type="text/css" id="campaignpress-custom-css">' . "\n";
        echo wp_strip_all_tags( $custom_css ) . "\n";
        echo '</style>' . "\n";
    }
}
add_action( 'wp_head', 'campaignpress_output_custom_css', 100 );

/**
 * Add Google Analytics
 */
function campaignpress_add_google_analytics() {
    $ga_id = get_option( 'campaignpress_google_analytics_id' );

    if ( ! empty( $ga_id ) && ! current_user_can( 'manage_options' ) ) {
        ?>
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_id ); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo esc_js( $ga_id ); ?>');
        </script>
        <?php
    }
}
add_action( 'wp_head', 'campaignpress_add_google_analytics' );

/**
 * Add Facebook Pixel
 */
function campaignpress_add_facebook_pixel() {
    $pixel_id = get_option( 'campaignpress_facebook_pixel_id' );

    if ( ! empty( $pixel_id ) && ! current_user_can( 'manage_options' ) ) {
        ?>
        <!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo esc_js( $pixel_id ); ?>');
            fbq('track', 'PageView');
        </script>
        <noscript>
            <img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $pixel_id ); ?>&ev=PageView&noscript=1"/>
        </noscript>
        <?php
    }
}
add_action( 'wp_head', 'campaignpress_add_facebook_pixel' );
