<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_OtherActions_Action")) {
    /**
     * Class WD_ASP_OtherActions_Action
     *
     * Other stuff
     *
     * @class         WD_ASP_OtherActions_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_OtherActions_Action extends WD_ASP_Action_Abstract {

        public function handle() {}

        public function on_save_post() {
            // Clear all the cache just in case
            $ch = new WD_ASP_Deletecache_Handler();
            $ch->handle(false);
        }

        /**
         * Fix for 'WP External Links' plugin
         * https://wordpress.org/plugins/wp-external-links/
         *
         * @param $link
         */
        public function plug_WPExternalLinks_fix( $link ) {
            // ignore links with class "asp_showmore"
            if ( $link->has_attr_value( 'class', 'asp_showmore' ) ) {
                $link->set_ignore();
            }
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