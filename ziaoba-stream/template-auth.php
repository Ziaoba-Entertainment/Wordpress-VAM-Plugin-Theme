<?php
/**
 * Template Name: Ziaoba Auth Template
 * 
 * This template is used for Login and Registration pages to provide
 * a cinematic, dark, Netflix-style experience.
 */

get_header(); ?>

<div class="auth-page-wrapper" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?php echo get_template_directory_uri(); ?>/assets/auth-bg.jpg'); background-size: cover; background-position: center; min-height: calc(100vh - 70px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
    
    <div class="auth-container" style="background: rgba(0,0,0,0.75); padding: 60px 68px 40px; width: 100%; max-width: 450px; border-radius: 4px; box-sizing: border-box;">
        
        <div class="auth-header" style="margin-bottom: 28px;">
            <h1 style="color: #fff; font-size: 32px; font-weight: 700; margin-bottom: 0;"><?php the_title(); ?></h1>
        </div>

        <div class="auth-content">
            <?php 
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    the_content();
                endwhile;
            endif;
            ?>
        </div>

        <?php if ( ! is_user_logged_in() ) : ?>
            <div class="auth-footer" style="margin-top: 30px; border-top: 1px solid #333; padding-top: 20px;">
                <div class="google-login-section" style="text-align: center;">
                    <div class="google-auth-button-container">
                        <?php 
                        // Hook for Google Site Kit or other social login buttons
                        // The divider and fallback are handled in functions.php
                        do_action( 'ziaoba_google_auth_button' ); 
                        ?>
                    </div>
                </div>

                <div style="margin-top: 30px; color: #737373; font-size: 16px;">
                    <?php if ( is_page('login') || strpos($_SERVER['REQUEST_URI'], 'login') !== false ) : ?>
                        <?php _e( 'New to Ziaoba?', 'ziaoba-stream' ); ?> 
                        <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('register') : wp_registration_url(); ?>" style="color: #fff; text-decoration: none; font-weight: 500;">
                            <?php _e( 'Sign up now.', 'ziaoba-stream' ); ?>
                        </a>
                    <?php else : ?>
                        <?php _e( 'Already have an account?', 'ziaoba-stream' ); ?> 
                        <a href="<?php echo ( function_exists('um_get_core_page_url') ) ? um_get_core_page_url('login') : wp_login_url(); ?>" style="color: #fff; text-decoration: none; font-weight: 500;">
                            <?php _e( 'Sign in.', 'ziaoba-stream' ); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<style>
/* Ultimate Member Form Overrides for Netflix Style */
.um-form input[type=text], 
.um-form input[type=password], 
.um-form input[type=email] {
    background: #333 !important;
    border: none !important;
    border-radius: 4px !important;
    color: #fff !important;
    height: 50px !important;
    padding: 0 20px !important;
    font-size: 16px !important;
}

.um-form .um-button {
    background: var(--primary-color) !important;
    border: none !important;
    border-radius: 4px !important;
    height: 50px !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    margin-top: 24px !important;
    text-transform: none !important;
}

.um-form .um-button:hover {
    background: var(--hover-color) !important;
}

.um-field-label {
    color: #8c8c8c !important;
    font-size: 14px !important;
    margin-bottom: 8px !important;
}

.um-left {
    color: #b3b3b3 !important;
}

.um-link-alt {
    color: #b3b3b3 !important;
    text-decoration: none !important;
}

.um-link-alt:hover {
    text-decoration: underline !important;
}

@media (max-width: 740px) {
    .auth-container {
        padding: 60px 5% 40px !important;
        max-width: 100% !important;
        background: #000 !important;
    }
    .auth-page-wrapper {
        background: #000 !important;
        padding: 0 !important;
        align-items: flex-start !important;
    }
}
</style>

<?php get_footer(); ?>
