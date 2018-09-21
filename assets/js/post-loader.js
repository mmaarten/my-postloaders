(function( $ )
{
	"use strict";

	/**
	 * Construct
	 */
	function Plugin( elem, options )
	{
		this.$elem   = $( elem );
		this.options = $.extend( {}, Plugin.defaultOptions, options );

		var _this = this;

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

		// `.autoload` field change
		this.$elem.on( 'change', 'form :input.autoload', function( event )
		{
			// Load
			_this.load();
		});

		// Pagination item click
		this.$elem.on( 'click', '.pagination .page-link', function( event )
		{
			event.preventDefault();

			// Load
			_this.load( 
			{
				page : $( this ).data( 'page' ),
				animate : true,
			});
		});

		// Form submit
		this.$elem.on( 'submit', 'form', function( event )
		{
			event.preventDefault();

			// Load
			_this.load();
		});

		// Notify initialisation
		this.$elem.trigger( 'postLoader.init', [ this ] );
	}

	Plugin.defaultOptions = 
	{
		animationSpeed : 400,
	};

	Plugin.prototype.$elem   = null;
	Plugin.prototype.options = {};
	Plugin.prototype.data    = {};

	/**
	 * Load
	 */
	Plugin.prototype.load = function( args )
	{
		// Arguments

		var defaults = 
		{
			page    : 1,
			animate : false,
		};

		args = $.extend( {}, defaults, args );

		// Set page
		this.$elem.find( 'form :input[name="paged"]' ).val( args.page );

		// Get fields
		var $fields = this.$elem.find( 'form :input:not([disabled])' );

		// Ajax

		$.ajax(
		{
			url : theme.ajaxurl,
			method : 'POST',
			data : this.$elem.find( 'form' ).serialize(),
			context : this,

			beforeSend : function( jqXHR, settings )
			{
				// Set loading
				this.$elem.addClass( 'loading' );

				// Disable fields
				$fields.prop( 'disabled', true );

				// Dispatch event
				this.$elem.trigger( 'postLoader.loadBeforeSend', [ this, jqXHR, settings ] );
			},

			success : function( response, textStatus, jqXHR )
			{
				console.log( response );

				this.data = response;

				// Set content
				this.$elem.find( '.post-loader-result' ).html( this.data.content );

				// Animation
				if ( args.animate ) 
				{
					// Scroll to result top
					$( [ document.documentElement, document.body ] ).stop().animate(
					{
        				scrollTop: this.$elem.find( '.post-loader-result' ).offset().top,

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
				// Enable fields
				$fields.prop( 'disabled', false );

				// Unset loading
				this.$elem.removeClass( 'loading' );

				// Dispatch event
				this.$elem.trigger( 'postLoader.loadComplete', [ this, jqXHR, textStatus ] );
			}
		})
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

(function( $ )
{
	$( document ).on( 'ready', function()
	{
		$( '.post-loader' ).postLoader();
	});

})( jQuery );
