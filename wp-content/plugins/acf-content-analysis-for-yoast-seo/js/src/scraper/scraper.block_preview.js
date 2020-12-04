/* global _ */

var Scraper = function() {};

Scraper.prototype.scrape = function( fields ) {
	fields = _.map( fields, function( field ) {
		if ( field.type !== "block_preview" ) {
			return field;
		}

		field.content = field.$el.html();

		return field;
	} );

	return fields;
};

module.exports = Scraper;
