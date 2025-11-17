<?php
/**
 * Campaign Performance Dashboard Widgets
 *
 * Provides campaign dashboard widgets with demo data for fundraising progress,
 * volunteer counts, event attendance, endorsements, social media reach,
 * election countdown, and campaign statistics.
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Campaign_Widgets
 *
 * Handles all campaign performance dashboard widgets
 */
class CP_Campaign_Widgets {

    /**
     * Constructor
     */
    public function __construct() {
        // Register dashboard widgets
        add_action('wp_dashboard_setup', array($this, 'register_dashboard_widgets'));

        // Enqueue widget styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_widget_styles'));

        // Register widget area for frontend display
        add_action('widgets_init', array($this, 'register_widget_areas'));

        // Register actual widgets
        add_action('widgets_init', array($this, 'register_widgets'));

        // AJAX handlers for widget updates
        add_action('wp_ajax_cp_update_widget_data', array($this, 'handle_widget_update'));

        // Add settings page
        add_action('admin_menu', array($this, 'add_widget_settings_page'));
    }

    /**
     * Register dashboard widgets
     *
     * Adds campaign performance widgets to WordPress admin dashboard
     */
    public function register_dashboard_widgets() {
        // Only show to users who can edit posts
        if (!current_user_can('edit_posts')) {
            return;
        }

        // Fundraising Progress Widget
        wp_add_dashboard_widget(
            'cp_fundraising_widget',
            __('Campaign Fundraising', 'campaignpress'),
            array($this, 'render_fundraising_widget')
        );

        // Volunteer Count Widget
        wp_add_dashboard_widget(
            'cp_volunteer_widget',
            __('Volunteer Engagement', 'campaignpress'),
            array($this, 'render_volunteer_widget')
        );

        // Event Attendance Widget
        wp_add_dashboard_widget(
            'cp_event_widget',
            __('Event Attendance', 'campaignpress'),
            array($this, 'render_event_widget')
        );

        // Endorsement Count Widget
        wp_add_dashboard_widget(
            'cp_endorsement_widget',
            __('Endorsements', 'campaignpress'),
            array($this, 'render_endorsement_widget')
        );

        // Social Media Reach Widget
        wp_add_dashboard_widget(
            'cp_social_widget',
            __('Social Media Reach', 'campaignpress'),
            array($this, 'render_social_widget')
        );

        // Election Countdown Widget
        wp_add_dashboard_widget(
            'cp_countdown_widget',
            __('Days Until Election', 'campaignpress'),
            array($this, 'render_countdown_widget')
        );

        // Campaign Statistics Dashboard
        wp_add_dashboard_widget(
            'cp_statistics_widget',
            __('Campaign Statistics', 'campaignpress'),
            array($this, 'render_statistics_widget')
        );
    }

