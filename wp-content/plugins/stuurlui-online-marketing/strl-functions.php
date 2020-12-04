<?php
/**
* Plugin Name: Stuurlui Online Marketing
* Plugin URI: https://stuurlui.nl
* Description: Custom functionaliteiten door Stuurlui Online Marketing
* Version: 1.7
* Author: Stuurlui Online Marketing
* Author URI: https://stuurlui.nl
**/

// Disable direct access
defined( 'ABSPATH' ) or die( 'GTFO' );



// adds Settings link in plugin overview page
add_filter( 'plugin_action_links', 'strl_settings_link', 10, 5 );
function strl_settings_link( $actions, $plugin_file ) {
  static $plugin;
  if (!isset($plugin))
    $plugin = plugin_basename(__FILE__);
  if ($plugin == $plugin_file) {

      $settings = array('settings' => '<a href="admin.php?page=stuurlui_plugin">' . __('Settings', 'General') . '</a>');
      $site_link = array('support' => '<a href="https://stuurlui.nl/contact" target="_blank">Support</a>');
      $actions = array_merge($settings, $actions);
      //$actions = array_merge($site_link, $actions);
  }
  return $actions;
}



// Get the STRL settings from the settings page
$strl_options = get_option( 'strl_settings' );
$strl_plugin_path = plugin_dir_url( __FILE__ );

// Declare variables based on database value
$strl_checkbox_field_gtm_developer = (isset($strl_options['strl_checkbox_field_gtm_developer']) ? $strl_options['strl_checkbox_field_gtm_developer'] : null);
$strl_checkbox_field_fontawesome = (isset($strl_options['strl_checkbox_field_fontawesome']) ? $strl_options['strl_checkbox_field_fontawesome'] : null);
$strl_checkbox_field_materialicons = (isset($strl_options['strl_checkbox_field_materialicons']) ? $strl_options['strl_checkbox_field_materialicons'] : null);
$strl_checkbox_field_fontawesome_shortcodes = (isset($strl_options['strl_checkbox_field_fontawesome_shortcodes']) ? $strl_options['strl_checkbox_field_fontawesome_shortcodes'] : null);
$strl_checkbox_field_more_google_fonts = (isset($strl_options['strl_checkbox_field_more_google_fonts']) ? $strl_options['strl_checkbox_field_more_google_fonts'] : null);
$strl_checkbox_field_change_login = (isset($strl_options['strl_checkbox_field_change_login']) ? $strl_options['strl_checkbox_field_change_login'] : null);
$strl_checkbox_field_gravity_message_html = (isset($strl_options['strl_checkbox_field_wrap_gravity_message_html']) ? $strl_options['strl_checkbox_field_wrap_gravity_message_html'] : null);
$strl_checkbox_field_disable_emojis = (isset($strl_options['strl_checkbox_field_disable_emojis']) ? $strl_options['strl_checkbox_field_disable_emojis'] : null);
$strl_checkbox_field_disable_attachement_comments = (isset($strl_options['strl_checkbox_field_disable_attachement_comments']) ? $strl_options['strl_checkbox_field_disable_attachement_comments'] : null);
$strl_checkbox_field_defer_javascript = (isset($strl_options['strl_checkbox_field_defer_javascript']) ? $strl_options['strl_checkbox_field_defer_javascript'] : null);
$strl_checkbox_field_pinch_zoom = (isset($strl_options['strl_checkbox_field_pinch_zoom']) ? $strl_options['strl_checkbox_field_pinch_zoom'] : null);
$strl_checkbox_field_reenable_text_widget = (isset($strl_options['strl_checkbox_field_reenable_text_widget']) ? $strl_options['strl_checkbox_field_reenable_text_widget'] : null);
$strl_checkbox_field_undisable_auto_updates = (isset($strl_options['strl_checkbox_field_undisable_auto_updates']) ? $strl_options['strl_checkbox_field_undisable_auto_updates'] : null);



// Options page in menu, courtesy of http://wpsettingsapi.jeroensormani.com/
require 'options-page.php';


// Add installation & activation functions for required and recommended plugins, courtesy of http://tgmpluginactivation.com/
// Only for admins
add_action( 'plugins_loaded', 'strl_admin_settings_page' );
function strl_admin_settings_page(){
  if (current_user_can( 'manage_options' )) {
    require 'includes/plugin-activation.php';
  }
}



