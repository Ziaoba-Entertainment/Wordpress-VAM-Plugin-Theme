<?php
/**
 * Player Shortcode
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

    /**
     * Initialize Shortcodes
     */
    public static function init() {
        $instance = new self();
        add_shortcode( 'ziaoba_player', array( $instance, 'player_shortcode' ) );
        add_shortcode( 'ziaoba_continue_watching', array( $instance, 'continue_watching_shortcode' ) );
    }

    /**
     * Player Shortcode
     */
    public function player_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => get_the_ID(),
        ), $atts, 'ziaoba_player' );

        $post_id = $atts['id'];
        $video_url = get_post_meta( $post_id, '_ziaoba_video_url', true );
        
        // Use backdrop helper
        $poster_url = ziaoba_get_backdrop( $post_id );

        if ( ! $video_url ) {
            return '<div class="ziaoba-error">' . __( 'Video URL not found.', 'ziaoba-asset-management' ) . '</div>';
        }

        // Restriction check
        if ( ! is_user_logged_in() ) {
            ob_start();
            ?>
            <div class="ziaoba-watch-now-placeholder ziaoba-backdrop" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo esc_url( $poster_url ); ?>');">
                <div class="play-icon"></div>
                <div class="cta-text"><?php _e( 'Watch Now', 'ziaoba-asset-management' ); ?></div>
                <div class="sub-cta"><?php _e( 'Sign up for Free Premium Access', 'ziaoba-asset-management' ); ?></div>
            </div>
            <?php
            return ob_get_clean();
        }

        // Age Restriction Check
        if ( class_exists( 'Ziaoba\VAM\AgeRestriction' ) ) {
            if ( ! AgeRestriction::can_user_view( $post_id ) ) {
                $age_rating = get_post_meta( $post_id, '_age_rating', true );
                ob_start();
                ?>
                <div class="ziaoba-watch-now-placeholder ziaoba-backdrop restricted-content" style="background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('<?php echo esc_url( $poster_url ); ?>');">
                    <div class="lock-icon mb-4"><i data-lucide="lock" class="w-12 h-12 text-red-600"></i></div>
                    <div class="cta-text"><?php _e( 'Content Restricted', 'ziaoba-asset-management' ); ?></div>
                    <div class="sub-cta"><?php printf( __( 'This content is rated %s and is not available for your age group.', 'ziaoba-asset-management' ), esc_html( $age_rating ) ); ?></div>
                </div>
                <script>if(typeof lucide !== 'undefined') lucide.createIcons();</script>
                <?php
                return ob_get_clean();
            }
        }

        // Enqueue player assets
        if ( class_exists( 'Ziaoba\VAM\Player' ) ) {
            Player::enqueue_assets();
        }

        // Localize script for tracking
        wp_localize_script( 'videojs-js', 'ziaobaPlayer', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ziaoba_player_nonce' ),
        ) );

        // We use a custom escaping for the URL to preserve parentheses which are common in HLS paths
        // but often broken by esc_url() in some server configurations.
        $safe_video_url = str_replace( array( '%28', '%29' ), array( '(', ')' ), esc_url( $video_url ) );

        ob_start();
        ?>
        <div class="ziaoba-player-wrapper">
            <video id="ziaoba-video-<?php echo $post_id; ?>" 
                   class="video-js vjs-big-play-centered vjs-fluid vjs-16-9" 
                   controls 
                   preload="auto" 
                   poster="<?php echo esc_url( $poster_url ); ?>">
                <source src="<?php echo $safe_video_url; ?>" type="application/x-mpegURL">
                <p class="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a web browser that
                    <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof videojs === 'function') {
                        var player = videojs('ziaoba-video-<?php echo $post_id; ?>', {
                            html5: {
                                vhs: {
                                    overrideNative: true
                                },
                                nativeAudioTracks: false,
                                nativeVideoTracks: false
                            }
                        });
                        
                        player.one('play', function() {
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', ziaobaPlayer.ajaxUrl, true);
                            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                            xhr.send('action=ziaoba_track_view&post_id=<?php echo $post_id; ?>&nonce=' + ziaobaPlayer.nonce);
                        });

                        // Handle errors gracefully
                        player.on('error', function() {
                            var error = player.error();
                            console.error('VideoJS Error:', error.code, error.message);
                        });
                    }
                });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Continue Watching Shortcode
     */
    public function continue_watching_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $user_id = get_current_user_id();
        global $wpdb;
        $table_name = $wpdb->prefix . 'ziaoba_playback_progress';

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT post_id, timecode, duration FROM $table_name WHERE user_id = %d AND (timecode / duration) < 0.95 ORDER BY last_watched DESC LIMIT 10",
            $user_id
        ) );

        if ( ! $results ) {
            return '';
        }

        ob_start();
        ?>
        <div class="ziaoba-continue-watching">
            <div class="section-heading-row">
                <h2 class="section-title"><?php _e( 'Continue Watching', 'ziaoba-asset-management' ); ?></h2>
            </div>
            <div class="swiper continue-watching-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ( $results as $row ) : 
                        $post_id = $row->post_id;
                        $progress = ( $row->timecode / $row->duration ) * 100;
                        ?>
                        <div class="swiper-slide episode-card" onclick="window.location.href='<?php echo get_permalink( $post_id ); ?>'">
                            <div class="episode-card-img-wrapper">
                                <img src="<?php echo esc_url( ziaoba_get_backdrop( $post_id ) ); ?>" class="card-img loaded" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
                                <div class="playback-progress-bar">
                                    <div class="playback-progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                </div>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title"><?php echo get_the_title( $post_id ); ?></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                new Swiper('.continue-watching-swiper', {
                    slidesPerView: 1.2,
                    spaceBetween: 16,
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    breakpoints: {
                        640: { slidesPerView: 2.2 },
                        1024: { slidesPerView: 4.2 }
                    }
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }
}
