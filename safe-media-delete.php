<?php
/**
 * Plugin Name:       Safe Media Delete
 * Plugin URI:        https://github.com/Bijal1704/safe-media-delete
 * Description:       Test Task.
 * Version:           1.0.0
 * Author:            Bijal
 * Author URI:        https://github.com/Bijal1704/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       safe-media-delete
 * Domain Path:       /languages
 *
 * @package   Safe Media Delete
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Basic Plugin Definitions
 *
 * @package Safe Media Delete
 * @since   1.0.0
 */
if ( ! defined( 'SMD_VERSION' ) ) {
	define( 'SMD_VERSION', '1.0.0' ); // version of plugin.
}

if ( ! defined( 'SMD_DIR' ) ) {
	define( 'SMD_DIR', dirname( __FILE__ ) ); // plugin dir.
}
if ( ! defined( 'SMD_ADMIN' ) ) {
	define( 'SMD_ADMIN', SMD_DIR . '/includes/admin' ); // plugin admin dir.
}
if ( ! defined( 'SMD_URL' ) ) {
	define( 'SMD_URL', plugin_dir_url( __FILE__ ) ); // plugin url.
}
if ( ! defined( 'SMD_TEXT_DOMAIN' ) ) {
	define( 'SMD_TEXT_DOMAIN', 'safe-media-delete' ); // text domain for translation.
}
if ( ! defined( 'SMD_PLUGIN_BASENAME' ) ) {
	define( 'SMD_PLUGIN_BASENAME', basename( SMD_DIR ) ); // Plugin base name.
}

if ( ! defined( 'SMD_META_PREFIX' ) ) {
	define( 'SMD_META_PREFIX', '_smd_' ); // Plugin base name.
}

/**
 * Load Text Domain
 *
 * This gets the plugin ready for translation.
 *
 * @package Safe Media Delete
 * @since   1.0.0
 */
function smd_load_textdomain() {
	// Set filter for plugin's languages directory.
	$smd_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$smd_lang_dir = apply_filters( 'smd_languages_directory', $smd_lang_dir );

	// Traditional WordPress plugin locale filter.
	$locale = apply_filters( 'plugin_locale', get_locale(), 'safe-media-delete' );
	$mofile = sprintf( '%1$s-%2$s.mo', 'safe-media-delete', $locale );

	// Setup paths to current locale file.
	$mofile_local  = $smd_lang_dir . $mofile;
	$mofile_global = WP_LANG_DIR . '/' . SMD_PLUGIN_BASENAME . '/' . $mofile;

	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/safe-media-delete folder.
		load_textdomain( 'safe-media-delete', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/safe-media-delete/languages/ folder.
		load_textdomain( 'safe-media-delete', $mofile_local );
	} else { // Load the default language files.
		load_plugin_textdomain( 'safe-media-delete', false, $smd_lang_dir );
	}
}

/**
 * Load Plugin
 *
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 *
 * @package Safe Media Delete
 * @since   1.0.0
 */
function smd_plugin_loaded() {
	// load first plugin text domain.
	Smd_Load_textdomain();
}

// add action to load plugin.
add_action( 'plugins_loaded', 'smd_plugin_loaded' );

/**
 * Initialize all global variables
 *
 * @package Safe Media Delete
 * @since   1.0.0
 */
global $smd_scripts, $smd_admin;

/**
 * Includes
 *
 * Includes all the needed files for plugin
 *
 * @package Safe Media Delete
 * @since   1.0.0
 */
require_once SMD_DIR . '/includes/smd-function.php';

require_once SMD_DIR . '/includes/class-smd-scripts.php';
$smd_scripts = new SMD_Scripts();
$smd_scripts->add_hooks();

// includes admin class file.
require_once SMD_ADMIN . '/class-smd-admin.php';
$smd_admin = new SMD_Admin();
$smd_admin->add_hooks();
