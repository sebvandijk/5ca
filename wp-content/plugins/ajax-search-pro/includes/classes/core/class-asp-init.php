<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * Class aspInit
 *
 * AJAX SEARCH PRO initializator Class
 */
class WD_ASP_Init {

    /**
     * Core singleton class
     * @var WD_ASP_Init self
     */
    private static $_instance;

    private function __construct() {
        wd_asp()->db = WD_ASP_DBMan::getInstance();
        // @TODO 4.10.5
        /*
        wd_asp()->instant = new WD_ASP_Instant(array(
            'table_name' => 'asp_instant'
        ));
        */

        load_plugin_textdomain( 'ajax-search-pro', false, ASP_DIR . '/languages' );
    }

    /**
     * Runs on activation OR if this->safety_check() detects a silent change
     */
    public function activate() {

        // Includes the index table creation as well
        WD_ASP_DBMan::getInstance()->create();

        // @TODO 4.10.5
        /*wd_asp()->instant->db_create();
        wd_asp()->instant->db_update();*/

        $this->create_chmod(true);

        $stored_ver = get_option('asp_version', 0);
        // Was the plugin previously installed, and updated?
        if ($stored_ver != 0 && $stored_ver != ASP_CURR_VER) {
            update_option("asp_recently_updated", 1);
        }

        /**
         * Store the version number after everything is done. This is going to help distinguishing
         * stored asp_version from the ASP_CURR_VER variable. These two are different in cases:
         *  - Uninstalling, installing new versions
         *  - Uploading and overwriting old version with a new one
         */
        update_option('asp_version', ASP_CURR_VER);

        set_transient('asp_just_activated', 1);
    }

    /**
     *  Checks if the user correctly updated the plugin and fixes if not
     */
    public function safety_check() {
        $curr_stored_ver = get_option('asp_version', 0);

        // Run the re-activation actions if this is actually a newer version
        if ($curr_stored_ver != ASP_CURR_VER) {
            $this->activate();
            // Run a backwards compatibility check
            $this->backwards_compatibility_fixes();
            asp_generate_the_css();
            // Take a note on the recent update
            update_option("asp_recently_updated", 1);
        } else {
            // Still, check the folders, they might have been deleted by accident
            $this->create_chmod();

            // Was the plugin just activated, without version change?
            if ( get_transient('asp_just_activated') !== false ) {
                // Run a backwards compatibility check
                $this->backwards_compatibility_fixes();
                asp_generate_the_css();
                delete_transient('asp_just_activated');
            }
        }
    }

