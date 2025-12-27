<?php
/**
 * Admin Menu Reorganization
 * 
 * Reorganizes the WordPress admin sidebar to create a premium, less cluttered experience.
 * - Creates a 'Campaign Data' container menu
 * - Moves CPTs under the container
 * - Enforces submenu order
 * - Adds custom icons to submenus
 * - Cleans up other menu items
 * 
 * @package CampaignPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the Campaign Data top-level menu page
 */
function cp_register_campaign_data_menu() {
    add_menu_page(
        __('Campaign Data', 'campaign-office'),           // Page title
        __('Campaign Data', 'campaign-office'),           // Menu title
        'edit_posts',                                    // Capability
        'campaign-data-main',                            // Menu slug
        'cp_campaign_data_main_page',                   // Callback function
        'dashicons-megaphone',                           // Icon
        25                                               // Position (after Comments)
    );
}
add_action('admin_menu', 'cp_register_campaign_data_menu', 9);

/**
 * Callback for the Campaign Data main page
 */
function cp_campaign_data_main_page() {
    global $wpdb;
    
    // Get counts for dashboard stats
    $issues_count = wp_count_posts('cp_issue')->publish ?? 0;
    $events_count = wp_count_posts('cp_event')->publish ?? 0;
    $endorsements_count = wp_count_posts('cp_endorsement')->publish ?? 0;
    $team_count = wp_count_posts('cp_team')->publish ?? 0;
    $volunteers_count = wp_count_posts('cp_volunteer')->publish ?? 0;
    $press_count = wp_count_posts('cp_press_release')->publish ?? 0;
    
    // Get donation stats if table exists
    $donations_table = $wpdb->prefix . 'campaignpress_donations';
    $total_donations = 0;
    $donation_count = 0;
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $donations_table)) == $donations_table) {
        $total_donations = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$donations_table} WHERE status = %s", 'completed')) ?? 0;
        $donation_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$donations_table} WHERE status = %s", 'completed')) ?? 0;
    }
    
    ?>
    <div class="wrap campaignpress-dashboard">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p class="description"><?php _e('Manage all your campaign data from this central hub.', 'campaign-office'); ?></p>
        
        <!-- Stats Overview -->
        <div class="campaignpress-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0;">
            
            <!-- Issues Stat -->
            <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #2271b1; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 32px; font-weight: bold; color: #2271b1;"><?php echo number_format($issues_count); ?></div>
                        <div style="color: #646970; font-size: 14px;"><?php _e('Issues', 'campaign-office'); ?></div>
                    </div>
                    <span class="dashicons dashicons-flag" style="font-size: 48px; color: #2271b1; opacity: 0.3;"></span>
                </div>
            </div>
            
            <!-- Events Stat -->
            <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #00a32a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 32px; font-weight: bold; color: #00a32a;"><?php echo number_format($events_count); ?></div>
                        <div style="color: #646970; font-size: 14px;"><?php _e('Events', 'campaign-office'); ?></div>
                    </div>
                    <span class="dashicons dashicons-calendar-alt" style="font-size: 48px; color: #00a32a; opacity: 0.3;"></span>
                </div>
            </div>
            
            <!-- Endorsements Stat -->
            <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #d63638; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 32px; font-weight: bold; color: #d63638;"><?php echo number_format($endorsements_count); ?></div>
                        <div style="color: #646970; font-size: 14px;"><?php _e('Endorsements', 'campaign-office'); ?></div>
                    </div>
                    <span class="dashicons dashicons-thumbs-up" style="font-size: 48px; color: #d63638; opacity: 0.3;"></span>
                </div>
            </div>
            
            <!-- Team Stat -->
            <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #9b51e0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 32px; font-weight: bold; color: #9b51e0;"><?php echo number_format($team_count); ?></div>
                        <div style="color: #646970; font-size: 14px;"><?php _e('Team Members', 'campaign-office'); ?></div>
                    </div>
                    <span class="dashicons dashicons-groups" style="font-size: 48px; color: #9b51e0; opacity: 0.3;"></span>
                </div>
            </div>
            
            <!-- Volunteers Stat -->
            <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #f0b849; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 32px; font-weight: bold; color: #f0b849;"><?php echo number_format($volunteers_count); ?></div>
                        <div style="color: #646970; font-size: 14px;"><?php _e('Volunteers', 'campaign-office'); ?></div>
                    </div>
                    <span class="dashicons dashicons-heart" style="font-size: 48px; color: #f0b849; opacity: 0.3;"></span>
                </div>
            </div>
            
            <!-- Donations Stat -->
            <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #00ba37; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 32px; font-weight: bold; color: #00ba37;">$<?php echo number_format($total_donations, 0); ?></div>
                        <div style="color: #646970; font-size: 14px;"><?php echo number_format($donation_count); ?> <?php _e('Donations', 'campaign-office'); ?></div>
                    </div>
                    <span class="dashicons dashicons-money-alt" style="font-size: 48px; color: #00ba37; opacity: 0.3;"></span>
                </div>
            </div>
            
            <!-- Press Stat -->
            <div class="stat-card" style="background: #fff; padding: 20px; border-left: 4px solid #3498db; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 32px; font-weight: bold; color: #3498db;"><?php echo number_format($press_count); ?></div>
                        <div style="color: #646970; font-size: 14px;"><?php _e('Press Releases', 'campaign-office'); ?></div>
                    </div>
                    <span class="dashicons dashicons-media-document" style="font-size: 48px; color: #3498db; opacity: 0.3;"></span>
                </div>
            </div>
            
        </div>
        
        <!-- Quick Actions Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 30px;">
            
            <!-- Campaign Content -->
            <div class="card">
                <h2><?php _e('Campaign Content', 'campaign-office'); ?></h2>
                <ul style="margin: 0; padding-left: 20px;">
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('edit.php?post_type=cp_issue'); ?>" class="row-title">
                            <span class="dashicons dashicons-flag" style="color: #2271b1;"></span>
                            <?php _e('Manage Issues', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('edit.php?post_type=cp_event'); ?>" class="row-title">
                            <span class="dashicons dashicons-calendar-alt" style="color: #00a32a;"></span>
                            <?php _e('Manage Events', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('edit.php?post_type=cp_endorsement'); ?>" class="row-title">
                            <span class="dashicons dashicons-thumbs-up" style="color: #d63638;"></span>
                            <?php _e('Manage Endorsements', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('edit.php?post_type=cp_team'); ?>" class="row-title">
                            <span class="dashicons dashicons-groups" style="color: #9b51e0;"></span>
                            <?php _e('Manage Team', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('edit.php?post_type=cp_volunteer'); ?>" class="row-title">
                            <span class="dashicons dashicons-heart" style="color: #f0b849;"></span>
                            <?php _e('Manage Volunteers', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('edit.php?post_type=cp_press_release'); ?>" class="row-title">
                            <span class="dashicons dashicons-media-document" style="color: #3498db;"></span>
                            <?php _e('Manage Press', 'campaign-office'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Analytics & Reports (Premium Only) -->
            <?php if (function_exists('cp_is_premium_active') && cp_is_premium_active()) : ?>
            <div class="card">
                <h2><?php _e('Analytics & Reports', 'campaign-office'); ?></h2>
                <ul style="margin: 0; padding-left: 20px;">
                    <li style="margin-bottom: 10px;">
                        <?php 
                        $analytics_slug = 'cp-analytics';
                        if (function_exists('cp_has_premium_feature') && cp_has_premium_feature('analytics')) {
                            $analytics_slug = 'campaignpress-analytics';
                        }
                        ?>
                        <a href="<?php echo admin_url('admin.php?page=' . $analytics_slug); ?>" class="row-title">
                            <span class="dashicons dashicons-chart-area" style="color: #2271b1;"></span>
                            <?php _e('Campaign Analytics', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=campaignpress-metrics'); ?>" class="row-title">
                            <span class="dashicons dashicons-dashboard" style="color: #00a32a;"></span>
                            <?php _e('Performance Metrics', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=campaignpress-reports'); ?>" class="row-title">
                            <span class="dashicons dashicons-media-document" style="color: #d63638;"></span>
                            <?php _e('Reports', 'campaign-office'); ?>
                        </a>
                    </li>
                    <li style="margin-bottom: 10px;">
                        <a href="<?php echo admin_url('admin.php?page=campaignpress-test-data'); ?>" class="row-title">
                            <span class="dashicons dashicons-database" style="color: #f0b849;"></span>
                            <?php _e('Generate Test Data', 'campaign-office'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Quick Actions -->
            <div class="card">
                <h2><?php _e('Quick Actions', 'campaign-office'); ?></h2>
                <p style="margin-bottom: 15px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=cp_issue'); ?>" class="button button-primary button-large" style="width: 100%; text-align: center;">
                        <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                        <?php _e('Add New Issue', 'campaign-office'); ?>
                    </a>
                </p>
                <p style="margin-bottom: 15px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=cp_event'); ?>" class="button button-secondary button-large" style="width: 100%; text-align: center;">
                        <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                        <?php _e('Add New Event', 'campaign-office'); ?>
                    </a>
                </p>
                <p style="margin-bottom: 15px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=cp_endorsement'); ?>" class="button button-secondary button-large" style="width: 100%; text-align: center;">
                        <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                        <?php _e('Add New Endorsement', 'campaign-office'); ?>
                    </a>
                </p>
                <p style="margin-bottom: 15px;">
                    <a href="<?php echo admin_url('post-new.php?post_type=cp_press_release'); ?>" class="button button-secondary button-large" style="width: 100%; text-align: center;">
                        <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                        <?php _e('Add New Press Release', 'campaign-office'); ?>
                    </a>
                </p>
            </div>
            
        </div>
        
        <!-- Help Section -->
        <div class="card" style="margin-top: 20px;">
            <h2><?php _e('Getting Started', 'campaign-office'); ?></h2>
            <p><?php _e('Welcome to the Campaign Data hub! Here you can manage all aspects of your political campaign:', 'campaign-office'); ?></p>
            <ol style="padding-left: 20px;">
                <li><?php _e('<strong>Issues</strong> - Define your campaign platform and policy positions', 'campaign-office'); ?></li>
                <li><?php _e('<strong>Events</strong> - Schedule and manage campaign events, rallies, and town halls', 'campaign-office'); ?></li>
                <li><?php _e('<strong>Endorsements</strong> - Showcase support from organizations and community leaders', 'campaign-office'); ?></li>
                <li><?php _e('<strong>Team</strong> - Introduce your campaign staff and leadership', 'campaign-office'); ?></li>
                <li><?php _e('<strong>Volunteers</strong> - Manage volunteer recruitment and activities', 'campaign-office'); ?></li>
                <li><?php _e('<strong>Press Releases</strong> - Official campaign statements and news', 'campaign-office'); ?></li>
            </ol>
            <p><?php _e('Use the Analytics section to track campaign performance, donations, and engagement metrics.', 'campaign-office'); ?></p>
        </div>
    </div>
    <?php
}

/**
 * Move CPT menu items under Campaign Data parent
 * Removes them from top-level and adds as submenus
 */
function cp_move_cpts_to_campaign_data() {
    global $menu, $submenu;

    // Define CPTs to move with their display names and icons
    // Note: CPT slugs have prefix 'cp_' as defined in custom-post-types.php
    $cpts_to_move = array(
        'cp_issue' => array(
            'name' => __('Issues', 'campaign-office'),
            'icon' => 'dashicons-flag'
        ),
        'cp_event' => array(
            'name' => __('Events', 'campaign-office'),
            'icon' => 'dashicons-calendar-alt'
        ),
        'cp_endorsement' => array(
            'name' => __('Endorsements', 'campaign-office'),
            'icon' => 'dashicons-thumbs-up'
        ),
        'cp_team' => array(
            'name' => __('Team', 'campaign-office'),
            'icon' => 'dashicons-groups'
        ),
        'cp_volunteer' => array(
            'name' => __('Volunteers', 'campaign-office'),
            'icon' => 'dashicons-heart'
        ),
        'cp_press_release' => array(
            'name' => __('Press Releases', 'campaign-office'),
            'icon' => 'dashicons-media-document'
        )
    );

    // Remove CPTs from top-level menu
    foreach ($cpts_to_move as $post_type => $data) {
        remove_menu_page('edit.php?post_type=' . $post_type);
    }

    // Add CPTs as submenus under Campaign Data in the desired order
    foreach ($cpts_to_move as $post_type => $data) {
        add_submenu_page(
            'campaign-data-main',                        // Parent slug
            $data['name'],                               // Page title
            $data['name'],                               // Menu title
            'edit_posts',                                // Capability
            'edit.php?post_type=' . $post_type          // Menu slug
        );
    }
}
add_action('admin_menu', 'cp_move_cpts_to_campaign_data', 999);

/**
 * Enforce exact submenu order under Campaign Data
 */
function cp_enforce_campaign_data_submenu_order() {
    global $submenu;

    if (!isset($submenu['campaign-data-main'])) {
        return;
    }

    // Define the desired order
    $desired_order = array(
        'campaign-data-main',                    // Campaign Data (main page)
        'edit.php?post_type=cp_issue',             // Issues
        'edit.php?post_type=cp_event',             // Events
        'edit.php?post_type=cp_endorsement',       // Endorsements
        'edit.php?post_type=cp_team',              // Team
        'edit.php?post_type=cp_volunteer',         // Volunteers
        'edit.php?post_type=cp_press_release'      // Press Releases
    );

    // Create a new ordered submenu array
    $ordered_submenu = array();
    
    // First, add items in desired order
    foreach ($desired_order as $slug) {
        foreach ($submenu['campaign-data-main'] as $item) {
            if ($item[2] === $slug) {
                $ordered_submenu[] = $item;
                break;
            }
        }
    }

    // Then add any remaining items that weren't in our list
    foreach ($submenu['campaign-data-main'] as $item) {
        if (!in_array($item[2], $desired_order)) {
            $ordered_submenu[] = $item;
        }
    }

    // Replace the submenu with our ordered version
    $submenu['campaign-data-main'] = $ordered_submenu;
}
add_action('admin_menu', 'cp_enforce_campaign_data_submenu_order', 9999);

/**
 * Inject CSS to add Dashicons to Campaign Data submenus
 */
function cp_add_submenu_icons_css() {
    ?>
    <style>
        /* Campaign Data submenu icons */
        #adminmenu .wp-submenu a[href*="post_type=cp_issue"]::before {
            content: "\f227";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=cp_event"]::before {
            content: "\f145";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=cp_endorsement"]::before {
            content: "\f529";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=cp_team"]::before {
            content: "\f307";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=cp_volunteer"]::before {
            content: "\f487";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        /* Hover effect for submenu icons */
        #adminmenu .wp-submenu a[href*="post_type="]::before {
            transition: opacity 0.2s ease;
        }

        #adminmenu .wp-submenu a[href*="post_type="]:hover::before {
            opacity: 1;
        }
    </style>
    <?php
}
add_action('admin_head', 'cp_add_submenu_icons_css');

/**
 * Move Analytics menu item under CampaignPress
 * Note: Disabled to prevent conflicts with free/premium analytics modules
 */
/*
function cp_move_analytics_to_campaignpress() {
    global $submenu;

    // Remove Analytics from top-level if it exists
    remove_menu_page('cp-analytics');

    // Add Analytics as submenu under CampaignPress
    add_submenu_page(
        'campaignpress',                                 
        __('Analytics', 'campaign-office'),               
        __('Analytics', 'campaign-office'),               
        'manage_options',                                
        'cp-analytics',                            
        'cp_analytics_page_callback'                    
    );
}
add_action('admin_menu', 'cp_move_analytics_to_campaignpress', 999);
*/

/**
 * Conditionally hide Dev Console menu unless WP_DEBUG is true
 */
function cp_conditionally_hide_dev_console() {
    // Only hide if WP_DEBUG is not enabled
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        remove_menu_page('campaignpress-developer-console');
    }
}
add_action('admin_menu', 'cp_conditionally_hide_dev_console', 999);

/**
 * Optional: Add admin notice to confirm reorganization is active
 * Remove this function once you've confirmed everything works
 */
function cp_admin_menu_reorganization_notice() {
    $screen = get_current_screen();
    
    // Only show on the Campaign Data main page
    if ($screen && $screen->id === 'toplevel_page_campaign-data-main') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Admin menu reorganization is active. Your Campaign Data is now centralized!', 'campaign-office'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'cp_admin_menu_reorganization_notice');
