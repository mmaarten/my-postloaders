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

/**
 * Include autoloader.
 */
$autoloader = __DIR__ . '/vendor/autoload.php';

if (! is_readable($autoloader)) {
    error_log(
        sprintf(
            /* translators: 1: Composer command. 2: plugin directory */
            esc_html__(
                'Your installation of the My Postloaders plugin is incomplete. Please run %1$s within the %2$s directory.',
                'my-postloaders'
            ),
            '<code>composer install</code>',
            '<code>' . esc_html(str_replace(ABSPATH, '', __DIR__)) . '</code>'
        )
    );
    return;
}

require $autoloader;

/**
 * Define constants.
 */
define('MY_POSTLOADERS_NONCE_NAME', 'postloader_nonce');
define('MY_POSTLOADERS_PLUGIN_FILE', __FILE__);
define('MY_POSTLOADERS_SHORTCODE_TAG', 'postloader');

/**
 * Initialize application.
 */
add_action('plugins_loaded', ['My\Postloaders\App', 'init']);

require_once plugin_dir_path(MY_POSTLOADERS_PLUGIN_FILE) . 'includes/api.php';
