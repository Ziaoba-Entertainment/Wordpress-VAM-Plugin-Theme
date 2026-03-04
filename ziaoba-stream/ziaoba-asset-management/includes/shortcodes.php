<?php
/**
 * Player Shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'ziaoba_player', 'ziaoba_player_shortcode' );

function ziaoba_player_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'id' => get_the_ID(),
    ), $atts, 'ziaoba_player' );

    $post_id = $atts['id'];
    $video_url = get_post_meta( $post_id, '_ziaoba_video_url', true );
    $poster_url = get_the_post_thumbnail_url( $post_id, 'full' );

    if ( ! $video_url ) {
        return '<div class="ziaoba-error">' . __( 'Video URL not found.', 'ziaoba' ) . '</div>';
    }

    // Restriction check
    if ( ! is_user_logged_in() ) {
        // This is a fallback, usually handled by the theme template
        return '';
    }

    ob_start();
    ?>
    <div class="ziaoba-player-wrapper">
        <video id="ziaoba-video-<?php echo $post_id; ?>" 
               class="video-js vjs-big-play-centered vjs-fluid vjs-16-9" 
               controls 
               preload="auto" 
               poster="<?php echo esc_url( $poster_url ); ?>"
               data-setup='{"fluid": true, "aspectRatio": "16:9"}'>
            <source src="<?php echo esc_url( $video_url ); ?>" type="application/x-mpegURL">
            <p class="vjs-no-js">
                To view this video please enable JavaScript, and consider upgrading to a web browser that
                <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
            </p>
        </video>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof videojs === 'function') {
                    var player = videojs('ziaoba-video-<?php echo $post_id; ?>');
                    player.on('play', function() {
                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.send('action=ziaoba_track_view&post_id=<?php echo $post_id; ?>');
                    });
                }
            });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
