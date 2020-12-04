/* global jQuery, YoastSEO, wp, YoastACFAnalysis: true */
/* exported YoastACFAnalysis */

var App = require( "./app.js" );

wp.domReady( function() {
	if ( "undefined" !== typeof YoastSEO ) {
		YoastACFAnalysis = new App();
	}
} );
