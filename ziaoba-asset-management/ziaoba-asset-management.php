<?php
/**
 * Plugin Name: Ziaoba Asset Management
 * Plugin URI: https://ziaoba.com
 * Description: Video Asset Management (VAM) for Ziaoba Entertainment. Handles CPTs, Player, and Analytics.
 * Version: 1.1.0
 * Author: Ziaoba Entertainment
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'ZIAOBA_VAM_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZIAOBA_VAM_URL', plugin_dir_url( __FILE__ ) );
define( 'ZIAOBA_VAM_VERSION', '1.1.0' );

// Include Files
require_once ZIAOBA_VAM_PATH . 'includes/cpt.php';
require_once ZIAOBA_VAM_PATH . 'includes/meta-boxes.php';
require_once ZIAOBA_VAM_PATH . 'includes/shortcodes.php';
require_once ZIAOBA_VAM_PATH . 'includes/player.php';
require_once ZIAOBA_VAM_PATH . 'includes/dashboard.php';
require_once ZIAOBA_VAM_PATH . 'includes/class-tmdb-api.php';
require_once ZIAOBA_VAM_PATH . 'admin/class-tmdb-search-ui.php';

// Initialize TMDB Search UI
new Ziaoba_TMDB_Search_UI();

/**
 * Fix Ultimate Member notice: Register dummy 'um_crop' script if UM active
 */
function ziaoba_fix_um_notices_plugin() {
    if ( ! wp_script_is( 'um_crop', 'registered' ) ) {
        wp_register_script( 'um_crop', '', array(), ZIAOBA_VAM_VERSION, true );
    }
}
add_action( 'wp_enqueue_scripts', 'ziaoba_fix_um_notices_plugin', 1 );
add_action( 'admin_enqueue_scripts', 'ziaoba_fix_um_notices_plugin', 1 );

/**
 * Enqueue Admin Scripts for Dashboard
 */
function ziaoba_vam_admin_scripts( $hook ) {
    if ( 'tools_page_ziaoba-analytics' !== $hook ) {
        return;
    }

    wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true );
    wp_enqueue_script( 'ziaoba-analytics-js', ZIAOBA_VAM_URL . 'js/analytics-dashboard.js', array( 'chart-js' ), ZIAOBA_VAM_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'ziaoba_vam_admin_scripts' );

/**
 * Tracking Views via AJAX
 */
add_action( 'wp_ajax_ziaoba_track_view', 'ziaoba_track_view_callback' );

function ziaoba_track_view_callback() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ziaoba' ) ), 401 );
    }

    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ziaoba_player_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'ziaoba' ) ), 403 );
    }

    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    if ( ! $post_id || ! in_array( get_post_type( $post_id ), array( 'entertainment', 'education' ), true ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid content ID.', 'ziaoba' ) ), 400 );
    }

    $user_id     = get_current_user_id();
    $tracked_key = 'ziaoba_view_tracked_' . $post_id;
    if ( get_user_meta( $user_id, $tracked_key, true ) ) {
        wp_send_json_success( array( 'views' => (int) get_post_meta( $post_id, '_ziaoba_views', true ) ) );
    }

    $views = (int) get_post_meta( $post_id, '_ziaoba_views', true );
    update_post_meta( $post_id, '_ziaoba_views', $views + 1 );

    $today = current_time( 'Y-m-d' );
    $log   = get_post_meta( $post_id, '_ziaoba_views_log', true );
    if ( ! is_array( $log ) ) {
        $log = array();
    }
    $log[ $today ] = isset( $log[ $today ] ) ? (int) $log[ $today ] + 1 : 1;
    update_post_meta( $post_id, '_ziaoba_views_log', $log );

    update_user_meta( $user_id, $tracked_key, current_time( 'mysql', true ) );

    wp_send_json_success( array( 'views' => $views + 1 ) );
}

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, 'ziaoba_vam_activate' );
function ziaoba_vam_activate() {
    ziaoba_register_cpts();
    flush_rewrite_rules();
}
