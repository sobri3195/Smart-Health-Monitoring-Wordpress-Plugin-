<?php
/**
 * Autoloader
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SHM_Autoloader {

	public static function register() {
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	public static function autoload( $class ) {
		$prefix = 'SHM_';
		$len    = strlen( $prefix );

		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class, $len );
		$relative_class = strtolower( str_replace( '_', '-', $relative_class ) );

		$file_map = array(
			'models'     => SHM_PLUGIN_DIR . 'includes/models/class-shm-' . $relative_class . '.php',
			'connectors' => SHM_PLUGIN_DIR . 'includes/connectors/class-shm-' . $relative_class . '.php',
			'admin'      => SHM_PLUGIN_DIR . 'includes/admin/class-shm-' . $relative_class . '.php',
			'api'        => SHM_PLUGIN_DIR . 'includes/api/class-shm-' . $relative_class . '.php',
			'blocks'     => SHM_PLUGIN_DIR . 'includes/blocks/class-shm-' . $relative_class . '.php',
			'root'       => SHM_PLUGIN_DIR . 'includes/class-shm-' . $relative_class . '.php',
		);

		foreach ( $file_map as $path ) {
			if ( file_exists( $path ) ) {
				require_once $path;
				return;
			}
		}
	}
}
