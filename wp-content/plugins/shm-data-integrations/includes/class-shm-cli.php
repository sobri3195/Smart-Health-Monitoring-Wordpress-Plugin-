<?php
/**
 * WP-CLI commands
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_CLI {

	public static function init() {
		if ( ! class_exists( 'WP_CLI' ) ) {
			return;
		}

		WP_CLI::add_command( 'shm seed', array( __CLASS__, 'seed' ) );
		WP_CLI::add_command( 'shm sync', array( __CLASS__, 'sync' ) );
		WP_CLI::add_command( 'shm export', array( __CLASS__, 'export' ) );
	}

	public static function seed( $args, $assoc_args ) {
		$user_id = isset( $assoc_args['user'] ) ? absint( $assoc_args['user'] ) : 1;
		$days    = isset( $assoc_args['days'] ) ? absint( $assoc_args['days'] ) : 30;

		WP_CLI::log( "Seeding {$days} days of data for user {$user_id}..." );

		for ( $i = 0; $i < $days; $i++ ) {
			$date = date( 'Y-m-d H:i:s', strtotime( "-{$i} days" ) );

			SHM_Database::insert_bp(
				$user_id,
				array(
					'systolic'  => wp_rand( 110, 140 ),
					'diastolic' => wp_rand( 70, 90 ),
					'pulse'     => wp_rand( 60, 90 ),
					'taken_at'  => $date,
					'source'    => 'demo',
				)
			);

			SHM_Database::insert_glucose(
				$user_id,
				array(
					'value'    => wp_rand( 80, 120 ),
					'state'    => 'random',
					'taken_at' => $date,
					'source'   => 'demo',
				)
			);

			SHM_Database::insert_activity(
				$user_id,
				array(
					'steps'          => wp_rand( 5000, 15000 ),
					'calories'       => wp_rand( 1800, 2500 ),
					'hr_avg'         => wp_rand( 65, 85 ),
					'distance'       => wp_rand( 3, 10 ),
					'active_minutes' => wp_rand( 30, 120 ),
					'taken_at'       => date( 'Y-m-d', strtotime( "-{$i} days" ) ),
					'source'         => 'demo',
				)
			);
		}

		WP_CLI::success( "Seeded {$days} days of demo data for user {$user_id}." );
	}

	public static function sync( $args, $assoc_args ) {
		$user_id   = isset( $assoc_args['user'] ) ? absint( $assoc_args['user'] ) : null;
		$connector = isset( $assoc_args['connector'] ) ? sanitize_text_field( $assoc_args['connector'] ) : null;

		if ( $user_id && $connector ) {
			WP_CLI::log( "Syncing {$connector} for user {$user_id}..." );
			$result = SHM_Connectors::sync_user_connection( $user_id, $connector );

			if ( $result ) {
				WP_CLI::success( 'Sync completed: ' . wp_json_encode( $result ) );
			} else {
				WP_CLI::error( 'Sync failed.' );
			}
		} else {
			WP_CLI::log( 'Syncing all connections...' );
			SHM_Connectors::sync_all_connections();
			WP_CLI::success( 'All connections synced.' );
		}
	}

	public static function export( $args, $assoc_args ) {
		$user_id = isset( $assoc_args['user'] ) ? absint( $assoc_args['user'] ) : 1;
		$range   = isset( $assoc_args['range'] ) ? sanitize_text_field( $assoc_args['range'] ) : '30d';
		$format  = isset( $assoc_args['format'] ) ? sanitize_text_field( $assoc_args['format'] ) : 'csv';

		$days = intval( str_replace( 'd', '', $range ) );
		$from = date( 'Y-m-d', strtotime( "-{$days} days" ) );
		$to   = date( 'Y-m-d' );

		$bp_data       = SHM_Database::get_bp_data( $user_id, $from, $to );
		$glucose_data  = SHM_Database::get_glucose_data( $user_id, $from, $to );
		$activity_data = SHM_Database::get_activity_data( $user_id, $from, $to );

		$filename = "health-data-{$user_id}-{$range}." . $format;

		if ( 'csv' === $format ) {
			$file = fopen( $filename, 'w' );
			fputcsv( $file, array( 'Type', 'Date', 'Value', 'Unit', 'Notes' ) );

			foreach ( $bp_data as $record ) {
				fputcsv( $file, array( 'Blood Pressure', $record->taken_at, $record->systolic . '/' . $record->diastolic, 'mmHg', $record->notes ) );
			}

			foreach ( $glucose_data as $record ) {
				fputcsv( $file, array( 'Glucose', $record->taken_at, $record->value, 'mg/dL', $record->state ) );
			}

			foreach ( $activity_data as $record ) {
				fputcsv( $file, array( 'Activity', $record->taken_at, $record->steps, 'steps', '' ) );
			}

			fclose( $file );
		}

		WP_CLI::success( "Exported to {$filename}" );
	}
}
