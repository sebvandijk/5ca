<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_FormOverride_Filter")) {
    /**
     * Class WD_ASP_FormOverride_Filter
     *
     * Handles the default search form layout override
     *
     * @class         WD_ASP_FormOverride_Filter
     * @version       1.1
     * @package       AjaxSearchPro/Classes/Filters
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_FormOverride_Filter extends WD_ASP_Filter_Abstract {

        public function handle( $form = "" ) {
            $asp_st_override = get_option("asp_st_override", -1);

            if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
                $comp_options = wd_asp()->o['asp_compatibility'];
                if ( $comp_options['old_browser_compatibility'] == 1 ) {
                    return $form;
                }
            }

            if ( $asp_st_override > -1 && wd_asp()->instances->exists( $asp_st_override ) ) {
                $new_form = do_shortcode("[wpdreams_ajaxsearchpro id=".$asp_st_override."]");
                if (strlen($new_form) > 100)
                    return $new_form;
                else
                    return $form;
            }

            return $form;
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