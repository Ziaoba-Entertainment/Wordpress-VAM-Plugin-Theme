<?php
/**
 * single-episode.php - Episode player page.
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
                                    <p>This content is exclusive to Ziaoba members. Sign in to start watching.</p>
                                    <div class="login-required-actions">
                                        <a href="<?php echo esc_url( ziaoba_get_um_url( 'login' ) ); ?>" class="btn btn-primary">Login</a>
                                        <a href="<?php echo esc_url( ziaoba_get_um_url( 'register' ) ); ?>" class="btn btn-outline">Register</a>
                                    </div>
                                    <?php do_action( 'ziaoba_google_auth_button' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h1 class="single-title"><?php the_title(); ?></h1>
                    <div class="hero-meta">
                        <span class="match-score">98% Match</span>
                        <span><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_duration', true ) ); ?></span>
                        <span class="age-rating"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_age_rating', true ) ); ?></span>
                    </div>

                    <div class="single-content">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    $post_id = get_the_ID();
                    $season_id = wp_get_post_parent_id( $post_id );

                    if ( $season_id ) : // This is an episode
                        $season = get_post( $season_id );
                        $series_id = wp_get_post_parent_id( $season_id );
                        $series = $series_id ? get_post( $series_id ) : null;
                        
                        $episode_num = get_post_meta( $post_id, '_ziaoba_episode_number', true );
                        $season_num = get_post_meta( $season_id, '_ziaoba_season_number', true );
                        
                        // Get sibling episodes for prev/next
                        $siblings = get_posts( array(
                            'post_parent'    => $season_id,
                            'post_type'      => 'episode',
                            'posts_per_page' => -1,
                            'meta_key'       => '_ziaoba_episode_number',
                            'orderby'        => 'meta_value_num',
                            'order'          => 'ASC',
                        ) );

                        $prev_ep = null;
                        $next_ep = null;
                        foreach ( $siblings as $index => $sibling ) {
                            if ( $sibling->ID == $post_id ) {
                                if ( isset( $siblings[$index - 1] ) ) $prev_ep = $siblings[$index - 1];
                                if ( isset( $siblings[$index + 1] ) ) $next_ep = $siblings[$index + 1];
                                break;
                            }
                        }
                    ?>
                        <div class="episode-nav-wrap">
                            <div class="series-info-box">
                                <span class="series-label">Series:</span>
                                <?php if ( $series ) : ?>
                                    <a href="<?php echo get_permalink( $series_id ); ?>" class="series-link"><?php echo esc_html( $series->post_title ); ?></a>
                                    <span class="ep-meta">
                                        • <a href="<?php echo get_permalink( $season_id ); ?>" class="season-link">Season <?php echo esc_html( $season_num ); ?></a>
                                        • Episode <?php echo esc_html( $episode_num ); ?>
                                    </span>
                                <?php else : ?>
                                    <a href="<?php echo get_permalink( $season_id ); ?>" class="series-link"><?php echo esc_html( $season->post_title ); ?></a>
                                    <span class="ep-meta">
                                        • Episode <?php echo esc_html( $episode_num ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="ep-controls">
                                <?php if ( $prev_ep ) : ?>
                                    <a href="<?php echo get_permalink( $prev_ep->ID ); ?>" class="ep-btn prev-ep">
                                        <i data-lucide="chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo get_permalink( $season_id ); ?>" class="ep-btn all-ep">
                                    <i data-lucide="layers"></i> All Episodes
                                </a>

                                <?php if ( $next_ep ) : ?>
                                    <a href="<?php echo get_permalink( $next_ep->ID ); ?>" class="ep-btn next-ep">
                                        Next <i data-lucide="chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>

                <aside class="sidebar">
                    <div class="sidebar-box">
                        <h3 style="margin-bottom: 20px;">Related Education</h3>
                        <?php
                        $related_id = get_post_meta( get_the_ID(), '_ziaoba_related_content', true );
                        if ( $related_id ) :
                            $related = get_post( $related_id );
                            if ( $related ) :
                        ?>
                            <div class="related-card" onclick="location.href='<?php echo esc_url( get_permalink( $related->ID ) ); ?>'">
                                <div class="related-thumb episode-card-img-wrapper" style="margin-bottom: 10px;">
                                    <?php echo wp_kses_post( ziaoba_get_display_poster( $related->ID, 'medium' ) ); ?>
                                </div>
                                <h4><?php echo esc_html( $related->post_title ); ?></h4>
                            </div>
                        <?php endif; endif; ?>
                    </div>
                </aside>
            </div>

        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
