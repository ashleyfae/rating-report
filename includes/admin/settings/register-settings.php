<?php
/**
 * Register Plugin Settings
 *
 * Based on register-settings.php in Easy Digital Downloads.
 *
 * @package   rating-report
 * @copyright Copyright (c) 2016, Nose Graze Ltd.
 * @license   GPL2+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get an Option
 *
 * Looks to see if the specified setting exists, returns the default if not.
 *
 * @param string $key     Key to retrieve
 * @param mixed  $default Default option
 *
 * @global       $rating_report_options
 *
 * @since 2.0
 * @return mixed
 */
function rating_report_get_option( $key = '', $default = false ) {
	global $rating_report_options;

	$value = ! empty( $rating_report_options[ $key ] ) ? $rating_report_options[ $key ] : $default;
	$value = apply_filters( 'rating-report/options/get', $value, $key, $default );

	return apply_filters( 'rating-report/options/get/' . $key, $value, $key, $default );
}

/**
 * Update an Option
 *
 * Updates an existing setting value in both the DB and the global variable.
 * Passing in an empty, false, or null string value will remove the key from the rating_report_settings array.
 *
 * @param string $key   Key to update
 * @param mixed  $value The value to set the key to
 *
 * @global       $rating_report_options
 *
 * @since 2.0
 * @return bool True if updated, false if not
 */
function rating_report_update_option( $key = '', $value = false ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	if ( empty( $value ) ) {
		$remove_option = rating_report_delete_option( $key );

		return $remove_option;
	}

	// First let's grab the current settings
	$options = get_option( 'rating_report_settings' );

	// Let's let devs alter that value coming in
	$value = apply_filters( 'rating-report/options/update', $value, $key );

	// Next let's try to update the value
	$options[ $key ] = $value;
	$did_update      = update_option( 'rating_report_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $rating_report_options;
		$rating_report_options[ $key ] = $value;
	}

	return $did_update;
}

/**
 * Remove an Option
 *
 * Removes an setting value in both the DB and the global variable.
 *
 * @param string $key The key to delete.
 *
 * @global       $rating_report_options
 *
 * @since 2.0
 * @return boolean True if updated, false if not.
 */
