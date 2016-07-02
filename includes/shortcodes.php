<?php
/**
 * Register Shortcodes
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

/**
 * Rating Report Shortcode
 *
 * @param array       $atts
 * @param null|string $content
 *
 * @since 1.0
 * @return string
 */
function rating_report_shortcode( $atts, $content = null ) {

	$atts = shortcode_atts( array(
		'type'         => null, // Defaults to setting value
		'categories'   => null, // Defaults to global settings
		'ratings'      => null, // Defaults to post meta
		'descriptions' => null  // Defaults to post meta
	), $atts, 'rating-report' );

	$report_card = new Rating_Report_Card( get_the_ID(), $atts['type'] );

	// Set up override categories
	if ( $atts['categories'] !== null ) {
		$categories = explode( ',', $atts['categories'] );
		$report_card->set_categories( array_map( 'trim', $categories ) );
	}

	// Set up override ratings
	if ( $atts['ratings'] !== null ) {
		$numbers = explode( ',', $atts['ratings'] );
		$report_card->set_numbers( array_map( 'trim', $numbers ) );
	}

	// Set up override descriptions
	if ( $atts['descriptions'] !== null ) {
		$descriptions = explode( ',', $atts['descriptions'] );
		$report_card->set_descriptions( array_map( 'trim', $descriptions ) );
	}

	$report_card->set_ratings();
	$html = $report_card->render();

	return apply_filters( 'rating-report/shortcode', $html, $atts, $content );

}

add_shortcode( 'rating-report', 'rating_report_shortcode' );