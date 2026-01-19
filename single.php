<?php
/**
 * The template for displaying single posts
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
                <?php while (have_posts()) : the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                        <header class="entry-header">
                            <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                            <?php if ('post' === get_post_type()) : ?>
                                <div class="entry-meta">
                                    <span class="posted-on"><?php echo get_the_date(); ?></span>
                                    <span class="byline"><?php _e('by', 'campaignpress'); ?> <?php the_author(); ?></span>
                                </div>
                            <?php endif; ?>
                        </header>

                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="entry-content">
                            <?php
                            the_content(
                                sprintf(
                                    wp_kses(
                                        __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'campaignpress'),
                                        array(
                                            'span' => array(
                                                'class' => array(),
                                            ),
                                        )
                                    ),
                                    get_the_title()
                                )
                            );

                            wp_link_pages(
                                array(
                                    'before' => '<div class="page-links">' . __('Pages:', 'campaignpress'),
                                    'after'  => '</div>',
                                )
                            );
                            ?>
                        </div>

                        <footer class="entry-footer">
                            <?php
                            $categories_list = get_the_category_list(esc_html__(', ', 'campaignpress'));
                            if ($categories_list) {
                                printf('<span class="cat-links">' . __('Posted in %1$s', 'campaignpress') . '</span>', $categories_list);
                            }

                            $tags_list = get_the_tag_list('', esc_html_x(', ', 'list item separator', 'campaignpress'));
                            if ($tags_list) {
                                printf('<span class="tags-links">' . __('Tagged %1$s', 'campaignpress') . '</span>', $tags_list);
                            }
                            ?>
                        </footer>
                    </article>

                    <?php
                    the_post_navigation(
                        array(
                            'prev_text' => '<span class="nav-subtitle">' . __('Previous:', 'campaignpress') . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . __('Next:', 'campaignpress') . '</span> <span class="nav-title">%title</span>',
                        )
                    );

                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;
                    ?>

                <?php endwhile; ?>
            </div>

            <div class="col-lg-4">
                <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
