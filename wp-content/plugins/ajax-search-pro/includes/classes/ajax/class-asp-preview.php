<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Preview_Handler")) {
    /**
     * Class WD_MS_Preview_Handler
     *
     * Back-end preview handler
     *
     * @class         WD_ASP_Preview_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Preview_Handler extends WD_ASP_Handler_Abstract {

        /**
         * This function is bound as the handler
         */
        function handle() {
            $o = WD_ASP_Search_Shortcode::getInstance();
            // Needs to be here, as the $o->handle(..) also prints out things :)
            ASP_Helpers::prepareAjaxHeaders();
            $out = $o->handle(array(
                "id" => $_POST['asid']
            ));
            print $out;
            die();
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