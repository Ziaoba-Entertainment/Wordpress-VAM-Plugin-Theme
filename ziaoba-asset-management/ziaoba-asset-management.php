<?php
/**
 * Plugin Name: Ziaoba Asset Management
 * Plugin URI: https://ziaoba.com
 * Description: Video Asset Management (VAM) for Ziaoba Entertainment. Handles CPTs, Player, and Analytics.
 * Version: 1.2.0
 * Author: Ziaoba Entertainment
 * License: GPL2
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
final class Plugin {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_classes();
        $this->init_hooks();
    }

    /**
     * Define constants
     */
    private function define_constants() {
        define( 'ZIAOBA_VAM_PATH', plugin_dir_path( __FILE__ ) );
        define( 'ZIAOBA_VAM_URL', plugin_dir_url( __FILE__ ) );
        define( 'ZIAOBA_VAM_VERSION', '1.2.0' );
        
        // UM Form IDs
        define( 'ZIAOBA_UM_LOGIN_FORM', 68 );
        define( 'ZIAOBA_UM_REGISTER_FORM', 67 );
    }

    /**
     * Include files
     */
    private function includes() {
        require_once ZIAOBA_VAM_PATH . 'includes/cpt.php';
        require_once ZIAOBA_VAM_PATH . 'includes/meta-boxes.php';
        require_once ZIAOBA_VAM_PATH . 'includes/auth.php';
        require_once ZIAOBA_VAM_PATH . 'includes/series-helper.php';
        require_once ZIAOBA_VAM_PATH . 'includes/frontend.php';
        require_once ZIAOBA_VAM_PATH . 'includes/admin-columns.php';
        require_once ZIAOBA_VAM_PATH . 'includes/shortcodes.php';
        require_once ZIAOBA_VAM_PATH . 'includes/player.php';
        require_once ZIAOBA_VAM_PATH . 'includes/dashboard.php';
        require_once ZIAOBA_VAM_PATH . 'includes/tracking.php';
        require_once ZIAOBA_VAM_PATH . 'includes/monetization.php';
        require_once ZIAOBA_VAM_PATH . 'includes/tmdb.php';
        require_once ZIAOBA_VAM_PATH . 'includes/age-restriction.php';
        require_once ZIAOBA_VAM_PATH . 'includes/playback.php';
    }

    /**
     * Initialize classes
     */
    private function init_classes() {
        CPT::init();
        MetaBoxes::init();
        Auth::init();
        Frontend::init();
        AdminColumns::init();
        Shortcodes::init();
        Player::init();
        Tracking::init();
        Dashboard::init();
        Monetization::init();
        TMDB::init();
        AgeRestriction::init();
        Playback::init();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'fix_um_notices' ), 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'fix_um_notices' ), 1 );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        add_action( 'init', array( $this, 'ensure_um_settings' ) );
        add_action( 'init', array( $this, 'handle_google_login_callback' ) );
        
        // UM Social Login Hooks
        add_action( 'um_after_login_fields', array( $this, 'google_site_kit_button' ), 20 );
        add_action( 'um_after_register_fields', array( $this, 'google_site_kit_button' ), 20 );
        add_action( 'ziaoba_google_auth_button', array( $this, 'google_site_kit_button' ), 20 );

        // Disable Gutenberg for Ziaoba Post Types
        add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );
        add_filter( 'gutenberg_can_edit_post_type', array( $this, 'disable_gutenberg' ), 10, 2 );

        add_action( 'wp_footer', array( $this, 'render_trailer_modal' ) );

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
    }

    /**
     * Render Trailer Modal
     */
    public function render_trailer_modal() {
        ?>
        <div id="trailer-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
            <div style="width: 100%; max-width: 900px; position: relative;">
                <button onclick="closeTrailer()" style="position: absolute; top: -40px; right: 0; background: none; border: none; color: #fff; font-size: 30px; cursor: pointer;">&times;</button>
                <div id="trailer-container"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle Google Login Callback
     */
    public function handle_google_login_callback() {
        if ( isset( $_GET['googlesitekit_login'] ) && '1' === $_GET['googlesitekit_login'] ) {
            // This is a placeholder for where the actual Google OAuth data would be processed.
            // In a real scenario, you'd get the email and name from the OAuth provider.
            // For now, we'll assume the OAuth flow is handled elsewhere and we just need to ensure the user exists.
            
            // If we have the data (e.g. from a session or cookie set by the OAuth provider)
            // we would call ziaoba_google_login($email, $name);
        }
    }

    /**
     * Disable Gutenberg for Ziaoba Post Types
     */
    public function disable_gutenberg( $use_block_editor, $post_type ) {
        $ziaoba_types = array( 'entertainment', 'education', 'series', 'season', 'episode' );
        if ( in_array( $post_type, $ziaoba_types ) ) {
            return false;
        }
        return $use_block_editor;
    }

    /**
     * Fix UM notice: Register dummy 'um_crop' script
     */
    public function fix_um_notices() {
        if ( ! wp_script_is( 'um_crop', 'registered' ) ) {
            wp_register_script( 'um_crop', '', array(), ZIAOBA_VAM_VERSION, true );
        }
    }

    /**
     * Enqueue Admin Scripts
     */
    public function admin_scripts( $hook ) {
        if ( 'tools_page_ziaoba-analytics' !== $hook ) {
            return;
        }

        wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '4.4.1', true );
        wp_enqueue_script( 'ziaoba-analytics-js', ZIAOBA_VAM_URL . 'js/analytics-dashboard.js', array( 'chart-js' ), ZIAOBA_VAM_VERSION, true );
    }

    /**
     * Ensure UM settings are correct
     */
    public function ensure_um_settings() {
        if ( ! is_admin() ) return;
        
        if ( ! function_exists( 'shortcode_exists' ) || ! shortcode_exists( 'ultimatemember' ) ) {
            return;
        }

        // Required so UM registration form can submit when WP setting was disabled.
        if ( '1' !== get_option( 'users_can_register' ) ) {
            update_option( 'users_can_register', '1' );
        }

        // Ensure canonical UM page assignment is present for fallback-safe routing.
        if ( function_exists( 'um_get_post_id' ) ) {
            $login_page_id = um_get_post_id( 'login' );
            if ( ! $login_page_id ) {
                $page = get_page_by_path( 'login' );
                if ( $page ) {
                    update_option( 'um_options_core_login', (int) $page->ID );
                }
            }

            $register_page_id = um_get_post_id( 'register' );
            if ( ! $register_page_id ) {
                $page = get_page_by_path( 'register' );
                if ( $page ) {
                    update_option( 'um_options_core_register', (int) $page->ID );
                }
            }
        }
    }

    /**
     * Google Site Kit Integration for UM
     */
    public function google_site_kit_button() {
        static $rendered = false;
        if ( $rendered ) return;
        $rendered = true;

        $google_auth_url = add_query_arg( 'googlesitekit_authenticate', '1', wp_login_url() );
        ?>
        <div class="ziaoba-social-auth-wrap">
            <p class="social-divider"><span><?php _e( 'Or continue with', 'ziaoba-asset-management' ); ?></span></p>
            <a href="<?php echo esc_url( $google_auth_url ); ?>" class="ziaoba-google-btn">
                <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google Logo">
                <span><?php _e( 'Sign in with Google', 'ziaoba-asset-management' ); ?></span>
            </a>
        </div>
        <?php
    }

    /**
     * Activation Hook
     */
    public function activate() {
        // Create Playback Table
        if ( class_exists( 'Ziaoba\VAM\Playback' ) ) {
            Playback::create_table();
        }

        if ( class_exists( 'Ziaoba\VAM\CPT' ) ) {
            $cpt = new CPT();
            $cpt->register();
        }
        
        // UM Default Forms via options
        if ( function_exists( 'UM' ) ) {
            $um_options = get_option( 'um_options', array() );
            $um_options['default_login_form'] = ZIAOBA_UM_LOGIN_FORM;
            $um_options['default_register_form'] = ZIAOBA_UM_REGISTER_FORM;
            update_option( 'um_options', $um_options );
        }

        flush_rewrite_rules();
    }
}

// Initialize
Plugin::get_instance();
