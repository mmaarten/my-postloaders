<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exits when accessed directly.
/*
Plugin Name:  Post Loader
Plugin URI:   
Description:  Render posts via ajax.
Version:      0.1.0
Author:       Maarten Menten
Author URI:   https://profiles.wordpress.org/maartenm
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  theme-post-loader
Domain Path:  /languages
*/

define( 'THEME_POST_LOADER_FILE'      , __FILE__ );
define( 'THEME_POST_LOADER_NONCE_NAME', 'theme_post_loader_nonce' );

require_once plugin_dir_path( THEME_POST_LOADER_FILE ) . 'includes/class-theme-post-loader.php';
require_once plugin_dir_path( THEME_POST_LOADER_FILE ) . 'includes/class-theme-post-loader-manager.php';

/**
 * Init
 */
function theme_post_loader_init()
{
	// Loads sample post loader
	require_once plugin_dir_path( THEME_POST_LOADER_FILE ) . 'includes/class-theme-sample-post-loader.php';
}

add_action( 'init', 'theme_post_loader_init' );

/**
 * Create Post Loader
 */
function theme_create_post_loader( $id, $args = array() )
{
	$manager = Theme_Post_Loader_Manager::get_instance();

	return $manager->create_loader( $id, $args );
}

/**
 * Register Post Loader
 */
function theme_register_post_loader( $loader )
{
	$manager = Theme_Post_Loader_Manager::get_instance();

	$manager->register_loader( $loader );
}

/**
 * Unregister Post Loader
 */
function theme_unregister_post_loader( $loader_id )
{
	$manager = Theme_Post_Loader_Manager::get_instance();

	$manager->unregister_loader( $loader_id );
}

/**
 * Get Post Loaders
 */
function theme_get_post_loaders( $loader_id )
{
	$manager = Theme_Post_Loader_Manager::get_instance();

	return $manager->get_loaders( $loader_id );
}

/**
 * Get Post Loader
 */
function theme_get_post_loader( $loader_id )
{
	$manager = Theme_Post_Loader_Manager::get_instance();

	return $manager->get_loader( $loader_id );
}

/**
 * Render Post Loader
 */
function theme_post_loader( $loader_id )
{
	$manager = Theme_Post_Loader_Manager::get_instance();

	$manager->render_loader( $loader_id );
}

/**
 *  Post Loader Shortcode
 */
function theme_post_loader_shortcode( $atts, $content, $tag )
{
	$defaults = array
	(
		'id' => '',
	);

	$atts = shortcode_atts( $defaults, $atts, $tag );

	ob_start();

	theme_post_loader( $atts['id'] );

	return ob_get_clean();
}

add_shortcode( 'post-loader', 'theme_post_loader_shortcode' );

/**
 * Enqueue Scripts
 */
function theme_post_loader_enqueue_scripts()
{
	wp_enqueue_script( 'theme-post-loader', plugins_url( 'assets/js/post-loader.js', THEME_POST_LOADER_FILE ), array( 'jquery' ), false, true );
	
	wp_localize_script( 'theme-post-loader', 'themePostLoader', array
	(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	));
}

/**
 * Auto Enqueue Scripts
 */
function theme_post_loader_auto_enqueue_scripts()
{
	$post = get_post();

	if ( is_a( $post, 'WP_Post') && has_shortcode( $post->post_content, 'post-loader' ) ) 
	{
		theme_post_loader_enqueue_scripts();
	}
}

add_action( 'wp_enqueue_scripts', 'theme_post_loader_auto_enqueue_scripts' );
