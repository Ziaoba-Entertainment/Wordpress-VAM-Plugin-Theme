<?php
/**
 * page-register.php - Custom Register Page Template
 */

if ( is_user_logged_in() ) {
    wp_safe_redirect( home_url( '/' ) );
    exit;
}

get_header(); ?>

<div class="auth-page-wrapper">
    <div class="auth-shell">
        <section class="auth-stage auth-stage-highlight">
            <div class="auth-stage-panel">
                <p class="auth-eyebrow"><?php esc_html_e( 'Create your account', 'ziaoba-stream' ); ?></p>
                <h1 class="auth-stage-title"><?php esc_html_e( 'One account for films, series, and learning.', 'ziaoba-stream' ); ?></h1>
                <p class="auth-stage-copy"><?php esc_html_e( 'Register in minutes, keep things simple on mobile, and use Google when your auth provider supports it.', 'ziaoba-stream' ); ?></p>
                <ul class="auth-benefits">
                    <li><?php esc_html_e( 'Simple sign-up layout built for mobile-first completion.', 'ziaoba-stream' ); ?></li>
                    <li><?php esc_html_e( 'Clear separation between account creation and sign-in.', 'ziaoba-stream' ); ?></li>
                    <li><?php esc_html_e( 'Fast access to series browsing, episode progression, and saved sessions.', 'ziaoba-stream' ); ?></li>
                </ul>
            </div>
        </section>

        <section class="auth-container auth-card auth-form-card">
            <div class="auth-header">
                <p class="auth-eyebrow"><?php esc_html_e( 'Register', 'ziaoba-stream' ); ?></p>
                <h2 class="auth-title"><?php esc_html_e( 'Start streaming with minimal effort', 'ziaoba-stream' ); ?></h2>
                <p class="auth-copy"><?php esc_html_e( 'Sign up with email or Google. Forms are simplified for mobile-first use and faster completion.', 'ziaoba-stream' ); ?></p>
            </div>

            <div class="auth-content auth-enhanced-form" data-auth-mode="register">
                <?php
                if ( ziaoba_is_um_active() && function_exists( 'UM' ) ) {
                    $register_form_id = UM()->options()->get( 'core_register' );
                    echo do_shortcode( $register_form_id ? '[ultimatemember form_id="' . absint( $register_form_id ) . '"]' : '[ultimatemember_register]' );
                } else {
                    echo '<p>' . esc_html__( 'Install or configure your preferred membership provider to enable registration.', 'ziaoba-stream' ) . '</p>';
                }
                ?>
            </div>

            <div class="auth-footer auth-footer-inline">
                <?php do_action( 'ziaoba_google_auth_button' ); ?>
                <div class="auth-alt-link">
                    <?php esc_html_e( 'Already have an account?', 'ziaoba-stream' ); ?>
                    <a href="<?php echo esc_url( ziaoba_get_auth_url( 'login' ) ); ?>"><?php esc_html_e( 'Sign in', 'ziaoba-stream' ); ?></a>
                </div>
            </div>
        </section>
    </div>
</div>

<?php get_footer(); ?>
