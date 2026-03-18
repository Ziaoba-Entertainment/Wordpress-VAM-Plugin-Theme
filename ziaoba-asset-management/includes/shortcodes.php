<?php
/**
 * Player Shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'ziaoba_player', 'ziaoba_player_shortcode' );

function ziaoba_player_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'id' => get_the_ID(),
        ),
        $atts,
        'ziaoba_player'
    );

    $post_id   = absint( $atts['id'] );
    $video_url = get_post_meta( $post_id, '_ziaoba_video_url', true );

    if ( ! $video_url ) {
        return '<div class="ziaoba-error">' . esc_html__( 'Video URL not found.', 'ziaoba' ) . '</div>';
    }

    if ( ! is_user_logged_in() ) {
        return '';
    }

    $poster_url = get_post_meta( $post_id, '_ziaoba_poster_url', true );
    if ( ! $poster_url ) {
        $poster_url = get_the_post_thumbnail_url( $post_id, 'full' );
    }

    ob_start();
    ?>
    <div class="ziaoba-player-wrapper">
        <video id="ziaoba-video-<?php echo esc_attr( $post_id ); ?>"
               class="video-js vjs-big-play-centered vjs-fluid vjs-16-9"
               controls
               preload="auto"
               <?php if ( $poster_url ) : ?>poster="<?php echo esc_url( $poster_url ); ?>"<?php endif; ?>
               data-setup='{"fluid": true, "aspectRatio": "16:9"}'>
            <source src="<?php echo esc_url( $video_url ); ?>" type="application/x-mpegURL">
            <p class="vjs-no-js">
                <?php esc_html_e( 'To view this video please enable JavaScript, and consider upgrading to a web browser that', 'ziaoba' ); ?>
                <a href="https://videojs.com/html5-video-support/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'supports HTML5 video', 'ziaoba' ); ?></a>
            </p>
        </video>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof videojs === 'function') {
                    var player = videojs('ziaoba-video-<?php echo esc_js( $post_id ); ?>');
                    var viewTracked = false;
                    player.on('play', function() {
                        if (viewTracked) {
                            return;
                        }
                        viewTracked = true;
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('action=ziaoba_track_view&post_id=<?php echo esc_js( $post_id ); ?>');
                    });
                }
            });
        </script>
    </div>
    <?php

    return ob_get_clean();
}
