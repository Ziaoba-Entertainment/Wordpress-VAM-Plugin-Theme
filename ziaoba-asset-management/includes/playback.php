<?php
/**
 * Playback Tracking and Resume Logic
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Playback {

    /**
     * Initialize Playback
     */
    public static function init() {
        $instance = new self();
        add_action( 'wp_ajax_ziaoba_save_playback_progress', array( $instance, 'ajax_save_progress' ) );
    }

    /**
     * Create Custom Table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ziaoba_playback_progress';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            timecode FLOAT NOT NULL DEFAULT 0,
            duration FLOAT NOT NULL DEFAULT 0,
            last_watched TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_post (user_id, post_id),
            KEY last_watched (last_watched)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * AJAX Save Progress
     */
    public function ajax_save_progress() {
        check_ajax_referer( 'ziaoba_player_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'Not logged in' );
        }

        $user_id  = get_current_user_id();
        $post_id  = intval( $_POST['post_id'] );
        $timecode = floatval( $_POST['timecode'] );
        $duration = floatval( $_POST['duration'] );

        if ( ! $post_id ) {
            wp_send_json_error( 'Invalid Post ID' );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ziaoba_playback_progress';

        $wpdb->replace(
            $table_name,
            array(
                'user_id'  => $user_id,
                'post_id'  => $post_id,
                'timecode' => $timecode,
                'duration' => $duration,
            ),
            array( '%d', '%d', '%f', '%f' )
        );

        wp_send_json_success();
    }

    /**
     * Get Progress for a User and Post
     */
    public static function get_progress( $user_id, $post_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ziaoba_playback_progress';
        
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND post_id = %d",
            $user_id,
            $post_id
        ) );
    }

    /**
     * Render Continue Watching Shortcode
     */
    public function render_continue_watching( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '';
        }

        $user_id = get_current_user_id();
        global $wpdb;
        $table_name = $wpdb->prefix . 'ziaoba_playback_progress';

        // Get last 10 items watched, excluding those finished (e.g. > 95% watched)
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE user_id = %d 
             AND (timecode / duration) < 0.95 
             ORDER BY last_watched DESC 
             LIMIT 10",
            $user_id
        ) );

        if ( ! $results ) {
            return '';
        }

        ob_start();
        ?>
        <section class="ziaoba-continue-watching">
            <div class="section-heading-row">
                <h2 class="section-title"><?php _e( 'Continue Watching', 'ziaoba-asset-management' ); ?></h2>
            </div>
            <div class="swiper continue-watching-swiper poster-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ( $results as $row ) : 
                        $post = get_post( $row->post_id );
                        if ( ! $post ) continue;
                        $progress_percent = ( $row->timecode / $row->duration ) * 100;
                        ?>
                        <article class="swiper-slide poster-slide" onclick="location.href='<?php echo get_permalink( $post->ID ); ?>'">
                            <div class="card-img-wrapper poster-card-img-wrapper">
                                <?php echo wp_kses_post( ziaoba_get_display_poster( $post->ID, 'medium' ) ); ?>
                                <div class="playback-progress-bar">
                                    <div class="playback-progress-fill" style="width: <?php echo esc_attr( $progress_percent ); ?>%;"></div>
                                </div>
                                <div class="card-overlay"><i data-lucide="play"></i></div>
                            </div>
                            <div class="card-info">
                                <h3 class="card-title"><?php echo esc_html( $post->post_title ); ?></h3>
                                <div class="card-meta">
                                    <span><?php echo sprintf( __( '%d min left', 'ziaoba-asset-management' ), ceil( ( $row->duration - $row->timecode ) / 60 ) ); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}
