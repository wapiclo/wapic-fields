<?php

/**
 * Bootstrap file for the Wapic Fields plugin.
 *
 * @package    Wapic_Fields
 * @subpackage Core
 * @author     Wapiclo Team
 * @license    GPL-2.0+
 * @link       https://wapiclo.com/
 * @version    2.4.0
 */

if (! defined('ABSPATH')) {
	exit;
}
// Check if the plugin is already loaded
if (defined('WAPIC_FIELDS_INIT')) {
	return;
}

define('WAPIC_FIELDS_INIT', true);
define('WAPIC_FIELDS_VERSION', '2.4.0');
define('WAPIC_FIELDS_DIR', __DIR__);
define('WAPIC_FIELDS_PATH', plugin_dir_path(__FILE__));
define('WAPIC_FIELDS_ASSETS', plugin_dir_url(__FILE__));

// Composer autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
	require_once __DIR__ . '/vendor/autoload.php';
}

// Register bundled translations on init, including when used as a nested library.
add_action(
	'init',
	function () {
		load_plugin_textdomain( 'wapic-fields', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	},
	0
);

// Inisialisasi
add_action(
	'admin_init',
	function () {
		\Wapic_Fields\Assets::get_instance();
	}
);

// Enqueue assets
add_action(
	'admin_enqueue_scripts',
	function ($hook) {
		$assets = \Wapic_Fields\Assets::get_instance();

		// Proaktif load media asset untuk halaman metabox dan taxonomy
		if (in_array($hook, array('post.php', 'post-new.php')) || filter_input(INPUT_GET, 'taxonomy')) {
			$assets->require_asset('media');
		}

		// Load assets di head untuk menghindari FOUC (Flash of Unstyled Content)
		$assets->enqueue_assets();
	}
);

// Fallback untuk asset yang baru di-require saat proses render komponen (on-the-fly)
add_action(
	'admin_footer',
	function () {
		\Wapic_Fields\Assets::get_instance()->enqueue_assets();
	}
);
