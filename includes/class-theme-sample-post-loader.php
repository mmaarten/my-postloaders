<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exits when accessed directly.

class Theme_Sample_Post_Loader extends Theme_Post_Loader
{
	public function __construct()
	{
		parent::__construct( 'sample');
	}

	/**
	 * Inside
	 */
	public function inside()
	{
		// Create grid

		?>

		<div class="row">

			<div class="col-lg-4">
				<?php $this->form(); ?>
			</div>

			<div class="col">
				<?php $this->content(); ?>
			</div>

		</div>

		<?php
	}

	/**
	 * Form
	 */
	public function form()
	{
		// Create term filter

		$terms = get_terms( array
		(
			'taxonomy'   => 'category',
			'hide_empty' => false, // test not found message
		));

		?>

		<form class="post-loader-form" method="post">

			<?php $this->settings_fields() ?>

			<?php if ( $terms ) : ?>
			<div class="term-filter d-lg-flex flex-lg-column">
				<?php foreach ( $terms as $term ) : ?>
				<label class="btn btn-outline-dark btn-sm text-lg-left"><input type="checkbox" class="autoload d-none" name="terms[]" value="<?php echo esc_attr( $term->term_id ); ?>"> <?php echo esc_html( $term->name ); ?></label>
				<?php endforeach; ?>
			</div><!-- .term-filter -->
			<?php endif ?>

		</form><!-- .post-loader-form -->

		<?php
	}

	/**
	 * Result
	 */
	public function result( &$query = null )
	{
		/**
		 * Post data
		 */

		$paged = isset( $_POST['paged'] ) ? $_POST['paged'] : 1;
		$terms = isset( $_POST['terms'] ) && is_array( $_POST['terms'] ) ? $_POST['terms'] : array();

		/**
		 * WP Query
		 */

		// Before filter

		$query_args = array
		(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'paged'       => $paged,
		);

		$pre_filter_query = new \WP_Query( $query_args );

		// Filter

		if ( $terms ) 
		{
			$query_args['tax_query'][] = array
			(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => array_map( 'intval', $terms ),
				'operator' => 'IN',
			);
		}

		// Define $query
		$query = new WP_Query( $query_args );

		/**
		 * Output
		 */

		if ( $query->have_posts() ) 
		{
			/**
			 * Message
			 */

			// Filter active

			if ( $pre_filter_query->query_vars != $query->query_vars ) 
			{
				$posts = _n( 'post', 'posts', $pre_filter_query->found_posts, 'theme-post-loader' );

				$message = sprintf( __( 'Found %d of %d %s.', 'theme-post-loader' ), $query->found_posts, $pre_filter_query->found_posts, $posts );
			}

			// Filter not active

			else
			{
				$posts = _n( 'post', 'posts', $query->found_posts, 'theme-post-loader' );

				$message = sprintf( __( 'Showing %d %s.', 'theme-post-loader' ), $query->found_posts, $posts );
			}

			// Output message
			printf( '<div class="alert alert-info">%s</div>', $message );

			/**
			 * List posts
			 */

			$this->list_posts( $query, array
			(
				'before_posts'  => '<div class="row">',
				'before_post'   => '<div class="col-md-4">',
				'post_template' => 'template-parts/card.php',
				'after_post'    => '</div>',
				'after_posts'   => '</div>',
			));
		}

		else
		{
			// No posts message
			$this->no_posts_message( $query, '<div class="alert alert-warning">', '</div>' );
		}
	}
}

// Register loader
theme_register_post_loader( 'Theme_Sample_Post_Loader' );

