<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Filter_Abstract")) {
    /**
     * Class WD_MS_Filter_Abstract
     *
     * An abstract for filter hook handlers.
     *
     * @class         WD_ASP_Filter_Abstract
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Filters
     * @category      Class
     * @author        Ernest Marcinko
     */
    abstract class WD_ASP_Filter_Abstract {

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
        public function handle() {}
    }
}