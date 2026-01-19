<?php
/**
 * Template for displaying single endorsement posts
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
                            
                            <div class="endorser-info">
                                <?php
                                $endorser_title = get_post_meta(get_the_ID(), '_cp_endorser_title', true);
                                $endorser_organization = get_post_meta(get_the_ID(), '_cp_endorser_organization', true);
                                
                                if ($endorser_title || $endorser_organization) {
                                    echo '<div class="endorser-meta">';
                                    if ($endorser_title) {
                                        echo '<span class="endorser-title">' . esc_html($endorser_title) . '</span>';
                                    }
                                    if ($endorser_organization) {
                                        echo '<span class="endorser-org">' . esc_html($endorser_organization) . '</span>';
                                    }
                                    echo '</div>';
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
