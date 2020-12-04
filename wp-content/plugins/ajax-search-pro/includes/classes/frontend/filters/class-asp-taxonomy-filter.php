<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists('aspTaxFilter') ) {
    class aspTaxFilter extends aspFilter {
        public $data = array(
            "type" => "checkbox",
            "required" => false,
            "invalid_input_text" => "Please select one!",
            "default" => "checked",
            "placeholder" => "",
            "taxonomy" => "category",
            "allow_empty" => '',    // true or false, or '' for inherit
            "logic" => ''           // and, or, andex or '' for inherit
        );

        protected $default = array(
            'label' => '',
            'selected' => false,
            'id' => 0,
            'level' => 0,
            'default' => false,
            'parent' => 0,
            'taxonomy' => 'category'
        );

        protected $type = 'taxonomy';

        public function isMixed() {
            $taxonomies = array();
            foreach ( $this->values as $value ) {
                if ( $value->id != 0 && isset($value->taxonomy) ) {
                    $taxonomies[] = $value->taxonomy;
                    $taxonomies = array_unique($taxonomies);
                    if (count($taxonomies) > 1)
                        return true;
                }
            }
            return false;
        }
    }
}