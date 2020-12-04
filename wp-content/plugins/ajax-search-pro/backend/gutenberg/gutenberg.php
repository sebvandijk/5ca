<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

add_action('admin_init', array('asp_Gutenberg', 'init'));

if ( !class_exists('asp_Gutenberg') ) {
    class asp_Gutenberg {

        private static $media_query = '';

        public static function init() {

            if ( function_exists('register_block_type') ) {
                $instances = wd_asp()->instances->getWithoutData();

                if (count($instances) > 0) {
                    $ids = array_keys($instances);
                    if (self::$media_query == '')
                        self::$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_option("asp_media_query", "defn");
                    wp_register_script(
                        'wd-asp-gutenberg',
                        ASP_URL_NP . 'backend/gutenberg/gutenberg.js',
                        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-components'),
                        self::$media_query,
                        true
                    );
                    wp_localize_script('wd-asp-gutenberg', 'ASP_GUTENBERG', array(
                        'ids' => $ids,
                        'instances' => $instances
                    ));
                    wp_register_style('wd-asp-gutenberg-css',
                        ASP_URL_NP . 'backend/gutenberg/gutenberg.css',
                        array( 'wp-edit-blocks' ),
                        self::$media_query
                    );
                    register_block_type( 'ajax-search-pro/block-asp-main', array(
                        'editor_script' => 'wd-asp-gutenberg',
                        'editor_style' => 'wd-asp-gutenberg-css'
                    ) );
                    //wp_enqueue_script('wd-asp-gutenberg');

                }
            }
        }
    }
}