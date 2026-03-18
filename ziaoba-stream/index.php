<?php
/**
 * index.php - Fallback template.
 */

get_header(); ?>

<main class="container" style="padding-top: 120px; min-height: 60vh;">
    <?php if ( have_posts() ) : ?>
        <h1 class="section-title"><?php _e( 'Latest Content', 'ziaoba-stream' ); ?></h1>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
            <?php while ( have_posts() ) : the_post(); ?>
                <article class="swiper-slide" onclick="location.href='<?php the_permalink(); ?>'">
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
                </article>
            <?php endwhile; ?>
        </div>
        <div class="pagination" style="margin-top: 40px;">
            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <p><?php _e( 'No content found.', 'ziaoba-stream' ); ?></p>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
