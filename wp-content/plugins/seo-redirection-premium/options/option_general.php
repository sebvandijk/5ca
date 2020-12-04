<?php
$SR_jforms = new jforms();

$request = SRP_PLUGIN::get_request();
$options = SRP_PLUGIN::get_options();
$app = SRP_PLUGIN::get_app();

SR_option_manager::option_listener();
$check = new bcheckbox_option();
?>
<script>

    function do_clear_history()
    {
        if(confirm("<?php _e('Are you sure you want to clear all history?','wsr')?>"))
        {
            document.getElementById('clear_history_flag').value = '1';
            document.getElementById('options_from').submit();
        }
    }

    function do_clear_404()
    {
        if(confirm("<?php _e('Are you sure you want to clear all discovered 404?','wsr')?>"))
        {
            document.getElementById('clear_404_flag').value = '1';
            document.getElementById('options_from').submit();
        }
    }

</script>
<form id="options_from" action="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
<br/><h4><span class="glyphicon glyphicon-cog"></span> <?php _e(' General Options','wsr') ?></h4><hr/>
    <div class="form-group">
        <label class="control-label col-sm-2" for="plugin_status"><?php _e(' Plugin Status:','wsr') ?></label>
        <div class="col-sm-10">
            <?php
                $drop=new dropdown_list("plugin_status");
                $drop->add(__('Enabled','wsr'),'1');
                $drop->add(__('Disabled','wsr'),'0');
                $drop->run($SR_jforms);
                $drop->select($options->read_option_value('plugin_status'));
            ?>
        </div>
		
		
    </div>
	
	<div class="form-group">
        <label class="control-label col-sm-2" for="plugin_status"><?php _e(' IP Logging:','wsr') ?></label>
        <div class="col-sm-2">
            <?php
                $drop=new dropdown_list("ip_logging_status");
                $drop->add(__('No IP logging','wsr'),'0');
                $drop->add(__('Full IP logging','wsr'),'1');
				$drop->add(__('Anonymize IP (mask last part)','wsr'),'2');
                $drop->run($SR_jforms);
                $drop->select($options->read_option_value('ip_logging_status'));
            ?>
        </div>
		<div class="col-sm-8">
            <i><br>used for GDPR compliance</i>
        </div>
		
		
		
    </div>
	
	
	<div class="form-group">
        <label class="control-label col-sm-2" for="plugin_status"><?php _e(' pagination (Rows per page):','wsr') ?></label>
        <div class="col-sm-10">
            <?php
                $drop=new dropdown_list("pagination_limit");
                $drop->add(__('10','wsr'),'10');
                $drop->add(__('50','wsr'),'50');
				$drop->add(__('100','wsr'),'100');
				$drop->add(__('All','wsr'),'100000');
                $drop->run($SR_jforms);
                $drop->select($options->read_option_value('pagination_limit'));
            ?>
        </div>
		
		
    </div>
	
    <div class="form-group">
        <label class="control-label col-sm-2" for="show_redirect_box"><?php _e(' Redirect Box:','wsr') ?><br/><br/><?php _e(' Permalink:','wsr') ?><br/><br/><?php _e(' Cache:','wsr') ?></label>
        <div class="col-sm-10">
            <div style="margin-top:5px;"><?php $check->create_single_option('show_redirect_box',$options->read_option_value('show_redirect_box')) ?>	<?php _e('Show Redirect Box in posts and other selected','wsr')?> <a target="_blank" href="options-general.php?page=<?php echo $app->get_plugin_slug()?>&SR_tab=redirect_manager&redirect_manager_tab=post_types"><?php _e('Post Types','wsr')?></a>.</div>
            <div style="margin-top:5px;"><?php $check->create_single_option('add_auto_redirect',$options->read_option_value('add_auto_redirect')) ?>	<?php _e('Add automatically a 301 redirect for the modified post permalinks. (Recommended)','wsr')?></div>
            <div style="margin-top:5px;"><?php $check->create_single_option('reflect_modifications',$options->read_option_value('reflect_modifications')) ?>	<?php _e('Reflect changes in post permalinks to the database. (Recommended)','wsr')?></div>
            <div style="margin-top:5px;"><?php $check->create_single_option('cache_enable',$options->read_option_value('cache_enable')) ?>	<?php _e('Enable caching redirects to boost performance. (Recommended)','wsr')?></div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" name="save_general_options" value="save_general_options" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e('Save General Options','wsr') ?></button>
        </div>
    </div>

