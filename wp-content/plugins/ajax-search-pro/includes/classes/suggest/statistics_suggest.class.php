<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (!class_exists('wpd_statisticsKeywordSuggest')) {
    /**
     * Statistics database keyword suggestions
     *
     * @class       wpd_statisticsKeywordSuggest
     * @version     1.1
     * @package     AjaxSearchPro/Classes
     * @category    Class
     * @author      Ernest Marcinko
     */
    class wpd_statisticsKeywordSuggest extends wpd_keywordSuggestAbstract {

        function __construct($args = array()) {
	        $defaults = array(
		        'maxCount' => 10,
		        'maxCharsPerWord' => 25,
		        'match_start' => false
	        );
	        $args = wp_parse_args( $args, $defaults );

            $this->maxCount = $args['maxCount'];
            $this->maxCharsPerWord = $args['maxCharsPerWord'];
	        $this->matchStart = $args['match_start'];
        }

        function getKeywords($q) {
            global $wpdb;
            $keywords = array();
            $res = array();

            $_keywords = $wpdb->get_results("SELECT keyword FROM ".$wpdb->base_prefix."ajaxsearchpro_statistics WHERE keyword LIKE '".$q."%' ORDER BY num desc", ARRAY_A);

            foreach($_keywords as $k=>$v) {
                $keywords[] = $v['keyword'];
            }

            foreach ($keywords as $keyword) {
                $t = ASP_mb::strtolower($keyword);
                $q = ASP_mb::strtolower($q);
	            if (
		            $t != $q &&
		            ('' != $str = wd_substr_at_word($t, $this->maxCharsPerWord))
	            ) {
		            if ($this->matchStart && ASP_mb::strpos($t, $q) === 0)
			            $res[] = $str;
		            elseif (!$this->matchStart)
			            $res[] = $str;
	            }
            }

            return $res;
        }

    }
}