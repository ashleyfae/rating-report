<?php
/**
 * Migrator
 *
 * Used for migrating data from version 1.x to 2.x.
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
 * Show a notice prompting to upgrade from 1.x to 2.x.
 *
 * @since 2.0.0
 * @return void
 */
function rating_report_upgrade_from_1() {
	// We have no old settings - bail.
	if ( ! get_option( 'rr-section-config' ) ) {
		return;
	}

	// We've already migrated from 1.0 - bail.
	if ( get_option( 'rating_report_upgraded_from_1' ) ) {
		return;
	}
	?>
	<div class="notice notice-warning">
		<p><strong><?php _e( 'Rating Report needs to upgrade your database.', 'rating-report' ); ?></strong></p>
		<p><?php _e( 'This upgrade will do the following:', 'rating-report' ); ?></p>
		<ul id="rating-report-migrator-list">
			<li><?php _e( 'Migrate your saved categories to the new settings panel.', 'rating-report' ); ?></li>
			<li><?php _e( 'Copy all post ratings to a new database format.', 'rating-report' ); ?></li>
			<li><?php _e( 'If selected, delete the old saved format of ratings. (If not selected, then you\'ll have duplicate data in your database, which isn\'t a huge deal, but makes things a tad messy database-wise.)', 'rating-report' ); ?></li>
		</ul>
		<p><?php _e( 'Some settings may need to be copied over manually, so check the settings page when you\'re done.', 'rating-report' ); ?></p>

		<div id="rating-report-migration-area">
			<p>
				<input type="checkbox" id="rating-report-delete-old-data" value="1">
				<label for="rating-report-delete-old-data"><?php _e( 'Delete old data after migrating', 'rating-report' ); ?></label>
			</p>
			<p>
				<button id="rating-report-migrate-button" class="button"><?php _e( 'Start Migration', 'rating-report' ); ?></button>
			</p>
		</div>

		<div id="rating-report-migrator-messages"></div>
	</div>
	<?php
}

add_action( 'admin_notices', 'rating_report_upgrade_from_1' );

/**
 * Load 1.x to 2.x migrator assets
 *
 * @since 2.0.0
 * @return void
 */
function rating_report_load_migrator_assets() {
	// We have no old settings - bail.
	if ( ! get_option( 'rr-section-config' ) ) {
		return;
	}

	// We've already migrated from 1.0 - bail.
	if ( get_option( 'rating_report_upgraded_from_1' ) ) {
		return;
	}

	$js_dir  = RATING_REPORT_PLUGIN_URL . 'assets/js/';
	$css_dir = RATING_REPORT_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	$admin_deps = array(
		'jquery'
	);

	wp_enqueue_script( 'rating-report-migrator', $js_dir . 'migrator' . $suffix . '.js', $admin_deps, RATING_REPORT_VERSION, true );
	wp_enqueue_style( 'rating-report-admin', $css_dir . 'rating-report-admin' . $suffix . '.css', RATING_REPORT_VERSION );
}

add_action( 'admin_enqueue_scripts', 'rating_report_load_migrator_assets' );

/**
 * AJAX CB: Migrate Data
 *
 * @since 2.0.0
 * @return void
 */
function rating_report_migrate_data() {
	$step            = absint( $_POST['step'] );
	$delete_old_data = $_POST['delete_old_data'];

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You shouldn\'t be here!', 'rating-report-migrator' ) );
	}

	$import     = new Rating_Report_Migrator( $step, $delete_old_data );
	$result     = $import->process_step();
	$percentage = $import->get_percentage_complete();

	if ( $result === true ) {

		// There's still more to do - let's go again.
		$step += 1;

		wp_send_json_success( array(
			'step'       => $step,
			'percentage' => $percentage,
			'message'    => ''
		) );

	} elseif ( $result === false ) {

		// We're all done, sweet!
		$message = __( 'Your data has been migrated successfully! All ratings have been copied over.', 'rating-report' );

		update_option( 'rating_report_upgraded_from_1', true );

		wp_send_json_success( array(
			'step'    => 'done',
			'message' => $message
		) );

	} else {

		// Unexpected error.
		$step += 1;

		wp_send_json_error( array(
			'step'       => $step,
			'percentage' => $percentage,
			'message'    => $result
		) );

	}

	exit;
}

add_action( 'wp_ajax_rating_report_migrate_data', 'rating_report_migrate_data' );