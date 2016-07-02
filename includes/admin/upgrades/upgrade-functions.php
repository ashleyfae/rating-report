#<?php
/**
 * Upgrade Functions
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
 * Perform Database Upgrades
 *
 * Also updates the version number.
 *
 * @since 2.0
 * @return void
 */
function rating_report_do_automatic_upgrades() {

	$did_upgrade           = false;
	$rating_report_version = get_option( 'rating_report_version' );

	// We're not up to date!
	if ( version_compare( $rating_report_version, RATING_REPORT_VERSION, '<' ) ) {
		$did_upgrade = true;
	}

	if ( $did_upgrade ) {

		update_option( 'rating_report_version', RATING_REPORT_VERSION );

	}

}

add_action( 'admin_init', 'rating_report_do_automatic_upgrades' );