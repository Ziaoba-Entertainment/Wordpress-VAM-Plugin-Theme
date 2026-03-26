<?php
/**
 * Meta Boxes for Video Assets
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MetaBoxes {

    /**
     * Initialize Meta Boxes
     */
    public static function init() {
        $instance = new self();
        add_action( 'add_meta_boxes', array( $instance, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $instance, 'save_meta' ) );
    }

    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        // Main Video Details for Movies/Lessons/Episodes
        add_meta_box(
            'ziaoba_video_details',
            __( 'Video Details', 'ziaoba-asset-management' ),
            array( $this, 'render_video_meta_box' ),
            array( 'entertainment', 'education', 'episode' ),
            'normal',
            'high'
        );

        // Series specific details
        add_meta_box(
            'ziaoba_series_details',
            __( 'Series Details', 'ziaoba-asset-management' ),
            array( $this, 'render_series_meta_box' ),
            array( 'series' ),
            'normal',
            'high'
        );

        // Season specific details
        add_meta_box(
            'ziaoba_season_details',
            __( 'Season Details', 'ziaoba-asset-management' ),
            array( $this, 'render_season_meta_box' ),
            array( 'season' ),
            'normal',
            'high'
        );

        // Episode specific details
        add_meta_box(
            'ziaoba_episode_details',
            __( 'Episode Details', 'ziaoba-asset-management' ),
            array( $this, 'render_episode_meta_box' ),
            array( 'episode' ),
            'normal',
            'high'
        );
    }

    /**
     * Render Video Meta Box (Movies, Lessons, Episodes)
     */
    public function render_video_meta_box( $post ) {
        wp_nonce_field( 'ziaoba_video_meta_nonce', 'ziaoba_video_meta_nonce_field' );

        $video_url = get_post_meta( $post->ID, '_ziaoba_video_url', true );
        $trailer_url = get_post_meta( $post->ID, '_ziaoba_trailer_url', true );
        $forced_view = get_post_meta( $post->ID, '_ziaoba_forced_view', true );
        $backdrop_url = get_post_meta( $post->ID, '_ziaoba_backdrop_url', true );
        $duration  = get_post_meta( $post->ID, '_ziaoba_duration', true );
        $rating    = get_post_meta( $post->ID, '_ziaoba_age_rating', true );
        $views     = get_post_meta( $post->ID, '_ziaoba_views', true ) ?: 0;
        
        // Education specific
        $topic = get_post_meta( $post->ID, '_ziaoba_lesson_topic', true );
        $related = get_post_meta( $post->ID, '_ziaoba_related_content', true );

        ?>
        <div class="ziaoba-meta-row" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Video URL (HLS/m3u8)', 'ziaoba-asset-management' ); ?></label>
            <input type="text" name="ziaoba_video_url" value="<?php echo esc_attr( $video_url ); ?>" style="width: 100%;" placeholder="https://example.com/video.m3u8">
        </div>
        <div class="ziaoba-meta-row" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Trailer URL (YouTube/Direct)', 'ziaoba-asset-management' ); ?></label>
            <input type="text" name="ziaoba_trailer_url" value="<?php echo esc_attr( $trailer_url ); ?>" style="width: 100%;" placeholder="https://youtube.com/watch?v=...">
        </div>
        <div class="ziaoba-meta-row" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;">
                <input type="checkbox" name="ziaoba_forced_view" value="yes" <?php checked( $forced_view, 'yes' ); ?>>
                <?php _e( 'Forced View (Disable forward seeking)', 'ziaoba-asset-management' ); ?>
            </label>
        </div>
        <div class="ziaoba-meta-row" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Backdrop URL (16:9)', 'ziaoba-asset-management' ); ?></label>
            <input type="text" name="ziaoba_backdrop_url" value="<?php echo esc_attr( $backdrop_url ); ?>" style="width: 100%;" placeholder="https://example.com/backdrop.jpg">
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Duration', 'ziaoba-asset-management' ); ?></label>
                <input type="text" name="ziaoba_duration" value="<?php echo esc_attr( $duration ); ?>" style="width: 100%;" placeholder="1h 45m or 15m">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Age Rating', 'ziaoba-asset-management' ); ?></label>
                <input type="text" name="ziaoba_age_rating" value="<?php echo esc_attr( $rating ); ?>" style="width: 100%;" placeholder="13+ or PG">
            </div>
        </div>
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Total Views (Read-only)', 'ziaoba-asset-management' ); ?></label>
            <input type="text" value="<?php echo esc_attr( $views ); ?>" style="width: 100%; background: #f0f0f1;" readonly>
        </div>

        <?php if ( $post->post_type === 'education' ) : ?>
        <div style="border-top: 1px solid #ddd; padding-top: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Lesson Topic', 'ziaoba-asset-management' ); ?></label>
            <input type="text" name="ziaoba_lesson_topic" value="<?php echo esc_attr( $topic ); ?>" style="width: 100%;">
        </div>
        <?php endif; ?>

        <div style="margin-top: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Related Content', 'ziaoba-asset-management' ); ?></label>
            <input type="number" name="ziaoba_related_content" value="<?php echo esc_attr( $related ); ?>" style="width: 100%;" placeholder="Post ID of related Ent/Edu">
        </div>
        <?php
    }

    /**
     * Render Series Meta Box
     */
    public function render_series_meta_box( $post ) {
        wp_nonce_field( 'ziaoba_video_meta_nonce', 'ziaoba_video_meta_nonce_field' );
        $tmdb_id = get_post_meta( $post->ID, '_ziaoba_tmdb_id', true );
        ?>
        <div class="ziaoba-meta-row">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'TMDB ID', 'ziaoba-asset-management' ); ?></label>
            <input type="text" name="ziaoba_tmdb_id" value="<?php echo esc_attr( $tmdb_id ); ?>" style="width: 100%;">
        </div>
        <?php
    }

    /**
     * Render Season Meta Box
     */
    public function render_season_meta_box( $post ) {
        wp_nonce_field( 'ziaoba_video_meta_nonce', 'ziaoba_video_meta_nonce_field' );
        $season_num = get_post_meta( $post->ID, '_ziaoba_season_number', true );
        $series_id  = get_post_meta( $post->ID, '_ziaoba_series_id', true );

        $series_posts = get_posts( array(
            'post_type'      => 'series',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
        ?>
        <div class="ziaoba-meta-row" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Parent Series', 'ziaoba-asset-management' ); ?></label>
            <select name="ziaoba_series_id" style="width: 100%;">
                <option value=""><?php _e( '-- Select Series --', 'ziaoba-asset-management' ); ?></option>
                <?php foreach ( $series_posts as $series ) : ?>
                    <option value="<?php echo $series->ID; ?>" <?php selected( $series_id, $series->ID ); ?>>
                        <?php echo esc_html( $series->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ziaoba-meta-row">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Season Number', 'ziaoba-asset-management' ); ?></label>
            <input type="number" name="ziaoba_season_number" value="<?php echo esc_attr( $season_num ); ?>" style="width: 100%;">
        </div>
        <?php
    }

    /**
     * Render Episode Meta Box
     */
    public function render_episode_meta_box( $post ) {
        wp_nonce_field( 'ziaoba_video_meta_nonce', 'ziaoba_video_meta_nonce_field' );
        $ep_num    = get_post_meta( $post->ID, '_ziaoba_episode_number', true );
        $season_id = get_post_meta( $post->ID, '_ziaoba_season_id', true );

        $season_posts = get_posts( array(
            'post_type'      => 'season',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
        ?>
        <div class="ziaoba-meta-row" style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Parent Season', 'ziaoba-asset-management' ); ?></label>
            <select name="ziaoba_season_id" style="width: 100%;">
                <option value=""><?php _e( '-- Select Season --', 'ziaoba-asset-management' ); ?></option>
                <?php foreach ( $season_posts as $season ) : ?>
                    <?php 
                    $parent_series_id = get_post_meta( $season->ID, '_ziaoba_series_id', true );
                    $parent_series_title = $parent_series_id ? get_the_title( $parent_series_id ) : __( 'No Series', 'ziaoba-asset-management' );
                    ?>
                    <option value="<?php echo $season->ID; ?>" <?php selected( $season_id, $season->ID ); ?>>
                        <?php echo esc_html( $season->post_title . ' (' . $parent_series_title . ')' ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ziaoba-meta-row">
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Episode Number', 'ziaoba-asset-management' ); ?></label>
            <input type="number" name="ziaoba_episode_number" value="<?php echo esc_attr( $ep_num ); ?>" style="width: 100%;">
        </div>
        <?php
    }

    /**
     * Save Meta Data
     */
    public function save_meta( $post_id ) {
        if ( ! isset( $_POST['ziaoba_video_meta_nonce_field'] ) || ! wp_verify_nonce( $_POST['ziaoba_video_meta_nonce_field'], 'ziaoba_video_meta_nonce' ) ) {
            return;
        }
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $fields = array(
            'ziaoba_video_url'      => '_ziaoba_video_url',
            'ziaoba_trailer_url'    => '_ziaoba_trailer_url',
            'ziaoba_backdrop_url'   => '_ziaoba_backdrop_url',
            'ziaoba_duration'       => '_ziaoba_duration',
            'ziaoba_age_rating'     => '_ziaoba_age_rating',
            'ziaoba_lesson_topic'   => '_ziaoba_lesson_topic',
            'ziaoba_related_content' => '_ziaoba_related_content',
            'ziaoba_tmdb_id'        => '_ziaoba_tmdb_id',
            'ziaoba_series_id'      => '_ziaoba_series_id',
            'ziaoba_season_id'      => '_ziaoba_season_id',
            'ziaoba_season_number'  => '_ziaoba_season_number',
            'ziaoba_episode_number' => '_ziaoba_episode_number',
        );

        foreach ( $fields as $key => $meta_key ) {
            if ( isset( $_POST[$key] ) ) {
                $value = sanitize_text_field( $_POST[$key] );
                update_post_meta( $post_id, $meta_key, $value );

                // Update post_parent for hierarchy
                if ( $key === 'ziaoba_series_id' && $value ) {
                    wp_update_post( array( 'ID' => $post_id, 'post_parent' => (int) $value ) );
                }
                if ( $key === 'ziaoba_season_id' && $value ) {
                    wp_update_post( array( 'ID' => $post_id, 'post_parent' => (int) $value ) );
                }
            }
        }

        // Handle checkbox
        $forced_view = isset( $_POST['ziaoba_forced_view'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_ziaoba_forced_view', $forced_view );
    }
}