<br/><h4><span class="glyphicon glyphicon-time"></span> <?php _e('Redirection History Options','wsr') ?></h4><hr/>
    <div class="form-group">
        <label class="control-label col-sm-2" for="history_status"><?php _e('History Status:','wsr')?></label>
        <div class="col-sm-10">
            <?php
            $drop=new dropdown_list("history_status");
            $drop->add(__('Enabled','wsr'),'1');
            $drop->add(__('Disabled','wsr'),'0');
            $drop->run($SR_jforms);
            $drop->select($options->read_option_value('history_status'));
            ?>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2" for="history_limit"><?php _e('History Limit:','wsr') ?></label>
        <div class="col-sm-10">
            <?php
            $drop=new dropdown_list("history_limit");
            $drop->add(__('7 days','wsr'),'7');
            $drop->add(__('1 month','wsr'),'30');
            $drop->add(__('2 months','wsr'),'60');
            $drop->add(__('3 months','wsr'),'90');
            $drop->run($SR_jforms);
            $drop->select($options->read_option_value('history_limit'));
            ?>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" name="save_history_options" value="save_history_options" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e('Save History Options','wsr')?></button> <button type="button" onclick="do_clear_history();" id="clear_history" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span> <?php _e('Clear All History','wsr')?></button>
            <input id="clear_history_flag" type="hidden" name="clear_history" value="" />
        </div>
    </div>

<br/><h4><span class="glyphicon glyphicon-exclamation-sign"></span> <?php _e('404 Error Pages Options','wsr')?></h4><hr/>
    <div class="form-group">
        <label class="control-label col-sm-2" for="p404_discovery_status"><?php _e('404 Discovery Status:','wsr') ?></label>
        <div class="col-sm-10">
            <?php
            $drop=new dropdown_list("p404_discovery_status");
            $drop->add(__('Enabled','wsr'),'1');
            $drop->add(__('Disabled','wsr'),'0');
            $drop->run($SR_jforms);
            $drop->select($options->read_option_value('p404_discovery_status'));
            ?>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label col-sm-2" for="p404_rules"><?php _e('404 Rules Status:','wsr') ?></label>
        <div class="col-sm-10">
            <?php
            $drop=new dropdown_list("p404_rules");
            $drop->add(__('Enabled','wsr'),'1');
            $drop->add(__('Disabled','wsr'),'0');
            $drop->run($SR_jforms);
            $drop->select($options->read_option_value('p404_rules'));
            _e(' Manage','wsr')?> <a target="_blank" href="options-general.php?page=<?php echo $app->get_plugin_slug()?>&SR_tab=404_manager&404_manager_tab=404_rules"><?php _e('404 Rules','wsr');?></a> and <a target="_blank" href="options-general.php?page=<?php echo $app->get_plugin_slug()?>&SR_tab=404_manager&404_manager_tab=general_rules"><?php _e('General 404 Rules','wsr') ?></a>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" name="save_404_options" value="save_404_options" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e(' Save 404 Options','wsr') ?></button> <button type="button" onclick="do_clear_404();" id="clear_404" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span> <?php _e('Clear All Discovered','wsr')?></button>
            <input id="clear_404_flag" type="hidden" name="clear_404" value="" />
        </div>
    </div>

<br/><h4><span class="glyphicon glyphicon-trash"></span> <?php _e('Uninstall Options','wsr')?></h4><hr/>
    <div class="form-group">
        <label class="control-label col-sm-2" for="keep_data"><?php _e('Plugin Data:','wsr') ?></label>
        <div class="col-sm-10">
            <div style="margin-top:5px;"><?php $check->create_single_option('keep_data',$options->read_option_value('keep_data')); _e(' Keep redirection data after uninstall the plugin, this will be useful when you install it later.','wsr');?></div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" name="save_uninstall_options" value="save_uninstall_options" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e('Save Uninstall Options','wsr') ?></button> <button type="submit" name="optimize_tables" value="optimize_tables" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-cog"></span> <?php _e('Optimize Database','wsr')?></button>
        </div>
    </div>
<br/><hr/>
    <div class="form-group">
        <div class="col-sm-10">
            <button type="submit" name="save_all_options" value="save_all_options" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e('Save All Options','wsr');?></button> <button type="submit" name="reset_options" value="reset_options" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-refresh"></span> <?php _e('Reset Options','wsr');?></button>
        </div>
    </div>
<br/>
</form>
<?php
$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts();
$SR_jforms->run();