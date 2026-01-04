<?php
/**
 * Volunteer Matcher Block - Server-side Render
 *
 * @package CampaignPress
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract attributes with defaults
$title            = $attributes['title'] ?? __( 'Volunteer Sign Up', 'campaign-office' );
$description      = $attributes['description'] ?? __( 'Join our campaign and help us make a difference!', 'campaign-office' );
$submit_text      = $attributes['submitText'] ?? __( 'Sign Me Up!', 'campaign-office' );
$show_interests   = $attributes['showInterests'] ?? true;
$show_availability = $attributes['showAvailability'] ?? true;
$show_skills      = $attributes['showSkills'] ?? true;
$bg_color         = $attributes['backgroundColor'] ?? '';
$text_color       = $attributes['textColor'] ?? '';

// Build inline styles
$styles = array();
if ( $bg_color ) {
    $styles[] = 'background-color: ' . esc_attr( $bg_color );
}
if ( $text_color ) {
    $styles[] = 'color: ' . esc_attr( $text_color );
}
$style_attr = ! empty( $styles ) ? 'style="' . implode( '; ', $styles ) . '"' : '';

// Get wrapper attributes
$wrapper_attributes = get_block_wrapper_attributes( array(
    'class' => 'cp-volunteer-matcher',
) );

// Generate unique form ID
$form_id = 'cp-volunteer-form-' . wp_unique_id();
?>

<div <?php echo $wrapper_attributes; ?> <?php echo $style_attr; ?>>
    <?php if ( $title ) : ?>
        <h2 class="cp-volunteer-title"><?php echo esc_html( $title ); ?></h2>
    <?php endif; ?>

    <?php if ( $description ) : ?>
        <p class="cp-volunteer-description"><?php echo esc_html( $description ); ?></p>
    <?php endif; ?>

    <form id="<?php echo esc_attr( $form_id ); ?>" class="cp-volunteer-form" method="post" action="">
        <?php wp_nonce_field( 'cp_volunteer_signup', 'cp_volunteer_nonce' ); ?>

        <div class="cp-form-row">
            <div class="cp-form-field">
                <label for="<?php echo esc_attr( $form_id ); ?>-name"><?php esc_html_e( 'Full Name', 'campaign-office' ); ?> <span class="required">*</span></label>
                <input type="text" id="<?php echo esc_attr( $form_id ); ?>-name" name="volunteer_name" required>
            </div>
        </div>

        <div class="cp-form-row cp-form-row-2col">
            <div class="cp-form-field">
                <label for="<?php echo esc_attr( $form_id ); ?>-email"><?php esc_html_e( 'Email', 'campaign-office' ); ?> <span class="required">*</span></label>
                <input type="email" id="<?php echo esc_attr( $form_id ); ?>-email" name="volunteer_email" required>
            </div>
            <div class="cp-form-field">
                <label for="<?php echo esc_attr( $form_id ); ?>-phone"><?php esc_html_e( 'Phone', 'campaign-office' ); ?></label>
                <input type="tel" id="<?php echo esc_attr( $form_id ); ?>-phone" name="volunteer_phone">
            </div>
        </div>

        <div class="cp-form-row">
            <div class="cp-form-field">
                <label for="<?php echo esc_attr( $form_id ); ?>-zip"><?php esc_html_e( 'ZIP Code', 'campaign-office' ); ?></label>
                <input type="text" id="<?php echo esc_attr( $form_id ); ?>-zip" name="volunteer_zip" maxlength="10">
            </div>
        </div>

        <?php if ( $show_interests ) : ?>
            <div class="cp-form-row">
                <fieldset class="cp-form-field">
                    <legend><?php esc_html_e( 'I am interested in:', 'campaign-office' ); ?></legend>
                    <div class="cp-checkbox-group">
                        <label><input type="checkbox" name="volunteer_interests[]" value="canvassing"> <?php esc_html_e( 'Door-to-door canvassing', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_interests[]" value="phone_banking"> <?php esc_html_e( 'Phone banking', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_interests[]" value="text_banking"> <?php esc_html_e( 'Text banking', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_interests[]" value="events"> <?php esc_html_e( 'Event support', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_interests[]" value="office"> <?php esc_html_e( 'Office help', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_interests[]" value="social_media"> <?php esc_html_e( 'Social media', 'campaign-office' ); ?></label>
                    </div>
                </fieldset>
            </div>
        <?php endif; ?>

        <?php if ( $show_availability ) : ?>
            <div class="cp-form-row">
                <fieldset class="cp-form-field">
                    <legend><?php esc_html_e( 'I am available:', 'campaign-office' ); ?></legend>
                    <div class="cp-checkbox-group">
                        <label><input type="checkbox" name="volunteer_availability[]" value="weekday_morning"> <?php esc_html_e( 'Weekday mornings', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_availability[]" value="weekday_afternoon"> <?php esc_html_e( 'Weekday afternoons', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_availability[]" value="weekday_evening"> <?php esc_html_e( 'Weekday evenings', 'campaign-office' ); ?></label>
                        <label><input type="checkbox" name="volunteer_availability[]" value="weekend"> <?php esc_html_e( 'Weekends', 'campaign-office' ); ?></label>
                    </div>
                </fieldset>
            </div>
        <?php endif; ?>

        <?php if ( $show_skills ) : ?>
            <div class="cp-form-row">
                <div class="cp-form-field">
                    <label for="<?php echo esc_attr( $form_id ); ?>-skills"><?php esc_html_e( 'Special skills or experience:', 'campaign-office' ); ?></label>
                    <textarea id="<?php echo esc_attr( $form_id ); ?>-skills" name="volunteer_skills" rows="3" placeholder="<?php esc_attr_e( 'Languages spoken, professional skills, previous campaign experience...', 'campaign-office' ); ?>"></textarea>
                </div>
            </div>
        <?php endif; ?>

        <div class="cp-form-row">
            <button type="submit" class="cp-volunteer-submit">
                <?php echo esc_html( $submit_text ); ?>
            </button>
        </div>
    </form>

    <div class="cp-volunteer-success" style="display: none;">
        <div class="cp-success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <h3><?php esc_html_e( 'Thank you for signing up!', 'campaign-office' ); ?></h3>
        <p><?php esc_html_e( 'We will be in touch soon with volunteer opportunities in your area.', 'campaign-office' ); ?></p>
    </div>
</div>
