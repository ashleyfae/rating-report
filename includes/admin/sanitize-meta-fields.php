<?php
/**
 * Sanitize Meta Fields
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Sanitize Ratings
 *
 * Runs each rating through rating_report_sanitize_rating_number()
 *
 * @uses  rating_report_sanitize_rating_number()
 *
 * @param mixed $ratings
 *
 * @since 2.0
 * @return array
 */
function rating_report_sanitize_ratings( $ratings ) {
	return is_array( $ratings ) ? array_map( 'rating_report_sanitize_rating_number', $ratings ) : array();
}

add_filter( 'rating-report/meta-box/sanitize/rating_report', 'rating_report_sanitize_ratings', 10 );

/**
 * Sanitize Ratings
 *
 * All meta fields are sanitized to become an integer (whole numbers), a float,
 * or an empty string (if all else fails).
 *
 * @uses  rating_report_sanitize_rating_number()
 *
 * @param mixed $value
 *
 * @since 2.0
 * @return float|int|string
 */
function rating_report_sanitize_rating_number( $value ) {
	if ( ! is_numeric( $value ) ) {
		return '';
	}

	// We have a half star value.
	if ( fmod( $value, 1 ) != 0 ) {
		$new_value = (float) round( $value, 1 );
	} else {
		$new_value = absint( $value );
	}

	return $new_value;
}

/**
 * Sanitize Descriptions
 *
 * Runs each descriptions through wp_kses_post
 *
 * @param mixed $descriptions
 *
 * @since 2.0
 * @return array
 */
function rating_report_sanitize_descriptons( $descriptions ) {
	return is_array( $descriptions ) ? array_map( 'wp_kses_post', $descriptions ) : array();
}

add_filter( 'rating-report/meta-box/sanitize/rating_report_descriptions', 'rating_report_sanitize_descriptons' );