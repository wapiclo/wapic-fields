<?php
/**
 * Plugin Name:       Wapic Fields
 * Plugin URI:        https://wapiclo.com/wapic-fields
 * Description:       A custom field for WordPress options page and meta box
 * Version:           1.2.2
 * Requires at least: 5.8
 * Requires PHP:      8.2
 * Author:            Wapiclo
 * Author URI:        https://wapiclo.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wapic-fields
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Load the bootstrap file.
require_once __DIR__ . '/bootstrap.php';