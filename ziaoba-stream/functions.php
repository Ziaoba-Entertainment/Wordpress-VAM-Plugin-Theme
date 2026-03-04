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
 * Check if current wp-login.php request should bypass UM redirect.
 *
 * This allows Site Kit (and related Google auth) OAuth requests
 * to continue using wp-login.php like the default WordPress login screen.
 */
function ziaoba_is_sitekit_oauth_request() {
    if ( empty( $_GET ) ) {
        return false;
    }

    foreach ( $_GET as $key => $value ) {
        $key_string   = sanitize_key( $key );
        $value_string = is_scalar( $value ) ? strtolower( sanitize_text_field( wp_unslash( $value ) ) ) : '';

        if ( strpos( $key_string, 'googlesitekit' ) !== false || strpos( $key_string, 'sitekit' ) !== false ) {
            return true;
        }

        if ( $key_string === 'action' && ( strpos( $value_string, 'google' ) !== false || strpos( $value_string, 'sitekit' ) !== false ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Safe Redirect wp-login.php to UM Login Page
 */
function ziaoba_redirect_wp_login() {
    global $pagenow;

    // Only redirect wp-login.php
    if ( 'wp-login.php' !== $pagenow ) {
        return;
    }

    // Keep Google Site Kit/Google OAuth requests on native wp-login.php
    if ( ziaoba_is_sitekit_oauth_request() ) {
        return;
    }

    // Allow logout, lostpassword, and register actions if needed, but primary focus is login
    $action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';
    if ( ! empty( $action ) && ! in_array( $action, array( 'login' ), true ) ) {
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
 * Build Google auth URL to trigger Site Kit OAuth flow through wp-login.php.
 */
function ziaoba_get_google_auth_url() {
    $redirect_to = home_url( '/dashboard/' );

    if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
        $current_url = home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );
        if ( filter_var( $current_url, FILTER_VALIDATE_URL ) ) {
            $redirect_to = $current_url;
        }
    }

    $wp_login_url = site_url( 'wp-login.php', 'login' );

    return add_query_arg(
        array(
            'googlesitekit_authenticate' => '1',
            'redirect_to'                => rawurlencode( $redirect_to ),
        ),
        $wp_login_url
    );
}

/**
 * Google Site Kit Social Button
 */
function ziaoba_render_google_auth_button() {
    $google_auth_url = add_query_arg(
        array(
            'googlesitekit_authenticate' => '1',
            'redirect_to'                => rawurlencode( home_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ),
        ),
        site_url( 'wp-login.php', 'login' )
    );
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
 * Ensure Ultimate Member auth pages are fully compatible and registration is enabled.
 */
function ziaoba_um_auth_compatibility() {
    if ( ! function_exists( 'shortcode_exists' ) || ! shortcode_exists( 'ultimatemember' ) ) {
        return;
    }

    // Ensure canonical UM page assignment is present for fallback-safe routing.
    if ( function_exists( 'UM' ) ) {
        $um_options = get_option( 'um_options', array() );
        $changed = false;

        if ( empty( $um_options['core_login'] ) ) {
            $page = get_page_by_path( 'login' );
            if ( $page ) {
                $um_options['core_login'] = $page->ID;
                $changed = true;
            }
        }

        if ( empty( $um_options['core_register'] ) ) {
            $page = get_page_by_path( 'register' );
            if ( $page ) {
                $um_options['core_register'] = $page->ID;
                $changed = true;
            }
        }

        if ( $changed ) {
            update_option( 'um_options', $um_options );
        }
    }
}
add_action( 'init', 'ziaoba_um_auth_compatibility', 20 );

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
 * Helper: Safely get UM Core Page URL
 */
function ziaoba_get_um_url( $page = 'login' ) {
    if ( function_exists( 'um_get_core_page_url' ) ) {
        $url = um_get_core_page_url( $page );
        if ( $url && strpos( $url, 'wp-login.php' ) === false ) {
            return $url;
        }
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
