<?php

if ( ! function_exists( 'rating_report_farbtastic_script' ) ) {
	add_action( 'init', 'rating_report_farbtastic_script' );
	function rating_report_farbtastic_script() {
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'farbtastic' );
	}
}

//Scripts for image uploader
function rating_report_admin_scripts() {
	wp_enqueue_script( 'media-upload' );
	wp_enqueue_script( 'thickbox' );
	wp_register_script( 'cwb-my-upload', get_template_directory_uri() . '/library/js/media-uploader.js', array(
		'jquery',
		'media-upload',
		'thickbox'
	) );
	wp_enqueue_script( 'cwb-my-upload' );
}

function rating_report_admin_styles() {
	wp_enqueue_style( 'thickbox' );
	wp_register_style( 'rr-styles', plugins_url( '/rating-report.css', __FILE__ ) );
	wp_enqueue_style( 'rr-styles' );
}

if ( isset( $_GET['page'] ) && ( ( $_GET['page'] == 'rating-report' ) || ( $_GET['page'] == 'rr-section-config' ) ) ) {
	add_action( 'admin_print_scripts', 'rating_report_admin_scripts' );
	add_action( 'admin_print_styles', 'rating_report_admin_styles' );
}


/**
 * This function introduces the plugin options into the 'Settings' menu
 */
function rating_report_menu() {

	add_submenu_page(
		'options-general.php',
		'Rating Report Settings',
		'Rating Report Settings',
		'administrator',
		'rating-report',
		'rating_report_display'
	);


} // end rating_report_menu
add_action( 'admin_menu', 'rating_report_menu' );

/**
 * Renders a simple page to display for the theme menu defined above.
 */
function rating_report_display( $active_tab = '' ) {
	?>
	<!-- Create a header in the default WordPress 'wrap' container -->
	<div class="wrap">

		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e( 'Rating Report Settings', 'sandbox' ); ?></h2>
		<?php settings_errors(); ?>

		<?php if ( isset( $_GET['tab'] ) ) {
			$active_tab = $_GET['tab'];
		} else {
			$active_tab = 'section_config';
		} // end if/else ?>

		<form method="post" action="options.php">
			<?php

			if ( $active_tab == 'section_config' ) {

				settings_fields( 'rr-section-config' );
				do_settings_sections( 'rr-section-config' );

			} elseif ( $active_tab == 'star_graphics' ) {

				settings_fields( 'rr-star-graphics' );
				do_settings_sections( 'rr-star-graphics' );

			} else {

				settings_fields( 'rr-report-display' );
				do_settings_sections( 'rr-report-display' );

			} // end if/else

			submit_button();

			?>
		</form>

	</div><!-- /.wrap -->
	<?php
} // end rating_report_display

/* ------------------------------------------------------------------------ *
 * Setting Registration
 * ------------------------------------------------------------------------ */

/**
 * Provides default values for the Review Index Options.
 */
function rr_default_section_config() {

	$defaults = array(
		'section_1' => 'Plot',
		'section_2' => 'Characters',
		'section_3' => 'Writing',
		'section_4' => 'Pacing',
		'section_5' => 'Cover'
	);

	return apply_filters( 'rr_default_section_config', $defaults );

} // end rr_default_section_config

/**
 * Provides default values for the Book Data Options.
 */
function rr_default_star_graphics() {

	$defaults = array();

	return apply_filters( 'rr_default_star_graphics', $defaults );

} // end rr_default_star_graphics

/**
 * Initializes the theme's display options page by registering the Sections,
 * Fields, and Settings.
 *
 * This function is registered with the 'admin_init' hook.
 */
