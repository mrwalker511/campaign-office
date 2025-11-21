<?php
/**
 * Developer Console System Health Class
 *
 * Monitors and reports on system health and performance
 *
 * @package CampaignPress
 * @subpackage DeveloperConsole
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class CampaignPress_Developer_System_Health {

    /**
     * Get all health data
     *
     * @return array
     */
    public function get_all_health_data() {
        return array(
            'wordpress' => $this->get_wordpress_info(),
            'server' => $this->get_server_info(),
            'database' => $this->get_database_info(),
            'theme' => $this->get_theme_info(),
            'performance' => $this->get_performance_metrics(),
            'security' => $this->get_security_status(),
            'license' => $this->get_license_info(),
            'features' => $this->get_feature_status(),
            'errors' => $this->get_recent_errors(),
            'storage' => $this->get_storage_info()
        );
    }

    /**
     * Get WordPress information
     *
     * @return array
     */
    private function get_wordpress_info() {
        global $wp_version;

        return array(
            'version' => $wp_version,
            'multisite' => is_multisite(),
            'debug_mode' => defined('WP_DEBUG') && WP_DEBUG,
            'memory_limit' => WP_MEMORY_LIMIT,
            'max_memory_limit' => WP_MAX_MEMORY_LIMIT,
            'language' => get_locale(),
            'timezone' => wp_timezone_string(),
            'permalink_structure' => get_option('permalink_structure'),
            'home_url' => home_url(),
            'site_url' => site_url(),
            'admin_email' => get_option('admin_email')
        );
    }

    /**
     * Get server information
     *
     * @return array
     */
    private function get_server_info() {
        return array(
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => phpversion(),
            'php_sapi' => php_sapi_name(),
            'os' => PHP_OS,
            'architecture' => php_uname('m'),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_time' => ini_get('max_input_time'),
            'memory_limit' => ini_get('memory_limit'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'max_input_vars' => ini_get('max_input_vars'),
            'extensions' => get_loaded_extensions(),
            'opcache_enabled' => function_exists('opcache_get_status') && opcache_get_status() !== false,
            'current_memory_usage' => size_format(memory_get_usage(true)),
            'peak_memory_usage' => size_format(memory_get_peak_usage(true))
        );
    }

    /**
     * Get database information
     *
     * @return array
     */
    private function get_database_info() {
        global $wpdb;

        $db_version = $wpdb->get_var('SELECT VERSION()');

        // Get database size
        $db_name = DB_NAME;
        $size_query = $wpdb->get_row($wpdb->prepare(
            "SELECT
                SUM(data_length + index_length) as size,
                SUM(data_length) as data_size,
                SUM(index_length) as index_size
             FROM information_schema.TABLES
             WHERE table_schema = %s",
            $db_name
        ));

        // Get table count
        $table_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = %s",
            $db_name
        ));

        // Get CampaignPress tables
        $cp_tables = $wpdb->get_results(
            "SELECT
                table_name,
                table_rows,
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
             FROM information_schema.TABLES
             WHERE table_schema = '" . DB_NAME . "'
             AND table_name LIKE '{$wpdb->prefix}cp_%'
             ORDER BY table_name"
        );

        return array(
            'version' => $db_version,
            'charset' => $wpdb->charset,
            'collation' => $wpdb->collate,
            'prefix' => $wpdb->prefix,
            'total_size' => size_format($size_query->size ?? 0),
            'data_size' => size_format($size_query->data_size ?? 0),
            'index_size' => size_format($size_query->index_size ?? 0),
            'table_count' => $table_count,
            'campaignpress_tables' => $cp_tables,
            'max_connections' => $wpdb->get_var('SELECT @@max_connections'),
            'max_allowed_packet' => size_format($wpdb->get_var('SELECT @@max_allowed_packet'))
        );
    }

    /**
     * Get theme information
     *
     * @return array
     */
    private function get_theme_info() {
        $theme = wp_get_theme();

        return array(
            'name' => $theme->get('Name'),
            'version' => $theme->get('Version'),
            'author' => $theme->get('Author'),
            'template' => $theme->get('Template'),
            'stylesheet' => $theme->get('Stylesheet'),
            'theme_uri' => $theme->get('ThemeURI'),
            'description' => $theme->get('Description'),
            'tags' => $theme->get('Tags'),
            'text_domain' => $theme->get('TextDomain')
        );
    }

    /**
     * Get performance metrics
     *
     * @return array
     */
    private function get_performance_metrics() {
        global $wpdb;

        // Query performance
        $slow_queries = $wpdb->get_results(
            "SELECT * FROM information_schema.processlist
             WHERE command != 'Sleep'
             AND time > 5
             ORDER BY time DESC
             LIMIT 10"
        );

        // Cache stats if object cache is available
        $cache_stats = array(
            'enabled' => wp_using_ext_object_cache(),
            'hits' => 0,
            'misses' => 0,
            'ratio' => 0
        );

        if (function_exists('wp_cache_get_stats')) {
            $stats = wp_cache_get_stats();
            if (!empty($stats)) {
                $cache_stats['hits'] = $stats['hits'] ?? 0;
                $cache_stats['misses'] = $stats['misses'] ?? 0;
                if ($cache_stats['hits'] + $cache_stats['misses'] > 0) {
                    $cache_stats['ratio'] = round(
                        $cache_stats['hits'] / ($cache_stats['hits'] + $cache_stats['misses']) * 100,
                        2
                    );
                }
            }
        }

        // Autoload options size
        $autoload_size = $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value))
             FROM {$wpdb->options}
             WHERE autoload = 'yes'"
        );

        return array(
            'slow_queries' => $slow_queries,
            'total_queries' => $wpdb->num_queries,
            'query_time' => timer_stop(0, 3),
            'cache' => $cache_stats,
            'autoload_size' => size_format($autoload_size),
            'page_generation_time' => timer_stop(0, 3) . ' seconds',
            'server_load' => function_exists('sys_getloadavg') ? sys_getloadavg() : array(0, 0, 0)
        );
    }

    /**
     * Get security status
     *
     * @return array
     */
    private function get_security_status() {
        $checks = array();

        // Check WP_DEBUG
        $checks['wp_debug'] = array(
            'status' => !defined('WP_DEBUG') || !WP_DEBUG,
            'message' => defined('WP_DEBUG') && WP_DEBUG ? 'WP_DEBUG is enabled' : 'WP_DEBUG is disabled'
        );

        // Check HTTPS
        $checks['https'] = array(
            'status' => is_ssl(),
            'message' => is_ssl() ? 'Site is using HTTPS' : 'Site is not using HTTPS'
        );

        // Check file permissions
        $checks['file_permissions'] = array(
            'wp-config.php' => $this->check_file_permissions(ABSPATH . 'wp-config.php', '0600'),
            '.htaccess' => $this->check_file_permissions(ABSPATH . '.htaccess', '0644')
        );

        // Check salts
        $salts = array('AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY');
        $salts_ok = true;
        foreach ($salts as $salt) {
            if (!defined($salt) || constant($salt) === 'put your unique phrase here') {
                $salts_ok = false;
                break;
            }
        }

        $checks['salts'] = array(
            'status' => $salts_ok,
            'message' => $salts_ok ? 'Security salts are configured' : 'Security salts need to be configured'
        );

        // Check database prefix
        $checks['db_prefix'] = array(
            'status' => $GLOBALS['wpdb']->prefix !== 'wp_',
            'message' => $GLOBALS['wpdb']->prefix === 'wp_' ? 'Using default database prefix' : 'Using custom database prefix'
        );

        // Check admin username
        $admin = get_user_by('login', 'admin');
        $checks['admin_username'] = array(
            'status' => !$admin,
            'message' => $admin ? 'Default "admin" username exists' : 'No default "admin" username'
        );

        return $checks;
    }

    /**
     * Check file permissions
     *
     * @param string $file File path
     * @param string $expected Expected permissions
     * @return array
     */
    private function check_file_permissions($file, $expected) {
        if (!file_exists($file)) {
            return array(
                'status' => false,
                'message' => 'File does not exist',
                'current' => 'N/A'
            );
        }

        $perms = substr(sprintf('%o', fileperms($file)), -4);

        return array(
            'status' => $perms === $expected,
            'message' => $perms === $expected ? 'Correct permissions' : 'Incorrect permissions',
            'current' => $perms,
            'expected' => $expected
        );
    }

    /**
     * Get license information
     *
     * @return array
     */
    private function get_license_info() {
        return array(
            'key' => get_option('campaignpress_license_key', ''),
            'email' => get_option('campaignpress_license_email', ''),
            'status' => get_option('campaignpress_license_status', ''),
            'type' => get_option('campaignpress_license_type', ''),
            'expiry' => get_option('campaignpress_license_expiry', ''),
            'activated_date' => get_option('campaignpress_license_activated_date', ''),
            'premium_active' => get_option('campaignpress_premium_active', false)
        );
    }

    /**
     * Get feature status
     *
     * @return array
     */
    private function get_feature_status() {
        $enabled_features = get_option('campaignpress_enabled_features', array());

        $features = array(
            'crm' => array(
                'name' => 'CRM System',
                'enabled' => in_array('crm', $enabled_features)
            ),
            'field_operations' => array(
                'name' => 'Field Operations',
                'enabled' => in_array('field_operations', $enabled_features)
            ),
            'analytics' => array(
                'name' => 'Analytics',
                'enabled' => in_array('analytics', $enabled_features)
            ),
            'fec_compliance' => array(
                'name' => 'FEC Compliance',
                'enabled' => in_array('fec_compliance', $enabled_features)
            ),
            'api' => array(
                'name' => 'REST API',
                'enabled' => in_array('api', $enabled_features)
            ),
            'automation' => array(
                'name' => 'Automation',
                'enabled' => in_array('automation', $enabled_features)
            )
        );

        return $features;
    }

    /**
     * Get recent errors from WordPress error log
     *
     * @return array
     */
    private function get_recent_errors() {
        $errors = array();

        // Try to read error log
        $error_log_file = ini_get('error_log');

        if (empty($error_log_file) || !file_exists($error_log_file)) {
            // Try WP debug.log
            $error_log_file = WP_CONTENT_DIR . '/debug.log';
        }

        if (file_exists($error_log_file) && is_readable($error_log_file)) {
            $lines = @file($error_log_file);

            if ($lines !== false) {
                // Get last 50 lines
                $lines = array_slice($lines, -50);

                foreach ($lines as $line) {
                    if (preg_match('/\[(.*?)\]\s+(.+)/', $line, $matches)) {
                        $errors[] = array(
                            'timestamp' => $matches[1],
                            'message' => $matches[2]
                        );
                    }
                }
            }
        }

        return array(
            'file' => $error_log_file ?? 'Not configured',
            'errors' => array_reverse($errors)
        );
    }

    /**
     * Get storage information
     *
     * @return array
     */
    private function get_storage_info() {
        $upload_dir = wp_upload_dir();

        $storage = array(
            'upload_path' => $upload_dir['basedir'],
            'upload_url' => $upload_dir['baseurl']
        );

        // Get directory size if possible
        if (function_exists('disk_free_space') && function_exists('disk_total_space')) {
            $free = disk_free_space($upload_dir['basedir']);
            $total = disk_total_space($upload_dir['basedir']);

            $storage['disk_free'] = size_format($free);
            $storage['disk_total'] = size_format($total);
            $storage['disk_used_percent'] = round((($total - $free) / $total) * 100, 2);
        }

        // Get upload directory size
        $storage['uploads_size'] = $this->get_directory_size($upload_dir['basedir']);

        return $storage;
    }

    /**
     * Get directory size
     *
     * @param string $directory Directory path
     * @return string Formatted size
     */
    private function get_directory_size($directory) {
        $size = 0;

        if (!is_dir($directory)) {
            return size_format(0);
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (Exception $e) {
            return 'Unable to calculate';
        }

        return size_format($size);
    }
}
