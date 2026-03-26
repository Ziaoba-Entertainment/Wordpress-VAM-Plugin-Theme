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
        
        // REST API Gating
        add_filter( 'rest_pre_dispatch', array( $instance, 'gate_rest_api' ), 10, 3 );
    }

    /**
     * Check if user has set their DOB
     */
    public function check_user_dob() {
        if ( ! is_user_logged_in() || is_admin() ) {
            return;
        }

        $user_id = get_current_user_id();
        $dob = get_user_meta( $user_id, 'dob', true );

        // If DOB is missing and not on the required info page, redirect
        if ( ! $dob && ! is_page( 'required-info' ) && ! is_page( 'profile' ) && ! is_page( 'logout' ) && ! is_page( 'restricted' ) ) {
            wp_safe_redirect( home_url( '/required-info/' ) );
            exit;
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
            if ( $dob ) {
                update_user_meta( $user_id, 'dob', $dob );
                wp_safe_redirect( home_url( '/' ) );
                exit;
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