function rr_initialize_section_config_options() {

	// If the theme options don't exist, create them.
	if ( false == get_option( 'rr-section-config' ) ) {
		add_option( 'rr-section-config', apply_filters( 'rr_default_section_config', rr_default_section_config() ) );
	} // end if

	// First, we register a section. This is necessary since all future options must belong to a 
	add_settings_section(
		'section_config_section',            // ID used to identify this section and with which to register options
		__( 'Configure Your Rating Sections', 'sandbox' ),        // Title to be displayed on the administration page
		'section_config_options_callback',    // Callback used to render the description of the section
		'rr-section-config'        // Page on which to add this section of options
	);

	add_settings_field(
		'section_1',
		__( 'Section 1', 'sandbox' ),
		'rr_section_1_callback',
		'rr-section-config',
		'section_config_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_field(
		'section_2',
		__( 'Section 2', 'sandbox' ),
		'rr_section_2_callback',
		'rr-section-config',
		'section_config_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_field(
		'section_3',
		__( 'Section 3', 'sandbox' ),
		'rr_section_3_callback',
		'rr-section-config',
		'section_config_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_field(
		'section_4',
		__( 'Section 4', 'sandbox' ),
		'rr_section_4_callback',
		'rr-section-config',
		'section_config_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_field(
		'section_5',
		__( 'Section 5', 'sandbox' ),
		'rr_section_5_callback',
		'rr-section-config',
		'section_config_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_section(
		'star_graphics_settings_section',            // ID used to identify this section and with which to register options
		__( 'Select Your Star Graphic', 'sandbox' ),        // Title to be displayed on the administration page
		'rr_star_graphics_options_callback',    // Callback used to render the description of the section
		'rr-section-config'        // Page on which to add this section of options
	);

	add_settings_field(
		'stars',
		__( 'Select a Star Graphic', 'sandbox' ),
		'rr_star_graphic_callback',
		'rr-section-config',
		'star_graphics_settings_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_field(
		'custom_upload_filled',
		__( 'Upload 1 filled in star', 'sandbox' ),
		'rr_custom_upload_filled_callback',
		'rr-section-config',
		'star_graphics_settings_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_field(
		'custom_upload_grey',
		__( 'Upload 1 grey star<br /><small>(optional)</small>', 'sandbox' ),
		'rr_custom_upload_grey_callback',
		'rr-section-config',
		'star_graphics_settings_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_field(
		'custom_upload_display',
		__( 'Your Custom Star', 'sandbox' ),
		'rr_custom_upload_display_callback',
		'rr-section-config',
		'star_graphics_settings_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	add_settings_section(
		'report_display_settings_section',            // ID used to identify this section and with which to register options
		__( 'Customize the Report Display', 'sandbox' ),        // Title to be displayed on the administration page
		'rr_report_display_options_callback',    // Callback used to render the description of the section
		'rr-section-config'        // Page on which to add this section of options
	);

	add_settings_field(
		'report_display',
		__( 'Automatically display at the bottom of the post?', 'sandbox' ),
		'rr_report_display_callback',
		'rr-section-config',
		'report_display_settings_section',
		array(
			__( '', 'sandbox' ),
		)
	);

	// Finally, we register the fields with WordPress
	register_setting(
		'rr-section-config',
		'rr-section-config'
	);

} // end rr_initialize_section_config_options
add_action( 'admin_init', 'rr_initialize_section_config_options' );


/* ------------------------------------------------------------------------ *
 * Section Callbacks
 * ------------------------------------------------------------------------ */

/**
 * This function provides a simple description for the REVIEW INDEX page.
 *
 * It's called from the 'rr_initialize_section_config_options' function by being passed as a parameter
 * in the add_settings_section function.
 */
function section_config_options_callback() {
	echo '<p>' . __( 'Customize the sections for your rating report breakdown.  You may use as many or as few sections as you like.  If you do not want to use five sections, just leave any extras blank.', 'sandbox' ) . '</p>';
} // end section_config_options_callback

//STAR GRAPHICS
function rr_star_graphics_options_callback() {
	echo '<p>' . __( 'Select the star graphic you want to display on your rating report.', 'sandbox' ) . '</p>';
} // end rr_star_graphics_options_callback

//REPORT DISPLAY
function rr_report_display_options_callback() {
	echo '<p>' . __( 'Select the display options.  If you choose not to automatically add it to the end of your post, you can insert it anywhere with the shortcode <code>[rating-report]</code>', 'sandbox' ) . '</p>';
} // end rr_star_graphics_options_callback


/* ------------------------------------------------------------------------ *
 * Field Callbacks
 * ------------------------------------------------------------------------ */

/**
 * This function renders the interface elements for toggling the visibility of the header element.
 *
 * It accepts an array or arguments and expects the first element in the array to be the description
 * to be displayed next to the checkbox.
 */

