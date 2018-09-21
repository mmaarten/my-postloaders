<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exits when accessed directly.

/**
 * Arguments
 */
function theme_post_loader_get_args( $loader_id = null )
{
	// Defaults
	$defaults = (array) apply_filters( 'theme/post_loader_defaults', array
	(
		'before'            => '',
		'before_form'       => '',
		'after_form'        => '',
		'before_result'     => '',
		'before_items'      => '',
		'before_item'       => '',
		'item'              => 'card', // item template name.
		'after_item'        => '',
		'after_items'       => '',
		'after_result'      => '',
		'after'             => '',
		'not_found_wrap'    => '<div class="alert alert-warning">%1$s</div>',
		'not_found_message' => null,
		'progress_content'  => theme_get_icon( 'spinner' ),
	));

	if ( $loader_id ) 
	{
		return (array) apply_filters( "theme/post_loader_args/loader=$loader_id", $defaults );
	}

	return $defaults;
}

/**
 * Render Post Loader
 */
function theme_post_loader( $loader_id )
{
	$args = theme_post_loader_get_args( $loader_id );

	?>

	<div id="<?php echo esc_attr( $loader_id ); ?>-post-loader" class="post-loader" data-id="<?php echo esc_attr( $loader_id ); ?>">

		<?php echo $args['before']; ?>
		<?php echo $args['before_form']; ?>

		<form class="post-loader-form" method="post">
			
			<?php wp_nonce_field( 'post_loader', THEME_NONCE_NAME ); ?>
			<input type="hidden" name="action" value="theme_post_loader_process">
			<input type="hidden" name="loader" value="<?php echo esc_attr( $loader_id ); ?>">
			<input type="hidden" name="paged" value="1">

			<?php do_action( "theme/post_loader_form/loader=$loader_id" ); ?>

		</form><!-- .post-loader-form -->

		<?php echo $args['after_form']; ?>
		<?php echo $args['before_result']; ?>

		<div class="post-loader-result">
			<?php theme_post_loader_result( $loader_id ); ?>
		</div><!-- .post-loader-result -->

		<?php echo $args['after_result']; ?>

		<div class="post-loader-progress">
			<?php echo $args['progress_content']; ?>
		</div><!-- .post-loader-progress -->

		<?php echo $args['after']; ?>

	</div><!-- .post-loader -->

	<?php
}

/**
 * Render Result
 */
function theme_post_loader_result( $loader_id, &$wp_query = null )
{
	$paged = isset( $_POST['paged'] ) ? $_POST['paged'] : 1;

	/**
	 * WP Query
	 * ---------------------------------------------------------------
	 */

	$query_args = array
	(
		'post_type'         => 'post',
		'paged'             => $paged,
		'post_status'       => 'publish',
		'theme_post_loader' => $loader_id, 
	);

	$wp_query = new WP_Query( $query_args );

	/**
	 * Custom result
	 * ---------------------------------------------------------------
	 */

	$tag = "theme/post_loader_result/loader=$loader_id";

	if ( has_action( $tag ) ) 
	{
		do_action_ref_array( $tag, array( &$wp_query, $loader_id ) );

		return;
	}

	/**
	 * Result
	 * ---------------------------------------------------------------
	 */

	$args = theme_post_loader_get_args( $loader_id );

	if ( $wp_query->have_posts() ) 
	{
		echo $args['before_items'];
		
		while ( $wp_query->have_posts() ) 
		{
			$wp_query->the_post();

			echo $args['before_item'];

			locate_template( "template-parts/{$args['item']}.php", true, false );

			echo $args['after_item'];
		}

		echo $args['after_items'];

		theme_posts_ajax_pagination( $wp_query );
	}

	else
	{
		if ( is_null( $args['not_found_message'] ) ) 
		{
			$post_type  = get_post_type_object( $wp_query->get( 'post_type' ) );
			$post_count = wp_count_posts( $post_type->name );

			if ( $post_count->publish ) 
			{
				$message = sprintf( __( 'No %s found that match the chosen search criteria.', 'theme-post-loader' ), strtolower( $post_type->labels->name ) );
			}

			else
			{
				$message = sprintf( __( 'No %s available.', 'theme-post-loader' ), strtolower( $post_type->labels->name ) );
			}
		}

		else
		{
			$message = $args['not_found_message'];
		}

		printf( $args['not_found_wrap'], $message );
	}
}

