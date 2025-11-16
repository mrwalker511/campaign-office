<?php
/**
 * The template for displaying single Team Members
 *
 * @package CampaignPress
 * @since 1.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        while ( have_posts() ) :
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>

                    <?php
                    $role = get_post_meta( get_the_ID(), '_cp_team_role', true );
                    if ( $role ) :
                        ?>
                        <div class="team-role-title"><?php echo esc_html( $role ); ?></div>
                    <?php endif; ?>
                </header>

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-thumbnail team-photo-large">
                        <?php the_post_thumbnail( 'campaignpress-candidate-headshot' ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php
                    the_content();

                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'campaignpress' ),
                        'after'  => '</div>',
                    ) );
                    ?>
                </div>

                <footer class="entry-footer">
                    <?php
                    // Display social links if available
                    $twitter = get_post_meta( get_the_ID(), '_cp_team_twitter', true );
                    $linkedin = get_post_meta( get_the_ID(), '_cp_team_linkedin', true );
                    $email = get_post_meta( get_the_ID(), '_cp_team_email', true );

                    if ( $twitter || $linkedin || $email ) :
                        ?>
                        <div class="team-social">
                            <h3><?php esc_html_e( 'Connect', 'campaignpress' ); ?></h3>
                            <div class="social-links">
                                <?php if ( $twitter ) : ?>
                                    <a href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener" class="social-twitter">
                                        <span class="dashicons dashicons-twitter"></span> Twitter
                                    </a>
                                <?php endif; ?>
                                <?php if ( $linkedin ) : ?>
                                    <a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener" class="social-linkedin">
                                        <span class="dashicons dashicons-linkedin"></span> LinkedIn
                                    </a>
                                <?php endif; ?>
                                <?php if ( $email ) : ?>
                                    <a href="mailto:<?php echo esc_attr( $email ); ?>" class="social-email">
                                        <span class="dashicons dashicons-email"></span> Email
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="post-navigation">
                        <?php
                        the_post_navigation( array(
                            'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous Team Member:', 'campaignpress' ) . '</span> <span class="nav-title">%title</span>',
                            'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next Team Member:', 'campaignpress' ) . '</span> <span class="nav-title">%title</span>',
                        ) );
                        ?>
                    </div>
                </footer>
            </article>

        <?php endwhile; ?>

    </main>
</div>

<?php
if ( campaignpress_show_sidebar() ) {
    get_sidebar();
}
get_footer();
