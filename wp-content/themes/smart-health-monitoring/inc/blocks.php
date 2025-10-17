<?php
/**
 * Custom Gutenberg blocks registration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_theme_register_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	wp_enqueue_script(
		'shm-blocks',
		SHM_THEME_URI . '/assets/js/blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-editor' ),
		SHM_THEME_VERSION,
		true
	);

	wp_enqueue_style(
		'shm-blocks-editor',
		SHM_THEME_URI . '/assets/css/blocks-editor.css',
		array( 'wp-edit-blocks' ),
		SHM_THEME_VERSION
	);
}
add_action( 'enqueue_block_editor_assets', 'shm_theme_register_blocks' );

function shm_theme_block_categories( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'shm',
				'title' => __( 'Health Monitoring', 'smart-health-monitoring' ),
				'icon'  => 'heart',
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'shm_theme_block_categories', 10, 1 );
