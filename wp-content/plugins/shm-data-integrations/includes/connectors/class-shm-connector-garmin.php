<?php
/**
 * Garmin connector
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once SHM_PLUGIN_DIR . 'includes/connectors/class-shm-connector-base.php';

class SHM_Connector_Garmin extends SHM_Connector_Base {

	protected $name = 'garmin';
	protected $label = 'Garmin';

	private $consumer_key;
	private $consumer_secret;

	public function __construct() {
		$this->consumer_key    = get_option( 'shm_garmin_consumer_key', '' );
		$this->consumer_secret = get_option( 'shm_garmin_consumer_secret', '' );
	}

	public function get_auth_url( $user_id ) {
		return '#';
	}

	public function handle_callback( $code, $user_id ) {
		return false;
	}

	public function sync( $user_id, $access_token, $connection ) {
		$counts = array(
			'activity' => 0,
		);

		return $counts;
	}

	public function refresh_token( $refresh_token ) {
		return false;
	}
}
