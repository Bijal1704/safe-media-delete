<?php
/**
 * Script class to handle CSS and Js files
 *
 * @package Safe Media Delete
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Scripts Class
 *
 * Handles adding scripts functionality to the admin pages
 * as well as the front pages.
 *
 * @package Safe Media Delete
 * @since 1.0.0
 */
class SMD_Scripts {

	/**
	 * Enqueuing Styles
	 *
	 * Loads the required stylesheets for displaying the theme settings page in the WordPress admin section.
	 *
	 * @param string $hook_suffix page hooks suffix.
	 * @package Safe Media Delete
	 * @since 1.0.0
	 */
	public function smd_admin_assets( $hook_suffix ) {

		$pages_hook_suffix = array( 'edit-tags.php', 'term.php' );

		global $typenow, $wp_version;

		$new_ui = $wp_version >= '3.5' ? '1' : '0'; // Check WordPress version for older scripts.

		// Check pages when you needed.
		if ( in_array( $hook_suffix, $pages_hook_suffix ) ) { // phpcs:ignore
			// loads the required styles for the plugin settings page.
			wp_register_style( 'ww-smd-admin', SMD_URL . 'includes/css/smd-admin.css', array(), SMD_VERSION );
			wp_enqueue_style( 'ww-smd-admin' );

			wp_register_script( 'ww-smd-admin', SMD_URL . 'includes/js/smd-admin.js', array(), SMD_VERSION, true );

			wp_localize_script(
				'ww-smd-admin',
				'CategoryImage',
				array(
					'wp_version' => $wp_version,
					'label'      => array(
						'title'  => esc_html__( 'Choose Category Image', 'safe-media-delete' ),
						'button' => esc_html__( 'Choose Image', 'safe-media-delete' ),
					),
					'new_ui'     => $new_ui,
				)
			);

			wp_enqueue_media();
			wp_enqueue_script( 'ww-smd-admin' );

		}
	}

	/**
	 * Adding Hooks
	 *
	 * Adding hooks for the styles and scripts.
	 *
	 * @package Safe Media Delete
	 * @since 1.0.0
	 */
	public function add_hooks() {
		// Admin enqueue scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'smd_admin_assets' ) );

	}
}
