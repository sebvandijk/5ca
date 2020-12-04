<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists("WD_ASP_Shortcodes") ) {
    /**
     * Class WD_ASP_Shortcodes
     *
     * Registers the plugin Shortcodes, with the proper handler classes.
     * Handling is passed to the handle() method of the specified class.
     * Handlers defined in /classes/shortcodes/class-asp-{handler}.php
     *
     * @class         WD_ASP_Shortcodes
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Core
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Shortcodes {

        /**
         * Array of internal known shortcodes
         *
         * @var array
         */
        private static $shortcodes = array(
            "wpdreams_ajaxsearchpro" => "Search",
            "wd_asp" => "SearchBox",
            "wpdreams_ajaxsearchpro_results" => "Results",
            "wpdreams_asp_settings" => "Settings",
            "wpdreams_ajaxsearchpro_two_column" => "TwoColumn"
        );

        /**
         * Array of already registered shortcodes
         *
         * @var array
         */
        private static $registered = array();

        /**
         * Registers all the handlers from the $actions variable
         */
        public static function registerAll() {

            foreach (self::$shortcodes as $shortcode => $handler)
                self::register($shortcode, $handler);

        }

        /**
         * Get all the queued handlers
         *
         * @return array
         */
        public static function getAll( ) {
            return array_keys(self::$shortcodes);
        }

        /**
         * Get all the already registered handlers
         *
         * @return array
         */
        public static function getRegistered() {
            return self::$registered;
        }

        /**
         * Registers a filter with the handler class name.
         *
         * @param $shortcode
         * @param $handler string|array
         * @return bool
         */
        public static function register( $shortcode, $handler ) {

            if ( is_array($handler) ) {
                $class = "WD_ASP_" . $handler[0] . "_Shortcode";
                $handle = $handler[1];
            } else {
                $class = "WD_ASP_" . $handler . "_Shortcode";
                $handle = "handle";
            }

            if ( !class_exists($class) ) return false;

            add_shortcode($shortcode, array(call_user_func(array($class, 'getInstance')), $handle));

            self::$registered[] = $shortcode;

            return true;
        }

        /**
         * Deregisters a shortcode
         *
         * @param $shortcode string
         * @return bool
         */
        public static function deregister( $shortcode ) {

            // Check if it is already registered
            if ( isset(self::$registered[$shortcode]) )
                remove_shortcode( $shortcode );
            else if ( isset(self::$shortcodes[$shortcode]) )
                unset(self::$shortcodes[$shortcode]);

            return true;

        }

    }
}