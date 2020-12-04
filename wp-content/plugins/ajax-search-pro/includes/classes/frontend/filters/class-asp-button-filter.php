<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists('aspButtonFilter') ) {
    class aspButtonFilter extends aspFilter {
        protected $default = array(
            'label' => '',
            'type' => '',
            'container_class' => '',
            'button_class' => ''
        );
        protected $key = 'type';
        protected $type = 'button';
    }
}