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

                <nav class="nav-menu" aria-label="<?php esc_attr_e( 'Primary navigation', 'ziaoba-stream' ); ?>">
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
                        <button type="button" class="icon-button" id="searchToggle" aria-expanded="false" aria-controls="searchForm">
                            <i data-lucide="search"></i>
                        </button>
                        <form role="search" method="get" class="search-form" id="searchForm" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search movies, shows, and lessons...', 'placeholder', 'ziaoba-stream' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
                            <button type="submit" class="search-submit"><i data-lucide="arrow-right"></i></button>
                        </form>
                    </div>

                    <?php if ( is_user_logged_in() ) : ?>
                        <div class="user-nav-wrap">
                            <?php $profile_url = ziaoba_get_auth_url( 'user' ); ?>
                            <a href="<?php echo esc_url( $profile_url ); ?>" class="user-avatar-link">
                                <?php
                                if ( ziaoba_is_um_active() && function_exists( 'um_get_user_avatar_url' ) ) {
                                    echo '<img src="' . esc_url( um_get_user_avatar_url( get_current_user_id(), 32 ) ) . '" alt="' . esc_attr__( 'Profile avatar', 'ziaoba-stream' ) . '">';
                                } else {
                                    echo '<i data-lucide="user"></i>';
                                }
                                ?>
                            </a>
                            <a href="<?php echo esc_url( ziaoba_get_auth_url( 'logout' ) ); ?>" title="<?php esc_attr_e( 'Logout', 'ziaoba-stream' ); ?>">
                                <i data-lucide="log-out"></i>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="auth-links">
                            <a href="<?php echo esc_url( ziaoba_get_auth_url( 'login' ) ); ?>" class="login-link"><?php esc_html_e( 'Login', 'ziaoba-stream' ); ?></a>
                            <a href="<?php echo esc_url( ziaoba_get_auth_url( 'register' ) ); ?>" class="btn btn-primary btn-sm"><?php esc_html_e( 'Register', 'ziaoba-stream' ); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>
