<?php
/**
 * single-series.php - Series overview page.
 */

get_header(); ?>

<main class="single-series-wrap">
    <div class="container">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div class="series-hero">
                <div class="hero-bg">
                    <img src="<?php echo esc_url( ziaoba_get_backdrop( get_the_ID() ) ); ?>" class="hero-img" alt="<?php the_title(); ?>">
                    <div class="hero-overlay"></div>
                </div>
                
                <div class="hero-content">
                    <h1 class="series-title"><?php the_title(); ?></h1>
                    <div class="hero-meta">
                        <span class="match-score">98% Match</span>
                        <span class="age-rating"><?php echo esc_html( ziaoba_get_content_meta( get_the_ID(), 'age_rating' ) ); ?></span>
                        <span><?php echo get_the_term_list( get_the_ID(), 'genre', '', ', ' ); ?></span>
                    </div>
                    <div class="series-description">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <div class="episodes-section">
                <?php
                $seasons = get_posts( array(
                    'post_parent'    => get_the_ID(),
                    'post_type'      => 'season',
                    'posts_per_page' => -1,
                    'meta_key'       => '_ziaoba_season_number',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'ASC',
                ) );

                if ( $seasons ) : 
                ?>
                    <div class="episodes-header">
                        <h2 class="section-title">Seasons & Episodes</h2>
                    </div>

                    <div class="seasons-accordion">
                        <?php foreach ( $seasons as $index => $season ) : 
                            $num = ziaoba_get_content_meta( $season->ID, 'season_number' );
                            $episodes = get_posts( array(
                                'post_parent'    => $season->ID,
                                'post_type'      => 'episode',
                                'posts_per_page' => -1,
                                'meta_key'       => '_ziaoba_episode_number',
                                'orderby'        => 'meta_value_num',
                                'order'          => 'ASC',
                            ) );
                        ?>
                            <div class="season-block <?php echo $index === 0 ? 'active' : ''; ?>" data-season="<?php echo esc_attr( $num ); ?>">
                                <div class="season-toggle">
                                    <div class="season-info">
                                        <span class="season-name">Season <?php echo esc_html( $num ); ?></span>
                                        <span class="episode-count"><?php echo count( $episodes ); ?> Episodes</span>
                                    </div>
                                    <i data-lucide="chevron-down" class="toggle-icon"></i>
                                </div>
                                
                                <div class="season-episodes" style="<?php echo $index === 0 ? 'display: block;' : 'display: none;'; ?>">
                                    <div class="episodes-grid">
                                        <?php foreach ( $episodes as $episode ) : 
                                            $ep_num = ziaoba_get_content_meta( $episode->ID, 'episode_number' );
                                            $ep_duration = ziaoba_get_content_meta( $episode->ID, 'duration' );
                                        ?>
                                            <div class="episode-card" onclick="location.href='<?php echo get_permalink($episode->ID); ?>'">
                                                <div class="card-img-wrapper episode-card-img-wrapper">
                                                    <?php echo wp_kses_post( ziaoba_get_display_poster( $episode->ID, 'medium_large' ) ); ?>
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
                                                        <span>S<?php echo esc_html( $num ); ?> E<?php echo esc_html( $ep_num ); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p>No seasons found for this series.</p>
                <?php endif; ?>
            </div>

        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
