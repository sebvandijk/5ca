<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists('aspGenericFilter') ) {
    class aspGenericFilter extends aspFilter {
        public $data = array(
            "field" => ""
        );

        protected $default = array(
            'label' => '',
            'selected' => false,
            'value' => '',
            'level' => 0,
            'default' => false
        );
        protected $key = 'value';
        protected $type = 'generic';
    }
}