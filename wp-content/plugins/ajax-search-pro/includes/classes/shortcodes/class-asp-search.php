<?php
if (!defined('ABSPATH')) die('-1');

if (!class_exists("WD_ASP_Search_Shortcode")) {
    /**
     * Class WD_ASP_Search_Shortcode
     *
     * Search bar shortcode
     *
     * @class         WD_ASP_Search_Shortcode
     * @version       1.0
     * @package       AjaxSearchPro/Classes/Shortcodes
     * @category      Class
     * @author        Ernest Marcinko
     */
    class WD_ASP_Search_Shortcode extends WD_ASP_Shortcode_Abstract {

        /**
         * Overall instance count
         *
         * @var int
         */
        private static $instanceCount = 0;

        /**
         * Used in views, true if the data view is printed
         *
         * @var bool
         */
        private static $dataPrinted = false;

        private static $filtersParsed = array();

        /**
         * Instance count per search ID
         *
         * @var array
         */
        private static $perInstanceCount = array();

        /**
         * Does the search shortcode stuff
         *
         * @param array|null $atts
         * @return string|void
         */
        public function handle($atts) {
            $style = null;
            $mdetectObj = new WD_MobileDetect();

            extract(shortcode_atts(array(
                'id' => 'something',
                'extra_class' => '',
                'display_on_mobile' => 1
            ), $atts));

            // If disabled on mobile exit
            if ( $display_on_mobile == 0 && $mdetectObj->isMobile() ) return;

			// Disable back-end display on taxonomy list pages
			if ( is_admin() && isset($_GET['taxonomy']) ) return;

            if ( WD_ASP_Ajax::doingAjax("ajaxsearchpro_preview") ) {
                require_once(ASP_PATH . "backend" . DIRECTORY_SEPARATOR . "settings" . DIRECTORY_SEPARATOR . "types.inc.php");
                parse_str($_POST['formdata'], $style);
                $search = wd_asp()->instances->get($id);
                $style = wpdreams_parse_params($style);
                $style = wd_asp()->instances->decode_params($style);
            } else {
                // Visual composer, first selected row, no data, so select the first one
                if ($id == 99999) {
                    $_instances = wd_asp()->instances->get();
                    if ( empty($_instances) )
                        return "There are no search instances to display. Please create one.";

                    $search = reset($_instances);
                } else {
                    $_instance = wd_asp()->instances->get($id);
                    if ( empty($_instance) )
                        return "This search form (with id $id) does not exist!";
                    $search = $_instance;
                }

                // Parse the id again for correction
                $id = $search['id'] + 0;
                $wpdreams_ajaxsearchpros[$id] = 1;
                $style = $search['data'];
            }

            // Fallback on IE<=8
            if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
                $comp_options = wd_asp()->o['asp_compatibility'];
                if ( $comp_options['old_browser_compatibility'] == 1 ) {
                    get_search_form(true);
                    return;
                }
            }

            // If disabled on mobile from the back-end
            if ( $style['mob_display_search'] == 0 && $mdetectObj->isMobile() ) return;
            if ( $style['desktop_display_search'] == 0 && !$mdetectObj->isMobile() ) return;

            // Don't move this above any return statements!
            self::$instanceCount++;
            if (isset(self::$perInstanceCount[$id]))
                self::$perInstanceCount[$id]++;
            else
                self::$perInstanceCount[$id] = 1;

            $style = array_merge(wd_asp()->options['asp_defaults'], $style);

            global $post;
            if ( !empty($post->ID) ) {
                $asp_metadata = get_post_meta( $post->ID, '_asp_metadata', true );
                if ( is_array($asp_metadata) ) {
                    if (
                        !empty($asp_metadata['asp_suggested_phrases']) &&
                        (
                            empty($asp_metadata['asp_suggested_instances']) ||
                            $asp_metadata['asp_suggested_instances'] == $id
                        )
                    )
                        $style['frontend_suggestions_keywords'] = $asp_metadata['asp_suggested_phrases'];
                }
            }

            // Disabled compact layout if the box is hidden anyways
            if ( $style['box_sett_hide_box'] == 1 ) {
                $style['box_compact_layout'] = 0;
                $style['frontend_search_settings_visible'] = 1;
                $style['show_frontend_search_settings'] = 1;
                $style['frontend_search_settings_position'] = "block";
                $style['resultsposition'] = "block";
                $style['charcount'] = 0;
                if ( $style['trigger_on_facet'] == 0 && $style['fe_search_button'] == 0 )
                    $style['trigger_on_facet'] = 1;
            }

            // Triggered by URL
            if ( isset($_GET['asp_s']) ) {
                if ( empty($_GET['asp_id']) || (!empty($_GET['asp_id']) && $_GET['asp_id']==$id) ) {
                    $style['auto_populate'] = "phrase";
                    $style['auto_populate_phrase'] = $_GET['asp_s'];
                }
            }

            $style['redirect_elementor'] = !empty($style['redirect_elementor']) ? get_permalink($style['redirect_elementor']) : home_url("/");
            $style['redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['redirect_elementor']);
            $style['mob_redirect_elementor'] = !empty($style['mob_redirect_elementor']) ? get_permalink($style['mob_redirect_elementor']) : home_url("/");
            $style['mob_redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['mob_redirect_elementor']);
            $style['fe_sb_redirect_elementor'] = !empty($style['fe_sb_redirect_elementor']) ? get_permalink($style['fe_sb_redirect_elementor']) : home_url("/");
            $style['fe_sb_redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['fe_sb_redirect_elementor']);
            $style['more_redirect_elementor'] = !empty($style['more_redirect_elementor']) ? get_permalink($style['more_redirect_elementor']) : home_url("/");
            $style['more_redirect_elementor'] = add_query_arg('asp_ls', '{phrase}', $style['more_redirect_elementor']);

            // Hidden, but might be possible to show it
            $settingsHidden = w_isset_def($style['show_frontend_search_settings'], 1) != 1 ? true : false;
            // Hidden, as well as the switch, so not possible to show
            $settingsFullyHidden =
                $style['show_frontend_search_settings']!= 1 &&
                $style['frontend_search_settings_visible'] != 1 ? true : false;

            // Initialize the filters global
            wd_asp()->front_filters = WD_ASP_FrontFilters::getInstance();

            /*$asp_f_items = array();
            if (w_isset_def($style['custom_field_items'], "") != "") {
                $asp_f_items = asp_parse_custom_field_filters( $style );
            }*/


            // If images are removed the results count is unpredictable, thus disable ajax loader on more results
            if (
                ($style['resultstype'] == "isotopic" && $style['i_ifnoimage'] == 'removeres') ||
                ($style['resultstype'] == 'polaroid' && $style['pifnoimage'] == 'removeres') ||
                $style['group_by'] != "none"
            ){
                $style['more_results_action'] = "redirect";
            }

            // IMPORTANT for Perfromace: If the index table is enabled, the generics are forced to be enabled
            // ..if unchecked, it will be overwritten excplicitly anyways
            if ( $style['search_engine'] == 'index') {
                $style['searchintitle'] = 1;
                $style['searchincontent'] = 1;
                $style['searchinexcerpt'] = 1;
            }

            $style['_id'] = $id;

            $style = apply_filters("asp_shortcode_search_options", $style);
            $_st = &$style; // Shorthand

            // Finally make preview changes after option changes
            if ( WD_ASP_Ajax::doingAjax("ajaxsearchpro_preview") ) {
                ob_start();
                include(ASP_PATH . "/css/style.basic.css.php");
                include(ASP_PATH . "/css/style.css.php");
                $out = ob_get_contents();
                ob_end_clean();
                //file_put_contents($file, $out, FILE_TEXT);
                $this->fonts( $style );
                ?>
                <div style='display: none;' id="asp_preview_options"><?php echo base64_encode(serialize($style)); ?></div>
                <style>
                    <?php echo $out; ?>
                </style>
                <?php
            }

            if (isset($_POST['p_asp_data']) || isset($_POST['np_asp_data'])) {
                $_p_data = isset($_POST['p_asp_data']) ? $_POST['p_asp_data'] : $_POST['np_asp_data'];
                $_p_id = isset($_POST['p_asid']) ? $_POST['p_asid'] : $_POST['np_asid'];
                if ( $_p_id == $id ) {
                    parse_str($_p_data, $style['_fo']);
                }
            } else if ( isset($_GET['p_asp_data']) ) {
                $_p_id = isset($_GET['p_asid']) ? $_GET['p_asid'] : $_GET['np_asid'];
                if ( $_GET['p_asp_data'] == 1 ) {
                    if ( $_p_id == $id ) {
                        $style['_fo'] = $_GET;
                    }
                } else {
                    // Legacy support
                    if ( $_p_id == $id ) {
                        parse_str(base64_decode($_GET['p_asp_data']), $style['_fo']);
                    }
                }
            }

            // Parse the filters for this shortcode ID
            asp_parse_filters($id, $_st, true);

            do_action('asp_layout_before_shortcode', $id);

            ob_start();
            include(ASP_PATH."includes/views/asp.shortcode.php");
            $out = ob_get_clean();

            do_action('asp_layout_after_shortcode', $id);

            return apply_filters('asp_shortcode_output', $out, $id);
        }

        /**
         * Importing fonts does not work correctly it appears.
         * Instead adding the links directly to the header is the best way to go.
         */
        public function fonts( $style = "" ) {
            // If custom font loading is disabled, exit
            $comp_options = wd_asp()->o['asp_compatibility'];
            if ( $comp_options['load_google_fonts'] != 1 )
                return false;

            $imports = array();
            $font_sources = array("inputfont", "descfont", "titlefont", 'fe_sb_font',
                "authorfont", "datefont", "showmorefont", "groupfont",
                "exsearchincategoriestextfont", "groupbytextfont", "settingsdropfont",
                "prestitlefont", "presdescfont", "pressubtitlefont", "search_text_font");


            if ($style != "") {
                foreach($font_sources as $fs) {
                    if (isset($style["import-".$fs]) && !in_array(trim($style["import-".$fs]), $imports))
                        $imports[] = trim($style["import-".$fs]);
                }
            } else {
                foreach (wd_asp()->instances->get() as $instance) {
                    foreach($font_sources as $fs) {
                        if (isset($instance['data']["import-".$fs]) && !in_array(trim($instance['data']["import-".$fs]), $imports))
                            $imports[] = trim($instance['data']["import-".$fs]);
                    }
                }
            }

            foreach ( $imports as $ik => $im )
                if ( $im == '' )
                    unset($imports[$ik]);

            $imports = apply_filters('asp_custom_fonts', $imports);
            foreach ($imports as $import) {
                $import = trim(str_replace(array("@import url(", ");", "https:", "http:"), "", $import));
                if ( $import == '' )
                    continue;
                ?>
                <link href='<?php echo $import; ?>' rel='stylesheet' type='text/css'>
                <?php
            }
        }

        public static function instanceCount() {
            return self::$instanceCount;
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