<?php
/**
 * Title: Staff Directory
 * Slug: campaignpress/staff-directory
 * Categories: campaignpress, team
 * Keywords: staff, team, directory
 */
?>
<!-- wp:group {"align":"wide","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide">
    <!-- wp:heading {"textAlign":"center","level":2} -->
    <h2 class="wp-block-heading has-text-align-center"><?php esc_html_e( 'Meet the Team', 'campaignpress' ); ?></h2>
    <!-- /wp:heading -->

    <!-- wp:columns {"style":{"spacing":{"blockGap":"2rem"}}} -->
    <div class="wp-block-columns">
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"medium","linkDestination":"none","className":"is-style-rounded"} -->
            <figure class="wp-block-image size-medium is-style-rounded"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/team-placeholder.jpg' ); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
            <!-- /wp:image -->
            <!-- wp:heading {"textAlign":"center","level":3,"fontSize":"lg"} -->
            <h3 class="wp-block-heading has-text-align-center has-lg-font-size">Jane Doe</h3>
            <!-- /wp:heading -->
            <!-- wp:paragraph {"align":"center","fontSize":"sm","style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
            <p class="has-text-align-center has-text-color has-sm-font-size" style="color:var(--wp--preset--color--neutral-600)"><?php esc_html_e( 'Campaign Manager', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->

        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"medium","linkDestination":"none","className":"is-style-rounded"} -->
            <figure class="wp-block-image size-medium is-style-rounded"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/team-placeholder.jpg' ); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
            <!-- /wp:image -->
            <!-- wp:heading {"textAlign":"center","level":3,"fontSize":"lg"} -->
            <h3 class="wp-block-heading has-text-align-center has-lg-font-size">John Smith</h3>
            <!-- /wp:heading -->
            <!-- wp:paragraph {"align":"center","fontSize":"sm","style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
            <p class="has-text-align-center has-text-color has-sm-font-size" style="color:var(--wp--preset--color--neutral-600)"><?php esc_html_e( 'Communications', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column -->
        <div class="wp-block-column">
            <!-- wp:image {"aspectRatio":"1","scale":"cover","sizeSlug":"medium","linkDestination":"none","className":"is-style-rounded"} -->
            <figure class="wp-block-image size-medium is-style-rounded"><img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/team-placeholder.jpg' ); ?>" alt="" style="aspect-ratio:1;object-fit:cover"/></figure>
            <!-- /wp:image -->
            <!-- wp:heading {"textAlign":"center","level":3,"fontSize":"lg"} -->
            <h3 class="wp-block-heading has-text-align-center has-lg-font-size">Mary Jones</h3>
            <!-- /wp:heading -->
            <!-- wp:paragraph {"align":"center","fontSize":"sm","style":{"color":{"text":"var:preset|color|neutral-600"}}} -->
            <p class="has-text-align-center has-text-color has-sm-font-size" style="color:var(--wp--preset--color--neutral-600)"><?php esc_html_e( 'Field Director', 'campaignpress' ); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
