<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/*
* Creates a cache file from a text
* 
* @author Ernest Marcinko <ernest.marcinko@wp-dreams.com>
* @version 1.0
* @link http://wp-dreams.com, http://codecanyon.net/user/anago/portfolio
* @copyright Copyright (c) 2012, Ernest Marcinko
*/

if (!class_exists('wpd_TextCache')) {
    class wpd_TextCache {

        private $interval;
        private $cache_name;
        private $cache_path;
        private $last_file_mtime = 0;
        private $last_file_path = "";
        private static $unique_db_prefix = '_aspdbcache_';

        public function __construct($cache_path, $cache_name = "txt", $interval = 36000) {
            $this->cache_name = $cache_name;
            $this->cache_path = $cache_path;
            $this->interval = $interval;
        }

        public function file_path($file) {
            return trailingslashit($this->cache_path) . $this->cache_name . "_" . $file . ".wpd";
        }

        public function getCache($file = "") {
            $file = $this->file_path($file);
            $this->last_file_path = $file;

            if ( wpd_is_file($file) ) {
                $filetime = wpd_mtime($file);
            } else {
                return false;
            }
            if ( $filetime === false || (time() - $filetime) > $this->interval )
                return false;
            $this->last_file_mtime = $filetime;
            return wpd_get_file($file);
        }

        public function getDBCache($handle) {
            $this->last_file_path = $handle;
            $data = get_option(self::$unique_db_prefix . $handle, '');
            if ( isset($data['time'], $data['content']) && (time() - $data['time']) <= $this->interval ) {
                $this->last_file_mtime = $data['time'];
                return $data['content'];
            } else {
                return false;
            }
        }

        public function getLastFileMtime() {
            return $this->last_file_mtime;
        }

        public function setCache($content, $file = "") {
            if ( $file === '' ) {
                $file = $this->last_file_path;
                if ( $file === '' )
                    return false;
            } else {
                $file = $this->file_path($file);
            }


            if ( wpd_is_file($file) ) {
                $filetime = wpd_mtime($file);
            } else {
                $filetime = 0;
            }

            if ( (time() - $filetime) > $this->interval ) {
                wpd_put_file($file, $content);
            }
        }

        public function setDBCache($content, $handle = '') {
            if ( $handle === '' ) {
                $handle = $this->last_file_path;
                if ( $handle === '' )
                    return false;
            }

            $data = get_option(self::$unique_db_prefix . $handle, '');
            if ( isset($data['time']) ) {
                if ( (time() - $data['time']) > $this->interval ) {
                    update_option(self::$unique_db_prefix . $handle, array(
                        'time' => time(),
                        'content' => $content
                    ));
                }
            } else {
                update_option(self::$unique_db_prefix . $handle, array(
                    'time' => time(),
                    'content' => $content
                ));
            }
        }

        public static function clearDBCache() {
            global $wpdb;
            $query = $wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE '%s'", self::$unique_db_prefix . '%');
            $res = $wpdb->query($query);
            if ( !is_wp_error($res) ) {
                return intval($res);
            } else {
                return 0;
            }
        }
    }
}