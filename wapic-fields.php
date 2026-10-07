<?php

/**
 * Plugin Name:       Wapic Fields
 * Plugin URI:        https://wapiclo.com/wapic-fields
 * Description:       A custom field for WordPress options page and meta box
 * Version:           2.4.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Wapiclo
 * Author URI:        https://wapiclo.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wapic-fields
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('ABSPATH')) {
	die;
}

// Load the bootstrap file.
require_once __DIR__ . '/bootstrap.php';

// Load examples
if (defined('WAPIC_FIELDS_LOAD_EXAMPLES') && WAPIC_FIELDS_LOAD_EXAMPLES === true) {
	require_once __DIR__ . '/examples/example.php';
}
