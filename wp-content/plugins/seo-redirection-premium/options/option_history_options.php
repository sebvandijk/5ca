<?php
$SR_jforms = new jforms();

$request = SRP_PLUGIN::get_request();
$options = SRP_PLUGIN::get_options();

SR_option_manager::manage_history_options();
SR_option_manager::clear_history();
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
    </script>
<form id="options_from" action="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
    <br/><h4><span class="glyphicon glyphicon-time"></span><?php _e(' Redirection History Options','wsr');?></h4><hr/>
    <div class="form-group">
        <label class="control-label col-sm-2" for="history_status"><?php _e(' History Status:','wsr');?></label>
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
        <label class="control-label col-sm-2" for="history_limit"><?php _e('History Limit:','wsr');?></label>
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
            <button type="submit" name="save_history_options" value="save_history_options" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span><?php _e(' Save History Options','wsr');?></button> <button type="button" onclick="do_clear_history();" id="clear_history" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span><?php _e(' Clear All History','wsr');?></button>
            <input id="clear_history_flag" type="hidden" name="clear_history" value="" />
        </div>
    </div>
    <br/>
</form>
    <br/>

<?php
$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts();
$SR_jforms->run();