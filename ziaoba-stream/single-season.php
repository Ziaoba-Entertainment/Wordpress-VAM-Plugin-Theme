<?php
/**
 * single-season.php - Season overview page.
 */

get_header(); ?>

<main class="single-season-wrap">
    <div class="container">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
            
            <div class="season-hero">
                <div class="hero-bg">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'full', array( 'class' => 'hero-img' ) ); ?>
                    <?php else : ?>
                        <img src="https://picsum.photos/seed/season-<?php the_ID(); ?>/1920/1080" class="hero-img">
                    <?php endif; ?>
                    <div class="hero-overlay"></div>
                </div>
                
                <div class="hero-content">
                    <h1 class="season-title"><?php the_title(); ?></h1>
                    <div class="hero-meta">
                        <span class="match-score">98% Match</span>
                        <span class="age-rating"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_age_rating', true ) ); ?></span>
                        <span><?php echo get_the_term_list( get_the_ID(), 'genre', '', ', ' ); ?></span>
                    </div>
                    <div class="season-description">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>

            <div class="episodes-section">
                <h2 class="section-title">Episodes</h2>
                <?php
                $episodes = get_posts( array(
                    'post_parent'    => get_the_ID(),
                    'post_type'      => 'episode',
                    'posts_per_page' => -1,
                    'meta_key'       => '_ziaoba_episode_number',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'ASC',
                ) );

                if ( $episodes ) : ?>
                    <div class="episodes-grid">
                        <?php foreach ( $episodes as $episode ) : 
                            $ep_num = get_post_meta( $episode->ID, '_ziaoba_episode_number', true );
                            $ep_duration = get_post_meta( $episode->ID, '_ziaoba_duration', true );
                        ?>
                            <div class="episode-card" onclick="location.href='<?php echo get_permalink($episode->ID); ?>'">
                                <div class="card-img-wrapper">
                                    <?php if ( has_post_thumbnail( $episode->ID ) ) : ?>
                                        <?php echo get_the_post_thumbnail( $episode->ID, 'medium_large', array( 'class' => 'card-img' ) ); ?>
                                    <?php else : ?>
                                        <img src="https://picsum.photos/seed/ep-<?php echo $episode->ID; ?>/400/225" class="card-img">
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
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p>No episodes found for this season.</p>
                <?php endif; ?>
            </div>

        <?php endwhile; endif; ?>
    </div>
</main>

<?php get_footer(); ?>
