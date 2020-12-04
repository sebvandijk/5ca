<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Updates_Action")) {
    /**
     * Class WD_ASP_Updates_Action
     *
     * Updates manager action
     *
     * @class         WD_ASP_Updates_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Updates_Action extends WD_ASP_Action_Abstract {

        /**
         * Importing fonts does not work correctly it appears.
         * Instead adding the links directly to the header is the best way to go.
         */
        function handle() {
            new asp_updates_manager(ASP_PLUGIN_NAME, ASP_PLUGIN_SLUG, wd_asp()->updates);
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