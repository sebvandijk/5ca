<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

if (ASP_DEMO) $_POST = null;
?>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . 'settings/assets/options_search.css?v='.ASP_CURR_VER; ?>" />
<div id='wpdreams' class='asp-be asp_updates_help<?php echo isset($_COOKIE['asp-accessibility']) ? ' wd-accessible' : ''; ?>'>
	<div class="wpdreams-box" style="float: left;">
		<div class="wpd-half">
            <h3><?php echo __('Version status', 'ajax-search-pro'); ?></h3>
            <div class="item">
                <?php if (wd_asp()->updates->needsUpdate(true)): ?>
                    <?php echo sprintf( __('Version <strong>%s</strong> is available.', 'ajax-search-pro'),
                        wd_asp()->updates->getVersionString() ); ?>
                    <?php echo __('Download the new version from Codecanyon.', 'ajax-search-pro'); ?>
                    <a target="_blank" href="https://documentation.ajaxsearchpro.com/update_notes.html">
                        <?php echo __('How to update?', 'ajax-search-pro'); ?>
                    </a>
                <?php else: ?>
                    <p><?php echo __('You have the latest version installed:', 'ajax-search-pro'); ?> <strong><?php echo ASP_CURR_VER_STRING; ?></strong></p>
                <?php endif; ?>
            </div>
            <?php if (wd_asp()->updates->getUpdateNotes(ASP_CURR_VER) != ""): ?>
                <h3><?php echo __('Recent update notes', 'ajax-search-pro'); ?></h3>
                <div class="item asp_update_notes">
                    <?php echo wd_asp()->updates->getUpdateNotes(ASP_CURR_VER); ?>
                </div>
            <?php endif; ?>
            <h3><?php echo __('Support', 'ajax-search-pro'); ?></h3>
            <div class="item">
                <?php if (wd_asp()->updates->getSupport() != ""): ?>
                    <p class="errorMsg">IMPORTANT:<br><?php echo wd_asp()->updates->getSupport(); ?></p>
                <?php endif; ?>
                <?php echo sprintf( __('If you can\'t find the answer in the documentation or knowledge base, or if you are having other issues,
                feel free to <a href="%s" target="_blank">open a support ticket</a>.', 'ajax-search-pro'), 'https://wp-dreams.com/open-support-ticket-step-1/' ); ?>
            </div>
			<h3><?php echo __('Documentation', 'ajax-search-pro'); ?></h3>
			<div class="item">
				<ul>
					<li><a target="_blank" href="https://documentation.ajaxsearchpro.com/" title="HTML documentation"><?php echo __('Onlie Documentation', 'ajax-search-pro'); ?></a></li>
					<li><a target="_blank" href="https://wp-dreams.com/knowledgebase/" title="Knowledge Base"><?php echo __('Knowledge base', 'ajax-search-pro'); ?></a></li>
				</ul>
			</div>
			<h3><?php echo __('Knowledge Base', 'ajax-search-pro'); ?></h3>
			<div class="item">
				<?php echo wd_asp()->updates->getKnowledgeBase(); ?>
			</div>
		</div>
		<div class="wpd-half-last">
            <?php if (ASP_DEMO == 0): ?>
			<h3><?php echo __('Automatic Updates', 'ajax-search-pro'); ?></h3>
            <div class="item<?php echo WD_ASP_License::isActivated() === false ? "" : " hiddend"; ?>">
                <div class="asp_auto_update">
                    <p><?php echo __('To activate Automatic Updates, please activate your purchase code with this site.', 'ajax-search-pro'); ?></p>
                    <label><?php echo __('Purchase code', 'ajax-search-pro'); ?></label>
                    <input type="text" name="asp_key" id="asp_key">
                    <div class="errorMsg" style="display:none;"></div>
                    <input type="button" id="asp_activate" name="asp_activate" class="submit wd_button_blue" value="<?php echo esc_attr__('Activate for this site', 'ajax-search-pro'); ?>">
                    <span class="small-loading" style="display:none; vertical-align: middle;"></span>
                    <?php echo __('<p>If you activated the plugin <b>with this site before</b>, and you see this activation form, just enter the purchase code again to re-activate.</p>', 'ajax-search-pro'); ?>
                </div>
                <div class="asp_remote_deactivate">
                    <p><?php echo __('If the purchase code is activated with a <b>different site</b>, then you will have to first de-activate it from there, or use the form below if the site does not work anymore:', 'ajax-search-pro'); ?></p>
                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo __('Site URL', 'ajax-search-pro'); ?></label>
                    <input type="text" name="asp_site_url" id="asp_site_url"><br><br>
                    <label><?php echo __('Purchase code', 'ajax-search-pro'); ?></label>
                    <input type="text" name="asp_keyd" id="asp_keyd"><br>
                    <div class="infoMsg" style="display:none;"></div>
                    <div class="errorMsg" style="display:none;"></div>
                    <input type="button" id="asp_deactivated" name="asp_deactivated" class="submit wd_button_blue" value="<?php echo esc_attr__('Deactivate', 'ajax-search-pro'); ?>">
                    <span class="small-loading" style="display:none; vertical-align: middle;"></span>
                    <p class="descMsg" style="text-align: left;margin-top: 10px;"><?php echo __('<b>NOTICE:</b> After deactivation there is a <b>30 minute</b> wait time until you can re-activate the same purchase code to prevent malicious activity.', 'ajax-search-pro'); ?></p>
                </div>
            </div>
            <div class="item<?php echo WD_ASP_License::isActivated() === false ? " hiddend" : ""; ?> asp_auto_update">
                <p><?php echo __('Auto updates are activated for this site with purchase code:', 'ajax-search-pro'); ?> <br><b><?php echo WD_ASP_License::isActivated(); ?></b></p>
                <div class="errorMsg" style="display:none;"></div>
                <input type="button" class="submit wd_button_blue" id="asp_deactivate" name="asp_deactivate" value="<?php echo esc_attr__('Deactivate', 'ajax-search-pro'); ?>">
                <span class="small-loading" style="display:none; vertical-align: middle;"></span>
                <p class="descMsg" style="text-align: left;margin-top: 10px;"><?php echo __('<b>NOTICE:</b> After deactivation there is a <b>30 minute</b> wait time until you can re-activate the same purchase code to prevent malicious activity.', 'ajax-search-pro'); ?></p>
            </div>
            <h3><?php echo __('Manual Updates', 'ajax-search-pro'); ?></h3>
            <div class="item">
                <a target="_blank" href="https://documentation.ajaxsearchpro.com/update_notes.html"><?php echo __('How to manual update?', 'ajax-search-pro'); ?></a>
            </div>
            <?php endif; ?>
			<h3><?php echo __('Changelog', 'ajax-search-pro'); ?></h3>
			<div class="item">
				<dl>
					<?php foreach (wd_asp()->updates->getChangeLog() as $version => $log): ?>
						<dt class="changelog_title">v<?php echo $version; ?> - <a href="#"><?php echo __('view changelog', 'ajax-search-pro'); ?></a></dt>
						<dd class="hiddend"><pre><?php echo $log; ?></pre></dd>
					<?php endforeach; ?>
				</dl>
			</div>
		</div>
        <div class="clear"></div>
	</div>
    <div id="asp-options-search">
        <a class="wd-accessible-switch" href="#"><?php echo isset($_COOKIE['asp-accessibility']) ?
                __('DISABLE ACCESSIBILITY', 'ajax-search-pro') :
                __('ENABLE ACCESSIBILITY', 'ajax-search-pro'); ?></a>
    </div>
    <div class="clear"></div>
</div>
<?php
wp_enqueue_script('wpd-backend-updates-help', plugin_dir_url(__FILE__) . 'settings/assets/updates_help.js', array(
    'jquery'
), ASP_CURR_VER_STRING, true);