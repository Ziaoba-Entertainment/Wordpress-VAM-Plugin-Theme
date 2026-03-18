<?php
/**
 * single-education.php - Education player page.
 */

get_header();
$related_posts = have_posts() ? ziaoba_get_related_posts( get_queried_object_id(), 4 ) : array();
?>
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
                                <div class="login-required-content auth-enhanced-form" data-auth-mode="login-gate">
                                    <h2><?php esc_html_e( 'Login Required', 'ziaoba-stream' ); ?></h2>
                                    <p><?php esc_html_e( 'Join Ziaoba to access this lesson with email, Google, or your saved session.', 'ziaoba-stream' ); ?></p>
                                    <div class="login-required-actions">
                                        <a href="<?php echo esc_url( ziaoba_get_auth_url( 'login', get_permalink() ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Login to Learn', 'ziaoba-stream' ); ?></a>
                                        <a href="<?php echo esc_url( ziaoba_get_auth_url( 'register', get_permalink() ) ); ?>" class="btn btn-outline"><?php esc_html_e( 'Register', 'ziaoba-stream' ); ?></a>
                                    </div>
                                    <?php do_action( 'ziaoba_google_auth_button' ); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h1 class="single-title"><?php the_title(); ?></h1>
                    <div class="single-badge-row">
                        <span class="single-pill"><?php esc_html_e( 'Education', 'ziaoba-stream' ); ?></span>
                        <?php foreach ( ziaoba_get_content_meta( get_the_ID() ) as $item ) : ?><span class="single-pill single-pill-muted"><?php echo esc_html( $item ); ?></span><?php endforeach; ?>
                    </div>
                    <?php if ( get_post_meta( get_the_ID(), '_ziaoba_tagline', true ) ) : ?>
                        <p class="single-tagline"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_tagline', true ) ); ?></p>
                    <?php endif; ?>
                    <div class="content single-description">
                        <?php the_content(); ?>
                    </div>
                    <div class="metadata-grid">
                        <?php foreach ( array(
                            __( 'Topic', 'ziaoba-stream' ) => get_post_meta( get_the_ID(), '_ziaoba_lesson_topic', true ),
                            __( 'Original title', 'ziaoba-stream' ) => get_post_meta( get_the_ID(), '_ziaoba_original_title', true ),
                            __( 'Language', 'ziaoba-stream' ) => get_post_meta( get_the_ID(), '_ziaoba_original_language', true ),
                            __( 'TMDB score', 'ziaoba-stream' ) => get_post_meta( get_the_ID(), '_ziaoba_tmdb_vote_average', true ),
                        ) as $label => $value ) : ?>
                            <?php if ( $value ) : ?><div class="metadata-card"><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $value ); ?></div><?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <aside class="sidebar">
                    <div class="sidebar-box">
                        <h3 style="margin-bottom: 20px;"><?php esc_html_e( 'Related Content', 'ziaoba-stream' ); ?></h3>
                        <div class="related-list">
                            <?php foreach ( $related_posts as $related ) : ?>
                                <article class="related-card" onclick="location.href='<?php echo esc_url( get_permalink( $related->ID ) ); ?>'">
                                    <div class="related-thumb"><?php echo wp_kses_post( ziaoba_get_display_poster( $related->ID, 'medium' ) ); ?></div>
                                    <div>
                                        <h4><?php echo esc_html( get_the_title( $related->ID ) ); ?></h4>
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
