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
        'name' => __('Free', 'campaignpress'),
        'price' => __('$0', 'campaignpress'),
        'period' => '',
        'description' => __('Perfect for small local campaigns', 'campaignpress'),
        'features' => array(
            __('Responsive Campaign Theme', 'campaignpress'),
            __('Basic Donation Forms', 'campaignpress'),
            __('Event Management', 'campaignpress'),
            __('Volunteer Sign-up Forms', 'campaignpress'),
            __('Social Media Integration', 'campaignpress'),
            __('Email Newsletter Integration', 'campaignpress'),
            __('Mobile Responsive Design', 'campaignpress'),
            __('SEO Optimized', 'campaignpress'),
            __('Translation Ready', 'campaignpress'),
            __('Community Support', 'campaignpress'),
        ),
        'button' => __('Current Plan', 'campaignpress'),
        'button_url' => '#',
        'highlight' => false,
    ),
    'professional' => array(
        'name' => __('Professional', 'campaignpress'),
        'price' => __('$299', 'campaignpress'),
        'period' => __('per year', 'campaignpress'),
        'description' => __('Complete solution for serious campaigns', 'campaignpress'),
        'features' => array(
            __('Everything in Free, plus:', 'campaignpress'),
            __('Advanced CRM System', 'campaignpress'),
            __('Field Operations Management', 'campaignpress'),
            __('Canvassing & Phone Banking Tools', 'campaignpress'),
            __('Advanced Analytics Dashboard', 'campaignpress'),
            __('FEC Compliance Tools', 'campaignpress'),
            __('REST API Access', 'campaignpress'),
            __('Donor Management System', 'campaignpress'),
            __('Volunteer Management Portal', 'campaignpress'),
            __('White Label Options', 'campaignpress'),
            __('Priority Phone & Chat Support', 'campaignpress'),
            __('Automatic Theme Updates', 'campaignpress'),
            __('Developer Console', 'campaignpress'),
            __('Up to 5 Site Licenses', 'campaignpress'),
        ),
        'button' => __('Upgrade to Professional', 'campaignpress'),
        'button_url' => 'https://campaignpress.com/pricing/?plan=professional',
        'highlight' => true,
    ),
);

// Feature comparison
$feature_comparison = array(
    array(
        'category' => __('Core Features', 'campaignpress'),
        'features' => array(
            array('name' => __('Responsive Campaign Theme', 'campaignpress'), 'free' => true, 'professional' => true),
            array('name' => __('Donation Forms', 'campaignpress'), 'free' => true, 'professional' => true),
            array('name' => __('Event Management', 'campaignpress'), 'free' => true, 'professional' => true),
            array('name' => __('Volunteer Sign-ups', 'campaignpress'), 'free' => true, 'professional' => true),
            array('name' => __('Social Media Integration', 'campaignpress'), 'free' => true, 'professional' => true),
        ),
    ),
    array(
        'category' => __('Support & Updates', 'campaignpress'),
        'features' => array(
            array('name' => __('Community Support', 'campaignpress'), 'free' => true, 'professional' => false),
            array('name' => __('Priority Support', 'campaignpress'), 'free' => false, 'professional' => true),
            array('name' => __('Automatic Updates', 'campaignpress'), 'free' => false, 'professional' => true),
        ),
    ),
    array(
        'category' => __('Advanced Features', 'campaignpress'),
        'features' => array(
            array('name' => __('CRM System', 'campaignpress'), 'free' => false, 'professional' => true),
            array('name' => __('Field Operations', 'campaignpress'), 'free' => false, 'professional' => true),
            array('name' => __('Advanced Analytics', 'campaignpress'), 'free' => false, 'professional' => true),
            array('name' => __('FEC Compliance Tools', 'campaignpress'), 'free' => false, 'professional' => true),
            array('name' => __('REST API Access', 'campaignpress'), 'free' => false, 'professional' => true),
            array('name' => __('White Label', 'campaignpress'), 'free' => false, 'professional' => true),
            array('name' => __('Developer Console', 'campaignpress'), 'free' => false, 'professional' => true),
        ),
    ),
    array(
        'category' => __('Licenses & Sites', 'campaignpress'),
        'features' => array(
            array('name' => __('Number of Sites', 'campaignpress'), 'free' => '∞', 'professional' => '5'),
        ),
    ),
);
?>

