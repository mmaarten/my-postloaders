<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exits when accessed directly.
/*
Plugin Name:  Post Loader
Plugin URI:   
Description:  Render posts via ajax
Version:      0.1.0
Author:       Maarten Menten
Author URI:   https://profiles.wordpress.org/maartenm
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  theme-post-loader
Domain Path:  /languages
*/

define( 'THEME_POST_LOADER_FILE', __FILE__ );

/**
 * Load
 */
function theme_post_loader_load()
{
	$theme = wp_get_theme();

	// Dependency check
	if ( $theme->template == 'theme' ) 
	{
		// Load
		require_once plugin_dir_path( THEME_POST_LOADER_FILE ) . 'load.php';
	}

	else
	{
		// Display notices
		add_action( 'admin_notices', 'theme_post_loader_dependency_notice' );
	}
}

add_action( 'plugins_loaded', 'theme_post_loader_load' );

/**
 * Dependency Notice
 */
function theme_post_loader_dependency_notice()
{
	$message = sprintf( __( '%s needs %s to be installed and active', 'theme-post-loader' ), 
		'<strong>Post Loader</strong>', '<strong>Theme</strong>' );

	printf( '<div class="notice notice-error"><p>%2$s</p></div>', esc_attr( $class ), $message ); 
}
