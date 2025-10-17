<?php
/**
 * Installation and database setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Install {

	public static function activate() {
		self::create_tables();
		self::create_roles();
		self::schedule_cron();
		
		update_option( 'shm_version', SHM_VERSION );
		
		flush_rewrite_rules();
	}

	private static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$tables = array(
			"CREATE TABLE {$wpdb->prefix}shm_bp (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				systolic int(3) unsigned NOT NULL,
				diastolic int(3) unsigned NOT NULL,
				pulse int(3) unsigned DEFAULT NULL,
				taken_at datetime NOT NULL,
				source varchar(50) DEFAULT 'manual',
				notes text,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY taken_at (taken_at),
				KEY user_taken (user_id, taken_at)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}shm_glucose (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				value decimal(5,1) unsigned NOT NULL,
				state varchar(20) DEFAULT 'random',
				taken_at datetime NOT NULL,
				source varchar(50) DEFAULT 'manual',
				notes text,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY taken_at (taken_at),
				KEY user_taken (user_id, taken_at)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}shm_activity (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				steps int(10) unsigned DEFAULT 0,
				calories int(10) unsigned DEFAULT 0,
				hr_avg int(3) unsigned DEFAULT NULL,
				distance decimal(10,2) DEFAULT NULL,
				active_minutes int(5) unsigned DEFAULT 0,
				taken_at date NOT NULL,
				source varchar(50) DEFAULT 'manual',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY user_date (user_id, taken_at),
				KEY taken_at (taken_at)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}shm_alerts (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				type varchar(50) NOT NULL,
				message text NOT NULL,
				severity varchar(20) DEFAULT 'info',
				metric_type varchar(50) DEFAULT NULL,
				metric_value varchar(100) DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				resolved_at datetime DEFAULT NULL,
				resolved_by bigint(20) unsigned DEFAULT NULL,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY created_at (created_at),
				KEY severity (severity)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}shm_audit_logs (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				action varchar(100) NOT NULL,
				entity_type varchar(50) NOT NULL,
				entity_id bigint(20) unsigned DEFAULT NULL,
				old_value text,
				new_value text,
				ip_address varchar(45) DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY user_id (user_id),
				KEY action (action),
				KEY created_at (created_at)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}shm_connections (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				connector varchar(50) NOT NULL,
				access_token text NOT NULL,
				refresh_token text,
				token_expires_at datetime DEFAULT NULL,
				external_user_id varchar(255) DEFAULT NULL,
				status varchar(20) DEFAULT 'active',
				last_sync_at datetime DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY user_connector (user_id, connector)
			) $charset_collate;"
		);

		foreach ( $tables as $sql ) {
			dbDelta( $sql );
		}
	}

	private static function create_roles() {
		$patient_capabilities = array(
			'read'                  => true,
			'shm_view_own_data'     => true,
			'shm_manage_own_data'   => true,
			'shm_connect_wearables' => true,
		);

		add_role( 'shm_patient', __( 'Patient', 'shm-data-integrations' ), $patient_capabilities );

		$clinician_capabilities = array_merge(
			$patient_capabilities,
			array(
				'shm_view_patient_data'   => true,
				'shm_create_notes'        => true,
				'shm_manage_alerts'       => true,
				'shm_view_reports'        => true,
			)
		);

		add_role( 'shm_clinician', __( 'Clinician', 'shm-data-integrations' ), $clinician_capabilities );

		$admin_capabilities = array_merge(
			$clinician_capabilities,
			array(
				'shm_manage_all_data'     => true,
				'shm_manage_settings'     => true,
				'shm_manage_integrations' => true,
				'shm_export_data'         => true,
				'shm_view_audit_logs'     => true,
			)
		);

		add_role( 'shm_admin', __( 'Health Admin', 'shm-data-integrations' ), $admin_capabilities );

		$wp_admin = get_role( 'administrator' );
		if ( $wp_admin ) {
			foreach ( $admin_capabilities as $cap => $grant ) {
				$wp_admin->add_cap( $cap );
			}
		}
	}

	private static function schedule_cron() {
		if ( ! wp_next_scheduled( 'shm_sync_wearables' ) ) {
			wp_schedule_event( time(), 'shm_30min', 'shm_sync_wearables' );
		}
	}
}
