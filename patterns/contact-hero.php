<?php
/**
 * Title: Contact Hero
 * Slug: campaignpress/contact-hero
 * Categories: campaign-office, call-to-action
 * Keywords: contact, help, help center
 */
?>
<!-- wp:group {"align":"full","className":"cp-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|16"}}},"backgroundColor":"primary","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cp-hero has-white-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--16)">
    <!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"1.5rem"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
    <div class="wp-block-group alignwide">
        <!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"clamp(2.5rem, 6vw, 5rem)","fontWeight":"700"}},"fontFamily":"display"} -->
        <h1 class="wp-block-heading has-text-align-center has-display-font-family" style="font-size:clamp(2.5rem, 6vw, 5rem);font-weight:700"><?php esc_html_e( 'Get In Touch', 'campaignpress' ); ?></h1>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.25rem","fontWeight":"400","lineHeight":"1.6"}},"textColor":"white"} -->
        <p class="has-text-align-center has-white-color has-text-color" style="font-size:1.25rem;font-weight:400;line-height:1.6;max-width:800px;margin-left:auto;margin-right:auto"><?php esc_html_e( 'Have a question, want to volunteer, or just want to share your thoughts? We\'d love to hear from you.', 'campaignpress' ); ?></p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->
