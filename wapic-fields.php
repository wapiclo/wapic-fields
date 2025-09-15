<?php

/**
 * Plugin Name: Wapic Fields
 * Plugin URI: https://wapiclo.com/wapic-fields
 * Description: A custom field for WordPress options page and meta box
 * Version: 1.1.1
 * Author: Wapiclo
 * Author URI: https://wapiclo.com/
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 8.2
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('WPINC')) {
    die;
}

require_once __DIR__ . '/bootstrap.php';

/**
 * Update checker for plugin
 */
if (class_exists('YahnisElsts\PluginUpdateChecker\v5\PucFactory')) {
    $myUpdateChecker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker('https://github.com/wapiclo/wapic-fields/', __FILE__, 'wapic-fields');
    $myUpdateChecker->setBranch('stable_release');
}
