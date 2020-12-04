<?php
global $wpdb;

$request = SRP_PLUGIN::get_request();
$app = SRP_PLUGIN::get_app();
$options = SRP_PLUGIN::get_options();

$SR_jforms = new jforms();

if($request->post('save_post_types')!='')
{
    $options->save_post_option_value('post_types');
    $app->echo_message("<b>".__("Post types are updated successfully!",'wsr')."</b>","success");
}

$selected_post_types=$options->read_option_value('post_types');
$all_types = get_post_types();
?>
    <h4><?php _e(' Post Types','wsr');?></h4><hr/>
    <form action="<?php echo $request->get_current_parameters(array("add","edit"));?>" method="post" class="form-horizontal" style="margin-left: 20px" role="form" data-toggle="validator">
        <div class="form-group">
            <div class="col-sm-12">
               <p><?php _e("Please choose post types to show the redirect box in it's add or update pages:",'wsr');?></p>
            </div>
        </div>
    <div class="form-group">
        <label class="control-label col-sm-1" for="pwd"><?php _e(' Types:','wsr');?></label>
        <div class="col-sm-5">
            <?php
            $check_list = new bcheckbox_option();
            $check_list->set_list("post_types");

            $check_list->create_check_all_button();
            echo ' ';
            $check_list->create_uncheck_all_button();
            echo "<br/><br/>";

            foreach( $all_types as $type ){
                $check_list->add_to_list($type,str_ireplace('_',' ',$type));
            }
            $check_list->selected_items($selected_post_types);
            $check_list->create_list_option();
            ?>
        </div>
    </div>
<br/>

    <div class="form-group">
        <div class="col-sm-offset-1 col-sm-5">
            <button type="submit" name="save_post_types" value="save_post_types" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e(' Save Post Types','wsr');?></button>
        </div>
    </div>
    <br/><br/>
    </form>


<?php

$SR_jforms->hide_alerts();
$SR_jforms->run();