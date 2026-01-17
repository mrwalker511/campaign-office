<?php
/**
 * Title: News Ticker
 * Slug: campaignpress/news-ticker
 * Categories: campaignpress, text
 * Keywords: news, ticker, latest
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"0.5rem","bottom":"0.5rem"}}},"backgroundColor":"neutral-900","textColor":"white","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group alignfull has-white-color has-neutral-900-background-color has-text-color has-background" style="padding-top:0.5rem;padding-bottom:0.5rem">
    <!-- wp:paragraph {"style":{"typography":{"fontWeight":"700","textTransform":"uppercase"},"color":{"text":"var:preset|color|accent"}}} -->
    <p class="has-text-color" style="color:var(--wp--preset--color--accent);font-weight:700;text-transform:uppercase"><?php esc_html_e( 'Latest News:', 'campaignpress' ); ?></p>
    <!-- /wp:paragraph -->

    <!-- wp:query {"queryId":2,"query":{"perPage":1,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list"}} -->
    <div class="wp-block-query">
        <!-- wp:post-template -->
            <!-- wp:post-title {"isLink":true,"style":{"typography":{"fontSize":"small"}}} /-->
        <!-- /wp:post-template -->
    </div>
    <!-- /wp:query -->
    
    <!-- wp:paragraph {"fontSize":"small"} -->
    <p class="has-small-font-size"><a href="#" style="color:inherit"><?php esc_html_e( 'Read More →', 'campaignpress' ); ?></a></p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
