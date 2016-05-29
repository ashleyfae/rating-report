<?php
/**
 * Functions used throughout the plugin
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Get Default Categories
 *
 * @since 1.0
 * @return array
 */
function rating_report_get_default_categories() {
	$categories = array(
		esc_html__( 'Characters', 'rating-report' ),
		esc_html__( 'Plot', 'rating-report' ),
		esc_html__( 'Writing', 'rating-report' ),
		esc_html__( 'Pacing', 'rating-report' ),
		esc_html__( 'Cover', 'rating-report' )
	);

	return apply_filters( 'rating-report/default-categories', $categories );
}

/**
 * Get Rating Scale
 *
 * Returns an array of available ratings. Array keys are the rating number
 * and values are the string name (ie "4.5 Stars).
 *
 * @since 1.0
 * @return array
 */
function rating_report_get_rating_scale() {
	$max_rating   = rating_report_get_option( 'max_rating', 5 );
	$half_stars   = rating_report_get_option( 'half_stars', false );
	$min_rating   = $increment = $half_stars ? 0.5 : 1;
	$rating_scale = array();

	for ( $i = apply_filters( 'rating-report/rating-scale/min-rating', $min_rating ); $i <= apply_filters( 'rating-report/rating-scale/max-rating', $max_rating ); $i += apply_filters( 'rating-report/rating-scale/increment', $increment ) ) {
		$rating_scale[ (string) $i ] = apply_filters( 'rating-report/rating-scale/name', sprintf( esc_html__( '%s Stars', 'rating-report' ), $i ), $i );
	}

	return apply_filters( 'rating-report/rating-scale', $rating_scale );
}