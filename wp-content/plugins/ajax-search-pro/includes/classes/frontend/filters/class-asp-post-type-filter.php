<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists('aspPostTypeFilter') ) {
    class aspPostTypeFilter extends aspFilter {
        public $data = array(
            'required' => false,
            'invalid_input_text' => 'This is required!'
        );

        protected $default = array(
            'label' => '',
            'selected' => false,
            'value' => '',
            'level' => 0,
            'default' => false
        );
        protected $key = 'value';
        protected $type = 'post_type';
    }
}