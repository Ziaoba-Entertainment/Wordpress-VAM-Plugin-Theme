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
add_action( 'wp_ajax_nopriv_ziaoba_track_view', 'ziaoba_track_view_callback' );

function ziaoba_track_view_callback() {
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    if ( $post_id ) {
        $views = get_post_meta( $post_id, '_ziaoba_views', true ) ?: 0;
        update_post_meta( $post_id, '_ziaoba_views', $views + 1 );
        
        // Log daily views
        $today = date( 'Y-m-d' );
        $log = get_post_meta( $post_id, '_ziaoba_views_log', true ) ?: array();
        $log[$today] = ( isset( $log[$today] ) ? $log[$today] : 0 ) + 1;
        update_post_meta( $post_id, '_ziaoba_views_log', $log );
    }
    wp_die();
}

/**
 * Activation Hook
 */
register_activation_hook( __FILE__, 'ziaoba_vam_activate' );
function ziaoba_vam_activate() {
    ziaoba_register_cpts();
    
    // Untick "Anyone can register" to force UM registration
    update_option( 'users_can_register', 0 );
    
    // Set UM Default Forms via options
    if ( function_exists( 'UM' ) ) {
        $um_options = get_option( 'um_options', array() );
        $um_options['default_login_form'] = 68;
        $um_options['default_register_form'] = 67;
        update_option( 'um_options', $um_options );
    }

    flush_rewrite_rules();
}

/**
 * Ensure UM settings are correct on init as well
 */
function ziaoba_ensure_um_settings() {
    if ( ! is_admin() ) return;
    
    if ( get_option( 'users_can_register' ) != 0 ) {
        update_option( 'users_can_register', 0 );
    }
}
add_action( 'init', 'ziaoba_ensure_um_settings' );

/**
 * Google Site Kit Integration for UM
 * 
 * Deprecated: Google auth buttons are rendered by the active theme to avoid
 * duplicate/non-functional buttons inside Ultimate Member forms.
 */
function ziaoba_google_site_kit_um_button() {
    return;
}
// Hooks removed to prevent duplicate buttons
// add_action( 'um_after_login_fields', 'ziaoba_google_site_kit_um_button', 20 );
// add_action( 'um_after_register_fields', 'ziaoba_google_site_kit_um_button', 20 );
