<?php
if (!defined('ABSPATH')) die('-1');

if ( !class_exists('aspPostTagsFilter') ) {
    class aspPostTagsFilter extends aspTaxFilter {
        protected $type = 'post_tags';
    }
}