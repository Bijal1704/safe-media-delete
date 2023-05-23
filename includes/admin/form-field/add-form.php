<?php
/**
 * Add form field
 *
 * @package Safe Media Delete
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$prefix = SMD_META_PREFIX;
?>

<div class="form-field smd-term-img-wrap">
	<label for="smd-url-btn"><?php esc_html_e( 'Image', 'safe-media-delete' ); ?></label>
	<input type="button" name="smd_url_btn" id="smd-url-btn" class="button button-secondary smd-url-btn smd-image-upload" value="<?php esc_attr_e( 'Upload Image', 'safe-media-delete' ); ?>" />
	<input type="button" name="smd_url_clear_btn" id="smd-url-clear-btn" class="button button-secondary smd-url-clear-btn smd-image-clear" value="<?php esc_attr_e( 'Clear', 'safe-media-delete' ); ?>" /> <br/>

	<input type="hidden" name="<?php echo esc_attr( $prefix ); ?>cat_thumb_id" value="" class="smd-cat-thumb-id smd-thumb-id" />
	<p class="description"><?php esc_html_e( 'Upload or Choose category image.', 'safe-media-delete' ); ?></p>
	<div class="smd-img-preview smd-img-view"></div>
</div>
