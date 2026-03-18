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

    $post_id   = (int) $atts['id'];
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

    $stream_url = ziaoba_get_stream_proxy_url( $post_id, $video_url );

    $player_config = array(
        'postId'          => $post_id,
        'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
        'trackNonce'      => wp_create_nonce( 'ziaoba_player_nonce' ),
        'progressNonce'   => wp_create_nonce( 'ziaoba_progress_nonce_' . $post_id ),
        'streamUrl'       => $stream_url,
        'resumeThreshold' => 15,
        'rewatchThreshold'=> 8,
        'seekGrace'       => 5,
        'i18n'            => array(
            'resume' => __( 'Resume from where you left off?', 'ziaoba' ),
        ),
    );

    ob_start();
    ?>
    <div class="ziaoba-player-wrapper" data-player-config="<?php echo esc_attr( wp_json_encode( $player_config ) ); ?>">
        <video id="ziaoba-video-<?php echo esc_attr( $post_id ); ?>"
               class="video-js vjs-big-play-centered vjs-fluid vjs-16-9"
               controls
               preload="auto"
               poster="<?php echo esc_url( $poster_url ); ?>"
               data-setup='{"fluid": true, "aspectRatio": "16:9"}'>
            <source src="<?php echo esc_url( $stream_url ); ?>" type="application/x-mpegURL">
            <p class="vjs-no-js">
                <?php esc_html_e( 'To view this video please enable JavaScript, and consider upgrading to a web browser that supports HTML5 video.', 'ziaoba' ); ?>
            </p>
        </video>
    </div>
    <?php
    return ob_get_clean();
}
