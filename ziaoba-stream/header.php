<?php
/**
 * header.php - Site header.
 * ziaoba-stream/header.php
 */
?>
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
                <?php
                if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    // Fallback text logo
                    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="logo">ZIAOBA</a>';
                }
                ?>
                
                <nav class="nav-menu" id="navMenu">
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'container'      => false,
                        'fallback_cb'    => 'ziaoba_fallback_menu',
                    ) );
                    ?>
                    
                    <!-- Mobile Auth Links -->
                    <div class="mobile-auth-links">
                        <?php if ( is_user_logged_in() ) : ?>
                            <a href="<?php echo ( function_exists('um_get_user_profile_url') ) ? um_get_user_profile_url() : '#'; ?>"><?php _e( 'My Profile', 'ziaoba-stream' ); ?></a>
                            <a href="<?php echo ( function_exists('um_get_logout_url') ) ? um_get_logout_url() : wp_logout_url(); ?>"><?php _e( 'Logout', 'ziaoba-stream' ); ?></a>
                        <?php else : ?>
                            <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('login') : wp_login_url(); ?>"><?php _e( 'Login', 'ziaoba-stream' ); ?></a>
                            <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('register') : wp_registration_url(); ?>"><?php _e( 'Register', 'ziaoba-stream' ); ?></a>
                        <?php endif; ?>
                    </div>
                </nav>

                <div class="nav-actions">
                    <div class="search-trigger"><i data-lucide="search"></i></div>
                    
                    <?php if ( is_user_logged_in() ) : ?>
                        <div class="user-nav-wrap">
                            <a href="<?php echo ( function_exists('um_get_user_profile_url') ) ? um_get_user_profile_url() : '#'; ?>" class="user-avatar-link">
                                <?php 
                                if ( function_exists( 'um_get_user_avatar_url' ) ) {
                                    $avatar = um_get_user_avatar_url( get_current_user_id(), 32 );
                                    echo '<img src="' . esc_url( $avatar ) . '" alt="Avatar">';
                                } else {
                                    echo '<div class="avatar-placeholder"><i data-lucide="user"></i></div>';
                                }
                                ?>
                            </a>
                            <a href="<?php echo ( function_exists('um_get_logout_url') ) ? um_get_logout_url() : wp_logout_url(); ?>" class="logout-link" title="Logout">
                                <i data-lucide="log-out"></i>
                            </a>
                        </div>
                    <?php else : ?>
                        <div class="auth-links">
                            <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('login') : wp_login_url(); ?>" class="login-link"><?php _e( 'Login', 'ziaoba-stream' ); ?></a>
                            <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('register') : wp_registration_url(); ?>" class="btn btn-primary btn-sm"><?php _e( 'Register', 'ziaoba-stream' ); ?></a>
                        </div>
                    <?php endif; ?>

                    <!-- Hamburger Toggle -->
                    <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>
