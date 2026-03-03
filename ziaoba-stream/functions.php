<?php
/**
 * functions.php - Theme setup and enqueues.
 * ziaoba-stream/functions.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Theme Setup
 */
if ( ! function_exists( 'ziaoba_stream_setup' ) ) {
    function ziaoba_stream_setup() {
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
        add_theme_support( 'automatic-feed-links' );

        // Register Navigation Menus
        register_nav_menus( array(
            'primary' => __( 'Primary Menu', 'ziaoba-stream' ),
            'footer'  => __( 'Footer Menu', 'ziaoba-stream' ),
        ) );

        // Add Custom Logo Support
        add_theme_support( 'custom-logo', array(
            'height'      => 80,
            'width'       => 300,
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => array( 'site-title', 'site-description' ),
        ) );
    }
}
add_action( 'after_setup_theme', 'ziaoba_stream_setup' );

/**
 * Enqueue Scripts and Styles
 */
if ( ! function_exists( 'ziaoba_stream_scripts' ) ) {
    function ziaoba_stream_scripts() {
        // Google Fonts
        wp_enqueue_style( 'ziaoba-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap' );

        // Swiper.js
        wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0' );
        wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );

        // Lucide Icons
        wp_enqueue_script( 'lucide-icons', 'https://unpkg.com/lucide@latest', array(), '1.0.0', true );

        // Theme Assets with Cache Busting
        $theme_ver = wp_get_theme()->get('Version');
        
        wp_enqueue_style( 'ziaoba-main-style', get_stylesheet_uri(), array(), $theme_ver );
        wp_enqueue_style( 'ziaoba-custom-style', get_template_directory_uri() . '/css/custom.css', array('ziaoba-main-style'), $theme_ver );
        
        wp_enqueue_script( 'ziaoba-main-js', get_template_directory_uri() . '/js/main.js', array('swiper-js', 'lucide-icons'), $theme_ver, true );

        // Pass AJAX URL to JS
        wp_localize_script( 'ziaoba-main-js', 'ziaobaData', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' )
        ) );
    }
}
add_action( 'wp_enqueue_scripts', 'ziaoba_stream_scripts' );

/**
 * Performance: Defer Scripts & Async Styles
 */
