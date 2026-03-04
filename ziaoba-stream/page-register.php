<?php
/**
 * page-register.php - Custom Register Page Template
 */

if ( is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

get_header(); ?>

<div class="auth-page-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title"><?php esc_html_e( 'Sign Up', 'ziaoba-stream' ); ?></h1>
        </div>

        <div class="auth-content">
            <?php echo do_shortcode( '[ultimatemember form_id="67"]' ); ?>
        </div>

        <div class="auth-footer">
            <?php do_action( 'ziaoba_google_auth_button' ); ?>

            <div class="auth-switch-link">
                <?php esc_html_e( 'Already have an account?', 'ziaoba-stream' ); ?>
                <a href="<?php echo esc_url( home_url( '/login/' ) ); ?>">
                    <?php esc_html_e( 'Sign in now.', 'ziaoba-stream' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
