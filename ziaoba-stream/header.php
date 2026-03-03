<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    
    <header class="site-header" id="mainHeader">
        <div class="container">
            <div class="nav-inner">
                <div class="logo-wrap">
                    <?php 
                    if ( has_custom_logo() ) {
                        the_custom_logo();
                    } else {
                        echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="logo">ZIAOBA</a>';
                    }
                    ?>
                </div>
                
                <nav class="nav-menu">
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'fallback_cb'    => 'ziaoba_fallback_menu',
                    ) );
                    ?>
                </nav>

                <div class="nav-actions">
                    <div class="search-wrap">
                        <i data-lucide="search" id="searchToggle"></i>
                        <form role="search" method="get" class="search-form" id="searchForm" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search movies, shows...', 'placeholder', 'ziaoba-stream' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
                            <button type="submit" class="search-submit"><i data-lucide="arrow-right"></i></button>
                        </form>
                    </div>
                    
                    <?php if ( is_user_logged_in() ) : ?>
                        <div class="user-nav-wrap">
                            <?php 
                            $um_active = function_exists( 'um_get_core_page_url' ) && is_plugin_active( 'ultimate-member/ultimate_member.php' );
                            $profile_url = $um_active ? um_get_core_page_url('user') : get_author_posts_url( get_current_user_id() );
                            $logout_url = $um_active ? um_get_core_page_url('logout') : wp_logout_url( home_url() );
                            ?>
                            <a href="<?php echo esc_url( $profile_url ); ?>" class="user-avatar-link">
                                <?php 
                                if ( $um_active && function_exists( 'um_get_user_avatar_url' ) ) {
                                    echo '<img src="' . esc_url( um_get_user_avatar_url( get_current_user_id(), 32 ) ) . '" alt="Avatar">';
                                } else {
                                    echo '<i data-lucide="user"></i>';
                                }
                                ?>
                            </a>
                            <a href="<?php echo esc_url( $logout_url ); ?>" title="Logout">
                                <i data-lucide="log-out"></i>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="auth-links">
                            <?php 
                            $um_active = function_exists( 'um_get_core_page_url' ) && is_plugin_active( 'ultimate-member/ultimate_member.php' );
                            
                            // Explicitly target /login and /register slugs for UM compatibility
                            $login_url = $um_active ? um_get_core_page_url('login') : wp_login_url();
                            $register_url = $um_active ? um_get_core_page_url('register') : wp_registration_url();

                            // Fallback if UM returns wp-login.php
                            if ( $um_active && ( ! $login_url || strpos( $login_url, 'wp-login.php' ) !== false ) ) {
                                $login_url = home_url( '/login/' );
                            }
                            if ( $um_active && ( ! $register_url || strpos( $register_url, 'wp-login.php' ) !== false ) ) {
                                $register_url = home_url( '/register/' );
                            }
                            ?>
                            <a href="<?php echo esc_url( $login_url ); ?>" class="login-link">Login</a>
                            <a href="<?php echo esc_url( $register_url ); ?>" class="btn btn-primary btn-sm">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
