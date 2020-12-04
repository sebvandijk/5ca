<?php
global $wpdb ;

$request = SRP_PLUGIN::get_request();

$SR_jforms = new jforms();
?>

<h4><?php _e("Change Group",'wsr')?></h4><hr/>
<form action="<?php echo $request->get_current_parameters(array("add","edit"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
    <div class="form-group">
        <div class="col-sm-12">
            <?php _e("Please choose a group to move the selected redirects to it and click the button 'Save', click the button 'Cancel' to return to the redirects list page.","wsr"); ?>
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-sm-2" for="name"><?php _e("Group Name:",'wsr');?></label>
        <div class="col-sm-5">
            <?php
            $drop = new dropdown_list('grpID');
            $groups = $wpdb->get_results("select * from `" . SR_database::WP_SEO_Groups() . "` where blog='" . get_current_blog_id() . "'  order by group_type desc;");
            foreach ( $groups as $group ) {
                $drop->add($group->group_title,$group->ID);
            }
            $drop->run($SR_jforms);
            ?>
        </div>
    </div>
    <br/><br/>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-5">
            <input type="hidden" name="change_ids" value="<?php echo $request->post('sel_items'); ?>"/>
            <button type="submit" name="save_groups" value="save_groups" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e("Save",'wsr')?></button>  <a href="<?php echo $request->get_current_parameters(array("add","edit"));?>" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span><?php _e("Cancel","wsr") ?></a>
        </div>
    </div>
    <br/><br/>
    </form>

<?php

$SR_jforms->hide_alerts();
$SR_jforms->run();
