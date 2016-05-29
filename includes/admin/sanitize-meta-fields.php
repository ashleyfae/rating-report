<?php
/**
 * Sanitize Meta Fields
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Sanitize Meta
 *
 * All meta fields are sanitized to become an integer (whole numbers), a float,
 * or an empty string (if all else fails).
 *
 * @param mixed $value
 * @param string $field_name
 *
 * @since 1.0
 * @return float|int|string
 */
function rating_report_sanitize_meta( $value, $field_name ) {
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

add_filter( 'rating-report/meta-box/sanitize/', 'rating_report_sanitize_meta', 10, 2 );