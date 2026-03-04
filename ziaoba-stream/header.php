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
                <div class="nav-left">
                    <div class="hamburger" id="mobileToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="logo-wrap">
                        <?php 
                        if ( has_custom_logo() ) {
                            the_custom_logo();
                        } else {
                            echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="logo">ZIAOBA</a>';
                        }
                        ?>
                    </div>
                    <nav class="nav-menu" id="navMenu">
                        <?php
                        wp_nav_menu( array(
                            'theme_location' => 'primary',
                            'container'      => false,
                            'fallback_cb'    => 'ziaoba_fallback_menu',
                        ) );
                        ?>
                    </nav>
                </div>

                <div class="nav-center">
                    <form role="search" method="get" class="search-form" id="searchForm" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="search-input-wrapper">
                            <i data-lucide="search" class="search-icon"></i>
                            <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search movies, shows...', 'placeholder', 'ziaoba-stream' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
                        </div>
                    </form>
                </div>

                <div class="nav-right">
                    <?php if ( is_user_logged_in() ) : ?>
                        <div class="auth-links">
                            <a href="<?php echo esc_url( ziaoba_get_um_url( 'user' ) ); ?>" class="login-link">Profile</a>
                            <a href="<?php echo esc_url( ziaoba_get_um_url( 'logout' ) ); ?>" class="btn btn-outline btn-sm">Logout</a>
                        </div>
                    <?php else : ?>
                        <div class="auth-links">
                            <a href="<?php echo esc_url( ziaoba_get_um_url( 'login' ) ); ?>" class="login-link">Login</a>
                            <a href="<?php echo esc_url( ziaoba_get_um_url( 'register' ) ); ?>" class="btn btn-primary btn-sm">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
