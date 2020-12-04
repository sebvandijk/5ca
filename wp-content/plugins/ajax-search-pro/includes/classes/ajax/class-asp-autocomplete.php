<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Autocomplete_Handler")) {
    /**
     * Class WD_ASP_Autocomplete_Handler
     *
     * This is the ajax autocomplete handler class
     *
     * @class         WD_ASP_Autocomplete_Handler
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Ajax
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Autocomplete_Handler extends WD_ASP_Handler_Abstract {

        /**
         * Handles autocomplete requests
         */
        public function handle() {

            // DO NOT TRIM! It will give incorrect results :)
            $s = preg_replace('/\s+/', ' ', $_POST['sauto']);

            do_action('asp_before_autocomplete', $s);

            if ( empty($_POST['asid']) ) return "";

            $search = wd_asp()->instances->get( $_POST['asid'] + 0 );

            if ( empty($search['data']) )
                return false;

            $sd = &$search['data'];

            $options = array();
            if ( isset($_POST['options']) ) {
                if (is_array($_POST['options']))
                    $options = $_POST['options'];
                else
                    parse_str($_POST['options'], $options);
            }

            $keyword = '';
            $types = array();

            if (isset($sd['customtypes']) && count($sd['customtypes']) > 0)
                $types = array_merge($types, $sd['customtypes']);

            foreach (w_isset_def($sd['selected-autocomplete_source'], array('google')) as $source) {

                if ( empty($source) )
                    continue;

                $taxonomy = "";
                // Check if this is a taxonomy
                if (strpos($source, 'xtax_') !== false) {
                    $taxonomy = str_replace('xtax_', '', $source);
                    $source = "terms";
                }

                if ( function_exists( 'qtranxf_use' ) && !empty($options['qtranslate_lang']) ) {
                    $lang = $options['qtranslate_lang'];
                } else if ( !empty($options['wpml_lang']) ) {
                    $lang = $options['wpml_lang'];
                } else if ( !empty($options['polylang_lang']) ) {
                    $lang = $options['polylang_lang'];
                } else {
                    $lang = $sd['keywordsuggestionslang'];
                }

                $t = new  wpd_keywordSuggest($source, array(
                    'maxCount' => 10,
                    'maxCharsPerWord' => $sd['autocomplete_length'],
                    'postTypes' => $types,
                    'lang' => $lang,
                    'overrideUrl' => '',
                    'taxonomy' => $taxonomy,
                    'match_start' => true,
                    'api_key' => $sd['autoc_google_places_api'],
                    'search_id' => $_POST['asid'] + 0,
                    'options' => $options
                ));

                $res = $t->getKeywords($s);
                if (isset($res[0]) && $keyword = $res[0])
                    break;
            }

            do_action('asp_after_autocomplete', $s, $keyword);
			ASP_Helpers::prepareAjaxHeaders();;
            print $keyword;
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