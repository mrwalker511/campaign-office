<?php
/**
 * Template for displaying single event posts
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
                            
                            <div class="event-meta">
                                <?php
                                $event_date = get_post_meta(get_the_ID(), '_cp_event_date', true);
                                $event_time = get_post_meta(get_the_ID(), '_cp_event_time', true);
                                $event_location = get_post_meta(get_the_ID(), '_cp_event_location', true);
                                
                                if ($event_date) {
                                    echo '<span class="event-date">' . esc_html($event_date) . '</span>';
                                }
                                if ($event_time) {
                                    echo '<span class="event-time">' . esc_html($event_time) . '</span>';
                                }
                                if ($event_location) {
                                    echo '<span class="event-location">' . esc_html($event_location) . '</span>';
                                }
                                ?>
                            </div>
                        </header>

                        <?php if (has_post_thumbnail()) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="entry-content">
                            <?php
                            the_content();

                            wp_link_pages(
                                array(
                                    'before' => '<div class="page-links">' . __('Pages:', 'campaignpress'),
                                    'after'  => '</div>',
                                )
                            );
                            ?>
                        </div>

                        <footer class="entry-footer">
                            <div class="event-actions">
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                    <?php _e('RSVP', 'campaignpress'); ?>
                                </a>
                                <?php
                                $event_date = get_post_meta(get_the_ID(), '_cp_event_date', true);
                                if ($event_date) {
                                    echo '<a href="' . esc_url(add_query_arg('calendar', 'google', get_permalink())) . '" class="btn btn-secondary">';
                                    _e('Add to Google Calendar', 'campaignpress');
                                    echo '</a>';
                                }
                                ?>
                            </div>
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