function ziaoba_performance_optimizations( $tag, $handle, $src ) {
    // List of scripts to defer
    $defer_scripts = array( 'swiper-js', 'lucide-icons', 'ziaoba-main-js', 'videojs-js', 'hls-js' );
    if ( in_array( $handle, $defer_scripts ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }
    return $tag;
}
add_filter( 'script_loader_tag', 'ziaoba_performance_optimizations', 10, 3 );

/**
 * Performance: Async Styles
 */
function ziaoba_async_styles( $html, $handle, $href, $media ) {
    // List of styles to load asynchronously
    $async_styles = array( 'ziaoba-fonts', 'swiper-css' );
    if ( in_array( $handle, $async_styles ) ) {
        return str_replace( "rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all'\"", $html );
    }
    return $html;
}
add_filter( 'style_loader_tag', 'ziaoba_async_styles', 10, 4 );

/**
 * Rename "Series" to "Entertainment" in Menus
 */
function ziaoba_rename_menu_items( $items ) {
    foreach ( $items as $item ) {
        if ( 'Series' === $item->title ) {
            $item->title = 'Entertainment';
        }
    }
    return $items;
}
add_filter( 'wp_get_nav_menu_items', 'ziaoba_rename_menu_items' );

/**
 * Ultimate Member Integration & Fixes
 */
if ( ! function_exists( 'ziaoba_um_integration' ) ) {
    function ziaoba_um_integration() {
        // Fix UM notice: Register dummy 'um_crop' script if UM active
        if ( function_exists( 'UM' ) ) {
            if ( ! wp_script_is( 'um_crop', 'registered' ) ) {
                wp_register_script( 'um_crop', '', array(), false, true );
            }
        }
    }
}
add_action( 'wp_enqueue_scripts', 'ziaoba_um_integration', 1 );
add_action( 'admin_enqueue_scripts', 'ziaoba_um_integration', 1 );

/**
 * Google Site Kit Integration (Placeholder for Sign in with Google)
 */
function ziaoba_site_kit_google_auth() {
    // If Site Kit is active, we can add a button or hook here
    // For now, we add a styled placeholder that would be replaced by Site Kit's button
    if ( class_exists( 'Google\Site_Kit\Plugin' ) ) {
        echo '<div class="google-site-kit-auth" style="margin-top: 20px; text-align: center;">';
        // Site Kit usually injects its own button, but we can provide a container
        echo '</div>';
    }
}
add_action( 'um_after_login_fields', 'ziaoba_site_kit_google_auth' );

/**
 * Redirect wp-login.php to UM Login Page
 */
function ziaoba_redirect_login() {
    if ( ! function_exists( 'um_get_core_page_url' ) ) return;
    
    global $pagenow;
    if ( 'wp-login.php' == $pagenow && !isset($_GET['action']) && !isset($_GET['key']) ) {
        wp_redirect( um_get_core_page_url('login') );
        exit;
    }
}
add_action( 'init', 'ziaoba_redirect_login' );

/**
 * Filter login URL to point to UM Login Page
 */
add_filter( 'login_url', function( $login_url, $redirect, $force_reauth ) {
    if ( function_exists( 'um_get_core_page_url' ) ) {
        return um_get_core_page_url( 'login' );
    }
    return $login_url;
}, 10, 3 );

/**
 * Add Social Login to UM Forms
 */
function ziaoba_add_social_login_to_um() {
    // MiniOrange removed. Using Site Kit or other methods.
}
add_action( 'um_after_login_fields', 'ziaoba_add_social_login_to_um' );
add_action( 'um_after_register_fields', 'ziaoba_add_social_login_to_um' );

/**
 * Conditional UM Assets
 */
function ziaoba_conditional_um_assets() {
    if ( ! function_exists( 'UM' ) ) return;
    
    // Only load UM assets on UM pages or single content pages
    if ( ! is_singular( array( 'entertainment', 'education' ) ) && ! is_page( array( 'login', 'register', 'user', 'password-reset' ) ) ) {
        // We could dequeue here if needed, but UM usually handles its own enqueues well.
        // This is a placeholder for more aggressive optimization if required.
    }
}
add_action( 'wp_enqueue_scripts', 'ziaoba_conditional_um_assets', 20 );

/**
 * Customizer Integration
 */
if ( ! function_exists( 'ziaoba_stream_customize_register' ) ) {
    function ziaoba_stream_customize_register( $wp_customize ) {
        // Logo Setting
        $wp_customize->add_section( 'ziaoba_theme_options', array(
            'title'    => __( 'Theme Options', 'ziaoba-stream' ),
            'priority' => 30,
        ) );

        $wp_customize->add_setting( 'ziaoba_footer_text', array(
            'default'           => '&copy; ' . date('Y') . ' Ziaoba Entertainment. Entertainment That Builds You.',
            'sanitize_callback' => 'wp_kses_post',
        ) );

        $wp_customize->add_control( 'ziaoba_footer_text', array(
            'label'    => __( 'Footer Text', 'ziaoba-stream' ),
            'section'  => 'ziaoba_theme_options',
            'type'     => 'textarea',
        ) );
    }
}
add_action( 'customize_register', 'ziaoba_stream_customize_register' );

/**
 * Helper: Get Random Hero Post
 */
if ( ! function_exists( 'ziaoba_get_hero_post' ) ) {
    function ziaoba_get_hero_post() {
        // Debug: Fetching random hero post for homepage
        $args = array(
            'post_type'      => 'entertainment',
            'posts_per_page' => 1,
            'orderby'        => 'rand',
            'post_status'    => 'publish', // Only published posts
            'meta_query'     => array(
                array(
                    'key'     => '_thumbnail_id',
                    'compare' => 'EXISTS'
                )
            )
        );
        
        try {
            $posts = get_posts( $args );
            return !empty($posts) ? $posts[0] : null;
        } catch ( Exception $e ) {
            error_log( 'Ziaoba Hero Error: ' . $e->getMessage() );
            return null;
        }
    }
}

/**
 * Fallback Menu
 */
if ( ! function_exists( 'ziaoba_fallback_menu' ) ) {
    function ziaoba_fallback_menu() {
        echo '<a href="' . esc_url( home_url( '/' ) ) . '">Home</a>';
        echo '<a href="' . get_post_type_archive_link( 'entertainment' ) . '">Series</a>';
        echo '<a href="' . get_post_type_archive_link( 'education' ) . '">Education</a>';
    }
}
