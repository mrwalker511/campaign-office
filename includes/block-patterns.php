<?php
/**
 * Block Patterns
 *
 * @package CampaignPress
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register Custom Block Patterns
 */
function campaignpress_register_block_patterns() {
    // Hero Section Pattern
    register_block_pattern(
        'campaignpress/hero-section',
        array(
            'title'       => __('Campaign Hero Section', 'campaign-office'),
            'description' => __('Full-width hero with heading, tagline, and CTA buttons', 'campaign-office'),
            'categories'  => array('campaign-office'),
            'content'     => '<!-- wp:cover {"url":"' . get_template_directory_uri() . '/assets/images/hero-placeholder.jpg","dimRatio":50,"overlayColor":"primary-900","className":"is-style-campaign-hero"} -->
                <div class="wp-block-cover is-style-campaign-hero">
                    <span aria-hidden="true" class="wp-block-cover__background has-primary-900-background-color has-background-dim"></span>
                    <div class="wp-block-cover__inner-container">
                        <!-- wp:heading {"level":1,"fontSize":"4-xl"} -->
                        <h1 class="wp-block-heading has-4-xl-font-size">Fighting for Our Future</h1>
                        <!-- /wp:heading -->

                        <!-- wp:paragraph {"fontSize":"2-xl"} -->
                        <p class="has-2-xl-font-size">Together, we can build a better tomorrow</p>
                        <!-- /wp:paragraph -->

                        <!-- wp:buttons -->
                        <div class="wp-block-buttons">
                            <!-- wp:button {"className":"is-style-fill"} -->
                            <div class="wp-block-button is-style-fill"><a class="wp-block-button__link">Donate Now</a></div>
                            <!-- /wp:button -->

                            <!-- wp:button {"className":"is-style-outline"} -->
                            <div class="wp-block-button is-style-outline"><a class="wp-block-button__link">Get Involved</a></div>
                            <!-- /wp:button -->
                        </div>
                        <!-- /wp:buttons -->
                    </div>
                </div>
                <!-- /wp:cover -->',
        )
    );

    // Issue Card Pattern
    register_block_pattern(
        'campaignpress/issue-card',
        array(
            'title'       => __('Issue Position Card', 'campaign-office'),
            'description' => __('Highlight a policy position with icon and description', 'campaign-office'),
            'categories'  => array('campaign-office'),
            'content'     => '<!-- wp:group {"className":"is-style-issue-card","layout":{"type":"constrained"}} -->
                <div class="wp-block-group is-style-issue-card">
                    <!-- wp:paragraph {"fontSize":"4-xl"} -->
                    <p class="has-4-xl-font-size">📚</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:heading {"level":3} -->
                    <h3>Education Reform</h3>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph -->
                    <p>Every child deserves access to quality education. We will invest in teachers, modernize classrooms, and make college affordable for all.</p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->',
        )
    );

    // Team Member Grid
    register_block_pattern(
        'campaignpress/team-grid',
        array(
            'title'       => __('Team Member Grid', 'campaign-office'),
            'description' => __('A 3-column grid displaying team members with photos and bios.', 'campaign-office'),
            'categories'  => array('campaign-office'),
            'content'     => '<!-- wp:group {"layout":{"type":"constrained"}} -->
                <div class="wp-block-group">
                    <!-- wp:heading {"textAlign":"center","level":2} -->
                    <h2 class="wp-block-heading has-text-align-center">Meet Our Team</h2>
                    <!-- /wp:heading -->

                    <!-- wp:columns -->
                    <div class="wp-block-columns">
                        <!-- wp:column -->
                        <div class="wp-block-column">
                            <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"is-style-rounded"} -->
                            <figure class="wp-block-image size-full is-style-rounded"><img src="' . get_template_directory_uri() . '/assets/images/team-placeholder.jpg" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
                            <!-- /wp:image -->
                            <!-- wp:heading {"level":3,"fontSize":"lg"} -->
                            <h3 class="wp-block-heading has-lg-font-size">Jane Doe</h3>
                            <!-- /wp:heading -->
                            <!-- wp:paragraph {"fontSize":"sm","style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
                            <p class="has-text-color has-sm-font-size" style="color:var(--wp--preset--color--neutral-600)">Campaign Manager</p>
                            <!-- /wp:paragraph -->
                        </div>
                        <!-- /wp:column -->

                        <!-- wp:column -->
                        <div class="wp-block-column">
                            <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"is-style-rounded"} -->
                            <figure class="wp-block-image size-full is-style-rounded"><img src="' . get_template_directory_uri() . '/assets/images/team-placeholder.jpg" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
                            <!-- /wp:image -->
                            <!-- wp:heading {"level":3,"fontSize":"lg"} -->
                            <h3 class="wp-block-heading has-lg-font-size">John Smith</h3>
                            <!-- /wp:heading -->
                            <!-- wp:paragraph {"fontSize":"sm","style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
                            <p class="has-text-color has-sm-font-size" style="color:var(--wp--preset--color--neutral-600)">Communications Director</p>
                            <!-- /wp:paragraph -->
                        </div>
                        <!-- /wp:column -->

                        <!-- wp:column -->
                        <div class="wp-block-column">
                             <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"is-style-rounded"} -->
                            <figure class="wp-block-image size-full is-style-rounded"><img src="' . get_template_directory_uri() . '/assets/images/team-placeholder.jpg" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
                            <!-- /wp:image -->
                            <!-- wp:heading {"level":3,"fontSize":"lg"} -->
                            <h3 class="wp-block-heading has-lg-font-size">Sarah Jones</h3>
                            <!-- /wp:heading -->
                            <!-- wp:paragraph {"fontSize":"sm","style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
                            <p class="has-text-color has-sm-font-size" style="color:var(--wp--preset--color--neutral-600)">Volunteer Coordinator</p>
                            <!-- /wp:paragraph -->
                        </div>
                        <!-- /wp:column -->
                    </div>
                    <!-- /wp:columns -->
                </div>
                <!-- /wp:group -->',
        )
    );

    // Donation CTA
    register_block_pattern(
        'campaignpress/donation-cta',
        array(
            'title'       => __('Donation Call to Action', 'campaign-office'),
            'description' => __('A prominent section asking for donations.', 'campaign-office'),
            'categories'  => array('campaign-office'),
            'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|12","bottom":"var:preset|spacing|12","left":"var:preset|spacing|6","right":"var:preset|spacing|6"}}},"backgroundColor":"primary","textColor":"white","layout":{"type":"constrained"}} -->
                <div class="wp-block-group has-white-color has-primary-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--12);padding-right:var(--wp--preset--spacing--6);padding-bottom:var(--wp--preset--spacing--12);padding-left:var(--wp--preset--spacing--6)">
                    <!-- wp:heading {"textAlign":"center","fontSize":"3-xl"} -->
                    <h2 class="wp-block-heading has-text-align-center has-3-xl-font-size">Fuel Our Movement</h2>
                    <!-- /wp:heading -->

                    <!-- wp:paragraph {"align":"center","fontSize":"xl"} -->
                    <p class="has-text-align-center has-xl-font-size">Your contribution helps us reach more voters and spread our message of hope.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
                    <div class="wp-block-buttons">
                        <!-- wp:button {"backgroundColor":"accent","textColor":"primary-900","className":"is-style-fill"} -->
                        <div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-primary-900-color has-accent-background-color has-text-color has-background">Donate $25</a></div>
                        <!-- /wp:button -->
                        <!-- wp:button {"backgroundColor":"accent","textColor":"primary-900","className":"is-style-fill"} -->
                        <div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-primary-900-color has-accent-background-color has-text-color has-background">Donate $50</a></div>
                        <!-- /wp:button -->
                        <!-- wp:button {"backgroundColor":"accent","textColor":"primary-900","className":"is-style-fill"} -->
                        <div class="wp-block-button is-style-fill"><a class="wp-block-button__link has-primary-900-color has-accent-background-color has-text-color has-background">Donate $100</a></div>
                        <!-- /wp:button -->
                         <!-- wp:button {"className":"is-style-outline","style":{"border":{"width":"1px"}}} -->
                        <div class="wp-block-button is-style-outline"><a class="wp-block-button__link" style="border-width:1px">Other Amount</a></div>
                        <!-- /wp:button -->
                    </div>
                    <!-- /wp:buttons -->
                </div>
                <!-- /wp:group -->',
        )
    );

    // Event Highlight
    register_block_pattern(
        'campaignpress/event-highlight',
        array(
            'title'       => __('Event Highlight', 'campaign-office'),
            'description' => __('Highlight an upcoming event.', 'campaign-office'),
            'categories'  => array('campaign-office'),
            'content'     => '<!-- wp:columns {"verticalAlignment":"center","style":{"spacing":{"blockGap":"var:preset|spacing|8"}}} -->
                <div class="wp-block-columns are-vertically-aligned-center">
                    <!-- wp:column {"verticalAlignment":"center","width":"40%"} -->
                    <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:40%">
                         <!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"large","linkDestination":"none","className":"is-style-default"} -->
                        <figure class="wp-block-image size-large is-style-default"><img src="' . get_template_directory_uri() . '/assets/images/event-placeholder.jpg" alt="" style="aspect-ratio:4/3;object-fit:cover"/></figure>
                        <!-- /wp:image -->
                    </div>
                    <!-- /wp:column -->

                    <!-- wp:column {"verticalAlignment":"center","width":"60%"} -->
                    <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:60%">
                        <!-- wp:paragraph {"style":{"typography":{"textTransform":"uppercase","letterSpacing":"2px"}},"fontSize":"sm"} -->
                        <p class="has-sm-font-size" style="text-transform:uppercase;letter-spacing:2px">Upcoming Event</p>
                        <!-- /wp:paragraph -->

                        <!-- wp:heading {"level":2} -->
                        <h2>Town Hall Meeting</h2>
                        <!-- /wp:heading -->

                        <!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
                        <p class="has-text-color" style="color:var(--wp--preset--color--neutral-600)">Join us for an open discussion about the future of our community. Bring your questions and ideas!</p>
                        <!-- /wp:paragraph -->

                         <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                        <div class="wp-block-group">
                            <!-- wp:paragraph -->
                            <p><strong>📅 Date:</strong> Oct 15, 2025</p>
                            <!-- /wp:paragraph -->
                            <!-- wp:paragraph -->
                            <p><strong>📍 Location:</strong> Community Center</p>
                            <!-- /wp:paragraph -->
                        </div>
                        <!-- /wp:group -->

                        <!-- wp:buttons -->
                        <div class="wp-block-buttons">
                            <!-- wp:button -->
                            <div class="wp-block-button"><a class="wp-block-button__link">RSVP Now</a></div>
                            <!-- /wp:button -->
                        </div>
                        <!-- /wp:buttons -->
                    </div>
                    <!-- /wp:column -->
                </div>
                <!-- /wp:columns -->',
        )
    );

    // Newsletter Signup
    register_block_pattern(
        'campaignpress/newsletter-signup',
        array(
            'title'       => __('Newsletter Signup', 'campaign-office'),
            'description' => __('A simple signup form layout.', 'campaign-office'),
            'categories'  => array('campaign-office'),
            'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|10"}}},"backgroundColor":"neutral-100","layout":{"type":"constrained"}} -->
                <div class="wp-block-group alignfull has-neutral-100-background-color has-background" style="padding-top:var(--wp--preset--spacing--10);padding-bottom:var(--wp--preset--spacing--10)">
                    <!-- wp:columns {"verticalAlignment":"center"} -->
                    <div class="wp-block-columns are-vertically-aligned-center">
                        <!-- wp:column {"width":"50%"} -->
                        <div class="wp-block-column" style="flex-basis:50%">
                            <!-- wp:heading {"level":2} -->
                            <h2>Stay Informed</h2>
                            <!-- /wp:heading -->
                            <!-- wp:paragraph -->
                            <p>Get the latest campaign updates, news, and volunteer opportunities delivered straight to your inbox.</p>
                            <!-- /wp:paragraph -->
                        </div>
                        <!-- /wp:column -->

                        <!-- wp:column {"width":"50%"} -->
                        <div class="wp-block-column" style="flex-basis:50%">
                            <!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                            <div class="wp-block-group">
                                <!-- wp:paragraph {"placeholder":"Enter your email"} -->
                                <p><em>[Email Signup Form Placeholder]</em></p>
                                <!-- /wp:paragraph -->
                                <!-- wp:button -->
                                <div class="wp-block-button"><a class="wp-block-button__link">Subscribe</a></div>
                                <!-- /wp:button -->
                            </div>
                            <!-- /wp:group -->
                        </div>
                        <!-- /wp:column -->
                    </div>
                    <!-- /wp:columns -->
                </div>
                <!-- /wp:group -->',
        )
    );

    // Testimonial Card
    register_block_pattern(
        'campaignpress/testimonial-card',
        array(
            'title'       => __('Testimonial Card', 'campaign-office'),
            'description' => __('A styled quote block for endorsements.', 'campaign-office'),
            'categories'  => array('campaign-office'),
            'content'     => '<!-- wp:group {"style":{"border":{"width":"1px","radius":"var:preset|custom|borderRadius|lg"}},"borderColor":"neutral-300","backgroundColor":"white","layout":{"type":"constrained"}} -->
                <div class="wp-block-group has-border-color has-neutral-300-border-color has-white-background-color has-background" style="border-width:1px;border-radius:var(--wp--preset--custom--border-radius--lg)">
                    <!-- wp:quote {"className":"is-style-plain"} -->
                    <blockquote class="wp-block-quote is-style-plain">
                        <!-- wp:paragraph {"fontSize":"lg","fontStyle":"italic"} -->
                        <p class="has-lg-font-size" style="font-style:italic">"This candidate has the vision and integrity we need to move our community forward. I am proud to support them."</p>
                        <!-- /wp:paragraph -->
                        <!-- wp:paragraph {"fontSize":"sm","style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
                        <p class="has-text-color has-sm-font-size" style="color:var(--wp--preset--color--neutral-600)">— <strong>Jane Smith</strong>, Community Leader</p>
                        <!-- /wp:paragraph -->
                    </blockquote>
                    <!-- /wp:quote -->
                </div>
                <!-- /wp:group -->',
        )
    );
}
add_action('init', 'campaignpress_register_block_patterns');
