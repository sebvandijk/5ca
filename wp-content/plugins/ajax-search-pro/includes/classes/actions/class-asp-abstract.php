<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Action_Abstract")) {
    /**
     * Class WD_MS_Action_Abstract
     *
     * An abstract for mainly ajax handler classes in Ajax Search Pro plugin.
     *
     * @class         WD_ASP_Action_Abstract
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    abstract class WD_ASP_Action_Abstract {

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