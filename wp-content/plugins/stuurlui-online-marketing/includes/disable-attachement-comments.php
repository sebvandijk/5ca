<?php 
// Disable direct access
defined( 'ABSPATH' ) or die( 'GTFO' );


// Disable attachement comments, based on http://www.wpbeginner.com/wp-tutorials/how-to-disable-comments-on-wordpress-media-attachments/
function strl_disable_attachment_comments( $open, $post_id ) {
	$post = get_post( $post_id );
	if( $post->post_type == 'attachment' ) {
		return false;
	}
	return $open;
}
add_filter( 'comments_open', 'strl_disable_attachment_comments', 10 , 2 );
