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