    /**
     * Fix known backwards incompatibilities
     */
    public function backwards_compatibility_fixes() {

        // Index table option fixes
        $ito = wd_asp()->o['asp_it_options'];
        if ( isset($ito['it_post_types']) && !is_array($ito['it_post_types']) ) {
            $ito['it_post_types'] = explode('|', $ito['it_post_types']);
            foreach ($ito['it_post_types'] as $ck => $ct) {
                if ( $ct == '' )
                    unset($ito['it_post_types'][$ck]);
            }
            wd_asp()->o['asp_it_options']['it_post_types'] = $ito['it_post_types'];
            asp_save_option('asp_it_options');
        }

        $ana = wd_asp()->o['asp_analytics'];
        // Analytics Options fixes 4.18
        if ( isset($ana['analytics']) && $ana['analytics'] == 1 ) {
            wd_asp()->o['asp_analytics']['analytics'] = 'pageview';
            asp_save_option('asp_analytics');
        }


        /*
         * Search instance option fixes
         *
         * - Get instances
         * - Check options
         * - Transition to new options based on old ones
         * - Save instances
         */
        foreach (wd_asp()->instances->get() as $si) {
            $id = $si['id'];
            $sd = $si['data'];

            // -------------------------- 4.10 ------------------------------
            // Primary and secondary fields
            $values = array('-1', '0', '1', '2', 'c__f');
            $adv_fields = array(
                'primary_titlefield',
                'secondary_titlefield',
                'primary_descriptionfield',
                'secondary_descriptionfield'
            );
            foreach($adv_fields as $field) {
                // Force string conversion for proper comparision
                if ( !in_array($sd[$field].'', $values) ) {
                    // Custom field value is selected
                    $sd[$field.'_cf'] = $sd[$field];
                    $sd[$field] = 'c__f';
                }
            }
            // -------------------------- 4.10 ------------------------------

            // ------------------------- 4.10.4 -----------------------------
            // Autocomplete aggreagated to one option only.
            if ( isset($sd['autocomplete_mobile']) ) {
                if ( $sd['autocomplete_mobile'] == 1 && $sd['autocomplete'] == 1 ) {
                    $sd['autocomplete'] = 1;
                } else if ( $sd['autocomplete_mobile'] == 1 ) {
                    $sd['autocomplete'] = 3;
                } else if ( $sd['autocomplete'] == 1 ) {
                    $sd['autocomplete'] = 2;
                } else {
                    $sd['autocomplete'] = 0;
                }
                unset($sd['autocomplete_mobile']);
            }
            // ------------------------- 4.10.4 -----------------------------

            // ------------------------- 4.11 -------------------------------
            // Autocomplete aggreagated to one option only.
            if ( !isset($sd['frontend_fields']['unselected']) )
                $sd['frontend_fields']['unselected'] = array();
            if ( isset($sd['showexactmatches'], $sd['exactmatchestext']) ) {
                $sd['frontend_fields']['labels']['exact'] = $sd['exactmatchestext'];
                if ($sd['showexactmatches'] == 0) {
                    $sd['frontend_fields']['unselected'][] = 'exact';
                    $sd['frontend_fields']['selected'] =
                        array_diff( $sd['frontend_fields']['selected'], array('exact') );
                }
            }
            if ( isset($sd['showsearchintitle'], $sd['searchintitletext']) ) {
                $sd['frontend_fields']['labels']['title'] = $sd['searchintitletext'];
                if ($sd['showsearchintitle'] == 0) {
                    $sd['frontend_fields']['unselected'][] = 'title';
                    $sd['frontend_fields']['selected'] =
                        array_diff( $sd['frontend_fields']['selected'], array('title') );
                }
            }
            if ( isset($sd['showsearchincontent'], $sd['searchincontenttext']) ) {
                $sd['frontend_fields']['labels']['content'] = $sd['searchincontenttext'];
                if ($sd['showsearchincontent'] == 0) {
                    $sd['frontend_fields']['unselected'][] = 'content';
                    $sd['frontend_fields']['selected'] =
                        array_diff( $sd['frontend_fields']['selected'], array('content') );
                }
            }
            if ( isset($sd['showsearchinexcerpt'], $sd['searchinexcerpttext']) ) {
                $sd['frontend_fields']['labels']['excerpt'] = $sd['searchinexcerpttext'];
                if ($sd['showsearchinexcerpt'] == 0) {
                    $sd['frontend_fields']['unselected'][] = 'excerpt';
                    $sd['frontend_fields']['selected'] =
                        array_diff( $sd['frontend_fields']['selected'], array('excerpt') );
                }
            }
            // ------------------------- 4.11 -------------------------------

            // ------------------------- 4.11.6 -----------------------------
            // User meta fields to array
            if ( isset($sd['user_search_meta_fields']) && !is_array($sd['user_search_meta_fields']) ) {
                $sd['user_search_meta_fields'] = explode(',', $sd['user_search_meta_fields']);
                foreach ( $sd['user_search_meta_fields'] as $umk=>$umv ) {
                    $sd['user_search_meta_fields'][$umk] = trim($umv);
                    if( $sd['user_search_meta_fields'][$umk] == '' )
                        unset($sd['user_search_meta_fields'][$umk]);
                }
            }
            // ------------------------- 4.11.6 -----------------------------

            // ------------------------- 4.11.10 ----------------------------
            // Before, this was a string
            if ( isset($sd['customtypes']) && !is_array($sd['customtypes']) ) {
                $sd['customtypes'] = explode('|', $sd['customtypes']);
                foreach ($sd['customtypes'] as $ck => $ct) {
                    if ( $ct == '' )
                        unset($sd['customtypes'][$ck]);
                }
            }
            // No longer exists
            if ( isset($sd['selected-customtypes']) )
                unset($sd['selected-customtypes']);
            // No longer exists
            if ( isset($sd['searchinpages']) ) {
                if ( $sd['searchinpages'] == 1 && !in_array('page', $sd['customtypes']) ) {
                    array_unshift($sd['customtypes'] , 'page');
                }
                unset($sd['searchinpages']);
            }
            // No longer exists
            if ( isset($sd['searchinposts']) ) {
                if ( $sd['searchinposts'] == 1 && !in_array('post', $sd['customtypes']) ) {
                    array_unshift($sd['customtypes'] , 'post');
                }
                unset($sd['searchinposts']);
            }
            // ------------------------- 4.11.10 ----------------------------

            // ------------------------- 4.12 -------------------------------
            if ( is_numeric($sd['i_item_width']) ) {
                $sd['i_item_width'] = $sd['i_item_width'].'px';
            }
            // ------------------------- 4.12 -------------------------------

            // ------------------------- 4.13.1 -----------------------------
            $font_sources = array("inputfont", "descfont", "titlefont",
                "authorfont", "datefont", "showmorefont", "groupfont",
                "exsearchincategoriestextfont", "groupbytextfont", "settingsdropfont",
                "prestitlefont", "presdescfont", "pressubtitlefont", "search_text_font");
            if ( isset($sd['inputfont']) && strpos($sd['inputfont'], '--g--') !== false ) {
                /**
                 * Remove the unneccessary --g-- tags and quotes
                 */
                foreach($font_sources as $fk) {
                    if ( isset($sd[$fk]) ) {
                        $sd[$fk] = str_replace(array('--g--', '"', "'"), '', $sd[$fk]);
                    }
                }
            }
            if ( isset($sd['results_order']) && strpos($sd['results_order'], 'peepso') === false ) {
                $sd['results_order'] .= '|peepso_groups|peepso_activities';
            }
            if ( isset($sd['groupby_content_type']) && !isset($sd['groupby_content_type']['peepso_groups']) ) {
                $sd['groupby_content_type']['peepso_groups'] = 'Peepso Groups';
                $sd['groupby_content_type']['peepso_activities'] = 'Peepso Activities';
            }
            // ------------------------- 4.13.1 -----------------------------

            // ------------------------- 4.14.4 -----------------------------
            if ( isset($sd['frontend_fields']['labels']['comments']) ) {
                unset($sd['frontend_fields']['labels']['comments']);
            }
            $sd['frontend_fields']['selected'] = array_diff( $sd['frontend_fields']['selected'], array('comments') );
            $sd['frontend_fields']['unselected'] = array_diff( $sd['frontend_fields']['unselected'], array('comments') );
            $sd['frontend_fields']['checked'] = array_diff( $sd['frontend_fields']['checked'], array('comments') );
            // ------------------------- 4.14.4 -----------------------------

            // ------------------------- 4.14.5 -----------------------------
            // For non-existence checks use the raw_data array
            if ( !isset($si['raw_data']['i_item_width_tablet']) ) {
                $sd['i_item_width_tablet'] = $sd['i_item_width'];
                $sd['i_item_width_phone'] = $sd['i_item_width'];
            }
            // For non-existence checks use the raw_data array
            if ( !isset($si['raw_data']['i_item_height_tablet']) ) {
                $sd['i_item_height_tablet'] = $sd['i_item_height'];
                $sd['i_item_height_phone'] = $sd['i_item_height'];
            }
            // For non-existence checks use the raw_data array
            if ( !isset($si['raw_data']['box_width_tablet']) ) {
                $sd['box_width_tablet'] = $sd['box_width'];
                $sd['box_width_phone'] = $sd['box_width'];
            }
            // ------------------------- 4.14.5 -----------------------------

            // ------------------------- 4.15 -------------------------------
            if ( is_numeric($sd['i_item_height']) ) {
                $sd['i_item_height'] = $sd['i_item_height'].'px';
            }
            if ( is_numeric($sd['i_item_height_tablet']) ) {
                $sd['i_item_height_tablet'] = $sd['i_item_height_tablet'].'px';
            }
            if ( is_numeric($sd['i_item_height_phone']) ) {
                $sd['i_item_height_phone'] = $sd['i_item_height_phone'].'px';
            }
            // ------------------------- 4.15 -------------------------------

            // ------------------------- 4.17 -------------------------------
            if ( !empty($sd['image_default'])
                && !isset($si['raw_data']['tax_image_default'], $si['raw_data']['tax_image_default']) )
            {
                $sd['tax_image_default'] = $sd['image_default'];
                $sd['user_image_default'] = $sd['image_default'];
            }
            // ------------------------- 4.17 -------------------------------

            // ----------------- Unset some unused search data --------------
            // Leave this here, so it is executed as last
            $values = array(
                // from 4.10
                'magnifierimage_selects', 'settingsimage_selects', 'loadingimage_selects',
                'i_res_magnifierimage_selects', 'i_pagination_arrow_selects', 'keyword_logic_def',
                'user_search_title_field_def', 'frontend_search_settings_position_def', 'term_logic_def',
                'cf_logic_def', 'resultstype_def', 'resultsposition_def', 'box_compact_float_def',
                'box_compact_position_def', 'keyword_suggestion_source_def', 'bpgroupstitle_def', 'bpgroupstitle',
                'settingsimagepos_def', 'blogtitleorderby_def', 'i_ifnoimage_def', 'i_pagination_position_def',
                'weight_def', 'user_search_description_field_def', 'triggeronclick', 'triggeronreturn', 'redirectonclick',
                'redirect_click_to', 'redirect_on_enter', 'redirect_enter_to', 'mob_trigger_on_click',
                // from 4.11
                'showexactmatches', 'exactmatchestext', 'showsearchintitle', 'searchintitletext', 'showsearchincontent',
                'searchincontenttext', 'showsearchincomments', 'searchincommentstext', 'showsearchinexcerpt', 'searchinexcerpttext'
            );
            foreach ($values as $v) {
                if ( isset($sd[$v]) )
                    unset($sd[$v]);
            }

            // At the end, update
            wd_asp()->instances->update($id, $sd);
        }
    }

