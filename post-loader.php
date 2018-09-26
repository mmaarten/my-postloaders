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

define( 'THEME_POST_LOADER_FILE', __FILE__ );

/**
 * Render
 */
function theme_post_loader( $loader_id, $include_content = true )
{
	$html_id    = "$loader_id-post-loader";
	$content_id = "$loader_id-post-loader-content";

	?>

	<div id="<?php echo esc_attr( $html_id ); ?>" class="post-loader" data-id="<?php echo esc_attr( $loader_id ); ?>" data-content="#<?php echo esc_attr( $content_id ); ?>">
		<?php theme_post_loader_inner( $loader_id, $include_content ); ?>
	</div><!-- .post-loader -->

	<?php
}

/**
 * Inner
 */
function theme_post_loader_inner( $loader_id, $include_content = true )
{
	if ( has_action( "theme_post_loader_inner/loader=$loader_id" ) )
	{
		do_action( "theme_post_loader_inner/loader=$loader_id", $loader_id, $include_content );

		return;
	}

	theme_post_loader_form( $loader_id );

	if ( $include_content ) 
	{
		theme_post_loader_content( $loader_id );
	}
}

/**
 * Form
 */
function theme_post_loader_form( $loader_id )
{
	$html_id = "$loader_id-post-loader-form";

	?>

	<form id="<?php echo esc_attr( $html_id ); ?>" class="post-loader-form" method="post">

		<?php wp_nonce_field( 'theme_post_loader' ); ?>

		<input type="hidden" name="action" value="theme_post_loader_process">
		<input type="hidden" name="loader" value="<?php echo esc_attr( $loader_id ); ?>">
		<input type="hidden" name="paged" value="1">

		<?php do_action( "theme_post_loader_form/loader=$loader_id", $loader_id ); ?>

	</form><!-- .post-loader-form -->

	<?php
}

/**
 * Content
 */
function theme_post_loader_content( $loader_id )
{
	$html_id = "$loader_id-post-loader-content";

	?>

	<div id="<?php echo esc_attr( $html_id ); ?>" class="post-loader-content">
		<?php theme_post_loader_result( $loader_id ); ?>
	</div><!-- .post-loader-content -->

	<?php
}

/**
 * Result
 */
function theme_post_loader_result( $loader_id, &$query = null )
{
	// Post data

	$paged = isset( $_POST['paged'] ) ? $_POST['paged'] : 1;

	// WP Query

	$query_args = array
	(
		'post_type'   => 'post',
		'post_status' => 'publish',
		'paged'       => $paged,
	);

	$query_args = apply_filters( "theme_post_loader_query_args/loader=$loader_id", $query_args, $loader_id );

	$query = new WP_Query( $query_args );

	// Output

	do_action( "theme_post_loader_result/loader=$loader_id", $query, $loader_id );
}

/**
 * Process
 */
function theme_post_loader_process()
{
	// Check if ajax
	if ( ! wp_doing_ajax() ) 
	{
		return;
	}

	// Check referer
	check_ajax_referer( 'theme_post_loader' );

	// Get result and WP Query object.

	ob_start();

	theme_post_loader_result( $_POST['loader'], $query );

	$result = ob_get_clean();

	// Response
	wp_send_json( array
	(
		'result'        => $result,
		'found_posts'   => intval( $query->found_posts ),
		'post_count'    => $query->post_count,
		'max_num_pages' => $query->max_num_pages,
		'paged'         => $query->get( 'paged' ),
	));
}

add_action( 'wp_ajax_theme_post_loader_process'       , 'theme_post_loader_process' );
add_action( 'wp_ajax_nopriv_theme_post_loader_process', 'theme_post_loader_process' );

/**
 * Shortcode
 */
function theme_post_loader_shortcode( $atts, $content, $tag )
{
	$defaults = array
	(
		'id'              => '',
		'include_content' => true,
	);

	$atts = shortcode_atts( $defaults, $atts, $tag );

	ob_start();

	theme_post_loader( $atts['id'], $atts['include_content'] && $atts['include_content'] !== 'false' );

	return ob_get_clean();
}

add_shortcode( 'post-loader', 'theme_post_loader_shortcode' );

/**
 * Content Shortcode
 */
function theme_post_loader_content_shortcode( $atts, $content, $tag )
{
	$defaults = array
	(
		'loader' => '',
	);

	$atts = shortcode_atts( $defaults, $atts, $tag );

	ob_start();

	theme_post_loader_content( $atts['loader'] );

	return ob_get_clean();
}

add_shortcode( 'post-loader-content', 'theme_post_loader_content_shortcode' );

/**
 * Enqueue Scripts
 */
function theme_post_loader_enqueue_scripts()
{
	wp_enqueue_script( 'theme-post-loader', plugins_url( 'assets/js/post-loader.js', THEME_POST_LOADER_FILE ), array( 'jquery', 'theme' ), false, true );
}

/**
 * Auto Enqueue Scripts
 */
function theme_post_loader_auto_enqueue_scripts()
{
	
}

add_action( 'wp_enqueue_scripts', 'theme_post_loader_auto_enqueue_scripts' );
