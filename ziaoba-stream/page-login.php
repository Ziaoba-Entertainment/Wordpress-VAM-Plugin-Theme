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
            // Try to get the UM login form ID from settings
            $login_form_id = UM()->options()->get( 'core_login' );
            if ( $login_form_id ) {
                echo do_shortcode( '[ultimatemember form_id="' . $login_form_id . '"]' );
            } else {
                // Fallback to generic shortcode
                echo do_shortcode( '[ultimatemember_login]' );
            }
            ?>
        </div>

        <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
            <?php do_action( 'ziaoba_google_auth_button' ); ?>

            <div style="margin-top: 30px; color: #737373; font-size: 16px;">
                New to Ziaoba? 
                <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('register') : wp_registration_url(); ?>" style="color: #fff; font-weight: 500;">
                    Sign up now.
                </a>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
