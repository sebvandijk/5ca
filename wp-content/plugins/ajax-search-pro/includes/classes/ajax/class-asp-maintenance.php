<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Maintenance_Handler")) {
    /**
     * Class WD_ASP_Maintenance_Handler
     *
     * Maintenance page ajax handler
     *
     * @class         WD_ASP_Maintenance_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Maintenance_Handler extends WD_ASP_Handler_Abstract {

        /**
         * This function handles the index table ajax requests
         */
        public function handle() {
            if (ASP_DEMO) {
                print "Maintenance !!!ASP_MAINT_START!!!";
                print_r(json_encode(array(
                    'status'    => 0,
                    'action'    => '',
                    'msg'       => 'Not allowed in demo mode!'
                )));
                ASP_Helpers::prepareAjaxHeaders();
                print "!!!ASP_MAINT_STOP!!!";
                die();
            }

            $status = 0;
            $msg = 'Missing POST information, please try again!';
            $action = 'none';
            $nonce = false;
            if ( isset($_POST, $_POST['data']) ) {
                if (is_array($_POST['data']))
                    $data = $_POST['data'];
                else
                    parse_str($_POST['data'], $data);
                if ( isset($data['asp_reset_nonce']) ) {
                    $nonce = 'asp_reset_nonce';
                } else if ( isset($data['asp_wipe_nonce']) ) {
                    $nonce = 'asp_wipe_nonce';
                } else if ( isset($data['asp_index_defrag_nonce']) ) {
                    $nonce = 'asp_index_defrag_nonce';
                }
                if (
                    $nonce !== false &&
                    isset($data[$nonce]) &&
                    wp_verify_nonce( $data[$nonce], $nonce )
                ) {
                    if ( $nonce == 'asp_reset_nonce' ) { // Reset
                        wd_asp()->init->pluginReset();
                        $status = 1;
                        $action = 'refresh';
                        $msg = 'The plugin data was successfully reset!';
                    } else if ( $nonce == 'asp_wipe_nonce' ) {  // Wipe
                        wd_asp()->init->pluginWipe();
                        $status = 1;
                        $action = 'redirect';
                        $msg = 'All plugin data was successfully wiped, you will be redirected in 5 seconds!';
                    } else {
                        $it = new asp_indexTable();
                        $it->optimize();
                        $status = 1;
                        $action = 'nothing';
                        $msg = 'Index Table Optimized and Defragmented!';
                    }
                } else {
                    $msg = 'Missing or invalid NONCE, please <strong>reload this page</strong> and try again!';
                }
            }
            $ret = array(
                'status'    => $status,
                'action'    => $action,
                'msg'       => $msg
            );
			ASP_Helpers::prepareAjaxHeaders();
            print "Maintenance !!!ASP_MAINT_START!!!";
            print_r(json_encode($ret));
            print "!!!ASP_MAINT_STOP!!!";
            die();
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