function rating_report_delete_option( $key = '' ) {
	// If no key, exit
	if ( empty( $key ) ) {
		return false;
	}

	// First let's grab the current settings
	$options = get_option( 'rating_report_settings' );

	// Next let's try to update the value
	if ( isset( $options[ $key ] ) ) {
		unset( $options[ $key ] );
	}

	$did_update = update_option( 'rating_report_settings', $options );

	// If it updated, let's update the global variable
	if ( $did_update ) {
		global $rating_report_options;
		$rating_report_options = $options;
	}

	return $did_update;
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 2.0
 * @return array Rating Report settings
 */
function rating_report_get_settings() {
	$settings = get_option( 'rating_report_settings', array() );

	return apply_filters( 'rating-report/get-settings', $settings );
}

/**
 * Add all settings sections and fields.
 *
 * @since 2.0
 * @return void
 */
function rating_report_register_settings() {

	if ( false == get_option( 'rating_report_settings' ) ) {
		add_option( 'rating_report_settings' );
	}

	foreach ( rating_report_get_registered_settings() as $tab => $sections ) {
		foreach ( $sections as $section => $settings ) {
			add_settings_section(
				'rating_report_settings_' . $tab . '_' . $section,
				__return_null(),
				'__return_false',
				'rating_report_settings_' . $tab . '_' . $section
			);

			foreach ( $settings as $option ) {
				// For backwards compatibility
				if ( empty( $option['id'] ) ) {
					continue;
				}

				$name = isset( $option['name'] ) ? $option['name'] : '';

				add_settings_field(
					'rating_report_settings[' . $option['id'] . ']',
					$name,
					function_exists( 'rating_report_' . $option['type'] . '_callback' ) ? 'rating_report_' . $option['type'] . '_callback' : 'rating_report_missing_callback',
					'rating_report_settings_' . $tab . '_' . $section,
					'rating_report_settings_' . $tab . '_' . $section,
					array(
						'section'     => $section,
						'id'          => isset( $option['id'] ) ? $option['id'] : null,
						'desc'        => ! empty( $option['desc'] ) ? $option['desc'] : '',
						'name'        => isset( $option['name'] ) ? $option['name'] : null,
						'size'        => isset( $option['size'] ) ? $option['size'] : null,
						'options'     => isset( $option['options'] ) ? $option['options'] : '',
						'std'         => isset( $option['std'] ) ? $option['std'] : '',
						'min'         => isset( $option['min'] ) ? $option['min'] : null,
						'max'         => isset( $option['max'] ) ? $option['max'] : null,
						'step'        => isset( $option['step'] ) ? $option['step'] : null,
						'chosen'      => isset( $option['chosen'] ) ? $option['chosen'] : null,
						'placeholder' => isset( $option['placeholder'] ) ? $option['placeholder'] : null
					)
				);
			}
		}
	}

	// Creates our settings in the options table
	register_setting( 'rating_report_settings', 'rating_report_settings', 'rating_report_settings_sanitize' );

}

add_action( 'admin_init', 'rating_report_register_settings' );

/**
 * Registered Settings
 *
 * Sets and returns the array of all plugin settings.
 * Developers can use the following filters to add their own settings or
 * modify existing ones:
 *
 *  + rating-report/settings/{key} - Where {key} is a specific tab. Used to modify a single tab/section.
 *  + rating-report/settings/registered-settings - Includes the entire array of all settings.
 *
 * @since 2.0
 * @return array
 */
function rating_report_get_registered_settings() {

	$rating_report_settings = array(
		/* Category Settings */
		'categories' => apply_filters( 'rating-report/settings/categories', array(
			'main' => array(
				'categories' => array(
					'id'      => 'categories',
					'name'    => esc_html__( 'Rating Categories', 'rating-report' ),
					'desc'    => __( 'Set up the categories you\'ll use for rating criteria.', 'rating-report' ),
					'type'    => 'repeat_text',
					'std'     => rating_report_get_default_categories(),
					'options' => array(
						'label' => esc_html__( 'Add Category', 'rating-report' )
					)
				)
			)
		) ),
		/* Display Settings */
		'display'    => apply_filters( 'rating-report/settings/display', array(
			'main'        => array(
				'display_location'     => array(
					'id'      => 'display_location',
					'name'    => esc_html__( 'Automatic Display', 'rating-report' ),
					'desc'    => __( 'If a location is selected, the rating report will be automatically added to your blog posts when the ratings are filled out.', 'rating-report' ),
					'type'    => 'select',
					'std'     => 'off',
					'options' => array(
						'off'    => esc_html__( 'Off', 'rating-report' ),
						'before' => esc_html__( 'Before Post', 'rating-report' ),
						'after'  => esc_html__( 'After Post', 'rating-report' )
					)
				),
				'display_type'         => array(
					'id'      => 'display_type',
					'name'    => esc_html__( 'Display Type', 'rating-report' ),
					'desc'    => __( 'What the rating report looks like on the front-end.', 'rating-report' ),
					'type'    => 'select',
					'std'     => 'table',
					'options' => array(
						'table'            => esc_html__( 'Table', 'rating-report' ),
						'horizontal_graph' => esc_html__( 'Horizontal Graph', 'rating-report' ),
						'vertical_graph'   => esc_html__( 'Vertical Graph', 'rating-report' )
					)
				),
				'disable_styles'       => array(
					'id'   => 'disable_styles',
					'name' => __( 'Disable Styles', 'rating-report' ),
					'desc' => __( 'Check this to disable the Rating Report stylesheet from being added to your site.', 'rating-report' ),
					'type' => 'checkbox',
					'std'  => false
				),
				'include_font_awesome' => array(
					'id'   => 'include_font_awesome',
					'name' => __( 'Load Font Awesome', 'rating-report' ),
					'desc' => __( 'Check this to load the Font Awesome stylesheet. You only need to enable this if Font Awesome is not already included by your theme or another plugin.', 'rating-report' ),
					'type' => 'checkbox',
					'std'  => false
				),
			),
			'text'        => array(
				'table_text_header'              => array(
					'id'   => 'table_text_header',
					'name' => esc_html__( 'Table Text', 'rating-report' ),
					'desc' => __( 'Customize all the text used in the Rating Report "table" display.', 'rating-report' ),
					'type' => 'header'
				),
				'table_title'                    => array(
					'id'   => 'table_title',
					'name' => esc_html__( 'Table Title', 'rating-report' ),
					'desc' => __( 'Shown at the top, before the ratings.', 'rating-report' ),
					'type' => 'text',
					'std'  => esc_html__( 'Rating Report', 'rating-report' )
				),
				'table_overall_label'            => array(
					'id'   => 'table_overall_label',
					'name' => esc_html__( 'Overall Rating', 'rating-report' ),
					'desc' => __( 'This text is shown before the overall rating. Examples might be: Overall, Final Rating, Average Rating.', 'rating-report' ),
					'type' => 'text',
					'std'  => esc_html__( 'Overall', 'rating-report' )
				),
				'table_category_rating_template' => array(
					'id'   => 'table_category_rating_template',
					'name' => esc_html__( 'Category Template', 'rating-report' ),
					'desc' => sprintf( __( 'Template used for displaying the rating of each category. Use %s as a placeholder for the actual star rating.', 'rating-report' ), '<code>%s</code>' ),
					'type' => 'text',
					'std'  => __( '%s stars', 'rating-report' )
				)
			),
			'graph-style' => array(
				'graph_style_header' => array(
					'id'   => 'graph_style_header',
					'name' => esc_html__( 'Graph Style', 'rating-report' ),
					'desc' => __( 'Customize the colours and display for the "graph" format.', 'rating-report' ),
					'type' => 'header'
				),
				'bar_color'             => array(
					'id'   => 'bar_color',
					'name' => esc_html__( 'Bar Colour', 'rating-report' ),
					'desc' => __( 'Colour of the filled in section of the bar.', 'rating-report' ),
					'type' => 'color',
					'std'  => '#3CB2D2'
				),
				'bar_bg'   => array(
					'id'   => 'bar_bg',
					'name' => esc_html__( 'Bar Background', 'rating-report' ),
					'desc' => __( 'Colour of the "empty" section of the bar. This will fill up to the maximum level.', 'rating-report' ),
					'type' => 'color',
					'std'  => '#eeeeee'
				),
				'show_numbers'       => array(
					'id'   => 'show_numbers',
					'name' => __( 'Show Numbers', 'rating-report' ),
					'desc' => __( 'Check this to display your rating numbers inside the bar graph.', 'rating-report' ),
					'type' => 'checkbox',
					'std'  => false
				),
			)
		) ),
		/* Rating Settings */
		'ratings'    => apply_filters( 'rating-report/settings/ratings', array(
			'main'     => array(
				'max_rating'          => array(
					'id'   => 'max_rating',
					'name' => esc_html__( 'Maximum Rating', 'rating-report' ),
					'desc' => __( 'Enter the maximum rating number you want to support. Example: enter 5 to use a 5-star scale, or enter 10 to use a 10-star scale.', 'rating-report' ),
					'type' => 'number',
					'std'  => '5',
				),
				'half_stars'          => array(
					'id'   => 'half_stars',
					'name' => __( 'Enable Half Stars', 'rating-report' ),
					'desc' => __( 'Check to enable half stars.', 'rating-report' ),
					'type' => 'checkbox',
					'std'  => false
				),
				'rating_type'         => array(
					'id'      => 'rating_type',
					'name'    => esc_html__( 'Rating Type (Categories)', 'rating-report' ),
					'desc'    => sprintf( __( 'What type of ratings to show on the table. Only applies to "Table" style. If you choose graphics, be sure to <a href="%s">choose your star graphics</a>.', 'rating-report' ), esc_url( admin_url( 'options-general.php?page=rating-report&tab=ratings&section=graphics' ) ) ),
					'type'    => 'select',
					'std'     => 'numbers',
					'options' => array(
						'numbers'      => esc_html__( 'Numbers', 'rating-report' ),
						'images'       => esc_html__( 'Images', 'rating-report' ),
						'font_awesome' => esc_html__( 'Font Awesome Stars', 'rating-report' )
					)
				),
				'rating_type_overall' => array(
					'id'      => 'rating_type_overall',
					'name'    => esc_html__( 'Rating Type (Overall)', 'rating-report' ),
					'desc'    => sprintf( __( 'What type of ratings to show on the table. Only applies to "Table" style. If you choose graphics, be sure to <a href="%s">choose your star graphics</a>. Note that if you choose Images or Font Awesome Stars, the overall rating will be rounded to the nearest whole or half number (depending on whether or not you support half stars). The Number option will show an accurate decimal.', 'rating-report' ), esc_url( admin_url( 'options-general.php?page=rating-report&tab=ratings&section=graphics' ) ) ),
					'type'    => 'select',
					'std'     => 'numbers',
					'options' => array(
						'numbers'      => esc_html__( 'Numbers', 'rating-report' ),
						'images'       => esc_html__( 'Images', 'rating-report' ),
						'font_awesome' => esc_html__( 'Font Awesome Stars', 'rating-report' )
					)
				),
			),
			'graphics' => array(
				'empty_star' => array(
					'id'   => 'empty_star',
					'name' => esc_html__( 'Empty Star Graphic', 'rating-report' ),
					'desc' => __( 'Optional. If set, this graphic will be used to "fill up" the rating to your maximum stars.', 'rating-report' ),
					'type' => 'image',
					'std'  => ''
				),
				'half_star'  => array(
					'id'   => 'half_star',
					'name' => esc_html__( 'Half Star Graphic', 'rating-report' ),
					'type' => 'image',
					'std'  => ''
				),
				'full_star'  => array(
					'id'   => 'full_star',
					'name' => esc_html__( 'Full Star Graphic', 'rating-report' ),
					'type' => 'image',
					'std'  => ''
				)
			)
		) ),
		/* Misc */
		'misc'       => apply_filters( 'rating-report/settings/misc', array(
			'main' => array(
				'uninstall_on_delete' => array(
					'id'   => 'uninstall_on_delete',
					'name' => __( 'Delete Data', 'rating-report' ),
					'desc' => __( 'Check this to delete all Rating Report data when deleting the plugin, including settings and all ratings.', 'rating-report' ),
					'type' => 'checkbox',
					'std'  => false
				)
			)
		) )
	);

	return apply_filters( 'rating-report/settings/registered-settings', $rating_report_settings );

}

/**
 * Sanitize Settings
 *
 * Adds a settings error for the updated message.
 *
 * @param array  $input                 The value inputted in the field
 *
 * @global array $rating_report_options Array of all the Rating Report options
 *
 * @since 2.0
 * @return array New, sanitized settings.
 */
function rating_report_settings_sanitize( $input = array() ) {

	global $rating_report_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	if ( ! is_array( $rating_report_options ) ) {
		$rating_report_options = array();
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = rating_report_get_registered_settings();
	$tab      = ( isset( $referrer['tab'] ) && $referrer['tab'] != 'import_export' ) ? $referrer['tab'] : 'categories';
	$section  = isset( $referrer['section'] ) ? $referrer['section'] : 'main';

	$input = $input ? $input : array();
	$input = apply_filters( 'rating-report/settings/sanitize/' . $tab . '/' . $section, $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {
		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[ $tab ][ $section ][ $key ]['type'] ) ? $settings[ $tab ][ $section ][ $key ]['type'] : false;
		if ( $type ) {
			// Field type specific filter
			$input[ $key ] = apply_filters( 'rating-report/settings/sanitize/' . $type, $value, $key );
		}
		// General filter
		$input[ $key ] = apply_filters( 'rating-report/settings/sanitize', $input[ $key ], $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	$main_settings    = $section == 'main' ? $settings[ $tab ] : array();
	$section_settings = ! empty( $settings[ $tab ][ $section ] ) ? $settings[ $tab ][ $section ] : array();
	$found_settings   = array_merge( $main_settings, $section_settings );

	if ( ! empty( $found_settings ) ) {
		foreach ( $found_settings as $key => $value ) {
			if ( ( empty( $input[ $key ] ) || ! array_key_exists( $key, $input ) ) && array_key_exists( $key, $rating_report_options ) ) {
				unset( $rating_report_options[ $key ] );
			}
		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $rating_report_options, $input );

	add_settings_error( 'rating-report-notices', '', __( 'Settings updated.', 'rating-report' ), 'updated' );

	return $output;

}

/**
 * Display "Default settings restored" message.
 * This gets displayed after the default settings have been restored and
 * the page has been redirected.
 *
 * @since 2.0
 * @return void
 */
function rating_report_defaults_restored_message() {
	if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'rating-report-settings' ) {
		return;
	}

	if ( ! isset( $_GET['defaults-restored'] ) || $_GET['defaults-restored'] !== 'true' ) {
		return;
	}

	add_settings_error( 'rating-report-notices', '', __( 'Default settings restored.', 'rating-report' ), 'updated' );
}

add_action( 'admin_init', 'rating_report_defaults_restored_message' );

/**
 * Restore Defaults
 *
 * Ajax callback that restores the default settings for a specific tab.
 *
 * @since 2.0
 * @return void
 */
function rating_report_restore_default_settings() {
	// Permission check.
	if ( ! current_user_can( 'manage_rating_report_settings' ) ) {
		wp_die( __( 'Bugger off! You don\'t have permission to do this.', 'rating-report' ) );
	}

	global $rating_report_options;
	$tab              = strip_tags( $_POST['tab'] );
	$section          = strip_tags( $_POST['section'] );
	$default_settings = rating_report_get_registered_settings();

	// Tab is missing.
	if ( ! array_key_exists( $tab, $default_settings ) ) {
		wp_send_json_error( __( 'Error: Tab missing.', 'rating-report' ) );
	}

	// Loop through each section.
	foreach ( $default_settings[ $tab ] as $section_id => $settings ) {
		if ( ! is_array( $settings ) ) {
			continue;
		}

		foreach ( $settings as $key => $options ) {
			if ( ! array_key_exists( 'std', $options ) ) {
				continue;
			}

			$rating_report_options[ $key ] = $options['std'];
		}
	}

	// Update options.
	update_option( 'rating_report_settings', $rating_report_options );

	// Build our URL
	$url    = admin_url( 'options-general.php' );
	$params = array(
		'page'              => 'rating-report',
		'tab'               => urlencode( $tab ),
		'section'           => urlencode( $section ),
		'defaults-restored' => 'true'
	);
	$url    = add_query_arg( $params, $url );

	wp_send_json_success( $url );
}

add_action( 'wp_ajax_rating_report_restore_default_settings', 'rating_report_restore_default_settings' );

/**
 * Sanitize Text Field
 *
 * @param string $input
 *
 * @since 2.0
 * @return string
 */
function rating_report_settings_sanitize_text_field( $input ) {
	return wp_kses_post( $input );
}

add_filter( 'rating-report/settings/sanitize/text', 'rating_report_settings_sanitize_text_field' );

/**
 * Sanitize Number Field
 *
 * @param string $input
 *
 * @since 2.0
 * @return int
 */
function rating_report_settings_sanitize_number_field( $input ) {
	return absint( $input );
}

add_filter( 'rating-report/settings/sanitize/number', 'rating_report_settings_sanitize_number_field' );

/**
 * Sanitize Select Field
 *
 * @param string $input
 *
 * @since 2.0
 * @return string
 */
function rating_report_settings_sanitize_select_field( $input ) {
	return sanitize_text_field( $input );
}

add_filter( 'rating-report/settings/sanitize/select', 'rating_report_settings_sanitize_select_field' );

/**
 * Sanitize Color Field
 *
 * Return 3 or 6 hex digits, or an empty string.
 *
 * @param string $input
 *
 * @since 2.0
 * @return string
 */
function rating_report_settings_sanitize_color_field( $input ) {
	if ( ! empty( $input ) && preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $input ) ) {
		return $input;
	}

	return '';
}

add_filter( 'rating-report/settings/sanitize/color', 'rating_report_settings_sanitize_color_field' );

/**
 * Sanitize Image Field
 *
 * Should be an integer (attachment ID).
 *
 * @param int $input
 *
 * @since 2.0
 * @return int
 */
function rating_report_settings_sanitize_image_field( $input ) {
	return absint( $input );
}

add_filter( 'rating-report/settings/sanitize/image', 'rating_report_settings_sanitize_image_field' );

/**
 * Sanitize Checkbox Field
 *
 * Returns either true or false.
 *
 * @param bool $input
 *
 * @since 2.0
 * @return bool
 */
function rating_report_settings_sanitize_checkbox_field( $input ) {
	return ! empty( $input ) ? true : false;
}

add_filter( 'rating-report/settings/sanitize/checkbox', 'rating_report_settings_sanitize_checkbox_field' );

/**
 * Sanitize Repeatable Text
 *
 * @param array $input
 *
 * @since 2.0
 * @return array
 */
function rating_report_settings_sanitize_repeat_text( $input ) {
	$new_input = array();

	if ( ! is_array( $input ) ) {
		return $new_input;
	}

	$new_input = array_map( 'sanitize_text_field', $input );

	return $new_input;
}

add_filter( 'rating-report/settings/sanitize/repeat_text', 'rating_report_settings_sanitize_repeat_text' );

/**
 * Retrieve settings tabs
 *
 * @since 2.0
 * @return array $tabs
 */
function rating_report_get_settings_tabs() {
	$tabs               = array();
	$tabs['categories'] = __( 'Categories', 'rating-report' );
	$tabs['display']    = __( 'Display', 'rating-report' );
	$tabs['ratings']    = __( 'Ratings', 'rating-report' );
	$tabs['misc']       = __( 'Misc', 'rating-report' );

	return apply_filters( 'rating-report/settings/tabs', $tabs );
}

/**
 * Retrieve settings tabs
 *
 * @since 2.0
 * @return array $section
 */
function rating_report_get_settings_tab_sections( $tab = false ) {
	$tabs     = false;
	$sections = rating_report_get_registered_settings_sections();

	if ( $tab && ! empty( $sections[ $tab ] ) ) {
		$tabs = $sections[ $tab ];
	} else if ( $tab ) {
		$tabs = false;
	}

	return $tabs;
}

/**
 * Get the settings sections for each tab
 * Uses a static to avoid running the filters on every request to this function
 *
 * @since  2.0
 * @return array Array of tabs and sections
 */
function rating_report_get_registered_settings_sections() {
	static $sections = false;

	if ( false !== $sections ) {
		return $sections;
	}

	$sections = array(
		'categories' => apply_filters( 'rating-report/settings/sections/categories', array(
			'main' => __( 'Categories', 'rating-report' )
		) ),
		'display'    => apply_filters( 'rating-report/settings/sections/display', array(
			'main'        => __( 'Settings', 'rating-report' ),
			'text'        => __( 'Table Text', 'rating-report' ),
			'graph-style' => __( 'Graph Style', 'rating-report' )
		) ),
		'ratings'    => apply_filters( 'rating-report/settings/sections/ratings', array(
			'main'     => __( 'Settings', 'rating-report' ),
			'graphics' => __( 'Graphics', 'rating-report' )
		) ),
		'misc'       => apply_filters( 'rating-report/settings/sections/misc', array(
			'main' => __( 'Misc', 'rating-report' ),
		) )
	);

	$sections = apply_filters( 'rating-report/settings/sections', $sections );

	return $sections;
}

/**
 * Sanitizes a string key for Rating Report Settings
 *
 * Keys are used as internal identifiers. Alphanumeric characters, dashes, underscores, stops, colons and slashes are
 * allowed
 *
 * @param  string $key String key
 *
 * @since 2.0
 * @return string Sanitized key
 */
function rating_report_sanitize_key( $key ) {
	$raw_key = $key;
	$key     = preg_replace( '/[^a-zA-Z0-9_\-\.\:\/]/', '', $key );

	return apply_filters( 'rating-report/sanitize-key', $key, $raw_key );
}

/*
 * Callbacks
 */

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @param array $args Arguments passed by the setting
 *
 * @since 2.0
 * @return void
 */
function rating_report_missing_callback( $args ) {
	printf(
		__( 'The callback function used for the %s setting is missing.', 'rating-report' ),
		'<strong>' . $args['id'] . '</strong>'
	);
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @param array  $args                  Arguments passed by the setting
 *
 * @global array $rating_report_options Array of all the Rating Report settings
 *
 * @since 2.0
 * @return void
 */
function rating_report_text_callback( $args ) {
	global $rating_report_options;

	if ( isset( $rating_report_options[ $args['id'] ] ) ) {
		$value = $rating_report_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="rating_report_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$readonly = ( array_key_exists( 'readonly', $args ) && $args['readonly'] === true ) ? ' readonly="readonly"' : '';
	$size     = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	?>
	<input type="text" class="<?php echo sanitize_html_class( $size ); ?>-text" id="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" <?php echo $name; ?> value="<?php echo esc_attr( stripslashes( $value ) ); ?>"<?php echo $readonly; ?>>
	<label for="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @param array  $args                  Arguments passed by the setting
 *
 * @global array $rating_report_options Array of all the Rating Report settings
 *
 * @since 2.0
 * @return void
 */
function rating_report_number_callback( $args ) {
	global $rating_report_options;

	if ( isset( $rating_report_options[ $args['id'] ] ) ) {
		$value = $rating_report_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	if ( isset( $args['faux'] ) && true === $args['faux'] ) {
		$args['readonly'] = true;
		$value            = isset( $args['std'] ) ? $args['std'] : '';
		$name             = '';
	} else {
		$name = 'name="rating_report_settings[' . esc_attr( $args['id'] ) . ']"';
	}

	$readonly = ( array_key_exists( 'readonly', $args ) && $args['readonly'] === true ) ? ' readonly="readonly"' : '';
	?>
	<input type="number" class="small-text" id="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" <?php echo $name; ?> value="<?php echo esc_attr( stripslashes( $value ) ); ?>"<?php echo $readonly; ?>>
	<label for="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @param array  $args                  Arguments passed by the setting
 *
 * @global array $rating_report_options Array of all the Rating Report settings
 *
 * @since 2.0
 * @return void
 */
function rating_report_textarea_callback( $args ) {
	global $rating_report_options;

	if ( isset( $rating_report_options[ $args['id'] ] ) ) {
		$value = $rating_report_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}
	?>
	<textarea class="large-text" id="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" name="rating_report_settings[<?php echo esc_attr( $args['id'] ); ?>" rows="10" cols="50"><?php echo esc_textarea( $value ); ?></textarea>
	<label for="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Checkbox Callback
 *
 * Renders checkbox fields.
 *
 * @param array  $args                  Arguments passed by the setting
 *
 * @global array $rating_report_options Array of all the Rating Report settings
 *
 * @since 2.0
 * @return void
 */
function rating_report_checkbox_callback( $args ) {
	global $rating_report_options;

	$checked = ( isset( $rating_report_options[ $args['id'] ] ) && ! empty( $rating_report_options[ $args['id'] ] ) ) ? checked( 1, $rating_report_options[ $args['id'] ], false ) : '';
	?>
	<input type="checkbox" id="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" name="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" value="1" <?php echo $checked; ?>>
	<label for="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Callback: Purchase Links
 *
 * @param array  $args
 *
 * @global array $rating_report_options
 *
 * @since 2.0
 * @return void
 */
function rating_report_repeat_text_callback( $args ) {
	global $rating_report_options;

	if ( isset( $rating_report_options[ $args['id'] ] ) ) {
		$value = $rating_report_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : array();
	}

	if ( ! is_array( $value ) ) {
		return;
	}

	$label = ( array_key_exists( 'options', $args ) && is_array( $args['options'] ) && array_key_exists( 'label', $args['options'] ) ) ? $args['options']['label'] : esc_html__( 'Add Field', 'rating-report' );

	$i = 1;
	$j = 0;

	?>
	<table class="rating-report-repeat-text wp-list-table widefat fixed posts">
		<thead>
		<tr>
			<th class="rating-report-category-name"><?php _e( 'Category Name', 'rating-report' ); ?></th>
			<th class="rating-report-link-remove"><?php _e( 'Remove', 'rating-report' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $value as $category_name ) :
			?>
			<tr class="rating-report-cloned">
				<td>
					<label for="rating_report_settings[<?php echo esc_attr( $args['id'] ); ?>]_name_<?php echo $i; ?>" class="screen-reader-text"><?php _e( 'Enter the name of the category', 'rating-report' ); ?></label>
					<input type="text" class="regular-text" id="rating_report_settings[<?php echo esc_attr( $args['id'] ); ?>]_name_<?php echo $i; ?>" name="rating_report_settings[<?php echo esc_attr( $args['id'] ); ?>][<?php echo $j; ?>]" value="<?php esc_attr_e( stripslashes( $category_name ) ); ?>">
				</td>
				<td>
					<button class="button-secondary rating-report-remove-link" onclick="<?php echo ( $i > 1 ) ? 'jQuery(this).parent().parent().remove(); return false' : 'return false'; ?>"><?php _e( 'Remove', 'rating-report' ); ?></button>
				</td>
			</tr>
			<?php
			$i ++;
			$j ++;
			?>
		<?php endforeach; ?>
		</tbody>
	</table>

	<div class="rating-report-clone-buttons">
		<button class="button button-secondary rating-report-add-link" rel=".rating-report-cloned"><?php echo $label; ?></button>
	</div>
	<?php
}

/**
 * Callback: Color
 *
 * @param array  $args
 *
 * @global array $rating_report_options
 *
 * @since 2.0
 * @return void
 */
function rating_report_color_callback( $args ) {
	global $rating_report_options;

	if ( isset( $rating_report_options[ $args['id'] ] ) ) {
		$value = $rating_report_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	$default = isset( $args['std'] ) ? $args['std'] : '';
	?>
	<input type="text" class="rating-report-color-picker" id="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" name="rating_report_settings[<?php echo esc_attr( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default ); ?>">
	<label for="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Callback: Select
 *
 * @param array  $args
 *
 * @global array $rating_report_options
 *
 * @since 2.0
 * @return void
 */
function rating_report_select_callback( $args ) {
	global $rating_report_options;

	if ( isset( $rating_report_options[ $args['id'] ] ) ) {
		$value = $rating_report_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}
	?>
	<select name="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" id="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]">
		<?php foreach ( $args['options'] as $key => $option ) : ?>
			<option value="<?php echo rating_report_sanitize_key( $key ); ?>"<?php selected( $key, $value ); ?>><?php echo esc_html( $option ); ?></option>
		<?php endforeach; ?>
	</select>
	<label for="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php
}

/**
 * Callback: Header
 *
 * @param array  $args
 *
 * @global array $rating_report_options
 *
 * @since 2.0
 * @return void
 */
function rating_report_header_callback( $args ) {
	if ( array_key_exists( 'desc', $args ) && ! empty( $args['desc'] ) ) {
		echo '<div class="desc">' . wp_kses_post( $args['desc'] ) . '</div>';
	}
}

/**
 * Callback: Image
 *
 * @param array  $args
 *
 * @global array $rating_report_options
 *
 * @since 2.0
 * @return void
 */
function rating_report_image_callback( $args ) {
	global $rating_report_options;

	if ( isset( $rating_report_options[ $args['id'] ] ) ) {
		$value = $rating_report_options[ $args['id'] ];
	} else {
		$value = isset( $args['std'] ) ? $args['std'] : '';
	}

	// Display the image if it exists.
	if ( ! empty( $value ) ) {
		$attr = array(
			'id'    => 'rating_report_settings_' . rating_report_sanitize_key( $args['id'] ) . '_image',
			'class' => 'rating-report-image-upload-image'
		);
		echo wp_get_attachment_image( intval( $value ), 'medium', false, $attr );
	} else {
		// Empty image.
		echo '<img src="" id="rating_report_settings_' . rating_report_sanitize_key( $args['id'] ) . '_image" style="display:none;">';
	}

	// Display the image and buttons.
	?>
	<div class="rating-report-image-upload-buttons-wrap" data-image="rating_report_settings_<?php echo rating_report_sanitize_key( $args['id'] ); ?>_image" data-value="rating_report_settings_<?php echo rating_report_sanitize_key( $args['id'] ); ?>">
		<input type="button" value="<?php esc_attr_e( 'Upload Image', 'rating-report' ); ?>" class="button-secondary rating-report-upload-image-button">
		<input type="button" value="<?php esc_attr_e( 'Remove Image', 'rating-report' ); ?>" class="button-secondary rating-report-remove-image-button" style="<?php echo empty( $value ) ? 'display: none;' : ''; ?>">
		<input type="hidden" id="rating_report_settings_<?php echo rating_report_sanitize_key( $args['id'] ); ?>" name="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>">
	</div>
	<?php if ( array_key_exists( 'desc', $args ) && ! empty( $args['desc'] ) ) : ?>
		<label for="rating_report_settings[<?php echo rating_report_sanitize_key( $args['id'] ); ?>]" class="desc"><?php echo wp_kses_post( $args['desc'] ); ?></label>
	<?php endif;
}