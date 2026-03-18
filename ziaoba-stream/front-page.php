<?php
/**
 * front-page.php - Homepage layout.
 */

get_header();

$hero = ziaoba_get_hero_post();
if ( $hero ) :
    $hero_img  = get_post_meta( $hero->ID, '_ziaoba_backdrop_url', true ) ?: get_the_post_thumbnail_url( $hero->ID, 'full' );
    $hero_meta = ziaoba_get_content_meta( $hero->ID );
    ?>
    <section class="hero" style="background-image: url('<?php echo esc_url( $hero_img ); ?>');">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><?php echo esc_html( $hero->post_title ); ?></h1>
                <div class="hero-meta">
                    <?php foreach ( $hero_meta as $item ) : ?>
                        <span><?php echo esc_html( $item ); ?></span>
                    <?php endforeach; ?>
                    <span><?php echo wp_kses_post( get_the_term_list( $hero->ID, 'genre', '', ', ' ) ); ?></span>
                </div>
                <?php if ( get_post_meta( $hero->ID, '_ziaoba_tagline', true ) ) : ?>
                    <p class="hero-tagline"><?php echo esc_html( get_post_meta( $hero->ID, '_ziaoba_tagline', true ) ); ?></p>
                <?php endif; ?>
                <p class="hero-desc"><?php echo esc_html( wp_trim_words( $hero->post_excerpt ?: $hero->post_content, 30 ) ); ?></p>
                <div class="hero-actions">
                    <a href="<?php echo esc_url( get_permalink( $hero->ID ) ); ?>" class="btn btn-primary">
                        <i data-lucide="play"></i> <?php esc_html_e( 'Watch Now', 'ziaoba-stream' ); ?>
                    </a>
                    <?php if ( get_post_meta( $hero->ID, '_ziaoba_trailer_url', true ) ) : ?>
                        <a href="<?php echo esc_url( get_post_meta( $hero->ID, '_ziaoba_trailer_url', true ) ); ?>" class="btn btn-secondary-glass" target="_blank" rel="noopener noreferrer">
                            <i data-lucide="clapperboard"></i> <?php esc_html_e( 'Watch Trailer', 'ziaoba-stream' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<main class="container homepage-main">
    <section>
        <div class="section-heading-row">
            <h2 class="section-title"><?php esc_html_e( 'Trending Now', 'ziaoba-stream' ); ?></h2>
            <p class="section-subtitle"><?php esc_html_e( 'Popular titles with cinema-grade poster proportions.', 'ziaoba-stream' ); ?></p>
        </div>
        <div class="swiper trending-swiper poster-swiper">
            <div class="swiper-wrapper">
                <?php
                $trending = new WP_Query( array(
                    'post_type'      => 'entertainment',
                    'posts_per_page' => 12,
                    'meta_key'       => '_ziaoba_views',
                    'orderby'        => 'meta_value_num',
                    'order'          => 'DESC',
                ) );
                while ( $trending->have_posts() ) :
                    $trending->the_post();
                    ?>
                    <article class="swiper-slide poster-slide" onclick="location.href='<?php the_permalink(); ?>'">
                        <div class="card-img-wrapper poster-card-img-wrapper">
                            <?php echo wp_kses_post( ziaoba_get_display_poster( get_the_ID(), 'large' ) ); ?>
                            <div class="card-overlay"><i data-lucide="play-circle"></i></div>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title"><?php the_title(); ?></h3>
                            <div class="card-meta wrap-meta">
                                <?php foreach ( ziaoba_get_content_meta( get_the_ID() ) as $item ) : ?>
                                    <span><?php echo esc_html( $item ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>

    <section>
        <div class="section-heading-row">
            <h2 class="section-title"><?php esc_html_e( 'Education Shorts', 'ziaoba-stream' ); ?></h2>
            <p class="section-subtitle"><?php esc_html_e( 'Short-form learning content that stays easy to scan on every device.', 'ziaoba-stream' ); ?></p>
        </div>
        <div class="swiper edu-swiper poster-swiper">
            <div class="swiper-wrapper">
                <?php
                $edu = new WP_Query( array(
                    'post_type'      => 'education',
                    'posts_per_page' => 12,
                ) );
                while ( $edu->have_posts() ) :
                    $edu->the_post();
                    ?>
                    <article class="swiper-slide poster-slide" onclick="location.href='<?php the_permalink(); ?>'">
                        <div class="card-img-wrapper poster-card-img-wrapper">
                            <?php echo wp_kses_post( ziaoba_get_display_poster( get_the_ID(), 'large' ) ); ?>
                            <div class="card-overlay"><i data-lucide="book-open"></i></div>
                        </div>
                        <div class="card-info">
                            <h3 class="card-title"><?php the_title(); ?></h3>
                            <p class="card-kicker"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_lesson_topic', true ) ?: __( 'Education Short', 'ziaoba-stream' ) ); ?></p>
                            <div class="card-meta wrap-meta">
                                <?php foreach ( ziaoba_get_content_meta( get_the_ID() ) as $item ) : ?>
                                    <span><?php echo esc_html( $item ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
