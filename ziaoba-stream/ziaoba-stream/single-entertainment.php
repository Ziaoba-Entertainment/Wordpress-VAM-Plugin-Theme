<?php
/**
 * single-entertainment.php - Video player page.
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
                    <div class="hero-meta" style="margin-bottom: 30px;">
                        <span style="color: var(--hover-color);">98% Match</span>
                        <span><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_duration', true ) ); ?></span>
                        <span style="border: 1px solid #666; padding: 0 6px; border-radius: 2px;"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_age_rating', true ) ); ?></span>
                        <span><?php echo get_the_term_list( get_the_ID(), 'genre', '', ', ' ); ?></span>
                    </div>

                    <div class="content" style="font-size: 18px; color: #e5e5e5; line-height: 1.6; margin-bottom: 40px;">
                        <?php the_content(); ?>
                    </div>

                    <?php
                    $post_id = get_the_ID();
                    $parent_id = wp_get_post_parent_id( $post_id );

                    if ( $parent_id ) : // This is an episode (child)
                        $parent = get_post( $parent_id );
                        $season = get_post_meta( $post_id, '_ziaoba_season', true );
                        $episode_num = get_post_meta( $post_id, '_ziaoba_episode_number', true );
                        
                        // Get sibling episodes for prev/next
                        $siblings = get_posts( array(
                            'post_parent'    => $parent_id,
                            'post_type'      => 'entertainment',
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
                                <a href="<?php echo get_permalink( $parent_id ); ?>" class="series-link"><?php echo esc_html( $parent->post_title ); ?></a>
                                <?php if ( $season || $episode_num ) : ?>
                                    <span class="ep-meta">
                                        <?php if ( $season ) echo 'Season ' . esc_html( $season ); ?>
                                        <?php if ( $season && $episode_num ) echo ' • '; ?>
                                        <?php if ( $episode_num ) echo 'Episode ' . esc_html( $episode_num ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="ep-controls">
                                <?php if ( $prev_ep ) : ?>
                                    <a href="<?php echo get_permalink( $prev_ep->ID ); ?>" class="ep-btn prev-ep">
                                        <i data-lucide="chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <a href="<?php echo get_permalink( $parent_id ); ?>" class="ep-btn all-ep">
                                    <i data-lucide="layers"></i> All Episodes
                                </a>

                                <?php if ( $next_ep ) : ?>
                                    <a href="<?php echo get_permalink( $next_ep->ID ); ?>" class="ep-btn next-ep">
                                        Next <i data-lucide="chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php else : // This might be a series parent
                        $episodes = get_posts( array(
                            'post_parent'    => $post_id,
                            'post_type'      => 'entertainment',
                            'posts_per_page' => -1,
                            'meta_key'       => '_ziaoba_episode_number',
                            'orderby'        => 'meta_value_num',
                            'order'          => 'ASC',
                        ) );

                        if ( $episodes ) :
                            $seasons = array();
                            foreach ( $episodes as $ep ) {
                                $s = get_post_meta( $ep->ID, '_ziaoba_season', true );
                                if ( $s ) $seasons[] = $s;
                            }
                            $seasons = array_unique( $seasons );
                            sort( $seasons );
                            $latest_season = !empty( $seasons ) ? end( $seasons ) : '';
                    ?>
                        <div class="episodes-section">
                            <div class="episodes-header">
                                <h2 class="section-title">Episodes</h2>
                                <?php if ( !empty( $seasons ) ) : ?>
                                    <div class="season-selector-wrap">
                                        <select id="seasonSelector" class="season-select">
                                            <option value="all">All Seasons</option>
                                            <?php foreach ( $seasons as $season ) : ?>
                                                <option value="<?php echo esc_attr( $season ); ?>" <?php selected( $season, $latest_season ); ?>>
                                                    Season <?php echo esc_html( $season ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="episodes-grid" id="episodesGrid">
                                <?php foreach ( $episodes as $episode ) : 
                                    $ep_num = get_post_meta( $episode->ID, '_ziaoba_episode_number', true );
                                    $ep_season = get_post_meta( $episode->ID, '_ziaoba_season', true );
                                    $ep_duration = get_post_meta( $episode->ID, '_ziaoba_duration', true );
                                ?>
                                    <div class="episode-card" data-season="<?php echo esc_attr( $ep_season ); ?>" onclick="location.href='<?php echo get_permalink($episode->ID); ?>'">
                                        <div class="card-img-wrapper">
                                            <?php if ( has_post_thumbnail( $episode->ID ) ) : ?>
                                                <?php echo get_the_post_thumbnail( $episode->ID, 'medium_large', array( 'class' => 'card-img', 'loading' => 'lazy' ) ); ?>
                                            <?php else : ?>
                                                <img src="https://picsum.photos/seed/ep-<?php echo $episode->ID; ?>/400/225" class="card-img" loading="lazy">
                                            <?php endif; ?>
                                            <div class="card-overlay">
                                                <i data-lucide="play-circle"></i>
                                            </div>
                                            <?php if ( $ep_num ) : ?>
                                                <div class="ep-badge">E<?php echo esc_html( $ep_num ); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-info">
                                            <h3 class="card-title"><?php echo esc_html( $episode->post_title ); ?></h3>
                                            <div class="card-meta">
                                                <?php if ( $ep_duration ) : ?>
                                                    <span><?php echo esc_html( $ep_duration ); ?></span>
                                                <?php endif; ?>
                                                <?php if ( $ep_season ) : ?>
                                                    <span>S<?php echo esc_html( $ep_season ); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; endif; ?>
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
