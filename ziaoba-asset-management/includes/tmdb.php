<?php
/**
 * TMDB API Integration and Ingestion
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TMDB {

    private static $api_key = '';
    private static $api_url = 'https://api.themoviedb.org/3';

    /**
     * Initialize TMDB
     */
    public static function init() {
        $instance = new self();
        $settings = get_option( 'ziaoba_monetization_settings', array() );
        self::$api_key = $settings['tmdb_api_key'] ?? '';

        add_action( 'add_meta_boxes', array( $instance, 'add_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( $instance, 'admin_scripts' ) );
        
        // AJAX Handlers
        add_action( 'wp_ajax_ziaoba_tmdb_search', array( $instance, 'ajax_search' ) );
        add_action( 'wp_ajax_ziaoba_tmdb_import', array( $instance, 'ajax_import' ) );
        add_action( 'wp_ajax_ziaoba_tmdb_fetch_seasons', array( $instance, 'ajax_fetch_seasons' ) );
        add_action( 'wp_ajax_ziaoba_tmdb_fetch_episodes', array( $instance, 'ajax_fetch_episodes' ) );
    }

    /**
     * Enqueue Admin Scripts
     */
    public function admin_scripts( $hook ) {
        if ( ! in_array( get_post_type(), array( 'entertainment', 'series', 'season', 'episode' ) ) ) {
            return;
        }

        wp_enqueue_style( 'ziaoba-tmdb-admin', ZIAOBA_VAM_URL . 'css/tmdb-admin.css', array(), ZIAOBA_VAM_VERSION );
        wp_enqueue_script( 'ziaoba-tmdb-admin', ZIAOBA_VAM_URL . 'js/tmdb-admin.js', array( 'jquery' ), ZIAOBA_VAM_VERSION, true );
        
        wp_localize_script( 'ziaoba-tmdb-admin', 'ziaobaTMDB', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ziaoba_tmdb_nonce' ),
            'apiKey'  => self::$api_key,
        ) );
    }

    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'ziaoba_tmdb_ingestion',
            __( 'TMDB Ingestion', 'ziaoba-asset-management' ),
            array( $this, 'render_meta_box' ),
            array( 'entertainment', 'series', 'season', 'episode' ),
            'side',
            'high'
        );
    }

    /**
     * Render Meta Box
     */
    public function render_meta_box( $post ) {
        if ( ! self::$api_key ) {
            echo '<p class="error">' . __( 'Please set your TMDB API Key in Monetization settings.', 'ziaoba-asset-management' ) . '</p>';
            return;
        }

        $tmdb_id = get_post_meta( $post->ID, '_ziaoba_tmdb_id', true );
        ?>
        <div class="ziaoba-tmdb-wrap">
            <?php if ( in_array( $post->post_type, array( 'entertainment', 'series' ) ) ) : ?>
                <div class="tmdb-search-box">
                    <input type="text" id="tmdb-search-query" placeholder="<?php _e( 'Search TMDB...', 'ziaoba-asset-management' ); ?>" style="width: 100%; margin-bottom: 10px;">
                    <button type="button" id="tmdb-search-btn" class="button button-secondary" style="width: 100%;"><?php _e( 'Search', 'ziaoba-asset-management' ); ?></button>
                </div>
                <div id="tmdb-search-results" style="margin-top: 15px; max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 5px; display: none;"></div>
            <?php endif; ?>

            <div class="tmdb-status" style="margin-top: 15px;">
                <p><strong><?php _e( 'Current TMDB ID:', 'ziaoba-asset-management' ); ?></strong> <span id="current-tmdb-id"><?php echo esc_html( $tmdb_id ?: 'N/A' ); ?></span></p>
                <?php if ( $post->post_type === 'series' && $tmdb_id ) : ?>
                    <button type="button" id="tmdb-fetch-seasons" class="button button-primary" style="width: 100%; margin-top: 10px;"><?php _e( 'Fetch Seasons', 'ziaoba-asset-management' ); ?></button>
                <?php endif; ?>
                <?php if ( $post->post_type === 'season' && $tmdb_id ) : ?>
                    <button type="button" id="tmdb-fetch-episodes" class="button button-primary" style="width: 100%; margin-top: 10px;"><?php _e( 'Fetch Episodes', 'ziaoba-asset-management' ); ?></button>
                <?php endif; ?>
            </div>
            
            <div id="tmdb-loader" style="display:none; text-align:center; margin-top:10px;">
                <span class="spinner is-active" style="float:none;"></span>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX Search
     */
    public function ajax_search() {
        check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );
        
        $query = sanitize_text_field( $_GET['query'] );
        $post_id = intval( $_GET['post_id'] );
        $post_type = get_post_type( $post_id );
        
        // Education and Entertainment (Movies) use 'movie' search, Series uses 'tv'
        $type = ( $post_type === 'series' ) ? 'tv' : 'movie';
        
        $response = wp_remote_get( self::$api_url . "/search/{$type}?api_key=" . self::$api_key . "&query=" . urlencode( $query ) );
        
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( 'API Error' );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        wp_send_json_success( $data['results'] ?? [] );
    }

    /**
     * AJAX Import
     */
    public function ajax_import() {
        check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );
        
        $post_id = intval( $_POST['post_id'] );
        $tmdb_id = intval( $_POST['tmdb_id'] );
        $post_type = get_post_type( $post_id );
        $type    = ( $post_type === 'series' ) ? 'tv' : 'movie';
        
        // Append videos and credits for trailers and cast/crew
        $response = wp_remote_get( self::$api_url . "/{$type}/{$tmdb_id}?api_key=" . self::$api_key . "&append_to_response=release_dates,content_ratings,videos,credits" );
        
        if ( is_wp_error( $response ) ) {
            wp_send_json_error( 'API Error' );
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( ! $data ) {
            wp_send_json_error( 'Invalid Data' );
        }

        // Update Post
        $post_data = array(
            'ID'           => $post_id,
            'post_title'   => $data['title'] ?? $data['name'],
            'post_content' => $data['overview'],
        );
        wp_update_post( $post_data );

        // Update Meta
        update_post_meta( $post_id, '_tmdb_id', $tmdb_id );
        update_post_meta( $post_id, '_poster', $data['poster_path'] );
        update_post_meta( $post_id, '_backdrop', $data['backdrop_path'] );
        update_post_meta( $post_id, '_rating', $data['vote_average'] );
        
        // Release Date
        $release_date = $data['release_date'] ?? $data['first_air_date'] ?? '';
        if ( $release_date ) {
            update_post_meta( $post_id, '_ziaoba_release_date', $release_date );
        }

        // Cast & Director
        if ( isset( $data['credits'] ) ) {
            $cast = array_slice( wp_list_pluck( $data['credits']['cast'], 'name' ), 0, 5 );
            update_post_meta( $post_id, '_ziaoba_cast', implode( ', ', $cast ) );

            $directors = array();
            foreach ( $data['credits']['crew'] as $crew ) {
                if ( $crew['job'] === 'Director' ) {
                    $directors[] = $crew['name'];
                }
            }
            if ( $directors ) {
                update_post_meta( $post_id, '_ziaoba_director', implode( ', ', $directors ) );
            }
        }

        // Duration
        if ( isset( $data['runtime'] ) ) {
            $hours = floor( $data['runtime'] / 60 );
            $mins = $data['runtime'] % 60;
            $duration = ( $hours > 0 ? $hours . 'h ' : '' ) . $mins . 'm';
            update_post_meta( $post_id, '_ziaoba_duration', $duration );
        }

        // Trailer Extraction (YouTube)
        if ( isset( $data['videos']['results'] ) ) {
            foreach ( $data['videos']['results'] as $video ) {
                if ( $video['site'] === 'YouTube' && ( $video['type'] === 'Trailer' || $video['type'] === 'Teaser' ) ) {
                    update_post_meta( $post_id, '_trailer', $video['key'] );
                    break;
                }
            }
        }

        // Age Rating Extraction
        $age_rating = $this->extract_certification( $data, $type );
        if ( $age_rating ) {
            update_post_meta( $post_id, '_age_rating', $age_rating );
        }

        // Genres
        if ( isset( $data['genres'] ) ) {
            $genre_names = wp_list_pluck( $data['genres'], 'name' );
            wp_set_object_terms( $post_id, $genre_names, 'genre' );
        }

        // Poster
        if ( isset( $data['poster_path'] ) ) {
            $poster_url = 'https://image.tmdb.org/t/p/original' . $data['poster_path'];
            $this->set_featured_image( $post_id, $poster_url );
        }

        // Backdrop
        if ( isset( $data['backdrop_path'] ) ) {
            $backdrop_url = 'https://image.tmdb.org/t/p/original' . $data['backdrop_path'];
            update_post_meta( $post_id, '_ziaoba_backdrop_url', $backdrop_url );
        }

        wp_send_json_success( array( 'message' => 'Imported successfully' ) );
    }

    /**
     * AJAX Fetch Seasons
     */
    public function ajax_fetch_seasons() {
        check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );
        
        $series_id = intval( $_POST['post_id'] );
        $tmdb_id   = get_post_meta( $series_id, '_ziaoba_tmdb_id', true );
        
        if ( ! $tmdb_id ) {
            wp_send_json_error( 'No TMDB ID' );
        }

        $response = wp_remote_get( self::$api_url . "/tv/{$tmdb_id}?api_key=" . self::$api_key );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( empty( $data['seasons'] ) ) {
            wp_send_json_error( 'No seasons found' );
        }

        $imported = 0;
        foreach ( $data['seasons'] as $season_data ) {
            if ( $season_data['season_number'] === 0 ) continue; // Skip specials usually

            // Check if already exists
            $existing = get_posts( array(
                'post_type'  => 'season',
                'meta_query' => array(
                    array( 'key' => '_ziaoba_series_id', 'value' => $series_id ),
                    array( 'key' => '_ziaoba_season_number', 'value' => $season_data['season_number'] ),
                ),
            ) );

            if ( $existing ) continue;

            $season_post_id = wp_insert_post( array(
                'post_type'   => 'season',
                'post_title'  => $season_data['name'],
                'post_status' => 'publish',
            ) );

            update_post_meta( $season_post_id, '_ziaoba_series_id', $series_id );
            update_post_meta( $season_post_id, '_ziaoba_season_number', $season_data['season_number'] );
            update_post_meta( $season_post_id, '_ziaoba_tmdb_id', $season_data['id'] );
            
            if ( $season_data['poster_path'] ) {
                $poster_url = 'https://image.tmdb.org/t/p/original' . $season_data['poster_path'];
                $this->set_featured_image( $season_post_id, $poster_url );
            }
            $imported++;
        }

        wp_send_json_success( array( 'message' => "Imported {$imported} seasons" ) );
    }

    /**
     * AJAX Fetch Episodes
     */
    public function ajax_fetch_episodes() {
        check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );
        
        $season_post_id = intval( $_POST['post_id'] );
        $season_num     = get_post_meta( $season_post_id, '_ziaoba_season_number', true );
        $series_id      = get_post_meta( $season_post_id, '_ziaoba_series_id', true );
        $series_tmdb_id = get_post_meta( $series_id, '_ziaoba_tmdb_id', true );
        
        if ( ! $series_tmdb_id || ! $season_num ) {
            wp_send_json_error( 'Missing parent data' );
        }

        $response = wp_remote_get( self::$api_url . "/tv/{$series_tmdb_id}/season/{$season_num}?api_key=" . self::$api_key );
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        
        if ( empty( $data['episodes'] ) ) {
            wp_send_json_error( 'No episodes found' );
        }

        $imported = 0;
        foreach ( $data['episodes'] as $ep_data ) {
            // Check if already exists
            $existing = get_posts( array(
                'post_type'  => 'episode',
                'meta_query' => array(
                    array( 'key' => '_ziaoba_season_id', 'value' => $season_post_id ),
                    array( 'key' => '_ziaoba_episode_number', 'value' => $ep_data['episode_number'] ),
                ),
            ) );

            if ( $existing ) continue;

            $ep_post_id = wp_insert_post( array(
                'post_type'    => 'episode',
                'post_title'   => $ep_data['name'],
                'post_content' => $ep_data['overview'],
                'post_status'  => 'publish',
            ) );

            update_post_meta( $ep_post_id, '_ziaoba_season_id', $season_post_id );
            update_post_meta( $ep_post_id, '_ziaoba_episode_number', $ep_data['episode_number'] );
            update_post_meta( $ep_post_id, '_ziaoba_tmdb_id', $ep_data['id'] );
            
            if ( $ep_data['still_path'] ) {
                $still_url = 'https://image.tmdb.org/t/p/original' . $ep_data['still_path'];
                update_post_meta( $ep_post_id, '_ziaoba_backdrop_url', $still_url );
                $this->set_featured_image( $ep_post_id, $still_url );
            }
            $imported++;
        }

        wp_send_json_success( array( 'message' => "Imported {$imported} episodes" ) );
    }

    /**
     * Extract Certification
     */
    private function extract_certification( $data, $type ) {
        $age_rating = '';
        if ( $type === 'movie' && isset( $data['release_dates']['results'] ) ) {
            foreach ( $data['release_dates']['results'] as $res ) {
                if ( $res['iso_3166_1'] === 'US' ) {
                    $age_rating = $res['release_dates'][0]['certification'];
                    break;
                }
            }
        } elseif ( $type === 'tv' && isset( $data['content_ratings']['results'] ) ) {
            foreach ( $data['content_ratings']['results'] as $res ) {
                if ( $res['iso_3166_1'] === 'US' ) {
                    $age_rating = $res['rating'];
                    break;
                }
            }
        }
        return $age_rating;
    }

    /**
     * Set Featured Image from URL
     */
    private function set_featured_image( $post_id, $url ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $attachment_id = media_sideload_image( $url, $post_id, null, 'id' );
        if ( ! is_wp_error( $attachment_id ) ) {
            set_post_thumbnail( $post_id, $attachment_id );
        }
    }
}
