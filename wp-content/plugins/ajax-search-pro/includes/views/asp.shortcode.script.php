<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * A better method to store the init data.
 *
 * The JSON data is stored inside this invisible element, the it is parsed
 * and passed as an argument to the initialization method.
 */
?>
<?php ob_start(); ?>
{
    "homeurl": "<?php echo function_exists("pll_home_url") ? @pll_home_url() : home_url("/"); ?>",
    "resultstype": "<?php echo ((isset($style['resultstype']) && $style['resultstype']!="")?$style['resultstype']:"vertical"); ?>",
    "resultsposition": "<?php echo $style['resultsposition']; ?>",
    "resultsSnapTo": "<?php echo $style['results_snap_to']; ?>",
    "results": {
        "width": "<?php echo $style['results_width']; ?>",
        "width_tablet": "<?php echo $style['results_width_tablet']; ?>",
        "width_phone": "<?php echo $style['results_width_phone']; ?>"
    },
    "itemscount": <?php echo ( ($style['v_res_show_scrollbar'] == 1) ? $style['itemscount'] : "0" ); ?>,
    "imagewidth": <?php echo ((isset($style['selected-imagesettings']['width']))?$style['selected-imagesettings']['width']:"70"); ?>,
    "imageheight": <?php echo ((isset($style['selected-imagesettings']['height']))?$style['selected-imagesettings']['height']:"70"); ?>,
    "resultitemheight": "<?php echo ((isset($style['resultitemheight']) && $style['resultitemheight']!="")?$style['resultitemheight']:"70"); ?>",
    "showauthor": <?php echo ((isset($style['showauthor']) && $style['showauthor']!="")?$style['showauthor']:"1"); ?>,
    "showdate": <?php echo ((isset($style['showdate']) && $style['showdate']!="")?$style['showdate']:"1"); ?>,
    "showdescription": <?php echo ((isset($style['showdescription']) && $style['showdescription']!="")?$style['showdescription']:"1"); ?>,
    "charcount":  <?php echo w_isset_def($style['charcount'], 3); ?>,
    "highlight": <?php echo ((isset($style['highlight']) && $style['highlight']!="")?$style['highlight']:1); ?>,
    "highlightwholewords": <?php echo ((isset($style['highlightwholewords']) && $style['highlightwholewords']!="")?$style['highlightwholewords']:1); ?>,
    "openToBlank": <?php echo w_isset_def($style['results_click_blank'], 0); ?>,
    "scrollToResults": <?php echo w_isset_def($style['scroll_to_results'], 1); ?>,
    "resultareaclickable": <?php echo ((isset($style['resultareaclickable']) && $style['resultareaclickable']!="")?$style['resultareaclickable']:0); ?>,
    "autocomplete": {
        "enabled": <?php echo $style['autocomplete'] == 1 || $style['autocomplete'] == 2 ? 1 : 0; ?>,
        "googleOnly": <?php echo w_isset_def($style['autocomplete_source'], 'google') == 'google' ? 1 : 0; ?>,
        "lang": "<?php echo w_isset_def($style['autocomplete_google_lang'], 'en'); ?>",
        "mobile": <?php echo $style['autocomplete'] == 1 || $style['autocomplete'] == 3 ? 1 : 0; ?>
    },
    "trigger": {
        "delay": <?php echo $style['trigger_delay']; ?>,
        "autocomplete_delay": <?php echo $style['autocomplete_trigger_delay']; ?>,
        "facet": <?php echo w_isset_def($style['trigger_on_facet'], 0); ?>,
        "type": <?php echo $style['triggerontype'] == 1 ? 1 : 0; ?>,
        "click": "<?php echo $style['click_action']; ?>",
        "click_location": "<?php echo $style['click_action_location']; ?>",
        "return": "<?php echo $style['return_action']; ?>",
        "return_location": "<?php echo $style['return_action_location']; ?>",
        "redirect_url": "<?php echo apply_filters( "asp_redirect_url", w_isset_def($style['redirect_url'], '?s={phrase}') ); ?>",
        "elementor_url": "<?php echo $style['redirect_elementor']; ?>"
    },
    "overridewpdefault": <?php echo w_isset_def($style['override_default_results'], 0); ?>,
    "override_method": "<?php echo $style['override_method']; ?>",
    "settings": {
        "hideChildren": <?php echo $style['frontend_terms_hide_children']; ?>
    },
    "settingsimagepos": "<?php echo ((isset($style['settingsimagepos']) && $style['settingsimagepos']!="")?$style['settingsimagepos']:0); ?>",
    "settingsVisible": <?php echo w_isset_def($style['frontend_search_settings_visible'], 0); ?>,
    "settingsHideOnRes": <?php echo $style['fss_hide_on_results']; ?>,
    "hresulthidedesc": "<?php echo ((isset($style['hhidedesc']) && $style['hhidedesc']!="")?$style['hhidedesc']:1); ?>",
    "prescontainerheight": "<?php echo ((isset($style['prescontainerheight']) && $style['prescontainerheight']!="")?$style['prescontainerheight']:"400px"); ?>",
    "pshowsubtitle": "<?php echo ((isset($style['pshowsubtitle']) && $style['pshowsubtitle']!="")?$style['pshowsubtitle']:0); ?>",
    "pshowdesc": "<?php echo ((isset($style['pshowdesc']) && $style['pshowdesc']!="")?$style['pshowdesc']:1); ?>",
    "closeOnDocClick": <?php echo w_isset_def($style['close_on_document_click'], 1); ?>,
    "isotopic": {
        "itemWidth": "<?php echo is_numeric($style['i_item_width']) ? $style['i_item_width'].'px' : $style['i_item_width']; ?>",
        "itemWidthTablet": "<?php echo is_numeric($style['i_item_width_tablet']) ? $style['i_item_width_tablet'].'px' : $style['i_item_width_tablet']; ?>",
        "itemWidthPhone": "<?php echo is_numeric($style['i_item_width_phone']) ? $style['i_item_width_phone'].'px' : $style['i_item_width_phone']; ?>",
        "itemHeight": "<?php echo w_isset_def($style['i_item_height'], '200px'); ?>",
        "itemHeightTablet": "<?php echo w_isset_def($style['i_item_height_tablet'], '200px'); ?>",
        "itemHeightPhone": "<?php echo w_isset_def($style['i_item_height_phone'], '200px'); ?>",
        "pagination": <?php echo $style['i_pagination']; ?>,
        "rows": <?php echo w_isset_def($style['i_rows'], 2); ?>,
        "gutter": <?php echo w_isset_def($style['i_item_margin'], 10); ?>,
        "showOverlay": <?php echo w_isset_def($style['i_overlay'], 1); ?>,
        "blurOverlay": <?php echo w_isset_def($style['i_overlay_blur'], 1); ?>,
        "hideContent": <?php echo w_isset_def($style['i_hide_content'], 1); ?>
    },
    "loaderLocation": "<?php echo $style['loader_display_location']; ?>",
    "show_more": {
        "enabled": <?php echo $style['showmoreresults']; ?>,
        "url": "<?php echo apply_filters( "asp_show_more_url", $style['more_redirect_url'] ); ?>",
        "elementor_url": "<?php echo $style['more_redirect_elementor']; ?>",
        "action": "<?php echo $style['more_results_action']; ?>",
        "location": "<?php echo $style['more_redirect_location']; ?>",
        "infinite": <?php echo $style['more_results_infinite'] == 1 && $style['more_results_action'] == 'ajax' ? 1 : 0; ?>
    },
    "mobile": {
        "trigger_on_type": <?php echo $style['mob_trigger_on_type']; ?>,
        "click_action": "<?php echo $style['mob_click_action'] == 'same' ? $style['click_action'] : $style['mob_click_action']; ?>",
        "return_action": "<?php echo $style['mob_return_action'] == 'same' ? $style['return_action'] : $style['mob_return_action']; ?>",
        "click_action_location": "<?php echo $style['mob_click_action'] == 'same' ? $style['click_action_location'] : $style['mob_click_action_location']; ?>",
        "return_action_location": "<?php echo $style['mob_return_action'] == 'same' ? $style['return_action_location'] : $style['mob_return_action_location']; ?>",
        "redirect_url": "<?php echo $style['mob_click_action'] == 'custom_url' || $style['mob_return_action'] == 'custom_url' ? $style['mob_redirect_url'] : $style['redirect_url']; ?>",
        "elementor_url": "<?php echo $style['mob_click_action'] == 'same' ? $style['redirect_elementor'] : $style['mob_redirect_elementor']; ?>",
        "hide_keyboard": <?php echo $style['mob_hide_keyboard']; ?>,
        "force_res_hover": <?php echo $style['mob_force_res_hover']; ?>,
        "force_sett_hover": <?php echo $style['mob_force_sett_hover']; ?>,
        "force_sett_state": "<?php echo $style['mob_force_sett_state']; ?>"
    },
    "compact": {
        "enabled": <?php echo w_isset_def($style['box_compact_layout'], 0); ?>,
        "width": "<?php echo w_isset_def($style['box_compact_width'], "100%"); ?>",
        "width_tablet": "<?php echo w_isset_def($style['box_compact_width_tablet'], "100%"); ?>",
        "width_phone": "<?php echo w_isset_def($style['box_compact_width_phone'], "100%"); ?>",
        "closeOnMagnifier": <?php echo w_isset_def($style['box_compact_close_on_magn'], 1); ?>,
        "closeOnDocument": <?php echo w_isset_def($style['box_compact_close_on_document'], 0); ?>,
        "position": "<?php echo w_isset_def($style['box_compact_position'], 0); ?>",
        "overlay": <?php echo w_isset_def($style['box_compact_overlay'], 0); ?>
    },
    "sb": {
        "redirect_action": "<?php echo $style['fe_sb_action']; ?>",
        "redirect_location": "<?php echo $style['fe_sb_action_location']; ?>",
        "redirect_url": "<?php echo $style['fe_sb_redirect_url']; ?>",
        "elementor_url": "<?php echo $style['fe_sb_redirect_elementor']; ?>"
    },
    "rb": {
        "action": "<?php echo $style['fe_rb_action']; ?>"
    },
    "animations": {
        "pc": {
            "settings": {
                "anim" : "<?php echo w_isset_def($style['sett_box_animation'], 'fadedrop'); ?>",
                "dur"  : <?php echo w_isset_def($style['sett_box_animation_duration'], 200); ?>
            },
            "results" : {
                "anim" : "<?php echo w_isset_def($style['res_box_animation'], 'fadedrop'); ?>",
                "dur"  : <?php echo w_isset_def($style['res_box_animation_duration'], 200); ?>
            },
            "items" : "<?php echo w_isset_def($style['res_items_animation'], 'fadeInUp'); ?>"
        },
        "mob": {
            "settings": {
                "anim" : "<?php echo w_isset_def($style['sett_box_animation_m'], 'fade'); ?>",
                "dur"  : <?php echo w_isset_def($style['sett_box_animation_duration_m'], 200); ?>
            },
            "results" : {
                "anim" : "<?php echo w_isset_def($style['res_box_animation_m'], 'fade'); ?>",
                "dur"  : <?php echo w_isset_def($style['res_box_animation_duration_m'], 200); ?>
            },
            "items" : "<?php echo w_isset_def($style['res_items_animation_m'], 'voidanim'); ?>"
        }
    },
    "chosen": {
        "nores": "<?php echo esc_html(asp_icl_t("Searchable select filter placeholder" . " ($real_id)", $style['jquery_chosen_nores'])); ?>"
    },
    "detectVisibility" : <?php echo $style['visual_detect_visbility']; ?>,
    "autop": {
        "state": "<?php echo $style['auto_populate']; ?>",
        "phrase": "<?php echo $style['auto_populate_phrase']; ?>",
        "count": <?php echo $style['auto_populate_count']; ?>
    },
    "resPage": {
        "useAjax": <?php echo is_search() && $style['res_live_search'] ? 1 : 0; ?>,
        "selector": "<?php echo $style['res_live_selector']; ?>",
        "trigger_type": <?php echo $style['res_live_trigger_type'] ?>,
        "trigger_facet": <?php echo $style['res_live_trigger_facet'] ?>,
        "trigger_magnifier": <?php echo $style['res_live_trigger_click'] ?>,
        "trigger_return": <?php echo $style['res_live_trigger_return'] ?>
    },
    "fss_layout": "<?php echo $style['fss_column_layout']; ?>",
    "scrollBar": {
        "vertical": {
            "autoHide": <?php echo $style['v_res_overflow_autohide']; ?>
        },
        "horizontal": {
            "autoHide": <?php echo $style['h_res_overflow_autohide']; ?>
        },
        "settings": {
            "autoHide": <?php echo $style['settings_overflow_autohide']; ?>
        }
    },
    "preventBodyScroll": <?php echo wd_asp()->o['asp_compatibility']['js_prevent_body_scroll']; ?>,
    "statistics": <?php echo get_option('asp_stat', 0) == 0 ? 0 : 1; ?>
}
<?php $_asp_script_out = ob_get_clean(); ?>
<?php if (wd_asp()->o['asp_compatibility']['js_init'] == "blocking"): ?>
<script type="text/javascript">
/* <![CDATA[ */
if ( typeof ASP_INSTANCES == "undefined" )
    var ASP_INSTANCES = {};
ASP_INSTANCES['<?php echo $id; ?>'] = <?php echo $_asp_script_out; ?>;
/* ]]> */
</script>
<?php else: ?>
<div class="asp_init_data" style="display:none !important;" id="asp_init_id_<?php echo $id; ?>" data-aspdata="<?php echo base64_encode($_asp_script_out); ?>"></div>
<?php endif; ?>