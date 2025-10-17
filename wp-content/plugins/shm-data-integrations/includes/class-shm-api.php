<?php
/**
 * REST API endpoints
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_API {

	private static $namespace = 'shm/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
	}

	public static function register_routes() {
		register_rest_route(
			self::$namespace,
			'/metrics/summary',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_summary' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
				'args'                => array(
					'range'   => array(
						'default'           => '7d',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'metrics' => array(
						'default'           => 'bp,glucose,activity',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::$namespace,
			'/metrics/series',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_series' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
				'args'                => array(
					'metric' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'from'   => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'to'     => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::$namespace,
			'/metrics/bp',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_bp' ),
				'permission_callback' => array( __CLASS__, 'check_write_permissions' ),
				'args'                => array(
					'systolic'  => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) && $param >= 50 && $param <= 250;
						},
					),
					'diastolic' => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) && $param >= 30 && $param <= 150;
						},
					),
					'pulse'     => array(
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) && $param >= 40 && $param <= 200;
						},
					),
					'taken_at'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'notes'     => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);

		register_rest_route(
			self::$namespace,
			'/metrics/glucose',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_glucose' ),
				'permission_callback' => array( __CLASS__, 'check_write_permissions' ),
				'args'                => array(
					'value'    => array(
						'required'          => true,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) && $param >= 20 && $param <= 600;
						},
					),
					'state'    => array(
						'default'           => 'random',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'taken_at' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'notes'    => array(
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);

		register_rest_route(
			self::$namespace,
			'/metrics/activity',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'create_activity' ),
				'permission_callback' => array( __CLASS__, 'check_write_permissions' ),
				'args'                => array(
					'steps'          => array(
						'default' => 0,
					),
					'calories'       => array(
						'default' => 0,
					),
					'hr_avg'         => array(),
					'distance'       => array(),
					'active_minutes' => array(
						'default' => 0,
					),
					'taken_at'       => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			self::$namespace,
			'/alerts',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'get_alerts' ),
				'permission_callback' => array( __CLASS__, 'check_permissions' ),
				'args'                => array(
					'resolved' => array(
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);

		register_rest_route(
			self::$namespace,
			'/alerts/(?P<id>\d+)/resolve',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'resolve_alert' ),
				'permission_callback' => array( __CLASS__, 'check_write_permissions' ),
			)
		);
	}

	public static function check_permissions( $request ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user_id = get_current_user_id();
		$target_user_id = $request->get_param( 'user_id' ) ?: $user_id;

		return SHM_Roles::can_view_user_data( $user_id, $target_user_id );
	}

	public static function check_write_permissions( $request ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		if ( ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return false;
		}

		$user_id = get_current_user_id();
		$target_user_id = $request->get_param( 'user_id' ) ?: $user_id;

		return SHM_Roles::can_manage_user_data( $user_id, $target_user_id );
	}

	public static function get_summary( $request ) {
		$user_id = $request->get_param( 'user_id' ) ?: get_current_user_id();
		$range   = $request->get_param( 'range' );
		$metrics = explode( ',', $request->get_param( 'metrics' ) );

		$summary = SHM_Database::get_summary( $user_id, $range );

		$filtered = array();
		foreach ( $metrics as $metric ) {
			if ( isset( $summary[ trim( $metric ) ] ) ) {
				$filtered[ trim( $metric ) ] = $summary[ trim( $metric ) ];
			}
		}

		return rest_ensure_response( $filtered );
	}

	public static function get_series( $request ) {
		$user_id = $request->get_param( 'user_id' ) ?: get_current_user_id();
		$metric  = $request->get_param( 'metric' );
		$from    = $request->get_param( 'from' );
		$to      = $request->get_param( 'to' );

		$cache_key = "shm_series_{$user_id}_{$metric}_{$from}_{$to}";
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return rest_ensure_response( $cached );
		}

		$data = array();

		switch ( $metric ) {
			case 'bp':
				$data = SHM_Database::get_bp_data( $user_id, $from, $to );
				break;
			case 'glucose':
				$data = SHM_Database::get_glucose_data( $user_id, $from, $to );
				break;
			case 'activity':
				$data = SHM_Database::get_activity_data( $user_id, $from, $to );
				break;
		}

		set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );

		return rest_ensure_response( $data );
	}

	public static function create_bp( $request ) {
		$user_id = get_current_user_id();
		$data    = $request->get_params();

		$id = SHM_Database::insert_bp( $user_id, $data );

		if ( $id ) {
			SHM_Database::log_audit( $user_id, 'create', 'bp', $id, null, $data );
			return rest_ensure_response( array( 'id' => $id, 'success' => true ) );
		}

		return new WP_Error( 'create_failed', __( 'Failed to create blood pressure record', 'shm-data-integrations' ), array( 'status' => 500 ) );
	}

	public static function create_glucose( $request ) {
		$user_id = get_current_user_id();
		$data    = $request->get_params();

		$id = SHM_Database::insert_glucose( $user_id, $data );

		if ( $id ) {
			SHM_Database::log_audit( $user_id, 'create', 'glucose', $id, null, $data );
			return rest_ensure_response( array( 'id' => $id, 'success' => true ) );
		}

		return new WP_Error( 'create_failed', __( 'Failed to create glucose record', 'shm-data-integrations' ), array( 'status' => 500 ) );
	}

	public static function create_activity( $request ) {
		$user_id = get_current_user_id();
		$data    = $request->get_params();

		$result = SHM_Database::insert_activity( $user_id, $data );

		if ( $result ) {
			SHM_Database::log_audit( $user_id, 'create', 'activity', null, null, $data );
			return rest_ensure_response( array( 'success' => true ) );
		}

		return new WP_Error( 'create_failed', __( 'Failed to create activity record', 'shm-data-integrations' ), array( 'status' => 500 ) );
	}

	public static function get_alerts( $request ) {
		$user_id  = $request->get_param( 'user_id' ) ?: get_current_user_id();
		$resolved = $request->get_param( 'resolved' );

		$alerts = SHM_Database::get_alerts( $user_id, $resolved );

		return rest_ensure_response( $alerts );
	}

	public static function resolve_alert( $request ) {
		$alert_id    = $request->get_param( 'id' );
		$resolver_id = get_current_user_id();

		$result = SHM_Database::resolve_alert( $alert_id, $resolver_id );

		if ( $result ) {
			return rest_ensure_response( array( 'success' => true ) );
		}

		return new WP_Error( 'resolve_failed', __( 'Failed to resolve alert', 'shm-data-integrations' ), array( 'status' => 500 ) );
	}
}
