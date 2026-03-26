<?php
/**
 * Player Shortcode
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Shortcodes {

    /**
     * Initialize Shortcodes
     */
    public static function init() {
        $instance = new self();
        add_shortcode( 'ziaoba_player', array( $instance, 'player_shortcode' ) );
        add_shortcode( 'ziaoba_continue_watching', array( $instance, 'continue_watching_shortcode' ) );
        add_shortcode( 'ziaoba_required_info', array( $instance, 'required_info_shortcode' ) );
    }

    /**
     * Required Info Shortcode (DOB Collection)
     */
    public function required_info_shortcode( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'Please login to provide your information.', 'ziaoba-asset-management' ) . '</p>';
        }

        $user_id = get_current_user_id();
        $dob = get_user_meta( $user_id, 'dob', true );
        $error = get_transient( 'ziaoba_dob_error_' . $user_id );
        if ( $error ) {
            delete_transient( 'ziaoba_dob_error_' . $user_id );
        }

        if ( $dob && ! current_user_can( 'manage_options' ) && AgeRestriction::is_valid_dob( $dob ) ) {
            return '<p>' . __( 'Your information has already been collected. Thank you.', 'ziaoba-asset-management' ) . '</p>';
        }

        $today = date( 'Y-m-d' );

        ob_start();
        ?>
        <div class="ziaoba-required-info-form">
            <h2><?php _e( 'Complete Your Profile', 'ziaoba-asset-management' ); ?></h2>
            <p><?php _e( 'To ensure a safe and personalized experience, please provide your date of birth.', 'ziaoba-asset-management' ); ?></p>
            
            <?php if ( $error ) : ?>
                <div class="ziaoba-error-message mb-4 p-3 bg-red-900 text-white rounded">
                    <?php echo esc_html( $error ); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <?php wp_nonce_field( 'ziaoba_dob_nonce', 'ziaoba_dob_nonce_field' ); ?>
                <div class="form-group mb-4">
                    <label for="dob" class="block mb-2"><?php _e( 'Date of Birth', 'ziaoba-asset-management' ); ?></label>
                    <input type="date" id="dob" name="dob" value="<?php echo esc_attr( $dob ); ?>" max="<?php echo $today; ?>" required class="w-full p-3 bg-gray-800 border border-gray-700 rounded text-white">
                </div>
                <button type="submit" name="dob_submit" class="btn btn-primary w-full">
                    <?php _e( 'Save and Continue', 'ziaoba-asset-management' ); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Player Shortcode
     */
    public function player_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => get_the_ID(),
        ), $atts, 'ziaoba_player' );

        $post_id = $atts['id'];
        $video_url = get_post_meta( $post_id, '_ziaoba_video_url', true );
        
        // Use backdrop helper
        $poster_url = ziaoba_get_backdrop( $post_id );

        if ( ! $video_url ) {
            return '<div class="ziaoba-error">' . __( 'Video URL not found.', 'ziaoba-asset-management' ) . '</div>';
        }

        // Restriction check
        if ( ! is_user_logged_in() ) {
            ob_start();
            ?>
            <div class="ziaoba-watch-now-placeholder ziaoba-backdrop" style="background-image: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('<?php echo esc_url( $poster_url ); ?>');">
                <div class="play-icon"></div>
                <div class="cta-text"><?php _e( 'Watch Now', 'ziaoba-asset-management' ); ?></div>
                <div class="sub-cta"><?php _e( 'Sign up for Free Premium Access', 'ziaoba-asset-management' ); ?></div>
            </div>
            <?php
            return ob_get_clean();
        }

        // Age Restriction Check
        if ( class_exists( 'Ziaoba\VAM\AgeRestriction' ) ) {
            if ( ! AgeRestriction::can_user_view( $post_id ) ) {
                $age_rating = get_post_meta( $post_id, '_age_rating', true );
                ob_start();
                ?>
                <div class="ziaoba-watch-now-placeholder ziaoba-backdrop restricted-content" style="background-image: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)), url('<?php echo esc_url( $poster_url ); ?>');">
                    <div class="lock-icon mb-4"><i data-lucide="lock" class="w-12 h-12 text-red-600"></i></div>
                    <div class="cta-text"><?php _e( 'Content Restricted', 'ziaoba-asset-management' ); ?></div>
                    <div class="sub-cta"><?php printf( __( 'This content is rated %s and is not available for your age group.', 'ziaoba-asset-management' ), esc_html( $age_rating ) ); ?></div>
                </div>
                <script>if(typeof lucide !== 'undefined') lucide.createIcons();</script>
                <?php
                return ob_get_clean();
            }
        }

        // Enqueue player assets
        if ( class_exists( 'Ziaoba\VAM\Player' ) ) {
            Player::enqueue_assets( $post_id );
        }

        // We use a custom escaping for the URL to preserve parentheses which are common in HLS paths
        $safe_video_url = str_replace( array( '%28', '%29' ), array( '(', ')' ), esc_url( $video_url ) );

        ob_start();
        ?>
        <div class="ziaoba-player-wrapper">
            <video id="ziaoba-video-player" 
                   class="video-js vjs-big-play-centered vjs-fluid vjs-16-9" 
                   controls 
                   preload="auto" 
                   poster="<?php echo esc_url( $poster_url ); ?>">
                <source src="<?php echo $safe_video_url; ?>" type="application/x-mpegURL">
                <p class="vjs-no-js">
                    To view this video please enable JavaScript, and consider upgrading to a web browser that
                    <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                </p>
            </video>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Continue Watching Shortcode
     */
    public function continue_watching_shortcode( $atts ) {
        if ( class_exists( 'Ziaoba\VAM\Playback' ) ) {
            $playback = new Playback();
            return $playback->render_continue_watching( $atts );
        }
        return '';
    }
}
