<?php
/**
 * single-education.php - Education post layout.
 * ziaoba-stream/single-education.php
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
<main class="container single-player-wrap">
    <div class="player-container" style="margin-bottom: 40px;">
        <?php if ( is_user_logged_in() ) : ?>
            <?php echo do_shortcode( '[ziaoba_player id="' . get_the_ID() . '"]' ); ?>
        <?php else : ?>
            <div class="login-required-box" style="background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>'); background-size: cover; background-position: center; border: 1px solid #222; padding: 100px 40px; text-align: center; border-radius: 8px; position: relative; overflow: hidden;">
                <div style="position: relative; z-index: 2;">
                    <h2 style="margin-bottom: 15px; font-size: 32px; font-weight: 900; letter-spacing: -0.5px;"><?php _e( 'Login Required', 'ziaoba-stream' ); ?></h2>
                    <p style="color: #ccc; margin-bottom: 35px; font-size: 17px; max-width: 550px; margin-left: auto; margin-right: auto; line-height: 1.5;"><?php _e( 'Sign in or register to access our practical education shorts and build your hustle. Connect stories to skills.', 'ziaoba-stream' ); ?></p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-bottom: 25px;">
                        <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('login') : wp_login_url( get_permalink() ); ?>" class="btn btn-primary" style="background: var(--hover-color); padding: 14px 35px; min-width: 160px;">
                            <?php _e( 'Login', 'ziaoba-stream' ); ?>
                        </a>
                        <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('register') : wp_registration_url(); ?>" class="btn" style="background: #fff; color: #000; padding: 14px 35px; min-width: 160px;">
                            <?php _e( 'Register', 'ziaoba-stream' ); ?>
                        </a>
                    </div>
                    <!-- Google Auth via Site Kit -->
                    <div class="google-auth-wrap" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 25px;">
                        <p style="font-size: 12px; color: #666; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px;"><?php _e( 'Or Continue With Google', 'ziaoba-stream' ); ?></p>
                        <?php do_action('ziaoba_google_auth_button'); ?>
                    </div>
                </div>
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px);"></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="single-grid">
        <div class="single-main">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <span style="background: var(--hover-color); color: #fff; padding: 3px 10px; font-size: 12px; font-weight: 800; border-radius: 2px;"><?php _e( 'EDUCATION', 'ziaoba-stream' ); ?></span>
                <span style="color: var(--muted-text); font-size: 14px; font-weight: 600;"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_lesson_topic', true ) ); ?></span>
            </div>
            <h1 class="single-title"><?php the_title(); ?></h1>
            
            <div class="single-content">
                <?php the_content(); ?>
            </div>
        </div>

        <aside class="single-sidebar">
            <?php 
            $related_ent_id = get_post_meta( get_the_ID(), '_ziaoba_related_content', true );
            if ( $related_ent_id ) :
                $ent_post = get_post( $related_ent_id );
            ?>
            <div class="sidebar-box" style="border-left: 4px solid var(--primary-color);">
                <p class="edu-hook-title" style="color: var(--primary-color);"><?php _e( 'Back to the Story', 'ziaoba-stream' ); ?></p>
                <h3 style="font-size: 20px; margin-bottom: 15px;"><?php echo esc_html( $ent_post->post_title ); ?></h3>
                <div style="position: relative; margin-bottom: 20px; border-radius: 4px; overflow: hidden;">
                    <img src="<?php echo get_the_post_thumbnail_url($ent_post->ID, 'medium'); ?>" style="width: 100%;">
                    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="play" style="color: #fff; width: 40px; height: 40px;"></i>
                    </div>
                </div>
                <a href="<?php echo get_permalink( $ent_post->ID ); ?>" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <?php _e( 'Resume Watching', 'ziaoba-stream' ); ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="sidebar-box">
                <h3 style="font-size: 16px; margin-bottom: 15px;"><?php _e( 'Lesson Details', 'ziaoba-stream' ); ?></h3>
                <div style="font-size: 14px; display: grid; gap: 10px;">
                    <p><span style="color: #666;"><?php _e( 'Topic:', 'ziaoba-stream' ); ?></span> <?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_lesson_topic', true ) ); ?></p>
                    <p><span style="color: #666;"><?php _e( 'Level:', 'ziaoba-stream' ); ?></span> Practical / Beginner</p>
                    <p><span style="color: #666;"><?php _e( 'Views:', 'ziaoba-stream' ); ?></span> <?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_views', true ) ?: '0' ); ?></p>
                </div>
            </div>
        </aside>
    </div>
</main>
<?php endwhile; ?>

<?php get_footer(); ?>
