<?php
/**
 * Elementor Page Builder Widgets for CampaignPress
 *
 * Provides custom Elementor widgets specifically designed for political campaigns,
 * including donation buttons, progress meters, issue cards, endorsements, events,
 * volunteer signups, social media, team members, and testimonials.
 *
 * @package CampaignPress
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class CP_Elementor_Widgets
 *
 * Main handler for Elementor widget registration and initialization
 */
class CP_Elementor_Widgets {

    /**
     * Constructor
     */
    public function __construct() {
        // Register widget category
        add_action('elementor/elements/categories_registered', array($this, 'register_widget_category'));

        // Register widgets
        add_action('elementor/widgets/register', array($this, 'register_widgets'));

        // Enqueue widget styles
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_widget_styles'));
    }

    /**
     * Register CampaignPress widget category
     *
     * @param object $elements_manager Elementor elements manager
     */
    public function register_widget_category($elements_manager) {
        $elements_manager->add_category(
            'campaignpress',
            array(
                'title' => __('CampaignPress', 'campaignpress'),
                'icon'  => 'fa fa-flag',
            )
        );
    }

    /**
     * Register all widgets
     *
     * @param object $widgets_manager Elementor widgets manager
     */
    public function register_widgets($widgets_manager) {
        // All widget classes are defined in this file
        // Register widgets
        $widgets_manager->register(new \CP_Elementor_Donation_Button());
        $widgets_manager->register(new \CP_Elementor_Progress_Meter());
        $widgets_manager->register(new \CP_Elementor_Issue_Card());
        $widgets_manager->register(new \CP_Elementor_Endorsement_Grid());
        $widgets_manager->register(new \CP_Elementor_Event_Countdown());
        $widgets_manager->register(new \CP_Elementor_Volunteer_CTA());
        $widgets_manager->register(new \CP_Elementor_Social_Follow());
        $widgets_manager->register(new \CP_Elementor_Team_Member());
        $widgets_manager->register(new \CP_Elementor_Event_RSVP());
        $widgets_manager->register(new \CP_Elementor_Testimonial());
    }

    /**
     * Enqueue widget styles
     */
    public function enqueue_widget_styles() {
        wp_enqueue_style(
            'cp-elementor-widgets',
            CAMPAIGNPRESS_ASSETS_URI . '/css/elementor-widgets.css',
            array(),
            CAMPAIGNPRESS_VERSION
        );
    }
}

// Initialize if Elementor is loaded
if (did_action('elementor/loaded')) {
    new CP_Elementor_Widgets();
}

/**
 * Base Widget Class
 *
 * Provides common functionality for all CampaignPress Elementor widgets
 */
abstract class CP_Elementor_Widget_Base extends \Elementor\Widget_Base {

    /**
     * Get widget categories
     *
     * @return array
     */
    public function get_categories() {
        return array('campaignpress');
    }

