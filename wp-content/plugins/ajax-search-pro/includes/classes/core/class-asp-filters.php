<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists("WD_ASP_Filters") ) {
    /**
     * Class WD_ASP_Filters
     *
     * Registers the plugin Filters, with the proper handler classes.
     * Handling is passed to the handle() method of the specified class.
     * Handlers defined in /classes/filters/class-asp-{handler}.php
     *
     * @class         WD_ASP_Filters
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Core
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Filters {

        /**
         * Array of internal known filters
         *
         * @var array
         */
        private static $filters = array(
            array(
                "filter" => "posts_results",
                "handler" => array("SearchOverride", "override"),
                "priority"    => 999999999,
                "args"  => 2
            ),
            array(
                "filter" => "posts_request",
                "handler" => array("SearchOverride", "maybeCancelWPQuery"),
                "priority"    => 999999999,
                "args"  => 2
            ),
            array(
                "filter" => "page_link",
                "handler" => array("SearchOverride", "fixUrls"),
                "priority"    => 999999999,
                "args"  => 2
            ),
            array(
                "filter" => "post_link",
                "handler" => array("SearchOverride", "fixUrls"),
                "priority"    => 999999999,
                "args"  => 2
            ),
            array(
                "filter" => "post_type_link",
                "handler" => array("SearchOverride", "fixUrls"),
                "priority"    => 999999999,
                "args"  => 2
            ),
            array(
                "filter" => "elementor/query/query_args",
                "handler" => array("Elementor", "posts"),
                "priority" => 999,
                "args"  => 2
            ),
            array(
                "filter" => "elementor/theme/posts_archive/query_posts/query_vars",
                "handler" => array("Elementor", "posts_archive"),
                "priority" => 999,
                "args"  => 1
            ),
            array(
                "filter" => "woocommerce_shortcode_products_query",
                "handler" => array("Elementor", "products"),
                "priority" => 999,
                "args"  => 3
            ),
            /* GENESIS REPLACEMENT FOR MULTISITE */
            array(
                "filter" => "genesis_post_title_output",
                "handler" => array("SearchOverride", "fixUrlsGenesis"),
                "priority"    => 999999999,
                "args"  => 3
            ),
            /* ALLOW SHORTCODE AS MENU TITLE */
            array(
                "filter" => "wp_nav_menu_objects",
                "handler" => array("EtcFixes", "allowShortcodeInMenus"),
                "priority"    => 10,
                "args"  => 1
            ),
            array(
                "filter" => "asp_theme_search_form",
                "handler" => "FormOverride",
                "priority"    => 999999999,
                "args"  => 1
            ),
            array(
                "filter" => "get_search_form",
                "handler" => "FormOverride",
                "priority"    => 999999999,
                "args"  => 1
            ),
            array(
                "filter" => "get_product_search_form",
                "handler" => "WooFormOverride",
                "priority"    => 999999999,
                "args"  => 1
            ),
            array(
                "filter" => "asp_results",
                "handler" => array("EtcFixes", "plug_DownloadMonitorLink"),
                "priority"    => 999999999,
                "args"  => 1
            ),
            array(
                "filter" => "asp_fontend_get_taxonomy_terms",
                "handler" => array("EtcFixes", "fixPostFormatStandard"),
                "priority"    => 999,
                "args"  => 4
            ),
            array(
                "filter" => "asp_query_args",
                "handler" => array("EtcFixes", "fixPostFormatStandardArgs"),
                "priority"    => 999,
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

            foreach (self::$filters as $data)
                self::register($data['filter'], $data['handler'], $data['priority'], $data['args']);
        }

        /**
         * Get all the queued handlers
         *
         * @return array
         */
        public static function getAll( ) {
            return array_keys(self::$filters);
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
         * @param $filter
         * @param $handler string|array
         * @param int $priority
         * @param int $accepted_args
         * @return bool
         */
        public static function register( $filter, $handler, $priority = 10, $accepted_args = 0) {

            if ( is_array($handler) ) {
                $class = "WD_ASP_" . $handler[0] . "_Filter";
                $handle = $handler[1];
            } else {
                $class = "WD_ASP_" . $handler . "_Filter";
                $handle = "handle";
            }

            if ( !class_exists($class) ) return false;

            add_filter($filter, array(call_user_func(array($class, 'getInstance')), $handle), $priority, $accepted_args);

            self::$registered[] = $filter;

            return true;
        }

        /**
         * Deregisters an action handler.
         *
         * @param $filter
         * @param $handler
         */
        public static function deregister( $filter, $handler ) {

            remove_filter($filter, array(call_user_func(array($handler, 'getInstance')), 'handle'));

        }

    }
}