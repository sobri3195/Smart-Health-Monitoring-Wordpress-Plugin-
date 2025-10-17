<?php
/**
 * Base connector class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class SHM_Connector_Base {

	protected $name;
	protected $label;

	abstract public function get_auth_url( $user_id );

	abstract public function handle_callback( $code, $user_id );

	abstract public function sync( $user_id, $access_token, $connection );

	abstract public function refresh_token( $refresh_token );

	public function get_name() {
		return $this->name;
	}

	public function get_label() {
		return $this->label;
	}

	protected function make_request( $url, $access_token, $method = 'GET', $body = null ) {
		$args = array(
			'method'  => $method,
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Accept'        => 'application/json',
			),
			'timeout' => 30,
		);

		if ( $body && in_array( $method, array( 'POST', 'PUT' ) ) ) {
			$args['body'] = is_array( $body ) ? wp_json_encode( $body ) : $body;
			$args['headers']['Content-Type'] = 'application/json';
		}

		$response = wp_remote_request( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code >= 400 ) {
			throw new Exception( 'API Error: ' . $code . ' - ' . $body );
		}

		return json_decode( $body, true );
	}
}
