<?php
/**
 * TMDB Search UI Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ziaoba_TMDB_Search_UI {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_vam_tmdb_search', array( $this, 'ajax_search' ) );
		add_action( 'wp_ajax_vam_tmdb_import', array( $this, 'ajax_import' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'ziaoba_tmdb_settings', 'ziaoba_tmdb_api_key', 'sanitize_text_field' );
	}

	public function add_menu_item() {
		add_submenu_page(
			'edit.php?post_type=entertainment',
			__( 'TMDB Search', 'ziaoba' ),
			__( 'TMDB Search', 'ziaoba' ),
			'manage_options',
			'ziaoba-tmdb-search',
			array( $this, 'render_page' )
		);
	}

	public function enqueue_scripts( $hook ) {
		if ( 'entertainment_page_ziaoba-tmdb-search' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'ziaoba-tmdb-search-css', ZIAOBA_VAM_URL . 'css/tmdb-search.css', array(), ZIAOBA_VAM_VERSION );
		wp_enqueue_script( 'ziaoba-tmdb-search-js', ZIAOBA_VAM_URL . 'js/tmdb-search.js', array( 'jquery' ), ZIAOBA_VAM_VERSION, true );

		wp_localize_script( 'ziaoba-tmdb-search-js', 'ziaobaTMDB', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ziaoba_tmdb_nonce' ),
			'i18n'    => array(
				'searching' => __( 'Searching...', 'ziaoba' ),
				'importing' => __( 'Importing...', 'ziaoba' ),
				'imported'  => __( 'Imported successfully!', 'ziaoba' ),
				'error'     => __( 'An error occurred.', 'ziaoba' ),
			),
		) );
	}

	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'TMDB Content Search', 'ziaoba' ); ?></h1>

			<div class="card" style="margin-top: 20px; padding: 20px; max-width: 100%;">
				<form method="post" action="options.php" style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
					<?php settings_fields( 'ziaoba_tmdb_settings' ); ?>
					<div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
						<div>
							<label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php esc_html_e( 'TMDB API Key', 'ziaoba' ); ?></label>
							<input type="text" name="ziaoba_tmdb_api_key" value="<?php echo esc_attr( get_option( 'ziaoba_tmdb_api_key' ) ); ?>" style="width: 350px; max-width:100%;">
						</div>
						<?php submit_button( __( 'Save API Key', 'ziaoba' ), 'secondary', 'submit', false ); ?>
					</div>
				</form>

				<div class="search-controls" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
					<div style="flex-grow: 1; min-width: 260px;">
						<label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php esc_html_e( 'Search Title', 'ziaoba' ); ?></label>
						<input type="text" id="tmdb-search-input" style="width: 100%;" placeholder="<?php esc_attr_e( 'Enter movie or TV show title...', 'ziaoba' ); ?>">
					</div>
					<div>
						<label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php esc_html_e( 'Type', 'ziaoba' ); ?></label>
						<select id="tmdb-search-type">
							<option value="multi"><?php esc_html_e( 'Multi', 'ziaoba' ); ?></option>
							<option value="movie"><?php esc_html_e( 'Movie', 'ziaoba' ); ?></option>
							<option value="tv"><?php esc_html_e( 'TV Show', 'ziaoba' ); ?></option>
						</select>
					</div>
					<button type="button" id="tmdb-search-button" class="button button-primary"><?php esc_html_e( 'Search', 'ziaoba' ); ?></button>
				</div>
			</div>

			<div id="tmdb-search-results" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;"></div>
		</div>
		<?php
	}

	public function ajax_search() {
		check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		$type  = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'multi';

		if ( empty( $query ) ) {
			wp_send_json_error( __( 'Query is empty.', 'ziaoba' ) );
		}

		$cache_key = 'ziaoba_tmdb_search_' . md5( $query . $type );
		$results   = get_transient( $cache_key );

		if ( false === $results ) {
			$api      = new Ziaoba_TMDB_API();
			$response = $api->search( $query, $type );

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( $response->get_error_message() );
			}

			$results = isset( $response['results'] ) ? $response['results'] : array();
			set_transient( $cache_key, $results, HOUR_IN_SECONDS );
		}

		wp_send_json_success( $results );
	}

	public function ajax_import() {
		check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';

		if ( ! $id || ! in_array( $type, array( 'movie', 'tv' ), true ) ) {
			wp_send_json_error( __( 'Invalid ID or Type.', 'ziaoba' ) );
		}

		$api     = new Ziaoba_TMDB_API();
		$details = $api->get_details( $id, $type );

		if ( is_wp_error( $details ) ) {
			wp_send_json_error( $details->get_error_message() );
		}

		$title        = isset( $details['title'] ) ? $details['title'] : $details['name'];
		$description  = isset( $details['overview'] ) ? $details['overview'] : '';
		$poster_path  = isset( $details['poster_path'] ) ? $details['poster_path'] : '';
		$backdrop     = isset( $details['backdrop_path'] ) ? $details['backdrop_path'] : '';
		$release_date = isset( $details['release_date'] ) ? $details['release_date'] : ( $details['first_air_date'] ?? '' );
		$runtime      = isset( $details['runtime'] ) ? $details['runtime'] . 'm' : ( ! empty( $details['episode_run_time'][0] ) ? $details['episode_run_time'][0] . 'm' : '' );
		$content_type = ( 'tv' === $type ) ? 'series' : 'movie';

		$post_id = wp_insert_post( array(
			'post_title'   => wp_strip_all_tags( $title ),
			'post_content' => $description,
			'post_excerpt' => $this->build_excerpt( $details ),
			'post_status'  => 'draft',
			'post_type'    => 'entertainment',
		) );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( $post_id->get_error_message() );
		}

		$meta_map = array(
			'_ziaoba_age_rating'         => $this->extract_age_rating( $details, $type ),
			'_ziaoba_duration'           => $runtime,
			'_ziaoba_tmdb_id'            => $id,
			'_ziaoba_tmdb_type'          => $type,
			'_ziaoba_content_type'       => $content_type,
			'_ziaoba_release_date'       => $release_date,
			'_ziaoba_tmdb_vote_average'  => isset( $details['vote_average'] ) ? (float) $details['vote_average'] : '',
			'_ziaoba_tmdb_vote_count'    => isset( $details['vote_count'] ) ? (int) $details['vote_count'] : '',
			'_ziaoba_original_title'     => isset( $details['original_title'] ) ? $details['original_title'] : ( $details['original_name'] ?? '' ),
			'_ziaoba_tagline'            => $details['tagline'] ?? '',
			'_ziaoba_status'             => $details['status'] ?? '',
			'_ziaoba_original_language'  => strtoupper( (string) ( $details['original_language'] ?? '' ) ),
			'_ziaoba_networks'           => $this->implode_names( $details['networks'] ?? $details['production_companies'] ?? array() ),
			'_ziaoba_origin_countries'   => $this->implode_country_codes( $details['origin_country'] ?? array() ),
			'_ziaoba_total_seasons'      => isset( $details['number_of_seasons'] ) ? (int) $details['number_of_seasons'] : '',
			'_ziaoba_total_episodes'     => isset( $details['number_of_episodes'] ) ? (int) $details['number_of_episodes'] : '',
			'_ziaoba_trailer_url'        => $this->extract_trailer_url( $details ),
			'_ziaoba_tmdb_last_sync'     => current_time( 'mysql' ),
		);

		if ( $backdrop ) {
			$meta_map['_ziaoba_backdrop_url'] = 'https://image.tmdb.org/t/p/original' . $backdrop;
		}
		if ( $poster_path ) {
			$meta_map['_ziaoba_poster_url'] = 'https://image.tmdb.org/t/p/original' . $poster_path;
		}

		foreach ( $meta_map as $meta_key => $value ) {
			if ( '' !== $value && null !== $value ) {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}

		if ( $poster_path ) {
			$this->set_featured_image_from_url( $post_id, 'https://image.tmdb.org/t/p/w780' . $poster_path, $title );
		}

		$genre_ids = array();
		if ( ! empty( $details['genres'] ) ) {
			foreach ( $details['genres'] as $genre ) {
				if ( empty( $genre['name'] ) ) {
					continue;
				}

				$term = get_term_by( 'name', $genre['name'], 'genre' );
				if ( ! $term ) {
					$term = wp_insert_term( $genre['name'], 'genre' );
				}

				if ( ! is_wp_error( $term ) ) {
					$genre_ids[] = is_array( $term ) ? $term['term_id'] : $term->term_id;
				}
			}
		}

		if ( $genre_ids ) {
			wp_set_post_terms( $post_id, $genre_ids, 'genre' );
		}

		if ( function_exists( 'ziaoba_sync_related_content' ) ) {
			ziaoba_sync_related_content( $post_id );
		}

		wp_send_json_success( array(
			'post_id'  => $post_id,
			'edit_url' => get_edit_post_link( $post_id, 'raw' ),
		) );
	}

	private function build_excerpt( $details ) {
		$bits = array_filter( array(
			$details['tagline'] ?? '',
			isset( $details['vote_average'] ) ? sprintf( 'TMDB %.1f/10', (float) $details['vote_average'] ) : '',
			! empty( $details['genres'] ) ? implode( ', ', wp_list_pluck( $details['genres'], 'name' ) ) : '',
		) );

		return implode( ' • ', array_slice( $bits, 0, 3 ) );
	}

	private function extract_age_rating( $details, $type ) {
		if ( 'movie' === $type && ! empty( $details['release_dates']['results'] ) ) {
			foreach ( $details['release_dates']['results'] as $country ) {
				if ( 'US' === ( $country['iso_3166_1'] ?? '' ) && ! empty( $country['release_dates'] ) ) {
					foreach ( $country['release_dates'] as $release ) {
						if ( ! empty( $release['certification'] ) ) {
							return $release['certification'];
						}
					}
				}
			}
		}

		if ( 'tv' === $type && ! empty( $details['content_ratings']['results'] ) ) {
			foreach ( $details['content_ratings']['results'] as $rating ) {
				if ( 'US' === ( $rating['iso_3166_1'] ?? '' ) && ! empty( $rating['rating'] ) ) {
					return $rating['rating'];
				}
			}
		}

		return '';
	}

	private function extract_trailer_url( $details ) {
		if ( empty( $details['videos']['results'] ) ) {
			return '';
		}

		foreach ( $details['videos']['results'] as $video ) {
			if ( 'YouTube' === ( $video['site'] ?? '' ) && 'Trailer' === ( $video['type'] ?? '' ) && ! empty( $video['key'] ) ) {
				return 'https://www.youtube.com/watch?v=' . rawurlencode( $video['key'] );
			}
		}

		return '';
	}

	private function implode_names( $items ) {
		if ( empty( $items ) || ! is_array( $items ) ) {
			return '';
		}

		return implode( ', ', array_filter( wp_list_pluck( $items, 'name' ) ) );
	}

	private function implode_country_codes( $items ) {
		if ( empty( $items ) || ! is_array( $items ) ) {
			return '';
		}

		return implode( ', ', array_filter( array_map( 'sanitize_text_field', $items ) ) );
	}

	private function set_featured_image_from_url( $post_id, $image_url, $title ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			return;
		}

		$file_array = array(
			'name'     => sanitize_title( $title ) . '.jpg',
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload( $file_array, $post_id, $title );
		if ( ! is_wp_error( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}

		@unlink( $tmp );
	}
}
