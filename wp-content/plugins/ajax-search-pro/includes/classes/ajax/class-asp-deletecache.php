<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Deletecache_Handler")) {
    /**
     * Class WD_MS_Search_Handler
     *
     * Cache delete ajax request handler
     *
     * @class         WD_ASP_Handler_Abstract
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Deletecache_Handler extends WD_ASP_Handler_Abstract {

        /**
         * Deletes the Ajax Search Pro directory
         */
        public function handle( $exit = true ) {
            $count = 0;
            if ( wd_asp()->upload_path !== '' )
                $count = $this->delFiles(wd_asp()->upload_path, '*.wpd');
            if ( wd_asp()->bfi_path !== '' )
                $count = $count + $this->delFiles(wd_asp()->bfi_path);

            // Clear database cache
            $count = $count + wpd_TextCache::clearDBCache();

            if ( $exit !== false ) {
                ASP_Helpers::prepareAjaxHeaders();
                print $count;
                die();
            }
        }

        /**
         * Delete *.wpd files in directory
         *
         * @param $dir string
         * @param $file_arg string
         * @return int files and directories deleted
         */
        private function delFiles($dir, $file_arg = '*.*') {
            $count = 0;
            $files = @glob($dir . $file_arg, GLOB_MARK);
            // Glob can return FALSE on error
            if ( is_array($files) ) {
                foreach ($files as $file) {
                    wpd_del_file($file);
                    $count++;
                }
            }
            return $count;
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