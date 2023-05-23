<?php
/**
 * Common Functions
 *
 * @package Safe Media Delete
 * @since 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @param string|array $var contains values.
 * @since 1.0.0
 */
function smd_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'smd_clean', $var );
	} else {
		$data = is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		return wp_unslash( $data );
	}
}

/**
 * Function to add array after specific key
 *
 * @param array   $array array values.
 * @param array   $value array values.
 * @param string  $index array index.
 * @param boolean $from_last contain last value flag.
 * @since 1.0.0
 */
function smd_add_array( &$array, $value, $index, $from_last = false ) {

	if ( is_array( $array ) && is_array( $value ) ) {

		if ( $from_last ) {
			$total_count = count( $array );
			$index       = ( ! empty( $total_count ) && ( $total_count > $index ) ) ? ( $total_count - $index ) : $index;
		}

		$split_arr = array_splice( $array, max( 0, $index ) );
		$array     = array_merge( $array, $value, $split_arr );
	}

	return $array;
}

/**
 * Get Taxonomy Image
 *
 * @param int    $term_id term Id.
 * @param string $size image size to get.
 * @since 1.0.0
 */
function smd_term_image( $term_id = 0, $size = 'full' ) {

	$prefix        = SMD_META_PREFIX;
	$size          = ! empty( $size ) ? $size : 'full';
	$attachment_id = get_term_meta( $term_id, $prefix . 'cat_thumb_id', true );

	// Backword compatibility.
	if ( empty( $attachment_id ) ) {
		$attachment_id = get_option( 'smd_categoryimage_' . $term_id );
	}

	$image = smd_get_image_src( $attachment_id, $size );

	return $image;
}

/**
 * Function to get post featured image
 *
 * @param int    $attachment_id attachment Id.
 * @param string $size image size to get.
 * @since 1.0.0
 */
function smd_get_image_src( $attachment_id = '', $size = 'full' ) {

	$size  = ! empty( $size ) ? $size : 'full';
	$image = wp_get_attachment_image_src( $attachment_id, $size );

	if ( ! empty( $image ) ) {
		$image = isset( $image[0] ) ? $image[0] : '';
	}

	return $image;
}

/**
 * Get Attached media message
 *
 * @param int     $attach_id attachment Id.
 * @param boolean $api api flag.
 * @since 1.0.0
 */
function smd_attached_media( $attach_id, $api = false ) {
	global $wpdb;

	// Check if the post being deleted is a media file.
	if ( 'attachment' === get_post_type( $attach_id ) && wp_attachment_is_image( $attach_id ) ) {

		// get the media file is being used as a featured image in a post.
		$posts_with_featured_image = get_post_meta( $attach_id, '_smd_media_parents', true );

		if ( $posts_with_featured_image ) {
			$attach_found = true;
			foreach ( $posts_with_featured_image as $postids ) {
				$post_fids[] = '<a href="' . get_edit_post_link( $postids ) . '">' . $postids . '</a>';
			}

			$postfeatured = implode( ', ', $post_fids );
			$message[]    = "As featured image in post: $postfeatured";
		}

		// Check if the media file is used in the post content.

		$posts_with_image_in_content = $wpdb->get_results(
			$wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status IN ('publish','draft','pending','private','future') AND (post_content LIKE %s)", "%wp-image-$attach_id%" ),
			ARRAY_A
		);
		if ( $posts_with_image_in_content ) {
			$attach_found = true;
			foreach ( $posts_with_image_in_content as $postids ) {
				$post_ids[] = '<a href="' . get_edit_post_link( $postids['ID'] ) . '">' . $postids['ID'] . '</a>';
			}

			$post_contents = implode( ', ', $post_ids );
			$message[]     = "In post content: $post_contents";
		}

		$category_image_attached = $wpdb->get_results(
			$wpdb->prepare( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key = '_smd_cat_thumb_id' and meta_value = %s", "$attach_id" ),
			ARRAY_A
		);

		if ( $category_image_attached ) {
			$attach_found = true;
			foreach ( $category_image_attached as $catids ) {
				$taxonomy  = get_term( $catids['term_id'] )->taxonomy;
				$tax_link  = get_edit_term_link( $catids['term_id'], $taxonomy );
				$cat_ids[] = '<a href="' . $tax_link . '">' . $catids['term_id'] . '</a>';
			}

			$category_attached = implode( ', ', $cat_ids );
			$message[]         = "In category image field: $category_attached";
		}
	}
	$attached_media['attach_found'] = isset( $attach_found ) ? $attach_found : false;
	$attached_media['message']      = isset( $message ) ? $message : array();
	return $attached_media;
}
