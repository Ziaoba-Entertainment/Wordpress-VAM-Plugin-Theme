<?php
/**
 * Age Restriction Logic
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AgeRestriction {

    /**
     * Initialize Age Restriction
     */
    public static function init() {
        $instance = new self();
        add_action( 'template_redirect', array( $instance, 'check_user_dob' ) );
        add_action( 'template_redirect', array( $instance, 'check_age_restriction' ) );
        add_action( 'init', array( $instance, 'handle_dob_submit' ) );
        
        // Login Redirect
        add_filter( 'login_redirect', array( $instance, 'login_redirect' ), 10, 3 );

        // Admin Profile Fields
        add_action( 'show_user_profile', array( $instance, 'add_dob_profile_field' ) );
        add_action( 'edit_user_profile', array( $instance, 'add_dob_profile_field' ) );
        add_action( 'personal_options_update', array( $instance, 'save_dob_profile_field' ) );
        add_action( 'edit_user_profile_update', array( $instance, 'save_dob_profile_field' ) );

        // REST API Gating
        add_filter( 'rest_pre_dispatch', array( $instance, 'gate_rest_api' ), 10, 3 );
    }

    /**
     * Validate DOB Format and Logic
     */
    public static function is_valid_dob( $dob ) {
        if ( empty( $dob ) ) {
            return false;
        }

        // Format YYYY-MM-DD
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dob ) ) {
            return false;
        }

        $parts = explode( '-', $dob );
        if ( ! checkdate( (int) $parts[1], (int) $parts[2], (int) $parts[0] ) ) {
            return false;
        }

        try {
            $date = new \DateTime( $dob );
            $now = new \DateTime();
            
            // Not in future
            if ( $date > $now ) {
                return false;
            }

            // Reasonable age bounds (e.g., not more than 120 years old)
            $age = $date->diff( $now )->y;
            if ( $age > 120 ) {
                return false;
            }

            return true;
        } catch ( \Exception $e ) {
            return false;
        }
    }

    /**
     * Check if user has set their DOB
     */
    public function check_user_dob() {
        // 1. Logged out users don't get redirected for DOB
        if ( ! is_user_logged_in() ) {
            return;
        }

        // 2. Admins are never blocked
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        $user_id = get_current_user_id();
        $dob = get_user_meta( $user_id, 'dob', true );

        // 3. If DOB is valid, no redirect
        if ( self::is_valid_dob( $dob ) ) {
            return;
        }

        // 4. Whitelist paths to prevent loops
        $current_url = home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
        $required_info_url = home_url( '/required-info/' );
        
        // Whitelist slugs
        $whitelist = array( 'required-info', 'profile', 'logout', 'login', 'register', 'user', 'account' );
        $is_whitelisted = false;
        foreach ( $whitelist as $slug ) {
            if ( is_page( $slug ) || strpos( $_SERVER['REQUEST_URI'], '/' . $slug ) !== false ) {
                $is_whitelisted = true;
                break;
            }
        }

        if ( $is_whitelisted ) {
            return;
        }

        // 5. Fallback if required-info page doesn't exist
        $page = get_page_by_path( 'required-info' );
        if ( ! $page ) {
            error_log( 'Ziaoba VAM: required-info page missing. Redirecting to home.' );
            return;
        }

        // 6. Redirect to required info
        error_log( 'Ziaoba VAM: Redirecting User ' . $user_id . ' to required-info (Missing/Invalid DOB: ' . $dob . ')' );
        wp_safe_redirect( $required_info_url );
        exit;
    }

    /**
     * Redirect after login if DOB is missing
     */
    public function login_redirect( $redirect_to, $request, $user ) {
        if ( is_wp_error( $user ) || ! $user ) {
            return $redirect_to;
        }

        if ( user_can( $user, 'manage_options' ) ) {
            return $redirect_to;
        }

        $dob = get_user_meta( $user->ID, 'dob', true );
        if ( ! self::is_valid_dob( $dob ) ) {
            error_log( 'Ziaoba VAM: Login Redirect User ' . $user->ID . ' to required-info (Missing/Invalid DOB: ' . $dob . ')' );
            return home_url( '/required-info/' );
        }

        return $redirect_to;
    }

    /**
     * Add DOB field to WP User Profile
     */
    public function add_dob_profile_field( $user ) {
        $dob = get_user_meta( $user->ID, 'dob', true );
        $age = self::get_user_age( $user->ID );
        $can_edit = current_user_can( 'manage_options' );
        ?>
        <h3><?php _e( 'Ziaoba Age Verification', 'ziaoba-asset-management' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="dob"><?php _e( 'Date of Birth', 'ziaoba-asset-management' ); ?></label></th>
                <td>
                    <?php if ( $can_edit ) : ?>
                        <input type="date" name="dob" id="dob" value="<?php echo esc_attr( $dob ); ?>" class="regular-text" />
                    <?php else : ?>
                        <input type="date" value="<?php echo esc_attr( $dob ); ?>" class="regular-text" disabled />
                        <p class="description"><?php _e( 'Only administrators can modify the date of birth.', 'ziaoba-asset-management' ); ?></p>
                    <?php endif; ?>
                    <p class="description">
                        <?php printf( __( 'Calculated Age: %d', 'ziaoba-asset-management' ), $age ); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save DOB from WP User Profile
     */
    public function save_dob_profile_field( $user_id ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            return false;
        }

        if ( isset( $_POST['dob'] ) ) {
            $dob = sanitize_text_field( $_POST['dob'] );
            if ( self::is_valid_dob( $dob ) ) {
                update_user_meta( $user_id, 'dob', $dob );
            }
        }
    }

    /**
     * Handle DOB Form Submission
     */
    public function handle_dob_submit() {
        if ( isset( $_POST['dob_submit'] ) ) {
            if ( ! is_user_logged_in() ) {
                return;
            }

            $user_id = get_current_user_id();
            $existing_dob = get_user_meta( $user_id, 'dob', true );

            // Only allow if DOB is not set OR user is admin
            if ( $existing_dob && ! current_user_can( 'manage_options' ) ) {
                return;
            }

            if ( ! isset( $_POST['ziaoba_dob_nonce_field'] ) || ! wp_verify_nonce( $_POST['ziaoba_dob_nonce_field'], 'ziaoba_dob_nonce' ) ) {
                return;
            }

            $dob = sanitize_text_field( $_POST['dob'] );
            if ( self::is_valid_dob( $dob ) ) {
                update_user_meta( $user_id, 'dob', $dob );
                wp_safe_redirect( home_url( '/' ) );
                exit;
            } else {
                set_transient( 'ziaoba_dob_error_' . $user_id, __( 'Invalid Date of Birth. Please check the format and ensure it is not in the future.', 'ziaoba-asset-management' ), 30 );
            }
        }
    }

    /**
     * Check if user meets age requirement for the current post
     */
    public function check_age_restriction() {
        if ( ! is_singular( array( 'entertainment', 'education', 'series', 'episode' ) ) ) {
            return;
        }

        $post_id = get_the_ID();
        if ( ! self::can_user_view( $post_id ) ) {
            wp_die( __( 'This content is not appropriate for your age group. Please go back.', 'ziaoba-asset-management' ), __( 'Access Restricted', 'ziaoba-asset-management' ), array( 'response' => 403 ) );
            exit;
        }
    }

    /**
     * Gate REST API access to restricted content
     */
    public function gate_rest_api( $result, $server, $request ) {
        $path = $request->get_route();
        
        // Check if it's a request for our CPTs
        if ( preg_match( '/wp\/v2\/(entertainment|education|series|episode)\/(\d+)/', $path, $matches ) ) {
            $post_id = intval( $matches[2] );
            if ( ! self::can_user_view( $post_id ) ) {
                return new \WP_Error( 'rest_forbidden', __( 'This content is not appropriate for your age group. Please go back.', 'ziaoba-asset-management' ), array( 'status' => 403 ) );
            }
        }
        
        return $result;
    }

    /**
     * Calculate age from DOB
     */
    public static function get_user_age( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return 0;
        }

        $dob = get_user_meta( $user_id, 'dob', true );
        if ( ! $dob ) {
            return 0;
        }

        try {
            $birth_date = new \DateTime( $dob );
            $today = new \DateTime( 'today' );
            $age = $birth_date->diff( $today )->y;
            return $age;
        } catch ( \Exception $e ) {
            return 0;
        }
    }

    /**
     * Check if user can view content based on age rating
     */
    public static function can_user_view( $post_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        // Admins can always view
        if ( $user_id && user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        $age_rating = get_post_meta( $post_id, '_ziaoba_age_rating', true );
        if ( ! $age_rating ) {
            $age_rating = get_post_meta( $post_id, '_age_rating', true ); // Legacy
        }

        // Configurable policy for missing ratings
        if ( ! $age_rating ) {
            $settings = get_option( 'ziaoba_monetization_settings', array() );
            $allow_missing = $settings['allow_missing_rating'] ?? 'yes';
            return $allow_missing === 'yes';
        }

        $min_age = self::map_rating_to_age( $age_rating );
        $user_age = self::get_user_age( $user_id );

        return $user_age >= $min_age;
    }

    /**
     * Map TMDB ratings to minimum ages
     */
    private static function map_rating_to_age( $rating ) {
        $rating = strtoupper( trim( $rating ) );
        
        $map = array(
            'G'      => 0,
            'TV-G'   => 0,
            'PG'     => 7,
            'TV-PG'  => 7,
            'PG-13'  => 13,
            'TV-14'  => 14,
            'R'      => 17,
            'TV-MA'  => 17,
            'NC-17'  => 18,
            '18+'    => 18,
            '16+'    => 16,
            '13+'    => 13,
            '12+'    => 12,
            '10+'    => 10,
            '7+'     => 7,
            'U'      => 0,
            'A'      => 18,
            'UA'     => 12,
            'S'      => 18,
            '12'     => 12,
            '15'     => 15,
            '18'     => 18,
        );

        return isset( $map[$rating] ) ? $map[$rating] : 0;
    }
}
