<?php
/**
 * Register and display post meta boxes
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
 * Register all the meta boxes for the post custom post type
 *
 * @since 1.0
 * @return void
 */
function rating_report_add_meta_box() {
	$post_types = apply_filters( 'rating-report/meta-box-post-types', array( 'post' ) );

	foreach ( $post_types as $post_type ) {
		add_meta_box( 'rating_report', esc_html__( 'Rating Report', 'rating-report' ), 'rating_report_render_meta_box', $post_type, 'normal', 'high' );
	}
}

add_action( 'add_meta_boxes', 'rating_report_add_meta_box' );

/**
 * Render Rating Report Meta Box
 *
 * @param WP_Post $post
 *
 * @see   rating_report_render_rating_fields()
 *
 * @since 1.0
 * @return void
 */
function rating_report_render_meta_box( $post ) {
	do_action( 'rating-report/meta-box/render', $post );

	wp_nonce_field( 'save_rating_report_meta', 'rating_report_meta_box_nonce' );
}

/**
 * Render Rating Dropdown Fields
 *
 * @param WP_Post $post
 *
 * @since 1.0
 * @return void
 */
function rating_report_render_rating_fields( $post ) {
	$categories    = rating_report_get_option( 'categories', rating_report_get_default_categories() );
	$rating_scale  = rating_report_get_rating_scale();
	$saved_ratings = get_post_meta( $post->ID, 'rating_report', true );
	$saved_ratings = is_array( $saved_ratings ) ? $saved_ratings : array();
	$descriptions  = get_post_meta( $post->ID, 'rating_report_descriptions', true );
	$descriptions  = is_array( $descriptions ) ? $descriptions : array();

	foreach ( $categories as $key => $name ) {
		$number      = ( $key + 1 );
		$field_id    = 'rating_report_category_' . $number;
		$saved_value = array_key_exists( $key, $saved_ratings ) ? $saved_ratings[ $key ] : '';
		?>
		<div class="rating-report-category">
			<div class="rating-report-field-row">
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $name ); ?></label>
				<div class="rating-report-field">
					<select id="<?php echo esc_attr( $field_id ); ?>" name="rating_report[]">
						<option value="" <?php selected( $saved_value, '' ); ?>><?php esc_html_e( '- Select -', 'rating-report' ); ?></option>
						<?php foreach ( $rating_scale as $rating_value => $rating_name ) : ?>
							<option value="<?php echo esc_attr( $rating_value ); ?>" <?php selected( $saved_value, $rating_value ); ?>><?php echo esc_html( $rating_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<?php
			$field_id    = 'rating_report_description_' . $number;
			$saved_value = array_key_exists( $key, $descriptions ) ? $descriptions[ $key ] : '';
			?>
			<div class="rating-report-field-row">
				<label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( sprintf( __( '%s Comments', 'rating-report' ), $name ) ); ?></label>
				<div class="rating-report-field">
					<textarea id="<?php echo esc_attr( $field_id ); ?>" name="rating_report_descriptions[]"><?php echo esc_textarea( $saved_value ); ?></textarea>
				</div>
			</div>
		</div>
		<?php
	}
}

add_action( 'rating-report/meta-box/render', 'rating_report_render_rating_fields' );

/**
 * Save Meta Fields
 *
 * @param int     $post_id ID of the book being saved
 * @param WP_Post $post    Object of the book being saved
 *
 * @since 1.0
 * @return void
 */
function rating_report_save_meta( $post_id, $post ) {

	/*
	 * Permission Check
	 */

	if ( ! isset( $_POST['rating_report_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['rating_report_meta_box_nonce'], 'save_rating_report_meta' ) ) {
		return;
	}

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	/*
	 * Okay now we can save.
	 */

	$fields     = array();
	$categories = rating_report_get_option( 'categories', rating_report_get_default_categories() );

	foreach ( $categories as $key => $name ) {
		$number   = ( $key + 1 );
		$fields[] = '_ratingr_section_' . $number;
	}

	$fields = array( 'rating_report' );

	foreach ( apply_filters( 'rating-report/meta-box/saved-fields', $fields ) as $field ) {
		if ( ! empty( $_POST[ $field ] ) ) {
			$sanitized_value = apply_filters( 'rating-report/meta-box/sanitize', $_POST[ $field ], $field ); // @see rating_report_sanitize_meta()
			update_post_meta( $post_id, $field, $sanitized_value );
		} else {
			delete_post_meta( $post_id, $field );
		}
	}

	do_action( 'novelist/meta-box/save', $post_id, $post );

}

add_action( 'save_post', 'rating_report_save_meta', 10, 2 );