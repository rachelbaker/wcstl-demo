<?php
/**
 * Plugin Name: WordCamp St. Louis Secured Post Meta Demo
 * Description: Useless plugin that demonstrates securing input and output from post_meta.
 * Author: Rachel Baker <rachel@10up.com>
 * Author URI: http://10up.com/
 */

// Wireup actions
add_action( 'add_meta_boxes', 'wcstl_add_meta_boxes' );
add_action( 'save_post', 'wcstl_save_location_meta_boxes' );
add_filter( 'template_include', 'wcstl_display_location_template', 99 );

/**
 * Add Location Information post meta group to posts.
 *
 * @uses   add_action : add_meta_boxes
 *
 * @return add_meta_box() | void
 */
function wcstl_add_meta_boxes() {
	return add_meta_box(
		'wcstl_section_id',
		'WCSTL Location Information',
	    'wcstl_display_location_meta_boxes',
		'post'
	);
}

/**
 * Callback function to display each individual post meta field from WCSTL Location Information Group.
 *
 * @param  object 	$post
 *
 * @return $location_meta_fields
 */
function wcstl_display_location_meta_boxes( $post ) {
	 // Get all location meta values for the current post.
	$name_value			= get_post_meta( $post->ID, '_wcstl_name', true );
	$email_value		= get_post_meta( $post->ID, '_wcstl_email', true );
	$phone_value		= get_post_meta( $post->ID, '_wcstl_phone', true );
	$address_value		= get_post_meta( $post->ID, '_wcstl_address', true );
	$description_value	= get_post_meta( $post->ID, '_wcstl_description', true );
	$map_url_value		= get_post_meta( $post->ID, '_wcstl_map_url', true );

	// encode text to display in rich text editor.
	$address_value		= wp_richedit_pre( $address_value );
	$description_value	= wp_richedit_pre( $description_value );

	// display hidden nonce field for CSRF protection.
	wp_nonce_field( 'wcstl_location_save_meta','wcstl_meta_nonce' );

	// output Location Information meta form fields on "Edit Post" screen.
	$location_meta_fields = '<p><label for="_wcstl_name">Location Name (text string only)</label></p>
		<input type="text" size="80" id="_wcstl_name" name="_wcstl_name" value="' . esc_attr( $name_value ) . '" /><p>
		<label for="_wcstl_email">Email Address (valid email address only)</label></p>
		<input type="text" size="20" id="_wcstl_email" name="_wcstl_email" value="' . esc_attr( $email_value ) . '" />
		<p><label for="_wcstl_phone">Phone Number (10 digit phone number only)</label></p>
		<input type="text" size="10" id="_wcstl_phone" name="_wcstl_phone" value="' . esc_attr( $phone_value ) . '" />
		<p><label for="_wcstl_address">Address (Basic HTML Allowed)</label></p>
		<textarea style="width:90%; height:60px;" cols="40" id="_wcstl_address" name="_wcstl_address">' . esc_html( $address_value ) . '</textarea>
		<p><label for="_wcstl_description">Description (Advanced HTML allowed)</label></p>
		<textarea style="width:90%; height:160px;" cols="40" id="_wcstl_description" name="_wcstl_description">' . esc_html( $description_value ) . '</textarea>
		<p><label for="_wcstl_map_url">Map URL (url only)</label></p>
		<input size="80" id="_wcstl_map_url" name="_wcstl_map_url" value="' . esc_url( $map_url_value ) . '" />';

	echo $location_meta_fields;
}

/**
 * Capture and save input data from WCSTL Location Information meta fields.
 *
 * @param  integer 	$post_id
 * @uses   add_action : save_post
 *
 * @return $post_id | void
 */
function wcstl_save_location_meta_boxes( $post_id ) {
	// return early if this is an autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}
	// return early if user does not have permission to edit content.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	// return early if this is a new post without data to save.
	if ( empty( $_POST ) ) {
		return $post_id;
	}

	// return early if nonce doesn't match.
	if ( ! check_admin_referer( 'wcstl_location_save_meta','wcstl_meta_nonce' ) ) {
		return $post_id;
	}

	// sanitize the location name input to only allow a text string 
	// and strip HTML tags.
	$safe_name = sanitize_text_field( $_POST['_wcstl_name'] );
	update_post_meta( $post_id, '_wcstl_name', $safe_name );

	// validate then sanitize the email address input.
	if ( is_email( $_POST['_wcstl_email'] ) ) {
		$safe_email = sanitize_email( $_POST['_wcstl_email'] );
		update_post_meta( $post_id, '_wcstl_email', $safe_email );
	}

	// correct phone number input to remove any non-numerical characters.
	$format_phone_input = preg_replace( "/\D+/","", $_POST['_wcstl_phone'] );
	// filter any added numbers from the input.
	if ( strlen( $format_phone_input ) > 10 ) {
		$format_phone_input = substr( $format_phone_input, 0, 10 );
	}
	// sanitize phone number input.
	$safe_phone = sanitize_text_field( $format_phone_input );
	update_post_meta( $post_id, '_wcstl_phone', $safe_phone );

	// sanitize the address text input based on $allowed_tags.
	$safe_address = wp_filter_kses( $_POST['_wcstl_address'] );
	update_post_meta( $post_id, '_wcstl_address', $safe_address );

	// sanitize the description HTML input based on post content HTML filter.
	$safe_description = wp_filter_post_kses( $_POST['_wcstl_description'] );
	update_post_meta( $post_id, '_wcstl_description', $safe_description );

	// filter any html tags then santize the url input.
	$filter_map_url = wp_strip_all_tags( $_POST['_wcstl_map_url'] );
	$safe_map_url = esc_url_raw( $filter_map_url );
	update_post_meta( $post_id, '_wcstl_map_url', $safe_map_url );
}

/**
 * Modify the single post template to use plugin's demo-template file.
 *
 * @param  string 	$template
 * @uses   add_filter : template_include
 *
 * @return string 	$template
 */
function wcstl_display_location_template( $template ) {
	if ( ! is_single() ) {
		return $template;
	}
	$template = dirname( __FILE__ ) . '/templates/demo-template.php';

	return $template ;
}
