<?php
/**
 * Smart Health Monitoring Theme Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'SHM_THEME_VERSION', '1.0.0' );
define( 'SHM_THEME_DIR', get_template_directory() );
define( 'SHM_THEME_URI', get_template_directory_uri() );

require_once SHM_THEME_DIR . '/inc/setup.php';
require_once SHM_THEME_DIR . '/inc/enqueue.php';
require_once SHM_THEME_DIR . '/inc/blocks.php';
require_once SHM_THEME_DIR . '/inc/theme-customizer.php';
require_once SHM_THEME_DIR . '/inc/template-functions.php';

require_once SHM_THEME_DIR . '/inc/feature-appointments.php';
require_once SHM_THEME_DIR . '/inc/feature-medications.php';
require_once SHM_THEME_DIR . '/inc/feature-health-goals.php';
require_once SHM_THEME_DIR . '/inc/feature-emergency-contacts.php';
require_once SHM_THEME_DIR . '/inc/feature-health-journal.php';
require_once SHM_THEME_DIR . '/inc/feature-symptom-tracker.php';
require_once SHM_THEME_DIR . '/inc/feature-water-tracker.php';
require_once SHM_THEME_DIR . '/inc/feature-sleep-tracker.php';
require_once SHM_THEME_DIR . '/inc/feature-bmi-calculator.php';
require_once SHM_THEME_DIR . '/inc/feature-document-manager.php';

function shm_theme_setup() {
    load_theme_textdomain( 'smart-health-monitoring', SHM_THEME_DIR . '/languages' );

    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    add_theme_support( 'custom-logo', array(
        'height'      => 60,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/css/editor-style.css' );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'smart-health-monitoring' ),
        'footer'  => __( 'Footer Menu', 'smart-health-monitoring' ),
    ) );

    add_theme_support( 'align-wide' );
    add_theme_support( 'wp-block-styles' );
}
add_action( 'after_setup_theme', 'shm_theme_setup' );

function shm_theme_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Dashboard Sidebar', 'smart-health-monitoring' ),
        'id'            => 'dashboard-sidebar',
        'description'   => __( 'Sidebar for dashboard pages', 'smart-health-monitoring' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 1', 'smart-health-monitoring' ),
        'id'            => 'footer-1',
        'description'   => __( 'First footer widget area', 'smart-health-monitoring' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 2', 'smart-health-monitoring' ),
        'id'            => 'footer-2',
        'description'   => __( 'Second footer widget area', 'smart-health-monitoring' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 3', 'smart-health-monitoring' ),
        'id'            => 'footer-3',
        'description'   => __( 'Third footer widget area', 'smart-health-monitoring' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'shm_theme_widgets_init' );

function shm_theme_content_width() {
    $GLOBALS['content_width'] = apply_filters( 'shm_theme_content_width', 1200 );
}
add_action( 'after_setup_theme', 'shm_theme_content_width', 0 );
