<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (ASP_DEMO) $_POST = null;

$args = array(
    'public'   => true,
    '_builtin' => false
);

$output = 'names'; // names or objects, note names is the default
$operator = 'or'; // 'and' or 'or'

$post_types = array_merge(array('all'), get_post_types( $args, $output, $operator ));

$blogs = array();
if (function_exists('get_sites'))
    $blogs = get_sites();

wd_asp()->priority_groups = WD_ASP_Priority_Groups::getInstance();
$_comp = wpdreamsCompatibility::Instance();
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/options_search.css?v='.ASP_CURR_VER; ?>" />
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/priorities.css?v='.ASP_CURR_VER; ?>" />
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

    <?php if ( $_comp->has_errors() ): ?>
        <div class="wpdreams-box errorbox">
            <p class='errors'>
            <?php echo sprintf( __('Possible incompatibility! Please go to the
                 <a href="%s">error check</a> page to see the details and solutions!', 'ajax-search-pro'),
                get_admin_url() . 'admin.php?page=asp_compatibility_settings'
            ); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="wpdreams-box" style="position: relative; float:left;">
        <ul id="tabs" class='tabs'>
            <li><a tabid="1" class='current general'><?php echo __('Priority Groups', 'ajax-search-pro'); ?></a></li>
            <li><a tabid="2" class='general'><?php echo __('Individual Priorities', 'ajax-search-pro'); ?></a></li>
        </ul>

        <div class='tabscontent'>
            <div tabid="1">
                <fieldset>
                    <legend><?php echo __('Priority Groups', 'ajax-search-pro'); ?></legend>

                    <?php include(ASP_PATH . "backend/tabs/priorities/priority_groups.php"); ?>

                </fieldset>
            </div>
            <div tabid="2">
                <fieldset>
                    <legend><?php echo __('Individual Priorities', 'ajax-search-pro'); ?></legend>

                    <?php include(ASP_PATH . "backend/tabs/priorities/priorities_individual.php"); ?>

                </fieldset>
            </div>
        </div>

    </div>
    <div id="asp-options-search">
        <a class="wd-accessible-switch" href="#"><?php echo isset($_COOKIE['asp-accessibility']) ?
                __('DISABLE ACCESSIBILITY', 'ajax-search-pro') :
                __('ENABLE ACCESSIBILITY', 'ajax-search-pro'); ?></a>
    </div>
    <div class="clear"></div>
</div>

<?php
$media_query = ASP_DEBUG == 1 ? asp_gen_rnd_str() : get_option("asp_media_query", "defn");
wp_enqueue_script('asp-backend-priorities', plugin_dir_url(__FILE__) . 'settings/assets/priorities.js', array(
    'jquery'
), $media_query, true);
wp_localize_script('asp-backend-priorities', 'ASP_PTS', array(
    "admin_url" => admin_url(),
    "ajax_url"  => admin_url('admin-ajax.php'),
    'msg_pda' => esc_attr__('Post Title/Date/Author', 'ajax-search-pro'),
    'msg_sav' => esc_attr__('Save changes!', 'ajax-search-pro'),
    'msg_pri' => esc_attr__('Priority', 'ajax-search-pro')
));
wp_enqueue_script('asp-backend-pg-controllers', plugin_dir_url(__FILE__) . 'settings/assets/priorities/controllers.js', array(
    'jquery'
), $media_query, true);
wp_enqueue_script('asp-backend-pg-events', plugin_dir_url(__FILE__) . 'settings/assets/priorities/events.js', array(
    'jquery',
    'asp-backend-pg-controllers'
), $media_query, true);
wp_localize_script('asp-backend-pg-events', 'ASP_EVTS', array(
    'msg_npg' => esc_attr__('Add new priority group', 'ajax-search-pro'),
    'msg_sav' => esc_attr__('Save!', 'ajax-search-pro'),
    'msg_can' => esc_attr__('Cancel', 'ajax-search-pro'),
    'msg_epg' => esc_attr__('Edit priority group:', 'ajax-search-pro'),
    'msg_del' => esc_attr__('Are you sure you want to delete %s ?', 'ajax-search-pro'),
    'msg_dal' => esc_attr__('Are you sure you want to delete all groups? This is not reversible!', 'ajax-search-pro'),
    'msg_dru' => esc_attr__('Are you sure you want to delete this rule?', 'ajax-search-pro'),
    'msg_cru' => esc_attr__('Only 10 categories are allowed per rule!', 'ajax-search-pro'),
    'msg_uns' => esc_attr__('You have unsaved changes! Are you sure you want to leave?', 'ajax-search-pro')
));