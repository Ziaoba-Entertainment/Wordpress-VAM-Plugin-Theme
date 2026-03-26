<?php
/**
 * View Tracking Logic
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Tracking {

    /**
     * Initialize Tracking
     */
    public static function init() {
        $instance = new self();
        add_action( 'wp_ajax_ziaoba_track_view', array( $instance, 'track_view_callback' ) );
        add_action( 'wp_ajax_nopriv_ziaoba_track_view', array( $instance, 'track_view_callback' ) );
    }

    /**
     * AJAX Callback for tracking views
     */
    public function track_view_callback() {
        // 1. Security Check: Nonce
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'ziaoba_player_nonce' ) ) {
            wp_send_json_error( 'Invalid security token.', 403 );
        }

        // 2. Intent Check: Post ID
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        if ( ! $post_id || ! in_array( get_post_type( $post_id ), array( 'entertainment', 'education' ) ) ) {
            wp_send_json_error( 'Invalid content ID.', 400 );
        }

        // 3. Rate Limiting (Simple Cookie-based)
        $cookie_name = 'ziaoba_viewed_' . $post_id;
        if ( isset( $_COOKIE[$cookie_name] ) ) {
            wp_send_json_error( 'View already recorded.', 429 );
        }

        // 4. Update Total Views
        $views = (int) get_post_meta( $post_id, '_ziaoba_views', true );
        update_post_meta( $post_id, '_ziaoba_views', $views + 1 );

        // 5. Log Daily View for Trend Aggregation
        $today = date( 'Y-m-d' );
        $log = get_post_meta( $post_id, '_ziaoba_views_log', true );
        if ( ! is_array( $log ) ) {
            $log = array();
        }
        
        if ( isset( $log[$today] ) ) {
            $log[$today]++;
        } else {
            $log[$today] = 1;
        }

        // Keep only last 90 days of logs to prevent meta bloat
        if ( count( $log ) > 90 ) {
            ksort( $log ); // Sort by date
            $log = array_slice( $log, -90, null, true );
        }

        update_post_meta( $post_id, '_ziaoba_views_log', $log );

        // Set cookie for 1 hour to prevent spamming
        setcookie( $cookie_name, '1', time() + 3600, COOKIEPATH, COOKIE_DOMAIN );

        wp_send_json_success( array( 'views' => $views + 1 ) );
    }
}
