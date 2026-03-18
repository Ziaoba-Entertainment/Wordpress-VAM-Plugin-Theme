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

	/**
	 * Register TMDB API Key setting.
	 */
	public function register_settings() {
		register_setting( 'ziaoba_tmdb_settings', 'ziaoba_tmdb_api_key', 'sanitize_text_field' );
	}

	/**
	 * Add TMDB Search menu item.
	 */
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

	/**
	 * Enqueue scripts and styles.
	 */
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

	/**
	 * Render the TMDB Search page.
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'TMDB Content Search', 'ziaoba' ); ?></h1>

			<div class="card" style="margin-top: 20px; padding: 20px; max-width: 100%;">
				<form method="post" action="options.php" style="margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 20px;">
					<?php settings_fields( 'ziaoba_tmdb_settings' ); ?>
					<div style="display: flex; gap: 20px; align-items: flex-end;">
						<div>
							<label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e( 'TMDB API Key', 'ziaoba' ); ?></label>
							<input type="text" name="ziaoba_tmdb_api_key" value="<?php echo esc_attr( get_option( 'ziaoba_tmdb_api_key' ) ); ?>" style="width: 350px;">
						</div>
						<?php submit_button( __( 'Save API Key', 'ziaoba' ), 'secondary', 'submit', false ); ?>
					</div>
				</form>

				<div class="search-controls" style="display: flex; gap: 10px; align-items: flex-end;">
					<div style="flex-grow: 1;">
						<label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e( 'Search Title', 'ziaoba' ); ?></label>
						<input type="text" id="tmdb-search-input" style="width: 100%;" placeholder="<?php _e( 'Enter movie or TV show title...', 'ziaoba' ); ?>">
					</div>
					<div>
						<label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e( 'Type', 'ziaoba' ); ?></label>
						<select id="tmdb-search-type">
							<option value="multi"><?php _e( 'Multi', 'ziaoba' ); ?></option>
							<option value="movie"><?php _e( 'Movie', 'ziaoba' ); ?></option>
							<option value="tv"><?php _e( 'TV Show', 'ziaoba' ); ?></option>
						</select>
					</div>
					<button type="button" id="tmdb-search-button" class="button button-primary"><?php _e( 'Search', 'ziaoba' ); ?></button>
				</div>
			</div>

			<div id="tmdb-search-results" style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
				<!-- Results will be loaded here via AJAX -->
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX Search Handler.
	 */
	public function ajax_search() {
		check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );

		$query = isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '';
		$type  = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : 'multi';

		if ( empty( $query ) ) {
			wp_send_json_error( __( 'Query is empty.', 'ziaoba' ) );
		}

		$cache_key = 'ziaoba_tmdb_search_' . md5( $query . $type );
		$results = get_transient( $cache_key );

		if ( false === $results ) {
			$api = new Ziaoba_TMDB_API();
			$response = $api->search( $query, $type );

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( $response->get_error_message() );
			}

			$results = $response['results'];
			set_transient( $cache_key, $results, HOUR_IN_SECONDS );
		}

		wp_send_json_success( $results );
	}

	/**
	 * AJAX Import Handler.
	 */
	public function ajax_import() {
		check_ajax_referer( 'ziaoba_tmdb_nonce', 'nonce' );

		$id   = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$type = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';

		if ( ! $id || ! $type ) {
			wp_send_json_error( __( 'Invalid ID or Type.', 'ziaoba' ) );
		}

		$api = new Ziaoba_TMDB_API();
		$details = $api->get_details( $id, $type );

		if ( is_wp_error( $details ) ) {
			wp_send_json_error( $details->get_error_message() );
		}

		// Map fields to existing data structures
		$title       = isset( $details['title'] ) ? $details['title'] : $details['name'];
		$description = $details['overview'];
		$poster_path = $details['poster_path'];
		$backdrop_path = $details['backdrop_path'];
		$release_date = isset( $details['release_date'] ) ? $details['release_date'] : $details['first_air_date'];
		$rating      = isset( $details['vote_average'] ) ? $details['vote_average'] : 0;
		$runtime     = isset( $details['runtime'] ) ? $details['runtime'] . 'm' : ( isset( $details['episode_run_time'][0] ) ? $details['episode_run_time'][0] . 'm' : '' );
		
		// Create post
		$post_type = ( $type === 'movie' ) ? 'entertainment' : 'entertainment'; // Default to entertainment for now
		
		$post_data = array(
			'post_title'   => $title,
			'post_content' => $description,
			'post_status'  => 'draft',
			'post_type'    => $post_type,
		);

		$post_id = wp_insert_post( $post_data );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( $post_id->get_error_message() );
		}

		// Save meta data
		update_post_meta( $post_id, '_ziaoba_age_rating', $rating );
		update_post_meta( $post_id, '_ziaoba_duration', $runtime );
		update_post_meta( $post_id, '_ziaoba_tmdb_id', $id );
		update_post_meta( $post_id, '_ziaoba_tmdb_type', $type );
		update_post_meta( $post_id, '_ziaoba_release_date', $release_date );
		
		if ( $backdrop_path ) {
			update_post_meta( $post_id, '_ziaoba_backdrop_url', 'https://image.tmdb.org/t/p/original' . $backdrop_path );
		}

		// Handle Poster (Featured Image)
		if ( $poster_path ) {
			$image_url = 'https://image.tmdb.org/t/p/w500' . $poster_path;
			$this->set_featured_image_from_url( $post_id, $image_url, $title );
		}

		// Handle Genres
		if ( ! empty( $details['genres'] ) ) {
			$genre_names = wp_list_pluck( $details['genres'], 'name' );
			$term_ids = array();
			foreach ( $genre_names as $genre_name ) {
				$term = get_term_by( 'name', $genre_name, 'genre' );
				if ( ! $term ) {
					$term = wp_insert_term( $genre_name, 'genre' );
				}
				if ( ! is_wp_error( $term ) ) {
					$term_ids[] = is_array( $term ) ? $term['term_id'] : $term->term_id;
				}
			}
			wp_set_post_terms( $post_id, $term_ids, 'genre' );
		}

		wp_send_json_success( array(
			'post_id' => $post_id,
			'edit_url' => get_edit_post_link( $post_id, 'raw' ),
		) );
	}

	/**
	 * Set featured image from URL.
	 */
	private function set_featured_image_from_url( $post_id, $image_url, $title ) {
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$tmp = download_url( $image_url );
		if ( is_wp_error( $tmp ) ) {
			return;
		}

		$file_array = array(
			'name'     => sanitize_title( $title ) . '.jpg',
			'tmp_name' => $tmp,
		);

		$id = media_handle_sideload( $file_array, $post_id, $title );

		if ( ! is_wp_error( $id ) ) {
			set_post_thumbnail( $post_id, $id );
		}

		@unlink( $tmp );
	}
}
