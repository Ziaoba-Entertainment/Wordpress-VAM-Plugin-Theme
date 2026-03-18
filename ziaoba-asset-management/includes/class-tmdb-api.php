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

	public function search( $query, $type = 'multi' ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'TMDB API key is missing.', 'ziaoba' ) );
		}

		return $this->make_request(
			"search/{$type}",
			array(
				'query'    => $query,
				'api_key'  => $this->api_key,
				'language' => 'en-US',
			)
		);
	}

	public function get_details( $id, $type ) {
		if ( empty( $this->api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'TMDB API key is missing.', 'ziaoba' ) );
		}

		return $this->make_request(
			"{$type}/{$id}",
			array(
				'api_key'            => $this->api_key,
				'language'           => 'en-US',
				'append_to_response' => 'credits,content_ratings,release_dates,videos,images,external_ids',
			)
		);
	}

	private function make_request( $endpoint, $params ) {
		$url      = add_query_arg( $params, $this->base_url . $endpoint );
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 20,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['status_code'] ) && $data['status_code'] > 1 ) {
			return new WP_Error( 'tmdb_api_error', $data['status_message'] );
		}

		return is_array( $data ) ? $data : array();
	}
}
