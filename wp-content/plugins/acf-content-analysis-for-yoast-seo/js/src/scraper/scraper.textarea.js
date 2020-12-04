/* global _ */

var Scraper = function() {};

Scraper.prototype.scrape = function( fields ) {
	fields = _.map( fields, function( field ) {
		if ( field.type !== "textarea" ) {
			return field;
		}

		field.content = "<p>" + field.$el.find( "textarea[id^=acf]" ).val() + "</p>";

		return field;
	} );

	return fields;
};

module.exports = Scraper;
