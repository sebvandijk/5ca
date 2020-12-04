<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Cookies_Action")) {
    /**
     * Class WD_ASP_Cookies_Action
     *
     * Cookie related stuff
     *
     * @class         WD_ASP_Cookies_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Cookies_Action extends WD_ASP_Action_Abstract {

        public function handle() {
            // Forcefully unset the cookies, if requested
            if ( isset($_GET['asp_unset_cookies']) ) {
                self::forceUnsetCookies();
            }

            /**
             * NOTES
             * ---------------------------------------
             * DO NOT DELETE THE COOKIES HERE, UNLESS A CERTAIN CRITERIA MET!!
             * This filter is executed on ajax requests and redirects and during all
             * kinds of background tasks. Unset only if the criteria is deterministic,
             * and applies on a very specific case. (like the example above)
             */

            // Set cookie if this is search, and asp is active
            if ( isset($_POST['asp_active']) && isset($_GET['s']) ) {
                if (isset($_POST['p_asp_data']) || isset($_POST['np_asp_data'])) {
                    $parse = parse_url(get_bloginfo('url'));
                    $host = $parse['host'];

                    $_p_data = isset($_POST['p_asp_data']) ? $_POST['p_asp_data'] : $_POST['np_asp_data'];
                    $_p_id = isset($_POST['p_asid']) ? $_POST['p_asid'] : $_POST['np_asid'];
                    setcookie('asp_data', $_p_data, time() + (30 * DAY_IN_SECONDS), '/', $host, is_ssl() );
                    setcookie('asp_id', $_p_id, time() + (30 * DAY_IN_SECONDS), '/', $host, is_ssl() );
                    setcookie('asp_phrase', $_GET['s'], time() + (30 * DAY_IN_SECONDS), '/', $host, is_ssl() );
                    $_COOKIE['asp_data'] = $_p_data;
                    $_COOKIE['asp_id'] = $_p_id;
                    $_COOKIE['asp_phrase'] = $_GET['s'];
                }
            }
        }

        public static function forceUnsetCookies() {
            $parse = parse_url(get_bloginfo('url'));
            $host = $parse['host'];
            setcookie('asp_data', '', time() - 7200, '/', $host, is_ssl() );
            setcookie('asp_id', '', time() - 7200, '/', $host, is_ssl() );
            setcookie('asp_phrase', '', time() - 7200, '/', $host, is_ssl() );
            unset($_COOKIE['asp_data']);
            unset($_COOKIE['asp_id']);
            unset($_COOKIE['asp_phrase']);
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