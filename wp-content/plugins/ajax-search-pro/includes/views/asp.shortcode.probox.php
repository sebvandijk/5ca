<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

/**
 * The loader class is not accessible from here,
 * as this is a front-end request and types.inc.php is not included.
 *
 * As an exception, the loading icon values are copied here.
 */
$asp_loaders = array(
    "ball-pulse" => 3,
    "ball-grid-pulse" => 9,
    "simple-circle" => 0,
    "ball-clip-rotate" => 1,
    "ball-clip-rotate-simple" => 2,
    "ball-clip-rotate-multiple" => 2,
    "ball-rotate" => 1,
    "cube-transition" => 2,
    "ball-scale" => 1,
    "line-scale" => 5,
    "line-scale-party" => 4,
    "ball-scale-multiple" => 3,
    "ball-pulse-sync" => 3,
    "ball-beat" => 3,
    "line-scale-pulse-out" => 5,
    "line-scale-pulse-out-rapid" => 5,
    "ball-scale-ripple" => 1,
    "ball-scale-ripple-multiple" => 3,
    "ball-spin-fade-loader" => 8,
    "line-spin-fade-loader" => 8,
    "ball-grid-beat" => 9,
);
?>
<div class="probox">
    <?php do_action('asp_layout_before_magnifier', $id); ?>

    <div class='promagnifier'>
        <?php do_action('asp_layout_in_magnifier', $id); ?>
	    <div class='asp_text_button<?php echo w_isset_def($style['display_search_text'], 0) == 1 ? "" : " hiddend"; ?>'>
		    <?php echo asp_icl_t( "Search button text ($real_id)", $style['search_text']); ?>
	    </div>
        <div class='innericon<?php echo w_isset_def($style['hide_magnifier'], 0) == 1 ? " hiddend" : ""; ?>'>
            <?php
            if (w_isset_def($style['magnifierimage_custom'], "") == "" &&
                pathinfo($style['magnifierimage'], PATHINFO_EXTENSION) == 'svg'
            ) {
                echo file_get_contents(WP_PLUGIN_DIR . '/' . $style['magnifierimage']);
            }
            ?>
        </div>
	    <div class="asp_clear"></div>
    </div>

    <?php do_action('asp_layout_after_magnifier', $id); ?>

    <?php do_action('asp_layout_before_settings', $id); ?>

    <div class='prosettings<?php echo w_isset_def($style['box_compact_layout'], 0)==1?' hiddend':''; ?>' <?php echo($settingsHidden ? "style='display:none;'" : ""); ?> data-opened=0>
        <?php do_action('asp_layout_in_settings', $id); ?>
        <div class='innericon'>
            <?php
            if (w_isset_def($style['settingsimage_custom'], "") == "" &&
                pathinfo($style['settingsimage'], PATHINFO_EXTENSION) == 'svg'
            ) {
                echo file_get_contents(WP_PLUGIN_DIR . '/' . $style['settingsimage']);
            }
            ?>
        </div>
    </div>

    <?php do_action('asp_layout_after_settings', $id); ?>

    <?php do_action('asp_layout_before_input', $id); ?>

    <div class='proinput<?php echo w_isset_def($style['box_compact_layout'], 0)==1?' hiddend':''; ?>'>
        <form action='#' autocomplete="off" aria-label="<?php echo __('Search form', 'ajax-search-pro') . ' ' . $real_id; ?>">
            <input type='search' class='orig'
                   placeholder='<?php echo asp_icl_t( "Search bar placeholder text" . " ($real_id)", w_isset_def($style['defaultsearchtext'], '') ); ?>'
                   name='phrase' value='<?php echo apply_filters('asp_print_search_query', asp_get_search_query(), $id, $real_id); ?>'
                   aria-label="<?php echo __('Search input', 'ajax-search-pro') . ' ' . $real_id; ?>"
                   autocomplete="off"/>
            <input type='text' class='autocomplete' name='phrase' value=''
                   aria-label="<?php echo __('Search autocomplete, ignore please', 'ajax-search-pro'); ?>"
                   aria-hidden="true"
                   autocomplete="off" disabled/>
            <input type='submit'
                   aria-hidden="true"
                   aria-label="<?php echo esc_html(asp_icl_t('Hidden button', 'Hidden button')); ?>"
                   style='width:0; height: 0; visibility: hidden;'>
        </form>
    </div>

    <?php do_action('asp_layout_after_input', $id); ?>

    <?php do_action('asp_layout_before_loading', $id); ?>

    <div class='proloading<?php echo w_isset_def($style['box_compact_layout'], 0)==1?' hiddend':''; ?>'>
        <?php
        $asp_loader_class = w_isset_def($style['loader_image'], "simple-circle");
        ?>
        <?php if ( trim($style['loadingimage_custom']) == '' ): ?>
        <div class="asp_loader">
            <div class="asp_loader-inner asp_<?php echo $asp_loader_class; ?>">
            <?php
            for($i=0;$i<$asp_loaders[$asp_loader_class];$i++) {
                echo "
                <div></div>
                ";
            }
            ?>
            </div>
        </div>
        <?php endif; ?>
        <?php
        do_action('asp_layout_in_loading', $id);
        ?>
    </div>

    <?php if ($style['show_close_icon']): ?>
        <div class='proclose<?php echo w_isset_def($style['box_compact_layout'], 0)==1?' hiddend':''; ?>'>
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
                 y="0px"
                 width="512px" height="512px" viewBox="0 0 512 512" enable-background="new 0 0 512 512"
                 xml:space="preserve">
            <polygon id="x-mark-icon"
                     points="438.393,374.595 319.757,255.977 438.378,137.348 374.595,73.607 255.995,192.225 137.375,73.622 73.607,137.352 192.246,255.983 73.622,374.625 137.352,438.393 256.002,319.734 374.652,438.378 "/>
            </svg>
        </div>
    <?php endif; ?>

    <?php do_action('asp_layout_after_loading', $id); ?>

</div>