<?php
/**
 * Theme Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function shm_theme_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'shm_theme_options', array(
		'title'    => __( 'Theme Options', 'smart-health-monitoring' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'shm_theme_color_scheme', array(
		'default'           => 'light',
		'sanitize_callback' => 'shm_sanitize_color_scheme',
	) );

	$wp_customize->add_control( 'shm_theme_color_scheme', array(
		'label'    => __( 'Default Color Scheme', 'smart-health-monitoring' ),
		'section'  => 'shm_theme_options',
		'type'     => 'select',
		'choices'  => array(
			'light' => __( 'Light', 'smart-health-monitoring' ),
			'dark'  => __( 'Dark', 'smart-health-monitoring' ),
			'auto'  => __( 'Auto (System Preference)', 'smart-health-monitoring' ),
		),
	) );

	$wp_customize->add_setting( 'shm_enable_animations', array(
		'default'           => true,
		'sanitize_callback' => 'wp_validate_boolean',
	) );

	$wp_customize->add_control( 'shm_enable_animations', array(
		'label'    => __( 'Enable Animations', 'smart-health-monitoring' ),
		'section'  => 'shm_theme_options',
		'type'     => 'checkbox',
	) );
}
add_action( 'customize_register', 'shm_theme_customize_register' );

function shm_sanitize_color_scheme( $input ) {
	$valid = array( 'light', 'dark', 'auto' );
	return in_array( $input, $valid, true ) ? $input : 'light';
}

function shm_theme_body_classes( $classes ) {
	$color_scheme = get_theme_mod( 'shm_theme_color_scheme', 'light' );
	
	if ( 'dark' === $color_scheme ) {
		$classes[] = 'theme-dark';
	} elseif ( 'auto' === $color_scheme ) {
		$classes[] = 'theme-auto';
	}

	if ( ! get_theme_mod( 'shm_enable_animations', true ) ) {
		$classes[] = 'no-animations';
	}

	if ( is_user_logged_in() ) {
		$classes[] = 'user-logged-in';
	}

	return $classes;
}
add_filter( 'body_class', 'shm_theme_body_classes' );
