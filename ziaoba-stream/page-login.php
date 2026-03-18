<?php
/**
 * page-login.php - Custom Login Page Template
 */

if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}

get_header(); ?>

<div class="auth-page-wrapper">
    <div class="auth-container auth-card">
        <div class="auth-header">
            <p class="auth-eyebrow"><?php esc_html_e( 'Welcome back', 'ziaoba-stream' ); ?></p>
            <h1 class="auth-title"><?php esc_html_e( 'Sign in to continue watching', 'ziaoba-stream' ); ?></h1>
            <p class="auth-copy"><?php esc_html_e( 'Use your email address or Google account. Your session can stay active on trusted devices.', 'ziaoba-stream' ); ?></p>
        </div>

        <div class="auth-content auth-enhanced-form" data-auth-mode="login">
            <?php
            if ( ziaoba_is_um_active() && function_exists( 'UM' ) ) {
                $login_form_id = UM()->options()->get( 'core_login' );
                echo do_shortcode( $login_form_id ? '[ultimatemember form_id="' . absint( $login_form_id ) . '"]' : '[ultimatemember_login]' );
            } else {
                wp_login_form( array(
                    'remember'       => true,
                    'redirect'       => home_url( '/' ),
                    'label_username' => __( 'Email Address', 'ziaoba-stream' ),
                    'label_log_in'   => __( 'Sign In', 'ziaoba-stream' ),
                ) );
            }
            ?>
        </div>

        <div class="auth-footer auth-footer-inline">
            <?php do_action( 'ziaoba_google_auth_button' ); ?>
            <div class="auth-alt-link">
                <?php esc_html_e( 'New to Ziaoba?', 'ziaoba-stream' ); ?>
                <a href="<?php echo esc_url( ziaoba_get_auth_url( 'register' ) ); ?>"><?php esc_html_e( 'Create your account', 'ziaoba-stream' ); ?></a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
