<?php
/**
 * Bootstrap file for the Wapic Fields plugin.
 *
 * @package    Wapic_Fields
 * @subpackage Core
 * @since      1.2.2
 * @author     Wapic Team
 * @license    GPL-2.0+
 * @link       https://wapiclo.com/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Check if the plugin is already loaded
if ( defined( 'WAPIC_FIELDS_INIT' ) ) {
	return;
}

define( 'WAPIC_FIELDS_INIT', true );
define( 'WAPIC_FIELDS_VERSION', '1.2.2' );
define( 'WAPIC_FIELDS_DIR', __DIR__ );
define( 'WAPIC_FIELDS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WAPIC_FIELDS_ASSETS', plugin_dir_url( __FILE__ ) );

// Composer autoload
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Load examples
add_action(
	'init',
	function () {
		if ( apply_filters( 'wapic_fields_load_examples', false ) ) {
			require_once __DIR__ . '/examples/example.php';
		}
	}
);

// Load text domain
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'wapic-fields', false, WAPIC_FIELDS_DIR . '/languages' );
	}
);

// Inisialisasi
add_action(
	'admin_init',
	function () {
		\Wapic_Fields\Assets::get_instance();
	}
);

// Enqueue assets di admin_footer
add_action(
	'admin_footer',
	function () {
		\Wapic_Fields\Assets::get_instance()->enqueue_assets();
	}
);

// Enqueue assets for taxonomy add/edit term
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$assets = \Wapic_Fields\Assets::get_instance();

		// Taxonomy add/edit term
		if ( isset( $_GET['taxonomy'] ) ) {
			$assets->require_asset( 'media' );
			$assets->enqueue_assets();
		}
	}
);
