<?php
/**
 * Player Scripts and Styles
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Player {

    /**
     * Initialize Player
     */
    public static function init() {
        $instance = new self();
        add_action( 'wp_enqueue_scripts', array( $instance, 'enqueue_assets' ) );
    }

    /**
     * Enqueue Player Assets
     */
    public static function enqueue_assets( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }

        // Video.js
        wp_enqueue_style( 'videojs-css', 'https://vjs.zencdn.net/8.10.0/video-js.css', array(), '8.10.0' );
        wp_enqueue_script( 'videojs-js', 'https://vjs.zencdn.net/8.10.0/video.min.js', array(), '8.10.0', true );

        // Custom Player JS
        wp_enqueue_script( 'ziaoba-player-js', ZIAOBA_VAM_URL . 'js/player.js', array( 'videojs-js' ), ZIAOBA_VAM_VERSION, true );

        $saved_progress = 0;
        if ( is_user_logged_in() ) {
            $progress = Playback::get_progress( get_current_user_id(), $post_id );
            if ( $progress ) {
                $saved_progress = $progress->timecode;
            }
        }

        wp_localize_script( 'ziaoba-player-js', 'ziaobaPlayer', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'ziaoba_player_nonce' ),
            'post_id'  => $post_id,
            'saved_time' => $saved_progress,
            'is_logged_in' => is_user_logged_in(),
            'forced_view' => get_post_meta( $post_id, '_ziaoba_forced_view', true ) === 'yes',
        ) );
    }
}
