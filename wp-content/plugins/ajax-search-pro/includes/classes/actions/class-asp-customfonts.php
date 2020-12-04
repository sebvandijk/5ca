<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_CustomFonts_Action")) {
    /**
     * Class WD_ASP_CustomFonts_Action
     *
     * Custom fonts used in search instances.
     *
     * @class         WD_ASP_CustomFonts_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_CustomFonts_Action extends WD_ASP_Action_Abstract {

        /**
         * Importing fonts does not work correctly it appears.
         * Instead adding the links directly to the header is the best way to go.
         */
        public function handle( ) {
            $_cf = WD_ASP_Search_Shortcode::getInstance();
            $_cf->fonts();
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