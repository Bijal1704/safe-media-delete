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
	 * Adding Hooks
	 *
	 * @package Safe Media Delete
	 * @since 1.0.0
	 */
	public function add_hooks() {

		// Action to add category columns.
		add_action( 'admin_init', array( $this, 'smd_admin_init_process' ) );
	}
}
