<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package CampaignPress
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Block Theme Note:
 *
 * As a block theme, CampaignPress uses HTML templates in the /templates/ directory
 * instead of traditional PHP template files. This index.php file is required by
 * WordPress for theme activation but is not actually used during normal operation.
 *
 * The block template hierarchy works as follows:
 * 1. WordPress checks for matching HTML templates in /templates/
 * 2. Falls back to index.html if no specific template is found
 * 3. This index.php serves only as a final fallback for compatibility
 *
 * See: https://developer.wordpress.org/themes/block-themes/templates-and-template-parts/
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="wp-site-blocks">
    <header class="wp-block-template-part">
        <?php get_template_part('parts/header'); ?>
    </header>

    <main class="wp-block-group">
        <div class="wp-block-group__inner-container">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                        </header>

                        <div class="entry-content">
                            <?php
                            the_content();

                            wp_link_pages(array(
                                'before' => '<div class="page-links">' . esc_html__('Pages:', 'campaign-office'),
                                'after'  => '</div>',
                            ));
                            ?>
                        </div>

                        <?php if (comments_open() || get_comments_number()) : ?>
                            <?php comments_template(); ?>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>

                <?php the_posts_navigation(); ?>

            <?php else : ?>
                <article class="no-results not-found">
                    <header class="entry-header">
                        <h1 class="entry-title"><?php esc_html_e('Nothing Found', 'campaign-office'); ?></h1>
                    </header>

                    <div class="entry-content">
                        <p><?php esc_html_e('It looks like nothing was found at this location. Maybe try a search?', 'campaign-office'); ?></p>
                        <?php get_search_form(); ?>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </main>

    <footer class="wp-block-template-part">
        <?php get_template_part('parts/footer'); ?>
    </footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