function theme_post_loader_pre_get_posts( $wp_query ) 
{
	$loader_id = $wp_query->get( 'theme_post_loader' );

	if ( ! $loader_id )
	{
		return;
	}

	do_action_ref_array( "theme/post_loader_wp_query/loader=$loader_id", array( &$wp_query, $loader_id ) );
}

add_action( 'pre_get_posts', 'theme_post_loader_pre_get_posts' );

/**
 * Process
 */
function theme_post_loader_process()
{
	/**
	 * Check
	 * ---------------------------------------------------------------
	 */

	// Check ajax
	if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) 
	{
		return;
	}

	// Check referer
	check_ajax_referer( 'post_loader', THEME_NONCE_NAME );

	/**
	 * Post data
	 * ---------------------------------------------------------------
	 */

	$loader_id = isset( $_POST['loader'] ) ? $_POST['loader'] : null;

	/**
	 * Content
	 * ---------------------------------------------------------------
	 */

	// Start output buffer
	ob_start();

	// Print result
	theme_post_loader_result( $loader_id, $wp_query );

	// Fetch output
	$content = ob_get_clean();

	/**
	 * Response
	 * ---------------------------------------------------------------
	 */

	// Make sure WP Query is set
	if ( ! $wp_query instanceof WP_Query ) 
	{
		$wp_query = new WP_Query();
	}

	wp_send_json( array
	(
		'content'       => $content,
		'found_posts'   => intval( $wp_query->found_posts ),
		'post_count'    => $wp_query->post_count,
		'max_num_pages' => $wp_query->max_num_pages,
		'paged'         => $wp_query->get( 'paged' ),
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
		'id' => '',
	);

	$atts = shortcode_atts( $defaults, $atts, $tag );

	ob_start();

	theme_post_loader( $atts['id'] );

	return ob_get_clean();
}

add_shortcode( 'post-loader', 'theme_post_loader_shortcode' );

function theme_post_loader_enqueue_scripts()
{
	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

	wp_enqueue_style( 'theme-post-loader', plugins_url( 'css/post-loader.css', THEME_POST_LOADER_FILE ) );
	wp_enqueue_script( 'theme-post-loader', plugins_url( "js/post-loadermin.js", THEME_POST_LOADER_FILE ), array( 'jquery', 'theme' ), false, true );
}

function theme_post_loader_auto_enqueue_scripts()
{
	if ( theme_has_shortcode( 'post-loader' ) ) 
	{
		theme_post_loader_enqueue_scripts();
	}
}

add_action( 'wp_enqueue_scripts', 'theme_post_loader_auto_enqueue_scripts' );

/*
----------------------------------------------------------------------
 Example
----------------------------------------------------------------------
*/

function theme_post_loader_example_args( $args )
{
	// Create grid
	return array_merge( $args, array
	(
		'before'              => '<div class="row">',
		'before_form'         => '<div class="col-md-3">',
		'after_form'          => '</div>',
		'before_result'       => '<div class="col">',
		'before_items'        => '<div class="row">',
		'before_item'         => '<div class="col-md-4">',
		'after_item'          => '</div>',
		'after_items'         => '</div>',
		'after_result'        => '</div>',
		'after'               => '</div>',
	));
}

add_filter( 'theme/post_loader_args/loader=example', 'theme_post_loader_example_args' );

function theme_post_loader_example_form()
{
	// Render category filter

	$terms = get_terms( array
	(
		'taxonomy' => 'category',
	));

	echo '<div class="terms d-flex flex-column">';

	foreach ( $terms as $term ) 
	{
		// add `autoload` class to load on change
		printf( '<label><input type="checkbox" class="autoload" name="terms[]" value="%s"> %s</label>', esc_attr( $term->term_id ), esc_html__( $term->name ) );
	}

	echo '</div>';
}

add_action( 'theme/post_loader_form/loader=example', 'theme_post_loader_example_form' );

function theme_post_loader_example_wp_query( &$wp_query )
{
	$terms = isset( $_POST['terms'] ) && is_array( $_POST['terms'] ) ? $_POST['terms'] : array();

	if ( $terms ) 
	{
		$tax_query = array
		(
			'relation' => 'AND',
		);

		foreach ( $terms as $term_id ) 
		{
			$tax_query[] = array
			( 
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => (array) intval( $term_id ),
				'operator' => 'IN',
			);
		}

		$wp_query->set( 'tax_query', $tax_query );
	}
}

add_action( 'theme/post_loader_wp_query/loader=example', 'theme_post_loader_example_wp_query' );
