<?php
/**
 * View Tracking Logic
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_ziaoba_track_view', 'ziaoba_track_view_callback' );
add_action( 'wp_ajax_nopriv_ziaoba_track_view', 'ziaoba_track_view_callback' );

if ( ! function_exists( 'ziaoba_track_view_callback' ) ) {
    function ziaoba_track_view_callback() {
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        
        if ( $post_id ) {
            $views = (int) get_post_meta( $post_id, '_ziaoba_views', true );
            update_post_meta( $post_id, '_ziaoba_views', $views + 1 );
        }

        wp_die();
    }
}
