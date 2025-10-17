<?php
/**
 * Roles and capabilities management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Roles {

	public static function init() {
		add_filter( 'user_has_cap', array( __CLASS__, 'filter_capabilities' ), 10, 4 );
	}

	public static function filter_capabilities( $allcaps, $caps, $args, $user ) {
		if ( ! isset( $args[0] ) ) {
			return $allcaps;
		}

		$capability = $args[0];
		$user_id    = $user->ID;

		if ( 'shm_view_own_data' === $capability && isset( $args[2] ) ) {
			$data_user_id = absint( $args[2] );
			if ( $user_id === $data_user_id || current_user_can( 'shm_manage_all_data' ) ) {
				$allcaps['shm_view_own_data'] = true;
			}
		}

		if ( 'shm_manage_own_data' === $capability && isset( $args[2] ) ) {
			$data_user_id = absint( $args[2] );
			if ( $user_id === $data_user_id || current_user_can( 'shm_manage_all_data' ) ) {
				$allcaps['shm_manage_own_data'] = true;
			}
		}

		return $allcaps;
	}

	public static function can_view_user_data( $user_id, $target_user_id ) {
		if ( current_user_can( 'shm_manage_all_data' ) ) {
			return true;
		}

		if ( current_user_can( 'shm_view_patient_data' ) ) {
			$assigned = get_user_meta( $target_user_id, '_shm_assigned_clinician', false );
			if ( in_array( $user_id, $assigned ) ) {
				return true;
			}
		}

		return $user_id === $target_user_id && current_user_can( 'shm_view_own_data' );
	}

	public static function can_manage_user_data( $user_id, $target_user_id ) {
		if ( current_user_can( 'shm_manage_all_data' ) ) {
			return true;
		}

		return $user_id === $target_user_id && current_user_can( 'shm_manage_own_data' );
	}
}
