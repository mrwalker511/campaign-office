<?php
/**
 * Emergency plugin deactivation for WordPress Playground
 *
 * Instructions:
 * 1. Upload this file to: wp-content/mu-plugins/fix-plugin.php
 * 2. Refresh your WordPress admin page
 * 3. Delete this file after the site loads successfully
 */

add_action('admin_init', function() {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');

    // Force deactivate the problematic plugin
    if (is_plugin_active('wp-fastest-cache/wpFastestCache.php')) {
        deactivate_plugins('wp-fastest-cache/wpFastestCache.php');

        // Show success message
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>Fixed!</strong> wp-fastest-cache has been deactivated. You can now delete this mu-plugin file.</p>';
            echo '</div>';
        });
    }
});
