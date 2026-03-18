<?php
/**
 * Meta Boxes for Video Assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'ziaoba_add_video_meta_boxes' );
add_action( 'save_post', 'ziaoba_save_video_meta' );

function ziaoba_add_video_meta_boxes() {
    add_meta_box(
        'ziaoba_video_details',
        __( 'Video Details', 'ziaoba' ),
        'ziaoba_video_meta_callback',
        array( 'entertainment', 'education' ),
        'normal',
        'high'
    );
}

function ziaoba_video_meta_callback( $post ) {
    wp_nonce_field( 'ziaoba_video_meta_nonce', 'ziaoba_video_meta_nonce_field' );

    $video_url = get_post_meta( $post->ID, '_ziaoba_video_url', true );
    $duration  = get_post_meta( $post->ID, '_ziaoba_duration', true );
    $rating    = get_post_meta( $post->ID, '_ziaoba_age_rating', true );
    $views     = get_post_meta( $post->ID, '_ziaoba_views', true ) ?: 0;
    
    // TMDB Data
    $tmdb_id   = get_post_meta( $post->ID, '_ziaoba_tmdb_id', true );
    $tmdb_type = get_post_meta( $post->ID, '_ziaoba_tmdb_type', true );
    $release   = get_post_meta( $post->ID, '_ziaoba_release_date', true );
    $backdrop  = get_post_meta( $post->ID, '_ziaoba_backdrop_url', true );

    // Education specific
    $topic = get_post_meta( $post->ID, '_ziaoba_lesson_topic', true );
    $related = get_post_meta( $post->ID, '_ziaoba_related_content', true );

    ?>
    <div class="ziaoba-meta-row" style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Video URL (HLS/m3u8)', 'ziaoba' ); ?></label>
        <input type="text" name="ziaoba_video_url" value="<?php echo esc_attr( $video_url ); ?>" style="width: 100%;" placeholder="https://example.com/video.m3u8">
    </div>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Duration', 'ziaoba' ); ?></label>
            <input type="text" name="ziaoba_duration" value="<?php echo esc_attr( $duration ); ?>" style="width: 100%;" placeholder="1h 45m or 15m">
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Age Rating / TMDB Vote', 'ziaoba' ); ?></label>
            <input type="text" name="ziaoba_age_rating" value="<?php echo esc_attr( $rating ); ?>" style="width: 100%;" placeholder="13+ or PG">
        </div>
    </div>

    <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'TMDB ID', 'ziaoba' ); ?></label>
            <input type="text" name="ziaoba_tmdb_id" value="<?php echo esc_attr( $tmdb_id ); ?>" style="width: 100%;" readonly>
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Release Date', 'ziaoba' ); ?></label>
            <input type="text" name="ziaoba_release_date" value="<?php echo esc_attr( $release ); ?>" style="width: 100%;">
        </div>
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Backdrop URL', 'ziaoba' ); ?></label>
        <input type="text" name="ziaoba_backdrop_url" value="<?php echo esc_attr( $backdrop ); ?>" style="width: 100%;">
    </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Total Views (Read-only)', 'ziaoba' ); ?></label>
        <input type="text" value="<?php echo esc_attr( $views ); ?>" style="width: 100%; background: #f0f0f1;" readonly>
    </div>

    <?php if ( $post->post_type === 'education' ) : ?>
    <div style="border-top: 1px solid #ddd; padding-top: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Lesson Topic', 'ziaoba' ); ?></label>
        <input type="text" name="ziaoba_lesson_topic" value="<?php echo esc_attr( $topic ); ?>" style="width: 100%;">
    </div>
    <?php endif; ?>

    <div style="margin-top: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: bold;"><?php _e( 'Related Content ID', 'ziaoba' ); ?></label>
        <input type="number" name="ziaoba_related_content" value="<?php echo esc_attr( $related ); ?>" style="width: 100%;" placeholder="Post ID of related Ent/Edu">
    </div>
    <?php
}

function ziaoba_save_video_meta( $post_id ) {
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
        'ziaoba_duration'       => '_ziaoba_duration',
        'ziaoba_age_rating'     => '_ziaoba_age_rating',
        'ziaoba_lesson_topic'   => '_ziaoba_lesson_topic',
        'ziaoba_related_content' => '_ziaoba_related_content',
        'ziaoba_tmdb_id'        => '_ziaoba_tmdb_id',
        'ziaoba_release_date'   => '_ziaoba_release_date',
        'ziaoba_backdrop_url'   => '_ziaoba_backdrop_url',
    );

    foreach ( $fields as $key => $meta_key ) {
        if ( isset( $_POST[$key] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[$key] ) );
        }
    }
}
