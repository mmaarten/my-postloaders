/**
 * Post Loader
 */
(function( $ )
{
	"use strict";

	function Plugin( elem, options )
	{
		this.$elem    = $( elem );
		this.options  = $.extend( {}, Plugin.defaultOptions, options );

		this.$elem.addClass( 'post-loader' );

		// Find content element

		if ( typeof this.$elem.data( 'content' ) !== 'undefined' ) 
		{
			this.$content = $( this.$elem.data( 'content' ) );
		}

		else
		{
			this.$content = this.$elem.find( '.post-loader-content' );
		}

		var _this = this;

		// Form submit
		this.$elem.on( 'submit', '.post-loader-form', function( event )
		{
			event.preventDefault();

			// Load
			_this.load();
		});

		// Pagination Click
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

		// .autload change
		this.$elem.on( 'change', ':input.autoload', function( event )
		{
			// Load
			_this.load();
		});

		// Checkbox and radio change
		this.$elem.on( 'change', 'input[type="checkbox"], input[type="radio"]', function( event )
		{
			// Update label active class

			var $label = $( this ).closest( 'label' );

			if ( $( this ).is( ':checked' ) ) 
			{
				$label.addClass( 'active' );
			}

			else
			{
				$label.removeClass( 'active' );
			}
		});

		$( document ).trigger( 'postLoader.init', [ this ] );
	}

	Plugin.defaultOptions = 
	{
		scrollSpeed : 500, // Milliseconds
	};

	Plugin.prototype.$elem = null;
	Plugin.prototype.$content = null;
	Plugin.prototype.options = {};

	Plugin.prototype.load = function( options ) 
	{
		var defaults = 
		{
			page : 1,
			animate : false,
		};

		options = $.extend( {}, defaults, options );

		this.$elem.find( ':input[name="paged"]' ).val( options.page );

		var $fields = this.$elem.find( ':input:not([disabled])' );

		$.ajax(
		{
			url : theme.ajaxurl,
			method : 'POST',
			data : this.$elem.find( '.post-loader-form' ).serialize(),
			context : this,

			beforeSend : function( jqXHR, settings )
			{
				this.$elem.addClass( 'loading' );
				this.$content.addClass( 'loading' );

				$fields.prop( 'disabled', true );

				this.$elem.trigger( 'postLoader.loadBeforeSend', [ jqXHR, settings ] );
			},

			success : function( response, textStatus, jqXHR )
			{
				console.log( 'response', response );

				this.$content.html( response.result );

				// Animation
				if ( options.animate ) 
				{
					// Scroll to content top
					$( [ document.documentElement, document.body ] ).stop().animate(
					{
						scrollTop: this.$content.offset().top

					}, this.options.scrollSpeed );
				}

				this.$elem.trigger( 'postLoader.loadSuccess', [ response, textStatus, jqXHR ] );
			},

			error : function( jqXHR, textStatus, errorThrown )
			{
				this.$elem.trigger( 'postLoader.loadError', [ jqXHR, textStatus, errorThrown ] );
			},

			complete : function( jqXHR, textStatus )
			{
				this.$elem.removeClass( 'loading' );
				this.$content.removeClass( 'loading' );

				$fields.prop( 'disabled', false );

				this.$elem.trigger( 'postLoader.loadComplete', [ jqXHR, textStatus ] );
			},
		})
	};

	$.fn.postLoader = function( options )
	{
		return this.each( function()
		{
			if ( typeof $( this ).data( 'postLoader' ) === 'undefined' ) 
			{
				var instance = new Plugin( this, options );

				$( this ).data( 'postLoader', instance );
			}
		});
	}

})( jQuery );

(function( $ )
{
	$( document ).on( 'ready', function()
	{
		$( '.post-loader' ).postLoader();
	});

})( jQuery );
