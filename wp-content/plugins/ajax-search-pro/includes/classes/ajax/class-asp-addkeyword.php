<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_AddKeyword_Handler")) {
    /**
     * Class WD_ASP_AddKeyword_Handler
     *
     * Handles the keyword deleting from the statistics database
     *
     * @class         WD_ASP_AddKeyword_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_AddKeyword_Handler extends WD_ASP_Handler_Abstract {

        /**
         * Keyword Delete handler
         */
        function handle() {
            if ( isset($_POST['id'], $_POST['keyword']) ) {
                echo (asp_statistics::addKeyword($_POST['id'] + 0, $_POST['keyword']) === true) ? 1 : 0;
                exit;
            }
            ASP_Helpers::prepareAjaxHeaders();
            echo 0;
            exit;
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