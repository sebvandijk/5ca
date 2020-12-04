<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * A wrappre class to use for keyword suggestion and autocomplete
 */

if (!class_exists('wpd_keywordSuggest')) {
    /**
     * Keyword suggestion wrapper class
     *
     * @class       wpd_keywordSuggest
     * @version     1.0
     * @package     AjaxSearchPro/Classes
     * @category    Class
     * @author      Ernest Marcinko
     */
    class wpd_keywordSuggest {

        private $suggest;

        function __construct ($source, $args) {
            $class = "wpd_". $source ."KeywordSuggest";
            $this->suggest = new $class($args);
        }

        function getKeywords($q) {
            return $this->suggest->getKeywords($q);
        }

    }
}