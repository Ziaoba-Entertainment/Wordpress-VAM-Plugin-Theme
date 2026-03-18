<?php
/**
 * functions.php - Theme setup and enqueues.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

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

function ziaoba_stream_scripts() {
    $version = wp_get_theme()->get( 'Version' ) . '.' . filemtime( get_template_directory() . '/js/main.js' );

    wp_enqueue_style( 'ziaoba-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap' );
    wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css' );
    wp_enqueue_style( 'ziaoba-main-style', get_stylesheet_uri(), array(), $version );

    wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );
    wp_enqueue_script( 'lucide-icons', 'https://unpkg.com/lucide@latest', array(), '1.0.0', true );
    wp_enqueue_script( 'ziaoba-main-js', get_template_directory_uri() . '/js/main.js', array( 'swiper-js', 'lucide-icons' ), $version, true );

    wp_localize_script( 'ziaoba-main-js', 'ziaobaData', array(
        'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
        'homeUrl'      => home_url( '/' ),
        'historyLabel' => __( 'Close overlay', 'ziaoba-stream' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'ziaoba_stream_scripts' );

add_filter( 'um_display_admin_notices', '__return_false' );

function ziaoba_is_um_active() {
    return function_exists( 'um_get_core_page_url' ) && is_plugin_active( 'ultimate-member/ultimate_member.php' );
}

function ziaoba_get_auth_url( $page = 'login', $redirect = '' ) {
    $fallbacks = array(
        'login'        => home_url( '/login/' ),
        'register'     => home_url( '/register/' ),
        'logout'       => wp_logout_url( home_url( '/' ) ),
        'lostpassword' => home_url( '/login/?action=lostpassword' ),
        'user'         => home_url( '/profile/' ),
    );

    if ( 'logout' === $page ) {
        return wp_logout_url( home_url( '/' ) );
    }

    if ( ziaoba_is_um_active() ) {
        $url = um_get_core_page_url( $page );
        if ( $url && false === strpos( $url, 'wp-login.php' ) ) {
            if ( $redirect ) {
                $url = add_query_arg( 'redirect_to', $redirect, $url );
            }
            return $url;
        }
    }

    $url = $fallbacks[ $page ] ?? $fallbacks['login'];
    if ( $redirect && 'logout' !== $page ) {
        $url = add_query_arg( 'redirect_to', $redirect, $url );
    }

    return $url;
}

function ziaoba_get_google_login_url( $redirect = '' ) {
    $redirect = $redirect ?: home_url( '/' );

    if ( has_filter( 'ziaoba_google_login_url' ) ) {
        return apply_filters( 'ziaoba_google_login_url', '', $redirect );
    }

    return add_query_arg(
        array(
            'action'      => 'google_login',
            'redirect_to' => $redirect,
        ),
        ziaoba_get_auth_url( 'login' )
    );
}

function ziaoba_handle_google_login_proxy() {
    if ( is_admin() ) {
        return;
    }

    $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
    if ( 'google_login' !== $action ) {
        return;
    }

    $redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : home_url( '/' );
    $provider_url = add_query_arg(
        array(
            'action'      => 'google_login',
            'redirect_to' => $redirect,
        ),
        wp_login_url()
    );

    wp_safe_redirect( $provider_url );
    exit;
}
add_action( 'template_redirect', 'ziaoba_handle_google_login_proxy', 1 );

function ziaoba_redirect_wp_login() {
    global $pagenow;

    if ( 'wp-login.php' !== $pagenow || is_admin() ) {
        return;
    }

    $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'login';

    if ( in_array( $action, array( 'logout', 'lostpassword', 'rp', 'resetpass', 'postpass', 'google_login' ), true ) ) {
        return;
    }

    if ( isset( $_GET['key'] ) || isset( $_GET['rp_key'] ) ) {
        return;
    }

    wp_safe_redirect( ziaoba_get_auth_url( 'login' ) );
    exit;
}
add_action( 'init', 'ziaoba_redirect_wp_login', 9999 );

add_filter( 'login_url', function( $login_url, $redirect ) {
    return ziaoba_get_auth_url( 'login', $redirect );
}, 999, 2 );

add_filter( 'register_url', function( $register_url ) {
    return ziaoba_get_auth_url( 'register' );
}, 999 );

add_filter( 'lostpassword_url', function( $lostpassword_url, $redirect ) {
    return ziaoba_get_auth_url( 'lostpassword', $redirect );
}, 999, 2 );

add_filter( 'logout_redirect', function() {
    return home_url( '/' );
} );

add_filter( 'auth_cookie_expiration', function( $length, $user_id, $remember ) {
    return $remember ? MONTH_IN_SECONDS * 6 : WEEK_IN_SECONDS * 2;
}, 10, 3 );

function ziaoba_render_google_auth_button() {
    $redirect   = is_singular() ? get_permalink() : home_url( '/' );
    $button_url = ziaoba_get_google_login_url( $redirect );
    ?>
    <div class="ziaoba-social-auth-wrap">
        <p class="social-divider"><span><?php esc_html_e( 'Or continue with', 'ziaoba-stream' ); ?></span></p>
        <a href="<?php echo esc_url( $button_url ); ?>" class="ziaoba-google-btn">
            <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google Logo">
            <span><?php esc_html_e( 'Continue with Google', 'ziaoba-stream' ); ?></span>
        </a>
        <p class="google-auth-note"><?php esc_html_e( 'If Google sign-in is enabled by your authentication plugin, you will be forwarded securely and returned here after sign-in.', 'ziaoba-stream' ); ?></p>
    </div>
    <?php
}
add_action( 'ziaoba_google_auth_button', 'ziaoba_render_google_auth_button' );

function ziaoba_restrict_search_query( $query ) {
    if ( ! is_admin() && $query->is_main_query() && $query->is_search() ) {
        $query->set( 'post_type', array( 'entertainment', 'education' ) );
        $query->set( 'post_status', 'publish' );
    }
}
add_action( 'pre_get_posts', 'ziaoba_restrict_search_query' );

function ziaoba_um_auth_compatibility() {
    if ( ! ziaoba_is_um_active() ) {
        return;
    }

    if ( '1' !== get_option( 'users_can_register' ) ) {
        update_option( 'users_can_register', '1' );
    }

    foreach ( array( 'login', 'register' ) as $slug ) {
        if ( function_exists( 'um_get_post_id' ) && ! um_get_post_id( $slug ) ) {
            $page = get_page_by_path( $slug );
            if ( $page ) {
                update_option( 'um_options_core_' . $slug, (int) $page->ID );
            }
        }
    }
}
add_action( 'init', 'ziaoba_um_auth_compatibility', 20 );

function ziaoba_get_hero_post() {
    $posts = get_posts( array(
        'post_type'      => 'entertainment',
        'posts_per_page' => 1,
        'orderby'        => 'rand',
        'post_status'    => 'publish',
        'meta_key'       => '_thumbnail_id',
    ) );

    return ! empty( $posts ) ? $posts[0] : null;
}

function ziaoba_get_display_poster( $post_id, $size = 'large' ) {
    $poster_url = get_post_meta( $post_id, '_ziaoba_poster_url', true );
    if ( $poster_url ) {
        return sprintf( '<img src="%s" class="card-img" alt="%s" loading="lazy">', esc_url( $poster_url ), esc_attr( get_the_title( $post_id ) ) );
    }

    if ( has_post_thumbnail( $post_id ) ) {
        return get_the_post_thumbnail( $post_id, $size, array( 'class' => 'card-img', 'loading' => 'lazy' ) );
    }

    return sprintf( '<img src="https://picsum.photos/seed/%1$d/500/750" class="card-img" alt="%2$s" loading="lazy">', absint( $post_id ), esc_attr( get_the_title( $post_id ) ) );
}

function ziaoba_get_content_meta( $post_id ) {
    $meta = array_filter( array(
        get_post_meta( $post_id, '_ziaoba_release_date', true ) ? gmdate( 'Y', strtotime( get_post_meta( $post_id, '_ziaoba_release_date', true ) ) ) : '',
        get_post_meta( $post_id, '_ziaoba_duration', true ),
        get_post_meta( $post_id, '_ziaoba_age_rating', true ),
        get_post_meta( $post_id, '_ziaoba_tmdb_vote_average', true ) ? sprintf( 'TMDB %s/10', number_format_i18n( (float) get_post_meta( $post_id, '_ziaoba_tmdb_vote_average', true ), 1 ) ) : '',
    ) );

    return $meta;
}

function ziaoba_get_related_posts( $post_id, $limit = 4 ) {
    $manual_ids = array_filter( array_map( 'intval', explode( ',', (string) get_post_meta( $post_id, '_ziaoba_related_content', true ) ) ) );
    if ( $manual_ids ) {
        return get_posts( array(
            'post_type'      => array( 'entertainment', 'education' ),
            'post_status'    => 'publish',
            'post__in'       => array_slice( $manual_ids, 0, $limit ),
            'orderby'        => 'post__in',
            'posts_per_page' => $limit,
        ) );
    }

    if ( function_exists( 'ziaoba_get_related_content_ids' ) ) {
        $ids = ziaoba_get_related_content_ids( $post_id, $limit );
        if ( $ids ) {
            return get_posts( array(
                'post_type'      => array( 'entertainment', 'education' ),
                'post_status'    => 'publish',
                'post__in'       => $ids,
                'orderby'        => 'post__in',
                'posts_per_page' => $limit,
            ) );
        }
    }

    return array();
}

function ziaoba_fallback_menu() {
    echo '<ul>';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
    echo '<li><a href="' . esc_url( get_post_type_archive_link( 'entertainment' ) ) . '">Entertainment</a></li>';
    echo '<li><a href="' . esc_url( get_post_type_archive_link( 'education' ) ) . '">Education</a></li>';
    echo '</ul>';
}
