<?php
/**
 * TMDB API Helper Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Ziaoba_TMDB_API {

	private $api_key;
	private $base_url = 'https://api.themoviedb.org/3/';

	public function __construct() {
		$this->api_key = get_option( 'ziaoba_tmdb_api_key' );
	}

	/**
	 * Search for movies, TV shows, or both.
	 *
	 * @param string $query The search query.
	 * @param string $type  The type of search: 'movie', 'tv', or 'multi'.
	 * @return array|WP_Error
	 */
	public function search( $query, $type = 'multi' ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'TMDB API key is missing.', 'ziaoba' ) );
		}

		$endpoint = "search/{$type}";
		$params = array(
			'query'    => urlencode( $query ),
			'api_key'  => $this->api_key,
			'language' => 'en-US',
		);

		return $this->make_request( $endpoint, $params );
	}

	/**
	 * Get full details for a movie or TV show.
	 *
	 * @param int    $id   The TMDB ID.
	 * @param string $type The type: 'movie' or 'tv'.
	 * @return array|WP_Error
	 */
	public function get_details( $id, $type ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'TMDB API key is missing.', 'ziaoba' ) );
		}

		$endpoint = "{$type}/{$id}";
		$params = array(
			'api_key'            => $this->api_key,
			'language'           => 'en-US',
			'append_to_response' => 'credits',
		);

		return $this->make_request( $endpoint, $params );
	}

	/**
	 * Make a request to the TMDB API.
	 *
	 * @param string $endpoint The API endpoint.
	 * @param array  $params   The query parameters.
	 * @return array|WP_Error
	 */
	private function make_request( $endpoint, $params ) {
		$url = add_query_arg( $params, $this->base_url . $endpoint );
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['status_code'] ) && $data['status_code'] > 1 ) {
			return new WP_Error( 'tmdb_api_error', $data['status_message'] );
		}

		return $data;
	}
}
