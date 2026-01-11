<?php
/**
 * Title: Contact Main
 * Slug: campaignpress/contact-main
 * Categories: campaign-office, call-to-action
 * Keywords: contact, form, office, locations
 */
?>
<!-- wp:group {"align":"full","className":"cp-section cp-section--white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull cp-section cp-section--white">
    <!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":"4rem"}}} -->
    <div class="wp-block-columns alignwide">
        <!-- wp:column {"width":"65%"} -->
        <div class="wp-block-column" style="flex-basis:65%">
            <!-- wp:paragraph {"className":"cp-section__label","style":{"typography":{"fontWeight":"700"}}} -->
            <p class="cp-section__label" style="font-weight:700"><?php esc_html_e( 'SEND A MESSAGE', 'campaign-office' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:heading {"level":2,"className":"cp-section__title cp-mb-4"} -->
            <h2 class="wp-block-heading cp-section__title cp-mb-4"><?php esc_html_e( 'We\'re Here to Listen', 'campaign-office' ); ?></h2>
            <!-- /wp:heading -->

            <!-- wp:group {"className":"cp-contact-form"} -->
            <div class="wp-block-group cp-contact-form">
                <!-- wp:columns -->
                <div class="wp-block-columns">
                    <!-- wp:column -->
                    <div class="wp-block-column">
                        <!-- wp:paragraph {"className":"form-label"} --><p class="form-label"><?php esc_html_e( 'Full Name *', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                        <!-- wp:html -->
                        <input type="text" class="form-control" placeholder="John Smith">
                        <!-- /wp:html -->
                    </div>
                    <!-- /wp:column -->
                    <!-- wp:column -->
                    <div class="wp-block-column">
                        <!-- wp:paragraph {"className":"form-label"} --><p class="form-label"><?php esc_html_e( 'Email Address *', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                        <!-- wp:html -->
                        <input type="email" class="form-control" placeholder="john@example.com">
                        <!-- /wp:html -->
                    </div>
                    <!-- /wp:column -->
                </div>
                <!-- /wp:columns -->

                <!-- wp:columns -->
                <div class="wp-block-columns">
                    <!-- wp:column -->
                    <div class="wp-block-column">
                        <!-- wp:paragraph {"className":"form-label"} --><p class="form-label"><?php esc_html_e( 'Phone Number', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                        <!-- wp:html -->
                        <input type="tel" class="form-control" placeholder="(555) 123-4567">
                        <!-- /wp:html -->
                    </div>
                    <!-- /wp:column -->
                    <!-- wp:column -->
                    <div class="wp-block-column">
                        <!-- wp:paragraph {"className":"form-label"} --><p class="form-label"><?php esc_html_e( 'Subject *', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                        <!-- wp:html -->
                        <select class="form-control">
                            <option><?php esc_html_e( 'Select a topic...', 'campaign-office' ); ?></option>
                            <option><?php esc_html_e( 'Volunteering', 'campaign-office' ); ?></option>
                            <option><?php esc_html_e( 'Donations', 'campaign-office' ); ?></option>
                            <option><?php esc_html_e( 'General Inquiry', 'campaign-office' ); ?></option>
                        </select>
                        <!-- /wp:html -->
                    </div>
                    <!-- /wp:column -->
                </div>
                <!-- /wp:columns -->

                <!-- wp:paragraph {"className":"form-label"} --><p class="form-label"><?php esc_html_e( 'Your Message *', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                <!-- wp:html -->
                <textarea class="form-control" style="height: 150px" placeholder="Tell us what's on your mind..."></textarea>
                <!-- /wp:html -->

                <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"},"style":{"spacing":{"margin":{"bottom":"1.5rem"}}}} -->
                <div class="wp-block-group cp-mb-4" style="margin-bottom:1.5rem">
                    <!-- wp:html -->
                    <input type="checkbox" id="keep-updated" style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer;">
                    <label for="keep-updated" style="font-size: 0.875rem; color: var(--ds-text-muted); cursor: pointer;"><?php esc_html_e( 'Keep me updated on campaign news, events, and ways to get involved.', 'campaign-office' ); ?></label>
                    <!-- /wp:html -->
                </div>
                <!-- /wp:group -->

                <!-- wp:button {"backgroundColor":"secondary","className":"is-style-fill","style":{"typography":{"fontWeight":"700"}}} -->
                <div class="wp-block-button is-style-fill"><a class="wp-block-button__link wp-element-button has-secondary-background-color has-background" style="font-weight:700"><span class="icon" style="margin-right: 10px;">✈</span><?php esc_html_e( 'Send Message', 'campaign-office' ); ?></a></div>
                <!-- /wp:button -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"35%"} -->
        <div class="wp-block-column" style="flex-basis:35%">
            <!-- wp:paragraph {"className":"cp-section__label","style":{"spacing":{"margin":{"bottom":"1rem"}},"typography":{"color":"var:preset|color|neutral-400"}}} -->
            <p class="cp-section__label" style="margin-bottom:1rem;color:var(--wp--preset--color--neutral-400)"><?php esc_html_e( 'OUR OFFICES', 'campaign-office' ); ?></p>
            <!-- /wp:paragraph -->

            <!-- wp:group {"className":"cp-contact-card"} -->
            <div class="wp-block-group cp-contact-card">
                <!-- wp:paragraph {"className":"cp-contact-card__title"} --><p class="cp-contact-card__title"><?php esc_html_e( 'Campaign Headquarters', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">📍</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>1776 Liberty Avenue, Suite 200<br>Springfield, State 12345</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">📞</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>(555) 123-4567</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">🕒</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>Mon-Fri: 9am - 6pm</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->

            <!-- wp:group {"className":"cp-contact-card"} -->
            <div class="wp-block-group cp-contact-card">
                <!-- wp:paragraph {"className":"cp-contact-card__title"} --><p class="cp-contact-card__title"><?php esc_html_e( 'Downtown Office', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">📍</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>45 Main Street<br>Capital City, State 12346</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">📞</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>(555) 987-6543</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">🕒</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>Mon-Sat: 10am - 5pm</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->

            <!-- wp:group {"className":"cp-contact-card","style":{"backgroundColor":"neutral-50","border":{"width":"0px"}}} -->
            <div class="wp-block-group cp-contact-card has-neutral-50-background-color has-background" style="border-width:0px; border-left: none;">
                <!-- wp:paragraph {"className":"cp-section__label","style":{"spacing":{"margin":{"bottom":"1rem"}},"typography":{"color":"var:preset|color|neutral-400"}}} -->
                <p class="cp-section__label" style="margin-bottom:1rem;color:var(--wp--preset--color--neutral-400)"><?php esc_html_e( 'QUICK CONTACT', 'campaign-office' ); ?></p>
                <!-- /wp:paragraph -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">✉</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>info@harrison2026.com</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
                <!-- wp:group {"className":"cp-contact-info"} -->
                <div class="wp-block-group cp-contact-info">
                    <!-- wp:paragraph {"className":"icon"} --><p class="icon">📞</p><!-- /wp:paragraph -->
                    <!-- wp:paragraph --><p>(555) 123-VOTE</p><!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->

            <!-- wp:group {"className":"cp-social-box"} -->
            <div class="wp-block-group cp-social-box">
                <!-- wp:paragraph {"className":"cp-social-box__title"} --><p class="cp-social-box__title"><?php esc_html_e( 'FOLLOW THE CAMPAIGN', 'campaign-office' ); ?></p><!-- /wp:paragraph -->
                <!-- wp:group {"className":"cp-social-icons"} -->
                <div class="wp-block-group cp-social-icons">
                    <!-- wp:html -->
                    <a href="#">f</a>
                    <a href="#">t</a>
                    <a href="#">i</a>
                    <!-- /wp:html -->
                </div>
                <!-- /wp:group -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
