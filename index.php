<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header(); ?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <?php if (have_posts()) : ?>

                    <?php if (is_home() && !is_front_page()) : ?>
                        <header>
                            <h1 class="page-title"><?php single_post_title(); ?></h1>
                        </header>
                    <?php endif; ?>

                    <?php while (have_posts()) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <header class="entry-header">
                                <?php the_title(sprintf('<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>

                                <?php if ('post' === get_post_type()) : ?>
                                    <div class="entry-meta">
                                        <span class="posted-on"><?php echo get_the_date(); ?></span>
                                        <span class="byline"><?php _e('by', 'campaignpress'); ?> <?php the_author(); ?></span>
                                    </div>
                                <?php endif; ?>
                            </header>

                            <div class="entry-content">
                                <?php the_excerpt(); ?>
                            </div>

                            <footer class="entry-footer">
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                    <?php _e('Read More', 'campaignpress'); ?>
                                </a>
                            </footer>
                        </article>

                    <?php endwhile; ?>

                    <?php the_posts_navigation(); ?>

                <?php else : ?>

                    <section class="no-results not-found">
                        <header class="page-header">
                            <h1 class="page-title"><?php _e('Nothing here', 'campaignpress'); ?></h1>
                        </header>

                        <div class="page-content">
                            <?php if (is_home() && current_user_can('publish_posts')) : ?>
                                <p><?php
                                    printf(
                                        wp_kses(
                                            __('Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'campaignpress'),
                                            array(
                                                'a' => array(
                                                    'href' => array(),
                                                ),
                                            )
                                        ),
                                        esc_url(admin_url('post-new.php'))
                                    );
                                    ?></p>
                            <?php elseif (is_search()) : ?>
                                <p><?php _e('Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'campaignpress'); ?></p>
                                <?php get_search_form(); ?>
                            <?php else : ?>
                                <p><?php _e('It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'campaignpress'); ?></p>
                                <?php get_search_form(); ?>
                            <?php endif; ?>
                        </div>
                    </section>

                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