    /**
     * Render Fundraising Progress Widget
     *
     * Shows fundraising goal progress with demo data
     */
    public function render_fundraising_widget() {
        // Get saved data or use demo data
        $goal = get_option('cp_fundraising_goal', 100000);
        $raised = get_option('cp_fundraising_raised', 67500);
        $donors = get_option('cp_fundraising_donors', 342);
        $avg_donation = $raised / max($donors, 1);

        $percentage = ($raised / $goal) * 100;
        $remaining = $goal - $raised;

        ?>
        <div class="cp-widget cp-fundraising-widget">
            <div class="cp-widget-stats">
                <div class="cp-stat-item">
                    <span class="cp-stat-label"><?php esc_html_e('Goal', 'campaignpress'); ?></span>
                    <span class="cp-stat-value">$<?php echo esc_html(number_format($goal)); ?></span>
                </div>
                <div class="cp-stat-item">
                    <span class="cp-stat-label"><?php esc_html_e('Raised', 'campaignpress'); ?></span>
                    <span class="cp-stat-value cp-primary">$<?php echo esc_html(number_format($raised)); ?></span>
                </div>
                <div class="cp-stat-item">
                    <span class="cp-stat-label"><?php esc_html_e('Remaining', 'campaignpress'); ?></span>
                    <span class="cp-stat-value">$<?php echo esc_html(number_format($remaining)); ?></span>
                </div>
            </div>

            <div class="cp-progress-bar">
                <div class="cp-progress-fill" style="width: <?php echo esc_attr(min($percentage, 100)); ?>%;">
                    <span class="cp-progress-text"><?php echo esc_html(round($percentage, 1)); ?>%</span>
                </div>
            </div>

            <div class="cp-widget-meta">
                <p>
                    <strong><?php echo esc_html(number_format($donors)); ?></strong> <?php esc_html_e('donors', 'campaignpress'); ?>
                    <span class="separator">•</span>
                    <?php esc_html_e('Avg donation:', 'campaignpress'); ?> <strong>$<?php echo esc_html(number_format($avg_donation, 2)); ?></strong>
                </p>
            </div>

            <div class="cp-widget-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-widget-settings')); ?>" class="button button-small">
                    <?php esc_html_e('Update Data', 'campaignpress'); ?>
                </a>
                <span class="cp-demo-badge"><?php esc_html_e('Demo Data', 'campaignpress'); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Render Volunteer Count Widget
     *
     * Shows volunteer engagement metrics with demo data
     */
    public function render_volunteer_widget() {
        global $wpdb;

        // Get actual volunteer count from database
        $table_name = $wpdb->prefix . 'cp_volunteers';
        $total_volunteers = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        // Use demo data if no real data exists
        if ($total_volunteers == 0) {
            $total_volunteers = get_option('cp_volunteer_count', 487);
        }

        $active_this_week = get_option('cp_volunteer_active_week', 142);
        $new_this_month = get_option('cp_volunteer_new_month', 73);
        $volunteer_hours = get_option('cp_volunteer_hours', 1256);

        ?>
        <div class="cp-widget cp-volunteer-widget">
            <div class="cp-widget-header">
                <div class="cp-big-number">
                    <?php echo esc_html(number_format($total_volunteers)); ?>
                </div>
                <div class="cp-big-label"><?php esc_html_e('Total Volunteers', 'campaignpress'); ?></div>
            </div>

            <div class="cp-widget-grid">
                <div class="cp-grid-item">
                    <span class="cp-grid-number"><?php echo esc_html(number_format($active_this_week)); ?></span>
                    <span class="cp-grid-label"><?php esc_html_e('Active This Week', 'campaignpress'); ?></span>
                </div>
                <div class="cp-grid-item">
                    <span class="cp-grid-number cp-success"><?php echo esc_html(number_format($new_this_month)); ?></span>
                    <span class="cp-grid-label"><?php esc_html_e('New This Month', 'campaignpress'); ?></span>
                </div>
                <div class="cp-grid-item">
                    <span class="cp-grid-number"><?php echo esc_html(number_format($volunteer_hours)); ?></span>
                    <span class="cp-grid-label"><?php esc_html_e('Hours Logged', 'campaignpress'); ?></span>
                </div>
            </div>

            <div class="cp-widget-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-volunteers')); ?>" class="button button-primary button-small">
                    <?php esc_html_e('Manage Volunteers', 'campaignpress'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Event Attendance Widget
     *
     * Shows event attendance statistics with demo data
     */
    public function render_event_widget() {
        // Get actual events if custom post type exists
        $events_query = new WP_Query(array(
            'post_type' => 'cp_event',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'event_date',
                    'value' => current_time('Y-m-d'),
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        ));

        $upcoming_events = $events_query->found_posts;

        // Use demo data if no events
        if ($upcoming_events == 0) {
            $upcoming_events = get_option('cp_event_upcoming', 12);
        }

        $total_attendees = get_option('cp_event_attendees', 1847);
        $avg_attendance = get_option('cp_event_avg_attendance', 154);
        $rsvp_rate = get_option('cp_event_rsvp_rate', 78);

        ?>
        <div class="cp-widget cp-event-widget">
            <div class="cp-widget-grid">
                <div class="cp-grid-item cp-featured">
                    <span class="cp-grid-number cp-primary"><?php echo esc_html(number_format($upcoming_events)); ?></span>
                    <span class="cp-grid-label"><?php esc_html_e('Upcoming Events', 'campaignpress'); ?></span>
                </div>
                <div class="cp-grid-item">
                    <span class="cp-grid-number"><?php echo esc_html(number_format($total_attendees)); ?></span>
                    <span class="cp-grid-label"><?php esc_html_e('Total Attendees', 'campaignpress'); ?></span>
                </div>
                <div class="cp-grid-item">
                    <span class="cp-grid-number"><?php echo esc_html(number_format($avg_attendance)); ?></span>
                    <span class="cp-grid-label"><?php esc_html_e('Avg per Event', 'campaignpress'); ?></span>
                </div>
                <div class="cp-grid-item">
                    <span class="cp-grid-number cp-success"><?php echo esc_html($rsvp_rate); ?>%</span>
                    <span class="cp-grid-label"><?php esc_html_e('RSVP Rate', 'campaignpress'); ?></span>
                </div>
            </div>

            <div class="cp-widget-actions">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=cp_event')); ?>" class="button button-small">
                    <?php esc_html_e('View Events', 'campaignpress'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=cp_event')); ?>" class="button button-primary button-small">
                    <?php esc_html_e('Add Event', 'campaignpress'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Endorsement Count Widget
     *
     * Shows endorsement statistics
     */
    public function render_endorsement_widget() {
        // Get actual endorsements if custom post type exists
        $endorsements_query = new WP_Query(array(
            'post_type' => 'cp_endorsement',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));

        $total_endorsements = $endorsements_query->found_posts;

        // Use demo data if no endorsements
        if ($total_endorsements == 0) {
            $total_endorsements = get_option('cp_endorsement_count', 87);
        }

        $recent_endorsements = get_option('cp_endorsement_recent', 14);
        $categories = get_option('cp_endorsement_categories', array(
            'Elected Officials' => 12,
            'Organizations' => 23,
            'Community Leaders' => 31,
            'Businesses' => 21
        ));

        ?>
        <div class="cp-widget cp-endorsement-widget">
            <div class="cp-widget-header">
                <div class="cp-big-number cp-primary">
                    <?php echo esc_html(number_format($total_endorsements)); ?>
                </div>
                <div class="cp-big-label"><?php esc_html_e('Total Endorsements', 'campaignpress'); ?></div>
            </div>

            <div class="cp-widget-meta">
                <p class="cp-highlight">
                    <span class="cp-badge cp-badge-success">+<?php echo esc_html($recent_endorsements); ?></span>
                    <?php esc_html_e('new in the last 30 days', 'campaignpress'); ?>
                </p>
            </div>

            <div class="cp-endorsement-breakdown">
                <h4><?php esc_html_e('By Category:', 'campaignpress'); ?></h4>
                <?php foreach ($categories as $category => $count) : ?>
                    <div class="cp-breakdown-item">
                        <span class="cp-breakdown-label"><?php echo esc_html($category); ?></span>
                        <span class="cp-breakdown-value"><?php echo esc_html($count); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cp-widget-actions">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=cp_endorsement')); ?>" class="button button-primary button-small">
                    <?php esc_html_e('Manage Endorsements', 'campaignpress'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Social Media Reach Widget
     *
     * Shows social media engagement metrics with demo data
     */
    public function render_social_widget() {
        $total_followers = get_option('cp_social_total_followers', 24567);
        $weekly_growth = get_option('cp_social_weekly_growth', 487);
        $engagement_rate = get_option('cp_social_engagement_rate', 4.2);

        $platforms = get_option('cp_social_platforms', array(
            'Facebook' => array('followers' => 8234, 'growth' => 156),
            'Twitter' => array('followers' => 12478, 'growth' => 243),
            'Instagram' => array('followers' => 3855, 'growth' => 88)
        ));

        ?>
        <div class="cp-widget cp-social-widget">
            <div class="cp-widget-header">
                <div class="cp-big-number">
                    <?php echo esc_html(number_format($total_followers)); ?>
                </div>
                <div class="cp-big-label"><?php esc_html_e('Total Followers', 'campaignpress'); ?></div>
            </div>

            <div class="cp-widget-stats">
                <div class="cp-stat-item">
                    <span class="cp-stat-value cp-success">+<?php echo esc_html(number_format($weekly_growth)); ?></span>
                    <span class="cp-stat-label"><?php esc_html_e('This Week', 'campaignpress'); ?></span>
                </div>
                <div class="cp-stat-item">
                    <span class="cp-stat-value"><?php echo esc_html($engagement_rate); ?>%</span>
                    <span class="cp-stat-label"><?php esc_html_e('Engagement Rate', 'campaignpress'); ?></span>
                </div>
            </div>

            <div class="cp-social-platforms">
                <?php foreach ($platforms as $platform => $data) : ?>
                    <div class="cp-platform-item">
                        <span class="cp-platform-name"><?php echo esc_html($platform); ?></span>
                        <span class="cp-platform-stats">
                            <strong><?php echo esc_html(number_format($data['followers'])); ?></strong>
                            <span class="cp-growth cp-success">+<?php echo esc_html($data['growth']); ?></span>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cp-widget-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-widget-settings#social')); ?>" class="button button-small">
                    <?php esc_html_e('Update Social Data', 'campaignpress'); ?>
                </a>
                <span class="cp-demo-badge"><?php esc_html_e('Demo Data', 'campaignpress'); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Render Election Countdown Widget
     *
     * Shows days until election with key milestones
     */
    public function render_countdown_widget() {
        $election_date = get_option('cp_election_date', '2026-11-03'); // Default to next general election
        $election_timestamp = strtotime($election_date);
        $current_timestamp = current_time('timestamp');

        $days_remaining = max(0, floor(($election_timestamp - $current_timestamp) / DAY_IN_SECONDS));

        // Calculate milestones
        $registration_deadline = get_option('cp_voter_registration_deadline', date('Y-m-d', $election_timestamp - (30 * DAY_IN_SECONDS)));
        $early_voting_start = get_option('cp_early_voting_start', date('Y-m-d', $election_timestamp - (14 * DAY_IN_SECONDS)));

        $days_to_registration = max(0, floor((strtotime($registration_deadline) - $current_timestamp) / DAY_IN_SECONDS));
        $days_to_early_voting = max(0, floor((strtotime($early_voting_start) - $current_timestamp) / DAY_IN_SECONDS));

        ?>
        <div class="cp-widget cp-countdown-widget">
            <div class="cp-countdown-display">
                <div class="cp-countdown-number">
                    <?php echo esc_html(number_format($days_remaining)); ?>
                </div>
                <div class="cp-countdown-label"><?php esc_html_e('Days Until Election', 'campaignpress'); ?></div>
                <div class="cp-countdown-date"><?php echo esc_html(date_i18n(get_option('date_format'), $election_timestamp)); ?></div>
            </div>

            <div class="cp-milestones">
                <h4><?php esc_html_e('Key Milestones:', 'campaignpress'); ?></h4>

                <?php if ($days_to_registration > 0) : ?>
                    <div class="cp-milestone-item">
                        <span class="cp-milestone-icon">📋</span>
                        <div class="cp-milestone-details">
                            <strong><?php esc_html_e('Voter Registration Deadline', 'campaignpress'); ?></strong>
                            <span class="cp-milestone-countdown">
                                <?php
                                printf(
                                    esc_html(_n('%s day', '%s days', $days_to_registration, 'campaignpress')),
                                    esc_html(number_format($days_to_registration))
                                );
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($days_to_early_voting > 0) : ?>
                    <div class="cp-milestone-item">
                        <span class="cp-milestone-icon">🗳️</span>
                        <div class="cp-milestone-details">
                            <strong><?php esc_html_e('Early Voting Begins', 'campaignpress'); ?></strong>
                            <span class="cp-milestone-countdown">
                                <?php
                                printf(
                                    esc_html(_n('%s day', '%s days', $days_to_early_voting, 'campaignpress')),
                                    esc_html(number_format($days_to_early_voting))
                                );
                                ?>
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="cp-milestone-item">
                    <span class="cp-milestone-icon">📅</span>
                    <div class="cp-milestone-details">
                        <strong><?php esc_html_e('Election Day', 'campaignpress'); ?></strong>
                        <span class="cp-milestone-countdown">
                            <?php
                            printf(
                                esc_html(_n('%s day', '%s days', $days_remaining, 'campaignpress')),
                                esc_html(number_format($days_remaining))
                            );
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="cp-widget-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=cp-widget-settings#election')); ?>" class="button button-small">
                    <?php esc_html_e('Update Dates', 'campaignpress'); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render Campaign Statistics Widget
     *
     * Shows comprehensive campaign statistics dashboard
     */
    public function render_statistics_widget() {
        // Gather data from various sources
        global $wpdb;

        // Get post counts
        $blog_posts = wp_count_posts('post')->publish;
        $press_releases = wp_count_posts('cp_press_release')->publish ?? 0;
        $team_members = wp_count_posts('cp_team')->publish ?? 0;

        // Get volunteer data
        $volunteer_table = $wpdb->prefix . 'cp_volunteers';
        $total_volunteers = $wpdb->get_var("SELECT COUNT(*) FROM $volunteer_table");

        if ($total_volunteers == 0) {
            $total_volunteers = 487; // Demo data
        }

        // Website stats
        $total_users = count_users();
        $total_pages = wp_count_posts('page')->publish;

        ?>
        <div class="cp-widget cp-statistics-widget">
            <h3><?php esc_html_e('Campaign Overview', 'campaignpress'); ?></h3>

            <div class="cp-stats-grid">
                <div class="cp-stat-box">
                    <div class="cp-stat-icon">💰</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-number">$<?php echo esc_html(number_format(get_option('cp_fundraising_raised', 67500))); ?></div>
                        <div class="cp-stat-title"><?php esc_html_e('Raised', 'campaignpress'); ?></div>
                    </div>
                </div>

                <div class="cp-stat-box">
                    <div class="cp-stat-icon">👥</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-number"><?php echo esc_html(number_format($total_volunteers)); ?></div>
                        <div class="cp-stat-title"><?php esc_html_e('Volunteers', 'campaignpress'); ?></div>
                    </div>
                </div>

                <div class="cp-stat-box">
                    <div class="cp-stat-icon">📱</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-number"><?php echo esc_html(number_format(get_option('cp_social_total_followers', 24567))); ?></div>
                        <div class="cp-stat-title"><?php esc_html_e('Followers', 'campaignpress'); ?></div>
                    </div>
                </div>

                <div class="cp-stat-box">
                    <div class="cp-stat-icon">✅</div>
                    <div class="cp-stat-content">
                        <div class="cp-stat-number"><?php echo esc_html(number_format(get_option('cp_endorsement_count', 87))); ?></div>
                        <div class="cp-stat-title"><?php esc_html_e('Endorsements', 'campaignpress'); ?></div>
                    </div>
                </div>
            </div>

            <div class="cp-content-stats">
                <h4><?php esc_html_e('Content Statistics:', 'campaignpress'); ?></h4>
                <table class="cp-stats-table">
                    <tr>
                        <td><?php esc_html_e('Blog Posts', 'campaignpress'); ?></td>
                        <td><strong><?php echo esc_html(number_format($blog_posts)); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Press Releases', 'campaignpress'); ?></td>
                        <td><strong><?php echo esc_html(number_format($press_releases)); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Team Members', 'campaignpress'); ?></td>
                        <td><strong><?php echo esc_html(number_format($team_members)); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Total Pages', 'campaignpress'); ?></td>
                        <td><strong><?php echo esc_html(number_format($total_pages)); ?></strong></td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Registered Users', 'campaignpress'); ?></td>
                        <td><strong><?php echo esc_html(number_format($total_users['total_users'])); ?></strong></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue widget styles
     *
     * Adds inline CSS for dashboard widgets (mobile-responsive)
     */
    public function enqueue_widget_styles($hook) {
        // Only load on dashboard and widget settings page
        if ($hook !== 'index.php' && $hook !== 'appearance_page_cp-widget-settings') {
            return;
        }

        $css = "
        /* Campaign Widget Styles */
        .cp-widget {
            padding: 12px;
        }

        .cp-widget-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .cp-big-number {
            font-size: 48px;
            font-weight: 700;
            line-height: 1;
            color: #0073aa;
            margin-bottom: 8px;
        }

        .cp-big-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Stats Grid */
        .cp-widget-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .cp-stat-item {
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .cp-stat-label {
            display: block;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .cp-stat-value {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: #23282d;
        }

        .cp-stat-value.cp-primary {
            color: #0073aa;
        }

        .cp-stat-value.cp-success {
            color: #46b450;
        }

        /* Progress Bar */
        .cp-progress-bar {
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
            position: relative;
        }

        .cp-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0073aa, #00a0d2);
            border-radius: 15px;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cp-progress-text {
            color: #fff;
            font-weight: 700;
            font-size: 14px;
        }

        /* Widget Grid */
        .cp-widget-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 15px;
        }

        .cp-grid-item {
            text-align: center;
            padding: 15px 10px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e5e5e5;
        }

        .cp-grid-item.cp-featured {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #0073aa, #00a0d2);
            color: #fff;
            border: none;
        }

        .cp-grid-item.cp-featured .cp-grid-label {
            color: rgba(255, 255, 255, 0.9);
        }

        .cp-grid-number {
            display: block;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .cp-grid-label {
            display: block;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }

        /* Widget Meta */
        .cp-widget-meta {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .cp-widget-meta p {
            margin: 0;
            font-size: 13px;
        }

        .cp-widget-meta .separator {
            margin: 0 8px;
            color: #ccc;
        }

        .cp-highlight {
            background: #fff3cd;
            border-left: 3px solid #ffc107;
            padding: 10px;
            margin: 0;
        }

        /* Badge */
        .cp-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 12px;
        }

        .cp-badge-success {
            background: #46b450;
            color: #fff;
        }

        /* Endorsement Breakdown */
        .cp-endorsement-breakdown {
            margin-bottom: 15px;
        }

        .cp-endorsement-breakdown h4 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 13px;
            color: #666;
        }

        .cp-breakdown-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .cp-breakdown-item:last-child {
            border-bottom: none;
        }

        .cp-breakdown-label {
            font-size: 13px;
        }

        .cp-breakdown-value {
            font-weight: 700;
            color: #0073aa;
        }

        /* Social Platforms */
        .cp-social-platforms {
            margin-bottom: 15px;
        }

        .cp-platform-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .cp-platform-name {
            font-weight: 600;
            font-size: 13px;
        }

        .cp-platform-stats {
            font-size: 13px;
        }

        .cp-growth {
            margin-left: 8px;
            font-size: 11px;
        }

        /* Countdown */
        .cp-countdown-display {
            text-align: center;
            padding: 30px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .cp-countdown-number {
            font-size: 72px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 10px;
        }

        .cp-countdown-label {
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .cp-countdown-date {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Milestones */
        .cp-milestones {
            margin-bottom: 15px;
        }

        .cp-milestones h4 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 13px;
            color: #666;
        }

        .cp-milestone-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .cp-milestone-icon {
            font-size: 24px;
            margin-right: 12px;
        }

        .cp-milestone-details {
            flex: 1;
        }

        .cp-milestone-details strong {
            display: block;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .cp-milestone-countdown {
            font-size: 12px;
            color: #0073aa;
            font-weight: 600;
        }

        /* Statistics Grid */
        .cp-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .cp-stat-box {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e5e5e5;
        }

        .cp-stat-icon {
            font-size: 32px;
            margin-right: 12px;
        }

        .cp-stat-content {
            flex: 1;
        }

        .cp-stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #0073aa;
            margin-bottom: 3px;
        }

        .cp-stat-title {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }

        /* Content Stats */
        .cp-content-stats {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .cp-content-stats h4 {
            margin-top: 0;
            margin-bottom: 12px;
            font-size: 13px;
            color: #666;
        }

        .cp-stats-table {
            width: 100%;
            font-size: 13px;
        }

        .cp-stats-table td {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .cp-stats-table td:last-child {
            text-align: right;
        }

        .cp-stats-table tr:last-child td {
            border-bottom: none;
        }

        /* Widget Actions */
        .cp-widget-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .cp-demo-badge {
            font-size: 11px;
            color: #999;
            font-style: italic;
        }

        /* Mobile Responsive */
        @media (max-width: 782px) {
            .cp-big-number {
                font-size: 36px;
            }

            .cp-countdown-number {
                font-size: 48px;
            }

            .cp-widget-grid,
            .cp-stats-grid {
                grid-template-columns: 1fr;
            }

            .cp-widget-stats {
                grid-template-columns: 1fr;
            }

            .cp-widget-actions {
                flex-direction: column;
                gap: 10px;
            }

            .cp-widget-actions .button {
                width: 100%;
            }
        }

        /* Color Utilities */
        .cp-primary {
            color: #0073aa;
        }

        .cp-success {
            color: #46b450;
        }

        .cp-warning {
            color: #ffb900;
        }

        .cp-danger {
            color: #dc3232;
        }
        ";

        wp_add_inline_style('dashboard', $css);
    }

    /**
     * Register widget areas for frontend display
     */
    public function register_widget_areas() {
        register_sidebar(array(
            'name'          => esc_html__('Campaign Dashboard', 'campaignpress'),
            'id'            => 'campaign-dashboard',
            'description'   => esc_html__('Campaign performance widgets for frontend display', 'campaignpress'),
            'before_widget' => '<div id="%1$s" class="campaign-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="campaign-widget-title">',
            'after_title'   => '</h3>',
        ));
    }

    /**
     * Register WordPress widgets
     */
    public function register_widgets() {
        // Widgets will be added here in future updates
        // For now, we're focusing on dashboard widgets
    }

    /**
     * Handle AJAX widget data updates
     */
    public function handle_widget_update() {
        // Verify nonce
        check_ajax_referer('cp_widget_update', 'nonce');

        // Verify permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'campaignpress')));
        }

        // Get and sanitize data
        $widget_type = isset($_POST['widget_type']) ? sanitize_text_field($_POST['widget_type']) : '';
        $data = isset($_POST['data']) ? $_POST['data'] : array();

        // Update based on widget type
        switch ($widget_type) {
            case 'fundraising':
                update_option('cp_fundraising_goal', absint($data['goal']));
                update_option('cp_fundraising_raised', absint($data['raised']));
                update_option('cp_fundraising_donors', absint($data['donors']));
                break;

            case 'volunteers':
                update_option('cp_volunteer_count', absint($data['total']));
                update_option('cp_volunteer_active_week', absint($data['active_week']));
                update_option('cp_volunteer_new_month', absint($data['new_month']));
                update_option('cp_volunteer_hours', absint($data['hours']));
                break;

            // Add more cases as needed
        }

        wp_send_json_success(array('message' => __('Widget data updated successfully', 'campaignpress')));
    }

    /**
     * Add widget settings page
     */
    public function add_widget_settings_page() {
        add_theme_page(
            __('Campaign Widgets', 'campaignpress'),
            __('Campaign Widgets', 'campaignpress'),
            'manage_options',
            'cp-widget-settings',
            array($this, 'render_widget_settings_page')
        );
    }

    /**
     * Render widget settings page
     */
    public function render_widget_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Handle form submission
        if (isset($_POST['cp_widget_settings_nonce']) &&
            wp_verify_nonce($_POST['cp_widget_settings_nonce'], 'cp_widget_settings')) {

            // Update election date
            if (isset($_POST['election_date'])) {
                update_option('cp_election_date', sanitize_text_field($_POST['election_date']));
            }

            // Update fundraising data
            if (isset($_POST['fundraising_goal'])) {
                update_option('cp_fundraising_goal', absint($_POST['fundraising_goal']));
                update_option('cp_fundraising_raised', absint($_POST['fundraising_raised']));
                update_option('cp_fundraising_donors', absint($_POST['fundraising_donors']));
            }

            echo '<div class="notice notice-success"><p>' . esc_html__('Settings saved successfully.', 'campaignpress') . '</p></div>';
        }

        // Get current values
        $election_date = get_option('cp_election_date', '2026-11-03');
        $fundraising_goal = get_option('cp_fundraising_goal', 100000);
        $fundraising_raised = get_option('cp_fundraising_raised', 67500);
        $fundraising_donors = get_option('cp_fundraising_donors', 342);

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <p><?php esc_html_e('Configure your campaign dashboard widgets. These widgets display campaign performance metrics in your WordPress admin dashboard.', 'campaignpress'); ?></p>

            <form method="post" action="">
                <?php wp_nonce_field('cp_widget_settings', 'cp_widget_settings_nonce'); ?>

                <h2 id="election"><?php esc_html_e('Election Settings', 'campaignpress'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="election_date"><?php esc_html_e('Election Date', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <input type="date" name="election_date" id="election_date" value="<?php echo esc_attr($election_date); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Set the election date for countdown widget.', 'campaignpress'); ?></p>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e('Fundraising Data', 'campaignpress'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="fundraising_goal"><?php esc_html_e('Fundraising Goal', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="fundraising_goal" id="fundraising_goal" value="<?php echo esc_attr($fundraising_goal); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="fundraising_raised"><?php esc_html_e('Amount Raised', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="fundraising_raised" id="fundraising_raised" value="<?php echo esc_attr($fundraising_raised); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="fundraising_donors"><?php esc_html_e('Number of Donors', 'campaignpress'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="fundraising_donors" id="fundraising_donors" value="<?php echo esc_attr($fundraising_donors); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Settings', 'campaignpress')); ?>
            </form>

            <div class="card">
                <h2><?php esc_html_e('Available Widgets', 'campaignpress'); ?></h2>
                <p><?php esc_html_e('The following widgets are available on your dashboard:', 'campaignpress'); ?></p>
                <ul style="list-style: disc; margin-left: 2em;">
                    <li><strong><?php esc_html_e('Campaign Fundraising', 'campaignpress'); ?></strong> - <?php esc_html_e('Track fundraising progress and donations', 'campaignpress'); ?></li>
                    <li><strong><?php esc_html_e('Volunteer Engagement', 'campaignpress'); ?></strong> - <?php esc_html_e('Monitor volunteer signups and activity', 'campaignpress'); ?></li>
                    <li><strong><?php esc_html_e('Event Attendance', 'campaignpress'); ?></strong> - <?php esc_html_e('View upcoming events and attendance stats', 'campaignpress'); ?></li>
                    <li><strong><?php esc_html_e('Endorsements', 'campaignpress'); ?></strong> - <?php esc_html_e('Track endorsements by category', 'campaignpress'); ?></li>
                    <li><strong><?php esc_html_e('Social Media Reach', 'campaignpress'); ?></strong> - <?php esc_html_e('Monitor social media followers and growth', 'campaignpress'); ?></li>
                    <li><strong><?php esc_html_e('Days Until Election', 'campaignpress'); ?></strong> - <?php esc_html_e('Countdown to election day with key milestones', 'campaignpress'); ?></li>
                    <li><strong><?php esc_html_e('Campaign Statistics', 'campaignpress'); ?></strong> - <?php esc_html_e('Comprehensive campaign overview', 'campaignpress'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
}

// Initialize campaign widgets
new CP_Campaign_Widgets();
