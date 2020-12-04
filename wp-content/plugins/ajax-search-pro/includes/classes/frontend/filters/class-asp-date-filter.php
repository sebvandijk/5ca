<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists('aspDateFilter') ) {
    class aspDateFilter extends aspFilter {
        public $data = array();

        protected $default = array(
            'label' => '',
            'value' => '',
            'name'  => '',
            'format' => 0,
            'default' => false,
            'placeholder' => ''
        );

        protected $key = 'value';
        protected $type = 'date';
    }
}