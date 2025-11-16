<?php
/**
 * Template Name: Landing Page (No Header/Footer Navigation)
 * Template Post Type: post, page
 *
 * @package CampaignPress
 * @since 1.0.0
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class( 'landing-page-template' ); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <div id="content" class="site-content">
        <div id="primary" class="content-area full-width-template">
            <main id="main" class="site-main">

                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <div class="entry-content">
                            <?php
                            the_content();

                            wp_link_pages( array(
                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campaignpress' ),
                                'after'  => '</div>',
                            ) );
                            ?>
                        </div>
                    </article>

                <?php endwhile; ?>

            </main>
        </div>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
