<?php
/**
 * Wearable connectors manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Connectors {

	private static $connectors = array();

	public static function init() {
		self::register_connectors();
		add_action( 'shm_sync_wearables', array( __CLASS__, 'sync_all_connections' ) );
	}

	private static function register_connectors() {
		self::$connectors['fitbit'] = new SHM_Connector_Fitbit();
		self::$connectors['garmin'] = new SHM_Connector_Garmin();

		self::$connectors = apply_filters( 'shm_register_connectors', self::$connectors );
	}

	public static function get_connector( $name ) {
		return isset( self::$connectors[ $name ] ) ? self::$connectors[ $name ] : null;
	}

	public static function get_all_connectors() {
		return self::$connectors;
	}

	public static function get_user_connection( $user_id, $connector ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}shm_connections WHERE user_id = %d AND connector = %s AND status = 'active'",
				$user_id,
				$connector
			)
		);
	}

	public static function save_connection( $user_id, $connector, $access_token, $refresh_token = null, $expires_at = null, $external_user_id = null ) {
		global $wpdb;

		$encryption_key = self::get_encryption_key();
		$encrypted_access = openssl_encrypt( $access_token, 'AES-256-CBC', $encryption_key, 0, substr( $encryption_key, 0, 16 ) );
		$encrypted_refresh = $refresh_token ? openssl_encrypt( $refresh_token, 'AES-256-CBC', $encryption_key, 0, substr( $encryption_key, 0, 16 ) ) : null;

		return $wpdb->replace(
			$wpdb->prefix . 'shm_connections',
			array(
				'user_id'          => $user_id,
				'connector'        => $connector,
				'access_token'     => $encrypted_access,
				'refresh_token'    => $encrypted_refresh,
				'token_expires_at' => $expires_at,
				'external_user_id' => $external_user_id,
				'status'           => 'active',
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	public static function disconnect( $user_id, $connector ) {
		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . 'shm_connections',
			array( 'status' => 'disconnected' ),
			array(
				'user_id'   => $user_id,
				'connector' => $connector,
			),
			array( '%s' ),
			array( '%d', '%s' )
		);
	}

	public static function decrypt_token( $encrypted_token ) {
		$encryption_key = self::get_encryption_key();
		return openssl_decrypt( $encrypted_token, 'AES-256-CBC', $encryption_key, 0, substr( $encryption_key, 0, 16 ) );
	}

	private static function get_encryption_key() {
		if ( defined( 'SHM_ENCRYPTION_KEY' ) ) {
			return SHM_ENCRYPTION_KEY;
		}

		$key = get_option( 'shm_encryption_key' );
		if ( ! $key ) {
			$key = wp_generate_password( 32, false );
			update_option( 'shm_encryption_key', $key, false );
		}

		return $key;
	}

	public static function sync_user_connection( $user_id, $connector_name ) {
		$connection = self::get_user_connection( $user_id, $connector_name );

		if ( ! $connection ) {
			return false;
		}

		$connector = self::get_connector( $connector_name );

		if ( ! $connector ) {
			return false;
		}

		$access_token = self::decrypt_token( $connection->access_token );

		try {
			$counts = $connector->sync( $user_id, $access_token, $connection );

			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . 'shm_connections',
				array( 'last_sync_at' => current_time( 'mysql' ) ),
				array( 'id' => $connection->id ),
				array( '%s' ),
				array( '%d' )
			);

			do_action( 'shm_after_sync', $user_id, $connector_name, $counts );

			return $counts;
		} catch ( Exception $e ) {
			error_log( 'SHM Sync Error: ' . $e->getMessage() );
			return false;
		}
	}

	public static function sync_all_connections() {
		global $wpdb;

		$connections = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}shm_connections WHERE status = 'active'"
		);

		foreach ( $connections as $connection ) {
			self::sync_user_connection( $connection->user_id, $connection->connector );
		}
	}
}
