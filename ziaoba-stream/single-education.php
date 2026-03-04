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
                    <div class="player-container" style="margin-bottom: 30px;">
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
                    <div style="background: var(--hover-color); display: inline-block; padding: 4px 12px; border-radius: 4px; font-weight: 800; font-size: 12px; margin-bottom: 20px;">EDUCATION</div>
                    
                    <div class="content" style="font-size: 18px; color: #e5e5e5; line-height: 1.6;">
                        <?php the_content(); ?>
                    </div>
                </div>

                <aside class="sidebar">
                    <div class="sidebar-box">
                        <h3 style="margin-bottom: 20px;">Related Entertainment</h3>
                        <?php
                        $related_id = get_post_meta( get_the_ID(), '_ziaoba_related_content', true );
                        if ( $related_id ) :
                            $related = get_post( $related_id );
                            if ( $related ) :
                        ?>
                            <div class="related-card" onclick="location.href='<?php echo get_permalink($related->ID); ?>'" style="cursor: pointer;">
                                <?php if ( has_post_thumbnail( $related->ID ) ) : ?>
                                    <?php echo get_the_post_thumbnail( $related->ID, 'medium', array( 'style' => 'width: 100%; border-radius: 8px; margin-bottom: 10px;', 'loading' => 'lazy' ) ); ?>
                                <?php else : ?>
                                    <img src="https://picsum.photos/seed/rel-<?php echo $related->ID; ?>/400/225" style="width: 100%; border-radius: 8px; margin-bottom: 10px;" loading="lazy">
                                <?php endif; ?>
                                <h4 style="font-size: 14px;"><?php echo esc_html($related->post_title); ?></h4>
                            </div>
                        <?php endif; endif; ?>
                    </div>
                </aside>
            </div>

        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
