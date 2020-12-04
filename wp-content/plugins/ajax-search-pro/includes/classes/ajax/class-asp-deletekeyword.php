<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_DeleteKeyword_Handler")) {
    /**
     * Class WD_MS_DeleteKeyword_Handler
     *
     * Handles the keyword deleting from the statistics database
     *
     * @class         WD_ASP_Autocomplete_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_DeleteKeyword_Handler extends WD_ASP_Handler_Abstract {

        /**
         * Keyword Delete handler
         */
        function handle() {
            if (isset($_POST['keywordid'])) {
                echo asp_statistics::deleteKw($_POST['keywordid'] + 0);
                exit;
            }
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