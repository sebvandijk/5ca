<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Shortcode_Abstract")) {
    /**
     * Class WD_ASP_Shortcode_Abstract
     *
     * An abstract for shortcodes handlers.
     *
     * @class         WD_ASP_Shortcode_Abstract
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Shortcodes
     * @category      Class
     * @author        Ernest Marcinko
     */
    abstract class WD_ASP_Shortcode_Abstract {

        /**
         * Static instance storage
         *
         * @var self
         */
        protected static $_instance;


        /**
         * Get the instance
         *
         * All shortcode classes must be singletons!
         */

        public static function getInstance() {}

        /**
         * This function is called by the appropriate handler
         *
         * @param $atts array|null
         */
        abstract public function handle( $atts );
    }
}