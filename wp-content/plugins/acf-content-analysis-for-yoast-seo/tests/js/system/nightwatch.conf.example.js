module.exports = (function(settings) {
	settings.test_settings.default.launch_url = "{{DOMAIN}}";
	return settings;
})(require('./nightwatch.json'));
