<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_License_Handler")) {
    /**
     * Class WD_MS_License_Handler
     *
     * Back-end preview handler
     *
     * @class         WD_ASP_Preview_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_License_Handler extends WD_ASP_Handler_Abstract {

        /**
         * This function is bound as the handler
         */
        function handle() {

            if ( !isset($_POST['op']) ) die(-2);

            if ( ASP_DEMO ) {
                print_r(json_encode(array("status"=>0, "msg"=>"This functions is disabled on this demo.")));
                die();
            }

            if ( $this->excessiveUsage() ) {
                print_r(json_encode(array("status"=>0, "msg"=>"WP Excessive usage Warning: Please wait a few seconds before the next request.")));
                die();
            }

            if ( $_POST['op'] == "activate" && !empty($_POST['asp_key']) ) {
                $key = $this->preValidateKey( $_POST['asp_key'] );
                if ( $key === false ) {
                    print_r(json_encode(array("status"=>0, "msg"=>"WP: Invalid key specified.")));
                    die();
                }
                $res = WD_ASP_License::activate( $key );
                if ($res === false)
                    print_r(json_encode(array("status"=>0, "msg"=>"WP: Connection error, please try again later.")));
                else
                    print_r(json_encode($res));

                die();
            } else if ($_POST['op'] == "deactivate") {
                $res = WD_ASP_License::deactivate();
                if ($res === false)
                    print_r(json_encode(array("status"=>0, "msg"=>"WP: Connection error, please try again later.")));
                else
                    print_r(json_encode($res));

                die();
            } else if ($_POST['op'] == "deactivate_remote") {
                if ( empty($_POST['site_url']) || empty($_POST['asp_key']) ) {
                    print_r(json_encode(array("status" => 0, "msg" => "Site url or purchase key was not specified.")));
                } else {
                    if ( strpos($_POST['site_url'], "//") === false)
                        $_POST['site_url'] = "//".$_POST['site_url'];
                    $host = parse_url($_POST['site_url'], PHP_URL_HOST);
                    if ( !empty($host) ) {
                        $key = $this->preValidateKey( $_POST['asp_key'] );
                        if ( $key === false ) {
                            print_r(json_encode(array("status"=>0, "msg"=>"WP: Invalid key specified.")));
                            die();
                        }
                        $res = WD_ASP_License::deactivateRemote( $key, $host);
                        if ($res === false)
                            print_r(json_encode(array("status"=>0, "msg"=>"WP: Connection error, please try again later.")));
                        else
                            print_r(json_encode($res));
                    } else {
                        print_r(json_encode(array("status"=>0, "msg"=>"Invalid URL." . $host)));
                    }
                }
                die();
            }
            // We reached here, something is missing..
            print_r(json_encode(array("status"=>0, "msg"=>"WP: Missing information, please check the input fields.")));
            die();
        }

        function preValidateKey( $key ) {
            $key = trim($key);
            if ( strlen($key)!=36 )
                return false;
            return $key;
        }

        function excessiveUsage() {
            $usage = get_option("_asp_update_usage", array());
            $n_usage = array();

            // Leave only recent usages
            foreach ($usage as $u) {
                if ($u > (time() - 60))
                    $n_usage[] = $u;
            }

            if ( count($n_usage) <= 10 ) {
                $n_usage[] = time();
                update_option("_asp_update_usage", $n_usage);
                return false;
            } else {
                return true;
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