<?php
/**
 * single-entertainment.php - Video player page.
 */

get_header();
?>
<main class="single-player-wrap">
    <div class="container">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            <?php
            $post_id          = get_the_ID();
            $parent_id        = wp_get_post_parent_id( $post_id );
            $content_type     = get_post_meta( $post_id, '_ziaoba_content_type', true );
            $is_episode       = $parent_id || 'episode' === $content_type;
            $display_meta     = ziaoba_get_content_meta( $post_id );
            $related_posts    = ziaoba_get_related_posts( $post_id, 4 );
            $total_seasons    = get_post_meta( $post_id, '_ziaoba_total_seasons', true );
            $total_episodes   = get_post_meta( $post_id, '_ziaoba_total_episodes', true );
            ?>
            <div class="single-grid">
                <div class="main-content">
                    <div class="player-container" style="margin-bottom: 30px;">
                        <?php if ( is_user_logged_in() ) : ?>
                            <?php echo do_shortcode( '[ziaoba_player]' ); ?>
                        <?php else : ?>
                            <div class="login-required-box">
                                <div class="login-required-content auth-enhanced-form" data-auth-mode="login-gate">
                                    <h2><?php esc_html_e( 'Login Required', 'ziaoba-stream' ); ?></h2>
                                    <p><?php esc_html_e( 'This content is exclusive to Ziaoba members. Sign in with email or Google to continue.', 'ziaoba-stream' ); ?></p>
                                    <div class="login-required-actions">
                                        <a href="<?php echo esc_url( ziaoba_get_auth_url( 'login', get_permalink() ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Login', 'ziaoba-stream' ); ?></a>
                                        <a href="<?php echo esc_url( ziaoba_get_auth_url( 'register', get_permalink() ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Register', 'ziaoba-stream' ); ?></a>
                                    </div>
                                    <?php do_action( 'ziaoba_google_auth_button' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h1 class="single-title"><?php the_title(); ?></h1>
                    <div class="hero-meta" style="margin-bottom: 18px; flex-wrap: wrap;">
                        <?php foreach ( $display_meta as $item ) : ?>
                            <span><?php echo esc_html( $item ); ?></span>
                        <?php endforeach; ?>
                        <?php if ( $total_seasons ) : ?><span><?php echo esc_html( sprintf( __( '%d Seasons', 'ziaoba-stream' ), (int) $total_seasons ) ); ?></span><?php endif; ?>
                        <?php if ( $total_episodes ) : ?><span><?php echo esc_html( sprintf( __( '%d Episodes', 'ziaoba-stream' ), (int) $total_episodes ) ); ?></span><?php endif; ?>
                        <span><?php echo wp_kses_post( get_the_term_list( $post_id, 'genre', '', ', ' ) ); ?></span>
                    </div>
                    <?php if ( get_post_meta( $post_id, '_ziaoba_tagline', true ) ) : ?>
                        <p class="single-tagline"><?php echo esc_html( get_post_meta( $post_id, '_ziaoba_tagline', true ) ); ?></p>
                    <?php endif; ?>

                    <div class="content single-description">
                        <?php the_content(); ?>
                    </div>

                    <div class="metadata-grid">
                        <?php foreach ( array(
                            __( 'Original title', 'ziaoba-stream' ) => get_post_meta( $post_id, '_ziaoba_original_title', true ),
                            __( 'Status', 'ziaoba-stream' ) => get_post_meta( $post_id, '_ziaoba_status', true ),
                            __( 'Language', 'ziaoba-stream' ) => get_post_meta( $post_id, '_ziaoba_original_language', true ),
                            __( 'Networks', 'ziaoba-stream' ) => get_post_meta( $post_id, '_ziaoba_networks', true ),
                            __( 'Countries', 'ziaoba-stream' ) => get_post_meta( $post_id, '_ziaoba_origin_countries', true ),
                            __( 'TMDB Votes', 'ziaoba-stream' ) => get_post_meta( $post_id, '_ziaoba_tmdb_vote_count', true ),
                        ) as $label => $value ) : ?>
                            <?php if ( $value ) : ?>
                                <div class="metadata-card"><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php
                    if ( $is_episode && $parent_id ) :
                        $parent      = get_post( $parent_id );
                        $season      = get_post_meta( $post_id, '_ziaoba_season', true );
                        $episode_num = get_post_meta( $post_id, '_ziaoba_episode_number', true );
                        $siblings    = get_posts( array(
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
                            if ( (int) $sibling->ID === (int) $post_id ) {
                                $prev_ep = $siblings[ $index - 1 ] ?? null;
                                $next_ep = $siblings[ $index + 1 ] ?? null;
                                break;
                            }
                        }
                        ?>
                        <div class="episode-nav-wrap">
                            <div class="series-info-box">
                                <span class="series-label"><?php esc_html_e( 'Series:', 'ziaoba-stream' ); ?></span>
                                <a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>" class="series-link"><?php echo esc_html( $parent->post_title ); ?></a>
                                <?php if ( $season || $episode_num ) : ?>
                                    <span class="ep-meta"><?php echo esc_html( trim( 'Season ' . $season . ' • Episode ' . $episode_num, ' •' ) ); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="ep-controls">
                                <?php if ( $prev_ep ) : ?><a href="<?php echo esc_url( get_permalink( $prev_ep->ID ) ); ?>" class="ep-btn prev-ep"><i data-lucide="chevron-left"></i> <?php esc_html_e( 'Previous', 'ziaoba-stream' ); ?></a><?php endif; ?>
                                <a href="<?php echo esc_url( get_permalink( $parent_id ) ); ?>" class="ep-btn all-ep"><i data-lucide="layers"></i> <?php esc_html_e( 'All Episodes', 'ziaoba-stream' ); ?></a>
                                <?php if ( $next_ep ) : ?><a href="<?php echo esc_url( get_permalink( $next_ep->ID ) ); ?>" class="ep-btn next-ep"><?php esc_html_e( 'Next', 'ziaoba-stream' ); ?> <i data-lucide="chevron-right"></i></a><?php endif; ?>
                            </div>
                        </div>
                    <?php else :
                        $episodes = get_posts( array(
                            'post_parent'    => $post_id,
                            'post_type'      => 'entertainment',
                            'posts_per_page' => -1,
                            'meta_key'       => '_ziaoba_episode_number',
                            'orderby'        => 'meta_value_num',
                            'order'          => 'ASC',
                        ) );
                        if ( $episodes ) :
                            $seasons = array_unique( array_filter( array_map( static function( $episode ) {
                                return get_post_meta( $episode->ID, '_ziaoba_season', true );
                            }, $episodes ) ) );
                            sort( $seasons );
                            ?>
                            <section class="episodes-section">
                                <div class="episodes-header">
                                    <h2 class="section-title"><?php esc_html_e( 'Episodes', 'ziaoba-stream' ); ?></h2>
                                    <?php if ( $seasons ) : ?>
                                        <select id="seasonSelector" class="season-select">
                                            <option value="all"><?php esc_html_e( 'All Seasons', 'ziaoba-stream' ); ?></option>
                                            <?php foreach ( $seasons as $season ) : ?>
                                                <option value="<?php echo esc_attr( $season ); ?>"><?php echo esc_html( sprintf( __( 'Season %s', 'ziaoba-stream' ), $season ) ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                </div>
                                <div class="episodes-grid" id="episodesGrid">
                                    <?php foreach ( $episodes as $episode ) : ?>
                                        <?php $ep_num = get_post_meta( $episode->ID, '_ziaoba_episode_number', true ); $ep_season = get_post_meta( $episode->ID, '_ziaoba_season', true ); ?>
                                        <article class="episode-card poster-slide" data-season="<?php echo esc_attr( $ep_season ); ?>" onclick="location.href='<?php echo esc_url( get_permalink( $episode->ID ) ); ?>'">
                                            <div class="card-img-wrapper poster-card-img-wrapper">
                                                <?php echo wp_kses_post( ziaoba_get_display_poster( $episode->ID, 'medium_large' ) ); ?>
                                                <div class="card-overlay"><i data-lucide="play-circle"></i></div>
                                                <?php if ( $ep_num ) : ?><div class="ep-badge">E<?php echo esc_html( $ep_num ); ?></div><?php endif; ?>
                                            </div>
                                            <div class="card-info">
                                                <h3 class="card-title"><?php echo esc_html( get_the_title( $episode->ID ) ); ?></h3>
                                                <div class="card-meta wrap-meta">
                                                    <?php if ( $ep_season ) : ?><span><?php echo esc_html( 'S' . $ep_season ); ?></span><?php endif; ?>
                                                    <?php foreach ( ziaoba_get_content_meta( $episode->ID ) as $item ) : ?><span><?php echo esc_html( $item ); ?></span><?php endforeach; ?>
                                                </div>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endif; endif; ?>
                </div>

                <aside class="sidebar">
                    <div class="sidebar-box">
                        <h3 style="margin-bottom: 20px;"><?php esc_html_e( 'Related Content', 'ziaoba-stream' ); ?></h3>
                        <div class="related-list">
                            <?php foreach ( $related_posts as $related ) : ?>
                                <article class="related-card" onclick="location.href='<?php echo esc_url( get_permalink( $related->ID ) ); ?>'">
                                    <div class="related-thumb"><?php echo wp_kses_post( ziaoba_get_display_poster( $related->ID, 'medium' ) ); ?></div>
                                    <div>
                                        <h4><?php echo esc_html( $related->post_title ); ?></h4>
                                        <p><?php echo esc_html( implode( ' • ', ziaoba_get_content_meta( $related->ID ) ) ); ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            </div>
        <?php endwhile; endif; ?>
    </div>
</main>
<?php get_footer(); ?>
