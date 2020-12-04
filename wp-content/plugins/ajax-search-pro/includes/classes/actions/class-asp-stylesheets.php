<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_StyleSheets_Action")) {
    /**
     * Class WD_ASP_StyleSheets_Action
     *
     * Handles the non-ajax searches if activated.
     *
     * @class         WD_ASP_StyleSheets_Action
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Actions
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_StyleSheets_Action extends WD_ASP_Action_Abstract {

        /**
         * Holds the inline CSS
         *
         * @var string
         */
        private static $inline_css = "";

        /**
         * This function is bound as the handler
         */
        public function handle()
        {

            if (function_exists('get_current_screen')) {
                $screen = get_current_screen();
                if (isset($screen) && isset($screen->id) && $screen->id == 'widgets')
                    return;
            }

            // If no instances exist, no need to load any of the stylesheets
            if (wd_asp()->instances->exists()) {

                $comp_settings = wd_asp()->o['asp_compatibility'];
                $force_inline = w_isset_def($comp_settings['forceinlinestyles'], false);
                $async_load = w_isset_def($comp_settings['css_async_load'], false);
                $media_query = get_option("asp_media_query", "defncss");

                $exit = apply_filters('asp_load_css_js', false);
                if ($exit)
                    return false;

                add_action('wp_head', array($this, 'inlineCSS'), 10, 0);

                if ( asp_is_asset_required('chosen') )
                    wp_enqueue_style('wpdreams-asp-chosen', asp_get_css_url('chosen'), array(), $media_query);

                if (ASP_DEBUG == 1) {
                    $css = asp_generate_the_css();
                    self::$inline_css = $css;
                    return;

                } else if ($force_inline == 1) {

                    $css = asp_generate_the_css(false);
                    // If it's still false, we have a problem
                    if ($css === false || $css == '') return;

                    self::$inline_css = $css;
                    return;

                } else if (
                    !file_exists(wd_asp()->upload_path . asp_get_css_filename('instances')) ||
                    @filesize(wd_asp()->upload_path . asp_get_css_filename('instances')) < 1025
                ) {
                    /* Check if the CSS exists, if not, then try to force-create it */
                    asp_generate_the_css();
                    // Check again, if doesn't exist, we need to force inline styles
                    if (
                        !file_exists(wd_asp()->upload_path . asp_get_css_filename('instances')) ||
                        @filesize(wd_asp()->upload_path . asp_get_css_filename('instances')) < 1025
                    ) {
                        $css = asp_generate_the_css();
                        // Still no CSS? Problem.
                        if ($css === false || $css == '')
                            return;
                        self::$inline_css = $css;

                        // Save the force inline
                        $comp_settings['forceinlinestyles'] = 1;
                        update_option('asp_compatibility', $comp_settings);

                        return;
                    }
                }

                // Asynchronous loader enabled, load the basic only, then abort
                if (ASP_DEBUG != 1 && $force_inline != 1 && $async_load) {
                    self::$inline_css = ".asp-try{visibility:hidden;}.wpdreams_asp_sc{display: none; max-height: 0; overflow: hidden;}";
                    return;
                } else {
                    // Basic FOUC prevention
                    self::$inline_css = ".asp_m{height: 0;}";
                }

                // If everything went ok, and the async loader not enabled, get the files
                wp_enqueue_style('wpdreams-ajaxsearchpro-instances', asp_get_css_url('instances'), array(), $media_query);
            }
        }

        /**
         * Echos the inline CSS if available
         */
        public function inlineCSS() {
            ?>
            <style type="text/css">
                <!--
                @font-face {
                    font-family: 'asppsicons2';
                    src: url('<?php echo str_replace('http:',"",plugins_url()); ?>/ajax-search-pro/css/fonts/icons/icons2.eot');
                    src: url('<?php echo str_replace('http:',"",plugins_url()); ?>/ajax-search-pro/css/fonts/icons/icons2.eot?#iefix') format('embedded-opentype'),
                    url('<?php echo str_replace('http:',"",plugins_url()); ?>/ajax-search-pro/css/fonts/icons/icons2.woff2') format('woff2'),
                    url('<?php echo str_replace('http:',"",plugins_url()); ?>/ajax-search-pro/css/fonts/icons/icons2.woff') format('woff'),
                    url('<?php echo str_replace('http:',"",plugins_url()); ?>/ajax-search-pro/css/fonts/icons/icons2.ttf') format('truetype'),
                    url('<?php echo str_replace('http:',"",plugins_url()); ?>/ajax-search-pro/css/fonts/icons/icons2.svg#icons') format('svg');
                    font-weight: normal;
                    font-style: normal;
                }
                <?php echo self::$inline_css != "" ? self::$inline_css : ''; ?>
                -->
            </style>
            <?php

            /**
             * Compatibility resolution to ajax page loaders:
             *
             * If the _ASP variable is defined at this point, it means that the page was already loaded before,
             * and this header script is executed once again. However that also means that the ASP variable is
             * resetted (due to the localization script) and that the page content is changed, so ajax search pro
             * is not initialized.
             */
            ?>
            <script type="text/javascript">
                if ( typeof _ASP !== "undefined" && _ASP !== null && typeof _ASP.initialize !== "undefined" )
                    _ASP.initialize();
            </script>
            <?php
        }

        public function shouldLoadAssets() {
            $comp_settings = wd_asp()->o['asp_compatibility'];

            $exit = false;

            if ( $comp_settings['selective_enabled'] ) {
                if ( is_front_page() ) {
                    if ( $comp_settings['selective_front'] == 0 ) {
                        $exit = true;
                    }
                } else if ( is_archive() ) {
                    if ( $comp_settings['selective_archive'] == 0 ) {
                        $exit = true;
                    }
                } else if ( is_single() ) {
                    if ( !$exit && $comp_settings['selective_exin'] != '' ) {
                        global $post;
                        if ( isset($post, $post->ID) ) {
                            $_ids = wpd_comma_separated_to_array($comp_settings['selective_exin']);
                            if ( !empty($_ids) ) {
                                if ( $comp_settings['selective_exin_logic'] == 'exclude' && in_array($post->ID, $_ids) ) {
                                    $exit = true;
                                } else if ( $comp_settings['selective_exin_logic'] == 'include' && !in_array($post->ID, $_ids) ) {
                                    $exit = true;
                                }
                            }
                        }
                    }
                }
            }

            return $exit;
        }

        // ------------------------------------------------------------
        //   ---------------- SINGLETON SPECIFIC --------------------
        // ------------------------------------------------------------
        public static function getInstance() {
            if ( ! ( self::$_instance instanceof self ) ) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }
    }
}