<?php
/**
 * front-page.php - Homepage layout.
 */

get_header(); ?>

<?php 
$hero = ziaoba_get_hero_post();
if ( $hero ) : 
    $hero_img = get_the_post_thumbnail_url( $hero->ID, 'full' );
    $duration = get_post_meta( $hero->ID, '_ziaoba_duration', true );
    $rating = get_post_meta( $hero->ID, '_ziaoba_age_rating', true );
?>
<section class="hero" style="background-image: url('<?php echo esc_url( $hero_img ); ?>');">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title"><?php echo esc_html( $hero->post_title ); ?></h1>
            <div class="hero-meta">
                <span style="color: var(--hover-color);">98% Match</span>
                <span><?php echo esc_html( $duration ); ?></span>
                <span style="border: 1px solid #666; padding: 0 6px; border-radius: 2px;"><?php echo esc_html( $rating ); ?></span>
                <span><?php echo get_the_term_list( $hero->ID, 'genre', '', ', ' ); ?></span>
            </div>
            <p class="hero-desc"><?php echo esc_html( wp_trim_words( $hero->post_excerpt ?: $hero->post_content, 30 ) ); ?></p>
            <div style="display: flex; gap: 15px;">
                <a href="<?php echo get_permalink( $hero->ID ); ?>" class="btn btn-primary">
                    <i data-lucide="play"></i> Watch Now
                </a>
                <a href="#" class="btn" style="background: rgba(109, 109, 110, 0.7); color: #fff;">
                    <i data-lucide="info"></i> More Info
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<main class="container">
    <!-- Trending Now -->
    <section>
        <h2 class="section-title">Trending Now</h2>
        <div class="swiper trending-swiper">
            <div class="swiper-wrapper">
                <?php
                $trending = new WP_Query( array(
                    'post_type'      => 'entertainment',
                    'posts_per_page' => 12,
                    'meta_key'       => '_ziaoba_views',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC'
                ) );
                while ( $trending->have_posts() ) : $trending->the_post();
                ?>
                <div class="swiper-slide" onclick="location.href='<?php the_permalink(); ?>'">
                    <div class="card-img-wrapper">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium_large', array( 'class' => 'card-img' ) ); ?>
                        <?php else : ?>
                            <img src="https://picsum.photos/seed/<?php the_ID(); ?>/800/450" class="card-img" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="card-overlay"><i data-lucide="play-circle"></i></div>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title"><?php the_title(); ?></h3>
                        <div class="card-meta">
                            <span><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_duration', true ) ); ?></span>
                            <span><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_age_rating', true ) ); ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>

    <!-- Education Shorts -->
    <section>
        <h2 class="section-title">Education Shorts</h2>
        <div class="swiper edu-swiper">
            <div class="swiper-wrapper">
                <?php
                $edu = new WP_Query( array(
                    'post_type'      => 'education',
                    'posts_per_page' => 12
                ) );
                while ( $edu->have_posts() ) : $edu->the_post();
                ?>
                <div class="swiper-slide" onclick="location.href='<?php the_permalink(); ?>'">
                    <div class="card-img-wrapper">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium_large', array( 'class' => 'card-img' ) ); ?>
                        <?php else : ?>
                            <img src="https://picsum.photos/seed/edu-<?php the_ID(); ?>/800/450" class="card-img" alt="<?php the_title(); ?>">
                        <?php endif; ?>
                        <div class="card-overlay"><i data-lucide="book-open"></i></div>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title"><?php the_title(); ?></h3>
                        <p style="font-size: 11px; color: var(--muted-text);"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_lesson_topic', true ) ); ?></p>
                    </div>
                </div>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
