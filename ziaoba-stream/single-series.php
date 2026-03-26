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
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'full', array( 'class' => 'hero-img' ) ); ?>
                    <?php else : ?>
                        <img src="https://picsum.photos/seed/series-<?php the_ID(); ?>/1920/1080" class="hero-img">
                    <?php endif; ?>
                    <div class="hero-overlay"></div>
                </div>
                
                <div class="hero-content">
                    <h1 class="series-title"><?php the_title(); ?></h1>
                    <div class="hero-meta">
                        <span class="match-score">98% Match</span>
                        <span class="age-rating"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_age_rating', true ) ); ?></span>
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
                    $season_nums = array();
                    foreach ( $seasons as $s ) {
                        $num = get_post_meta( $s->ID, '_ziaoba_season_number', true );
                        if ( $num ) $season_nums[] = $num;
                    }
                    $latest_season = !empty( $season_nums ) ? max( $season_nums ) : 1;
                ?>
                    <div class="episodes-header">
                        <h2 class="section-title">Episodes</h2>
                        <div class="season-selector-wrap">
                            <select id="seasonSelector" class="season-select">
                                <option value="all">All Seasons</option>
                                <?php foreach ( $seasons as $season ) : 
                                    $num = get_post_meta( $season->ID, '_ziaoba_season_number', true );
                                ?>
                                    <option value="<?php echo esc_attr( $num ); ?>" <?php selected( $num, $latest_season ); ?>>
                                        Season <?php echo esc_html( $num ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="episodes-grid" id="episodesGrid">
                        <?php foreach ( $seasons as $season ) : 
                            $num = get_post_meta( $season->ID, '_ziaoba_season_number', true );
                            $episodes = get_posts( array(
                                'post_parent'    => $season->ID,
                                'post_type'      => 'episode',
                                'posts_per_page' => -1,
                                'meta_key'       => '_ziaoba_episode_number',
                                'orderby'        => 'meta_value_num',
                                'order'          => 'ASC',
                            ) );

                            foreach ( $episodes as $episode ) : 
                                $ep_num = get_post_meta( $episode->ID, '_ziaoba_episode_number', true );
                                $ep_duration = get_post_meta( $episode->ID, '_ziaoba_duration', true );
                        ?>
                                <div class="episode-card" data-season="<?php echo esc_attr( $num ); ?>" onclick="location.href='<?php echo get_permalink($episode->ID); ?>'">
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
                                            <span>S<?php echo esc_html( $num ); ?></span>
                                        </div>
                                    </div>
                                </div>
                        <?php endforeach; endforeach; ?>
                    </div>
                <?php else : ?>
                    <p>No seasons found for this series.</p>
                <?php endif; ?>
            </div>

        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
