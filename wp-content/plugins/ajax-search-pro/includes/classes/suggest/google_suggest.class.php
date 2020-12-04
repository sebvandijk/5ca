<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_googleKeywordSuggest')) {
    /**
     * Google keyword suggestion class
     *
     * @class       wpd_googleKeywordSuggest
     * @version     1.2
     * @package     AjaxSearchPro/Classes
     * @category    Class
     * @author      Ernest Marcinko
     */
    class wpd_googleKeywordSuggest extends wpd_keywordSuggestAbstract {
        function __construct( $args = array() ) {
	        $defaults = array(
		        'maxCount' => 10,
		        'maxCharsPerWord' => 25,
		        'lang' => "en",
		        'overrideUrl' => '',
		        'match_start' => false
	        );
	        $args = wp_parse_args( $args, $defaults );

            $this->maxCount = $args['maxCount'];
            $this->maxCharsPerWord = $args['maxCharsPerWord'];
            $this->lang = $args['lang'];
	        $this->matchStart = $args['match_start'];

            if ($args['overrideUrl'] != '') {
                $this->url = $args['overrideUrl'];
            } else {
                $this->url = 'http://suggestqueries.google.com/complete/search?output=toolbar&oe=utf-8&client=toolbar&hl=' . $this->lang . '&q=';
            }
        }


        public function getKeywords($q) {
            $qf = str_replace(' ', '+', $q);
            $method = $this->can_get_file();
            if ($method == false) {
                return array('Error: The fopen url wrapper is not enabled on your server!');
            }
            $_content = $this->url_get_contents($this->url . rawurlencode($qf), $method);
            if ($_content == null || $_content == "") return array();
            if (function_exists('mb_convert_encoding'))
                $_content = mb_convert_encoding($_content, "UTF-8");
            try {
                $xml = simplexml_load_string($_content);
                if ($xml == null || $xml === '') return array();
                $json = json_encode($xml);
                $array = json_decode($json, TRUE);
                $res = array();
                $keywords = array();
                if (isset($array['CompleteSuggestion'])) {
                    foreach ($array['CompleteSuggestion'] as $k => $v) {
                        if (isset($v['suggestion']))
                            $keywords[] = $v['suggestion']['@attributes']['data'];
                        elseif (isset($v[0]))
                            $keywords[] = $v[0]['@attributes']['data'];
                    }
                }
                foreach ($keywords as $keyword) {
                    $t = ASP_mb::strtolower($keyword);
	                if (
		                $t != $q &&
		                ('' != $str = wd_substr_at_word($keyword, $this->maxCharsPerWord))
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