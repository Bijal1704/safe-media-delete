<?php
/**
 * Admin class to handle backend functionality
 *
 * @package Safe Media Delete
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 *
 * Handles generic Admin functionality and AJAX requests.
 *
 * @package Safe Media Delete
 * @since 1.0.0
 */
class SMD_Admin {

	/**
	 * Add taxonomy hooks on admin init
	 *
	 * @since 1.0.0
	 */
	public function smd_admin_init_process() {
		// Get Taxonomy from plugin setting page.
		$taxonomies = array( 'category' );
		if ( ! empty( $taxonomies ) ) {
			foreach ( (array) $taxonomies as $taxonomy ) {
				$this->smd_taxonomy_hooks( $taxonomy );
			}
		}
	}

	/**
	 * Add custom column field
	 *
	 * @param string $taxonomy WordPress Taxonomy.
	 * @since 1.0.0
	 */
	public function smd_taxonomy_hooks( $taxonomy ) {

		add_action( "{$taxonomy}_add_form_fields", array( $this, 'smd_add_taxonomy_field' ) );
		add_action( "{$taxonomy}_edit_form_fields", array( $this, 'smd_edit_taxonomy_field' ) );

		// Save taxonomy fields.
		add_action( 'edited_' . $taxonomy, array( $this, 'smd_save_taxonomy_custom_meta' ) );
		add_action( 'create_' . $taxonomy, array( $this, 'smd_save_taxonomy_custom_meta' ) );

		// Add custom columns to custom taxonomies.
		add_filter( "manage_edit-{$taxonomy}_columns", array( $this, 'smd_manage_category_columns' ) );
		add_filter( "manage_{$taxonomy}_custom_column", array( $this, 'smd_manage_category_columns_fields' ), 10, 3 );
	}

	/**
	 * Add form field on taxonomy page
	 *
	 * @param string $taxonomy WordPress Taxonomy.
	 * @since 1.0.0
	 */
	public function smd_add_taxonomy_field( $taxonomy ) {
		include_once SMD_DIR . '/includes/admin/form-field/add-form.php';
	}

	/**
	 * Add form field on edit-taxonomy page
	 *
	 * @param string $term WordPress Taxonomy.
	 * @since 1.0.0
	 */
	public function smd_edit_taxonomy_field( $term ) {
		include_once SMD_DIR . '/includes/admin/form-field/edit-form.php';
	}

	/**
	 * Function to add term field on edit screen
	 *
	 * @param int $term_id WordPress Taxonomy ID.
	 * @since 1.0.0
	 */
	public function smd_save_taxonomy_custom_meta( $term_id ) {

		$prefix = SMD_META_PREFIX;

		// phpcs:disable
		// If post data is submitted.
		if ( isset( $_POST[ $prefix . 'cat_thumb_id' ] ) ) {
			$cat_thumb_id = ! empty( $_POST[ $prefix . 'cat_thumb_id' ] ) ? smd_clean( $_POST[ $prefix . 'cat_thumb_id' ] ) : '';

			update_term_meta( $term_id, $prefix . 'cat_thumb_id', $cat_thumb_id );
		}
		// phpcs:enable
	}

	/**
	 * Add image column
	 *
	 * @param array $columns Cloumns values.
	 * @since 1.0.0
	 */
	public function smd_manage_category_columns( $columns ) {

		$new_columns['smd_image'] = esc_html__( 'Image', 'safe-media-delete' );

		$columns = smd_add_array( $columns, $new_columns, 1, true );

		return $columns;
	}

	/**
	 * Add column data
	 *
	 * @param string $output category column html.
	 * @param string $column_name column name.
	 * @param int    $term_id Term id.
	 * @since 1.0.0
	 */
	public function smd_manage_category_columns_fields( $output, $column_name, $term_id ) {

		if ( 'smd_image' === $column_name ) {

			$prefix         = SMD_META_PREFIX;
			$cat_thum_image = smd_term_image( $term_id, 'thumbnail' );

			if ( ! empty( $cat_thum_image ) ) {
				$output .= '<img class="smd-cat-img" src="' . esc_url( $cat_thum_image ) . '" height="70" width="70" />';
			}
		}

		return $output;
	}

	/**
	 * Add custom columns to media library page
	 *
	 * @param array $columns column array.
	 * @since 1.0.0
	 */
	public function smd_column_attached( $columns ) {
		$columns['colAttached'] = esc_html__( 'Linked Object', 'safe-media-delete' );
		return $columns;
	}

