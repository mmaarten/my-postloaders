<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit when accessed directly.

function my_postloader_form( $loader_id )
{
	$terms = get_terms( array
	(
		'taxonomy' => 'category',
	));

	if ( $terms ) 
	{
		echo '<nav>';

		foreach ( $terms as $term ) 
		{
			echo '<div class="form-check">';
			printf( '<input type="checkbox" id="term-%s-%s" class="form-check-input autoload" name="terms[]" value="%s">', esc_attr( $term->taxonomy ), esc_attr( $term->term_id ), esc_attr( $term->term_id ) );
			printf( '<label class="form-check-label" for="term-%s-%s">%s</label>', esc_attr( $term->taxonomy ), esc_attr( $term->term_id ), esc_html( $term->name ) );
			echo '</div>';
		}

		echo '</nav>';
	}
}

add_action( 'theme/postloader_form/loader=my-postloader', 'my_postloader_form', 10, 2 );

function my_postloader_query_args( $query_args, $is_submit, $loader_id )
{
	$terms = $is_submit && isset( $_POST['terms'] ) ? $_POST['terms'] : array();

	if ( $terms ) 
	{
		$query_args['tax_query'] = array
		(
			array
			(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', $terms ),
				'operator' => 'IN',
			),
		);
	}

	return $query_args;
}

add_action( 'theme/postloader_query_args/loader=my-postloader', 'my_postloader_query_args', 10, 3 );

function my_postloader_content( $the_query, $loader_id )
{
	if ( $the_query->have_posts() ) 
	{
		echo '<div class="row">';

		while ( $the_query->have_posts() ) 
		{
			$the_query->the_post();

			echo '<div class="col-md-6 col-lg-4">';

			get_template_part( 'template-parts/loop', 'card' );

			echo '</div>';
		}

		echo '</div>'; // .row

		theme\postloader_pagination( $the_query );

		wp_reset_postdata();
	}

	else
	{
		printf( '<div class="alert alert-info">%s</div>', __( 'No posts available.', 'theme' ) );
	}
}

add_action( 'theme/postloader_content/loader=my-postloader', 'my_postloader_content', 10, 2 );
