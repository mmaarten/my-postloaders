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

$autoloader = __DIR__ . '/vendor/autoload.php';
if (!is_readable($autoloader)) {
    error_log(
        sprintf(
            // translators: 1: plugin name. 2: Composer command. 3: theme directory
            __('%1$s installation is incomplete. Run %2$s within the %3$s directory.', 'my-postloaders'),
            __('My Postloaders', 'my-postloaders'),
            '<code>composer install</code>',
            '<code>' . str_replace(ABSPATH, '', __DIR__) . '</code>'
        )
    );
    return;
}
require $autoloader;

define('POSTLOADER_PLUGIN_FILE', __FILE__);

add_action('plugins_loaded', ['\My\Postloaders\App', 'init']);
