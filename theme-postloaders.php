<?php
/*
Plugin Name: Postloaders
Plugin URI:
Description: Load posts via Ajax
Version: 0.1.0
Author: Maarten Menten
Author URI: https://profiles.wordpress.org/maartenm/
Text Domain: postloader
Domain Path: /languages
*/

namespace theme;

define( 'POSTLOADERS_FILE', __FILE__ );
define( 'POSTLOADERS_VERSION', '0.1.0' );
define( 'POSTLOADERS_SHORTCODE', 'postloader' );

/**
 * Render Postloader
 *
 * @param string $loader_id
 */
function postloader( $loader_id )
{
	$html_id = "$loader_id-postloader";

	?>

	<div class="postloader" id="<?php echo esc_attr( $html_id ); ?>">

		<form class="postloader-form" method="post">
			
			<?php wp_nonce_field( 'postloader_form', THEME_NONCE_NAME ); ?>

			<input type="hidden" name="action" value="theme_postloader_process">
			<input type="hidden" name="loader" value="<?php echo esc_attr( $loader_id ); ?>">
			<input type="hidden" name="page" value="1">

			<?php do_action( "theme/postloader_form/loader=$loader_id", $loader_id ); ?>

		</form><!-- .postloader-form -->

		<div class="postloader-content">

			<?php postloader_content( $loader_id ); ?>
		
		</div><!-- .postloader-content -->

	</div><!-- .postloader -->

	<?php
}

/**
 * Postloader Content
 *
 * @param string $loader_id
 * @param null   $the_query
 */
function postloader_content( $loader_id, &$the_query = null )
{
	$is_submit = postloader_is_submit( $loader_id );

	// Posted data

	$page = $is_submit && ! empty( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;

	// Query arguments

	$query_args = array
	(
		'post_type'   => 'post',
		'post_status' => 'publish',
		'paged'       => max( $page, 1 ),
	);

	$query_args = apply_filters( "theme/postloader_query_args/loader=$loader_id", $query_args, $is_submit, $loader_id );

	// Set WP Query

	$the_query = new \WP_Query( $query_args );

	// Output

	do_action( "theme/postloader_content/loader=$loader_id", $the_query, $loader_id );
}

/**
 * Check if form is submitted
 *
 * @param mixed $loader_id
 *
 * @return bool
 */
function postloader_is_submit( $loader_id = null )
{
	if ( empty( $_POST[ THEME_NONCE_NAME ] ) ) 
	{
		return false;
	}

	if ( ! wp_verify_nonce( $_POST[ THEME_NONCE_NAME ], 'postloader_form' ) ) 
	{
		return false;
	}

	if ( ! is_null( $loader_id ) ) 
	{
		return isset( $_POST['loader'] ) && $loader_id == $_POST['loader'];
	}

	return true;
}

/**
 * Process
 *
 * @param string $loader_id
 */
function postloader_process()
{	
	// Check if ajax

	if ( ! wp_doing_ajax() )
	{
		return;
	}

	// Check nonce and referer

	check_ajax_referer( 'postloader_form', THEME_NONCE_NAME );

	// Get loader id

	$loader_id = $_POST['loader'];

	// Get content en WP_Query object

	ob_start();

	postloader_content( $loader_id, $the_query );

	$content = ob_get_clean();

	// Response

	$response = array
	(
		'content' => $content,
	);

	$response = apply_filters( "postloader_response/loader=$loader_id", $response, $the_query, $loader_id );

	wp_send_json( $response );
}

add_action( 'wp_ajax_theme_postloader_process'       , 'theme\postloader_process' );
add_action( 'wp_ajax_nopriv_theme_postloader_process', 'theme\postloader_process' );

/**
 * Pagination
 *
 * @param WP_Query $wp_query
 * @param array    $args
 */
function postloader_pagination( $wp_query, $args = array() )
{
	$defaults = array
	(
		'mid_size'           => 2,
		'prev_text'          => __( '&laquo;', 'theme' ),
		'next_text'          => __( '&raquo;', 'theme' ),
		'screen_reader_text' => __( 'Posts navigation', 'theme' ),
	);

	$args = wp_parse_args( $args, $defaults );

	// Check if pagination is needed

	$paged = $wp_query->get( 'paged' );

	if ( ! $paged || $wp_query->max_num_pages == 1 ) 
	{
		return;
	}

	// Set size

	$size_start = $paged - $args['mid_size'];
	$size_end   = $paged + $args['mid_size'];

	if ( $size_start < 1 ) 
	{
		$size_start = 1;
	}

	if ( $size_end > $wp_query->max_num_pages ) 
	{
		$size_end = $wp_query->max_num_pages;
	}

	// Output

	?>

	<nav class="postloader-pagination pagination-nav" aria-label="<?php echo esc_attr( $args['screen_reader_text'] ); ?>">
		
		<ul class="pagination">

			<?php if ( $paged > 1 ) : ?>
			<li class="page-item"><a class="page-link" data-page="<?php echo $paged - 1; ?>" href="#" tabindex="-1"><?php echo $args['prev_text']; ?></a></li>
			<?php endif; ?>

			<?php for ( $page = $size_start; $page <= $size_end; $page++ ) : 

				$class   = 'page-item';
				$content = $page;

				if ( $page == $paged ) 
				{
					$class   .= ' active';
					$content = sprintf( '%d <span class="sr-only">%s</span>', $page, esc_html__( '(current)', 'theme' ) );
				}

			?>
			<li class="<?php echo $class; ?>"><a class="page-link" href="#" data-page="<?php echo $page; ?>"><?php echo $content; ?></a></li>
			<?php endfor; ?>
			
			<?php if ( $paged < $wp_query->max_num_pages ) : ?>
			<li class="page-item"><a class="page-link" data-page="<?php echo $paged + 1; ?>" href="#" tabindex="-1"><?php echo $args['next_text']; ?></a></li>
			<?php endif; ?>

		</ul><!-- .pagination -->

	</nav><!-- .pagination-nav -->

	<?php
}

/**
 * Shortcode
 *
 * @param array $atts
 *
 * @return string
 */
function postloader_shortcode( $atts )
{
	if ( ! is_array( $atts ) || ! array_key_exists( 'id', $atts ) ) 
	{
		trigger_error( 'Postloader shortcode `id` attribute is required.', E_USER_WARNING );

		return '';
	}

	ob_start();

	postloader( $atts['id'] );

	return ob_get_clean();
}

add_shortcode( POSTLOADERS_SHORTCODE, 'theme\postloader_shortcode' );

/**
 * Scripts
 */
function postloader_scripts()
{
	wp_register_script( 'postloader', plugins_url( 'js/postloader.js', POSTLOADERS_FILE ), array( 'jquery' ), POSTLOADERS_VERSION );

	wp_localize_script( 'postloader', 'PostloaderDefaults', array
	(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	));

	// Auto enqueue

	$post = get_post();

	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, POSTLOADERS_SHORTCODE ) ) 
	{
		wp_enqueue_script( 'postloader' );
	}
}

add_action( 'wp_enqueue_scripts', 'theme\postloader_scripts' );
