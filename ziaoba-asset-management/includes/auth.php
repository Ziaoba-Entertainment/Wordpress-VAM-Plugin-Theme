<?php
/**
 * Authentication and Login Modal Logic
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Auth {

    /**
     * Initialize Auth
     */
    public static function init() {
        $instance = new self();
        add_action( 'wp_enqueue_scripts', array( $instance, 'enqueue_assets' ) );
        add_action( 'wp_footer', array( $instance, 'render_modal' ) );
        
        // AJAX Handlers
        add_action( 'wp_ajax_nopriv_ziaoba_login', array( $instance, 'ajax_login' ) );
        add_action( 'wp_ajax_nopriv_ziaoba_register', array( $instance, 'ajax_register' ) );
    }

    /**
     * Enqueue Assets
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'ziaoba-auth-css', ZIAOBA_VAM_URL . 'css/auth.css', array(), ZIAOBA_VAM_VERSION );
        wp_enqueue_script( 'ziaoba-auth-js', ZIAOBA_VAM_URL . 'js/auth.js', array( 'jquery' ), ZIAOBA_VAM_VERSION, true );

        wp_localize_script( 'ziaoba-auth-js', 'ziaobaAuth', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ziaoba_auth_nonce' ),
            'isLoggedIn' => is_user_logged_in(),
            'messages' => array(
                'login_success' => __( 'Login successful! Redirecting...', 'ziaoba-asset-management' ),
                'register_success' => __( 'Registration successful! Redirecting...', 'ziaoba-asset-management' ),
                'error_generic' => __( 'Something went wrong. Please try again.', 'ziaoba-asset-management' ),
            )
        ) );
    }

    /**
     * Render Modal HTML
     */
    public function render_modal() {
        if ( is_user_logged_in() ) {
            return;
        }
        ?>
        <div id="ziaoba-auth-modal" class="ziaoba-modal">
            <div class="ziaoba-modal-content">
                <span class="ziaoba-modal-close">&times;</span>
                
                <div id="ziaoba-auth-form-container">
                    <!-- Login Form -->
                    <form id="ziaoba-login-form" class="ziaoba-auth-form">
                        <h2 class="ziaoba-modal-title"><?php _e( 'Sign In', 'ziaoba-asset-management' ); ?></h2>
                        <p class="ziaoba-modal-subtitle"><?php _e( 'Ready to watch? Enter your email to sign in.', 'ziaoba-asset-management' ); ?></p>
                        
                        <div class="ziaoba-input-group">
                            <input type="text" name="username" id="login-username" placeholder=" " required>
                            <label for="login-username"><?php _e( 'Email or Username', 'ziaoba-asset-management' ); ?></label>
                        </div>
                        
                        <div class="ziaoba-input-group">
                            <input type="password" name="password" id="login-password" placeholder=" " required>
                            <label for="login-password"><?php _e( 'Password', 'ziaoba-asset-management' ); ?></label>
                        </div>
                        
                        <div class="ziaoba-form-error" id="login-error"></div>
                        
                        <button type="submit" class="ziaoba-btn ziaoba-btn-primary ziaoba-btn-block">
                            <span class="btn-text"><?php _e( 'Sign In', 'ziaoba-asset-management' ); ?></span>
                            <span class="btn-loader"></span>
                        </button>
                        
                        <div class="ziaoba-form-footer">
                            <span><?php _e( 'New to Ziaoba?', 'ziaoba-asset-management' ); ?></span>
                            <a href="#" class="ziaoba-switch-form" data-target="register"><?php _e( 'Sign up now.', 'ziaoba-asset-management' ); ?></a>
                        </div>
                    </form>

                    <!-- Register Form -->
                    <form id="ziaoba-register-form" class="ziaoba-auth-form" style="display: none;">
                        <h2 class="ziaoba-modal-title"><?php _e( 'Sign Up', 'ziaoba-asset-management' ); ?></h2>
                        <p class="ziaoba-modal-subtitle"><?php _e( 'Create an account to get Free Premium Access.', 'ziaoba-asset-management' ); ?></p>
                        
                        <div class="ziaoba-input-group">
                            <input type="text" name="username" id="reg-username" placeholder=" " required>
                            <label for="reg-username"><?php _e( 'Username', 'ziaoba-asset-management' ); ?></label>
                        </div>

                        <div class="ziaoba-input-group">
                            <input type="email" name="email" id="reg-email" placeholder=" " required>
                            <label for="reg-email"><?php _e( 'Email Address', 'ziaoba-asset-management' ); ?></label>
                        </div>
                        
                        <div class="ziaoba-input-group">
                            <input type="password" name="password" id="reg-password" placeholder=" " required>
                            <label for="reg-password"><?php _e( 'Password', 'ziaoba-asset-management' ); ?></label>
                        </div>
                        
                        <div class="ziaoba-form-error" id="register-error"></div>
                        
                        <button type="submit" class="ziaoba-btn ziaoba-btn-primary ziaoba-btn-block">
                            <span class="btn-text"><?php _e( 'Sign Up', 'ziaoba-asset-management' ); ?></span>
                            <span class="btn-loader"></span>
                        </button>
                        
                        <div class="ziaoba-form-footer">
                            <span><?php _e( 'Already have an account?', 'ziaoba-asset-management' ); ?></span>
                            <a href="#" class="ziaoba-switch-form" data-target="login"><?php _e( 'Sign in now.', 'ziaoba-asset-management' ); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX Login
     */
    public function ajax_login() {
        check_ajax_referer( 'ziaoba_auth_nonce', 'nonce' );

        $info = array();
        $info['user_login']    = sanitize_user( $_POST['username'] );
        $info['user_password'] = $_POST['password'];
        $info['remember']      = true;

        $user_signon = wp_signon( $info, false );

        if ( is_wp_error( $user_signon ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid username or password.', 'ziaoba-asset-management' ) ) );
        } else {
            wp_send_json_success( array( 'message' => __( 'Login successful!', 'ziaoba-asset-management' ) ) );
        }
    }

    /**
     * AJAX Register
     */
    public function ajax_register() {
        check_ajax_referer( 'ziaoba_auth_nonce', 'nonce' );

        $username = sanitize_user( $_POST['username'] );
        $email    = sanitize_email( $_POST['email'] );
        $password = $_POST['password'];

        if ( empty( $username ) || empty( $email ) || empty( $password ) ) {
            wp_send_json_error( array( 'message' => __( 'Please fill in all fields.', 'ziaoba-asset-management' ) ) );
        }

        if ( username_exists( $username ) ) {
            wp_send_json_error( array( 'message' => __( 'Username already exists.', 'ziaoba-asset-management' ) ) );
        }

        if ( email_exists( $email ) ) {
            wp_send_json_error( array( 'message' => __( 'Email already exists.', 'ziaoba-asset-management' ) ) );
        }

        $user_id = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ) );
        }

        // Auto login after registration
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        wp_send_json_success( array( 'message' => __( 'Registration successful!', 'ziaoba-asset-management' ) ) );
    }
}
