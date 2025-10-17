<?php
/**
 * Fitbit connector
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHM_PLUGIN_DIR . 'includes/connectors/class-shm-connector-base.php';

class SHM_Connector_Fitbit extends SHM_Connector_Base {

	protected $name = 'fitbit';
	protected $label = 'Fitbit';

	private $client_id;
	private $client_secret;
	private $api_base = 'https://api.fitbit.com/1/user/-/';

	public function __construct() {
		$this->client_id     = get_option( 'shm_fitbit_client_id', '' );
		$this->client_secret = get_option( 'shm_fitbit_client_secret', '' );
	}

	public function get_auth_url( $user_id ) {
		$redirect_uri = admin_url( 'admin-ajax.php?action=shm_fitbit_callback' );
		$state        = wp_create_nonce( 'shm_fitbit_' . $user_id );

		return add_query_arg(
			array(
				'client_id'     => $this->client_id,
				'response_type' => 'code',
				'scope'         => 'activity heartrate sleep profile',
				'redirect_uri'  => urlencode( $redirect_uri ),
				'state'         => $state,
			),
			'https://www.fitbit.com/oauth2/authorize'
		);
	}

	public function handle_callback( $code, $user_id ) {
		$redirect_uri = admin_url( 'admin-ajax.php?action=shm_fitbit_callback' );

		$response = wp_remote_post(
			'https://api.fitbit.com/oauth2/token',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'client_id'    => $this->client_id,
					'grant_type'   => 'authorization_code',
					'code'         => $code,
					'redirect_uri' => $redirect_uri,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $data['access_token'] ) ) {
			$expires_at = date( 'Y-m-d H:i:s', time() + $data['expires_in'] );

			SHM_Connectors::save_connection(
				$user_id,
				'fitbit',
				$data['access_token'],
				$data['refresh_token'],
				$expires_at,
				$data['user_id']
			);

			return true;
		}

		return false;
	}

	public function sync( $user_id, $access_token, $connection ) {
		$counts = array(
			'activity' => 0,
			'hr'       => 0,
		);

		$today = date( 'Y-m-d' );
		$week_ago = date( 'Y-m-d', strtotime( '-7 days' ) );

		try {
			$activity_data = $this->make_request(
				$this->api_base . 'activities/date/' . $today . '.json',
				$access_token
			);

			if ( isset( $activity_data['summary'] ) ) {
				$summary = $activity_data['summary'];
				SHM_Database::insert_activity(
					$user_id,
					array(
						'steps'          => $summary['steps'] ?? 0,
						'calories'       => $summary['caloriesOut'] ?? 0,
						'distance'       => isset( $summary['distances'][0]['distance'] ) ? $summary['distances'][0]['distance'] : 0,
						'active_minutes' => $summary['veryActiveMinutes'] ?? 0,
						'taken_at'       => $today,
						'source'         => 'fitbit',
					)
				);
				$counts['activity']++;
			}

			$hr_data = $this->make_request(
				$this->api_base . 'activities/heart/date/' . $today . '/1d.json',
				$access_token
			);

			if ( isset( $hr_data['activities-heart'][0]['value']['restingHeartRate'] ) ) {
				$hr = $hr_data['activities-heart'][0]['value']['restingHeartRate'];

				global $wpdb;
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}shm_activity SET hr_avg = %d WHERE user_id = %d AND taken_at = %s",
						$hr,
						$user_id,
						$today
					)
				);
				$counts['hr']++;
			}
		} catch ( Exception $e ) {
			error_log( 'Fitbit sync error: ' . $e->getMessage() );
		}

		return $counts;
	}

	public function refresh_token( $refresh_token ) {
		$response = wp_remote_post(
			'https://api.fitbit.com/oauth2/token',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),
					'Content-Type'  => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'grant_type'    => 'refresh_token',
					'refresh_token' => $refresh_token,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $data['access_token'] ) ) {
			return array(
				'access_token'  => $data['access_token'],
				'refresh_token' => $data['refresh_token'],
				'expires_at'    => date( 'Y-m-d H:i:s', time() + $data['expires_in'] ),
			);
		}

		return false;
	}
}
