<?php
/**
 * Plugin Name: SHM Data & Integrations
 * Plugin URI: https://example.com/shm-data-integrations
 * Description: Health data management, wearable integrations (OAuth 2.0), REST API, roles/permissions, and reporting for Smart Health Monitoring
 * Version: 1.0.0
 * Author: SHM Team
 * Author URI: https://example.com
 * Text Domain: shm-data-integrations
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SHM_VERSION', '1.0.0' );
define( 'SHM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SHM_PLUGIN_FILE', __FILE__ );

require_once SHM_PLUGIN_DIR . 'includes/class-shm-autoloader.php';

SHM_Autoloader::register();

final class SHM_Data_Integrations {

	private static $instance = null;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
	}

	private function init_hooks() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	public function activate() {
		require_once SHM_PLUGIN_DIR . 'includes/class-shm-install.php';
		SHM_Install::activate();
	}

	public function deactivate() {
		wp_clear_scheduled_hook( 'shm_sync_wearables' );
	}

	public function init() {
		$this->init_classes();

		do_action( 'shm_init' );
	}

	private function init_classes() {
		SHM_Roles::init();
		SHM_Database::init();
		SHM_API::init();
		SHM_Admin::init();
		SHM_Blocks::init();
		SHM_Shortcodes::init();
		SHM_Connectors::init();
		SHM_Cron::init();
		SHM_CLI::init();
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'shm-data-integrations',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/languages'
		);
	}
}

function shm_di() {
	return SHM_Data_Integrations::instance();
}

shm_di();
