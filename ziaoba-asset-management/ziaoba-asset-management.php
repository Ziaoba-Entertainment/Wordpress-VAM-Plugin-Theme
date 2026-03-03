<?php
/**
 * Plugin Name: Ziaoba Asset Management
 * Plugin URI: https://ziaoba.com
 * Description: Video Asset Management (VAM) for Ziaoba Entertainment.
 * Version: 1.0.0
 * Author: Senior WP Developer
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Constants
define( 'ZIAOBA_VAM_PATH', plugin_dir_path( __FILE__ ) );
define( 'ZIAOBA_VAM_URL', plugin_dir_url( __FILE__ ) );
define( 'ZIAOBA_VAM_VERSION', '1.0.0' );

// Include Files
require_once ZIAOBA_VAM_PATH . 'includes/cpt.php';
require_once ZIAOBA_VAM_PATH . 'includes/meta-boxes.php';
require_once ZIAOBA_VAM_PATH . 'includes/admin-columns.php';
require_once ZIAOBA_VAM_PATH . 'includes/shortcodes.php';
require_once ZIAOBA_VAM_PATH . 'includes/player.php';
require_once ZIAOBA_VAM_PATH . 'includes/monetization.php';
require_once ZIAOBA_VAM_PATH . 'includes/dashboard.php';
require_once ZIAOBA_VAM_PATH . 'includes/tracking.php';

// Fix Ultimate Member notice: Register dummy 'um_crop' script if UM active
if ( ! function_exists( 'ziaoba_fix_um_notices' ) ) {
    function ziaoba_fix_um_notices() {
        if ( ! wp_script_is( 'um_crop', 'registered' ) ) {
            wp_register_script( 'um_crop', '', array(), ZIAOBA_VAM_VERSION, true );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'ziaoba_fix_um_notices', 1 );
add_action( 'admin_enqueue_scripts', 'ziaoba_fix_um_notices', 1 );

/**
 * Enqueue Admin Scripts
 */
function ziaoba_vam_admin_scripts( $hook ) {
    if ( 'tools_page_ziaoba-analytics' !== $hook ) {
        return;
    }

    wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true );
    wp_enqueue_script( 'ziaoba-analytics-js', ZIAOBA_VAM_URL . 'js/analytics-dashboard.js', array( 'chart-js' ), ZIAOBA_VAM_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'ziaoba_vam_admin_scripts' );

// Flush permalinks on activation
if ( ! function_exists( 'ziaoba_vam_activate' ) ) {
    function ziaoba_vam_activate() {
        if ( function_exists( 'ziaoba_register_cpts' ) ) {
            ziaoba_register_cpts();
        }
        
        // Disable "Anyone can register" in general settings
        update_option( 'users_can_register', 0 );
        
        flush_rewrite_rules();
    }
}
register_activation_hook( __FILE__, 'ziaoba_vam_activate' );
