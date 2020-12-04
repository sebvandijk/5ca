<?php 
// Disable direct access
defined( 'ABSPATH' ) or die( 'GTFO' );



// phone shortcode
function strl_awesome_phone() {
  return '<i class="fa fa-phone" aria-hidden="true"></i>';
}
add_shortcode('phone', 'strl_awesome_phone');


// enveloppe shortcode
function strl_awesome_enveloppe() {
  return '<i class="fa fa-envelope-o" aria-hidden="true"></i>';
}
add_shortcode('enveloppe', 'strl_awesome_enveloppe');