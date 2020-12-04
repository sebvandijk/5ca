<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Synonyms_Handler")) {
    /**
     * Class WD_MS_Synonyms_Handler
     *
     * Synonyms ajax request handler
     *
     * @class         WD_ASP_Synonyms_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Synonyms_Handler extends WD_ASP_Handler_Abstract {

        /**
         * This function is bound as the handler
         */
        function handle() {
            require_once(ASP_CLASSES_PATH . 'etc/synonyms.class.php');

            if ( !isset($_POST['op']) ) {
                print -1;
                die();
            } else if ($_POST['op'] == 'find' || $_POST['op'] == 'findexact') {
                $this->find();
            } else if ($_POST['op'] == 'update') {
                $this->update();
            } else if ($_POST['op'] == 'delete') {
                $this->delete();
            } else if ($_POST['op'] == 'wipe') {
                $this->wipe();
            } else if ($_POST['op'] == 'export') {
                $this->export();
            } else if ($_POST['op'] == 'import') {
                $this->import();
            }
        }

        private function find() {
            $syn = ASP_Synonyms::getInstance();
            $exact = $_POST['op'] == 'findexact';
            $limit = $exact == true ? 1 : 30;
            if ( isset($_POST['keyword'], $_POST['lang']) )
                $ret = $syn->find($_POST['keyword'], $_POST['lang'], $limit, $exact);
            else
                $ret = -1;
            print '!!!ASP_SYN_START!!!';
			ASP_Helpers::prepareAjaxHeaders();
            print_r(json_encode($ret));
            print '!!!ASP_SYN_END!!!';
            die();
        }

        private function update() {
            $syn = ASP_Synonyms::getInstance();
            if ( isset($_POST['keyword'], $_POST['synonyms'], $_POST['lang'], $_POST['overwrite_existing']) )
                $ret = $syn->update($_POST['keyword'], $_POST['synonyms'], $_POST['lang'], $_POST['overwrite_existing']);
            else
                $ret = -1;
			ASP_Helpers::prepareAjaxHeaders();
            print
                '!!!ASP_SYN_START!!!'.
                $ret.
                '!!!ASP_SYN_END!!!';
            die();
        }

        private function delete() {
            $syn = ASP_Synonyms::getInstance();
            if ( isset($_POST['id']) )
                $ret = $syn->deleteByID($_POST['id']);
            else
                $ret = -1;
			ASP_Helpers::prepareAjaxHeaders();
            print
                '!!!ASP_SYN_START!!!'.
                $ret.
                '!!!ASP_SYN_END!!!';
            die();
        }

        private function wipe() {
            $syn = ASP_Synonyms::getInstance();
            $syn->wipe();
			ASP_Helpers::prepareAjaxHeaders();
            print
                '!!!ASP_SYN_START!!!0!!!ASP_SYN_END!!!';
            die();
        }

        private function export() {
            $syn = ASP_Synonyms::getInstance();
			ASP_Helpers::prepareAjaxHeaders();
            print
                '!!!ASP_SYN_START!!!'.$syn->export().'!!!ASP_SYN_END!!!';
            die();
        }

        private function import() {
            $syn = ASP_Synonyms::getInstance();
            if ( !isset($_POST['path']) )
                $ret = -1;
            else
                $ret = $syn->import($_POST['path']);
			ASP_Helpers::prepareAjaxHeaders();
            print
                '!!!ASP_SYN_START!!!'.$ret.'!!!ASP_SYN_END!!!';
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