<?php
/**
 * functions.php - Theme setup and enqueues.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Ensure is_plugin_active is available on frontend
if ( ! function_exists( 'is_plugin_active' ) ) {
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

/**
 * Theme Setup
 */
function ziaoba_stream_setup() {
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
    
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'ziaoba-stream' ),
    ) );

    add_theme_support( 'custom-logo', array(
        'height'      => 80,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
}
add_action( 'after_setup_theme', 'ziaoba_stream_setup' );

/**
 * Enqueue Scripts and Styles
 */
function ziaoba_stream_scripts() {
    $version = wp_get_theme()->get( 'Version' ) . '.' . time();

    wp_enqueue_style( 'ziaoba-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap' );
    wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css' );
    wp_enqueue_style( 'ziaoba-main-style', get_stylesheet_uri(), array(), $version );

    wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );
    wp_enqueue_script( 'lucide-icons', 'https://unpkg.com/lucide@latest', array(), '1.0.0', true );
    wp_enqueue_script( 'ziaoba-main-js', get_template_directory_uri() . '/js/main.js', array('swiper-js', 'lucide-icons'), $version, true );

    wp_localize_script( 'ziaoba-main-js', 'ziaobaData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' )
    ) );
}
add_action( 'wp_enqueue_scripts', 'ziaoba_stream_scripts' );

/**
 * Hide Ultimate Member Notices
 */
add_filter( 'um_display_admin_notices', '__return_false' );

/**
 * Safe Redirect wp-login.php to UM Login Page
 */
function ziaoba_redirect_wp_login() {
    global $pagenow;
    
    // Only redirect wp-login.php
    if ( 'wp-login.php' !== $pagenow ) {
        return;
    }

    // Allow logout, lostpassword, and register actions if needed, but primary focus is login
    $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
    if ( ! empty( $action ) && ! in_array( $action, array( 'login' ) ) ) {
        return;
    }

    // Skip if it's a password reset link
    if ( isset( $_GET['key'] ) || isset( $_GET['rp_key'] ) ) {
        return;
    }

    // Check if UM is active
    if ( function_exists( 'um_get_core_page_url' ) && is_plugin_active( 'ultimate-member/ultimate_member.php' ) ) {
        $login_url = um_get_core_page_url('login');
        
        // Final fallback if UM returns wp-login.php or empty
        if ( ! $login_url || strpos( $login_url, 'wp-login.php' ) !== false ) {
            $login_url = home_url( '/login/' );
        }

        wp_redirect( $login_url );
        exit;
    }
}
add_action( 'init', 'ziaoba_redirect_wp_login' );

/**
 * Safe login_url filter to point to /login
 */
add_filter( 'login_url', function( $login_url, $redirect ) {
    if ( function_exists( 'um_get_core_page_url' ) && is_plugin_active( 'ultimate-member/ultimate_member.php' ) ) {
        $um_login = um_get_core_page_url( 'login' );
        
        // Ensure we don't return wp-login.php in a loop
        if ( $um_login && strpos( $um_login, 'wp-login.php' ) === false ) {
            return $um_login;
        }
        
        // Fallback to hardcoded slug if UM setting is missing
        return home_url( '/login/' );
    }
    return $login_url;
}, 999, 2 );

/**
 * Google Site Kit Social Button
 */
function ziaoba_render_google_auth_button() {
    $google_auth_url = add_query_arg( 'googlesitekit_login', '1', wp_login_url() );
    ?>
    <div class="ziaoba-social-auth-wrap">
        <p class="social-divider"><span><?php _e( 'Or continue with', 'ziaoba-stream' ); ?></span></p>
        <a href="<?php echo esc_url( $google_auth_url ); ?>" class="ziaoba-google-btn">
            <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google Logo">
            <span><?php _e( 'Sign in with Google', 'ziaoba-stream' ); ?></span>
        </a>
    </div>
    <?php
}
add_action( 'ziaoba_google_auth_button', 'ziaoba_render_google_auth_button' );

/**
 * Restrict Search to Entertainment and Education CPTs
 */
function ziaoba_restrict_search_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $query->set( 'post_type', array( 'entertainment', 'education' ) );
        $query->set( 'post_status', 'publish' );
    }
}
add_action( 'pre_get_posts', 'ziaoba_restrict_search_query' );

/**
 * Helpers
 */
function ziaoba_get_hero_post() {
    $posts = get_posts( array( 'post_type' => 'entertainment', 'posts_per_page' => 1, 'orderby' => 'rand', 'post_status' => 'publish', 'meta_key' => '_thumbnail_id' ) );
    return !empty($posts) ? $posts[0] : null;
}

function ziaoba_fallback_menu() {
    echo '<ul>';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
    echo '<li><a href="' . get_post_type_archive_link( 'entertainment' ) . '">Entertainment</a></li>';
    echo '<li><a href="' . get_post_type_archive_link( 'education' ) . '">Education</a></li>';
    echo '</ul>';
}
