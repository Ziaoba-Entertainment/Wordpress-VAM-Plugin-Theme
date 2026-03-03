<?php
/**
 * single-entertainment.php - Entertainment post layout.
 * ziaoba-stream/single-entertainment.php
 */

get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>
<main class="container single-player-wrap">
    <div class="single-grid">
        <div class="single-main">
            <!-- Player Container -->
            <div class="player-container" style="margin-bottom: 40px;">
                <?php if ( is_user_logged_in() ) : ?>
                    <?php echo do_shortcode( '[ziaoba_player id="' . get_the_ID() . '"]' ); ?>
                <?php else : ?>
                    <div class="login-required-box" style="background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.85)), url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>'); background-size: cover; background-position: center; border: 1px solid #222; padding: 100px 40px; text-align: center; border-radius: 8px; position: relative; overflow: hidden;">
                        <div style="position: relative; z-index: 2;">
                            <h2 style="margin-bottom: 15px; font-size: 32px; font-weight: 900; letter-spacing: -0.5px;"><?php _e( 'Login Required', 'ziaoba-stream' ); ?></h2>
                            <p style="color: #ccc; margin-bottom: 35px; font-size: 17px; max-width: 550px; margin-left: auto; margin-right: auto; line-height: 1.5;"><?php _e( 'Sign in or register to watch this content. Join Ziaoba Entertainment to stream full African stories and build your future.', 'ziaoba-stream' ); ?></p>
                            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; margin-bottom: 25px;">
                                <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('login') : wp_login_url( get_permalink() ); ?>" class="btn btn-primary" style="padding: 14px 35px; min-width: 160px;">
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

            <h1 class="single-title"><?php the_title(); ?></h1>
            <div class="single-meta">
                <span style="color: var(--hover-color);">98% Match</span>
                <span><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_duration', true ) ); ?></span>
                <span style="border: 1px solid #666; padding: 0 8px; border-radius: 2px;"><?php echo esc_html( get_post_meta( get_the_ID(), '_ziaoba_age_rating', true ) ); ?></span>
                <span><?php echo get_the_term_list( get_the_ID(), 'genre', '', ', ' ); ?></span>
            </div>
            
            <div class="single-content">
                <?php the_content(); ?>
            </div>

            <!-- Episodes if Series -->
            <?php
            $current_id = get_the_ID();
            $parent_id  = wp_get_post_parent_id( $current_id );
            
            // If this is a child (episode), the "series" is the parent.
            // If this has children, this IS the series.
            $series_id = $parent_id ? $parent_id : $current_id;
            
            $episodes = get_children( array(
                'post_parent' => $series_id,
                'post_type'   => 'entertainment',
                'orderby'     => 'menu_order',
                'order'       => 'ASC',
                'post_status' => 'publish'
            ) );

            if ( $episodes ) :
            ?>
            <div style="margin-top: 50px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 class="section-title" style="margin: 0;"><?php _e( 'Episodes', 'ziaoba-stream' ); ?></h2>
                    <?php if ( $parent_id ) : ?>
                        <a href="<?php echo get_permalink( $parent_id ); ?>" style="font-size: 14px; color: var(--hover-color); font-weight: 600;">
                            <i data-lucide="chevron-left" style="width: 16px; height: 16px; vertical-align: middle;"></i> <?php _e( 'Back to Series', 'ziaoba-stream' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div style="display: grid; gap: 15px;">
                    <?php foreach ( $episodes as $ep ) : 
                        $is_active = ( $ep->ID == $current_id );
                        $ep_number = get_post_meta( $ep->ID, '_ziaoba_episode_number', true );
                        $ep_duration = get_post_meta( $ep->ID, '_ziaoba_duration', true );
                    ?>
                    <div style="display: flex; gap: 20px; background: <?php echo $is_active ? '#222' : '#141414'; ?>; padding: 15px; border-radius: 4px; align-items: center; cursor: pointer; border: 1px solid <?php echo $is_active ? 'var(--hover-color)' : 'transparent'; ?>;" onclick="location.href='<?php echo get_permalink($ep->ID); ?>'">
                        <div style="font-size: 24px; font-weight: 800; color: #444; width: 30px; text-align: center;">
                            <?php echo $ep_number ? $ep_number : '—'; ?>
                        </div>
                        <div style="width: 160px; aspect-ratio: 16/9; position: relative; flex-shrink: 0;">
                            <?php echo get_the_post_thumbnail($ep->ID, 'medium', array('style'=>'width:100%; height:100%; object-fit:cover; border-radius:2px;')); ?>
                            <?php if ( $ep_duration ) : ?>
                                <div style="position: absolute; bottom: 5px; right: 5px; background: rgba(0,0,0,0.8); font-size: 10px; padding: 2px 4px; border-radius: 2px;"><?php echo esc_html( $ep_duration ); ?></div>
                            <?php endif; ?>
                            <?php if ( $is_active ) : ?>
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,200,83,0.3); display: flex; align-items: center; justify-content: center;">
                                    <i data-lucide="play" style="fill: #fff; color: #fff;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="flex-grow: 1;">
                            <h4 style="margin-bottom: 5px; color: <?php echo $is_active ? 'var(--hover-color)' : '#fff'; ?>;">
                                <?php echo esc_html($ep->post_title); ?>
                            </h4>
                            <p style="font-size: 13px; color: var(--muted-text); line-height: 1.4;">
                                <?php echo esc_html( wp_trim_words( $ep->post_excerpt ?: $ep->post_content, 20 ) ); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <aside class="single-sidebar">
            <?php 
            $related_edu_id = get_post_meta( get_the_ID(), '_ziaoba_related_content', true );
            if ( $related_edu_id ) :
                $edu_post = get_post( $related_edu_id );
            ?>
            <div class="sidebar-box" style="border-left: 4px solid var(--hover-color);">
                <p class="edu-hook-title"><?php _e( 'Build Your Skills', 'ziaoba-stream' ); ?></p>
                <h3 style="font-size: 20px; margin-bottom: 12px;"><?php echo esc_html( $edu_post->post_title ); ?></h3>
                <p style="font-size: 14px; color: var(--muted-text); margin-bottom: 20px;"><?php echo esc_html( wp_trim_words( $edu_post->post_content, 22 ) ); ?></p>
                <a href="<?php echo get_permalink( $edu_post->ID ); ?>" class="btn" style="background: var(--hover-color); color: #fff; width: 100%; justify-content: center;">
                    <i data-lucide="book-open"></i> <?php _e( 'Start Learning', 'ziaoba-stream' ); ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="sidebar-box">
                <h3 style="font-size: 16px; margin-bottom: 15px;"><?php _e( 'About this Story', 'ziaoba-stream' ); ?></h3>
                <div style="font-size: 14px; display: grid; gap: 10px;">
                    <p><span style="color: #666;"><?php _e( 'Cast:', 'ziaoba-stream' ); ?></span> Local Talent</p>
                    <p><span style="color: #666;"><?php _e( 'Director:', 'ziaoba-stream' ); ?></span> Ziaoba Team</p>
                    <p><span style="color: #666;"><?php _e( 'Genres:', 'ziaoba-stream' ); ?></span> <?php echo get_the_term_list( get_the_ID(), 'genre', '', ', ' ); ?></p>
                </div>
            </div>
        </aside>
    </div>
</main>
<?php endwhile; ?>

<?php get_footer(); ?>
