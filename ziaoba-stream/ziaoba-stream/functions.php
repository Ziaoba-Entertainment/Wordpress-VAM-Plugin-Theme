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
    $version = '1.0.1';

    wp_enqueue_style( 'ziaoba-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap' );
    wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css' );
    wp_enqueue_style( 'videojs-css', 'https://vjs.zencdn.net/8.10.0/video-js.css' );
    wp_enqueue_style( 'ziaoba-main-style', get_stylesheet_uri(), array(), $version );

    wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );
    wp_enqueue_script( 'lucide-icons', 'https://unpkg.com/lucide@latest', array(), '1.0.0', true );
    wp_enqueue_script( 'videojs-js', 'https://vjs.zencdn.net/8.10.0/video.min.js', array(), '8.10.0', true );
    wp_enqueue_script( 'ziaoba-main-js', get_template_directory_uri() . '/js/main.js', array('swiper-js', 'lucide-icons'), $version, true );

    wp_localize_script( 'ziaoba-main-js', 'ziaobaData', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' )
    ) );
}
add_action( 'wp_enqueue_scripts', 'ziaoba_stream_scripts' );

/**
 * Helper: Safely get UM Core Page URL
 */
function ziaoba_get_um_url( $page = 'login' ) {
    if ( function_exists( 'um_get_core_page_url' ) ) {
        $url = um_get_core_page_url( $page );
        if ( $url ) return $url;
    }
    
    // Fallbacks
    switch ( $page ) {
        case 'register':
            return home_url( '/register/' );
        case 'user':
            return home_url( '/user/' );
        case 'logout':
            return wp_logout_url( home_url() );
        default:
            return home_url( '/login/' );
    }
}

/**
 * Redirect wp-login.php to UM Login Page
 */
function ziaoba_redirect_wp_login() {
    global $pagenow;
    // Only redirect if we are on wp-login.php, not in admin, and no specific action (like logout/lostpassword) is set
    if ( 'wp-login.php' === $pagenow && ! is_admin() && ! isset( $_GET['action'] ) ) {
        wp_redirect( ziaoba_get_um_url( 'login' ) );
        exit;
    }
}
add_action( 'init', 'ziaoba_redirect_wp_login', 9999 );

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
 * Google Site Kit Social Button
 */
function ziaoba_render_google_auth_button() {
    $google_auth_url = add_query_arg( 'googlesitekit_authenticate', '1', wp_login_url() );
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
add_action( 'um_after_login_fields', 'ziaoba_render_google_auth_button' );
add_action( 'um_after_register_fields', 'ziaoba_render_google_auth_button' );
add_action( 'ziaoba_google_auth_button', 'ziaoba_render_google_auth_button' );

/**
 * Ensure Ultimate Member auth pages are fully compatible and registration is enabled.
 */
function ziaoba_um_auth_compatibility() {
    if ( ! function_exists( 'shortcode_exists' ) || ! shortcode_exists( 'ultimatemember' ) ) {
        return;
    }

    // Required so UM registration form can submit when WP setting was disabled.
    if ( '1' !== get_option( 'users_can_register' ) ) {
        update_option( 'users_can_register', '1' );
    }

    // Ensure canonical UM page assignment is present for fallback-safe routing.
    if ( function_exists( 'um_get_post_id' ) ) {
        $login_page_id = um_get_post_id( 'login' );
        if ( ! $login_page_id ) {
            $page = get_page_by_path( 'login' );
            if ( $page ) {
                update_option( 'um_options_core_login', (int) $page->ID );
            }
        }

        $register_page_id = um_get_post_id( 'register' );
        if ( ! $register_page_id ) {
            $page = get_page_by_path( 'register' );
            if ( $page ) {
                update_option( 'um_options_core_register', (int) $page->ID );
            }
        }
    }
}
add_action( 'init', 'ziaoba_um_auth_compatibility', 20 );

/**
 * Helpers
 */
function ziaoba_get_hero_post() {
    $posts = get_posts( array(
        'post_type' => 'entertainment',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post_status' => 'publish',
        'meta_key' => '_thumbnail_id'
    ) );
    return !empty($posts) ? $posts[0] : null;
}

function ziaoba_fallback_menu() {
    echo '<ul>';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
    echo '<li><a href="' . get_post_type_archive_link( 'entertainment' ) . '">Entertainment</a></li>';
    echo '<li><a href="' . get_post_type_archive_link( 'education' ) . '">Education</a></li>';
    echo '</ul>';
}
