<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exits when accessed directly.

class Theme_Post_Loader
{
	public $id = null;

	public function __construct( $id )
	{
		$this->id = $id;

		add_action( 'wp_ajax_theme_post_loader_process'       , array( $this, 'process' ) );
		add_action( 'wp_ajax_nopriv_theme_post_loader_process', array( $this, 'process' ) );
	}

	/**
	 * Render
	 */
	public function render()
	{
		$html_id = "{$this->id}-post-loader";

		?>

		<div id="<?php echo esc_attr( $html_id ); ?>" class="post-loader">
			<?php $this->inside(); ?>
		</div>

		<?php
	}

	/**
	 * Inside
	 */
	public function inside()
	{
		if ( has_action( "theme_post_loader_inside/loader={$this->id}" ) ) 
		{
			do_action( "theme_post_loader_inside/loader={$this->id}", $this );

			return;
		}

		$this->form();
		$this->content();
	}

	/**
	 * Settings Fields
	 */
	public function settings_fields()
	{
		wp_nonce_field( 'post_loader', THEME_POST_LOADER_NONCE_NAME );

		?>
			
		<input type="hidden" name="action" value="theme_post_loader_process">
		<input type="hidden" name="loader" value="<?php echo esc_attr( $this->id ); ?>">
		<input type="hidden" name="paged" value="1">

		<?php
	}

	/**
	 * Form
	 */
	public function form()
	{
		// Custom

		if ( has_action( "theme_post_loader_form/loader={$this->id}" ) ) 
		{
			do_action( "theme_post_loader_form/loader={$this->id}", $this );

			return;
		}

		// Built-in

		?>

		<form class="post-loader-form" method="post">

			<?php $this->settings_fields(); ?>

		</form>

		<?php
	}

	/**
	 * Content
	 */
	public function content()
	{
		?>

		<div class="post-loader-content">
			<?php $this->result(); ?>
		</div>

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

		/**
		 * WP Query
		 */

		$query_args = array
		(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'paged'       => $paged,
		);

		$query_args = apply_filters( "theme_post_loader_query_args/loader={$this->id}", $query_args, $this );

		// Define $query
		$query = new \WP_Query( $query_args );

		/**
		 * Output
		 */

		// Custom

		if ( has_action( "theme_post_loader_result/loader={$this->id}" ) ) 
		{
			do_action( "theme_post_loader_result/loader={$this->id}", $query, $this );

			return;
		}

		// Built-in

		if ( $query->have_posts() ) 
		{
			$this->list_posts( $query );
		}

		else
		{
			$this->no_posts_message( $query, '<div class="alert alert-warning">', '</div>' );
		}
	}

	/**
	 * Process
	 */
	public function process()
	{
		// Check if ajax
		if ( ! wp_doing_ajax() ) 
		{
			return;
		}

		// Check referer
		check_ajax_referer( 'post_loader', THEME_POST_LOADER_NONCE_NAME );

		// Check loader
		if ( $this->id != $_POST['loader'] ) 
		{
			return;
		}

		// Get result and WP Query object

		ob_start();

		$this->result( $query );

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

	/**
	 * List Posts
	 */
	public function list_posts( $query, $args = array() )
	{
		$defaults = array
		(
			'before_posts'  => '',
			'before_post'   => '',
			'post_template' => 'template-parts/card.php',
			'after_post'    => '',
			'after_posts'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		// Check posts
		if ( ! $query->have_posts() ) 
		{
			return;
		}

		echo $args['before_posts'];

		// The Loop
		while ( $query->have_posts() ) 
		{
			$query->the_post();

			echo $args['before_post'];

			// Include post template
			locate_template( $args['post_template'], true, false );

			echo $args['after_post'];
		}

		echo $args['after_posts'];

		// Pagination
		$this->pagination( $query );

		// Reset post data
		wp_reset_postdata();
	}

	/**
	 * No Posts Message
	 */
	public function no_posts_message( $query, $before = '', $after = '' )
	{
		// Check posts
		if ( $query->have_posts() ) 
		{
			return;
		}

		/**
		 * Get post type name
		 */

		$post_types = array();

		// Check post type
		if ( $query->get( 'post_type' ) ) 
		{
			// Loop post types
			foreach ( (array) $query->get( 'post_type' ) as $post_type ) 
			{
				// Get object
				$post_type = get_post_type_object( $post_type );

				// Store post type name
				if ( $post_type ) 
				{
					$post_types[ $post_type->name ] = strtolower( $post_type->labels->name );
				}
			}

			$post_types = array_values( $post_types );
		}

		/**
		 * Message
		 */

		// Check post types
		if ( $post_types ) 
		{
			// One post type
			if ( count( $post_types ) == 1 ) 
			{
				$message = sprintf( __( 'No %s found.', 'theme-post-loader' ), $post_types[0] );
			}

			// Multiple post types
			else
			{
				$last = array_pop( $post_types );

				$message = sprintf( __( 'No %s or %s found.', 'theme-post-loader' ), implode( ', ', $post_types ), $last );
			}
		}

		else
		{
			// No post types
			$message = __( 'No items found.', 'theme-post-loader' );
		}

		// Output
		echo $before . $message . $after;
	}


	/**
	 * Pagination
	 */
	public function pagination( $query, $args = array() )
	{
		// Arguments

		$defaults = array
		(
			'mid_size'  => 3,
			'prev_text' => __( 'Previous page', 'theme-post-loader' ),
			'next_text' => __( 'Next page', 'theme-post-loader' ),
		);

		$args = wp_parse_args( $args, $defaults );

		// Check if pagination is needed

		$paged = $query->get( 'paged' );

		if ( ! $paged || $query->max_num_pages == 1 ) 
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

		if ( $size_end > $query->max_num_pages ) 
		{
			$size_end = $query->max_num_pages;
		}

		// Output

		?>

		<nav class="pagination-nav show-if-js">
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
						$content = sprintf( '%d <span class="sr-only">%s</span>', $page, esc_html__( '(current)', 'theme-post-loader' ) );
					}

				?>
				<li class="<?php echo $class; ?>"><a class="page-link" href="#" data-page="<?php echo $page; ?>"><?php echo $content; ?></a></li>
				<?php endfor; ?>
				
				<?php if ( $paged < $query->max_num_pages ) : ?>
				<li class="page-item"><a class="page-link" data-page="<?php echo $paged + 1; ?>" href="#" tabindex="-1"><?php echo $args['next_text']; ?></a></li>
				<?php endif; ?>

			</ul><!-- .pagination -->
		</nav><!-- .pagination-nav -->

		<?php
	}
}
