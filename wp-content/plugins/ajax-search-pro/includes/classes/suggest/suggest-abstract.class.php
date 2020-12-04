<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * A sample and parent class for keyword suggestion and autocomplete
 */

if (!class_exists('wpd_keywordSuggestAbstract')) {
    /**
     * Statistics database keyword suggestions
     *
     * @class       wpd_keywordSuggest
     * @version     1.0
     * @package     AjaxSearchPro/Classes
     * @category    Class
     * @author      Ernest Marcinko
     */
    abstract class wpd_keywordSuggestAbstract {

        protected  $maxCount;

        protected  $maxCharsPerWord;

        /**
         * This should always return an array of keywords or an empty array
         *
         * @param string $q  search keyword
         * @return array() of keywords
         */
        abstract function getKeywords($q);

        protected function can_get_file() {
            if (function_exists('curl_init')){
                return 1;
            } else if (ini_get('allow_url_fopen')==true) {
                return 2;
            }
            return false;
        }

        protected function url_get_contents($Url, $method) {
            if ($method==2) {
                return file_get_contents($Url);
            } else if ($method==1) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $Url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $output = curl_exec($ch);
                curl_close($ch);
                return $output;
            }
        }

        /**
         * Performs a full escape
         *
         * @uses wd_mysql_escape_mimic()
         * @param $string
         * @return array|mixed
         */
        protected function escape( $string ) {
            global $wpdb;

            // recursively go through if it is an array
            if ( is_array($string) ) {
                foreach ($string as $k => $v) {
                    $string[$k] = $this->escape($v);
                }
                return $string;
            }

            if ( is_float( $string ) )
                return $string;

            // Extra escape for 4.0 >=
            if ( method_exists( $wpdb, 'esc_like' ) )
                return esc_sql( $wpdb->esc_like( $string ) );

            // Escape support for WP < 4.0
            if ( function_exists( 'like_escape' ) )
                return esc_sql( like_escape($string) );

            // Okay, what? Not one function is present, use the one we have
            return wd_mysql_escape_mimic($string);
        }

    }
}