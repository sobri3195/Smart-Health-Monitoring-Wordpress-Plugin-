<?php
/**
 * Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Shortcodes {

	public static function init() {
		add_shortcode( 'shm_metric', array( __CLASS__, 'metric_shortcode' ) );
		add_shortcode( 'shm_chart', array( __CLASS__, 'chart_shortcode' ) );
		add_shortcode( 'shm_alerts', array( __CLASS__, 'alerts_shortcode' ) );
		add_shortcode( 'shm_integrations', array( __CLASS__, 'integrations_shortcode' ) );
	}

	public static function metric_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'metric' => 'bp',
				'range'  => '7d',
			),
			$atts,
			'shm_metric'
		);

		return SHM_Blocks::render_metric_card( $atts );
	}

	public static function chart_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'metric'  => 'bp',
				'type'    => 'line',
				'range'   => '30d',
				'showAvg' => true,
			),
			$atts,
			'shm_chart'
		);

		return SHM_Blocks::render_weekly_trend( $atts );
	}

	public static function alerts_shortcode( $atts ) {
		return SHM_Blocks::render_alerts_list();
	}

	public static function integrations_shortcode( $atts ) {
		return SHM_Blocks::render_integration_status();
	}
}
