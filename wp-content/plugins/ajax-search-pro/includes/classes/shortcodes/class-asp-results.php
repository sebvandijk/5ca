<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Results_Shortcode")) {
    /**
     * Class WD_ASP_Results_Shortcode
     *
     * Search bar shortcode
     *
     * @class         WD_ASP_Results_Shortcode
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Shortcodes
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Results_Shortcode extends WD_ASP_Shortcode_Abstract {

        public function handle( $atts ) {
            extract( shortcode_atts( array(
                'id' => '0',
                'element' => 'div',
                'display_on_mobile' => 1
            ), $atts ) );

            $mdetectObj = new WD_MobileDetect();
            if ( $display_on_mobile == 0 && $mdetectObj->isMobile() ) return;

            if ($id == "") return;

			// Disable back-end display on taxonomy list pages
			if ( is_admin() && isset($_GET['taxonomy']) ) return;

            // Visual composer bug, get the first instance ID
            if ($id == 99999) {
                $_instances = wd_asp()->instances->get();
                if ( empty($_instances) )
                    return "";

                $search = reset($_instances);
                $id = $search['id'];
            }

            return "<".$element." id='wpdreams_asp_results_".$id."'></".$element.">";
        }
        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------
        public static function getInstance() {
            if ( ! ( self::$_instance instanceof self ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}