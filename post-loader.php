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
 * Render
 */
function theme_post_loader( $loader_id, $include_content = true )
{
	$elem_id = "$loader_id-post-loader";
	$target  = "$loader_id-post-loader-content";
	
	?>

	<div id="<?php echo esc_attr( $elem_id ); ?>" class="post-loader" data-target="#<?php echo esc_attr( $target ); ?>">
		
		<form class="post-loader-form" method="post">
			
			<?php wp_nonce_field( 'post_loader', THEME_NONCE_NAME ); ?>

			<input type="hidden" name="action" value="theme_post_loader_process">
			<input type="hidden" name="loader" value="<?php echo esc_attr( $loader_id ); ?>">
			<input type="hidden" name="paged" value="1">

			<?php do_action( "theme_post_loader_form/loader=$loader_id", $loader_id ); ?>

		</form><!-- .post-loader-form -->

		<?php if ( $include_content ) theme_post_loader_content( $loader_id ); ?>

	</div><!-- .post-loader -->

	<?php
}

/**
 * Content
 */
function theme_post_loader_content( $loader_id )
{
	$elem_id = "$loader_id-post-loader-content";

	?>

	<div id="<?php echo esc_attr( $elem_id ); ?>" class="post-loader-content">
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
function theme_post_loader_process( $loader_id )
{
	// Check ajax
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) 
	{
		return;
	}

	// Check referer
	check_ajax_referer( 'post_loader', THEME_NONCE_NAME );

	// Get result and WP Query object

	ob_start();

	theme_post_loader_result( $_POST['loader'], $query );

	$content = ob_get_clean();

	// Response
	wp_send_json( array
	(
		'content'       => $content,
		'found_posts'   => intval( $query->found_posts ),
		'post_count'    => $query->post_count,
		'max_num_pages' => $query->max_num_pages,
		'paged'         => $query->get( 'paged' ),
	));
}

add_action( 'wp_ajax_theme_post_loader_process'		  , 'theme_post_loader_process' );
add_action( 'wp_ajax_nopriv_theme_post_loader_process', 'theme_post_loader_process' );

/**
 * Shortcode
 */
function theme_post_loader_shortcode( $atts, $content = null, $tag )
{
	$defaults = array
	(
		'id'              => '',
		'include_content' => true,
	);

	$atts = shortcode_atts( $defaults, $atts, $tag );

	$include_content = $atts['include_content'] && $atts['include_content'] !== 'false';

	ob_start();

	theme_post_loader( $atts['id'], $include_content );

	return ob_get_clean();
}

add_shortcode( 'post-loader', 'theme_post_loader_shortcode' );

/**
 * Content Shortcode
 */
function theme_post_loader_content_shortcode( $atts, $content = null, $tag )
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
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

	wp_enqueue_style( 'theme-post-loader', plugins_url( 'css/post-loader.css', THEME_POST_LOADER_FILE ) );
	wp_enqueue_script( 'theme-post-loader', plugins_url( "js/post-loader$min.js", THEME_POST_LOADER_FILE ), array( 'jquery', 'theme' ), false, true );
}

/**
 * Auto Enqueue Scripts
 */
function theme_post_loader_auto_enqueue_scripts()
{
	if ( theme_has_shortcode( 'post-loader' ) ) 
	{
		theme_post_loader_enqueue_scripts();
	}
}

add_action( 'wp_enqueue_scripts', 'theme_post_loader_auto_enqueue_scripts' );
