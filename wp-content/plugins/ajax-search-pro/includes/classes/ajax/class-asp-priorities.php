<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Priorities_Handler")) {
    /**
     * Class WD_MS_Priorities_Handler
     *
     * Priority ajax request handler
     *
     * @class         WD_ASP_Priorities_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Priorities_Handler extends WD_ASP_Handler_Abstract {

        /**
         * This function is bound as the handler
         */
        function handle() {

            if ( !empty($_POST['ptask']) ) {
                if ( ASP_DEMO && $_POST['ptask'] == "set" ) {
                    echo "!!PSASPSTART!!0!!PSASPEND!!";
                    die();
                }

                if ( $_POST['ptask'] == "get" )
                    WD_ASP_Priorities::ajax_get_posts();
                else if ( $_POST['ptask'] == "set" )
                    WD_ASP_Priorities::ajax_set_priorities();
            }
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