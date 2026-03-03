<?php
/**
 * Player Shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'ziaoba_player', 'ziaoba_player_shortcode' );

if ( ! function_exists( 'ziaoba_player_shortcode' ) ) {
    function ziaoba_player_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => get_the_ID(),
        ), $atts, 'ziaoba_player' );

        $post_id = $atts['id'];
        $video_url = get_post_meta( $post_id, '_ziaoba_video_url', true );

        if ( ! $video_url ) {
            return '<div class="ziaoba-error">' . __( 'Video URL not found.', 'ziaoba' ) . '</div>';
        }

        // Restriction check
        if ( ! is_user_logged_in() ) {
            return '<div class="ziaoba-restricted">
                <h3>' . __( 'Exclusive Content', 'ziaoba' ) . '</h3>
                <p>' . __( 'Please log in to watch this video.', 'ziaoba' ) . '</p>
                <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '" class="ziaoba-btn">' . __( 'Login / Register', 'ziaoba' ) . '</a>
            </div>';
        }

        // Monetization Settings
        $monetization = get_option( 'ziaoba_monetization_settings', array() );
        $flussonic_base = $monetization['flussonic_url'] ?? '';
        $flussonic_enable = $monetization['flussonic_enable'] ?? '0';
        $gam_vast = $monetization['gam_vast_url'] ?? '';
        $gam_enable = $monetization['gam_enable'] ?? '0';
        $sponsor_name = $monetization['sponsor_name'] ?? '';
        $sponsor_logo = $monetization['sponsor_logo'] ?? '';
        $sponsor_enable = $monetization['sponsor_enable'] ?? '0';

        // Construct SSAI URL if Flussonic is enabled
        $final_url = $video_url;
        if ( $flussonic_enable === '1' && ! empty( $flussonic_base ) ) {
            $final_url = rtrim( $flussonic_base, '/' ) . '/ssai/playlist.m3u8?url=' . urlencode( $video_url );
            if ( ! empty( $monetization['flussonic_token'] ) ) {
                $final_url .= '&token=' . urlencode( $monetization['flussonic_token'] );
            }
        }

        ob_start();
        ?>
        <div class="ziaoba-player-wrapper" style="width:100%; max-width:1000px; margin:0 auto; background:#000; border-radius:8px; overflow:hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.5); position: relative;">
            
            <?php if ( $sponsor_enable === '1' && ! empty( $sponsor_name ) ) : ?>
            <div class="ziaoba-sponsor-overlay" style="position: absolute; top: 15px; right: 15px; z-index: 10; pointer-events: none; display: flex; align-items: center; gap: 8px; background: rgba(0,0,0,0.6); padding: 5px 12px; border-radius: 20px; font-size: 11px; color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                <?php if ( ! empty( $sponsor_logo ) ) : ?>
                    <img src="<?php echo esc_url( $sponsor_logo ); ?>" alt="<?php echo esc_attr( $sponsor_name ); ?>" style="height: 14px; width: auto; filter: brightness(0) invert(1);">
                <?php endif; ?>
                <span><?php printf( __( 'Sponsored by %s', 'ziaoba' ), esc_html( $sponsor_name ) ); ?></span>
            </div>
            <?php endif; ?>

            <video id="ziaoba-video-<?php echo $post_id; ?>" 
                   class="video-js vjs-big-play-centered vjs-fluid vjs-16-9" 
                   controls 
                   preload="auto" 
                   data-setup='{"fluid": true, "aspectRatio": "16:9"}'>
                <source src="<?php echo esc_url( $final_url ); ?>" type="application/x-mpegURL">
                <p class="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a web browser that
                    <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var player = videojs('ziaoba-video-<?php echo $post_id; ?>');
                    
                    // Tracking
                    player.on('play', function() {
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'action=ziaoba_track_view&post_id=<?php echo $post_id; ?>'
                        });
                    });

                    // IMA / GAM Fallback
                    <?php if ( $gam_enable === '1' && ! empty( $gam_vast ) ) : ?>
                    console.log('Ziaoba: GAM VAST Fallback Ready');
                    // In a full implementation, you'd load the IMA SDK and videojs-ima plugin here.
                    <?php endif; ?>
                });
            </script>
        </div>
        <?php
        return ob_get_clean();
    }
}
