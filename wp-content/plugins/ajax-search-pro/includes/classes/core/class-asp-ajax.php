<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists("WD_ASP_Ajax") ) {
    /**
     * Class WD_ASP_Ajax
     *
     * Registers the Ajax Handlers, with the proper handler classes.
     * Handling is passed to the handle() method of the specified class.
     * Handlers defined in /classes/ajax/class-asp-{handler}.php
     *
     * @class         WD_ASP_Ajax
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Core
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Ajax {

        /**
         * Array of internal ajax actions
         *
         * @var array
         */
        private static $actions = array(
            "ajaxsearchpro_search" => array(
                "handler" => "Search",
                "priv"    => true,
                "nopriv"  => true
            ),
            "ajaxsearchpro_autocomplete" => array(
                "handler" => "Autocomplete",
                "priv"    => true,
                "nopriv"  => true
            ),
            "ajaxsearchpro_addkeyword" => array(
                "handler" => "AddKeyword",
                "priv"    => true,
                "nopriv"  => true
            ),
            "ajaxsearchpro_preview" => array(
                "handler" => "Preview",
                "priv"    => true,
                "nopriv"  => false
            ),
            "ajaxsearchpro_precache" => array(
                "handler" => "Precache",
                "priv"    => true,
                "nopriv"  => false
            ),
            "ajaxsearchpro_priorities" => array(
                "handler" => "Priorities",
                "priv"    => true,
                "nopriv"  => false
            ),
            "ajaxsearchpro_deletecache" => array(
                "handler" => "DeleteCache",
                "priv"    => true,
                "nopriv"  => false
            ),
            "ajaxsearchpro_deletekeyword" => array(
                "handler" => "DeleteKeyword",
                "priv"    => true,
                "nopriv"  => false
            ),
            "asp_indextable_admin_ajax" => array(
                "handler" => "IndexTable",
                "priv"    => true,
                "nopriv"  => false
            ),
            "asp_indextable_optimize" => array(
                "handler" => "IndexTable",
                "priv"    => true,
                "nopriv"  => false
            ),
            "asp_indextable_get_stats" => array(
                "handler" => "IndexTable",
                "priv"    => true,
                "nopriv"  => false
            ),
            'asp_maintenance_admin_ajax' => array(
                "handler" => 'Maintenance',
                "priv"    => true,
                "nopriv"  => false
            ),
            "asp_license_ajax" => array(
                "handler" => "License",
                "priv"    => true,
                "nopriv"  => false
            ),
            "asp_syn_admin_ajax" => array(
                "handler" => "Synonyms",
                "priv"    => true,
                "nopriv"  => false
            )
        );

        /**
         * Array of already registered handlers
         *
         * @var array
         */
        private static $registered = array();

        /**
         * Registers all the handlers from the $actions variable
         */
        public static function registerAll( $custom_ajax = false) {

            foreach (self::$actions as $action => $data)
                self::register($action, $data['handler'], $data['priv'], $data['nopriv'], $custom_ajax);

        }

        /**
         * Get all the queued handlers
         *
         * @return array
         */
        public static function getAll( ) {
            return array_keys(self::$actions);
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
         * Checks if currently a Search Plugin ajax is in progress
         *
         * @param string $handle
         * @return bool
         */
        public static function doingAjax( $handle = "" ) {
            if (!empty($_POST['action'])) {
                if ($handle != "")
                    return $_POST['action'] == $handle;
                return in_array($_POST['action'], WD_ASP_Ajax::getAll());
            }
            return false;
        }

        /**
         * Registers an action with the handler class name.
         *
         * @param $action
         * @param $handler
         * @param bool $priv
         * @param bool $nopriv
         * @return bool
         */
        public static function register( $action, $handler, $priv = true, $nopriv = true, $custom_ajax = false) {

            if ( !$priv && !$nopriv) return false;

            $class = "WD_ASP_" . $handler . "_Handler";
            $prefix = $custom_ajax == true ? "ASP_" : "wp_ajax_";

            if ( !class_exists($class) ) return false;

            if ( $priv )
                add_action($prefix . $action, array(call_user_func(array($class, 'getInstance')), 'handle'));

            if ( $nopriv )
                add_action($prefix . 'nopriv_' . $action, array(call_user_func(array($class, 'getInstance')), 'handle'));

            self::$registered[] = $action;

            return true;
        }

        /**
         * Deregisters an action handler.
         *
         * @param $action
         * @param $handler
         */
        public static function deregister( $action, $handler ) {

            remove_action($action, array(call_user_func(array($handler, 'getInstance')), 'handle'));

        }

        /**
         * Adds an action to the register queue
         *
         * @param $action
         * @param $handler
         * @param bool $priv
         * @param bool $nopriv
         */
        public static function queue($action, $handler, $priv = true, $nopriv = true) {

            self::$actions[$action] = array(
                "handler" => $handler,
                "priv"    => $priv,
                "nopriv"  => $nopriv
            );

        }

        /**
         * Dequeues an action from the register queue.
         *
         * @param $action
         */
        public static function dequeue($action) {

            if ( isset(self::$actions[$action]) )
                unset(self::$actions[$action]);

        }

    }
}