	/**
	 * Add custom columns to media library page
	 *
	 * @param string $column_name column name.
	 * @param int    $column_id column id.
	 * @since 1.0.0
	 */
	public function smd_column_attached_row( $column_name, $column_id ) {
		if ( 'colAttached' === $column_name ) {

			$attached_media = smd_attached_media( $column_id, false );

			if ( true === $attached_media['attach_found'] && isset( $attached_media['message'] ) ) {
				$msg = implode( '<br>', $attached_media['message'] );
				echo "<p>$msg</p>"; // phpcs:ignore
			}
		}
	}

	/**
	 * Function to add custom media field in attchment edit page
	 *
	 * @param array  $form_fields attachment fields data.
	 * @param object $post wp_post object.
	 * @since 1.0.0
	 */
	public function smd_media_custom_field( $form_fields, $post ) {
		$post_id        = $post->ID;
		$attached_media = smd_attached_media( $post->ID, false );
		if ( true === $attached_media['attach_found'] && isset( $attached_media['message'] ) ) {
			$field_value                   = implode( '<br>', $attached_media['message'] );
			$form_fields['linked_artical'] = array(
				'html'  => $field_value ? $field_value : '',
				'label' => esc_html__( 'Linked Articles: ', 'safe-media-delete' ),
				'input' => 'html',
			);
		}
		return $form_fields;
	}

	/**
	 * Function to save custom metadata in attchment
	 *
	 * @param array  $id post id.
	 * @param object $post wp_post object.
	 * @since 1.0.0
	 */
	public function smd_update_attachment_details( $id, $post ) {

		if ( 'attchment' === $post->post_type ) {
			return $id;
		}

		$post_attchment = get_post_meta( $id, '_thumbnail_id', true );

		if ( ! empty( $post_attchment ) ) { // Code to store post id in meta.

			$attachment_parents = get_post_meta( $post_attchment, '_smd_media_parents', true );
			$attachment_parents = ! empty( $attachment_parents ) ? $attachment_parents : array();

			if ( ! in_array( $id, $attachment_parents ) ) {
				$attachment_parents[] = $id;
				update_post_meta( $post_attchment, '_smd_media_parents', $attachment_parents );
			}
		} else { // Remove post id from attachment meta.

			if ( isset( $_POST['_smb_old_thumbnail'] ) ) { // phpcs:ignore

				$old_attachment = sanitize_text_field( wp_unslash( $_POST['_smb_old_thumbnail'] ) ); // phpcs:ignore

				if ( ! empty( $old_attachment ) ) {

					$attachment_parents = get_post_meta( $old_attachment, '_smd_media_parents', true );
					$attachment_parents = ! empty( $attachment_parents ) ? $attachment_parents : array();

					if ( ! empty( $attachment_parents ) ) {

						$key = array_search( $id, $attachment_parents );

						if ( ! empty( $key ) || 0 === $key ) {

							unset( $attachment_parents[ $key ] );
						}
					}

					update_post_meta( $old_attachment, '_smd_media_parents', $attachment_parents );
				}
			}
		}

	}


	/**
	 * Function to add custom field to have old thumnail id in post
	 *
	 * @param string $html metabox html.
	 * @param object $id post id.
	 * @since 1.0.0
	 */
	public function smd_featured_image_display( $html, $id ) {

		$thumbnail_id = get_post_meta( $id, '_thumbnail_id', true );

		if ( ! empty( $thumbnail_id ) ) {
			$new_field = '<input type="hidden" name="_smb_old_thumbnail" value="' . esc_attr( $thumbnail_id ) . '" >';

			$html .= $new_field;
		}

		return $html;
	}

	/**
	 * Function to remove thumnail id from the custom field.
	 *
	 * @param string $action detach.
	 * @param object $attachment_id attachment id.
	 * @param object $parent_id attachment parent id.
	 * @since 1.0.0
	 */
	public function smd_media_detach_action( $action, $attachment_id, $parent_id ) {

		if ( 'detach' === $action ) {

			$attachment_parents = get_post_meta( $attachment_id, '_smd_media_parents', true );
			$attachment_parents = ! empty( $attachment_parents ) ? $attachment_parents : array();

			if ( ! empty( $attachment_parents ) ) {

				$key = array_search( $parent_id, $attachment_parents );

				if ( ! empty( $key ) || 0 === $key ) {

					unset( $attachment_parents[ $key ] );
				}
			}

			update_post_meta( $attachment_id, '_smd_media_parents', $attachment_parents );

		}
	}

