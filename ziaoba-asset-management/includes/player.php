<?php
/**
 * Player Scripts and Styles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'ziaoba_enqueue_player_assets' );

function ziaoba_enqueue_player_assets() {
    // Video.js
    wp_enqueue_style( 'videojs-css', 'https://vjs.zencdn.net/8.10.0/video-js.css', array(), '8.10.0' );
    wp_enqueue_script( 'videojs-js', 'https://vjs.zencdn.net/8.10.0/video.min.js', array(), '8.10.0', true );

    // Custom Player CSS
    wp_add_inline_style( 'videojs-css', '
        .video-js .vjs-big-play-button { border-radius: 50%; width: 2em; height: 2em; line-height: 2em; margin-top: -1em; margin-left: -1em; background-color: rgba(229, 9, 20, 0.8); border-color: transparent; }
        .video-js:hover .vjs-big-play-button { background-color: #E50914; }
        .vjs-control-bar { background-color: rgba(10, 10, 10, 0.9); }
        .video-js.vjs-fluid { display: block; width: 100%; min-height: 200px; background: #000; }
        .ziaoba-player-wrapper { position: relative; width: 100%; display: block; }
    ' );
}