    /**
     * Get common style controls
     *
     * @return array
     */
    protected function get_common_style_controls() {
        return array(
            'background_color' => array(
                'label'     => __('Background Color', 'campaignpress'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}}' => 'background-color: {{VALUE}};',
                ),
            ),
            'padding' => array(
                'label'      => __('Padding', 'campaignpress'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            ),
            'margin' => array(
                'label'      => __('Margin', 'campaignpress'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors'  => array(
                    '{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            ),
            'border_radius' => array(
                'label'      => __('Border Radius', 'campaignpress'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors'  => array(
                    '{{WRAPPER}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            ),
        );
    }
}

// =============================================================================
// WIDGET 1: Donation Button Widget
// =============================================================================

/**
 * Donation Button Widget
 */
class CP_Elementor_Donation_Button extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_donation_button';
    }

    public function get_title() {
        return __('Donation Button', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-button';
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'processor',
            array(
                'label'   => __('Payment Processor', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'actblue',
                'options' => array(
                    'actblue'  => __('ActBlue', 'campaignpress'),
                    'winred'   => __('WinRed', 'campaignpress'),
                    'paypal'   => __('PayPal', 'campaignpress'),
                    'stripe'   => __('Stripe', 'campaignpress'),
                    'square'   => __('Square', 'campaignpress'),
                    'donorbox' => __('Donorbox', 'campaignpress'),
                ),
            )
        );

        $this->add_control(
            'button_text',
            array(
                'label'   => __('Button Text', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate Now', 'campaignpress'),
            )
        );

        $this->add_control(
            'amounts',
            array(
                'label'       => __('Quick Amounts', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => '25,50,100,250,500',
                'description' => __('Comma-separated amounts', 'campaignpress'),
            )
        );

        $this->add_control(
            'show_recurring',
            array(
                'label'        => __('Show Recurring Options', 'campaignpress'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __('Yes', 'campaignpress'),
                'label_off'    => __('No', 'campaignpress'),
                'return_value' => 'yes',
                'default'      => 'yes',
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'button_color',
            array(
                'label'     => __('Button Color', 'campaignpress'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#0073aa',
                'selectors' => array(
                    '{{WRAPPER}} .cp-donate-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .cp-donate-btn',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $shortcode = sprintf(
            '[cp_donation_button processor="%s" amounts="%s" text="%s"]',
            esc_attr($settings['processor']),
            esc_attr($settings['amounts']),
            esc_attr($settings['button_text'])
        );

        echo do_shortcode($shortcode);
    }
}

// =============================================================================
// WIDGET 2: Campaign Progress Meter Widget
// =============================================================================

/**
 * Campaign Progress Meter Widget
 */
class CP_Elementor_Progress_Meter extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_progress_meter';
    }

    public function get_title() {
        return __('Progress Meter', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-skill-bar';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'title',
            array(
                'label'   => __('Title', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Fundraising Progress', 'campaignpress'),
            )
        );

        $this->add_control(
            'goal',
            array(
                'label'   => __('Goal Amount', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 100000,
            )
        );

        $this->add_control(
            'current',
            array(
                'label'   => __('Current Amount', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 67500,
            )
        );

        $this->add_control(
            'prefix',
            array(
                'label'   => __('Currency Symbol', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => '$',
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'bar_color',
            array(
                'label'     => __('Progress Bar Color', 'campaignpress'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#0073aa',
                'selectors' => array(
                    '{{WRAPPER}} .cp-progress-fill' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'bar_height',
            array(
                'label'      => __('Bar Height', 'campaignpress'),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 10,
                        'max' => 100,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 30,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .cp-progress-bar' => 'height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $percentage = ($settings['current'] / $settings['goal']) * 100;
        $remaining = $settings['goal'] - $settings['current'];

        ?>
        <div class="cp-progress-meter-widget">
            <?php if (!empty($settings['title'])) : ?>
                <h3 class="cp-progress-title"><?php echo esc_html($settings['title']); ?></h3>
            <?php endif; ?>

            <div class="cp-progress-stats">
                <div class="cp-stat">
                    <span class="cp-stat-label"><?php esc_html_e('Goal', 'campaignpress'); ?></span>
                    <span class="cp-stat-value"><?php echo esc_html($settings['prefix'] . number_format($settings['goal'])); ?></span>
                </div>
                <div class="cp-stat">
                    <span class="cp-stat-label"><?php esc_html_e('Raised', 'campaignpress'); ?></span>
                    <span class="cp-stat-value"><?php echo esc_html($settings['prefix'] . number_format($settings['current'])); ?></span>
                </div>
                <div class="cp-stat">
                    <span class="cp-stat-label"><?php esc_html_e('Remaining', 'campaignpress'); ?></span>
                    <span class="cp-stat-value"><?php echo esc_html($settings['prefix'] . number_format($remaining)); ?></span>
                </div>
            </div>

            <div class="cp-progress-bar">
                <div class="cp-progress-fill" style="width: <?php echo esc_attr(min($percentage, 100)); ?>%;">
                    <span class="cp-progress-text"><?php echo esc_html(round($percentage, 1)); ?>%</span>
                </div>
            </div>
        </div>
        <?php
    }
}

// =============================================================================
// WIDGET 3: Issue Card Widget
// =============================================================================

/**
 * Issue Card Widget
 */
class CP_Elementor_Issue_Card extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_issue_card';
    }

    public function get_title() {
        return __('Issue Card', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-info-box';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'icon',
            array(
                'label'   => __('Icon', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::ICONS,
                'default' => array(
                    'value'   => 'fas fa-graduation-cap',
                    'library' => 'fa-solid',
                ),
            )
        );

        $this->add_control(
            'title',
            array(
                'label'   => __('Title', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Education', 'campaignpress'),
            )
        );

        $this->add_control(
            'description',
            array(
                'label'   => __('Description', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Investing in our schools and ensuring every child has access to quality education.', 'campaignpress'),
            )
        );

        $this->add_control(
            'link_url',
            array(
                'label'       => __('Link URL', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'campaignpress'),
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'icon_color',
            array(
                'label'     => __('Icon Color', 'campaignpress'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#0073aa',
                'selectors' => array(
                    '{{WRAPPER}} .cp-issue-icon' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'title_typography',
                'selector' => '{{WRAPPER}} .cp-issue-title',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        ?>
        <div class="cp-issue-card">
            <?php if (!empty($settings['icon']['value'])) : ?>
                <div class="cp-issue-icon">
                    <?php \Elementor\Icons_Manager::render_icon($settings['icon'], array('aria-hidden' => 'true')); ?>
                </div>
            <?php endif; ?>

            <h3 class="cp-issue-title"><?php echo esc_html($settings['title']); ?></h3>
            <p class="cp-issue-description"><?php echo esc_html($settings['description']); ?></p>

            <?php if (!empty($settings['link_url']['url'])) : ?>
                <a href="<?php echo esc_url($settings['link_url']['url']); ?>"
                   class="cp-issue-link"
                   <?php echo !empty($settings['link_url']['is_external']) ? 'target="_blank"' : ''; ?>
                   <?php echo !empty($settings['link_url']['nofollow']) ? 'rel="nofollow"' : ''; ?>>
                    <?php esc_html_e('Learn More', 'campaignpress'); ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}

// =============================================================================
// WIDGET 4: Endorsement Grid Widget
// =============================================================================

/**
 * Endorsement Grid Widget
 */
class CP_Elementor_Endorsement_Grid extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_endorsement_grid';
    }

    public function get_title() {
        return __('Endorsement Grid', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'posts_per_page',
            array(
                'label'   => __('Number of Endorsements', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
            )
        );

        $this->add_control(
            'columns',
            array(
                'label'   => __('Columns', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => array(
                    '2' => __('2 Columns', 'campaignpress'),
                    '3' => __('3 Columns', 'campaignpress'),
                    '4' => __('4 Columns', 'campaignpress'),
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $query = new WP_Query(array(
            'post_type'      => 'cp_endorsement',
            'posts_per_page' => $settings['posts_per_page'],
            'post_status'    => 'publish',
        ));

        if ($query->have_posts()) :
            ?>
            <div class="cp-endorsement-grid cp-columns-<?php echo esc_attr($settings['columns']); ?>">
                <?php while ($query->have_posts()) : $query->the_post(); ?>
                    <div class="cp-endorsement-item">
                        <?php if (has_post_thumbnail()) : ?>
                            <div class="cp-endorsement-image">
                                <?php the_post_thumbnail('campaignpress-endorsement'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="cp-endorsement-content">
                            <h4 class="cp-endorsement-name"><?php the_title(); ?></h4>
                            <?php
                            $organization = get_post_meta(get_the_ID(), 'organization', true);
                            if ($organization) :
                                ?>
                                <p class="cp-endorsement-org"><?php echo esc_html($organization); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php
            wp_reset_postdata();
        else :
            echo '<p>' . esc_html__('No endorsements found.', 'campaignpress') . '</p>';
        endif;
    }
}

// =============================================================================
// WIDGET 5: Event Countdown Widget
// =============================================================================

/**
 * Event Countdown Widget
 */
class CP_Elementor_Event_Countdown extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_event_countdown';
    }

    public function get_title() {
        return __('Event Countdown', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-countdown';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'event_date',
            array(
                'label'       => __('Event Date', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::DATE_TIME,
                'default'     => date('Y-m-d H:i', strtotime('+30 days')),
                'description' => __('Select the event date and time', 'campaignpress'),
            )
        );

        $this->add_control(
            'event_title',
            array(
                'label'   => __('Event Title', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Election Day', 'campaignpress'),
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'number_color',
            array(
                'label'     => __('Number Color', 'campaignpress'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#0073aa',
                'selectors' => array(
                    '{{WRAPPER}} .cp-countdown-number' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $event_timestamp = strtotime($settings['event_date']);
        $current_timestamp = current_time('timestamp');
        $diff = $event_timestamp - $current_timestamp;

        $days = floor($diff / DAY_IN_SECONDS);
        $hours = floor(($diff % DAY_IN_SECONDS) / HOUR_IN_SECONDS);
        $minutes = floor(($diff % HOUR_IN_SECONDS) / MINUTE_IN_SECONDS);
        $seconds = floor($diff % MINUTE_IN_SECONDS);

        ?>
        <div class="cp-event-countdown">
            <?php if (!empty($settings['event_title'])) : ?>
                <h3 class="cp-countdown-title"><?php echo esc_html($settings['event_title']); ?></h3>
            <?php endif; ?>

            <div class="cp-countdown-timer">
                <div class="cp-countdown-item">
                    <span class="cp-countdown-number"><?php echo esc_html($days); ?></span>
                    <span class="cp-countdown-label"><?php esc_html_e('Days', 'campaignpress'); ?></span>
                </div>
                <div class="cp-countdown-item">
                    <span class="cp-countdown-number"><?php echo esc_html($hours); ?></span>
                    <span class="cp-countdown-label"><?php esc_html_e('Hours', 'campaignpress'); ?></span>
                </div>
                <div class="cp-countdown-item">
                    <span class="cp-countdown-number"><?php echo esc_html($minutes); ?></span>
                    <span class="cp-countdown-label"><?php esc_html_e('Minutes', 'campaignpress'); ?></span>
                </div>
                <div class="cp-countdown-item">
                    <span class="cp-countdown-number"><?php echo esc_html($seconds); ?></span>
                    <span class="cp-countdown-label"><?php esc_html_e('Seconds', 'campaignpress'); ?></span>
                </div>
            </div>
        </div>
        <?php
    }
}

// =============================================================================
// WIDGET 6: Volunteer Signup CTA Widget
// =============================================================================

/**
 * Volunteer Signup CTA Widget
 */
class CP_Elementor_Volunteer_CTA extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_volunteer_cta';
    }

    public function get_title() {
        return __('Volunteer CTA', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-call-to-action';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'heading',
            array(
                'label'   => __('Heading', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Join Our Team', 'campaignpress'),
            )
        );

        $this->add_control(
            'description',
            array(
                'label'   => __('Description', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Make a difference in your community. Sign up to volunteer today!', 'campaignpress'),
            )
        );

        $this->add_control(
            'button_text',
            array(
                'label'   => __('Button Text', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Sign Up Now', 'campaignpress'),
            )
        );

        $this->add_control(
            'button_link',
            array(
                'label'       => __('Button Link', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => __('https://your-link.com', 'campaignpress'),
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'bg_color',
            array(
                'label'     => __('Background Color', 'campaignpress'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#f8f9fa',
                'selectors' => array(
                    '{{WRAPPER}} .cp-volunteer-cta' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        ?>
        <div class="cp-volunteer-cta">
            <div class="cp-volunteer-icon">
                <i class="fas fa-users"></i>
            </div>

            <?php if (!empty($settings['heading'])) : ?>
                <h3 class="cp-volunteer-heading"><?php echo esc_html($settings['heading']); ?></h3>
            <?php endif; ?>

            <?php if (!empty($settings['description'])) : ?>
                <p class="cp-volunteer-description"><?php echo esc_html($settings['description']); ?></p>
            <?php endif; ?>

            <?php if (!empty($settings['button_link']['url'])) : ?>
                <a href="<?php echo esc_url($settings['button_link']['url']); ?>"
                   class="cp-volunteer-button"
                   <?php echo !empty($settings['button_link']['is_external']) ? 'target="_blank"' : ''; ?>
                   <?php echo !empty($settings['button_link']['nofollow']) ? 'rel="nofollow"' : ''; ?>>
                    <?php echo esc_html($settings['button_text']); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}

// =============================================================================
// WIDGET 7: Social Follow Buttons Widget
// =============================================================================

/**
 * Social Follow Buttons Widget
 */
class CP_Elementor_Social_Follow extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_social_follow';
    }

    public function get_title() {
        return __('Social Follow Buttons', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-social-icons';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Social Networks', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'facebook_url',
            array(
                'label'       => __('Facebook URL', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://facebook.com/...',
            )
        );

        $this->add_control(
            'twitter_url',
            array(
                'label'       => __('Twitter/X URL', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://twitter.com/...',
            )
        );

        $this->add_control(
            'instagram_url',
            array(
                'label'       => __('Instagram URL', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://instagram.com/...',
            )
        );

        $this->add_control(
            'youtube_url',
            array(
                'label'       => __('YouTube URL', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://youtube.com/...',
            )
        );

        $this->add_control(
            'tiktok_url',
            array(
                'label'       => __('TikTok URL', 'campaignpress'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://tiktok.com/...',
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'button_size',
            array(
                'label'      => __('Button Size', 'campaignpress'),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range'      => array(
                    'px' => array(
                        'min' => 30,
                        'max' => 100,
                    ),
                ),
                'default'    => array(
                    'unit' => 'px',
                    'size' => 50,
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .cp-social-button' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; font-size: calc({{SIZE}}{{UNIT}} / 2);',
                ),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $networks = array(
            'facebook'  => array('url' => $settings['facebook_url'], 'icon' => 'fab fa-facebook-f', 'color' => '#1877f2'),
            'twitter'   => array('url' => $settings['twitter_url'], 'icon' => 'fab fa-x-twitter', 'color' => '#000000'),
            'instagram' => array('url' => $settings['instagram_url'], 'icon' => 'fab fa-instagram', 'color' => '#e4405f'),
            'youtube'   => array('url' => $settings['youtube_url'], 'icon' => 'fab fa-youtube', 'color' => '#ff0000'),
            'tiktok'    => array('url' => $settings['tiktok_url'], 'icon' => 'fab fa-tiktok', 'color' => '#000000'),
        );

        ?>
        <div class="cp-social-follow">
            <?php foreach ($networks as $network => $data) : ?>
                <?php if (!empty($data['url'])) : ?>
                    <a href="<?php echo esc_url($data['url']); ?>"
                       class="cp-social-button cp-social-<?php echo esc_attr($network); ?>"
                       style="background-color: <?php echo esc_attr($data['color']); ?>;"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="<?php echo esc_attr(ucfirst($network)); ?>">
                        <i class="<?php echo esc_attr($data['icon']); ?>"></i>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
    }
}

// =============================================================================
// WIDGET 8: Team Member Card Widget
// =============================================================================

/**
 * Team Member Card Widget
 */
class CP_Elementor_Team_Member extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_team_member';
    }

    public function get_title() {
        return __('Team Member Card', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-person';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'image',
            array(
                'label'   => __('Image', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => array(
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ),
            )
        );

        $this->add_control(
            'name',
            array(
                'label'   => __('Name', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Jane Doe', 'campaignpress'),
            )
        );

        $this->add_control(
            'position',
            array(
                'label'   => __('Position', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Campaign Manager', 'campaignpress'),
            )
        );

        $this->add_control(
            'bio',
            array(
                'label'   => __('Bio', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Brief bio about the team member.', 'campaignpress'),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        ?>
        <div class="cp-team-member">
            <?php if (!empty($settings['image']['url'])) : ?>
                <div class="cp-team-image">
                    <img src="<?php echo esc_url($settings['image']['url']); ?>"
                         alt="<?php echo esc_attr($settings['name']); ?>">
                </div>
            <?php endif; ?>

            <div class="cp-team-content">
                <h4 class="cp-team-name"><?php echo esc_html($settings['name']); ?></h4>
                <p class="cp-team-position"><?php echo esc_html($settings['position']); ?></p>
                <?php if (!empty($settings['bio'])) : ?>
                    <p class="cp-team-bio"><?php echo esc_html($settings['bio']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}

// =============================================================================
// WIDGET 9: Event RSVP Form Widget
// =============================================================================

/**
 * Event RSVP Form Widget
 */
class CP_Elementor_Event_RSVP extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_event_rsvp';
    }

    public function get_title() {
        return __('Event RSVP Form', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'form_title',
            array(
                'label'   => __('Form Title', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('RSVP for this Event', 'campaignpress'),
            )
        );

        $this->add_control(
            'submit_text',
            array(
                'label'   => __('Submit Button Text', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Submit RSVP', 'campaignpress'),
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        ?>
        <div class="cp-event-rsvp-form">
            <?php if (!empty($settings['form_title'])) : ?>
                <h3 class="cp-form-title"><?php echo esc_html($settings['form_title']); ?></h3>
            <?php endif; ?>

            <form class="cp-rsvp-form" method="post" action="">
                <?php wp_nonce_field('cp_rsvp_form', 'cp_rsvp_nonce'); ?>

                <div class="cp-form-field">
                    <label for="cp-rsvp-name"><?php esc_html_e('Name', 'campaignpress'); ?> *</label>
                    <input type="text" id="cp-rsvp-name" name="rsvp_name" required>
                </div>

                <div class="cp-form-field">
                    <label for="cp-rsvp-email"><?php esc_html_e('Email', 'campaignpress'); ?> *</label>
                    <input type="email" id="cp-rsvp-email" name="rsvp_email" required>
                </div>

                <div class="cp-form-field">
                    <label for="cp-rsvp-phone"><?php esc_html_e('Phone', 'campaignpress'); ?></label>
                    <input type="tel" id="cp-rsvp-phone" name="rsvp_phone">
                </div>

                <div class="cp-form-field">
                    <label for="cp-rsvp-guests"><?php esc_html_e('Number of Guests', 'campaignpress'); ?></label>
                    <input type="number" id="cp-rsvp-guests" name="rsvp_guests" min="1" max="10" value="1">
                </div>

                <button type="submit" class="cp-rsvp-submit">
                    <?php echo esc_html($settings['submit_text']); ?>
                </button>
            </form>
        </div>
        <?php
    }
}

// =============================================================================
// WIDGET 10: Testimonial/Quote Widget
// =============================================================================

/**
 * Testimonial/Quote Widget
 */
class CP_Elementor_Testimonial extends CP_Elementor_Widget_Base {

    public function get_name() {
        return 'cp_testimonial';
    }

    public function get_title() {
        return __('Testimonial/Quote', 'campaignpress');
    }

    public function get_icon() {
        return 'eicon-testimonial';
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'quote',
            array(
                'label'   => __('Quote', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('This candidate truly cares about our community and will fight for what matters most.', 'campaignpress'),
            )
        );

        $this->add_control(
            'author_name',
            array(
                'label'   => __('Author Name', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('John Smith', 'campaignpress'),
            )
        );

        $this->add_control(
            'author_title',
            array(
                'label'   => __('Author Title/Location', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __('Small Business Owner, Springfield', 'campaignpress'),
            )
        );

        $this->add_control(
            'author_image',
            array(
                'label'   => __('Author Image', 'campaignpress'),
                'type'    => \Elementor\Controls_Manager::MEDIA,
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'campaignpress'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'quote_color',
            array(
                'label'     => __('Quote Color', 'campaignpress'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#333333',
                'selectors' => array(
                    '{{WRAPPER}} .cp-testimonial-quote' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'quote_typography',
                'selector' => '{{WRAPPER}} .cp-testimonial-quote',
            )
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        ?>
        <div class="cp-testimonial">
            <div class="cp-testimonial-quote-icon">
                <i class="fas fa-quote-left"></i>
            </div>

            <blockquote class="cp-testimonial-quote">
                <?php echo esc_html($settings['quote']); ?>
            </blockquote>

            <div class="cp-testimonial-author">
                <?php if (!empty($settings['author_image']['url'])) : ?>
                    <div class="cp-testimonial-author-image">
                        <img src="<?php echo esc_url($settings['author_image']['url']); ?>"
                             alt="<?php echo esc_attr($settings['author_name']); ?>">
                    </div>
                <?php endif; ?>

                <div class="cp-testimonial-author-info">
                    <div class="cp-testimonial-author-name"><?php echo esc_html($settings['author_name']); ?></div>
                    <?php if (!empty($settings['author_title'])) : ?>
                        <div class="cp-testimonial-author-title"><?php echo esc_html($settings['author_title']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
