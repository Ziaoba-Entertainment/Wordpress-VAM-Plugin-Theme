<?php
/**
 * single-education.php - Education player page.
 */

get_header(); ?>

<main class="single-player-wrap">
    <div class="container">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div class="single-grid">
                <div class="main-content">
                    <div class="player-container">
                        <?php if ( is_user_logged_in() ) : ?>
                            <?php echo do_shortcode( '[ziaoba_player]' ); ?>
                        <?php else : ?>
                            <div class="login-required-box">
                                <div class="login-required-content">
                                    <h2>Login Required</h2>
                                    <p>Join Ziaoba to access this lesson and build your future.</p>
                                    <div class="login-required-actions">
                                        <a href="<?php echo esc_url( ziaoba_get_um_url( 'login' ) ); ?>" class="btn btn-primary">Login to Learn</a>
                                        <a href="<?php echo esc_url( ziaoba_get_um_url( 'register' ) ); ?>" class="btn btn-outline">Register</a>
                                    </div>
                                    <?php do_action( 'ziaoba_google_auth_button' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h1 class="single-title"><?php the_title(); ?></h1>
                    <div class="education-badge">EDUCATION</div>
                    
                    <div class="single-content">
                        <?php the_content(); ?>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="sidebar-box">
                        <h3 class="sidebar-title">Related Entertainment</h3>
                        <?php
                        $related_id = get_post_meta( get_the_ID(), '_ziaoba_related_content', true );
                        if ( $related_id ) :
                            $related = get_post( $related_id );
                            if ( $related ) :
                        ?>
                            <div class="related-card" onclick="location.href='<?php echo get_permalink($related->ID); ?>'">
                                <div class="card-img-wrapper" style="margin-bottom: 10px;">
                                    <?php echo wp_kses_post( ziaoba_get_display_poster( $related->ID, 'medium' ) ); ?>
                                </div>
                                <h4><?php echo esc_html($related->post_title); ?></h4>
                            </div>
                        <?php endif; endif; ?>
                    </div>
                </aside>
            </div>

        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
