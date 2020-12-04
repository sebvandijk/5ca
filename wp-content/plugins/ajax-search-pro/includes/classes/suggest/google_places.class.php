<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_google_placesKeywordSuggest')) {
    /**
     * Google places API keyword suggestion class
     *
     * @class       wpd_google-placesKeywordSuggest
     * @version     1.0
     * @package     AjaxSearchPro/Classes
     * @category    Class
     * @author      Ernest Marcinko
     */
    class wpd_google_placesKeywordSuggest extends wpd_keywordSuggestAbstract {
        private $api_key = "";

        public function __construct( $args = array() ) {
            $defaults = array(
                'maxCount' => 10,
                'maxCharsPerWord' => 25,
                'lang' => "en",
                'overrideUrl' => '',
                'match_start' => false,
                'api_key' => ''
            );
            $args = wp_parse_args( $args, $defaults );

            $this->maxCount = $args['maxCount'];
            $this->maxCharsPerWord = $args['maxCharsPerWord'];
            $this->lang = $args['lang'];
            $this->matchStart = $args['match_start'];
            $this->api_key = $args['api_key'];

            if ($args['overrideUrl'] != '') {
                $this->url = $args['overrideUrl'];
            } else {
                $this->url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?types=geocode&language=' . $this->lang . '&key='.$args['api_key'] . "&input=";
            }
        }


        public function getKeywords($q) {
            if ( $this->api_key === "" )
                return array();

            $qf = str_replace(' ', '+', $q);

            $response = wp_remote_get( $this->url . rawurlencode($qf), array( 'timeout' => 1 ) );

            if ( is_wp_error( $response ) || !isset($response['body']) ) {
                return array();
            } else {
                $data = $response['body'];
            }

            if (function_exists('mb_convert_encoding'))
                $data = mb_convert_encoding($data, "UTF-8");
            try {
                $array = json_decode($data, TRUE);
                $res = array();

                foreach ($array['predictions']  as $keyword) {
                    $t = ASP_mb::strtolower($keyword['description']);
                    if (
                        $t != $q &&
                        ('' != $str = wd_substr_at_word($keyword['description'], $this->maxCharsPerWord))
                    ) {
                        if ($this->matchStart && strpos($t, ASP_mb::strtolower($q)) === 0)
                            $res[] = $str;
                        elseif (!$this->matchStart)
                            $res[] = $str;
                    }
                }
                $res = array_slice($res, 0, $this->maxCount);
                if (count($res) > 0)
                    return $res;
                else
                    return array();
            } catch(Exception $e) {
                return array();
            }
        }
    }
}