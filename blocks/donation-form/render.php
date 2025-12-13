<?php
/**
 * Render for Donation Form Block
 */
$attributes = $attributes ?? [];
$tiers = $attributes['tiers'] ?? [
    ['amount' => 25, 'label' => 'Supporter'],
    ['amount' => 50, 'label' => 'Friend'],
    ['amount' => 100, 'label' => 'Backer']
];
$currency = $attributes['currency'] ?? 'USD';
$enable_crypto = $attributes['enableCrypto'] ?? false;
$btc_address = $attributes['btcAddress'] ?? '';

$wrapper_attributes = get_block_wrapper_attributes(array(
    'class' => 'cp-donation-form'
));
?>
<div <?php echo $wrapper_attributes; ?>>
    <h3 class="cp-donation-heading"><?php esc_html_e('Support Our Campaign', 'campaign-office'); ?></h3>
    
    <div class="cp-donation-tiers">
        <?php foreach ($tiers as $tier): ?>
            <button class="cp-donation-tier-btn" data-amount="<?php echo esc_attr($tier['amount']); ?>">
                <span class="cp-tier-amount">$<?php echo esc_html($tier['amount']); ?></span>
                <?php if (!empty($tier['label'])): ?>
                    <span class="cp-tier-label"><?php echo esc_html($tier['label']); ?></span>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
        <button class="cp-donation-tier-btn cp-custom-amount-btn"><?php esc_html_e('Other', 'campaign-office'); ?></button>
    </div>

    <div class="cp-custom-amount-input" style="display:none;">
        <span class="cp-currency-symbol">$</span>
        <input type="number" placeholder="Enter amount" min="1">
    </div>

    <div class="cp-donation-actions">
        <button class="cp-donate-submit-btn"><?php esc_html_e('Donate via Secure Card', 'campaign-office'); ?></button>
        
        <?php if ($enable_crypto && !empty($btc_address)): ?>
            <div class="cp-crypto-toggle">
                <button class="cp-crypto-btn" data-crypto="btc">
                    <span class="dashicons dashicons-bitcoin"></span> <?php esc_html_e('Donate Crypto', 'campaign-office'); ?>
                </button>
            </div>
            <div class="cp-crypto-address" style="display:none;">
                <p><strong>BTC:</strong> <code class="cp-btc-addy"><?php echo esc_html($btc_address); ?></code></p>
                <button class="cp-copy-btn"><?php esc_html_e('Copy', 'campaign-office'); ?></button>
            </div>
        <?php endif; ?>
    </div>
</div>