	/**
	 * Prevent Media delete
	 *
	 * @param int $post_id Post Id.
	 * @since 1.0.0
	 */
	public function smd_prevent_media_delete( $post_id ) {
		$attached_media = smd_attached_media( $post_id, false );
		if ( true === $attached_media['attach_found'] && isset( $attached_media['message'] ) ) {
			$msg = implode( '<br>', $attached_media['message'] );
			wp_die( "<p>Sorry, this image is used and can't be deleted. Used at following places.<br>$msg" ); // phpcs:ignore
			return false;
		}
	}

	/**
	 * Create a new namespace and endpoint
	 *
	 * @param string $rest column name.
	 * @since 1.0.0
	 */
	public function smd_assignment_endpoint( $rest ) {
		register_rest_route(
			'assignment/v1',
			'/attachment/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'smd_attachment_details' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'assignment/v1',
			'/deleteattachment/(?P<id>\d+)',
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'smd_attachment_delete_details' ),
				'permission_callback' => function () {
					return current_user_can( 'delete_posts' );
				},
			)
		);
	}

	/**
	 * API endpoint callback function
	 *
	 * @param array $data endpoint passed data.
	 * @since 1.0.0
	 */
	public function smd_attachment_details( $data ) {
		if ( 'attachment' === get_post_type( $data['id'] ) && wp_attachment_is_image( $data['id'] ) ) {
			$post = get_post( $data['id'] );
			if ( empty( $post ) ) {
				return new WP_Error( 'no_attachment', 'Invalid attchment id', array( 'status' => 404 ) );
			}
			$attached_media         = smd_attached_media( $data['id'], true );
			$post_array['ID']       = $post->ID;
			$post_array['Date']     = $post->post_date;
			$post_array['Slug']     = $post->post_name;
			$post_array['Type']     = $post->post_mime_type;
			$post_array['Link']     = $post->guid;
			$post_array['Alt text'] = $post->post_title;
			$post_array['Attached'] = $attached_media['message'];

			return new WP_REST_Response( array( 'body' => $post_array ), 200 );
		} else {
			return new WP_Error( array( 'message' => 'Invalid attchment id' ), 404 );
		}
	}

	/**
	 * API delete attachment endpoint callback function
	 *
	 * @param array $data endpoint passed data.
	 * @since 1.0.0
	 */
	public function smd_attachment_delete_details( $data ) {
		if ( 'attachment' === get_post_type( $data['id'] ) && wp_attachment_is_image( $data['id'] ) ) {
			$post = get_post( $data['id'] );
			if ( empty( $post ) ) {
				return new WP_REST_Response( array( 'message' => 'Invalid attchment id' ), 400 );
			}
			$attached_media = smd_attached_media( $data['id'], true );
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
				$message = new WP_REST_Response( array( 'message' => "Sorry, this image is used and can't be deleted. $msg" ), 200 );
			} else {
				$deleted = wp_delete_attachment( $data['id'], true );
				if ( $deleted ) {
					$message = new WP_REST_Response( array( 'body' => 'Attachment delete successfully' ), 200 );
				} else {
					$message = new WP_REST_Response( array( 'message' => 'Something went wrong so image deletion failed.' ), 400 );
				}
			}
		} else {
			$message = new WP_REST_Response( array( 'message' => 'Invalid attchment id' ), 400 );
		}
		return $message;
	}

	/**
	 * Adding Hooks
	 *
	 * @package Safe Media Delete
	 * @since 1.0.0
	 */
	public function add_hooks() {

		// Action to add category columns.
		add_action( 'admin_init', array( $this, 'smd_admin_init_process' ) );

		// Add custom columns to media library page.
		add_filter( 'manage_media_columns', array( $this, 'smd_column_attached' ) );
		add_filter( 'manage_media_custom_column', array( $this, 'smd_column_attached_row' ), 10, 2 );

		// Add custom field to edit attachment page.
		add_filter( 'attachment_fields_to_edit', array( $this, 'smd_media_custom_field' ), 10, 2 );

		add_action( 'save_post', array( $this, 'smd_update_attachment_details' ), 10, 2 );

		add_filter( 'admin_post_thumbnail_html', array( $this, 'smd_featured_image_display' ), 10, 2 );

		add_action( 'wp_media_attach_action', array( $this, 'smd_media_detach_action' ), 10, 3 );

		// prevent delete attachment.
		add_action( 'delete_attachment', array( $this, 'smd_prevent_media_delete' ), 10, 1 );

		// Create a new namespace and endpoint.
		add_action( 'rest_api_init', array( $this, 'smd_assignment_endpoint' ) );
	}
}
