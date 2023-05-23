<?php
/**
 * Contains test cases of attachment REST API
 *
 * @package Safe Media Delete
 */

/**
 * Class for attachment REST API
 */
class Safe_Media_Delete_Rest_Api_Test extends WP_UnitTestCase {

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
	 * Function to get attachment rest API
	 */
	public function test_get_attachment_rest_api() {

		// Create a new post.
		$post_id = $this->factory->post->create();

		// Attach test image to post.
		$attachment_id = $this->upload_test_image_file_to_post( $post_id );

		set_post_thumbnail( $post_id, $attachment_id );

		// Get the attachment via REST API.
		$response = $this->perform_rest_request( 'GET', '/assignment/v1/attachment/' . $attachment_id );

		// Assert the response status code is 200.
		$this->assertEquals( 200, $response->get_status() );

		// Assert the response contains the attachment data.
		$data = $response->get_data();
		$this->assertEquals( $attachment_id, $data['body']['ID'] );
	}

	/**
	 * Test for delete attachment rest api
	 */
	public function test_delete_attachment_rest_api() {

		// Attach test image to post.
		$attachment_id = $this->upload_test_image_file_to_post();

		$user_id = $this->factory->user->create();
		$user    = new WP_User( $user_id );
		$user->add_cap( 'delete_posts' );

		// Set the current user to simulate authentication.
		wp_set_current_user( $user_id );

		$response = $this->perform_rest_request( 'DELETE', '/assignment/v1/deleteattachment/' . $attachment_id );

		$this->assertEquals( 200, $response->status );
		$this->assertEquals( 'Attachment delete successfully', $response->get_data()['body'] );
	}

	/**
	 * Function to delete attachment rest API
	 */
	public function test_attached_delete_attachment_rest_api() {
		global $smd_admin;

		// Create a new post.
		$post_id = $this->factory->post->create();

		// Attach test image to post.
		$attachment_id = $this->upload_test_image_file_to_post( $post_id );

		set_post_thumbnail( $post_id, $attachment_id );

		// Update attachment meta to update attached objects.
		$smd_admin->smd_update_attachment_details( $post_id, get_post( $post_id ) );

		// Create a user.
		$user_id = $this->factory->user->create();
		$user    = new WP_User( $user_id );
		$user->add_cap( 'delete_posts' );

		// Set the current user to simulate authentication.
		wp_set_current_user( $user_id );

		$response = $this->perform_rest_request( 'DELETE', '/assignment/v1/deleteattachment/' . $attachment_id );

		$this->assertEquals( 200, $response->status );
		$attached_media = smd_attached_media( $attachment_id, true );
		$msg            = '';
		if ( true === $attached_media['attach_found'] ) {
			$msg = 'Used ';
			if ( isset( $attached_media['message']['featured_image'] ) ) {
				$msg .= 'As featured image in post: ';
				$msg .= strtolower( implode( ',', $attached_media['message']['featured_image'] ) ) . ' ';
			}
			if ( isset( $attached_media['message']['category'] ) ) {
				$msg .= 'In category image field: ';
				$msg .= strtolower( implode( ',', $attached_media['message']['category'] ) ) . ' ';
			}
			if ( isset( $attached_media['message']['post_content'] ) ) {
				$msg .= 'In post content: ';
				$msg .= strtolower( implode( ',', $attached_media['message']['post_content'] ) ) . ' ';
			}
		}
		$this->assertEquals( "Sorry, this image is used and can't be deleted. $msg", $response->get_data()['message'] );
	}

	/**
	 * Function to perform REST api
	 *
	 * @param string $method REST API method.
	 * @param string $path REST API URL.
	 */
	private function perform_rest_request( $method, $path ) {
		global $wp_rest_server;

		if ( is_null( $wp_rest_server ) ) {
			$wp_rest_server = new WP_REST_Server();
			do_action( 'rest_api_init' );
		}

		$request  = new WP_REST_Request( $method, $path );
		$response = $wp_rest_server->dispatch( $request );

		return $response;
	}
}
