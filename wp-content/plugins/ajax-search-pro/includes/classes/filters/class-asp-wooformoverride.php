<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_WooFormOverride_Filter")) {
    /**
     * Class WD_ASP_WooFormOverride_Filter
     *
     * Handles the default search form layout override for WooCommerce
     *
     * @class         WD_ASP_WooFormOverride_Filter
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Filters
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_WooFormOverride_Filter extends WD_ASP_Filter_Abstract {

        public function handle( $form = "" ) {
            $asp_woo_override = get_option("asp_woo_override", -1);

            if ( $asp_woo_override > -1 && wd_asp()->instances->exists( $asp_woo_override ) ) {
                $new_form = do_shortcode("[wpdreams_ajaxsearchpro id=".$asp_woo_override."]");
                if (strlen($new_form) > 100)
                    return $new_form;
                else
                    return $form;
            }

            return $form;
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