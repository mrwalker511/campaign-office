<?php
/**
 * Upgrade Page
 *
 * Admin page showing upgrade options and free vs pro comparison
 *
 * @package CampaignPress
 * @subpackage Premium
 * @since 2.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get premium instance
$premium = CampaignPress_Premium::get_instance();
$license_data = $premium->get_license_data();
$current_license = $license_data ? $license_data['license_type'] : 'free';

// Pricing plans
$plans = array(
    'free' => array(
        'name' => __('Free', 'campaign-office'),
        'price' => __('$0', 'campaign-office'),
        'period' => '',
        'description' => __('Perfect for small local campaigns', 'campaign-office'),
        'features' => array(
            __('Responsive Campaign Theme', 'campaign-office'),
            __('Basic Donation Forms', 'campaign-office'),
            __('Event Management', 'campaign-office'),
            __('Volunteer Sign-up Forms', 'campaign-office'),
            __('Social Media Integration', 'campaign-office'),
            __('Email Newsletter Integration', 'campaign-office'),
            __('Mobile Responsive Design', 'campaign-office'),
            __('SEO Optimized', 'campaign-office'),
            __('Translation Ready', 'campaign-office'),
            __('Community Support', 'campaign-office'),
        ),
        'button' => __('Current Plan', 'campaign-office'),
        'button_url' => '#',
        'highlight' => false,
    ),
    'basic' => array(
        'name' => __('Basic', 'campaign-office'),
        'price' => __('$99', 'campaign-office'),
        'period' => __('per year', 'campaign-office'),
        'description' => __('Essential tools for growing campaigns', 'campaign-office'),
        'features' => array(
            __('Everything in Free, plus:', 'campaign-office'),
            __('Priority Email Support', 'campaign-office'),
            __('Automatic Theme Updates', 'campaign-office'),
            __('Remove CampaignPress Branding', 'campaign-office'),
            __('Advanced Customization Options', 'campaign-office'),
            __('Google Analytics Integration', 'campaign-office'),
            __('Enhanced Security Features', 'campaign-office'),
            __('1 Site License', 'campaign-office'),
        ),
        'button' => __('Upgrade to Basic', 'campaign-office'),
        'button_url' => 'https://campaignpress.com/pricing/?plan=basic',
        'highlight' => false,
    ),
    'professional' => array(
        'name' => __('Professional', 'campaign-office'),
        'price' => __('$299', 'campaign-office'),
        'period' => __('per year', 'campaign-office'),
        'description' => __('Complete solution for serious campaigns', 'campaign-office'),
        'features' => array(
            __('Everything in Basic, plus:', 'campaign-office'),
            __('Advanced CRM System', 'campaign-office'),
            __('Field Operations Management', 'campaign-office'),
            __('Canvassing & Phone Banking Tools', 'campaign-office'),
            __('Advanced Analytics Dashboard', 'campaign-office'),
            __('Donor Management System', 'campaign-office'),
            __('Volunteer Management Portal', 'campaign-office'),
            __('Priority Phone & Chat Support', 'campaign-office'),
            __('Up to 5 Site Licenses', 'campaign-office'),
        ),
        'button' => __('Upgrade to Professional', 'campaign-office'),
        'button_url' => 'https://campaignpress.com/pricing/?plan=professional',
        'highlight' => true,
    ),
    'enterprise' => array(
        'name' => __('Enterprise', 'campaign-office'),
        'price' => __('$799', 'campaign-office'),
        'period' => __('per year', 'campaign-office'),
        'description' => __('Enterprise-grade for large campaigns', 'campaign-office'),
        'features' => array(
            __('Everything in Professional, plus:', 'campaign-office'),
            __('FEC Compliance Tools', 'campaign-office'),
            __('REST API Access', 'campaign-office'),
            __('Multi-site Support', 'campaign-office'),
            __('Custom Integrations', 'campaign-office'),
            __('White Label Options', 'campaign-office'),
            __('Dedicated Account Manager', 'campaign-office'),
            __('24/7 Priority Support', 'campaign-office'),
            __('Unlimited Site Licenses', 'campaign-office'),
            __('Custom Development Available', 'campaign-office'),
        ),
        'button' => __('Upgrade to Enterprise', 'campaign-office'),
        'button_url' => 'https://campaignpress.com/pricing/?plan=enterprise',
        'highlight' => false,
    ),
);

// Feature comparison
$feature_comparison = array(
    array(
        'category' => __('Core Features', 'campaign-office'),
        'features' => array(
            array('name' => __('Responsive Campaign Theme', 'campaign-office'), 'free' => true, 'basic' => true, 'professional' => true, 'enterprise' => true),
            array('name' => __('Donation Forms', 'campaign-office'), 'free' => true, 'basic' => true, 'professional' => true, 'enterprise' => true),
            array('name' => __('Event Management', 'campaign-office'), 'free' => true, 'basic' => true, 'professional' => true, 'enterprise' => true),
            array('name' => __('Volunteer Sign-ups', 'campaign-office'), 'free' => true, 'basic' => true, 'professional' => true, 'enterprise' => true),
            array('name' => __('Social Media Integration', 'campaign-office'), 'free' => true, 'basic' => true, 'professional' => true, 'enterprise' => true),
        ),
    ),
    array(
        'category' => __('Support & Updates', 'campaign-office'),
        'features' => array(
            array('name' => __('Community Support', 'campaign-office'), 'free' => true, 'basic' => false, 'professional' => false, 'enterprise' => false),
            array('name' => __('Email Support', 'campaign-office'), 'free' => false, 'basic' => true, 'professional' => true, 'enterprise' => true),
            array('name' => __('Priority Support', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => true, 'enterprise' => true),
            array('name' => __('24/7 Support', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => false, 'enterprise' => true),
            array('name' => __('Automatic Updates', 'campaign-office'), 'free' => false, 'basic' => true, 'professional' => true, 'enterprise' => true),
        ),
    ),
    array(
        'category' => __('Advanced Features', 'campaign-office'),
        'features' => array(
            array('name' => __('CRM System', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => true, 'enterprise' => true),
            array('name' => __('Field Operations', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => true, 'enterprise' => true),
            array('name' => __('Advanced Analytics', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => true, 'enterprise' => true),
            array('name' => __('FEC Compliance Tools', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => false, 'enterprise' => true),
            array('name' => __('REST API Access', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => false, 'enterprise' => true),
            array('name' => __('White Label', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => false, 'enterprise' => true),
        ),
    ),
    array(
        'category' => __('Licenses & Sites', 'campaign-office'),
        'features' => array(
            array('name' => __('Number of Sites', 'campaign-office'), 'free' => '∞', 'basic' => '1', 'professional' => '5', 'enterprise' => '∞'),
            array('name' => __('Multisite Support', 'campaign-office'), 'free' => false, 'basic' => false, 'professional' => false, 'enterprise' => true),
        ),
    ),
);
?>

<div class="wrap campaignpress-upgrade-page">
    <h1><?php _e('Upgrade CampaignPress', 'campaign-office'); ?></h1>
    <p class="description">
        <?php _e('Choose the plan that best fits your campaign needs.', 'campaign-office'); ?>
    </p>

    <?php if ($license_data): ?>
        <div class="cp-current-license-banner">
            <span class="dashicons dashicons-info"></span>
            <div>
                <strong><?php _e('Current License:', 'campaign-office'); ?></strong>
                <?php echo esc_html(ucfirst($current_license)); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-premium')); ?>" class="button button-small">
                    <?php _e('Manage License', 'campaign-office'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Pricing Cards -->
    <div class="cp-pricing-section">
        <h2><?php _e('Choose Your Plan', 'campaign-office'); ?></h2>
        <div class="cp-pricing-grid">
            <?php foreach ($plans as $plan_key => $plan): ?>
                <div class="cp-pricing-card <?php echo $plan['highlight'] ? 'cp-pricing-highlight' : ''; ?> <?php echo $plan_key === $current_license ? 'cp-pricing-current' : ''; ?>">
                    <?php if ($plan['highlight']): ?>
                        <div class="cp-pricing-badge"><?php _e('Most Popular', 'campaign-office'); ?></div>
                    <?php endif; ?>

                    <?php if ($plan_key === $current_license): ?>
                        <div class="cp-pricing-badge cp-current-badge"><?php _e('Current Plan', 'campaign-office'); ?></div>
                    <?php endif; ?>

                    <h3><?php echo esc_html($plan['name']); ?></h3>
                    <div class="cp-pricing-price">
                        <span class="cp-price"><?php echo esc_html($plan['price']); ?></span>
                        <?php if ($plan['period']): ?>
                            <span class="cp-period"><?php echo esc_html($plan['period']); ?></span>
                        <?php endif; ?>
                    </div>
                    <p class="cp-pricing-description"><?php echo esc_html($plan['description']); ?></p>

                    <ul class="cp-pricing-features">
                        <?php foreach ($plan['features'] as $feature): ?>
                            <li>
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php echo esc_html($feature); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="cp-pricing-action">
                        <?php if ($plan_key === $current_license): ?>
                            <button class="button button-secondary button-large" disabled>
                                <?php _e('Current Plan', 'campaign-office'); ?>
                            </button>
                        <?php elseif ($plan_key === 'free'): ?>
                            <button class="button button-secondary button-large" disabled>
                                <?php echo esc_html($plan['button']); ?>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo esc_url($plan['button_url']); ?>" class="button button-primary button-large" target="_blank">
                                <?php echo esc_html($plan['button']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Feature Comparison Table -->
    <div class="cp-comparison-section">
        <h2><?php _e('Detailed Feature Comparison', 'campaign-office'); ?></h2>
        <div class="cp-comparison-table-wrapper">
            <table class="cp-comparison-table">
                <thead>
                    <tr>
                        <th><?php _e('Features', 'campaign-office'); ?></th>
                        <th><?php _e('Free', 'campaign-office'); ?></th>
                        <th><?php _e('Basic', 'campaign-office'); ?></th>
                        <th class="cp-highlight-col"><?php _e('Professional', 'campaign-office'); ?></th>
                        <th><?php _e('Enterprise', 'campaign-office'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feature_comparison as $category): ?>
                        <tr class="cp-category-row">
                            <td colspan="5"><strong><?php echo esc_html($category['category']); ?></strong></td>
                        </tr>
                        <?php foreach ($category['features'] as $feature): ?>
                            <tr>
                                <td><?php echo esc_html($feature['name']); ?></td>
                                <td><?php echo cp_render_feature_check($feature['free']); ?></td>
                                <td><?php echo cp_render_feature_check($feature['basic']); ?></td>
                                <td class="cp-highlight-col"><?php echo cp_render_feature_check($feature['professional']); ?></td>
                                <td><?php echo cp_render_feature_check($feature['enterprise']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="cp-faq-section">
        <h2><?php _e('Frequently Asked Questions', 'campaign-office'); ?></h2>
        <div class="cp-faq-grid">
            <div class="cp-faq-item">
                <h3><?php _e('Can I upgrade or downgrade my plan?', 'campaign-office'); ?></h3>
                <p><?php _e('Yes! You can upgrade or downgrade your plan at any time. When you upgrade, you\'ll only pay the prorated difference. Downgrades take effect at the end of your current billing period.', 'campaign-office'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('What payment methods do you accept?', 'campaign-office'); ?></h3>
                <p><?php _e('We accept all major credit cards (Visa, MasterCard, American Express, Discover) and PayPal. Enterprise customers can also pay via invoice.', 'campaign-office'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('Is there a money-back guarantee?', 'campaign-office'); ?></h3>
                <p><?php _e('Absolutely! We offer a 30-day money-back guarantee. If you\'re not satisfied with CampaignPress Premium, contact us within 30 days for a full refund.', 'campaign-office'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('Can I use my license on multiple sites?', 'campaign-office'); ?></h3>
                <p><?php _e('It depends on your plan. Basic includes 1 site, Professional includes 5 sites, and Enterprise includes unlimited sites. Each site requires license activation.', 'campaign-office'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('What happens when my license expires?', 'campaign-office'); ?></h3>
                <p><?php _e('You have a 7-day grace period after expiration. Premium features will continue to work, but you won\'t receive updates. After the grace period, premium features will be disabled.', 'campaign-office'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('Do you offer discounts for nonprofits?', 'campaign-office'); ?></h3>
                <p><?php _e('Yes! We offer special pricing for registered 501(c)(3) organizations and political campaigns. Contact our sales team for more information.', 'campaign-office'); ?></p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cp-cta-section">
        <h2><?php _e('Ready to Upgrade Your Campaign?', 'campaign-office'); ?></h2>
        <p><?php _e('Join thousands of campaigns using CampaignPress Premium to win elections.', 'campaign-office'); ?></p>
        <div class="cp-cta-buttons">
            <a href="https://campaignpress.com/pricing/" class="button button-primary button-hero" target="_blank">
                <?php _e('View All Plans', 'campaign-office'); ?>
            </a>
            <a href="https://campaignpress.com/contact/" class="button button-secondary button-hero" target="_blank">
                <?php _e('Contact Sales', 'campaign-office'); ?>
            </a>
        </div>
    </div>
</div>

<?php

?>

<style>
.campaignpress-upgrade-page {
    max-width: 1400px;
}

.cp-current-license-banner {
    background: #f0f6fc;
    border: 1px solid #0969da;
    border-radius: 6px;
    padding: 15px 20px;
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 15px;
}

.cp-current-license-banner .dashicons {
    font-size: 24px;
    color: #0969da;
}

.cp-pricing-section {
    margin: 40px 0;
}

.cp-pricing-section h2 {
    text-align: center;
    font-size: 32px;
    margin-bottom: 40px;
}

.cp-pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.cp-pricing-card {
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 12px;
    padding: 30px;
    position: relative;
    transition: all 0.3s ease;
}

.cp-pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}

.cp-pricing-highlight {
    border-color: #2271b1;
    box-shadow: 0 4px 12px rgba(34,113,177,0.2);
}

.cp-pricing-current {
    border-color: #46b450;
}

.cp-pricing-badge {
    position: absolute;
    top: -12px;
    left: 50%;
    transform: translateX(-50%);
    background: #2271b1;
    color: #fff;
    padding: 6px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.cp-current-badge {
    background: #46b450;
}

.cp-pricing-card h3 {
    font-size: 24px;
    margin: 10px 0 15px 0;
    text-align: center;
}

.cp-pricing-price {
    text-align: center;
    margin-bottom: 15px;
}

.cp-price {
    font-size: 48px;
    font-weight: 700;
    color: #1d2327;
    display: block;
}

.cp-period {
    font-size: 14px;
    color: #666;
    display: block;
}

.cp-pricing-description {
    text-align: center;
    color: #666;
    margin-bottom: 20px;
    min-height: 40px;
}

.cp-pricing-features {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.cp-pricing-features li {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: flex-start;
}

.cp-pricing-features li:last-child {
    border-bottom: none;
}

.cp-pricing-features .dashicons {
    color: #46b450;
    margin-right: 10px;
    flex-shrink: 0;
}

.cp-pricing-action {
    margin-top: 20px;
    text-align: center;
}

.cp-pricing-action .button-large {
    width: 100%;
    padding: 12px 24px;
    height: auto;
    font-size: 16px;
}

.cp-comparison-section {
    margin: 60px 0;
}

.cp-comparison-section h2 {
    text-align: center;
    font-size: 32px;
    margin-bottom: 40px;
}

.cp-comparison-table-wrapper {
    overflow-x: auto;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cp-comparison-table {
    width: 100%;
    border-collapse: collapse;
}

.cp-comparison-table thead th {
    background: #f9f9f9;
    padding: 20px;
    text-align: center;
    font-weight: 600;
    border-bottom: 2px solid #ddd;
}

.cp-comparison-table thead th:first-child {
    text-align: left;
}

.cp-highlight-col {
    background: #f0f6fc !important;
}

.cp-comparison-table tbody td {
    padding: 15px 20px;
    border-bottom: 1px solid #f0f0f0;
    text-align: center;
}

.cp-comparison-table tbody td:first-child {
    text-align: left;
    font-weight: 500;
}

.cp-category-row td {
    background: #f9f9f9;
    font-weight: 600;
    padding: 12px 20px !important;
}

.cp-check-yes {
    color: #46b450;
    font-size: 20px;
}

.cp-check-no {
    color: #ddd;
    font-size: 20px;
}

.cp-check-text {
    font-weight: 600;
    color: #2271b1;
}

.cp-faq-section {
    margin: 60px 0;
    padding: 40px;
    background: #f9f9f9;
    border-radius: 12px;
}

.cp-faq-section h2 {
    text-align: center;
    font-size: 32px;
    margin-bottom: 40px;
}

.cp-faq-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}

.cp-faq-item {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.cp-faq-item h3 {
    margin-top: 0;
    margin-bottom: 12px;
    font-size: 18px;
    color: #1d2327;
}

.cp-faq-item p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.cp-cta-section {
    text-align: center;
    padding: 60px 40px;
    background: linear-gradient(135deg, #2271b1 0%, #1557a0 100%);
    color: #fff;
    border-radius: 12px;
    margin: 60px 0 40px 0;
}

.cp-cta-section h2 {
    font-size: 36px;
    margin-bottom: 15px;
    color: #fff;
}

.cp-cta-section p {
    font-size: 18px;
    margin-bottom: 30px;
    opacity: 0.9;
}

.cp-cta-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.cp-cta-buttons .button-hero {
    padding: 15px 40px;
    height: auto;
    font-size: 18px;
}

@media (max-width: 768px) {
    .cp-pricing-grid {
        grid-template-columns: 1fr;
    }

    .cp-faq-grid {
        grid-template-columns: 1fr;
    }

    .cp-comparison-table {
        font-size: 14px;
    }

    .cp-comparison-table th,
    .cp-comparison-table td {
        padding: 10px !important;
    }
}
</style>
