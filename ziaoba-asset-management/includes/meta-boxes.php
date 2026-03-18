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

function ziaoba_get_related_content_ids( $post_id, $limit = 6 ) {
    $genres = wp_get_post_terms( $post_id, 'genre', array( 'fields' => 'ids' ) );

    if ( empty( $genres ) || is_wp_error( $genres ) ) {
        return array();
    }

    $query = new WP_Query( array(
        'post_type'              => array( 'entertainment', 'education' ),
        'post_status'            => 'publish',
        'posts_per_page'         => $limit,
        'post__not_in'           => array( $post_id ),
        'orderby'                => 'rand',
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => true,
        'tax_query'              => array(
            array(
                'taxonomy' => 'genre',
                'field'    => 'term_id',
                'terms'    => $genres,
            ),
        ),
        'fields'                 => 'ids',
    ) );

    return array_map( 'intval', $query->posts );
}

function ziaoba_sync_related_content( $post_id ) {
    $related_ids = ziaoba_get_related_content_ids( $post_id );

    if ( ! empty( $related_ids ) ) {
        update_post_meta( $post_id, '_ziaoba_related_content', implode( ',', $related_ids ) );
    }
}

function ziaoba_video_meta_callback( $post ) {
    wp_nonce_field( 'ziaoba_video_meta_nonce', 'ziaoba_video_meta_nonce_field' );

    $meta = array(
        'video_url'          => get_post_meta( $post->ID, '_ziaoba_video_url', true ),
        'duration'           => get_post_meta( $post->ID, '_ziaoba_duration', true ),
        'age_rating'         => get_post_meta( $post->ID, '_ziaoba_age_rating', true ),
        'tmdb_vote_average'  => get_post_meta( $post->ID, '_ziaoba_tmdb_vote_average', true ),
        'tmdb_vote_count'    => get_post_meta( $post->ID, '_ziaoba_tmdb_vote_count', true ),
        'views'              => get_post_meta( $post->ID, '_ziaoba_views', true ) ?: 0,
        'tmdb_id'            => get_post_meta( $post->ID, '_ziaoba_tmdb_id', true ),
        'tmdb_type'          => get_post_meta( $post->ID, '_ziaoba_tmdb_type', true ),
        'content_type'       => get_post_meta( $post->ID, '_ziaoba_content_type', true ) ?: 'movie',
        'release_date'       => get_post_meta( $post->ID, '_ziaoba_release_date', true ),
        'poster_url'         => get_post_meta( $post->ID, '_ziaoba_poster_url', true ),
        'backdrop_url'       => get_post_meta( $post->ID, '_ziaoba_backdrop_url', true ),
        'original_title'     => get_post_meta( $post->ID, '_ziaoba_original_title', true ),
        'tagline'            => get_post_meta( $post->ID, '_ziaoba_tagline', true ),
        'status'             => get_post_meta( $post->ID, '_ziaoba_status', true ),
        'original_language'  => get_post_meta( $post->ID, '_ziaoba_original_language', true ),
        'networks'           => get_post_meta( $post->ID, '_ziaoba_networks', true ),
        'origin_countries'   => get_post_meta( $post->ID, '_ziaoba_origin_countries', true ),
        'trailer_url'        => get_post_meta( $post->ID, '_ziaoba_trailer_url', true ),
        'tmdb_last_sync'     => get_post_meta( $post->ID, '_ziaoba_tmdb_last_sync', true ),
        'lesson_topic'       => get_post_meta( $post->ID, '_ziaoba_lesson_topic', true ),
        'season'             => get_post_meta( $post->ID, '_ziaoba_season', true ),
        'episode_number'     => get_post_meta( $post->ID, '_ziaoba_episode_number', true ),
        'total_seasons'      => get_post_meta( $post->ID, '_ziaoba_total_seasons', true ),
        'total_episodes'     => get_post_meta( $post->ID, '_ziaoba_total_episodes', true ),
        'related_content'    => get_post_meta( $post->ID, '_ziaoba_related_content', true ),
    );

    $related_preview_ids = array_filter( array_map( 'intval', explode( ',', (string) $meta['related_content'] ) ) );
    ?>
    <style>
        .ziaoba-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-bottom:18px}
        .ziaoba-field label{display:block;margin-bottom:6px;font-weight:600}
        .ziaoba-field input,.ziaoba-field textarea,.ziaoba-field select{width:100%}
        .ziaoba-panel{border-top:1px solid #ddd;padding-top:18px;margin-top:18px}
        @media (max-width:782px){.ziaoba-grid{grid-template-columns:1fr}}
    </style>
    <div class="ziaoba-field" style="margin-bottom:18px;">
        <label for="ziaoba_video_url"><?php esc_html_e( 'Video URL (HLS/m3u8)', 'ziaoba' ); ?></label>
        <input type="url" id="ziaoba_video_url" name="ziaoba_video_url" value="<?php echo esc_attr( $meta['video_url'] ); ?>" placeholder="https://example.com/video.m3u8">
    </div>

    <div class="ziaoba-grid">
        <div class="ziaoba-field">
            <label for="ziaoba_duration"><?php esc_html_e( 'Duration', 'ziaoba' ); ?></label>
            <input type="text" id="ziaoba_duration" name="ziaoba_duration" value="<?php echo esc_attr( $meta['duration'] ); ?>" placeholder="1h 45m or 45m">
        </div>
        <div class="ziaoba-field">
            <label for="ziaoba_age_rating"><?php esc_html_e( 'Age Rating', 'ziaoba' ); ?></label>
            <input type="text" id="ziaoba_age_rating" name="ziaoba_age_rating" value="<?php echo esc_attr( $meta['age_rating'] ); ?>" placeholder="TV-MA, PG-13, 13+">
        </div>
    </div>

    <div class="ziaoba-grid">
        <div class="ziaoba-field">
            <label for="ziaoba_tmdb_vote_average"><?php esc_html_e( 'TMDB Vote Average', 'ziaoba' ); ?></label>
            <input type="number" step="0.1" min="0" max="10" id="ziaoba_tmdb_vote_average" name="ziaoba_tmdb_vote_average" value="<?php echo esc_attr( $meta['tmdb_vote_average'] ); ?>">
        </div>
        <div class="ziaoba-field">
            <label for="ziaoba_tmdb_vote_count"><?php esc_html_e( 'TMDB Vote Count', 'ziaoba' ); ?></label>
            <input type="number" min="0" id="ziaoba_tmdb_vote_count" name="ziaoba_tmdb_vote_count" value="<?php echo esc_attr( $meta['tmdb_vote_count'] ); ?>">
        </div>
    </div>

    <div class="ziaoba-panel">
        <h3><?php esc_html_e( 'TMDB Metadata', 'ziaoba' ); ?></h3>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_tmdb_id"><?php esc_html_e( 'TMDB ID', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_tmdb_id" name="ziaoba_tmdb_id" value="<?php echo esc_attr( $meta['tmdb_id'] ); ?>" readonly>
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_tmdb_type"><?php esc_html_e( 'TMDB Type', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_tmdb_type" name="ziaoba_tmdb_type" value="<?php echo esc_attr( $meta['tmdb_type'] ); ?>" readonly>
            </div>
        </div>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_content_type"><?php esc_html_e( 'Content Type', 'ziaoba' ); ?></label>
                <select id="ziaoba_content_type" name="ziaoba_content_type">
                    <option value="movie" <?php selected( $meta['content_type'], 'movie' ); ?>><?php esc_html_e( 'Movie / Standalone', 'ziaoba' ); ?></option>
                    <option value="series" <?php selected( $meta['content_type'], 'series' ); ?>><?php esc_html_e( 'Series', 'ziaoba' ); ?></option>
                    <option value="episode" <?php selected( $meta['content_type'], 'episode' ); ?>><?php esc_html_e( 'Episode', 'ziaoba' ); ?></option>
                    <option value="short" <?php selected( $meta['content_type'], 'short' ); ?>><?php esc_html_e( 'Short', 'ziaoba' ); ?></option>
                </select>
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_release_date"><?php esc_html_e( 'Release Date', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_release_date" name="ziaoba_release_date" value="<?php echo esc_attr( $meta['release_date'] ); ?>">
            </div>
        </div>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_original_title"><?php esc_html_e( 'Original Title', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_original_title" name="ziaoba_original_title" value="<?php echo esc_attr( $meta['original_title'] ); ?>">
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_status"><?php esc_html_e( 'Status', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_status" name="ziaoba_status" value="<?php echo esc_attr( $meta['status'] ); ?>">
            </div>
        </div>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_original_language"><?php esc_html_e( 'Original Language', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_original_language" name="ziaoba_original_language" value="<?php echo esc_attr( $meta['original_language'] ); ?>">
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_origin_countries"><?php esc_html_e( 'Origin Countries', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_origin_countries" name="ziaoba_origin_countries" value="<?php echo esc_attr( $meta['origin_countries'] ); ?>">
            </div>
        </div>
        <div class="ziaoba-field" style="margin-bottom:18px;">
            <label for="ziaoba_tagline"><?php esc_html_e( 'Tagline', 'ziaoba' ); ?></label>
            <input type="text" id="ziaoba_tagline" name="ziaoba_tagline" value="<?php echo esc_attr( $meta['tagline'] ); ?>">
        </div>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_networks"><?php esc_html_e( 'Networks / Studios', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_networks" name="ziaoba_networks" value="<?php echo esc_attr( $meta['networks'] ); ?>">
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_trailer_url"><?php esc_html_e( 'Trailer URL', 'ziaoba' ); ?></label>
                <input type="url" id="ziaoba_trailer_url" name="ziaoba_trailer_url" value="<?php echo esc_attr( $meta['trailer_url'] ); ?>">
            </div>
        </div>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_poster_url"><?php esc_html_e( 'Poster URL', 'ziaoba' ); ?></label>
                <input type="url" id="ziaoba_poster_url" name="ziaoba_poster_url" value="<?php echo esc_attr( $meta['poster_url'] ); ?>">
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_backdrop_url"><?php esc_html_e( 'Backdrop URL', 'ziaoba' ); ?></label>
                <input type="url" id="ziaoba_backdrop_url" name="ziaoba_backdrop_url" value="<?php echo esc_attr( $meta['backdrop_url'] ); ?>">
            </div>
        </div>
        <div class="ziaoba-field">
            <label for="ziaoba_tmdb_last_sync"><?php esc_html_e( 'TMDB Last Sync', 'ziaoba' ); ?></label>
            <input type="text" id="ziaoba_tmdb_last_sync" name="ziaoba_tmdb_last_sync" value="<?php echo esc_attr( $meta['tmdb_last_sync'] ); ?>" readonly>
        </div>
    </div>

    <div class="ziaoba-panel">
        <h3><?php esc_html_e( 'Series Metadata', 'ziaoba' ); ?></h3>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_season"><?php esc_html_e( 'Season Number', 'ziaoba' ); ?></label>
                <input type="number" min="1" id="ziaoba_season" name="ziaoba_season" value="<?php echo esc_attr( $meta['season'] ); ?>">
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_episode_number"><?php esc_html_e( 'Episode Number', 'ziaoba' ); ?></label>
                <input type="number" min="1" id="ziaoba_episode_number" name="ziaoba_episode_number" value="<?php echo esc_attr( $meta['episode_number'] ); ?>">
            </div>
        </div>
        <div class="ziaoba-grid">
            <div class="ziaoba-field">
                <label for="ziaoba_total_seasons"><?php esc_html_e( 'Total Seasons', 'ziaoba' ); ?></label>
                <input type="number" min="0" id="ziaoba_total_seasons" name="ziaoba_total_seasons" value="<?php echo esc_attr( $meta['total_seasons'] ); ?>">
            </div>
            <div class="ziaoba-field">
                <label for="ziaoba_total_episodes"><?php esc_html_e( 'Total Episodes', 'ziaoba' ); ?></label>
                <input type="number" min="0" id="ziaoba_total_episodes" name="ziaoba_total_episodes" value="<?php echo esc_attr( $meta['total_episodes'] ); ?>">
            </div>
        </div>
    </div>

    <?php if ( 'education' === $post->post_type ) : ?>
        <div class="ziaoba-panel">
            <div class="ziaoba-field">
                <label for="ziaoba_lesson_topic"><?php esc_html_e( 'Lesson Topic', 'ziaoba' ); ?></label>
                <input type="text" id="ziaoba_lesson_topic" name="ziaoba_lesson_topic" value="<?php echo esc_attr( $meta['lesson_topic'] ); ?>">
            </div>
        </div>
    <?php endif; ?>

    <div class="ziaoba-panel">
        <div class="ziaoba-field" style="margin-bottom:18px;">
            <label for="ziaoba_related_content"><?php esc_html_e( 'Related Content IDs', 'ziaoba' ); ?></label>
            <input type="text" id="ziaoba_related_content" name="ziaoba_related_content" value="<?php echo esc_attr( $meta['related_content'] ); ?>" placeholder="Automatically populated from matching genres">
            <p class="description"><?php esc_html_e( 'Automatically refreshed from matching genres on save/import. You can also enter comma-separated post IDs manually if required.', 'ziaoba' ); ?></p>
        </div>
        <div class="ziaoba-field">
            <label><?php esc_html_e( 'Related Content Preview', 'ziaoba' ); ?></label>
            <?php if ( empty( $related_preview_ids ) ) : ?>
                <p><?php esc_html_e( 'No related content found yet. Assign relevant genres and save the post to auto-populate this field.', 'ziaoba' ); ?></p>
            <?php else : ?>
                <ul>
                    <?php foreach ( $related_preview_ids as $related_id ) : ?>
                        <li>
                            <a href="<?php echo esc_url( get_edit_post_link( $related_id ) ); ?>">
                                <?php echo esc_html( get_the_title( $related_id ) ); ?> (#<?php echo esc_html( $related_id ); ?>)
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>

    <div class="ziaoba-panel">
        <div class="ziaoba-field">
            <label><?php esc_html_e( 'Total Views (Read-only)', 'ziaoba' ); ?></label>
            <input type="text" value="<?php echo esc_attr( $meta['views'] ); ?>" readonly style="background:#f0f0f1;">
        </div>
    </div>
    <?php
}

function ziaoba_save_video_meta( $post_id ) {
    if ( ! isset( $_POST['ziaoba_video_meta_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ziaoba_video_meta_nonce_field'] ) ), 'ziaoba_video_meta_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields = array(
        'ziaoba_video_url'         => array( 'meta_key' => '_ziaoba_video_url', 'sanitize' => 'esc_url_raw' ),
        'ziaoba_duration'          => array( 'meta_key' => '_ziaoba_duration', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_age_rating'        => array( 'meta_key' => '_ziaoba_age_rating', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_tmdb_vote_average' => array( 'meta_key' => '_ziaoba_tmdb_vote_average', 'sanitize' => 'floatval' ),
        'ziaoba_tmdb_vote_count'   => array( 'meta_key' => '_ziaoba_tmdb_vote_count', 'sanitize' => 'intval' ),
        'ziaoba_lesson_topic'      => array( 'meta_key' => '_ziaoba_lesson_topic', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_related_content'   => array( 'meta_key' => '_ziaoba_related_content', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_tmdb_id'           => array( 'meta_key' => '_ziaoba_tmdb_id', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_tmdb_type'         => array( 'meta_key' => '_ziaoba_tmdb_type', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_content_type'      => array( 'meta_key' => '_ziaoba_content_type', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_release_date'      => array( 'meta_key' => '_ziaoba_release_date', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_backdrop_url'      => array( 'meta_key' => '_ziaoba_backdrop_url', 'sanitize' => 'esc_url_raw' ),
        'ziaoba_poster_url'        => array( 'meta_key' => '_ziaoba_poster_url', 'sanitize' => 'esc_url_raw' ),
        'ziaoba_original_title'    => array( 'meta_key' => '_ziaoba_original_title', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_tagline'           => array( 'meta_key' => '_ziaoba_tagline', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_status'            => array( 'meta_key' => '_ziaoba_status', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_original_language' => array( 'meta_key' => '_ziaoba_original_language', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_networks'          => array( 'meta_key' => '_ziaoba_networks', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_origin_countries'  => array( 'meta_key' => '_ziaoba_origin_countries', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_trailer_url'       => array( 'meta_key' => '_ziaoba_trailer_url', 'sanitize' => 'esc_url_raw' ),
        'ziaoba_tmdb_last_sync'    => array( 'meta_key' => '_ziaoba_tmdb_last_sync', 'sanitize' => 'sanitize_text_field' ),
        'ziaoba_season'            => array( 'meta_key' => '_ziaoba_season', 'sanitize' => 'intval' ),
        'ziaoba_episode_number'    => array( 'meta_key' => '_ziaoba_episode_number', 'sanitize' => 'intval' ),
        'ziaoba_total_seasons'     => array( 'meta_key' => '_ziaoba_total_seasons', 'sanitize' => 'intval' ),
        'ziaoba_total_episodes'    => array( 'meta_key' => '_ziaoba_total_episodes', 'sanitize' => 'intval' ),
    );

    foreach ( $fields as $key => $config ) {
        if ( isset( $_POST[ $key ] ) ) {
            $raw = wp_unslash( $_POST[ $key ] );
            $value = call_user_func( $config['sanitize'], $raw );
            update_post_meta( $post_id, $config['meta_key'], $value );
        }
    }

    if ( empty( $_POST['ziaoba_related_content'] ) ) {
        ziaoba_sync_related_content( $post_id );
    }
}
