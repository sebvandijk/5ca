<?php
// Disable direct access
defined( 'ABSPATH' ) or die( 'GTFO' );


// Defer jQuery Parsing using the HTML5 defer property, based on http://www.laplacef.com/2014/05/24/how-to-defer-parsing-javascript-in-wordpress/
function strl_optimize_jquery() {
  if (!is_admin()) {
    wp_deregister_script('jquery');
    wp_deregister_script('jquery-migrate.min');
    wp_deregister_script('comment-reply.min');
    $protocol = 'http:';
      if( isset( $_SERVER['HTTPS'] ) ) {
        $protocol = 'https:';
      }
    wp_register_script('jquery', $protocol.'//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js', false, '3.6', true);
    wp_enqueue_script('jquery');
  }
}

if (!(is_admin() )) {
  add_action('template_redirect', 'strl_optimize_jquery');
}

function strl_defer_parsing_of_js ( $url ) {
  if ( FALSE === strpos( $url, '.js' ) ) return $url;
  if ( strpos( $url, 'jquery.js' ) ) return $url;
  return "$url' defer '";
}

if (!(is_admin() )) {
  add_filter( 'clean_url', 'strl_defer_parsing_of_js', 11, 1 );
}