// Adds GTM for error tracking
function strl_gtm_script() {
    echo '<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\'//www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);})(window,document,\'script\',\'dataLayer\',\'GTM-NGSPLM\');</script>';
  }
function strl_gtm_noscript() {
    echo '<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-NGSPLM" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
}
if ($strl_checkbox_field_gtm_developer == 2) {
  // Add hook for <head></head>
  add_action('admin_head', 'strl_gtm_script');
  add_action('wp_head', 'strl_gtm_script');
  // Add hook for front-end footer
  add_action('wp_footer', 'strl_gtm_noscript');
}



// Add latest fontawesome in header, based on value in database
add_action( 'wp_enqueue_scripts', 'strl_add_header_scripts' );
function strl_add_header_scripts() {
    global $strl_checkbox_field_fontawesome;
    global $strl_checkbox_field_materialicons;
    global $strl_plugin_path;
    if ($strl_checkbox_field_fontawesome == 1) {
      wp_enqueue_style( 'fontawesome', $strl_plugin_path .'includes/font-awesome/css/font-awesome.min.css', array(), null, 'all');
    }
    if ($strl_checkbox_field_materialicons == 1) {
      wp_enqueue_style( 'material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), null, 'all');
    }
}



// Add shortcodes for phone and enveloppe
if ($strl_checkbox_field_fontawesome_shortcodes == 1) {
  require 'includes/fontawesome-shortcodes.php';
}



// Change login page style, based on value in database
if ($strl_checkbox_field_change_login == 1) {
  require 'includes/change-login.php';
}



// Wrap Gravity Forms notifications in html, based on value in database
if ($strl_checkbox_field_gravity_message_html == 1) {
  add_filter( 'gform_pre_send_email', function ( $email, $message_format ) {
      if ( $message_format != 'html' ) {
          return $email;
      }
      $email['message'] = '<html>' . $email['message'] . '</html>';
      return $email;
  }, 10, 2 );
}



// Disable disable emojis
if ($strl_checkbox_field_disable_emojis == 1) {
  require 'includes/disable-emojis.php';
}



// Disable attachement comments
if ($strl_checkbox_field_disable_attachement_comments == 1) {
  require 'includes/disable-attachement-comments.php';
}



// Defer jQuery Parsing
if ($strl_checkbox_field_defer_javascript == 1) {
  require 'includes/defer-jquery-parsing.php';
}




// Enable Pinch and Zoom in Iphone / Ipad
if ($strl_checkbox_field_pinch_zoom == 1) {
  add_action( 'init', 'woo_remove_responsive_design', 10 );

  function strl_load_responsive_meta_tags () {
    $html = '';

    $html .= "\n" . '<!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->' . "\n";
    $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />' . "\n";

    /* Remove this if not responsive design */
    $html .= "\n" . '<!--  Mobile viewport scale | Disable user zooming as the layout is optimised -->' . "\n";
    $html .= '<meta content="initial-scale=1.0; maximum-scale=4.0; user-scalable=yes" name="viewport"/>' . "\n";

    echo $html;
  } // End woo_load_responsive_meta_tags()
  add_action( 'wp_head', 'strl_load_responsive_meta_tags', 1 );
}



// Add styling to backend
add_action('admin_head', 'strl_admin_custom_styling');
function strl_admin_custom_styling() {
  echo '<style>
    #update-nag, .update-nag {
      display: block!important;
    }
    #tgmpa-plugins .tablenav.bottom small {
      display:none;
    }
    #toplevel_page_stuurlui_plugin img {width: 16px;}
  </style>';
}



// Automatically remove Generator info for Wordpress
function strl_remove_version() {
return '';
}
add_filter('the_generator', 'strl_remove_version');



// Unregister Text widget from Siteorigin Page Builder
if ($strl_checkbox_field_reenable_text_widget != 1) {
  function strl_remove_widgets($widgets){
      unset($widgets['WP_Widget_Text']);
      return $widgets;
  }
  add_filter('siteorigin_panels_widgets', 'strl_remove_widgets');
}


// disable auto updates completely
if ($strl_checkbox_field_undisable_auto_updates != 1) {
  add_filter( 'automatic_updater_disabled', '__return_true' );
  add_filter( 'gform_disable_auto_update', '__return_true' );
}



// Disable Gutenberg
remove_action( 'try_gutenberg_panel', 'wp_try_gutenberg_panel' );


// Disable 'Welcome to Wordpress'
remove_action('welcome_panel', 'wp_welcome_panel');