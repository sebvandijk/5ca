<?php
global $wpdb ;

$request = SRP_PLUGIN::get_request();
$app = SRP_PLUGIN::get_app();

$table_name = SR_database::WP_SEO_Groups();
$SR_jforms = new jforms();
$SR_redirect_cache = new clogica_SR_redirect_cache();

if($request->post("name")!="")
{
    $name = $request->post("name");

    if($request->post("add")!="")
    {
        $wpdb->query(" insert into $table_name(group_title,blog) values('$name','" . get_current_blog_id() . "');  ");
        
        $app->echo_message("<b>".__("A new group has been added successfully!",'wsr')."</b>","success");

    }else if($request->post("edit","int")!=0)
    {
        $id=$request->post("edit","int");
        $wpdb->query(" update $table_name set group_title='$name' where blog='" . get_current_blog_id() . "' and ID=$id;  ");
        
        $app->echo_message("<b>".__("A group has been updated successfully!",'wsr')."</b>","success");
    }
}

//- Add - update section ---------------------------------------------
if($request->get('add')!='' || $request->get('edit','int')!=0) {
    $default_name="";
    if($request->get("edit","int")!=0)
    {
        $id=$request->get("edit","int");
        $default_name=$wpdb->get_var("select group_title from $table_name where ID=$id");
    }

?>


    <h4><?php _e('Add Custom Group','wsr')?></h4><hr/>
    <form action="<?php echo $request->get_current_parameters(array("add","edit"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
    <div class="form-group">
        <label class="control-label col-sm-2" for="name"><?php _e('Group Name:','wsr')?></label>
        <div class="col-sm-5">
            <input type="text" class="form-control" id="name" name="name" value="<?php echo $default_name; ?>" placeholder="Group Name" data-error="<?php _e('address is invalid','wsr')?>"  title="<?php _e('Input Group Name!','wsr')?>" required autofocus>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-5">
            <button type="submit" name="save" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-floppy-disk"></span> <?php _e('Save','wsr')?></button>  <a href="<?php echo $request->get_current_parameters(array("add","edit"));?>" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span> <?php _e('Cancel','wsr');?></a>
        </div>
    </div>
        <?php
        $fildname="noaction";
        $fildval="";

        if($request->get("add")!="")
        {
            $fildname="add";
            $fildval="1";
        }elseif($request->get("edit","int")!=0)
        {
            $fildname="edit";
            $fildval=$request->get("edit","int");
        }
        echo "<input type=\"hidden\" name=\"$fildname\" value=\"$fildval\" >";
        ?>
    </form>

<?php

//- Delete section ---------------------------------------------
}else if($request->get('del','int')!=0){
?>

    <h4><?php _e('Delete Custom Group','wsr') ?></h4><hr/>
    <form action="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
    <div class="container">
        <p><?php _e('Please choose one of the following option to decide what to do with the redirects that belong the the group you want to delete :','wsr');?></p>
            <div class="radio">
                <label><input type="radio" value="move_all" name="del_option" checked><?php _e('Move all redirects inside this group to: ','wsr');?></label> <?php
                    $drop = new dropdown_list('grpID');
                    $groups = $wpdb->get_results("select * from `$table_name` where blog='" . get_current_blog_id() . "'  order by group_type desc;");
                    foreach ( $groups as $group ) {
                        $drop->add($group->group_title,$group->ID);
                    }
                    $drop->run($SR_jforms);
                    ?>
            </div>
            <div class="radio">
                <label><input type="radio" value="del_all" name="del_option"><?php _e(' Delete the group and all redirects inside it.','wsr');?></label>
            </div>
        <br/>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-5">
                <input type="hidden" name="del_id" value="<?php echo $request->get('del'); ?>" />
                <button type="submit" name="Delete" value="Delete" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span> <?php _e('Delete','wsr'); ?></button>  <a href="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" class="btn btn-default btn-sm"><span class="fa fa-undo"></span> <?php _e('Cancel','wsr');?></a>
            </div>
        </div>
        <br/>

    </div>
    </form>


<?php
}else{
//- List section ---------------------------------------------


 if($request->post('del_id')!='')
 {

     $del_option = $request->post('del_option');
     $del_id = $request->post('del_id','int');
     $grpID = $request->post('grpID','int');

     if($del_option == 'del_all')
     {
         $wpdb->query("delete from $table_name where blog='" . get_current_blog_id() . "' and ID=$del_id ");
         $wpdb->query("delete from " . SR_database::WP_SEO_Redirection() . " where blog='" . get_current_blog_id() . "' and grpID=$del_id ");

     }else
     {
         $wpdb->query("delete from $table_name where blog='" . get_current_blog_id() . "' and ID=$del_id ");
         $wpdb->query("update " . SR_database::WP_SEO_Redirection() . " set grpID=$grpID  where blog='" . get_current_blog_id() . "' and grpID=$del_id ");
     }

     $SR_redirect_cache->free_cache();
     $app->echo_message("<b>".__("The selected group is deleted successfully!",'wsr')."</b>",'success') ;

 }


    ?>

<h4><?php _e('Custom Group','wsr')?></h4><hr/>
<div class="form-group">
    <div class="col-sm-8">
        <table class="table table-bordered table-hover table-striped" >
            <thead>
            <tr>
                <th class="btn btn-default table_header toolcell"><?php _e('Del','wsr'); ?></th>
                <th class="btn btn-default table_header toolcell"><?php _e('Edit','wsr'); ?></th>
                <th class="btn btn-default table_header"><?php _e('Group Name','wsr'); ?></th>
                <th class="btn btn-default table_header" width="100"><?php _e('Redirects','wsr'); ?></th>
            </tr>
            </thead>
            <?php

                $system_groups = $wpdb->get_results("select * from `$table_name` where blog='" . get_current_blog_id() . "' and group_type=0 order by ID desc;");
                foreach ( $system_groups as $group ) {
                    ?>
                <tr>
                    <td class="toolcell"><a class="btn btn-danger btn-xs tool" href="<?php echo $request->get_current_parameters();?>&del=<?php echo $group->ID ;?>"><span aria-hidden="true" class="fa fa-trash"></span></a></td>
                    <td class="toolcell"><a class="btn btn-primary btn-xs tool" href="<?php echo $request->get_current_parameters();?>&edit=<?php echo $group->ID ;?>"><span aria-hidden="true" class="fa fa-pencil"></span></a></td>
                    <td><?php echo $group->group_title ;?></td>
                    <td width="100" align="center"><a href="<?php echo $request->get_current_parameters(array("add","edit","grpID","redirect_manager_tab"));?>&redirect_manager_tab=redirects&grpID=<?php echo $group->ID ;?>"><?php

                            $id=$group->ID;
                            echo $wpdb->get_var("select count(*) as cnt from `" . SR_database::WP_SEO_Redirection() . "` where cat='link' and blog='" . get_current_blog_id() . "' and grpID=$id ");


                            ?></a></td>
                </tr>
            <?php
            }
            ?>
        </table>

        <a href="<?php echo $request->get_current_parameters();?>&add=1" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> <?php _e('Add New Group','wsr') ?></a>
    <br/><br/>
    </div>
</div>
<?php
}

$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts();
$SR_jforms->run();
?>