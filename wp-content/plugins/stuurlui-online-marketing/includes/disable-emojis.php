<?php
// Disable direct access
defined( 'ABSPATH' ) or die( 'GTFO' );


// Disable emojis, based on the work by http://wordpress.stackexchange.com/questions/185577/disable-emojicons-introduced-with-wp-4-2
add_action( 'init', 'strl_disable_emojis' );

function strl_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );	
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'tiny_mce_plugins', 'strl_disable_emojis_tinymce' );
}

// Filter function used to remove the tinymce emoji plugin.
function strl_disable_emojis_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}
