<?php
/**
 * Plugin Name:       My Postloaders
 * Plugin URI:        https://github.com/mmaarten/my-postloaders
 * Description:       Load posts via Ajax.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      5.6
 * Author:            Maarten Menten
 * Author URI:        https://profiles.wordpress.org/maartenm/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-postloaders
 * Domain Path:       /languages
 */

define('MY_POSTLOADERS_NONCE_NAME', 'postloader_nonce');
define('MY_POSTLOADERS_PLUGIN_FILE', __FILE__);
define('MY_POSTLOADERS_SHORTCODE_TAG', 'postloader');

require plugin_dir_path(MY_POSTLOADERS_PLUGIN_FILE) . 'vendor/autoload.php';

add_action('plugins_loaded', ['My\Postloaders\App', 'init']);
