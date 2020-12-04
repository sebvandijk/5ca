<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Handler_Abstract")) {
    /**
     * Class WD_MS_Handler_Abstract
     *
     * An abstract for mainly ajax handler classes in Morphing Search plugin.
     *
     * @class         WD_ASP_Handler_Abstract
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    abstract class WD_ASP_Handler_Abstract {

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
         * The handler
         *
         * This function is called by the appropriate handler
         */
        abstract public function handle();
    }
}