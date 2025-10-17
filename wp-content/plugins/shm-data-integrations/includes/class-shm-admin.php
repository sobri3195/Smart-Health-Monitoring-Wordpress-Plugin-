<?php
/**
 * Admin area
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'wp_ajax_shm_export_csv', array( __CLASS__, 'export_csv' ) );
		add_action( 'wp_ajax_shm_sync_manual', array( __CLASS__, 'sync_manual' ) );
	}

	public static function add_menu() {
		add_menu_page(
			__( 'Smart Health Monitoring', 'shm-data-integrations' ),
			__( 'Health Data', 'shm-data-integrations' ),
			'shm_view_own_data',
			'shm-dashboard',
			array( __CLASS__, 'render_dashboard' ),
			'dashicons-heart',
			30
		);

		add_submenu_page(
			'shm-dashboard',
			__( 'Dashboard', 'shm-data-integrations' ),
			__( 'Dashboard', 'shm-data-integrations' ),
			'shm_view_own_data',
			'shm-dashboard',
			array( __CLASS__, 'render_dashboard' )
		);

		add_submenu_page(
			'shm-dashboard',
			__( 'Patients', 'shm-data-integrations' ),
			__( 'Patients', 'shm-data-integrations' ),
			'shm_view_patient_data',
			'shm-patients',
			array( __CLASS__, 'render_patients' )
		);

		add_submenu_page(
			'shm-dashboard',
			__( 'Alerts', 'shm-data-integrations' ),
			__( 'Alerts', 'shm-data-integrations' ),
			'shm_view_own_data',
			'shm-alerts',
			array( __CLASS__, 'render_alerts' )
		);

		add_submenu_page(
			'shm-dashboard',
			__( 'Integrations', 'shm-data-integrations' ),
			__( 'Integrations', 'shm-data-integrations' ),
			'shm_connect_wearables',
			'shm-integrations',
			array( __CLASS__, 'render_integrations' )
		);

		add_submenu_page(
			'shm-dashboard',
			__( 'Settings', 'shm-data-integrations' ),
			__( 'Settings', 'shm-data-integrations' ),
			'shm_manage_settings',
			'shm-settings',
			array( __CLASS__, 'render_settings' )
		);
	}

	public static function enqueue_assets( $hook ) {
		if ( strpos( $hook, 'shm-' ) === false ) {
			return;
		}

		wp_enqueue_style( 'shm-admin', SHM_PLUGIN_URL . 'assets/css/admin.css', array(), SHM_VERSION );
		wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
		wp_enqueue_script( 'shm-admin', SHM_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery', 'chart-js' ), SHM_VERSION, true );

		wp_localize_script(
			'shm-admin',
			'shmAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'shm_admin' ),
				'apiUrl'  => rest_url( 'shm/v1' ),
			)
		);
	}

	public static function register_settings() {
		register_setting( 'shm_settings', 'shm_fitbit_client_id' );
		register_setting( 'shm_settings', 'shm_fitbit_client_secret' );
		register_setting( 'shm_settings', 'shm_garmin_consumer_key' );
		register_setting( 'shm_settings', 'shm_garmin_consumer_secret' );
	}

	public static function render_dashboard() {
		$user_id = get_current_user_id();
		$summary = SHM_Database::get_summary( $user_id, '30d' );
		include SHM_PLUGIN_DIR . 'includes/admin/views/dashboard.php';
	}

	public static function render_patients() {
		if ( ! current_user_can( 'shm_view_patient_data' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'shm-data-integrations' ) );
		}

		$patients = get_users( array( 'role__in' => array( 'shm_patient', 'shm_clinician' ) ) );
		include SHM_PLUGIN_DIR . 'includes/admin/views/patients.php';
	}

	public static function render_alerts() {
		$user_id = get_current_user_id();
		$alerts  = SHM_Database::get_alerts( $user_id, false );
		include SHM_PLUGIN_DIR . 'includes/admin/views/alerts.php';
	}

	public static function render_integrations() {
		$user_id    = get_current_user_id();
		$connectors = SHM_Connectors::get_all_connectors();
		include SHM_PLUGIN_DIR . 'includes/admin/views/integrations.php';
	}

	public static function render_settings() {
		if ( ! current_user_can( 'shm_manage_settings' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'shm-data-integrations' ) );
		}

		include SHM_PLUGIN_DIR . 'includes/admin/views/settings.php';
	}

	public static function export_csv() {
		check_ajax_referer( 'shm_admin', 'nonce' );

		if ( ! current_user_can( 'shm_export_data' ) ) {
			wp_die( 'Unauthorized' );
		}

		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : get_current_user_id();
		$from    = isset( $_POST['from'] ) ? sanitize_text_field( $_POST['from'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
		$to      = isset( $_POST['to'] ) ? sanitize_text_field( $_POST['to'] ) : date( 'Y-m-d' );

		$bp_data       = SHM_Database::get_bp_data( $user_id, $from, $to );
		$glucose_data  = SHM_Database::get_glucose_data( $user_id, $from, $to );
		$activity_data = SHM_Database::get_activity_data( $user_id, $from, $to );

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="health-data-' . $user_id . '-' . date( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );

		fputcsv( $output, array( 'Type', 'Date', 'Value', 'Unit', 'Notes' ) );

		foreach ( $bp_data as $record ) {
			fputcsv( $output, array( 'Blood Pressure', $record->taken_at, $record->systolic . '/' . $record->diastolic, 'mmHg', $record->notes ) );
		}

		foreach ( $glucose_data as $record ) {
			fputcsv( $output, array( 'Glucose', $record->taken_at, $record->value, 'mg/dL', $record->state ) );
		}

		foreach ( $activity_data as $record ) {
			fputcsv( $output, array( 'Activity', $record->taken_at, $record->steps, 'steps', '' ) );
		}

		fclose( $output );
		exit;
	}

	public static function sync_manual() {
		check_ajax_referer( 'shm_admin', 'nonce' );

		$user_id   = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : get_current_user_id();
		$connector = isset( $_POST['connector'] ) ? sanitize_text_field( $_POST['connector'] ) : '';

		if ( ! $connector ) {
			wp_send_json_error( array( 'message' => 'Invalid connector' ) );
		}

		$result = SHM_Connectors::sync_user_connection( $user_id, $connector );

		if ( $result ) {
			wp_send_json_success( array( 'counts' => $result ) );
		} else {
			wp_send_json_error( array( 'message' => 'Sync failed' ) );
		}
	}
}
