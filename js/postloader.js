/**
 * Postloader
 */

(function( $, window, undefined )
{
	"use strict";

	function Postloader( elem, options )
	{
		var _this = this;

		this.$elem   = $( elem );
		this.options = $.extend( {}, Postloader.defaultOptions, options );

		// Form submit

		this.$elem.on( 'submit', '.postloader-form', function( event )
		{
			event.preventDefault();

			_this.load( { page : 1 } );

		});

		// Form reset

		this.$elem.on( 'reset', '.postloader-form', function( event )
		{
			event.preventDefault();

			if ( _this.options.loadOnReset ) 
			{
				setTimeout( function() 
				{ 
					_this.load( { page : 1 } ); 
				}, 50 );
			}
		});

		// Input .autoload change

		this.$elem.on( 'change', ':input.autoload', function( event )
		{
			event.preventDefault();

			_this.load( { page : 1 } );
		});

		// Pagination click

		this.$elem.on( 'click', '.postloader-pagination .page-link[data-page]', function( event )
		{
			event.preventDefault();

			_this.load( { page : $( this ).data( 'page' ), scroll : true } );
		});

		// Notify

		$( document ).trigger( 'postloader.init', [ this ] );
	}

	Postloader.defaultOptions = 
	{
		scrollSpeed  : 250, // Milliseconds
		scrollOffset : -90,
		loadOnReset  : false,
	};

	Postloader.prototype.load = function( options ) 
	{
		// Options

		var defaults = 
		{
			page   : undefined,
			scroll : false,
			ajaxurl : false,
		};

		options = $.extend( {}, defaults, options );

		// Set page

		if ( undefined !== options.page ) 
		{
			this.$elem.find( '.postloader-form :input[name="page"]' ).val( options.page );
		}

		// Load

		$.post( this.options.ajaxurl, this.$elem.find( '.postloader-form' ).serialize(), function( response )
		{
			// Set content
			this.$elem.find( '.postloader-content' ).html( response.content );

			// Scroll
			if ( options.scroll ) 
			{
				$( [ document.documentElement, document.body ] ).animate(
				{
			        scrollTop: this.$elem.find( '.postloader-content' ).offset().top + this.options.scrollOffset

			    }, this.options.scrollSpeed );
			};

			this.$elem.trigger( 'postloader.loadComplete', [ response ] );

		}.bind( this ) );
	};

	$.fn.postloader = function( options )
	{
		return this.each( function()
		{
			if ( undefined === $( this ).data( 'postloader' ) ) 
			{
				$( this ).data( 'postloader', new Postloader( this, options ) );
			}
		});
	};

	window.Postloader = Postloader;

})( jQuery, window );

(function( $ )
{
	document.addEventListener( 'DOMContentLoaded', function()
	{
		$( '.postloader' ).postloader( PostloaderDefaults );
	});

})( jQuery );
