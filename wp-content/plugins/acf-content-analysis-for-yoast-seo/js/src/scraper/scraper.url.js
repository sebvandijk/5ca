/* global _ */

var Scraper = function() {};

Scraper.prototype.scrape = function( fields ) {
	fields = _.map( fields, function( field ) {
		if ( field.type !== "url" ) {
			return field;
		}

		var content = field.$el.find( "input[type=url][id^=acf]" ).val();

		field.content = content ? '<a href="' + content + '">' + content + "</a>" : "";

		return field;
	} );

	return fields;
};

module.exports = Scraper;
