<?php
/**
 * Uninstall Rating Report
 *
 * Deletes the following plugin data:
 *      + All `rating_report` and `rating_report_descriptions` meta.
 *      + Plugin settings.
 *      + Plugin version number.
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Include Rating Report file.
include_once 'rating-report.php';

// Bail if they haven't opted to delete settings.
if ( ! rating_report_get_option( 'uninstall_on_delete' ) ) {
	return;
}

/*
 * Delete post meta.
 */
global $wpdb;

$postmeta_table = $wpdb->postmeta;
$postmeta_table = str_replace( $wpdb->base_prefix, $wpdb->prefix, $postmeta_table );

$wpdb->query( "DELETE FROM " . $postmeta_table . " WHERE meta_key = 'rating_report'" );
$wpdb->query( "DELETE FROM " . $postmeta_table . " WHERE meta_key = 'rating_report_descriptions'" );

/*
 * Delete plugin options.
 */
delete_option( 'rating_report_settings' );
delete_option( 'rating_report_version' );
delete_option( 'rating_report_upgraded_from_1' );