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
		'type' => null // Defaults to setting value
	), $atts, 'rating-report' );

	$report_card = new Rating_Report_Card( get_the_ID(), $atts['type'] );
	$report_card->set_ratings();
	$html = $report_card->render();

	return apply_filters( 'rating-report/shortcode', $html, $atts, $content );

}

add_shortcode( 'rating-report', 'rating_report_shortcode' );