function rr_section_1_callback() {
	$options = get_option( 'rr-section-config' );
	$config  = 'Plot';
	if ( isset( $options['section_1'] ) ) {
		$config = $options['section_1'];
	}
	echo '<input type="text" name="rr-section-config[section_1]" value="' . $config . '" size="50" />';
}

function rr_section_2_callback() {
	$options = get_option( 'rr-section-config' );
	$config  = 'Characters';
	if ( isset( $options['section_2'] ) ) {
		$config = $options['section_2'];
	}
	echo '<input type="text" name="rr-section-config[section_2]" value="' . $config . '" size="50" />';
}

function rr_section_3_callback() {
	$options = get_option( 'rr-section-config' );
	$config  = 'Writing';
	if ( isset( $options['section_3'] ) ) {
		$config = $options['section_3'];
	}
	echo '<input type="text" name="rr-section-config[section_3]" value="' . $config . '" size="50" />';
}

function rr_section_4_callback() {
	$options = get_option( 'rr-section-config' );
	$config  = 'Pacing';
	if ( isset( $options['section_4'] ) ) {
		$config = $options['section_4'];
	}
	echo '<input type="text" name="rr-section-config[section_4]" value="' . $config . '" size="50" />';
}

function rr_section_5_callback() {
	$options = get_option( 'rr-section-config' );
	$config  = 'Cover';
	if ( isset( $options['section_5'] ) ) {
		$config = $options['section_5'];
	}
	echo '<input type="text" name="rr-section-config[section_5]" value="' . $config . '" size="50" />';
}

