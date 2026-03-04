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
 * Filter Login URL to point to UM Login Page
 */
function ziaoba_custom_login_url( $login_url, $redirect, $force_reauth ) {
    $custom_login_url = ziaoba_get_um_url( 'login' );
    
    if ( ! empty( $redirect ) ) {
        $custom_login_url = add_query_arg( 'redirect_to', urlencode( $redirect ), $custom_login_url );
    }
    
    return $custom_login_url;
}
add_filter( 'login_url', 'ziaoba_custom_login_url', 10, 3 );

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
 * Add Google Sign-in to UM Forms
 */
function ziaoba_add_google_login_to_um() {
    ?>
    <div class="ziaoba-social-login">
        <div class="ziaoba-separator">
            <span>Or continue with</span>
        </div>
        <div class="ziaoba-social-buttons">
            <a href="<?php echo esc_url( add_query_arg( 'action', 'google_login', ziaoba_get_um_url( 'login' ) ) ); ?>" class="btn-google">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google Logo">
                Sign in with Google
            </a>
        </div>
    </div>
    <?php
}
add_action( 'um_after_login_fields', 'ziaoba_add_google_login_to_um' );
add_action( 'um_after_register_fields', 'ziaoba_add_google_login_to_um' );
add_action( 'ziaoba_google_auth_button', 'ziaoba_add_google_login_to_um' );

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
