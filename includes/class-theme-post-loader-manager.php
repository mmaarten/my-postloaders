<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exits when accessed directly.

class Theme_Post_Loader_Manager
{
	static private $instance = null;

	static public function get_instance()
	{
		if ( ! self::$instance ) 
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	protected $loaders = array();

	public function __construct()
	{
		
	}

	/**
	 * Create Loader
	 */
	public function create_loader( $id, $args = array() )
	{
		$loader = new Theme_Post_Loader( $id, $args );

		$this->register_loader( $loader );

		return $loader;
	}

	/**
	 * Register Loader
	 */
	public function register_loader( $loader )
	{
		if ( ! $loader instanceof Theme_Post_Loader ) 
		{
			$loader = new $loader();
		}

		$this->loaders[ $loader->id ] = $loader;
	}

	/**
	 * Unregister Loader
	 */
	public function unregister_loader( $loader_id )
	{
		unset( $this->loaders[ $loader_id ] );
	}

	/**
	 * Get Loaders
	 */
	public function get_loaders()
	{
		return $this->loaders;
	}

	/**
	 * Get Loader
	 */
	public function get_loader( $loader_id )
	{
		if ( isset( $this->loaders[ $loader_id ] ) ) 
		{
			return $this->loaders[ $loader_id ];
		}

		return null;
	}

	/**
	 * Render Loader
	 */
	public function render_loader( $loader_id )
	{
		$loader = $this->get_loader( $loader_id );

		if ( $loader ) 
		{
			$loader->render();
		}
	}
}
