<?php
/**
 * page-login.php - Custom Login Page Template
 */

if ( is_user_logged_in() ) {
    wp_redirect( home_url() );
    exit;
}

get_header(); ?>

<div class="auth-page-wrapper">
    <div class="auth-container">
        <div class="auth-header">
            <h1 class="auth-title">Sign In</h1>
        </div>

        <div class="auth-content">
            <?php 
            if ( shortcode_exists( 'ultimatemember' ) && defined( 'ZIAOBA_UM_LOGIN_FORM' ) ) {
                echo do_shortcode( '[ultimatemember form_id="' . ZIAOBA_UM_LOGIN_FORM . '"]' );
            } else {
                echo '<p>' . esc_html__( 'Ultimate Member is required to display the login form.', 'ziaoba-stream' ) . '</p>';
            }
            ?>
        </div>

        <div class="auth-footer">
            <?php do_action( 'ziaoba_google_auth_button' ); ?>

            <div class="auth-footer-text">
                New to Ziaoba? 
                <a href="<?php echo esc_url( ziaoba_get_um_url( 'register' ) ); ?>" class="auth-footer-link">
                    Sign up now.
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
