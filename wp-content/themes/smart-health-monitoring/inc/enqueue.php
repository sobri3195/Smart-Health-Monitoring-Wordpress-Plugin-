<?php
/**
 * Enqueue scripts and styles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_theme_enqueue_scripts() {
	wp_enqueue_style( 'shm-theme-style', get_stylesheet_uri(), array(), SHM_THEME_VERSION );
	wp_enqueue_style( 'shm-theme-main', SHM_THEME_URI . '/assets/css/main.css', array(), SHM_THEME_VERSION );

	if ( is_page_template( 'page-dashboard.php' ) || is_page_template( 'page-reports.php' ) ) {
		wp_enqueue_script( 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true );
	}

	wp_enqueue_script( 'shm-theme-main', SHM_THEME_URI . '/assets/js/main.js', array(), SHM_THEME_VERSION, true );

	wp_localize_script( 'shm-theme-main', 'shmTheme', array(
		'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
		'restUrl'   => rest_url( 'shm/v1' ),
		'nonce'     => wp_create_nonce( 'wp_rest' ),
		'userId'    => get_current_user_id(),
		'isDark'    => get_user_meta( get_current_user_id(), 'shm_dark_mode', true ) === 'yes',
		'i18n'      => array(
			'loading'   => __( 'Loading...', 'smart-health-monitoring' ),
			'error'     => __( 'An error occurred', 'smart-health-monitoring' ),
			'noData'    => __( 'No data available', 'smart-health-monitoring' ),
		),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'shm_theme_enqueue_scripts' );

function shm_theme_preload_fonts() {
	?>
	<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
	<link rel="dns-prefetch" href="https://fonts.googleapis.com">
	<?php
}
add_action( 'wp_head', 'shm_theme_preload_fonts', 1 );

function shm_theme_defer_scripts( $tag, $handle, $src ) {
	$defer_scripts = array( 'shm-theme-main' );
	
	if ( in_array( $handle, $defer_scripts ) ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	
	return $tag;
}
add_filter( 'script_loader_tag', 'shm_theme_defer_scripts', 10, 3 );