<div class="wrap campaignpress-upgrade-page">
    <h1><?php _e('Upgrade CampaignPress', 'campaignpress'); ?></h1>
    <p class="description">
        <?php _e('Choose the plan that best fits your campaign needs.', 'campaignpress'); ?>
    </p>

    <?php if ($license_data): ?>
        <div class="cp-current-license-banner">
            <span class="dashicons dashicons-info"></span>
            <div>
                <strong><?php _e('Current License:', 'campaignpress'); ?></strong>
                <?php echo esc_html(ucfirst($current_license)); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=campaignpress-premium')); ?>" class="button button-small">
                    <?php _e('Manage License', 'campaignpress'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Pricing Cards -->
    <div class="cp-pricing-section">
        <h2><?php _e('Choose Your Plan', 'campaignpress'); ?></h2>
        <div class="cp-pricing-grid">
            <?php foreach ($plans as $plan_key => $plan): ?>
                <div class="cp-pricing-card <?php echo $plan['highlight'] ? 'cp-pricing-highlight' : ''; ?> <?php echo $plan_key === $current_license ? 'cp-pricing-current' : ''; ?>">
                    <?php if ($plan['highlight']): ?>
                        <div class="cp-pricing-badge"><?php _e('Most Popular', 'campaignpress'); ?></div>
                    <?php endif; ?>

                    <?php if ($plan_key === $current_license): ?>
                        <div class="cp-pricing-badge cp-current-badge"><?php _e('Current Plan', 'campaignpress'); ?></div>
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
                                <?php _e('Current Plan', 'campaignpress'); ?>
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
        <h2><?php _e('Detailed Feature Comparison', 'campaignpress'); ?></h2>
        <div class="cp-comparison-table-wrapper">
            <table class="cp-comparison-table">
                <thead>
                    <tr>
                        <th><?php _e('Features', 'campaignpress'); ?></th>
                        <th><?php _e('Free', 'campaignpress'); ?></th>
                        <th class="cp-highlight-col"><?php _e('Professional', 'campaignpress'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($feature_comparison as $category): ?>
                        <tr class="cp-category-row">
                            <td colspan="3"><strong><?php echo esc_html($category['category']); ?></strong></td>
                        </tr>
                        <?php foreach ($category['features'] as $feature): ?>
                            <tr>
                                <td><?php echo esc_html($feature['name']); ?></td>
                                <td><?php echo cp_render_feature_check($feature['free']); ?></td>
                                <td class="cp-highlight-col"><?php echo cp_render_feature_check($feature['professional']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="cp-faq-section">
        <h2><?php _e('Frequently Asked Questions', 'campaignpress'); ?></h2>
        <div class="cp-faq-grid">
            <div class="cp-faq-item">
                <h3><?php _e('Can I upgrade or downgrade my plan?', 'campaignpress'); ?></h3>
                <p><?php _e('Yes! You can upgrade or downgrade your plan at any time. When you upgrade, you\'ll only pay the prorated difference. Downgrades take effect at the end of your current billing period.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('What payment methods do you accept?', 'campaignpress'); ?></h3>
                <p><?php _e('We accept all major credit cards (Visa, MasterCard, American Express, Discover) and PayPal. Professional plan customers can also pay via invoice.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('Is there a money-back guarantee?', 'campaignpress'); ?></h3>
                <p><?php _e('Absolutely! We offer a 30-day money-back guarantee. If you\'re not satisfied with CampaignPress Premium, contact us within 30 days for a full refund.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('Can I use my license on multiple sites?', 'campaignpress'); ?></h3>
                <p><?php _e('It depends on your plan. Free is for unlimited personal projects, Professional includes 5 site licenses. Each site requires license activation.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('What happens when my license expires?', 'campaignpress'); ?></h3>
                <p><?php _e('You have a 7-day grace period after expiration. Premium features will continue to work, but you won\'t receive updates. After the grace period, premium features will be disabled.', 'campaignpress'); ?></p>
            </div>

            <div class="cp-faq-item">
                <h3><?php _e('Do you offer discounts for nonprofits?', 'campaignpress'); ?></h3>
                <p><?php _e('Yes! We offer special pricing for registered 501(c)(3) organizations and political campaigns. Contact our sales team for more information.', 'campaignpress'); ?></p>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cp-cta-section">
        <h2><?php _e('Ready to Upgrade Your Campaign?', 'campaignpress'); ?></h2>
        <p><?php _e('Join thousands of campaigns using CampaignPress Premium to win elections.', 'campaignpress'); ?></p>
        <div class="cp-cta-buttons">
            <a href="https://campaignpress.com/pricing/" class="button button-primary button-hero" target="_blank">
                <?php _e('View All Plans', 'campaignpress'); ?>
            </a>
            <a href="https://campaignpress.com/contact/" class="button button-secondary button-hero" target="_blank">
                <?php _e('Contact Sales', 'campaignpress'); ?>
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
