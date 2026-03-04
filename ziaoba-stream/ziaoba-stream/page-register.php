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
        <div class="auth-header" style="margin-bottom: 28px;">
            <h1 style="color: #fff; font-size: 32px; font-weight: 700; margin-bottom: 0;">Sign Up</h1>
        </div>

        <div class="auth-content">
            <?php 
            // Force the Register page to use the required UM form ID.
            if ( shortcode_exists( 'ultimatemember' ) ) {
                echo do_shortcode( '[ultimatemember form_id="67"]' );
            } else {
                echo '<p>' . esc_html__( 'Ultimate Member is required to display the registration form.', 'ziaoba-stream' ) . '</p>';
            }
            ?>
        </div>

        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
            <?php do_action( 'ziaoba_google_auth_button' ); ?>

            <div style="margin-top: 30px; color: #737373; font-size: 16px;">
                Already have an account? 
                <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('login') : home_url( '/login/' ); ?>" style="color: #fff; font-weight: 500;">
                    Sign in now.
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
