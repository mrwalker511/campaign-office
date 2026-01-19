<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <section class="error-404 not-found text-center">
                    <header class="page-header">
                        <h1 class="page-title"><?php _e('Oops! That page can&rsquo;t be found.', 'campaignpress'); ?></h1>
                    </header>

                    <div class="page-content">
                        <p><?php _e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'campaignpress'); ?></p>

                        <?php get_search_form(); ?>

                        <div class="widget widget_categories">
                            <h2 class="widget-title"><?php _e('Most Used Categories', 'campaignpress'); ?></h2>
                            <ul>
                                <?php
                                wp_list_categories(
                                    array(
                                        'orderby'    => 'count',
                                        'order'      => 'DESC',
                                        'show_count' => 1,
                                        'title_li'   => '',
                                        'number'     => 10,
                                    )
                                );
                                ?>
                            </ul>
                        </div>

                        <div class="widget widget_recent_entries">
                            <h2 class="widget-title"><?php _e('Recent Posts', 'campaignpress'); ?></h2>
                            <ul>
                                <?php
                                $recent_posts = wp_get_recent_posts(
                                    array(
                                        'numberposts' => 10,
                                        'post_status' => 'publish',
                                    )
                                );
                                foreach ($recent_posts as $recent) :
                                    ?>
                                    <li>
                                        <a href="<?php echo get_permalink($recent['ID']); ?>">
                                            <?php echo esc_html($recent['post_title']); ?>
                                        </a>
                                    </li>
                                <?php endforeach; wp_reset_query(); ?>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
