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
        <div class="auth-header" style="margin-bottom: 28px;">
            <h1 style="color: #fff; font-size: 32px; font-weight: 700; margin-bottom: 0;">Sign In</h1>
        </div>

        <div class="auth-content">
            <?php 
            // Force the Login page to use the required UM form ID.
            if ( shortcode_exists( 'ultimatemember' ) ) {
                echo do_shortcode( '[ultimatemember form_id="68"]' );
            } else {
                echo '<p>' . esc_html__( 'Ultimate Member is required to display the login form.', 'ziaoba-stream' ) . '</p>';
            }
            ?>
        </div>

        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
            <?php do_action( 'ziaoba_google_auth_button' ); ?>

            <div style="margin-top: 30px; color: #737373; font-size: 16px;">
                New to Ziaoba? 
                <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('register') : home_url( '/register/' ); ?>" style="color: #fff; font-weight: 500;">
                    Sign up now.
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