function rr_star_graphic_callback() {
	$options = get_option( 'rr-section-config' );

	if ( ! array_key_exists( 'star_graphic', $options ) ) {
		$options['star_graphic'] = null;
	}

	//Small yellow star
	$html = '<input type="radio" id="small_yellow" name="rr-section-config[star_graphic]" value="1"' . checked( 1, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="small_yellow"><div class="rr-rating"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Smile yellow star
	$html .= '<input type="radio" id="smile_yellow" name="rr-section-config[star_graphic]" value="2"' . checked( 2, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="smile_yellow"><div class="rr-rating rr-smile-yellow"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Circle Blue
	$html .= '<input type="radio" id="circle_blue" name="rr-section-config[star_graphic]" value="3"' . checked( 3, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="circle_blue"><div class="rr-rating rr-circle-blue"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Circle Pink
	$html .= '<input type="radio" id="circle_pink" name="rr-section-config[star_graphic]" value="4"' . checked( 4, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="circle_pink"><div class="rr-rating rr-circle-pink"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Circle Red
	$html .= '<input type="radio" id="circle_red" name="rr-section-config[star_graphic]" value="5"' . checked( 5, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="circle_red"><div class="rr-rating rr-circle-red"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Circle Green
	$html .= '<input type="radio" id="circle_green" name="rr-section-config[star_graphic]" value="6"' . checked( 6, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="circle_green"><div class="rr-rating rr-circle-green"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Circle Yellow
	$html .= '<input type="radio" id="circle_yellow" name="rr-section-config[star_graphic]" value="7"' . checked( 7, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="circle_yellow"><div class="rr-rating rr-circle-yellow"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Circle Black
	$html .= '<input type="radio" id="circle_black" name="rr-section-config[star_graphic]" value="8"' . checked( 8, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="circle_black"><div class="rr-rating rr-circle-black"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Green
	$html .= '<input type="radio" id="green" name="rr-section-config[star_graphic]" value="9"' . checked( 9, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="green"><div class="rr-rating rr-green"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Orange
	$html .= '<input type="radio" id="orange" name="rr-section-config[star_graphic]" value="10"' . checked( 10, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="orange"><div class="rr-rating rr-orange"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Purple
	$html .= '<input type="radio" id="purple" name="rr-section-config[star_graphic]" value="11"' . checked( 11, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="purple"><div class="rr-rating rr-purple"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Red
	$html .= '<input type="radio" id="red" name="rr-section-config[star_graphic]" value="12"' . checked( 12, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="red"><div class="rr-rating rr-red"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Black
	$html .= '<input type="radio" id="black" name="rr-section-config[star_graphic]" value="13"' . checked( 13, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="black"><div class="rr-rating rr-black"><div class="rr-stars" style="width:70%;"></div></div></label>';
	$html .= '<br />';

	//Upload Your Own
	$html .= '<input type="radio" id="custom" name="rr-section-config[star_graphic]" value="14"' . checked( 14, $options['star_graphic'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="custom">Upload your own! (below)</label>';
	$html .= '<br />';

	echo $html;
}

function rr_custom_upload_filled_callback() {
	$options = get_option( 'rr-section-config' );

	$options['custom_upload_filled'] = array_key_exists( 'custom_upload_filled', $options ) ? $options['custom_upload_filled'] : null;

	echo '<input type="text" id="custom_upload_filled" name="rr-section-config[custom_upload_filled]" class="rating_upload" value="' . $options['custom_upload_filled'] . '" /> <input id="custom_upload_filled_upload_button" type="button" class="button rating_upload_button" value="Upload Image" />';
	if ( $options['custom_upload_filled'] != '' ) {
		echo '<br /><img src="' . $options['custom_upload_filled'] . '" alt="Filled In Star" />';
	}
}

function rr_custom_upload_grey_callback() {
	$options = get_option( 'rr-section-config' );

	$options['custom_upload_grey'] = array_key_exists( 'custom_upload_grey', $options ) ? $options['custom_upload_grey'] : null;

	echo '<input type="text" id="custom_upload_grey" name="rr-section-config[custom_upload_grey]" class="rating_upload" value="' . $options['custom_upload_grey'] . '" /> <input id="custom_upload_grey_upload_button" type="button" class="button rating_upload_button" value="Upload Image" />';
	if ( $options['custom_upload_grey'] != '' ) {
		echo '<br /><img src="' . $options['custom_upload_grey'] . '" alt="Filled In Star" />';
	}
}

function rr_custom_upload_display_callback() {
	$options = get_option( 'rr-section-config' );
	$filled  = array_key_exists( 'custom_upload_filled', $options ) ? $options['custom_upload_filled'] : null;
	$grey    = array_key_exists( 'custom_upload_grey', $options ) ? $options['custom_upload_grey'] : null;
	if ( isset( $filled ) && $filled != '' ) {
		$curl_geturl = curl_init();
		curl_setopt( $curl_geturl, CURLOPT_URL, $filled );
		curl_setopt( $curl_geturl, CURLOPT_BINARYTRANSFER, 1 );
		curl_setopt( $curl_geturl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl_geturl, CURLOPT_CONNECTTIMEOUT, 3 );
		curl_setopt( $curl_geturl, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $curl_geturl, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $curl_geturl, CURLOPT_TIMEOUT, 6 );
		ob_start();
		$curl_return_url = curl_exec( $curl_geturl );
		ob_end_clean();
		$curl_http_return = curl_getinfo( $curl_geturl, CURLINFO_HTTP_CODE );
		curl_close( $curl_geturl );
		if ( $curl_http_return == 200 && strlen( $curl_return_url ) > 0 ) {
			$img_resource  = imagecreatefromstring( $curl_return_url );
			$img['width']  = imagesx( $img_resource );
			$img['height'] = imagesy( $img_resource );
		} else {
			// Failed to retrieve the image, invalid URL maybe?
			$img['width'] = $img['height'] = 0;
		}

		echo '<div class="rr-rating rr-custom" style="height:' . $img[ height ] . 'px; width:' . $img[ width ] * 5 . 'px; background:url(\'' . $grey . '\') repeat-x;"><div class="rr-stars" style="width:70%; height:' . $img[ height ] . 'px; background:url(\'' . $filled . '\') repeat-x 0 100%;"></div></div>';
	}
}

function rr_report_display_callback() {
	$options = get_option( 'rr-section-config' );
	$options['report_display'] = array_key_exists( 'report_display', $options ) ? $options['report_display'] : null;

	$html = '<input type="checkbox" id="report_display" name="rr-section-config[report_display]" value="1"' . checked( 1, $options['report_display'], false ) . '/>';
	$html .= '&nbsp;';
	$html .= '<label for="report_display">Yes</label> <br />';

	echo $html;
}