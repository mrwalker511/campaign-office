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
        __('Campaign Data', 'campaignpress'),           // Page title
        __('Campaign Data', 'campaignpress'),           // Menu title
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
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p><?php _e('Manage all your campaign data from this central hub.', 'campaignpress'); ?></p>
        <div class="card">
            <h2><?php _e('Quick Links', 'campaignpress'); ?></h2>
            <ul>
                <li><a href="<?php echo admin_url('edit.php?post_type=issue'); ?>"><?php _e('Issues', 'campaignpress'); ?></a></li>
                <li><a href="<?php echo admin_url('edit.php?post_type=event'); ?>"><?php _e('Events', 'campaignpress'); ?></a></li>
                <li><a href="<?php echo admin_url('edit.php?post_type=endorsement'); ?>"><?php _e('Endorsements', 'campaignpress'); ?></a></li>
                <li><a href="<?php echo admin_url('edit.php?post_type=team'); ?>"><?php _e('Team', 'campaignpress'); ?></a></li>
                <li><a href="<?php echo admin_url('edit.php?post_type=volunteer'); ?>"><?php _e('Volunteers', 'campaignpress'); ?></a></li>
            </ul>
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
    $cpts_to_move = array(
        'issue' => array(
            'name' => __('Issues', 'campaignpress'),
            'icon' => 'dashicons-flag'
        ),
        'event' => array(
            'name' => __('Events', 'campaignpress'),
            'icon' => 'dashicons-calendar-alt'
        ),
        'endorsement' => array(
            'name' => __('Endorsements', 'campaignpress'),
            'icon' => 'dashicons-thumbs-up'
        ),
        'team' => array(
            'name' => __('Team', 'campaignpress'),
            'icon' => 'dashicons-groups'
        ),
        'volunteer' => array(
            'name' => __('Volunteers', 'campaignpress'),
            'icon' => 'dashicons-heart'
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
        'edit.php?post_type=issue',             // Issues
        'edit.php?post_type=event',             // Events
        'edit.php?post_type=endorsement',       // Endorsements
        'edit.php?post_type=team',              // Team
        'edit.php?post_type=volunteer'          // Volunteers
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
        #adminmenu .wp-submenu a[href*="post_type=issue"]::before {
            content: "\f227";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=event"]::before {
            content: "\f145";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=endorsement"]::before {
            content: "\f529";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=team"]::before {
            content: "\f307";
            font-family: dashicons;
            margin-right: 8px;
            opacity: 0.7;
        }

        #adminmenu .wp-submenu a[href*="post_type=volunteer"]::before {
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
 */
function cp_move_analytics_to_campaignpress() {
    global $submenu;

    // Remove Analytics from top-level if it exists
    remove_menu_page('campaign-analytics');

    // Add Analytics as submenu under CampaignPress
    // Adjust 'campaignpress' to match your actual CampaignPress menu slug
    add_submenu_page(
        'campaignpress',                                 // Parent slug (adjust if needed)
        __('Analytics', 'campaignpress'),               // Page title
        __('Analytics', 'campaignpress'),               // Menu title
        'manage_options',                                // Capability
        'campaign-analytics',                            // Menu slug
        'cp_analytics_page_callback'                    // Callback (adjust if needed)
    );
}
add_action('admin_menu', 'cp_move_analytics_to_campaignpress', 999);

/**
 * Conditionally hide Dev Console menu unless WP_DEBUG is true
 */
function cp_conditionally_hide_dev_console() {
    // Only hide if WP_DEBUG is not enabled
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        remove_menu_page('dev-console');
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
            <p><?php _e('Admin menu reorganization is active. Your Campaign Data is now centralized!', 'campaignpress'); ?></p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'cp_admin_menu_reorganization_notice');
