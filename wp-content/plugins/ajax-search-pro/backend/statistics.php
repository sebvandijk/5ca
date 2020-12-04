<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

wp_register_script('wpdreams-jqPlot', ASP_URL_NP . 'backend/settings/assets/js/jqPlot/jquery.jqplot.min.js', array(
    'jquery'
));
wp_enqueue_script('wpdreams-jqPlot');
wp_register_script('wpdreams-jqPlotdateAxisRenderer', ASP_URL_NP . 'backend/settings/assets/js/jqPlot/plugins/jqplot.dateAxisRenderer.min.js', array(
    'wpdreams-jqPlot'
));
wp_enqueue_script('wpdreams-jqPlotdateAxisRenderer');
wp_register_script('wpdreams-jqPlotcanvasTextRenderer', ASP_URL_NP . 'backend/settings/assets/js/jqPlot/plugins/jqplot.canvasTextRenderer.min.js', array(
    'wpdreams-jqPlot'
));
wp_enqueue_script('wpdreams-jqPlotcanvasTextRenderer');
wp_register_script('wpdreams-jqPlotcanvasAxisTickRenderer', ASP_URL_NP . 'backend/settings/assets/js/jqPlot/plugins/jqplot.canvasAxisTickRenderer.min.js', array(
    'wpdreams-jqPlot'
));
wp_enqueue_script('wpdreams-jqPlotcanvasAxisTickRenderer');
wp_register_script('wpdreams-jqPlotcategoryAxisRenderer', ASP_URL_NP . 'backend/settings/assets/js/jqPlot/plugins/jqplot.categoryAxisRenderer.min.js', array(
    'wpdreams-jqPlot'
));
wp_enqueue_script('wpdreams-jqPlotcategoryAxisRenderer');
wp_register_script('wpdreams-jqPlotbarRenderer', ASP_URL_NP . 'backend/settings/assets/js/jqPlot/plugins/jqplot.barRenderer.min.js', array(
    'wpdreams-jqPlot'
));
wp_enqueue_script('wpdreams-jqPlotbarRenderer');

wp_register_style('wpdreams-jqPlotstyle', ASP_URL_NP . 'backend/settings/assets/js/jqPlot/jquery.jqplot.min.css');
wp_enqueue_style('wpdreams-jqPlotstyle');

global $wpdb;

if (isset($_POST['asp_stat'])) {
    update_option("asp_stat", $_POST['asp_stat']);
}
$asp_stat = get_option("asp_stat", 0);
$where = "";

if ( isset($_POST['searchform']) ) {
    $where = " WHERE search_id=" . ( $_POST['searchform'] + 0);
}
if (isset($_POST['clearstatistics']))
    asp_statistics::clearAll();

$top20 = isset($_POST['searchform']) ? asp_statistics::getTop(20, $_POST['searchform']) : asp_statistics::getTop(20);
$last20 = isset($_POST['searchform']) ? asp_statistics::getLast(20, $_POST['searchform']) : asp_statistics::getLast(20);
$top500 = isset($_POST['searchform']) ? asp_statistics::getTop(500, $_POST['searchform']) : asp_statistics::getTop(500);

if (isset($_POST['searchform']))
    $current_search = wd_asp()->instances->get($_POST['searchform'] + 0);