    /**
     * Extra styles if needed..
     */
    public function styles() {
        // Fallback on IE<=8
        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
            $comp_options = wd_asp()->o['asp_compatibility'];
            if ( $comp_options['old_browser_compatibility'] == 1 ) {
                return;
            }
        }
    }

    /**
     * Prints the scripts
     */
    public function scripts() {
        $comp_settings = wd_asp()->o['asp_compatibility'];

        // Fallback on IE<=8
        if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(?i)msie [6-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
            if ( $comp_settings['old_browser_compatibility'] == 1 ) {
                return;
            }
        }

        $exit = apply_filters('asp_load_css_js', false);
        if ( $exit )
            return false;

        $analytics = wd_asp()->o['asp_analytics'];
        $comp_settings = wd_asp()->o['asp_compatibility'];
        $load_in_footer = w_isset_def($comp_settings['load_in_footer'], 1) == 1 ? true : false;
        $css_async_load = w_isset_def($comp_settings['css_async_load'], 0) == 1 ? true : false;
        $media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_option("asp_media_query", "defn");

        $js_source = w_isset_def($comp_settings['js_source'], 'min');
        $load_mcustom = w_isset_def($comp_settings['load_mcustom_js'], "yes") == "yes" && asp_is_asset_required('simplebar');
        $load_lazy = w_isset_def($comp_settings['load_lazy_js'], 0);

        $load_noui = asp_is_asset_required('noui');
        $load_isotope = asp_is_asset_required('isotope');
        $load_datepicker = asp_is_asset_required('datepicker');
        $load_chosen = asp_is_asset_required('chosen');
        $load_polaroid = asp_is_asset_required('polaroid');

        // @TODO 4.10.5
        //$load_autocomplete = w_isset_def($comp_settings['load_autocomplete_js'], 1);
        $minify_string = (($load_noui == 1) ? '-noui' : '') . (($load_isotope == 1) ? '-isotope' : '') . (($load_mcustom == 1) ? '-sb' : '');

        if (ASP_DEBUG) $js_source = 'nomin';

        if ( $load_polaroid ) {
            wp_register_script('wd-asp-photostack', ASP_URL . 'js/nomin/photostack.js', array("jquery"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-photostack');
        }

        if ( $css_async_load ) {
            wp_register_script('wd-asp-async-loader', ASP_URL . 'js/nomin/async.css.js', array("jquery"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-async-loader');
        }

        // @TODO 4.10.5
        // https://gist.github.com/anag0/d7211125b0eda4ecb8f459118bb67938
        /*if ( $load_autocomplete ) {
            wp_register_script('wd-asp-ajaxsearchpro-ac', ASP_URL . 'js/nomin/asp_autocomplete.js', array('jquery'), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro-ac');
        }*/

        if ( $load_chosen ) {
            if ( ASP_DEBUG == 1 || defined('WP_ASP_TEST_ENV') ) {
                wp_register_script('wd-asp-chosen', ASP_URL . 'js/nomin/chosen.jquery.js', array('jquery'), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-chosen');
            } else if ( strpos($js_source, 'scoped') !== false ) {
                wp_register_script('wd-asp-chosen', ASP_URL . 'js/min-scoped/chosen.jquery.min.js', array('wd-asp-ajaxsearchpro'), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-chosen');
            } else {
                wp_register_script('wd-asp-chosen', ASP_URL . 'js/min/chosen.jquery.min.js', array('jquery'), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-chosen');
            }
        }

        if ( $load_lazy ) {
            if ( ASP_DEBUG == 1 || defined('WP_ASP_TEST_ENV') ) {
                wp_register_script('wd-asp-lazy', ASP_URL . 'js/nomin/jquery.lazy.js', array('jquery'), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-lazy');
            } else if ( strpos($js_source, 'scoped') !== false ) {
                wp_register_script('wd-asp-lazy', ASP_URL . 'js/min-scoped/jquery.lazy.min.js', array('wd-asp-ajaxsearchpro'), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-lazy');
            } else {
                wp_register_script('wd-asp-lazy', ASP_URL . 'js/min/jquery.lazy.min.js', array('jquery'), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-lazy');
            }
        }

        if ($js_source == 'nomin' || $js_source == 'nomin-scoped') {
            $prereq = "jquery";
            if ($js_source == "nomin-scoped") {
                $prereq = "wd-asp-aspjquery";
                wp_register_script('wd-asp-aspjquery', ASP_URL . 'js/' . $js_source . '/aspjquery.js', array(), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-aspjquery');
            }

            wp_register_script('wd-asp-gestures', ASP_URL . 'js/' . $js_source . '/jquery.gestures.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-gestures');
            wp_register_script('wd-asp-mousewheel', ASP_URL . 'js/' . $js_source . '/jquery.mousewheel.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-mousewheel');

            if ( $load_mcustom ) {
                wp_register_script('wd-asp-scroll-simple', ASP_URL . 'js/' . $js_source . '/simplebar.js', array($prereq), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-scroll-simple');
            }
            wp_register_script('wd-asp-highlight', ASP_URL . 'js/' . $js_source . '/jquery.highlight.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-highlight');
            if ($load_noui) {
                wp_register_script('wd-asp-nouislider', ASP_URL . 'js/' . $js_source . '/jquery.nouislider.all.js', array($prereq), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-nouislider');
            }
            if ($load_isotope) {
                wp_register_script('wd-asp-rpp-isotope', ASP_URL . 'js/' . $js_source . '/rpp_isotope.js', array($prereq), $media_query, $load_in_footer);
                wp_enqueue_script('wd-asp-rpp-isotope');
            }
            wp_register_script('wd-asp-inviewport', ASP_URL . 'js/' . $js_source . '/jquery.inviewport.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-inviewport');

            wp_register_script('wd-asp-ajaxsearchpro', ASP_URL . 'js/' . $js_source . '/jquery.ajaxsearchpro.js', array($prereq), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro');

            wp_register_script('wd-asp-ajaxsearchpro-widgets', ASP_URL . 'js/' . $js_source . '/asp_widgets.js', array($prereq, "wd-asp-ajaxsearchpro"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro-widgets');

            wp_register_script('wd-asp-ajaxsearchpro-wrapper', ASP_URL . 'js/' . $js_source . '/asp_wrapper.js', array($prereq, "wd-asp-ajaxsearchpro"), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro-wrapper');
        } else {
            wp_enqueue_script('jquery');
            wp_register_script('wd-asp-ajaxsearchpro', ASP_URL . "js/" . $js_source . "/jquery.ajaxsearchpro" . $minify_string . ".min.js", array('jquery'), $media_query, $load_in_footer);
            wp_enqueue_script('wd-asp-ajaxsearchpro');
        }

        if ($load_datepicker) {
            wp_enqueue_script('jquery-ui-datepicker');
        }

        $ajax_url = admin_url('admin-ajax.php');
        if (w_isset_def($comp_settings['usecustomajaxhandler'], 0) == 1) {
            $ajax_url = ASP_URL . 'ajax_search.php';
        }

        if (ASP_DEBUG < 1 && strpos(w_isset_def($comp_settings['js_source'], 'min-scoped'), "scoped") !== false) {
            $scope = "aspjQuery";
        } else {
            $scope = "jQuery";
        }

        // The new variable is ASP
        wp_localize_script('wd-asp-ajaxsearchpro', 'ASP', array(
            'ajaxurl' => $ajax_url,
            'backend_ajaxurl' => admin_url('admin-ajax.php'),
            'js_scope' => $scope,
            'asp_url' => ASP_URL,
            'upload_url' => wd_asp()->upload_url,
            'css_basic_url' => asp_get_css_url('basic'),
            'detect_ajax' => w_isset_def($comp_settings['detect_ajax'], 0),
            'media_query' => get_option("asp_media_query", "defn"),
            'version' => ASP_CURR_VER,
            'scrollbar' => $load_mcustom,
            'css_loaded' => $css_async_load == 1 ? 0 : 1,
            'js_retain_popstate' => w_isset_def($comp_settings['js_retain_popstate'], 1),
            'fix_duplicates' => w_isset_def($comp_settings['js_fix_duplicates'], 1),
            'debug' => ASP_DEBUG == 1 || defined('WP_ASP_TEST_ENV'),
            'analytics' => array(
                'method' => $analytics['analytics'],
                'tracking_id' => $analytics['analytics_tracking_id'],
                'string' => $analytics['analytics_string'],
                'event' => array(
                    'focus' => array(
                        'active' => $analytics['gtag_focus'],
                        'action' => $analytics['gtag_focus_action'],
                        "category" => $analytics['gtag_focus_ec'],
                        "label" =>  $analytics['gtag_focus_el'],
                        "value" => $analytics['gtag_focus_value']
                    ),
                    'search_start' => array(
                        'active' => $analytics['gtag_search_start'],
                        'action' => $analytics['gtag_search_start_action'],
                        "category" => $analytics['gtag_search_start_ec'],
                        "label" =>  $analytics['gtag_search_start_el'],
                        "value" => $analytics['gtag_search_start_value']
                    ),
                    'search_end' => array(
                        'active' => $analytics['gtag_search_end'],
                        'action' => $analytics['gtag_search_end_action'],
                        "category" => $analytics['gtag_search_end_ec'],
                        "label" =>  $analytics['gtag_search_end_el'],
                        "value" => $analytics['gtag_search_end_value']
                    ),
                    'magnifier' => array(
                        'active' => $analytics['gtag_magnifier'],
                        'action' => $analytics['gtag_magnifier_action'],
                        "category" => $analytics['gtag_magnifier_ec'],
                        "label" =>  $analytics['gtag_magnifier_el'],
                        "value" => $analytics['gtag_magnifier_value']
                    ),
                    'return' => array(
                        'active' => $analytics['gtag_return'],
                        'action' => $analytics['gtag_return_action'],
                        "category" => $analytics['gtag_return_ec'],
                        "label" =>  $analytics['gtag_return_el'],
                        "value" => $analytics['gtag_return_value']
                    ),
                    'try_this' => array(
                        'active' => $analytics['gtag_try_this'],
                        'action' => $analytics['gtag_try_this_action'],
                        "category" => $analytics['gtag_try_this_ec'],
                        "label" =>  $analytics['gtag_try_this_el'],
                        "value" => $analytics['gtag_try_this_value']
                    ),
                    'facet_change' => array(
                        'active' => $analytics['gtag_facet_change'],
                        'action' => $analytics['gtag_facet_change_action'],
                        "category" => $analytics['gtag_facet_change_ec'],
                        "label" =>  $analytics['gtag_facet_change_el'],
                        "value" => $analytics['gtag_facet_change_value']
                    ),
                    'result_click' => array(
                        'active' => $analytics['gtag_result_click'],
                        'action' => $analytics['gtag_result_click_action'],
                        "category" => $analytics['gtag_result_click_ec'],
                        "label" =>  $analytics['gtag_result_click_el'],
                        "value" => $analytics['gtag_result_click_value']
                    )
                )
            )
        ));
    }

    /**
     *  Create and chmod the upload directory
     */
    public function create_chmod( $is_activation = false ) {
        if ( $is_activation ) {
            global $wp_filesystem;

            if ( wpd_fs_init(true, 'is_dir') && !$wp_filesystem->is_dir( wd_asp()->upload_path ) ) {
                if ( !$wp_filesystem->mkdir( wd_asp()->upload_path, 0777 ) ) {
                    return false;
                } else {
                    if ( !@chmod(wd_asp()->upload_path, 0777) ) {
                        @chmod(wd_asp()->upload_path, 0755);
                    }
                }
            }
        } else {
            /**
             * DO NOT initialize WP_Filesystem() nor $wp_filesystem here!
             * It causes conflicts later on. Instead just use the native PHP functions.
             */
            if ( !is_dir( wd_asp()->upload_path ) ) {
                if ( !mkdir( wd_asp()->upload_path, '0777', true ) ) {
                    return false;
                } else {
                    if ( !@chmod(wd_asp()->upload_path, 0777) ) {
                        @chmod(wd_asp()->upload_path, 0755);
                    }
                }
            }
        }
        return true;
    }

    public function pluginReset( $triggerActivate = true ) {
        $options = array(
            'asp_version',
            'asp_glob_d',
            'asp_performance_def',
            'asp_performance',
            'asp_it_def',
            'asp_it',
            'asp_it_options',
            'asp_analytics_def',
            'asp_analytics',
            'asp_caching_def',
            'asp_caching',
            'asp_compatibility_def',
            'asp_compatibility',
            'asp_defaults',
            'asp_st_override',
            'asp_woo_override',
            'asp_stat',
            'asp_updates',
            'asp_updates_lc',
            'asp_media_query',
            'asp_performance_stats',
            'asp_recently_updated',
            '_asp_tables',
            '_asp_priority_groups',
            '_asp_it_pool_sizes'
        );
        foreach ($options as $o)
            delete_option($o);

        wp_clear_scheduled_hook('asp_cron_it_extend');

        if ( $triggerActivate )
            $this->activate();
    }

    public function pluginWipe() {
        require_once(ASP_CLASSES_PATH . "cache/cache.inc.php");

        // Options
        $this->pluginReset( false );

        // Meta
        if ( is_multisite() ) {
            global $switched;
            $sites = get_sites(array('fields' => 'ids'));
            foreach ($sites as $site) {
                switch_to_blog($site);
                delete_metadata('post', 1, '_asp_additional_tags', '', true);
                delete_metadata('post', 1, '_asp_metadata', '', true);
                wpd_TextCache::clearDBCache(); // Delete options cache
                restore_current_blog();
            }
        } else {
            delete_metadata('post', 1, '_asp_additional_tags', '', true);
            delete_metadata('post', 1, '_asp_metadata', '', true);
            wpd_TextCache::clearDBCache(); // Delete options cache
        }

        // Database
        wd_asp()->db->delete();

        // Files with safety check for alterations
        if (
            str_replace('/', '', get_home_path()) != str_replace('/', '', wd_asp()->upload_path) &&
            strpos(wd_asp()->upload_path, 'wp-content') > 5 &&
            strpos(wd_asp()->upload_path, 'wp-includes') === false &&
            strpos(wd_asp()->upload_path, 'wp-admin') === false &&
            is_dir( wd_asp()->upload_path )
        ) {
            wpd_rmdir( wd_asp()->upload_path  );
            if ( is_dir( wd_asp()->upload_path ) ) {
                wpd_rmdir( wd_asp()->upload_path, true);
                if ( is_dir( wd_asp()->upload_path ) ) {
                    // Last attempt, with force
                    wpd_rmdir( wd_asp()->upload_path, true, true);
                }
            }
        }
        // Deactivate
        deactivate_plugins(ASP_FILE);
    }


    /**
     *  If anything we need in the footer
     */
    public function footer() {

    }

    /**
     * Get the instane of asp_indexTable
     *
     * @return self
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}