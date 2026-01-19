<?php
/**
 * Template for displaying single volunteer posts
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
                            
                            <div class="volunteer-meta">
                                <?php
                                $volunteer_type = get_post_meta(get_the_ID(), '_cp_volunteer_type', true);
                                $volunteer_commitment = get_post_meta(get_the_ID(), '_cp_volunteer_commitment', true);
                                $volunteer_skills = get_post_meta(get_the_ID(), '_cp_volunteer_skills', true);
                                
                                if ($volunteer_type) {
                                    echo '<span class="volunteer-type">' . esc_html($volunteer_type) . '</span>';
                                }
                                if ($volunteer_commitment) {
                                    echo '<span class="volunteer-commitment">' . esc_html($volunteer_commitment) . '</span>';
                                }
                                if ($volunteer_skills) {
                                    echo '<div class="volunteer-skills">' . wp_kses_post($volunteer_skills) . '</div>';
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
                            <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                <?php _e('Volunteer Now', 'campaignpress'); ?>
                            </a>
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
