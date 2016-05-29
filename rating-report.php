<?php
/**
 * Plugin Name: Rating Report
 * Plugin URI: https://shop.nosegraze.com/product/rating-report/
 * Description: Generate a rating report card based on different criteria.
 * Version: 1.0
 * Author: Nose Graze
 * Author URI: https://www.nosegraze.com
 * License: GPL2
 * Text Domain: rating-report
 * Domain Path: languages
 *
 * Rating Report is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Rating Report is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Rating Report. If not, see <http://www.gnu.org/licenses/>.
 *
 * Thanks to Easy Digital Downloads for serving as a great code base
 * and resource, which a lot of Rating Report's structure is based on.
 * Easy Digital Downloads is made by Pippin Williamson and licensed
 * under GPL2+.
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016 Nose Graze Ltd
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Rating_Report' ) ) :
	
	class Rating_Report {

		/**
		 * Rating_Report object
		 *
		 * @var Rating_Report Instance of the Rating_Report class.
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Rating_Report instance.
		 *
		 * Insures that only one instance of Rating_Report exists at any one time.
		 *
		 * @uses   Rating_Report::setup_constants() Set up the plugin constants.
		 * @uses   Rating_Report::includes() Include any required files.
		 * @uses   Rating_Report::load_textdomain() Load the language files.
		 *
		 * @access public
		 * @since  1.0
		 * @return Rating_Report Instance of Rating_Report class
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! self::$instance instanceof Rating_Report ) {
				self::$instance = new Rating_Report;
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				self::$instance->includes();
			}

			return self::$instance;

		}

		/**
		 * Throw error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
		 * @access protected
		 * @since  1.0
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'rating-report' ), '1.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access protected
		 * @since  1.0
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'rating-report' ), '1.0' );
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since  1.0
		 * @return void
		 */
		private function setup_constants() {

			// Plugin version.
			if ( ! defined( 'RATING_REPORT_VERSION' ) ) {
				define( 'RATING_REPORT_VERSION', '1.0.0' );
			}

			// Plugin Folder Path.
			if ( ! defined( 'RATING_REPORT_PLUGIN_DIR' ) ) {
				define( 'RATING_REPORT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'RATING_REPORT_PLUGIN_URL' ) ) {
				define( 'RATING_REPORT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'RATING_REPORT_PLUGIN_FILE' ) ) {
				define( 'RATING_REPORT_PLUGIN_FILE', __FILE__ );
			}

		}

		/**
		 * Include Required Files
		 *
		 * @access private
		 * @since  1.0
		 * @return void
		 */
		private function includes() {

			global $rating_report_options;

			// Settings.
			require_once RATING_REPORT_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
			if ( empty( $rating_report_options ) ) {
				//$rating_report_options = rating_report_get_settings();
			}
			
			require_once RATING_REPORT_PLUGIN_DIR . 'includes/class-rating-report-card.php';
			require_once RATING_REPORT_PLUGIN_DIR . 'includes/load-assets.php';
			require_once RATING_REPORT_PLUGIN_DIR . 'includes/shortcodes.php';

			if ( is_admin() ) {
				//require_once RATING_REPORT_PLUGIN_DIR . 'includes/admin/admin-actions.php';
				require_once RATING_REPORT_PLUGIN_DIR . 'includes/admin/admin-pages.php';
				require_once RATING_REPORT_PLUGIN_DIR . 'includes/admin/class-rr-notices.php';
				require_once RATING_REPORT_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
				require_once RATING_REPORT_PLUGIN_DIR . 'includes/admin/meta-box.php';
				require_once RATING_REPORT_PLUGIN_DIR . 'includes/admin/sanitize-meta-fields.php';
			}

			//require_once RATING_REPORT_PLUGIN_DIR . 'includes/install.php';

		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since  1.0
		 * @return void
		 */
		public function load_textdomain() {

			$lang_dir = dirname( plugin_basename( RATING_REPORT_PLUGIN_FILE ) ) . '/languages/';
			$lang_dir = apply_filters( 'rating-report/languages-directory', $lang_dir );
			load_plugin_textdomain( 'rating-report', false, $lang_dir );

		}
		
	}

endif;

/**
 * Get Rating_Report up and running.
 *
 * This function returns an instance of the Rating_Report class.
 *
 * @since 1.0
 * @return Rating_Report
 */
function Rating_Report() {
	return Rating_Report::instance();
}

Rating_Report();