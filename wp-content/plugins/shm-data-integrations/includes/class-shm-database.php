<?php
/**
 * Database abstraction layer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Database {

	public static function init() {
		// Initialization if needed
	}

	public static function insert_bp( $user_id, $data ) {
		global $wpdb;

		$data = wp_parse_args(
			$data,
			array(
				'systolic'  => 0,
				'diastolic' => 0,
				'pulse'     => null,
				'taken_at'  => current_time( 'mysql' ),
				'source'    => 'manual',
				'notes'     => '',
			)
		);

		$result = $wpdb->insert(
			$wpdb->prefix . 'shm_bp',
			array(
				'user_id'   => $user_id,
				'systolic'  => absint( $data['systolic'] ),
				'diastolic' => absint( $data['diastolic'] ),
				'pulse'     => ! empty( $data['pulse'] ) ? absint( $data['pulse'] ) : null,
				'taken_at'  => $data['taken_at'],
				'source'    => sanitize_text_field( $data['source'] ),
				'notes'     => sanitize_textarea_field( $data['notes'] ),
			),
			array( '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
		);

		if ( $result ) {
			self::check_bp_threshold( $user_id, $data );
			do_action( 'shm_bp_inserted', $wpdb->insert_id, $user_id, $data );
			return $wpdb->insert_id;
		}

		return false;
	}

	public static function insert_glucose( $user_id, $data ) {
		global $wpdb;

		$data = wp_parse_args(
			$data,
			array(
				'value'    => 0,
				'state'    => 'random',
				'taken_at' => current_time( 'mysql' ),
				'source'   => 'manual',
				'notes'    => '',
			)
		);

		$result = $wpdb->insert(
			$wpdb->prefix . 'shm_glucose',
			array(
				'user_id'  => $user_id,
				'value'    => floatval( $data['value'] ),
				'state'    => in_array( $data['state'], array( 'fasting', 'random', 'postprandial' ) ) ? $data['state'] : 'random',
				'taken_at' => $data['taken_at'],
				'source'   => sanitize_text_field( $data['source'] ),
				'notes'    => sanitize_textarea_field( $data['notes'] ),
			),
			array( '%d', '%f', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			self::check_glucose_threshold( $user_id, $data );
			do_action( 'shm_glucose_inserted', $wpdb->insert_id, $user_id, $data );
			return $wpdb->insert_id;
		}

		return false;
	}

	public static function insert_activity( $user_id, $data ) {
		global $wpdb;

		$data = wp_parse_args(
			$data,
			array(
				'steps'          => 0,
				'calories'       => 0,
				'hr_avg'         => null,
				'distance'       => null,
				'active_minutes' => 0,
				'taken_at'       => current_time( 'Y-m-d' ),
				'source'         => 'manual',
			)
		);

		$result = $wpdb->replace(
			$wpdb->prefix . 'shm_activity',
			array(
				'user_id'        => $user_id,
				'steps'          => absint( $data['steps'] ),
				'calories'       => absint( $data['calories'] ),
				'hr_avg'         => ! empty( $data['hr_avg'] ) ? absint( $data['hr_avg'] ) : null,
				'distance'       => ! empty( $data['distance'] ) ? floatval( $data['distance'] ) : null,
				'active_minutes' => absint( $data['active_minutes'] ),
				'taken_at'       => $data['taken_at'],
				'source'         => sanitize_text_field( $data['source'] ),
			),
			array( '%d', '%d', '%d', '%d', '%f', '%d', '%s', '%s' )
		);

		if ( $result ) {
			do_action( 'shm_activity_inserted', $user_id, $data );
		}

		return $result;
	}

	public static function get_bp_data( $user_id, $from, $to ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}shm_bp 
				WHERE user_id = %d 
				AND taken_at BETWEEN %s AND %s 
				ORDER BY taken_at DESC",
				$user_id,
				$from,
				$to
			)
		);
	}

	public static function get_glucose_data( $user_id, $from, $to ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}shm_glucose 
				WHERE user_id = %d 
				AND taken_at BETWEEN %s AND %s 
				ORDER BY taken_at DESC",
				$user_id,
				$from,
				$to
			)
		);
	}

	public static function get_activity_data( $user_id, $from, $to ) {
		global $wpdb;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}shm_activity 
				WHERE user_id = %d 
				AND taken_at BETWEEN %s AND %s 
				ORDER BY taken_at DESC",
				$user_id,
				$from,
				$to
			)
		);
	}

	public static function get_summary( $user_id, $range = '7d' ) {
		$cache_key = "shm_summary_{$user_id}_{$range}";
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$days = intval( str_replace( 'd', '', $range ) );
		$from = date( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
		$to   = current_time( 'mysql' );

		$summary = array(
			'bp'       => self::get_bp_summary( $user_id, $from, $to ),
			'glucose'  => self::get_glucose_summary( $user_id, $from, $to ),
			'activity' => self::get_activity_summary( $user_id, $from, $to ),
		);

		set_transient( $cache_key, $summary, 5 * MINUTE_IN_SECONDS );

		return $summary;
	}

	private static function get_bp_summary( $user_id, $from, $to ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT 
					COUNT(*) as count,
					AVG(systolic) as avg_systolic,
					AVG(diastolic) as avg_diastolic,
					AVG(pulse) as avg_pulse,
					MAX(systolic) as max_systolic,
					MAX(diastolic) as max_diastolic,
					MIN(systolic) as min_systolic,
					MIN(diastolic) as min_diastolic
				FROM {$wpdb->prefix}shm_bp 
				WHERE user_id = %d 
				AND taken_at BETWEEN %s AND %s",
				$user_id,
				$from,
				$to
			),
			ARRAY_A
		);
	}

	private static function get_glucose_summary( $user_id, $from, $to ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT 
					COUNT(*) as count,
					AVG(value) as avg_value,
					MAX(value) as max_value,
					MIN(value) as min_value
				FROM {$wpdb->prefix}shm_glucose 
				WHERE user_id = %d 
				AND taken_at BETWEEN %s AND %s",
				$user_id,
				$from,
				$to
			),
			ARRAY_A
		);
	}

	private static function get_activity_summary( $user_id, $from, $to ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT 
					COUNT(*) as count,
					SUM(steps) as total_steps,
					SUM(calories) as total_calories,
					AVG(steps) as avg_steps,
					AVG(hr_avg) as avg_hr
				FROM {$wpdb->prefix}shm_activity 
				WHERE user_id = %d 
				AND taken_at BETWEEN %s AND %s",
				$user_id,
				$from,
				$to
			),
			ARRAY_A
		);
	}

	private static function check_bp_threshold( $user_id, $data ) {
		$thresholds = apply_filters(
			'shm_metric_thresholds',
			array(
				'bp_systolic_high'  => 140,
				'bp_systolic_low'   => 90,
				'bp_diastolic_high' => 90,
				'bp_diastolic_low'  => 60,
			)
		);

		if ( $data['systolic'] >= $thresholds['bp_systolic_high'] || $data['diastolic'] >= $thresholds['bp_diastolic_high'] ) {
			self::create_alert( $user_id, 'bp_high', sprintf(
				__( 'High blood pressure detected: %d/%d mmHg', 'shm-data-integrations' ),
				$data['systolic'],
				$data['diastolic']
			), 'warning', 'bp', $data['systolic'] . '/' . $data['diastolic'] );
		} elseif ( $data['systolic'] <= $thresholds['bp_systolic_low'] || $data['diastolic'] <= $thresholds['bp_diastolic_low'] ) {
			self::create_alert( $user_id, 'bp_low', sprintf(
				__( 'Low blood pressure detected: %d/%d mmHg', 'shm-data-integrations' ),
				$data['systolic'],
				$data['diastolic']
			), 'warning', 'bp', $data['systolic'] . '/' . $data['diastolic'] );
		}
	}

	private static function check_glucose_threshold( $user_id, $data ) {
		$thresholds = apply_filters(
			'shm_metric_thresholds',
			array(
				'glucose_high' => 180,
				'glucose_low'  => 70,
			)
		);

		if ( $data['value'] >= $thresholds['glucose_high'] ) {
			self::create_alert( $user_id, 'glucose_high', sprintf(
				__( 'High blood glucose detected: %.1f mg/dL', 'shm-data-integrations' ),
				$data['value']
			), 'warning', 'glucose', $data['value'] );
		} elseif ( $data['value'] <= $thresholds['glucose_low'] ) {
			self::create_alert( $user_id, 'glucose_low', sprintf(
				__( 'Low blood glucose detected: %.1f mg/dL', 'shm-data-integrations' ),
				$data['value']
			), 'warning', 'glucose', $data['value'] );
		}
	}

	public static function create_alert( $user_id, $type, $message, $severity = 'info', $metric_type = null, $metric_value = null ) {
		global $wpdb;

		return $wpdb->insert(
			$wpdb->prefix . 'shm_alerts',
			array(
				'user_id'      => $user_id,
				'type'         => $type,
				'message'      => $message,
				'severity'     => $severity,
				'metric_type'  => $metric_type,
				'metric_value' => $metric_value,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	public static function get_alerts( $user_id, $resolved = null ) {
		global $wpdb;

		$where = $wpdb->prepare( 'WHERE user_id = %d', $user_id );

		if ( null !== $resolved ) {
			$where .= $resolved ? ' AND resolved_at IS NOT NULL' : ' AND resolved_at IS NULL';
		}

		return $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}shm_alerts {$where} ORDER BY created_at DESC LIMIT 50"
		);
	}

	public static function resolve_alert( $alert_id, $resolver_id ) {
		global $wpdb;

		return $wpdb->update(
			$wpdb->prefix . 'shm_alerts',
			array(
				'resolved_at' => current_time( 'mysql' ),
				'resolved_by' => $resolver_id,
			),
			array( 'id' => $alert_id ),
			array( '%s', '%d' ),
			array( '%d' )
		);
	}

	public static function log_audit( $user_id, $action, $entity_type, $entity_id = null, $old_value = null, $new_value = null ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->prefix . 'shm_audit_logs',
			array(
				'user_id'     => $user_id,
				'action'      => $action,
				'entity_type' => $entity_type,
				'entity_id'   => $entity_id,
				'old_value'   => maybe_serialize( $old_value ),
				'new_value'   => maybe_serialize( $new_value ),
				'ip_address'  => self::get_user_ip(),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
		);
	}

	private static function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} else {
			$ip = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		}
		return $ip;
	}
}
