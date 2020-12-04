<?php
if (!defined('ABSPATH')) die('-1');

/**
 * Includes all files required for keyword suggestions
 */

require_once(ASP_CLASSES_PATH . "suggest/suggest-abstract.class.php");
require_once(ASP_CLASSES_PATH . "suggest/google_suggest.class.php");
require_once(ASP_CLASSES_PATH . "suggest/google_places.class.php");
require_once(ASP_CLASSES_PATH . "suggest/tags_suggest.class.php");
require_once(ASP_CLASSES_PATH . "suggest/terms_suggest.class.php");
require_once(ASP_CLASSES_PATH . "suggest/titles_suggest.class.php");
require_once(ASP_CLASSES_PATH . "suggest/statistics_suggest.class.php");
require_once(ASP_CLASSES_PATH . "suggest/suggest-wrapper.class.php");