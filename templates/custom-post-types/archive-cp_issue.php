<?php
/**
 * Template for displaying issue archive pages
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <header class="page-header">
                    <h1 class="page-title"><?php _e('Issues', 'campaignpress'); ?></h1>
                    <?php the_archive_description('<div class="archive-description">', '</div>'); ?>
                </header>

                <?php if (have_posts()) : ?>

                    <div class="issues-grid">
                        <?php while (have_posts()) : the_post(); ?>

                            <article id="post-<?php the_ID(); ?>" <?php post_class('issue-card'); ?>>
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="post-thumbnail">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <header class="entry-header">
                                    <?php the_title(sprintf('<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h2>'); ?>
                                </header>

                                <div class="entry-summary">
                                    <?php the_excerpt(); ?>
                                </div>

                                <footer class="entry-footer">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                        <?php _e('Read More', 'campaignpress'); ?>
                                    </a>
                                </footer>
                            </article>

                        <?php endwhile; ?>
                    </div>

                    <?php the_posts_navigation(); ?>

                <?php else : ?>

                    <section class="no-results not-found">
                        <header class="page-header">
                            <h1 class="page-title"><?php _e('Nothing here', 'campaignpress'); ?></h1>
                        </header>
                        <div class="page-content">
                            <p><?php _e('No issues found.', 'campaignpress'); ?></p>
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
