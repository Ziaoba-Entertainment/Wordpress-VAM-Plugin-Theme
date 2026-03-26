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
            <h1 class="auth-title">Sign Up</h1>
        </div>

        <div class="auth-content">
            <?php 
            if ( shortcode_exists( 'ultimatemember' ) && defined( 'ZIAOBA_UM_REGISTER_FORM' ) ) {
                echo do_shortcode( '[ultimatemember form_id="' . ZIAOBA_UM_REGISTER_FORM . '"]' );
            } else {
                echo '<p>' . esc_html__( 'Ultimate Member is required to display the registration form.', 'ziaoba-stream' ) . '</p>';
            }
            ?>
        </div>

        <div class="auth-footer">
            <?php do_action( 'ziaoba_google_auth_button' ); ?>

            <div class="auth-footer-text">
                Already have an account? 
                <a href="<?php echo esc_url( ziaoba_get_um_url( 'login' ) ); ?>" class="auth-footer-link">
                    Sign in now.
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
