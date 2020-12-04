<?php
if (!defined('ABSPATH')) die('-1');

require_once(ASP_CLASSES_PATH . "widgets/class-search-widget.php");
require_once(ASP_CLASSES_PATH . "widgets/class-last_searches-widget.php");
require_once(ASP_CLASSES_PATH . "widgets/class-top_searches-widget.php");

function asp_register_the_widgets() {
    register_widget("AjaxSearchProWidget");
    register_widget("AjaxSearchProLastSearchesWidget");
    register_widget("AjaxSearchProTopSearchesWidget");
}

add_action( 'widgets_init', 'asp_register_the_widgets' );