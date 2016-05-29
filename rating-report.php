<?php
/*
Plugin Name: Rating Report
Plugin URI: http://www.creativewhim.com/shop/rating-report-plugin/
Description: Generate a rating report for your book or product.  Configure the categories, choose a rating image, and you're good to go!
Version: 1.0
Author: Ashley Evans
Author URI: http://booknook.me
*/

/*
 * Add settings link on plugin page
 */
function rr_plugin_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=rating-report">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'rr_plugin_settings_link' );

function rr_register_styles()
{
	// Register the style like this for a plugin:
	wp_register_style( 'rr-style', plugins_url( '/rating-report.css', __FILE__ ), array(), '20120208', 'all' );
	// For either a plugin or a theme, you can then enqueue the style:
	wp_enqueue_style( 'rr-style' );
}
add_action( 'wp_enqueue_scripts', 'rr_register_styles' );

/* Includes */
require_once('meta-boxes.php');
include('admin-page.php');

function rr_get_star_graphic() {
	$options = get_option('rr-section-config');
	$star = $options[star_graphic];
	
	$graphic = array(
		1	=>	'',
		2	=>	'smile-yellow',
		3	=>	'rr-circle-blue',
		4	=>	'rr-circle-pink',
		5	=>	'rr-circle-red',
		6	=>	'rr-circle-green',
		7	=>	'rr-circle-yellow',
		8	=>	'rr-circle-black',
		9	=>	'rr-green',
		10	=>	'rr-orange',
		11	=>	'rr-purple',
		12	=>	'rr-red',
		13	=>	'rr-black',
		14	=>	'rr-custom'
	);
	
	return $graphic[$star];
}

function rr_get_report() {
	$section = get_option('rr-section-config');
	global $post;
	$ratingcompile = array();
	$finalrating = 0;
	$total = 0;
	
	$rating_array = array(
		'Section 1'	=>	array('title' => $section[section_1], 'rating' => get_post_meta($post->ID, '_ratingr_section_1', true)),
		'Section 2'	=>	array('title' => $section[section_2], 'rating' => get_post_meta($post->ID, '_ratingr_section_2', true)),
		'Section 3'	=>	array('title' => $section[section_3], 'rating' => get_post_meta($post->ID, '_ratingr_section_3', true)),
		'Section 4'	=>	array('title' => $section[section_4], 'rating' => get_post_meta($post->ID, '_ratingr_section_4', true)),
		'Section 5'	=>	array('title' => $section[section_5], 'rating' => get_post_meta($post->ID, '_ratingr_section_5', true)),
	);
	
	$wrapstart = '<table class="rating-report"><tr><th colspan="2">Rating Report</th></tr>';
	
	foreach ($rating_array as $category) {
		$rating = $category[rating];
		if($rating != null && $rating != 0 && $category[title] != null && $category[title] != '') {
			$finalrating += $rating;
			$total++;
			$rating = round(($rating / 5) * 100);
			if($section[star_graphic] == 14) {
				$filled = $section['custom_upload_filled'];
				$grey = $section['custom_upload_grey'];
				$curl_geturl = curl_init();
				curl_setopt($curl_geturl, CURLOPT_URL, $filled);
				curl_setopt($curl_geturl, CURLOPT_BINARYTRANSFER, 1);
				curl_setopt($curl_geturl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl_geturl, CURLOPT_CONNECTTIMEOUT, 3);
				curl_setopt($curl_geturl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl_geturl, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($curl_geturl, CURLOPT_TIMEOUT, 6);
				ob_start();
				$curl_return_url = curl_exec($curl_geturl);
				ob_end_clean();
				$curl_http_return = curl_getinfo($curl_geturl, CURLINFO_HTTP_CODE);
				curl_close($curl_geturl);
				if ($curl_http_return == 200 && strlen($curl_return_url) > 0) {
					$img_resource = imagecreatefromstring($curl_return_url);
					$img['width'] = imagesx($img_resource);
					$img['height'] = imagesy($img_resource);
				}
				else {
					// Failed to retrieve the image, invalid URL maybe?
					$img['width'] = $img['height'] = 0;
				}
				$ratingcompile[] = '<tr><td class="section-title">' . $category[title] . '</td><td class="section-rating"><div class="rr-rating rr-custom" style="height:' . $img[height] . 'px; width:' . $img[width]*5 . 'px; background:url(\'' . $grey . '\') repeat-x;"><div class="rr-stars" style="width:' . $rating . '%; height:' . $img[height] . 'px; background:url(\'' . $filled . '\') repeat-x 0 100%;"></div></div></td></tr>';
			}
			else {
				$ratingcompile[] = '<tr><td class="section-title">' . $category[title] . '</td><td class="section-rating"><div class="rr-rating ' . rr_get_star_graphic() . '"><div class="rr-stars" style="width:' . $rating . '%;"></div></div></td></tr>';
			}
		}
	}
	
	if($total !=0 ) {
		$finalrating = round(($finalrating / $total), 1);
		$stars = round(($finalrating / 5) * 100);
		$finalscore = '<tr class="overall"><td class="section-title">Overall:</td> <td class="section-rating">' . $finalrating . '</td></tr>';
	}
	
	$wrapend .= '</table>';
	$output = $wrapstart . implode('', $ratingcompile) . $finalscore . $wrapend;
	
	if($total != 0) {return $output; }
	else { return ''; }
}

/* Shortcode */
function rating_report_register_shortcodes() {
	add_shortcode('rating-report', 'rr_get_report');
}
add_action('init', 'rating_report_register_shortcodes');

/* Add to End of Post */
function add_rating_report_after_content($content) {
	$report = get_option('rr-section-config');
	$display = $report[report_display];
	if(in_array('get_the_excerpt', $GLOBALS['wp_current_filter'])) {
		return $content;
	}
	else {
		if ($display == '1') {
			return $content . rr_get_report();
		}
		else {
			return $content;
		}
	}
}
add_filter('the_content', 'add_rating_report_after_content');