/**
 * Post Loader
 */
(function( $ )
{
	"use strict";

	function Plugin( elem, options )
	{
		this.$elem    = $( elem );
		this.$content = $( this.$elem.data( 'target' ) );
		this.options  = $.extend( {}, Plugin.defaultOptions, options );

		this.$elem.addClass( 'post-loader' );

		var _this = this;

		// Form submit
		this.$elem.on( 'submit', '.post-loader-form', function( event )
		{
			event.preventDefault();

			// Load
			_this.load();
		});

		// Pagination click
		this.$content.on( 'click', '.pagination .page-link', function( event )
		{
			event.preventDefault();

			// Load
			_this.load(
			{
				page : $( this ).data( 'page' ),
				animate : true,
			});
		});

		// Checkbox and radio change.
		this.$elem.on( 'change', 'input[type="checkbox"], input[type="radio"]', function( event )
		{
			// Toggle label `active` class.

			var $label = $( this ).closest( 'label' );

			if ( $label.length ) 
			{
				if ( $( this ).is( ':checked' ) ) 
				{
					$label.addClass( 'active' );
				}

				else
				{
					$label.removeClass( 'active' );
				}
			}
		});

		// input.autoload change
		this.$elem.on( 'change', ':input.autoload', function( event )
		{
			// Load
			_this.load();
		});

		// Notify init
		$( document ).trigger( 'postLoader.init', [ this ] );
	}

	Plugin.defaultOptions = 
	{
		animationSpeed : 400,
	};

	Plugin.prototype.$elem    = null;
	Plugin.prototype.options  = {};
	Plugin.prototype.response = null;

	Plugin.prototype.load = function( options ) 
	{
		// Options

		var defaults = 
		{
			page    : 1,
			animate : false,
		};

		options = $.extend( {}, defaults, options );

		// Set page
		this.$elem.find( '.post-loader-form :input[name="paged"]' ).val( options.page );

		// Get fields
		var $fields = this.$elem.find( '.post-loader-form :input:not([disabled])' );

		// Ajax
		$.ajax(
		{
			url : theme.ajaxurl,
			method : 'POST',
			data : this.$elem.find( '.post-loader-form' ).serialize(),
			context : this,
			
			beforeSend : function( jqXHR, settings )
			{
				// Set loading
				this.$elem.addClass( 'loading' );
				this.$content.addClass( 'loading' );

				// Disable fields
				$fields.prop( 'disabled', true );

				// Dispatch event
				this.$elem.trigger( 'postLoader.loadBeforeSend', [ this, jqXHR, settings ] );
			},

			success : function( response, textStatus, jqXHR )
			{
				console.log( 'response', response );

				this.response = response;

				// Set content
				this.$content.html( this.response.content );

				// Animate
				if ( options.animate ) 
				{
					// Scroll to content top
					$( [ document.documentElement, document.body ] ).stop().animate(
					{
	        			scrollTop: this.$content.offset().top,

	    			}, this.options.animationSpeed );
				}

				// Dispatch event
				this.$elem.trigger( 'postLoader.loadSuccess', [ this, response, textStatus, jqXHR ] );
			},

			error : function( jqXHR, textStatus, errorThrown )
			{
				console.warn( 'error', errorThrown );

				// Dispatch event
				this.$elem.trigger( 'postLoader.loadError', [ this, jqXHR, textStatus, errorThrown ] );
			},

			complete : function( jqXHR, textStatus )
			{
				// Unset loading
				this.$elem.removeClass( 'loading' );
				this.$content.removeClass( 'loading' );

				// Enable fields
				$fields.prop( 'disabled', false );

				// Dispatch event
				this.$elem.trigger( 'postLoader.loadComplete', [ this, jqXHR, textStatus ] );
			}
		});
	};

	/**
	 * jQuery Plugin
	 */
	$.fn.postLoader = function( options )
	{
		// Loop elements
		return this.each( function()
		{
			// Check if already instantiated
			if ( typeof $( this ).data( 'postLoader' ) === 'undefined' ) 
			{
				// Create instance
				var instance = new Plugin( this, options );

				// Attach instance to element
				$( this ).data( 'postLoader', instance );
			}
		});
	}

	// Assign to global scope
	window.postLoader = Plugin;

})( jQuery );

(function()
{
	jQuery( document ).on( 'ready', function()
	{
		jQuery( '.post-loader' ).postLoader();
	});
})();
