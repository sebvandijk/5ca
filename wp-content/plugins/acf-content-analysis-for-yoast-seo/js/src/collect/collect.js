/* global _ */

var config = require( "./../config/config.js" );
var helper = require( "./../helper.js" );
var scraper_store = require( "./../scraper-store.js" );

var Collect = function() {

};

Collect.prototype.getFieldData = function() {
	var field_data = this.sort( this.filterBroken( this.filterBlacklistName( this.filterBlacklistType( this.getData() ) ) ) );

	var used_types = _.uniq( _.pluck( field_data, "type" ) );

	if ( config.debug ) {
		console.log( "Used types:" );
		console.log( used_types );
	}

	_.each( used_types, function( type ) {
		field_data = scraper_store.getScraper( type ).scrape( field_data );
	} );

	return field_data;
};

Collect.prototype.append = function( data ) {
	if ( config.debug ) {
		console.log( "Recalculate..." + new Date() );
	}

	var field_data = this.getFieldData();

	_.each( field_data, function( field ) {
		if ( typeof field.content !== "undefined" && field.content !== "" ) {
			if ( field.order < 0 ) {
				data = field.content + "\n" + data;
				return;
			}
			data += "\n" + field.content;
		}
	} );

	if ( config.debug ) {
		console.log( "Field data:" );
		console.table( field_data );

		console.log( "Data:" );
		console.log( data );
	}

	return data;
};

Collect.prototype.getData = function() {
	if ( helper.acf_version >= 5 ) {
		return require( "./collect-v5.js" )();
	}
};

Collect.prototype.filterBlacklistType = function( field_data ) {
	return _.filter( field_data, function( field ) {
		return ! _.contains( config.blacklistType, field.type );
	} );
};

Collect.prototype.filterBlacklistName = function( field_data ) {
	return _.filter( field_data, function( field ) {
		return ! _.contains( config.blacklistName, field.name );
	} );
};

Collect.prototype.filterBroken = function( field_data ) {
	return _.filter( field_data, function( field ) {
		return ( "key" in field );
	} );
};

Collect.prototype.sort = function( field_data ) {
	if ( typeof config.fieldOrder === "undefined" || ! config.fieldOrder ) {
		return field_data;
	}

	_.each( field_data, function( field ) {
		field.order = ( typeof config.fieldOrder[ field.key ] === "undefined" ) ? 0 : config.fieldOrder[ field.key ];
	} );

	return field_data.sort( function( a, b ) {
		return a.order > b.order;
	} );
};

module.exports = new Collect();
