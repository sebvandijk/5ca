<?php
/*
  Plugin Name: SEO Redirection Premium
  Plugin URI: http://www.clogica.com/product/seo-redirection-premium-wordpress-plugin
  Description: Manage all your 301 redirects and monitor 404 errors and more ..
  Version: 3.7
  Author: Fakhri Alsadi
  Author URI: http://www.clogica.com
  Text Domain: wsr
 */
define('ALLOW_UNFILTERED_UPLOADS', true);
define('SR_PLUGIN_NAME', 'SEO Redirection Premium');
define('SR_PLUGINS_URL', plugins_url() . '/seo-redirection-premium/');

require_once "SRP_PLUGIN.php";
require_once "custom/installer.php";
require_once "custom/lib/cf.SR_redirect_cache.class.php";
require_once "custom/lib/cf.SR_database.class.php";
require_once "custom/lib/cf.SR_option_manager.class.php";
require_once "custom/lib/cf.SR_redirect_manager.class.php";
require_once "custom/lib/cf.SR_plugin_menus.class.php";
require_once "custom/lib/cf.SR_test_regex.class.php";
require_once "custom/lib/cf.SR_custom_app.class.php";

function buddy_press_check_locking()
{

  // ensure is_plugin_active() exists (not on frontend)
  if( !function_exists('is_plugin_active') ) {

    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

  }
  if (is_plugin_active('lock-my-bp/bp-lock.php')) {
      $bplock_general_settings = get_option('bplock_general_settings');
      if ($bplock_general_settings && isset($bplock_general_settings) && is_array($bplock_general_settings)) {

          $get_c_id = get_the_ID();
          if (isset($bplock_general_settings['locked_pages'])) {
              if (in_array($get_c_id, $bplock_general_settings['locked_pages']) && !is_user_logged_in()) {
                  return true;
              }
          }
      }
  }
  return false;
}

SRP_PLUGIN::init('wp-seo-redirection-group', __FILE__);

SR_plugin_menus::init();
SR_plugin_menus::hook_menus();

SR_custome_app::hook_scripts();

seo_redirection_installer::set_version("3.3");
seo_redirection_installer::hook_installer();

SR_redirect_manager::hook_redirection();

function SR_multiple_plugin_activate() {
    global $wpdb;
	
// ensure is_plugin_active() exists (not on frontend)
    if( !function_exists('is_plugin_active_for_network') ) {

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    }

		
    if (is_multisite()) {
        if (is_plugin_active_for_network(__FILE__)) {
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
            }
        }
    }
}
register_activation_hook(__FILE__, 'SR_multiple_plugin_activate');



require 'plugin-update-checker/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://www.clogica.com/update/wp-update-server-php7/?action=get_metadata&slug=seo-redirection-premium', //Metadata URL.
	__FILE__, //Full path to the main plugin file.
	'seo-redirection-premium' //Plugin slug. Usually it's the same as the name of the directory.
);


