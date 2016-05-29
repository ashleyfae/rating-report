<?php
/**
 * Include and setup custom metaboxes and fields.
 *
 * @category YourThemeOrPlugin
 * @package  Metaboxes
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress
 */

add_filter( 'cmb_meta_boxes', 'rating_report_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function rating_report_metaboxes( array $meta_boxes ) {

	// Start with an underscore to hide fields from custom fields list
	$prefix = '_ratingr_';
	// Array
	$section = get_option('rr-section-config');
	
	$meta_boxes[] = array(
		'id'         => 'rating_report_metabox',
		'title'      => 'Rating Report',
		'pages'      => array( 'post', ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'show_names' => true, // Show field names on the left
		'fields'     => array(
			//Plot
			array(
				'name'    => $section[section_1],
				'id'      => $prefix . 'section_1',
				'type'    => 'select',
				'options' => array(
					array( 'name' => '- Select -', 'value' => 'select', ),
					array( 'name' => '5 Stars', 'value' => '5', ),
					array( 'name' => '4.5 Stars', 'value' => '4.5', ),
					array( 'name' => '4 Stars', 'value' => '4', ),
					array( 'name' => '3.5 Stars', 'value' => '3.5', ),
					array( 'name' => '3 Stars', 'value' => '3', ),
					array( 'name' => '2.5 Stars', 'value' => '2.5', ),
					array( 'name' => '2 Stars', 'value' => '2', ),
					array( 'name' => '1.5 Stars', 'value' => '1.5', ),
					array( 'name' => '1 Star', 'value' => '1', ),
					array( 'name' => '0.5 Stars', 'value' => '0.5', ),
				),
			),
			//Characters
			array(
				'name'    => $section[section_2],
				'id'      => $prefix . 'section_2',
				'type'    => 'select',
				'options' => array(
					array( 'name' => '- Select -', 'value' => 'select', ),
					array( 'name' => '5 Stars', 'value' => '5', ),
					array( 'name' => '4.5 Stars', 'value' => '4.5', ),
					array( 'name' => '4 Stars', 'value' => '4', ),
					array( 'name' => '3.5 Stars', 'value' => '3.5', ),
					array( 'name' => '3 Stars', 'value' => '3', ),
					array( 'name' => '2.5 Stars', 'value' => '2.5', ),
					array( 'name' => '2 Stars', 'value' => '2', ),
					array( 'name' => '1.5 Stars', 'value' => '1.5', ),
					array( 'name' => '1 Star', 'value' => '1', ),
					array( 'name' => '0.5 Stars', 'value' => '0.5', ),
				),
			),
			//Writing
			array(
				'name'    => $section[section_3],
				'id'      => $prefix . 'section_3',
				'type'    => 'select',
				'options' => array(
					array( 'name' => '- Select -', 'value' => 'select', ),
					array( 'name' => '5 Stars', 'value' => '5', ),
					array( 'name' => '4.5 Stars', 'value' => '4.5', ),
					array( 'name' => '4 Stars', 'value' => '4', ),
					array( 'name' => '3.5 Stars', 'value' => '3.5', ),
					array( 'name' => '3 Stars', 'value' => '3', ),
					array( 'name' => '2.5 Stars', 'value' => '2.5', ),
					array( 'name' => '2 Stars', 'value' => '2', ),
					array( 'name' => '1.5 Stars', 'value' => '1.5', ),
					array( 'name' => '1 Star', 'value' => '1', ),
					array( 'name' => '0.5 Stars', 'value' => '0.5', ),
				),
			),
			//Pacing
			array(
				'name'    => $section[section_4],
				'id'      => $prefix . 'section_4',
				'type'    => 'select',
				'options' => array(
					array( 'name' => '- Select -', 'value' => 'select', ),
					array( 'name' => '5 Stars', 'value' => '5', ),
					array( 'name' => '4.5 Stars', 'value' => '4.5', ),
					array( 'name' => '4 Stars', 'value' => '4', ),
					array( 'name' => '3.5 Stars', 'value' => '3.5', ),
					array( 'name' => '3 Stars', 'value' => '3', ),
					array( 'name' => '2.5 Stars', 'value' => '2.5', ),
					array( 'name' => '2 Stars', 'value' => '2', ),
					array( 'name' => '1.5 Stars', 'value' => '1.5', ),
					array( 'name' => '1 Star', 'value' => '1', ),
					array( 'name' => '0.5 Stars', 'value' => '0.5', ),
				),
			),
			
			//Cover
			array(
				'name'    => $section[section_5],
				'id'      => $prefix . 'section_5',
				'type'    => 'select',
				'options' => array(
					array( 'name' => '- Select -', 'value' => 'select', ),
					array( 'name' => '5 Stars', 'value' => '5', ),
					array( 'name' => '4.5 Stars', 'value' => '4.5', ),
					array( 'name' => '4 Stars', 'value' => '4', ),
					array( 'name' => '3.5 Stars', 'value' => '3.5', ),
					array( 'name' => '3 Stars', 'value' => '3', ),
					array( 'name' => '2.5 Stars', 'value' => '2.5', ),
					array( 'name' => '2 Stars', 'value' => '2', ),
					array( 'name' => '1.5 Stars', 'value' => '1.5', ),
					array( 'name' => '1 Star', 'value' => '1', ),
					array( 'name' => '0.5 Stars', 'value' => '0.5', ),
				),
			),
		),
	);



	// Add other metaboxes as needed

	return $meta_boxes;
}

add_action( 'init', 'cmb_initialize_rating_report_metaboxes', 9999 );
/**
 * Initialize the metabox class.
 */
function cmb_initialize_rating_report_metaboxes() {

	if ( ! class_exists( 'cmb_Meta_Box' ) )
		require_once 'init.php';

}

?>