?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/options_search.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be wpdreams wrap<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>

	<?php if ( wd_asp()->updates->needsUpdate() ): ?>
        <p class='infoMsgBox'>
            <?php echo sprintf( __('Version <strong>%s</strong> is available.', 'ajax-search-pro'),
                wd_asp()->updates->getVersionString() ); ?>
            <?php echo __('Download the new version from Codecanyon.', 'ajax-search-pro'); ?>
            <a target="_blank" href="https://documentation.ajaxsearchpro.com/update_notes.html">
                <?php echo __('How to update?', 'ajax-search-pro'); ?>
            </a>
        </p>
	<?php endif; ?>

    <div class="wpdreams-box" style="float:left;">
        <style>
            #all span {
                font-size: 12px;
            }
        </style>
        <fieldset>
            <legend><?php echo __('Statistics Options', 'ajax-search-pro'); ?></legend>
            <form style='margin:20px;' name="asp_stat_settings" action="" method="POST">
                <div class="item">
                    <?php $o = new wpdreamsYesNo("asp_stat", __('Enable statistics?', 'ajax-search-pro'), $asp_stat); ?>
                </div>
            </form>
            <form style='float:left;margin:20px;' name="settings" action="" method="POST">
                <input name="clearstatistics" class='submit' type="submit"
                       onclick='var c=confirm("<?php echo esc_attr__('Are you sure?', 'ajax-search-pro'); ?>");if (!c) event.preventDefault();'
                       value="<?php echo esc_attr__('Clear search Statistics', 'ajax-search-pro'); ?>"/>
            </form>
            <form style='float:left;margin:20px;' name="settings" action="" method="POST">
                <label><?php echo __('Statistics for:', 'ajax-search-pro'); ?></label>
                <select name='searchform'>
                    <option value='0'><?php echo __('All', 'ajax-search-pro'); ?></option>
                    <?php foreach (wd_asp()->instances->get() as $search) { ?>
                        <option value='<?php echo $search['id'] ?>'><?php echo $search['name'] ?></option>
                    <?php } ?>
                </select>
                <input type='submit' class='submit' value='<?php echo esc_attr__('Get Statistics!', 'ajax-search-pro'); ?>'/>
            </form>
            <div class='clear'></div>
        </fieldset>
        </form>
    </div>
    <ul id="tabs" class='tabs'>
        <li><a tabid="1" class='current'><?php echo __('Statistics for:', 'ajax-search-pro'); ?> <?php echo(isset($current_search['name']) ? $current_search['name'] : __('All', 'ajax-search-pro')); ?></a></li>
        <li><a tabid="2"><?php echo __('Keywords', 'ajax-search-pro'); ?></a></li>
    </ul>
    <div class='tabscontent'>
        <div tabid="1">
            <div id='top20' style='width:800px; height:300px;margin:70px;float:left;'></div>
            <div id='last20' style='width:800px; height:300px;margin:70px;float:left;'></div>
            <div class='clear'></div>
        </div>
        <div tabid="2">
            <div id='all' style='width:800px; height:auto;margin:70px;float:left;'>
                <h3><?php echo __('Top 500 keywords', 'ajax-search-pro'); ?></h3>
                <?php
                foreach ($top500 as $keyword) {
                    echo "
            <span>&nbsp;&nbsp;" . strip_tags($keyword['keyword']) . " (" . $keyword['num'] . ")
            &nbsp;&nbsp;<img keyword='" . $keyword['id'] . "' style='cursor:pointer;vertical-align:middle;' title='Click here if you want to delete this keyword from the list!'' src='" . plugins_url('/settings/assets/icons/delete.png', __FILE__) . "' class='deletekeyword' />
            </span>
            ";
                }
                ?>
            </div>
            <div class='clear'></div>
        </div>
    </div>
    <div id="asp-options-search">
        <a class="wd-accessible-switch" href="#"><?php echo isset($_COOKIE['asp-accessibility']) ?
                __('DISABLE ACCESSIBILITY', 'ajax-search-pro') :
                __('ENABLE ACCESSIBILITY', 'ajax-search-pro'); ?></a>
    </div>
    <div class="clear"></div>
    <script>
        <?php
            $items1 = "";
        foreach ($top20 as $item) {
            $items1.= "['".wd_mysql_escape_mimic(substr($item['keyword'], 0, 40))."', ".$item['num']."],";
        }
        rtrim($items1, ",");

        $items2 = "";
        foreach ($last20 as $item) {
            $items2.= "['".wd_mysql_escape_mimic(substr($item['keyword'], 0, 40))."', ".$item['num']."],";
        }
        rtrim($items2, ",");
        ?>
        var line1 = [<?php echo $items1; ?>];
        var line2 = [<?php echo $items2; ?>];
    </script>
</div>
<?php
wp_enqueue_script('wd-backend-statistics', ASP_URL_NP . 'backend/settings/assets/statistics.js', array(
    'wpdreams-jqPlot'
), ASP_CURR_VER_STRING, true);
wp_localize_script('wd-backend-statistics', 'ASP_STAT', array(
    'msg_del' => __('Do yo really want to delete this item?', 'ajax-search-pro'),
    'msg_t20' => __('Top 20 Search Phrases', 'ajax-search-pro'),
    'msg_l20' => __('Last 20 Search Phrases', 'ajax-search-pro'),
));