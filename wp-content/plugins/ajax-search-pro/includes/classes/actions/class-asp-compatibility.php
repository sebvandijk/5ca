<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Compatibility_Action")) {
    /**
     * Class WD_ASP_Compatibility_Action
     *
     * Check for compatibility issues
     *
     * @class         WD_ASP_Compatibility_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Compatibility_Action extends WD_ASP_Action_Abstract {

        public function handle() {
            $_comp = wpdreamsCompatibility::Instance();
            $_comp->check_dir_w(
                wd_asp()->upload_path,
                "Images may not show in results, the caching of results may not work."
            );
            $_comp->can_open_url("Images may not show in results.");
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