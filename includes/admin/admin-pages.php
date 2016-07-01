<?php
/**
 * Admin Pages
 *
 * Creates admin pages and loads any required assets on those pages.
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates admin submenu pages under 'Books'.
 *
 * @since 1.0.0
 * @return void
 */
function rating_report_add_options_link() {
	$rating_report_settings_page = add_submenu_page( 'options-general.php', __( 'Rating Report Settings', 'rating-report' ), __( 'Rating Report', 'rating-report' ), 'manage_options', 'rating-report', 'rating_report_options_page' );
}

add_action( 'admin_menu', 'rating_report_add_options_link', 10 );

/**
 * Is Admin Page
 *
 * Checks whether or not the current page is a Novelist admin page.
 *
 * @since 1.0
 * @return bool
 */
function rating_report_is_admin_page() {
	$screen                = get_current_screen();
	$is_rating_report_page = false;

	if ( $screen->base == 'settings_page_rating-report' ) {
		$is_rating_report_page = true;
	}

	if ( $screen->base == 'post' && $screen->post_type == 'post' ) {
		$is_rating_report_page = true;
	}

	return apply_filters( 'rating-report/is-admin-page', $is_rating_report_page, $screen );
}

/**
 * Load Admin Scripts
 *
 * Adds all admin scripts and stylesheets to the admin panel.
 *
 * @param string $hook Currently loaded page
 *
 * @since 1.0
 * @return void
 */
function rating_report_load_admin_scripts( $hook ) {
	if ( ! apply_filters( 'rating-report/load-admin-scripts', rating_report_is_admin_page(), $hook ) ) {
		return;
	}

	$js_dir  = RATING_REPORT_PLUGIN_URL . 'assets/js/';
	$css_dir = RATING_REPORT_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	/*
	 * JavaScript
	 */

	wp_register_script( 'jquery-recopy', $js_dir . 'jquery.recopy' . $suffix . '.js', array( 'jquery' ), '1.1.0', true );
	wp_enqueue_script( 'jquery-recopy' );

	// Media Upload
	wp_enqueue_media();
	wp_register_script( 'rating-report-media-upload', $js_dir . 'media-upload' . $suffix . '.js', array( 'jquery' ), RATING_REPORT_VERSION, true );
	wp_enqueue_script( 'rating-report-media-upload' );

	$settings = array(
		'text_title'  => __( 'Upload or Select an Image', 'rating-report' ),
		'text_button' => __( 'Insert Image', 'rating-report' )
	);

	wp_localize_script( 'rating-report-media-upload', 'RATING_REPORT_MEDIA', apply_filters( 'rating-report/media-upload-js-settings', $settings ) );

	$admin_deps = array(
		'jquery',
		'jquery-recopy',
		'wp-color-picker'
	);

	wp_register_script( 'rating-report-admin-scripts', $js_dir . 'admin-scripts' . $suffix . '.js', $admin_deps, RATING_REPORT_VERSION, true );
	wp_enqueue_script( 'rating-report-admin-scripts' );

	$settings = array(
		'text_remove'    => __( 'Remove', 'rating-report' ),
		'confirm_reset'  => __( 'Are you sure you wish to revert all the settings in this tab to their default values? This cannot be undone.', 'rating-report' ),
		'gallery_title'  => __( 'Select an Image', 'rating-report' ),
		'gallery_update' => __( 'Update Image', 'rating-report' ),
		'gallery_delete' => __( 'Delete Image', 'rating-report' )
	);

	wp_localize_script( 'rating-report-admin-scripts', 'RATING_REPORT', apply_filters( 'rating-report/admin-scripts-settings', $settings ) );

	/*
	 * Stylesheets
	 */

	// Color Picker
	wp_enqueue_style( 'wp-color-picker' );

	wp_register_style( 'rating-report-admin', $css_dir . 'rating-report-admin' . $suffix . '.css', RATING_REPORT_VERSION );
	wp_enqueue_style( 'rating-report-admin' );
}

add_action( 'admin_enqueue_scripts', 'rating_report_load_admin_scripts', 100 );