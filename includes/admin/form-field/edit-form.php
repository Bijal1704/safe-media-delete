<?php
/**
 * Edit form field
 *
 * @package Safe Media Delete
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Getting stored values.
$prefix         = SMD_META_PREFIX;
$cat_thumb_id   = get_term_meta( $term->term_id, $prefix . 'cat_thumb_id', true );
$cat_thum_image = smd_term_image( $term->term_id, 'thumbnail' ); ?>

<tr class="form-field">
	<th scope="row" valign="top"><label for="smd-cat-image"><?php esc_html_e( 'Image', 'safe-media-delete' ); ?></label></th>
	<td>
		<input type="button" name="smd_url_btn" id="smd_url_btn" class="button button-secondary smd-url-btn smd-image-upload" value="<?php esc_attr_e( 'Upload Image', 'safe-media-delete' ); ?>" />
		<input type="button" name="smd_url_clear_btn" id="smd_url_clear_btn" class="button button-secondary smd-url-clear-btn smd-image-clear" value="<?php esc_attr_e( 'Clear', 'safe-media-delete' ); ?>" /> <br/>

		<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>cat_thumb_id" value="<?php echo esc_attr( $cat_thumb_id ); ?>" class="smd-cat-thumb-id smd-thumb-id" />
		<p class="description"><?php esc_html_e( 'Upload or Choose category image.', 'safe-media-delete' ); ?></p>

		<div class="smd-img-preview smd-img-view smd-img-view">
			<?php if ( ! empty( $cat_thum_image ) ) { ?>
				<img src="<?php echo esc_url( $cat_thum_image ); ?>" alt="" />
			<?php } ?>
		</div>
	</td>
</tr>
