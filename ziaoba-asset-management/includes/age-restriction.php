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

            $dob = sanitize_text_field( $_POST['dob'] );
            if ( $dob ) {
                update_user_meta( get_current_user_id(), 'dob', $dob );
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
            wp_safe_redirect( home_url( '/restricted/' ) );
            exit;
        }
    }

    /**
     * Calculate age from DOB
     */
    public static function get_user_age( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $dob = get_user_meta( $user_id, 'dob', true );
        if ( ! $dob ) {
            return 0;
        }

        $birth_date = new \DateTime( $dob );
        $today = new \DateTime( 'today' );
        $age = $birth_date->diff( $today )->y;

        return $age;
    }

    /**
     * Check if user can view content based on age rating
     */
    public static function can_user_view( $post_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $age_rating = get_post_meta( $post_id, '_age_rating', true );
        if ( ! $age_rating ) {
            return true; // No rating, assume safe
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
        );

        return isset( $map[$rating] ) ? $map[$rating] : 0;
    }
}
