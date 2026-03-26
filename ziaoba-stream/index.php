<?php
/**
 * index.php - Fallback template.
 */

get_header(); ?>

<div class="container" style="padding-top: 120px; min-height: 60vh;">
    <?php if ( have_posts() ) : ?>
        <h1 class="section-title"><?php echo is_search() ? 'Search Results' : 'Latest Content'; ?></h1>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
            <?php while ( have_posts() ) : the_post(); ?>
                <div class="swiper-slide poster-card" onclick="location.href='<?php the_permalink(); ?>'">
                    <div class="card-img-wrapper">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <img src="<?php the_post_thumbnail_url( 'medium_large' ); ?>" class="card-img">
                        <?php else : ?>
                            <img src="https://picsum.photos/seed/<?php echo get_the_ID(); ?>/400/225" class="card-img">
                        <?php endif; ?>
                        <div class="card-overlay">
                            <i data-lucide="play-circle"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        <h3 class="card-title"><?php the_title(); ?></h3>
                        <div class="card-meta">
                            <span><?php echo get_post_type() === 'entertainment' ? 'Movie' : 'Lesson'; ?></span>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="pagination" style="margin-top: 40px;">
            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <div style="text-align: center; padding: 100px 0;">
            <h2>No content found.</h2>
            <p>Try searching for something else or browse our categories.</p>
            <a href="<?php echo home_url(); ?>" class="btn btn-primary" style="margin-top: 20px;">Back to Home</a>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
