<?php
/**
 * Cron jobs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Cron {

	public static function init() {
		add_filter( 'cron_schedules', array( __CLASS__, 'add_schedules' ) );
	}

	public static function add_schedules( $schedules ) {
		$schedules['shm_30min'] = array(
			'interval' => 1800,
			'display'  => __( 'Every 30 Minutes', 'shm-data-integrations' ),
		);

		return $schedules;
	}
}
