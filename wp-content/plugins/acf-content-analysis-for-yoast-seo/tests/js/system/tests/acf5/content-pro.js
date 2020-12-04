var assert = require('assert');
var logContains = require('../../helpers/logContains');
var dummyContent = require('../../helpers/dummyContent');

module.exports = {
    tags: ['acf5', 'pro', 'content'],

    before: function (browser) {

    },

    beforeEach: function (browser) {
        var page = browser.page.WordPressHelper();
        page.newPost();
    },

    'Gallery Field': function (browser) {

        var hashTitle = dummyContent.hash();
        var hashAlt = dummyContent.hash();

        //Open Modal
        browser.waitForElementVisible('.acf-field-gallery .acf-gallery-add', 10000);
        browser.click(".acf-field-gallery .acf-gallery-add");

        // Open Media Library
        browser.page.WordPressHelper().openMediaLibraryTab();

        // Select Attachment
        browser.waitForElementVisible('.media-modal .attachment', 10000);
        browser.click(".media-modal .attachment");

        // Update Title
        browser.waitForElementVisible('.setting[data-setting="title"] input', 1000);
        browser
            .clearValue( '.setting[data-setting="title"] input')
            .setValue( '.setting[data-setting="title"] input', [ hashTitle , browser.Keys.TAB ] );


        browser.waitForElementNotPresent( '.attachment-details.save-waiting', 10000 );

        // Update Alt
        browser.waitForElementVisible('.setting[data-setting="alt"] input', 1000);
        browser
            .clearValue( '.setting[data-setting="alt"] input')
            .setValue( '.setting[data-setting="alt"] input', [ hashAlt , browser.Keys.TAB ] );

        browser.waitForElementNotPresent( '.attachment-details.save-waiting', 10000 );

        // Insert Attachment (closes Modal)
        browser.click(".media-modal .media-toolbar-primary .media-button-select");

        browser.pause( 15000 );

        logContains( browser, 'alt=\\"' + hashTitle + '\\" title=\\"' + hashAlt + '\\"', browser.assert.ok );

    },

    'Link Field': function (browser) {

        var hashUrl = 'http://' + dummyContent.hash();
        var hashLinkText = dummyContent.hash();

        browser.element('css selector', '.acf-field-link .button', function(res) {
            if( 0 === res.status ){
                //Open Modal
                browser.click('.acf-field-link .button');
            }
        });

        // Update Url
        browser.waitForElementVisible('#wp-link-url', 1000);
        browser
            .clearValue( '#wp-link-url')
            .setValue( '#wp-link-url', hashUrl );

        // Update Link Text
        browser.waitForElementVisible('#wp-link-text', 1000);
        browser
            .clearValue( '#wp-link-text')
            .setValue( '#wp-link-text', hashLinkText );

        // Insert Attachment (closes Modal)
        browser.click("#wp-link-submit");

        browser.pause( 15000 );

        logContains( browser, 'href=\\"' + hashUrl + '\\" target=\\"\\">' + hashLinkText, browser.assert.ok );

    },

    after : function(browser) {
        browser.end();
    }
};
