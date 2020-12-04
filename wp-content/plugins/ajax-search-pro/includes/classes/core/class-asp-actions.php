<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists("WD_ASP_Actions") ) {
    /**
     * Class WD_ASP_Actions
     *
     * Registers the plugin Actions, with the proper handler classes.
     * Handling is passed to the handle() method of the specified class.
     * Handlers defined in /classes/actions/class-asp-{handler}.php
     *
     * @class         WD_ASP_Actions
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Core
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Actions {

        /**
         * Array of internal known actions
         *
         * @var array
         */
        private static $actions = array(
            array(
                "action" => "init",
                "handler" => "Cookies",
                "priority"    => 10,
                "args"  => 0
            ),
            array(
                "action" => "wp_enqueue_scripts",
                "handler" => "StyleSheets",
                "priority"    => 10,
                "args"  => 0
            ),
            // Still add the inline styles to the admin head, bot not the others
            array(
                "action" => "admin_head",
                "handler" => array('StyleSheets', 'inlineCSS'),
                "priority"    => 10,
                "args"  => 0
            ),
            array(
                'action' => 'asp_load_css_js',
                'handler' => array('StyleSheets', 'shouldLoadAssets'),
                'priority'    => 10,
                'args'  => 0
            ),
            array(
                "action" => "wp_head",
                "handler" => "CustomFonts",
                "priority"    => 10,
                "args"  => 0
            ),
            array(
                "action" => "wp_footer",
                "handler" => "FooterStuff",
                "priority"    => 10,
                "args"  => 0
            ),
            array(
                "action" => "init",
                "handler" => "Updates",
                "priority"    => 10,
                "args"  => 0
            ),
            array(
                "action" => "edit_attachment",
                "handler" => array("IndexTable", "update"),
                "priority"    => 999999998,
                "args"  => 3
            ),
            array(
                "action" => "save_post",
                "handler" => array("IndexTable", "update"),
                "priority"    => 999999998,
                "args"  => 3
            ),
            array(
                "action" => "wp_insert_post",
                "handler" => array("IndexTable", "update"),
                "priority"    => 999999999,
                "args"  => 3
            ),
            array(
                "action" => "new_to_publish",
                "handler" => array("OtherActions", "on_save_post"),
                "priority"    => 999999999,
                "args"  => 0
            ),
            array(
                "action" => "draft_to_publish",
                "handler" => array("OtherActions", "on_save_post"),
                "priority"    => 999999999,
                "args"  => 0
            ),
            array(
                "action" => "pending_to_publish",
                "handler" => array("OtherActions", "on_save_post"),
                "priority"    => 999999999,
                "args"  => 0
            ),
            array(
                "action" => "delete_post",
                "handler" => array("IndexTable", "delete"),
                "priority"    => 10,
                "args"  => 1
            ),
            array(
                "action" => "asp_cron_it_extend",
                "handler" => array("IndexTable", "extend"),
                "priority"    => 10,
                "args"  => 0,
                "cron"  => true
            ),
            array(
                "action" => "wpel_before_apply_link",
                "handler" => array("OtherActions", "plug_WPExternalLinks_fix"),
                "priority"    => 9999,
                "args"  => 1
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
        public static function registerAll() {

            foreach (self::$actions as $data) {
                self::register($data['action'], $data['handler'], $data['priority'], $data['args']);

                if ( !empty($data['cron']) )
                    self::registerCron( $data['handler'] );
            }
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
         * Registers an action with the handler class name.
         *
         * @param $action
         * @param $handler string|array
         * @param int $priority
         * @param int $accepted_args
         * @return bool
         */
        public static function register( $action, $handler, $priority = 10, $accepted_args = 0) {

            if ( is_array($handler) ) {
                $class = "WD_ASP_" . $handler[0] . "_Action";
                $handle = $handler[1];
            } else {
                $class = "WD_ASP_" . $handler . "_Action";
                $handle = "handle";
            }

            if ( !class_exists($class) ) return false;

            add_action($action, array(call_user_func(array($class, 'getInstance')), $handle), $priority, $accepted_args);

            self::$registered[] = $action;

            return true;
        }

        public static function registerCron( $handler ) {
            if ( is_array($handler) ) {
                $class = "WD_ASP_" . $handler[0] . "_Action";
                $handle = "cron_".$handler[1];
            } else {
                $class = "WD_ASP_" . $handler . "_Action";
                $handle = "cron_handle";
            }

            $o = call_user_func(array($class, 'getInstance'));
            // Run the handler, that will register the event
            $o->$handle();
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

    }
}