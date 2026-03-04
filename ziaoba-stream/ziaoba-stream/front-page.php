<?php
/**
 * front-page.php - Cinematic landing page.
 */

get_header(); ?>

<!-- Hero Section -->
<?php 
$hero = ziaoba_get_hero_post();
if ( $hero ) :
    $hero_img = get_the_post_thumbnail_url( $hero->ID, 'full' );
?>
    <section class="hero" style="background-image: url('<?php echo esc_url( $hero_img ); ?>');">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><?php echo esc_html( $hero->post_title ); ?></h1>
                <div class="hero-meta">
                    <span style="color: var(--hover-color);">New Arrival</span>
                    <span><?php echo esc_html( get_post_meta( $hero->ID, '_ziaoba_duration', true ) ); ?></span>
                    <span style="border: 1px solid #666; padding: 0 6px; border-radius: 2px;"><?php echo esc_html( get_post_meta( $hero->ID, '_ziaoba_age_rating', true ) ); ?></span>
                </div>
                <p class="hero-desc"><?php echo wp_trim_words( $hero->post_content, 25 ); ?></p>
                <div class="hero-actions">
                    <a href="<?php echo get_permalink( $hero->ID ); ?>" class="btn btn-primary">
                        <i data-lucide="play"></i> Watch Now
                    </a>
                    <a href="<?php echo get_permalink( $hero->ID ); ?>" class="btn btn-outline">
                        <i data-lucide="info"></i> More Info
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<main class="container">
    <!-- Trending Entertainment -->
    <section class="carousel-wrap">
        <h2 class="section-title">Trending Now</h2>
        <div class="swiper trending-swiper">
            <div class="swiper-wrapper">
                <?php
                $trending = new WP_Query( array(
                    'post_type' => 'entertainment',
                    'posts_per_page' => 12,
                    'orderby' => 'rand'
                ) );
                if ( $trending->have_posts() ) : while ( $trending->have_posts() ) : $trending->the_post();
                ?>
                    <div class="swiper-slide poster-card" onclick="location.href='<?php the_permalink(); ?>'">
                        <div class="card-img-wrapper">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium_large', array( 'class' => 'card-img', 'loading' => 'lazy' ) ); ?>
                            <?php else : ?>
                                <img src="https://picsum.photos/seed/<?php echo get_the_ID(); ?>/400/225" class="card-img" loading="lazy">
                            <?php endif; ?>
                            <div class="card-overlay">
                                <i data-lucide="play-circle"></i>
                            </div>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title"><?php the_title(); ?></h3>
                            <div class="card-meta">
                                <span><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_duration', true ) ); ?></span>
                                <span><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_age_rating', true ) ); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>

    <!-- Education Lessons -->
    <section class="carousel-wrap">
        <h2 class="section-title">Lessons for Life</h2>
        <div class="swiper edu-swiper">
            <div class="swiper-wrapper">
                <?php
                $edu = new WP_Query( array(
                    'post_type' => 'education',
                    'posts_per_page' => 12
                ) );
                if ( $edu->have_posts() ) : while ( $edu->have_posts() ) : $edu->the_post();
                ?>
                    <div class="swiper-slide poster-card" onclick="location.href='<?php the_permalink(); ?>'">
                        <div class="card-img-wrapper">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium_large', array( 'class' => 'card-img', 'loading' => 'lazy' ) ); ?>
                            <?php else : ?>
                                <img src="https://picsum.photos/seed/edu-<?php echo get_the_ID(); ?>/400/225" class="card-img" loading="lazy">
                            <?php endif; ?>
                            <div class="card-overlay">
                                <i data-lucide="book-open"></i>
                            </div>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title"><?php the_title(); ?></h3>
                            <div class="card-meta">
                                <span>Lesson</span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
