<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

$com_options = wd_asp()->o['asp_compatibility'];

if (ASP_DEMO) $_POST = null;

/* Error Checking*/
$_comp = wpdreamsCompatibility::Instance();
$_comp_errors = $_comp->get_errors();
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

    <div class="wpdreams-box" style="float:left; width: 690px;">

        <?php ob_start(); ?>

        <div tabid="1">
            <fieldset>
                <legend><?php echo __('CSS and JS compatibility', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/cssjs_options.php"); ?>

            </fieldset>
        </div>
        <div tabid="4">
            <fieldset>
                <legend><?php echo __('CSS and JS loading', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/cssjs_loading.php"); ?>

            </fieldset>
        </div>
        <div tabid="2">
            <fieldset>
                <legend><?php echo __('Query compatibility options', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/query_options.php"); ?>

            </fieldset>
        </div>
        <div tabid="3">
            <fieldset>
                <legend><?php echo __('Other options', 'ajax-search-pro'); ?></legend>

                <?php include(ASP_PATH . "backend/tabs/compatibility/other.php"); ?>

            </fieldset>
        </div>

        <?php $_r = ob_get_clean(); ?>

        <?php
        $updated = false;
        if (isset($_POST) && isset($_POST['asp_compatibility']) && (wpdreamsType::getErrorNum() == 0)) {
            $values = array(
                // CSS and JS
                "js_source" => $_POST['js_source'],
                "js_init" => $_POST['js_init'],
                "load_in_footer" => $_POST['load_in_footer'],
                "detect_ajax" => $_POST['detect_ajax'],
                'js_retain_popstate' => $_POST['js_retain_popstate'],
                'js_fix_duplicates' => $_POST['js_fix_duplicates'],
                "css_compatibility_level" => $_POST['css_compatibility_level'],
                'css_minify' => $_POST['css_minify'],
                "forceinlinestyles" => $_POST['forceinlinestyles'],
                "load_google_fonts" => $_POST['load_google_fonts'],
                "css_async_load" => $_POST['css_async_load'],
                "usecustomajaxhandler" => $_POST['usecustomajaxhandler'],
                'old_browser_compatibility' => $_POST['old_browser_compatibility'],
                // Loading
                "load_lazy_js" => $_POST['load_lazy_js'],
                "load_mcustom_js" => $_POST['load_mcustom_js'],
                'selective_enabled' => $_POST['selective_enabled'],
                'selective_front' => $_POST['selective_front'],
                'selective_archive' => $_POST['selective_archive'],
                'selective_exin_logic' => $_POST['selective_exin_logic'],
                'selective_exin' => $_POST['selective_exin'],
                // Query options
                'query_soft_check' => $_POST['query_soft_check'],
                'use_acf_getfield' => $_POST['use_acf_getfield'],
                'db_force_case' => $_POST['db_force_case'],
                'db_force_unicode' => $_POST['db_force_unicode'],
                'db_force_utf8_like' => $_POST['db_force_utf8_like'],
                // Other options
                'meta_box_post_types' => $_POST['meta_box_post_types']
            );
            update_option('asp_compatibility', $values);
            asp_parse_options();
            $updated = true;
            wd_asp()->init->create_chmod(); // Make sure to check if the upload folder exists.
            asp_generate_the_css();
        }
        ?>
        <div class='wpdreams-slider'>

            <?php if ($updated): ?>
                <div class='successMsg'>
                    <?php echo __('Search compatibility settings successfuly updated!', 'ajax-search-pro'); ?>
                </div>
            <?php endif; ?>

            <?php if (ASP_DEMO): ?>
                <p class="infoMsg">DEMO MODE ENABLED - Please note, that these options are read-only</p>
            <?php endif; ?>

            <ul id="tabs" class='tabs'>
                <li><a tabid="1" class='current multisite'><?php echo __('CSS & JS compatibility', 'ajax-search-pro'); ?></a></li>
                <li><a tabid="4" class='general'><?php echo __('CSS & JS loading', 'ajax-search-pro'); ?></a></li>
                <li><a tabid="2" class='general'><?php echo __('Query compatibility', 'ajax-search-pro'); ?></a></li>
                <li><a tabid="3" class='general'><?php echo __('Other', 'ajax-search-pro'); ?></a></li>
            </ul>

            <div class='tabscontent'>

            <!-- Compatibility form -->
            <form name='compatibility' method='post'>

                <?php print $_r; ?>

                <div class="item">
                    <input type='submit' class='submit' value='<?php echo esc_attr__('Save options', 'ajax-search-pro'); ?>'/>
                </div>
                <input type='hidden' name='asp_compatibility' value='1'/>
            </form>

            </div>
        </div>
    </div>

    <?php if ($_comp->has_errors()): ?>
        <div class="wpdreams-box errorbox" style="float:left;width: 270px;">
            <a class="wd-accessible-switch" href="#"><?php echo isset($_COOKIE['asp-accessibility']) ? 'DISABLE ACCESSIBILITY' : 'ENABLE ACCESSIBILITY'; ?></a><br>
            <h1><?php echo __('Possible compatibility errors:', 'ajax-search-pro'); ?> <?php echo count($_comp_errors['errors']); ?></h1>
            <?php foreach($_comp_errors['errors'] as $k=>$err): ?>
                <div>
                    <h3><?php echo __('Error', 'ajax-search-pro'); ?> #<?php echo ($k+1); ?></h3><p class='err'><?php echo $err; ?></p>
                    <h3><?php echo __('Possible Consequences', 'ajax-search-pro'); ?></h3><p class='cons'><?php echo $_comp_errors['cons'][$k]; ?></p>
                    <h3><?php echo __('Solutions', 'ajax-search-pro'); ?></h3><p class='sol'><?php echo $_comp_errors['solutions'][$k]; ?></p>
                </div>
            <?php endforeach; ?>
            <?php echo __('Please note, that these errors may not be accurate!', 'ajax-search-pro'); ?>
        </div>
    <?php else: ?>
        <div class="wpdreams-box errorbox" style="float:left; width: auto;">
            <a class="wd-accessible-switch" href="#"><?php echo isset($_COOKIE['asp-accessibility']) ?
                    __('DISABLE ACCESSIBILITY', 'ajax-search-pro') :
                    __('ENABLE ACCESSIBILITY', 'ajax-search-pro'); ?></a><br>
            <p class='tick'><?php echo __('No compatibility errors found!', 'ajax-search-pro'); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php
wp_enqueue_script('wpd-backend-compatibility', plugin_dir_url(__FILE__) . 'settings/assets/compatibility_settings.js', array(
    'jquery'
), ASP_CURR_VER_STRING, true);