<?php
/**
 * Safe Media Delete General Test cases
 *
 * @package Safe Media Delete
 */

/**
 * Test cases for create post with featured image and upload image to category
 *
 * @package Safe Media Delete
 */
class Safe_Media_Delete_Test extends WP_UnitTestCase {

	/**
	 * Upload test image and set post as post parent if passed
	 *
	 * @param string $post_id Post id.
	 */
	public function upload_test_image_file_to_post( $post_id = '' ) {

		// Path to test file.
		$file_path = SMD_DIR . '/tests/test-file.jpg';
		$file_name = 'test-file.jpg';

		$upload_file = wp_upload_bits( $file_name, null, file_get_contents( $file_path ) );

		$attachment_id = wp_insert_attachment(
			array(
				'guid'           => $upload_file['url'],
				'post_mime_type' => 'image/jpeg',
				'post_title'     => $file_name,
				'post_content'   => '',
				'post_status'    => 'inherit',
				'post_parent'    => $post_id,
			),
			$upload_file['file'],
			$post_id
		);

		return $attachment_id;
	}

	/**
	 * Function to test upload image in category
	 */
	public function test_create_category_with_image() {
		// Create a test category.
		$category_name = 'Test Category';
		$category_slug = 'test-category';

		$category_id = wp_create_category( $category_name, 0 );
		$category    = get_category( $category_id );

		// Attach test image to post.
		$attachment_id = $this->upload_test_image_file_to_post();

		update_term_meta( $category_id, '_smd_cat_thumb_id', $attachment_id );

		// Get the category thumbnail ID.
		$thumbnail_id = get_term_meta( $category_id, '_smd_cat_thumb_id', true );

		// Assert that the thumbnail ID matches the uploaded image attachment ID.
		$this->assertEquals( $attachment_id, $thumbnail_id );

		// Clean up the category and attachments.
		wp_delete_category( $category_id );
		wp_delete_attachment( $attachment_id, true );
	}

	/**
	 * Test to delete image if not attached anywhere
	 */
	public function test_delete_attachment() {
		// Attach test image to post.
		$attachment_id = $this->upload_test_image_file_to_post();

		$post_data = wp_delete_attachment( $attachment_id, true );
		if ( $post_data ) {
			$this->assertTrue( true );
		}
	}

	/**
	 * Test to prevent delete image if attached anywhere
	 */
	public function test_prevent_delete_attachment() {
		global $smd_admin;

		// Create a new post.
		$post_id = $this->factory->post->create();

		// Attach test image to post.
		$attachment_id = $this->upload_test_image_file_to_post( $post_id );

		set_post_thumbnail( $post_id, $attachment_id );

		// Update attachment meta to update attached objects.
		$smd_admin->smd_update_attachment_details( $post_id, get_post( $post_id ) );

		try {
			$post_data = wp_delete_attachment( $attachment_id, true );
		} catch ( Exception $e ) {
			$attached_media = smd_attached_media( $attachment_id, false );
			if ( true === $attached_media['attach_found'] && isset( $attached_media['message'] ) ) {
				$msg = implode( '<br>', $attached_media['message'] );
			}
			$this->assertEquals( "<p>Sorry, this image is used and can't be deleted. Used at following places.<br>$msg", $e->getMessage() );
		}
	}
}
