<?php

/**
 * Handles the data migration.
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Rating_Report_Migrator {

	/**
	 * The current step being processed
	 *
	 * @var int
	 * @since 1.0
	 */
	public $step;

	/**
	 * Delete Old Data
	 *
	 * @var bool
	 * @since 1.0
	 */
	private $delete_old_data;

	/**
	 * The total number of steps
	 *
	 * @var int
	 * @since 1.0
	 */
	private $total;

	/**
	 * Constructor
	 *
	 * @param int  $step            The step to process
	 * @param bool $delete_old_data Whether or not to delete old data
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct( $step = 0, $delete_old_data = false ) {
		$this->step            = $step;
		$this->delete_old_data = $delete_old_data ? true : false;
	}

	/**
	 * Processes a step
	 *
	 * @access public
	 * @since  1.0.0
	 * @return bool|string True if there's still more to do, false if we're done, string if there was an error
	 */
	public function process_step() {

		/*
		 * If we're on the first step, then copy over the categories.
		 */
		$ubb_settings = get_option( 'ubb_settings' );
		$old_settings = get_option( 'rr-section-config' );

		if ( function_exists( 'Ultimate_Book_Blogger' ) && is_array( $ubb_settings ) && array_key_exists( 'rr_categories', $ubb_settings ) ) {

			// Copy over categories.
			$old_categories             = $ubb_settings['rr_categories'];
			$sanitized_old_categories   = array_map( 'sanitize_text_field', $old_categories );
			$new_settings['categories'] = $sanitized_old_categories;

			// Report title.
			$new_settings['table_title'] = sanitize_text_field( $ubb_settings['rr_title'] );

			// Rating types.
			$new_settings['rating_type']         = $ubb_settings['rr_individual_rating'] == 'images' ? 'images' : 'numbers';
			$new_settings['rating_type_overall'] = $ubb_settings['rr_overall_rating'] == 'images' ? 'images' : 'numbers';

			// Automatic display.
			$new_settings['display_location'] = $ubb_settings['rr_auto_display'] == 1 ? 'after' : 'off';

			update_option( 'rating_report_settings', $new_settings );

		} elseif ( is_array( $old_settings ) ) {
			$old_categories = array();

			// Collect the categories from the old settings.
			for ( $i = 1; $i <= 5; $i ++ ) {
				if ( array_key_exists( 'section_' . $i, $old_settings ) ) {
					$old_categories = $old_settings[ 'section_' . $i ];
				}
			}

			$new_settings               = get_option( 'rating_report_settings' );
			$new_settings               = is_array( $new_settings ) ? $new_settings : array();
			$sanitized_old_categories   = array_map( 'sanitize_text_field', $old_categories );
			$new_settings['categories'] = $sanitized_old_categories;

			update_option( 'rating_report_settings', $new_settings );
		}

		/*
		 * Now query for posts.
		 */

		$args = array(
			'post_status'    => 'any',
			'post_type'      => 'post',
			'posts_per_page' => 10,
			'paged'          => $this->step
		);

		$posts = new WP_Query( $args );

		if ( ! $posts->have_posts() ) {
			return false;
		}

		while ( $posts->have_posts() ) : $posts->the_post();

			/*
			 * Get old meta
			 */
			$old_ratings = array();

			for ( $i = 1; $i <= 5; $i ++ ) {
				$old_ratings[] = get_post_meta( get_the_ID(), '_ratingr_section_' . $i, true );
			}

			/*
			 * Add new meta
			 */
			update_post_meta( get_the_ID(), 'rating_report', $old_ratings );

			/*
			 * Maybe delete old meta
			 */
			if ( $this->delete_old_data === true ) {
				for ( $i = 1; $i <= 5; $i ++ ) {
					delete_post_meta( get_the_ID(), '_ratingr_section_' . $i );
				}
			}

		endwhile;
		wp_reset_postdata();

		return true;

	}

	/**
	 * Returns the calculated completion percentage
	 *
	 * @access public
	 * @since  1.0
	 * @return float|int
	 */
	public function get_percentage_complete() {
		$args = array(
			'post_type'      => 'post',
			'posts_per_page' => - 1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		);

		$posts       = new WP_Query( $args );
		$this->total = (int) $posts->post_count;

		$percentage = 100;

		if ( $this->total > 0 ) {
			$percentage = ( ( 10 * $this->step ) / $this->total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

}