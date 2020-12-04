<?php
$request = SRP_PLUGIN::get_request();
$options = SRP_PLUGIN::get_options();
$app = SRP_PLUGIN::get_app();

if($request->post('pages_save')!='')
{
    $pages_status=$request->post('pages_status');
    $redirect_pages_to = $request->post('redirect_pages_to');

    if($pages_status=='on' && $redirect_pages_to=="")
    {
        $app->echo_message(__("Please input the redirect destination value!",'wsr'),'danger');
    } else{
        $options->save_option_value('pages_status',$pages_status);
        $options->save_option_value('redirect_pages_to',$redirect_pages_to);
        $app->echo_message(__("Unknown 404 web pages general rule saved successfully!",'wsr'));
    }
}

if($request->post('images_save')!='')
{
    $images_status=$request->post('images_status');
    $redirect_images_to = $request->post('redirect_images_to');

    if($images_status=='on' && $redirect_images_to=="")
    {
        $app->echo_message(__("Please input the redirect destination value!",'wsr'),'danger');
    } else{
        $options->save_option_value('images_status',$images_status);
        $options->save_option_value('redirect_images_to',$redirect_images_to);
        $app->echo_message(__("Unknown 404 images general rule saved successfully!",'wsr'));
    }
}

if($request->post('scripts_save')!='')
{
    $scripts_status=$request->post('scripts_status');
    $redirect_scripts_to = $request->post('redirect_scripts_to');

    if($scripts_status=='on' && $redirect_scripts_to=="")
    {
        $app->echo_message(__("Please input the redirect destination value!",'wsr'),'danger');
    } else{
        $options->save_option_value('scripts_status',$scripts_status);
        $options->save_option_value('redirect_scripts_to',$redirect_scripts_to);
        $app->echo_message(__("Unknown 404 JS/CSS files general rule saved successfully!",'wsr'));
    }
}

if($request->post('otherfiles_save')!='')
{
    $otherfiles_status=$request->post('otherfiles_status');
    $redirect_otherfiles_to = $request->post('redirect_otherfiles_to');

    if($otherfiles_status=='on' && $redirect_otherfiles_to=="")
    {
        $app->echo_message(__("Please input the redirect destination value!",'wsr'),'danger');
    } else{
        $options->save_option_value('otherfiles_status',$otherfiles_status);
        $options->save_option_value('redirect_otherfiles_to',$redirect_otherfiles_to);
        $app->echo_message(__("Unknown other 404 files general rule saved successfully!",'wsr'));
    }
}

$pages_status=$options->read_option_value('pages_status');
$redirect_pages_to = $options->read_option_value('redirect_pages_to');
$images_status=$options->read_option_value('images_status');
$redirect_images_to = $options->read_option_value('redirect_images_to');
$scripts_status=$options->read_option_value('scripts_status');
$redirect_scripts_to = $options->read_option_value('redirect_scripts_to');
$otherfiles_status=$options->read_option_value('otherfiles_status');
$redirect_otherfiles_to = $options->read_option_value('redirect_otherfiles_to');

$SR_jforms = new jforms();
?>
<style>
    #g404from .form-group{ margin-bottom: 5px;}
    #g404from p{ margin: 5px 0 5px 0;}
</style>
<h4><?php _e('General 404 Rules','wsr'); ?></h4><hr/>

<form id="g404from" action="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
<div class="container">
    <h5 style="display: inline; color: #636465"><b><?php _e('Unknown 404 Web Pages','wsr'); ?></b></h5>
</div>
<div class="row">
    <div class="col-sm-10 webpage_icon">
        <div class="form-group">
            <div class="col-sm-5">
                <?php
                $switch = new switch_option("pages_status",$pages_status,$SR_jforms);
                ?>
            </div>
        </div>
    <div  class="form-group">
        <div class="col-sm-12">
        <p><?php _e('Redirect all 404 web pages with no rule described to the following destination:','wsr') ?></p>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-5">
                <input type="text" style="width: 350px" class="small_text_box" value="<?php echo $redirect_pages_to;?>" id="redirect_pages_to" name="redirect_pages_to" placeholder="Redirect unknown pages to">
            </div>
        </div>
    <div  class="form-group">
        <div  class="col-sm-12">
            <button type="submit" name="pages_save" value="pages_save" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span><?php _e('Save Changes','wsr'); ?></button>
        </div>
    </div>
    </div>
</div>



    <div class="container">
        <h5 style="display: inline; color: #636465"><b><?php _e('Unknown 404 images','wsr'); ?></b></h5>
    </div>
    <div class="row">
        <div class="col-sm-10 images_icon">
            <div class="form-group">
                <div class="col-sm-5">
                    <?php
                    $switch = new switch_option("images_status",$images_status,$SR_jforms);
                    ?>
                </div>
            </div>
            <div  class="form-group">
                <div class="col-sm-12">
                    <p><?php _e('Redirect all 404 images with no rule described to the following destination:','wsr'); ?></p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <?php
                    $file_chooser = new file_chooser("redirect_images_to",$redirect_images_to);
                    ?>
                </div>
            </div>
            <div  class="form-group">
                <div  class="col-sm-12">
                    <button type="submit" name="images_save" value="images_save" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span><?php _e('Save Changes','wsr');?></button>
                </div>
            </div>
        </div>
    </div>



    <div class="container">
        <h5 style="display: inline; color: #636465"><b><?php _e('Unknown 404 JS/CSS files','wsr'); ?></b></h5>
    </div>
    <div class="row">
        <div class="col-sm-10 scripts_icon">
            <div class="form-group">
                <div class="col-sm-5">
                    <?php
                    $switch = new switch_option("scripts_status",$scripts_status,$SR_jforms);
                    ?>
                </div>
            </div>
            <div  class="form-group">
                <div class="col-sm-12">
                    <p><?php _e('Redirect all 404 JS/CSS files with no rule described to the following destination:','wsr');?></p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <input type="text" style="width: 350px" class="small_text_box" value="<?php echo $redirect_scripts_to;?>" id="redirect_scripts_to" name="redirect_scripts_to" placeholder="<?php _e('Redirect unknown JS/CSS to','wsr')?>">
                </div>
            </div>
            <div  class="form-group">
                <div  class="col-sm-12">
                    <button type="submit" name="scripts_save" value="scripts_save" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e('Save Changes'); ?></button>
                </div>
            </div>
        </div>
    </div>



    <div class="container">
        <h5 style="display: inline; color: #636465"><b><?php _e('Unknown other 404 files','wsr'); ?></b></h5>
    </div>
    <div class="row">
        <div class="col-sm-10 otherfiles_icon">
            <div class="form-group">
                <div class="col-sm-5">
                    <?php
                    $switch = new switch_option("otherfiles_status",$otherfiles_status,$SR_jforms);
                    ?>
                </div>
            </div>
            <div  class="form-group">
                <div class="col-sm-12">
                    <p><?php _e('Redirect all 404 files with no rule described to the following destination:','wsr');?></p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-5">
                    <input type="text" style="width: 350px" class="small_text_box" value="<?php echo $redirect_otherfiles_to; ?>" id="redirect_otherfiles_to" name="redirect_otherfiles_to" placeholder="<?php _e('Redirect unknown files to','wsr') ?>">
                </div>
            </div>
            <div  class="form-group">
                <div  class="col-sm-12">
                    <button type="submit" name="otherfiles_save" value="otherfiles_save" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e('Save Changes','wsr'); ?></button>
                </div>
            </div>
        </div>
    </div>
    </form>
<?php
$SR_jforms->hide_alerts();
$SR_jforms->run();