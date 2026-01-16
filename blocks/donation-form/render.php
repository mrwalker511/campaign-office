<?php
/**
 * Donation Form Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$heading            = $attributes['heading'] ?? __( 'Support Our Campaign', 'campaign-office' );
$description        = $attributes['description'] ?? __( 'Your contribution helps us build a better future.', 'campaign-office' );
$tiers              = $attributes['tiers'] ?? array();
$allow_custom       = $attributes['allowCustomAmount'] ?? true;
$min_custom         = absint( $attributes['minCustomAmount'] ?? 5 );
$max_custom         = absint( $attributes['maxCustomAmount'] ?? 5000 );
$currency_symbol    = $attributes['currencySymbol'] ?? '$';
$allow_recurring    = $attributes['allowRecurring'] ?? true;
$payment_processor  = $attributes['paymentProcessor'] ?? 'actblue';
$actblue_url        = $attributes['actblueUrl'] ?? '';
$enable_crypto      = $attributes['enableCrypto'] ?? false;
$btc_address        = $attributes['btcAddress'] ?? '';
$show_goal          = $attributes['showGoal'] ?? true;
$goal_amount        = absint( $attributes['goalAmount'] ?? 10000 );
$current_amount     = absint( $attributes['currentAmount'] ?? 0 );
$primary_color      = $attributes['primaryColor'] ?? '#0053c3';
$background_color   = $attributes['backgroundColor'] ?? '#ffffff';
$button_text        = $attributes['buttonText'] ?? __( 'Donate Now', 'campaign-office' );
$show_disclaimer    = $attributes['showDisclaimer'] ?? true;
$disclaimer_text    = $attributes['disclaimerText'] ?? __( 'Contributions are not tax deductible. By donating, you certify that you are a U.S. citizen or permanent resident.', 'campaign-office' );

// Calculate goal percentage
$goal_percentage = $goal_amount > 0 ? min( 100, ( $current_amount / $goal_amount ) * 100 ) : 0;

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-donation-form',
    'style' => 'background-color: ' . esc_attr( $background_color ) . ';',
) );
?>

<div <?php echo wp_kses_data( $wrapper_attributes ); ?>>
    <?php if ( $heading ) : ?>
        <h2 class="cp-donation-heading"><?php echo esc_html( $heading ); ?></h2>
    <?php endif; ?>

    <?php if ( $description ) : ?>
        <p class="cp-donation-description"><?php echo esc_html( $description ); ?></p>
    <?php endif; ?>

    <?php if ( $show_goal ) : ?>
        <div class="cp-donation-goal">
            <div class="cp-goal-stats">
                <span class="cp-goal-raised" style="color: <?php echo esc_attr( $primary_color ); ?>;">
                    <?php echo esc_html( $currency_symbol . number_format( $current_amount ) ); ?>
                </span>
                <span class="cp-goal-target">
                    <?php
                    printf(
                        /* translators: %s: goal amount */
                        esc_html__( 'Goal: %s', 'campaign-office' ),
                        esc_html( $currency_symbol . number_format( $goal_amount ) )
                    );
                    ?>
                </span>
            </div>
            <div class="cp-goal-bar">
                <div class="cp-goal-progress" style="width: <?php echo esc_attr( $goal_percentage ); ?>%; background-color: <?php echo esc_attr( $primary_color ); ?>;"></div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $tiers ) ) : ?>
        <div class="cp-donation-tiers">
            <?php foreach ( $tiers as $tier ) :
                $is_featured = ! empty( $tier['featured'] );
                $tier_class = $is_featured ? 'cp-tier cp-tier-featured' : 'cp-tier';
                $tier_style = $is_featured ? 'border-color: ' . esc_attr( $primary_color ) . ';' : '';
            ?>
                <div class="<?php echo esc_attr( $tier_class ); ?>" style="<?php echo esc_attr( $tier_style ); ?>">
                    <div class="cp-tier-amount" style="color: <?php echo esc_attr( $primary_color ); ?>;">
                        <?php echo esc_html( $currency_symbol . number_format( $tier['amount'] ?? 0 ) ); ?>
                    </div>
                    <div class="cp-tier-label"><?php echo esc_html( $tier['label'] ?? '' ); ?></div>
                    <?php if ( ! empty( $tier['description'] ) ) : ?>
                        <div class="cp-tier-description"><?php echo esc_html( $tier['description'] ); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ( $allow_custom ) : ?>
        <div class="cp-custom-amount">
            <label for="cp-custom-amount-input"><?php esc_html_e( 'Or enter custom amount:', 'campaign-office' ); ?></label>
            <div class="cp-custom-amount-input-wrapper">
                <span class="cp-currency-symbol"><?php echo esc_html( $currency_symbol ); ?></span>
                <input
                    type="number"
                    id="cp-custom-amount-input"
                    min="<?php echo esc_attr( $min_custom ); ?>"
                    max="<?php echo esc_attr( $max_custom ); ?>"
                    placeholder="<?php echo esc_attr( $min_custom ); ?>"
                    class="cp-amount-input"
                >
            </div>
        </div>
    <?php endif; ?>

    <?php if ( $allow_recurring ) : ?>
        <div class="cp-recurring-option">
            <label class="cp-recurring-label">
                <input type="checkbox" id="cp-recurring-checkbox" class="cp-recurring-checkbox">
                <span><?php esc_html_e( 'Make this a monthly donation', 'campaign-office' ); ?></span>
            </label>
        </div>
    <?php endif; ?>

    <?php if ( 'actblue' === $payment_processor && ! empty( $actblue_url ) ) : ?>
        <a href="<?php echo esc_url( $actblue_url ); ?>" class="cp-donate-button" style="background-color: <?php echo esc_attr( $primary_color ); ?>;" target="_blank" rel="noopener">
            <?php echo esc_html( $button_text ); ?>
        </a>
    <?php else : ?>
        <button type="button" class="cp-donate-button" style="background-color: <?php echo esc_attr( $primary_color ); ?>;" data-processor="<?php echo esc_attr( $payment_processor ); ?>">
            <?php echo esc_html( $button_text ); ?>
        </button>
    <?php endif; ?>

    <?php if ( $enable_crypto && ! empty( $btc_address ) ) : ?>
        <div class="cp-crypto-option">
            <p class="cp-crypto-label"><?php esc_html_e( 'Or donate with cryptocurrency:', 'campaign-office' ); ?></p>
            <code class="cp-btc-address"><?php echo esc_html( $btc_address ); ?></code>
        </div>
    <?php endif; ?>

    <?php if ( $show_disclaimer ) : ?>
        <p class="cp-donation-disclaimer"><?php echo esc_html( $disclaimer_text ); ?></p>
    <?php endif; ?>
</div>
