<?php
/**
 * Meta Boxes for Video Assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'ziaoba_add_video_meta_boxes' );
add_action( 'save_post', 'ziaoba_save_video_meta' );

if ( ! function_exists( 'ziaoba_add_video_meta_boxes' ) ) {
    function ziaoba_add_video_meta_boxes() {
        add_meta_box(
            'ziaoba_video_details',
            __( 'Video Details', 'ziaoba' ),
            'ziaoba_video_details_callback',
            array( 'entertainment', 'education' ),
            'normal',
            'high'
        );

        add_meta_box(
            'ziaoba_related_content',
            __( 'Related Content', 'ziaoba' ),
            'ziaoba_related_content_callback',
            array( 'entertainment', 'education' ),
            'side',
            'default'
        );
    }
}

if ( ! function_exists( 'ziaoba_video_details_callback' ) ) {
    function ziaoba_video_details_callback( $post ) {
        wp_nonce_field( 'ziaoba_video_meta_nonce', 'ziaoba_video_meta_nonce_field' );

        $video_url = get_post_meta( $post->ID, '_ziaoba_video_url', true );
        $duration = get_post_meta( $post->ID, '_ziaoba_duration', true );
        $age_rating = get_post_meta( $post->ID, '_ziaoba_age_rating', true );
        $lesson_topic = get_post_meta( $post->ID, '_ziaoba_lesson_topic', true );
        $season = get_post_meta( $post->ID, '_ziaoba_season', true );
        $episode_number = get_post_meta( $post->ID, '_ziaoba_episode_number', true );

        ?>
        <p>
            <label for="ziaoba_video_url"><strong><?php _e( 'Video URL (HLS m3u8):', 'ziaoba' ); ?></strong></label><br>
            <input type="text" id="ziaoba_video_url" name="ziaoba_video_url" value="<?php echo esc_attr( $video_url ); ?>" class="widefat" placeholder="https://cdn.example.com/video.m3u8">
        </p>
        <div style="display: flex; gap: 20px;">
            <p style="flex: 1;">
                <label for="ziaoba_duration"><strong><?php _e( 'Duration (e.g. 45:00):', 'ziaoba' ); ?></strong></label><br>
                <input type="text" id="ziaoba_duration" name="ziaoba_duration" value="<?php echo esc_attr( $duration ); ?>" class="widefat">
            </p>
            <p style="flex: 1;">
                <label for="ziaoba_age_rating"><strong><?php _e( 'Age Rating:', 'ziaoba' ); ?></strong></label><br>
                <select id="ziaoba_age_rating" name="ziaoba_age_rating" class="widefat">
                    <option value="G" <?php selected( $age_rating, 'G' ); ?>>G</option>
                    <option value="PG" <?php selected( $age_rating, 'PG' ); ?>>PG</option>
                    <option value="TV-14" <?php selected( $age_rating, 'TV-14' ); ?>>TV-14</option>
                    <option value="TV-MA" <?php selected( $age_rating, 'TV-MA' ); ?>>TV-MA</option>
                </select>
            </p>
        </div>

        <p>
            <label for="ziaoba_lesson_topic"><strong><?php _e( 'Lesson Topic / Subtitle:', 'ziaoba' ); ?></strong></label><br>
            <input type="text" id="ziaoba_lesson_topic" name="ziaoba_lesson_topic" value="<?php echo esc_attr( $lesson_topic ); ?>" class="widefat" placeholder="<?php _e( 'e.g. Financial Literacy, Episode Theme, etc.', 'ziaoba' ); ?>">
        </p>

        <?php if ( $post->post_type === 'entertainment' ) : ?>
        <div style="display: flex; gap: 20px;">
            <p style="flex: 1;">
                <label for="ziaoba_season"><strong><?php _e( 'Season:', 'ziaoba' ); ?></strong></label><br>
                <input type="number" id="ziaoba_season" name="ziaoba_season" value="<?php echo esc_attr( $season ); ?>" class="widefat">
            </p>
            <p style="flex: 1;">
                <label for="ziaoba_episode_number"><strong><?php _e( 'Episode Number:', 'ziaoba' ); ?></strong></label><br>
                <input type="number" id="ziaoba_episode_number" name="ziaoba_episode_number" value="<?php echo esc_attr( $episode_number ); ?>" class="widefat">
            </p>
        </div>
        <?php endif; ?>
        <?php
    }
}

if ( ! function_exists( 'ziaoba_related_content_callback' ) ) {
    function ziaoba_related_content_callback( $post ) {
        $related_id = get_post_meta( $post->ID, '_ziaoba_related_content', true );
        $target_type = ( $post->post_type === 'entertainment' ) ? 'education' : 'entertainment';
        
        $items = get_posts( array(
            'post_type' => $target_type,
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        ) );

        echo '<label for="ziaoba_related_content"><strong>' . sprintf( __( 'Related %s:', 'ziaoba' ), ucfirst($target_type) ) . '</strong></label><br>';
        echo '<select id="ziaoba_related_content" name="ziaoba_related_content" class="widefat">';
        echo '<option value="">' . __( 'None', 'ziaoba' ) . '</option>';
        foreach ( $items as $item ) {
            echo '<option value="' . $item->ID . '" ' . selected( $related_id, $item->ID, false ) . '>' . esc_html( $item->post_title ) . '</option>';
        }
        echo '</select>';
    }
}

if ( ! function_exists( 'ziaoba_save_video_meta' ) ) {
    function ziaoba_save_video_meta( $post_id ) {
        if ( ! isset( $_POST['ziaoba_video_meta_nonce_field'] ) || ! wp_verify_nonce( $_POST['ziaoba_video_meta_nonce_field'], 'ziaoba_video_meta_nonce' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $fields = array(
            'ziaoba_video_url'      => '_ziaoba_video_url',
            'ziaoba_duration'       => '_ziaoba_duration',
            'ziaoba_age_rating'     => '_ziaoba_age_rating',
            'ziaoba_lesson_topic'   => '_ziaoba_lesson_topic',
            'ziaoba_season'         => '_ziaoba_season',
            'ziaoba_episode_number' => '_ziaoba_episode_number',
            'ziaoba_related_content' => '_ziaoba_related_content',
        );

        foreach ( $fields as $key => $meta_key ) {
            if ( isset( $_POST[$key] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[$key] ) );
            }
        }
    }
}
