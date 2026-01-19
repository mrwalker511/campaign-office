<?php
/**
 * Template for displaying single team posts
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
                            
                            <div class="team-meta">
                                <?php
                                $team_position = get_post_meta(get_the_ID(), '_cp_team_position', true);
                                $team_email = get_post_meta(get_the_ID(), '_cp_team_email', true);
                                $team_phone = get_post_meta(get_the_ID(), '_cp_team_phone', true);
                                $team_social = get_post_meta(get_the_ID(), '_cp_team_social', true);
                                
                                if ($team_position) {
                                    echo '<span class="team-position">' . esc_html($team_position) . '</span>';
                                }
                                if ($team_email) {
                                    echo '<span class="team-email"><a href="mailto:' . esc_attr($team_email) . '">' . esc_html($team_email) . '</a></span>';
                                }
                                if ($team_phone) {
                                    echo '<span class="team-phone"><a href="tel:' . esc_attr($team_phone) . '">' . esc_html($team_phone) . '</a></span>';
                                }
                                if ($team_social) {
                                    echo '<div class="team-social">' . wp_kses_post($team_social) . '</div>';
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
