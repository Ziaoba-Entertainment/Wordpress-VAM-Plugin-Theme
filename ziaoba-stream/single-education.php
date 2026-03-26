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
                                <?php if ( has_post_thumbnail( $related->ID ) ) : ?>
                                    <?php echo get_the_post_thumbnail( $related->ID, 'medium', array( 'class' => 'related-card-img', 'loading' => 'lazy' ) ); ?>
                                <?php else : ?>
                                    <img src="https://picsum.photos/seed/rel-<?php echo $related->ID; ?>/400/225" class="related-card-img" loading="lazy">
                                <?php endif; ?>
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
