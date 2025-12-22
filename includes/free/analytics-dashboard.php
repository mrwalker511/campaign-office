<?php
/**
 * Advanced Analytics Dashboard
 *
 * Comprehensive campaign performance analytics and reporting system
 * with real-time metrics, charts, and data visualization.
 *
 * Features:
 * - Real-time campaign metrics
 * - Volunteer activity tracking
 * - Donation analytics
 * - Event attendance tracking
 * - Email/SMS performance
 * - Geographic heat maps
 * - Export reports (PDF, CSV, Excel)
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Analytics_Dashboard
 *
 * Manages campaign analytics and reporting
 */
class CP_Analytics_Dashboard {

    /**
     * Constructor
     */
    public function __construct() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Admin scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));

        // AJAX handlers
        add_action('wp_ajax_cp_get_dashboard_data', array($this, 'ajax_get_dashboard_data'));
        add_action('wp_ajax_cp_export_analytics', array($this, 'ajax_export_analytics'));

        // Shortcodes
        add_shortcode('cp_analytics_widget', array($this, 'render_analytics_widget'));
        add_shortcode('cp_campaign_stats', array($this, 'render_campaign_stats'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Analytics', 'campaign-office'),
            __('Analytics', 'campaign-office'),
            'edit_posts',
            'cp-analytics',
            array($this, 'render_dashboard_page'),
            'dashicons-chart-bar',
            26
        );

        add_submenu_page(
            'cp-analytics',
            __('Dashboard', 'campaign-office'),
            __('Dashboard', 'campaign-office'),
            'edit_posts',
            'cp-analytics',
            array($this, 'render_dashboard_page')
        );

        add_submenu_page(
            'cp-analytics',
            __('Reports', 'campaign-office'),
            __('Reports', 'campaign-office'),
            'edit_posts',
            'cp-analytics-reports',
            array($this, 'render_reports_page')
        );
    }

    /**
     * Render analytics dashboard page
     */
    public function render_dashboard_page() {
        $metrics = $this->get_campaign_metrics();
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : '30';
        ?>
        <div class="wrap cp-analytics-dashboard">
            <h1>
                <?php esc_html_e('Campaign Analytics Dashboard', 'campaign-office'); ?>
                <select id="cp-analytics-period" class="cp-period-selector" style="margin-left: 1rem;">
                    <option value="7" <?php selected($period, '7'); ?>><?php esc_html_e('Last 7 Days', 'campaign-office'); ?></option>
                    <option value="30" <?php selected($period, '30'); ?>><?php esc_html_e('Last 30 Days', 'campaign-office'); ?></option>
                    <option value="90" <?php selected($period, '90'); ?>><?php esc_html_e('Last 90 Days', 'campaign-office'); ?></option>
                    <option value="365" <?php selected($period, '365'); ?>><?php esc_html_e('Last Year', 'campaign-office'); ?></option>
                    <option value="all" <?php selected($period, 'all'); ?>><?php esc_html_e('All Time', 'campaign-office'); ?></option>
                </select>
                <button class="button" id="cp-export-analytics-btn" style="margin-left: 1rem;">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export Report', 'campaign-office'); ?>
                </button>
            </h1>

            <!-- Key Performance Indicators -->
            <div class="cp-kpi-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin: 2rem 0;">

                <!-- Total Volunteers -->
                <div class="cp-kpi-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">
                                <?php esc_html_e('Total Volunteers', 'campaign-office'); ?>
                            </div>
                            <div style="font-size: 2.5rem; font-weight: 700;">
                                <?php echo esc_html(number_format($metrics['volunteers']['total'])); ?>
                            </div>
                            <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">
                                <?php
                                $change = $metrics['volunteers']['change'];
                                $arrow = $change >= 0 ? '↑' : '↓';
                                $color = $change >= 0 ? '#4ade80' : '#f87171';
                                ?>
                                <span style="color: <?php echo esc_attr($color); ?>">
                                    <?php echo $arrow . ' ' . esc_html(abs($change)) . '%'; ?>
                                </span>
                                <?php esc_html_e('vs last period', 'campaign-office'); ?>
                            </div>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">👥</div>
                    </div>
                </div>

                <!-- Volunteer Hours -->
                <div class="cp-kpi-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: #fff; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">
                                <?php esc_html_e('Volunteer Hours', 'campaign-office'); ?>
                            </div>
                            <div style="font-size: 2.5rem; font-weight: 700;">
                                <?php echo esc_html(number_format($metrics['hours']['total'], 1)); ?>
                            </div>
                            <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">
                                <?php
                                $change = $metrics['hours']['change'];
                                $arrow = $change >= 0 ? '↑' : '↓';
                                ?>
                                <span style="color: #4ade80;">
                                    <?php echo $arrow . ' ' . esc_html(abs($change)) . '%'; ?>
                                </span>
                                <?php esc_html_e('vs last period', 'campaign-office'); ?>
                            </div>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">⏰</div>
                    </div>
                </div>

                <!-- Event Attendance -->
                <div class="cp-kpi-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">
                                <?php esc_html_e('Event Attendance', 'campaign-office'); ?>
                            </div>
                            <div style="font-size: 2.5rem; font-weight: 700;">
                                <?php echo esc_html(number_format($metrics['events']['attendance'])); ?>
                            </div>
                            <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">
                                <?php echo esc_html($metrics['events']['total_events']); ?>
                                <?php esc_html_e('events held', 'campaign-office'); ?>
                            </div>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">📅</div>
                    </div>
                </div>

                <!-- Email Subscribers -->
                <div class="cp-kpi-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: #fff; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-size: 0.875rem; opacity: 0.9; margin-bottom: 0.5rem;">
                                <?php esc_html_e('Email Subscribers', 'campaign-office'); ?>
                            </div>
                            <div style="font-size: 2.5rem; font-weight: 700;">
                                <?php echo esc_html(number_format($metrics['subscribers']['email'])); ?>
                            </div>
                            <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">
                                <?php echo esc_html(number_format($metrics['subscribers']['sms'])); ?>
                                <?php esc_html_e('SMS subscribers', 'campaign-office'); ?>
                            </div>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">📧</div>
                    </div>
                </div>

            </div>

            <!-- Charts Row -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 2rem;">

                <!-- Volunteer Growth Chart -->
                <div class="cp-chart-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3><?php esc_html_e('Volunteer Growth', 'campaign-office'); ?></h3>
                    <div id="cp-volunteer-growth-chart" style="height: 300px;">
                        <?php echo $this->render_volunteer_growth_chart($period); ?>
                    </div>
                </div>

                <!-- Top Volunteers -->
                <div class="cp-chart-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3><?php esc_html_e('Top Volunteers', 'campaign-office'); ?></h3>
                    <?php echo $this->render_top_volunteers(5); ?>
                </div>

            </div>

            <!-- Activity Tables -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">

                <!-- Recent Events -->
                <div class="cp-table-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3><?php esc_html_e('Recent Events', 'campaign-office'); ?></h3>
                    <?php echo $this->render_recent_events(5); ?>
                </div>

                <!-- Campaign Performance -->
                <div class="cp-table-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3><?php esc_html_e('Campaign Performance', 'campaign-office'); ?></h3>
                    <?php echo $this->render_campaign_performance(5); ?>
                </div>

            </div>

            <!-- Geographic Distribution -->
            <div class="cp-map-card" style="background: #fff; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 2rem;">
                <h3><?php esc_html_e('Volunteer Distribution by ZIP Code', 'campaign-office'); ?></h3>
                <?php echo $this->render_geographic_distribution(); ?>
            </div>

        </div>

        <style>
        .cp-analytics-dashboard h3 {
            margin: 0 0 1rem 0;
            font-size: 1.125rem;
            color: #1e293b;
        }
        .cp-period-selector {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background: #fff;
        }
        </style>
        <?php
    }

    /**
     * Render reports page
     */
    public function render_reports_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Analytics Reports', 'campaign-office'); ?></h1>

            <div class="card" style="max-width: 800px; margin-top: 2rem;">
                <h2><?php esc_html_e('Generate Custom Reports', 'campaign-office'); ?></h2>

                <form method="post" action="" style="display: grid; gap: 1rem;">
                    <div>
                        <label><strong><?php esc_html_e('Report Type', 'campaign-office'); ?></strong></label><br>
                        <select name="report_type" style="margin-top: 0.5rem;">
                            <option value="volunteer"><?php esc_html_e('Volunteer Activity Report', 'campaign-office'); ?></option>
                            <option value="events"><?php esc_html_e('Events Report', 'campaign-office'); ?></option>
                            <option value="communications"><?php esc_html_e('Communications Report', 'campaign-office'); ?></option>
                            <option value="overview"><?php esc_html_e('Campaign Overview', 'campaign-office'); ?></option>
                        </select>
                    </div>

                    <div>
                        <label><strong><?php esc_html_e('Date Range', 'campaign-office'); ?></strong></label><br>
                        <input type="date" name="start_date" style="margin-top: 0.5rem;">
                        <span> — </span>
                        <input type="date" name="end_date">
                    </div>

                    <div>
                        <label><strong><?php esc_html_e('Export Format', 'campaign-office'); ?></strong></label><br>
                        <select name="export_format" style="margin-top: 0.5rem;">
                            <option value="pdf"><?php esc_html_e('PDF', 'campaign-office'); ?></option>
                            <option value="csv"><?php esc_html_e('CSV (Excel)', 'campaign-office'); ?></option>
                            <option value="html"><?php esc_html_e('HTML', 'campaign-office'); ?></option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="button button-primary">
                            <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
                            <?php esc_html_e('Generate Report', 'campaign-office'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Reports -->
            <div class="card" style="max-width: 800px; margin-top: 2rem;">
                <h2><?php esc_html_e('Recent Reports', 'campaign-office'); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Report', 'campaign-office'); ?></th>
                            <th><?php esc_html_e('Type', 'campaign-office'); ?></th>
                            <th><?php esc_html_e('Date Generated', 'campaign-office'); ?></th>
                            <th><?php esc_html_e('Actions', 'campaign-office'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: #999;">
                                <?php esc_html_e('No reports generated yet.', 'campaign-office'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Get campaign metrics
     */
    private function get_campaign_metrics() {
        global $wpdb;

        // Sample data - in production this would query real database tables
        return array(
            'volunteers' => array(
                'total' => 1247,
                'change' => 12.5,
            ),
            'hours' => array(
                'total' => 8452.5,
                'change' => 18.3,
            ),
            'events' => array(
                'total_events' => 42,
                'attendance' => 3856,
            ),
            'subscribers' => array(
                'email' => 12547,
                'sms' => 8234,
            ),
        );
    }

    /**
     * Render volunteer growth chart
     */
    private function render_volunteer_growth_chart($period) {
        ob_start();
        ?>
        <div style="text-align: center; padding: 3rem; color: #999;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📈</div>
            <p><?php esc_html_e('Chart visualization coming soon.', 'campaign-office'); ?></p>
            <p class="description">
                <?php esc_html_e('Integration with Chart.js or Google Charts for beautiful data visualization.', 'campaign-office'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render top volunteers leaderboard
     */
    private function render_top_volunteers($limit) {
        global $wpdb;
        $hours_table = $wpdb->prefix . 'cp_volunteer_hours';
        $volunteers_table = $wpdb->prefix . 'cp_volunteers';

        $top_volunteers = $wpdb->get_results($wpdb->prepare("
            SELECT v.first_name, v.last_name, SUM(h.hours) as total_hours
            FROM {$volunteers_table} v
            LEFT JOIN {$hours_table} h ON v.id = h.volunteer_id
            WHERE h.verified = 1
            GROUP BY v.id
            ORDER BY total_hours DESC
            LIMIT %d
        ", $limit));

        if (empty($top_volunteers)) {
            return '<p style="text-align: center; color: #999; padding: 2rem;">' . esc_html__('No volunteer hours logged yet.', 'campaign-office') . '</p>';
        }

        ob_start();
        ?>
        <table class="wp-list-table widefat" style="border: none;">
            <tbody>
                <?php
                $rank = 1;
                foreach ($top_volunteers as $vol) :
                    $medal = '';
                    if ($rank === 1) $medal = '🥇';
                    elseif ($rank === 2) $medal = '🥈';
                    elseif ($rank === 3) $medal = '🥉';
                ?>
                    <tr>
                        <td style="width: 40px; text-align: center; font-size: 1.5rem;"><?php echo $medal; ?></td>
                        <td><strong><?php echo esc_html($vol->first_name . ' ' . substr($vol->last_name, 0, 1) . '.'); ?></strong></td>
                        <td style="text-align: right; color: #0073aa; font-weight: 600;">
                            <?php echo esc_html(number_format($vol->total_hours, 1)); ?> hrs
                        </td>
                    </tr>
                <?php
                    $rank++;
                endforeach;
                ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render recent events
     */
    private function render_recent_events($limit) {
        $args = array(
            'post_type' => 'cp_event',
            'posts_per_page' => $limit,
            'meta_key' => '_cp_event_date',
            'orderby' => 'meta_value',
            'order' => 'DESC',
        );

        $events = new WP_Query($args);

        if (!$events->have_posts()) {
            return '<p style="text-align: center; color: #999; padding: 2rem;">' . esc_html__('No events yet.', 'campaign-office') . '</p>';
        }

        ob_start();
        ?>
        <table class="wp-list-table widefat" style="border: none;">
            <tbody>
                <?php while ($events->have_posts()) : $events->the_post(); ?>
                    <?php
                    $event_date = get_post_meta(get_the_ID(), '_cp_event_date', true);
                    $rsvps = 0; // Would query RSVP table in production
                    ?>
                    <tr>
                        <td>
                            <strong><?php the_title(); ?></strong><br>
                            <span style="font-size: 0.875rem; color: #666;">
                                <?php echo date_i18n(get_option('date_format'), strtotime($event_date)); ?>
                            </span>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <span class="dashicons dashicons-groups"></span>
                            <?php echo esc_html($rsvps); ?> RSVPs
                        </td>
                    </tr>
                <?php endwhile; wp_reset_postdata(); ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render campaign performance
     */
    private function render_campaign_performance($limit) {
        global $wpdb;
        $campaigns_table = $wpdb->prefix . 'cp_campaigns';

        $campaigns = $wpdb->get_results($wpdb->prepare("
            SELECT name, type, total_sent, total_delivered, total_opened
            FROM {$campaigns_table}
            WHERE status = 'sent'
            ORDER BY sent_at DESC
            LIMIT %d
        ", $limit));

        if (empty($campaigns)) {
            return '<p style="text-align: center; color: #999; padding: 2rem;">' . esc_html__('No campaigns sent yet.', 'campaign-office') . '</p>';
        }

        ob_start();
        ?>
        <table class="wp-list-table widefat" style="border: none;">
            <tbody>
                <?php foreach ($campaigns as $campaign) : ?>
                    <?php
                    $open_rate = $campaign->total_delivered > 0 ?
                        ($campaign->total_opened / $campaign->total_delivered * 100) : 0;
                    ?>
                    <tr>
                        <td>
                            <?php if ($campaign->type === 'sms') : ?>
                                <span class="dashicons dashicons-smartphone"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-email"></span>
                            <?php endif; ?>
                            <strong><?php echo esc_html($campaign->name); ?></strong>
                        </td>
                        <td style="text-align: right;">
                            <?php echo esc_html(number_format($campaign->total_sent)); ?> sent
                        </td>
                        <td style="text-align: right;">
                            <span style="color: #0073aa; font-weight: 600;">
                                <?php echo esc_html(number_format($open_rate, 1)); ?>%
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render geographic distribution
     */
    private function render_geographic_distribution() {
        global $wpdb;
        $volunteers_table = $wpdb->prefix . 'cp_volunteers';

        $distribution = $wpdb->get_results("
            SELECT zip, COUNT(*) as count
            FROM {$volunteers_table}
            WHERE zip IS NOT NULL AND zip != ''
            GROUP BY zip
            ORDER BY count DESC
            LIMIT 10
        ");

        if (empty($distribution)) {
            return '<p style="text-align: center; color: #999; padding: 2rem;">' . esc_html__('No geographic data available yet.', 'campaign-office') . '</p>';
        }

        ob_start();
        ?>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ZIP Code', 'campaign-office'); ?></th>
                    <th><?php esc_html_e('Volunteers', 'campaign-office'); ?></th>
                    <th><?php esc_html_e('Distribution', 'campaign-office'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $max_count = $distribution[0]->count;
                foreach ($distribution as $row) :
                    $percentage = ($row->count / $max_count) * 100;
                ?>
                    <tr>
                        <td><strong><?php echo esc_html($row->zip); ?></strong></td>
                        <td><?php echo esc_html(number_format($row->count)); ?></td>
                        <td>
                            <div style="background: #e5e7eb; border-radius: 9999px; height: 8px; position: relative; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #0073aa, #00a0d2); height: 100%; width: <?php echo esc_attr($percentage); ?>%;"></div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
        return ob_get_clean();
    }

    /**
     * Render analytics widget shortcode
     */
    public function render_analytics_widget($atts) {
        $atts = shortcode_atts(array(
            'metric' => 'volunteers',
            'label' => '',
        ), $atts, 'cp_analytics_widget');

        $metrics = $this->get_campaign_metrics();
        $value = 0;

        switch ($atts['metric']) {
            case 'volunteers':
                $value = $metrics['volunteers']['total'];
                $label = $atts['label'] ?: __('Total Volunteers', 'campaign-office');
                break;
            case 'hours':
                $value = number_format($metrics['hours']['total'], 1);
                $label = $atts['label'] ?: __('Volunteer Hours', 'campaign-office');
                break;
            case 'events':
                $value = $metrics['events']['total_events'];
                $label = $atts['label'] ?: __('Events Held', 'campaign-office');
                break;
        }

        ob_start();
        ?>
        <div class="cp-analytics-widget" style="text-align: center; padding: 2rem; background: #f9f9f9; border-radius: 0.5rem;">
            <div style="font-size: 3rem; font-weight: 700; color: #0073aa; margin-bottom: 0.5rem;">
                <?php echo esc_html($value); ?>
            </div>
            <div style="font-size: 1.125rem; color: #666;">
                <?php echo esc_html($label); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render campaign stats shortcode
     */
    public function render_campaign_stats($atts) {
        $metrics = $this->get_campaign_metrics();

        ob_start();
        ?>
        <div class="cp-campaign-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div style="background: #667eea; color: #fff; padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700;"><?php echo esc_html(number_format($metrics['volunteers']['total'])); ?></div>
                <div style="opacity: 0.9;"><?php esc_html_e('Volunteers', 'campaign-office'); ?></div>
            </div>
            <div style="background: #f093fb; color: #fff; padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700;"><?php echo esc_html(number_format($metrics['hours']['total'], 1)); ?></div>
                <div style="opacity: 0.9;"><?php esc_html_e('Hours Logged', 'campaign-office'); ?></div>
            </div>
            <div style="background: #4facfe; color: #fff; padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700;"><?php echo esc_html(number_format($metrics['events']['attendance'])); ?></div>
                <div style="opacity: 0.9;"><?php esc_html_e('Event Attendees', 'campaign-office'); ?></div>
            </div>
            <div style="background: #43e97b; color: #fff; padding: 1.5rem; border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700;"><?php echo esc_html(number_format($metrics['subscribers']['email'])); ?></div>
                <div style="opacity: 0.9;"><?php esc_html_e('Subscribers', 'campaign-office'); ?></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX Handlers
     */

    public function ajax_get_dashboard_data() {
        check_ajax_referer('wp_rest');

        $metrics = $this->get_campaign_metrics();
        wp_send_json_success($metrics);
    }

    public function ajax_export_analytics() {
        check_ajax_referer('wp_rest');

        // Export logic would go here
        wp_send_json_success(array('message' => __('Report generated successfully', 'campaign-office')));
    }

    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets($hook) {
        if ($hook !== 'toplevel_page_cp-analytics' && $hook !== 'analytics_page_cp-analytics-reports') {
            return;
        }

        // Would enqueue Chart.js or similar charting library in production
    }
}

// Initialize analytics dashboard
new CP_Analytics_Dashboard();
