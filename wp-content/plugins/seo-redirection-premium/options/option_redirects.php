<?php

global $wpdb;

$request = SRP_PLUGIN::get_request();
$app = SRP_PLUGIN::get_app();
$options = SRP_PLUGIN::get_options();
$misc = SRP_PLUGIN::get_misc();
$security = SRP_PLUGIN::get_security();

$table_name =SR_database::WP_SEO_Redirection();
$SR_jforms = new jforms();
$SR_redirect_cache = new clogica_SR_redirect_cache();
$redirect_manager_tab =  $request->get('redirect_manager_tab');

//- Add Update Code  ------------------------------------
if($request->post('add')!='' || $request->post('edit','int')>0)
{
    $search_for = '';
    $replace_with  = '';
	
    $enabled=$request->post("enabled");
    $grpID=$request->post("grpID","int");
    $redirect_from_type=$request->post("redirect_from_type");
    $redirect_from_folder_settings = $request->post("redirect_from_folder_settings");
    $redirect_from_subfolders=$request->post("redirect_from_subfolders");
    $redirect_to_folder_settings=$request->post("redirect_to_folder_settings");
    $redirect_to_type=$request->post("redirect_to_type");
    $redirect_type=$request->post("redirect_type");
    $redirect_from = $request->post('redirect_from');   
    if($redirect_from_type !='Regex' && $redirect_from_type !='Replace')
      $redirect_from = $request->make_relative_url(urldecode($redirect_from));
    if($redirect_from_type == "Regex")
    {
       $redirect_from = SR_redirect_manager::regex_add_slashes(SR_redirect_manager::regex_strip_slashes($redirect_from));
    }
    
    $redirect_to=$request->make_relative_url($request->post('redirect_to'));

    $wpdb->query("delete from " . SR_database::WP_SEO_404_links() . " where blog='" . get_current_blog_id() . "' and link='$redirect_from' ");

    if(($request->post('edit','int')>0 && $wpdb->get_var("select ID from " . SR_database::WP_SEO_Redirection() . " where blog='" . get_current_blog_id() . "' and redirect_from='$redirect_from' and cat='link'")==$request->post('edit','int')) || $wpdb->get_var("select redirect_from from $table_name where blog='" . get_current_blog_id() . "' and redirect_from='$redirect_from' and cat='link'")!=$redirect_from)
    {
		
        $regex="";
        if($redirect_from_type =='Folder')
        {
            if(substr($redirect_from,-1)!='/')
            {
                $redirect_from = $redirect_from . '/';
            }

            if($redirect_from_folder_settings==2)
            {
                if($redirect_from_subfolders ==0)
                {
                    $regex= '^'. $misc->regex_prepare($redirect_from) . '.*';
                }
                else
                {
                    $regex= '^'. $misc->regex_prepare($redirect_from) . '[^/]*$';
                }
            }
            else if($redirect_from_folder_settings==3)
            {
                if($redirect_from_subfolders ==0)
                {
                    $regex= '^'. $misc->regex_prepare($redirect_from) . '.+';
                }
                else
                {
                    $regex= '^'. $misc->regex_prepare($redirect_from) . '[^/]+$';
                }
            }
        }
        else if($redirect_from_type =='Regex')
        {
            $regex= $redirect_from;
        }
        else if($redirect_from_type =='Contain')
        {
            $regex= '^.*' . $misc->regex_prepare($redirect_from) . '.*$';
        }
        else if($redirect_from_type =='StartWith')
        {
            $regex= '^' . $misc->regex_prepare($redirect_from) . '.*$';
        }
        else if($redirect_from_type =='EndWith')
        {
            $regex= '^.*' . $misc->regex_prepare($redirect_from) . '$';
        }
        else if($redirect_from_type =='Filetype')
        {
            $regex= '^.*' . $misc->regex_prepare( '.' . $redirect_from) . '$';
        }
        else if($redirect_from_type =='Replace')
        {
            $redirect_from = $request->post("search_for");
            $redirect_to = $request->post("replace_with");
            $redirect_to_type = "Replace";
        }
        
        if ($redirect_from_type=='Page' || $redirect_from_type=='Regex')
        {
            $redirect_from_folder_settings="";
            $redirect_from_subfolders="";
        }

        if ($redirect_to_type=='Page')
        {
            $redirect_to_folder_settings="";
        }

        if($redirect_to_type =='Folder')
        {
            if(substr($redirect_to,-1)!='/')
                $redirect_to= $redirect_to. '/';
        }

        if($request->post('edit')!='')
        {
            $ID = $request->post('edit','int');
            $wpdb->query("update $table_name set enabled='$enabled', grpID=$grpID, redirect_from_type='$redirect_from_type', redirect_to_type='$redirect_to_type', redirect_from_folder_settings='$redirect_from_folder_settings', redirect_from_subfolders='$redirect_from_subfolders', redirect_to_folder_settings='$redirect_to_folder_settings', redirect_type='$redirect_type', redirect_from='$redirect_from', redirect_to='$redirect_to', regex='$regex' where blog='" . get_current_blog_id() . "' and ID=$ID    ");
			
			
            SR_redirect_manager::clear_fixed_404($redirect_from, $regex);
            $app->echo_message("<b>".__("The redirect is updated successfully!",'wsr')."</b>",'success') ;                       
            $SR_redirect_cache->free_cache();
        }else
        {
            $cat='link';
            if($redirect_manager_tab == 'redirects_rules')
            {
                $cat="special";
            }
	    
            $wpdb->query("insert into $table_name(enabled,grpID,redirect_from_type,redirect_from_folder_settings,redirect_from_subfolders,redirect_to_type,redirect_to_folder_settings,redirect_type,redirect_from,redirect_to,cat,regex,blog) values('$enabled','$grpID','$redirect_from_type','$redirect_from_folder_settings','$redirect_from_subfolders','$redirect_to_type','$redirect_to_folder_settings','$redirect_type' ,'$redirect_from','$redirect_to','$cat','$regex','" . get_current_blog_id() . "') ");
		
            SR_redirect_manager::clear_fixed_404($redirect_from, $regex);
            $app->echo_message("<b>".__("New redirect is added successfully!",'wsr')."</b>",'success') ;
            $SR_redirect_cache->free_cache();
        }
    }else
    {
        $app->echo_message("<b>".__("This redirect is already exists!",'wsr')."</b>",'danger') ;
    }

    if($request->post('add')!='')
    {
        if($request->post('post_operation')=='draft')
        {
            if($request->post('post_operation_id','int')>0)
            {
                $draft_post = array();
                $draft_post['ID'] = $request->post('post_operation_id','int');
                $draft_post['post_status'] = 'draft';
                wp_update_post( $draft_post );
            }
        }elseif($request->post('post_operation')=='trash')
        {
            if($request->post('post_operation_id','int')>0)
            wp_trash_post($request->post('post_operation_id','int'));
            $_POST['return']='';
        }
    }
}

//- Add Delete Forms  ------------------------------------
if($request->get('add')!='' || $request->get('edit')!='' )
{
    require "option_redirects_add_edit.php";

}else if($request->post('change_group')!='' && $request->post('sel_items')!='')
{
    require "option_change_group.php";
}
else
{

//- Check for return or any action on posts
    if($request->get('del','int')>0)
    {
        $ID=$request->get('del','int');
        $wpdb->query("delete from $table_name where blog='" . get_current_blog_id() . "' and ID='$ID'");
        
        $app->echo_message("<b>".__("The redirect is deleted successfully!",'wsr')."</b>",'success') ;
        $SR_redirect_cache->free_cache();
    }
    if($request->post('return')!='' && $request->post('post_operation')!='trash')
    {
        echo "<div style='text-align: center'></br><br/><h4><span class=\"glyphicon glyphicon-refresh\"></span>".__("Redirecting you back, please wait ...",'wsr')."</h4></br></br></div>";
        $misc->js_redirect($request->post('return'));
    }
    elseif($request->get('return')!='' && $request->post('post_operation')!='trash')
    {
        echo "<div style='text-align: center'></br><br/><h4><span class=\"glyphicon glyphicon-refresh\"></span>".__("Redirecting you back, please wait ...",'wsr')."</h4></br></br></div>";
        $misc->js_redirect(urldecode($request->get('return')));
    }
//- List Delete Code  ------------------------------------

    $check = new bcheckbox_option();
    $current_link=$request->get_current_parameters(array('del','search','page_num','add','edit'));
    $no_group_current_link=$request->get_current_parameters(array('del','search','page_num','add','edit','grpID'));
    if($request->get('grpID')=='')
    {
        $current_link=$no_group_current_link;
    }

    if($request->post('search')!='')
    {
        echo "<script>window.location='" . $current_link . '&search=' . $request->post('search','title') . "'</script>";
    }
    ?>

    <script type="text/javascript">
        function go_search(){
            var sword = encodeURIComponent(document.getElementById('search').value);
            if(sword!=''){
                window.location = "<?php echo $current_link?>&search=" + sword ;
            }else
            {
                alert("<?php _e('Please input any search words!','wsr') ?>");
                document.getElementById('search').focus();
            }
        }
        function go_group(){
            var sword = document.getElementById('grpID').value;
            window.location = "<?php echo $no_group_current_link?>&grpID=" + sword ;
        }
    </script>
<?php

//- Delete Code  ------------------------------------

    if($request->post('sel_items')!='' && $request->post('change_group')=='')
    {
        $IDs=$request->post('sel_items');
        $wpdb->query("delete from $table_name where blog='" . get_current_blog_id() . "' and ID in ($IDs)");
        $count = count(explode(',',$IDs));
        if($count>1)
        {
            $app->echo_message("<b>$count".__(" Redirects are deleted successfully!",'wsr')."</b>",'success') ;
        }else
        {
            $app->echo_message("<b>$count".__(" Redirect is deleted successfully!",'wsr')."</b>",'success') ;

        }
        $SR_redirect_cache->free_cache();
    }
    elseif($request->post('change_group')!='' && $request->post('sel_items')=='')
    {
        
        $app->echo_message("<b>".__("Please select the redirects you need to apply the group change on them!",'wsr')."</b>",'error') ;
    }

//- Change Group Code  ------------------------------------

    if($request->post('save_groups')!='' && $request->post('change_ids')!='')
    {
        $IDs=$request->post('change_ids');
        $change_grpID=$request->post('grpID');
        $wpdb->query("update $table_name set grpID=$change_grpID where blog='" . get_current_blog_id() . "' and ID in ($IDs)");
        
        $app->echo_message("<b>".__("Group is changed successfully!",'wsr')."</b>",'success') ;
    }

    ?>
<br/>
<form id="myform" action="" method="post" class="form-horizontal" role="form">
    <div class="form-group">

        <div class="col-sm-5">
            <a href="<?php echo $current_link;?>&add=1" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add New','wsr');?></a>
            <button type="submit" name="change_group" value="change_group" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-link"></span> <?php _e('Change Group','wsr');?></button>
            
            <a href="#" onclick="if(confirm('<?php _e('Are you sure you want to delete the selected redirects?','wsr') ?> ')) document.getElementById('myform').submit();" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span> <?php _e('Delete','wsr');?></a>
        </div>

        <div style="text-align: right; vertical-align: middle" class="col-sm-7">
            <?php
            $cat="link";
            if($redirect_manager_tab == 'redirects_rules')
            {
                $cat="special";
            }
            $drop = new dropdown_list('grpID');
            $drop->onchange("go_group()");
            $drop->add(__('All Groups','wsr'),'');
            $groups = $wpdb->get_results("select * from `".  SR_database::WP_SEO_Groups() . "` where blog='" . get_current_blog_id() . "' order by group_type desc;");
                foreach ( $groups as $group ) {

                    $count= $wpdb->get_var("select count(*) as cnt from `$table_name` where cat='$cat' and grpID=" . $group->ID);
                    $drop->add($group->group_title . ' (' . $count . ')' ,$group->ID);
                }
            $drop->run($SR_jforms);
            $drop->select($request->get('grpID'));
            ?>
               <input onkeyup="if (event.keyCode == 13) go_search();" value="<?php echo $request->get('search','textbox');?>" type="text" style="max-width: 200px; height: 30px;" class="small_text_box" id="search" name="search" placeholder="<?php _e('Search Keywords','wsr');?>" >
                <a style="height: 31px; " href="javascript:go_search();" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-search"></span> <?php _e('Search','wsr');?></a>
            <a style="height: 31px; " href="<?php echo $request->get_current_parameters(array('search'));?>" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th-list"></span> <?php _e('All','wsr');?></a>
        </div>

    </div>
        <div class="form-group">
            <div class="col-sm-12">
<!-- ***** Table  *******************************************************  !-->                
            <table class="table table-bordered table-hover table-striped">
                <thead>
                <tr>
                    <th class="btn btn-default table_header toolcell"><?php
                        $check->set_group("sel_items");
                        $check->set_primary_style();
                        $check->create_check_all_option();
                        ?></th>
                    <th class="btn btn-default table_header toolcell"><?php _e('Edit','wsr');?></th>
                    <th class="btn btn-default table_header"><?php _e('Redirect From / To','wsr');?></th>
                    
				
                    
                    <th style="width: 50px; text-align: center" class="btn btn-default table_header "><?php _e('Type','wsr');?></th>
                    <th style="width: 80px; text-align: center" class="btn btn-default table_header "><?php _e('Hits','wsr');?></th>
                    <th style="width: 170px; text-align: center" class="btn btn-default table_header "><?php _e('Last Access','wsr');?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                    
                    $where="where cat='$cat' and blog='" . get_current_blog_id() . "' ";
                    if($request->get('search')!='')
                    {
                        $search = urldecode($request->get('search'));
                        $where = $where . " and (redirect_from like '%%$search%%' or redirect_to like '%%$search%%' or redirect_type like '%%$search%%'  )";
                    }

                    if($request->get('grpID')!='')
                    {
                        $grpID = $request->get('grpID');
                        $where = $where . " and (grpID=$grpID)";
                    }

                    $where = $where . " order by ID desc";
                    $pagination= new clogica_pagination($table_name,$where);
                    $rows_per_page = ($options->read_option_value('pagination_limit') >0) ? $options->read_option_value('pagination_limit') : 10;
					
                    $pagination->set_rows($rows_per_page);
					
                    $limit = $pagination->get_sql_limit();
                    $redirects = $wpdb->get_results("select ID, redirect_from, redirect_from_type, enabled, redirect_to,  redirect_to_type, redirect_type,  hits, access_date from $table_name $where $limit ");

                    $i=0;
                    foreach($redirects as $redirect){
                    $i++;
                ?>
                <tr>
                    <td scope="row" class="toolcell"><?php $check->create_grouped_option($redirect->ID)?></td>
                    <td class="toolcell">
                        <a class="btn btn-primary btn-xs tool" href="<?php echo $request->get_current_parameters(array('edit'));?>&edit=<?php echo $redirect->ID; ?>"><span aria-hidden="true" class="fa fa-pencil"></span></a>
                    </td>
                    <td style="vertical-align: middle;">
                    <span class="<?php echo $redirect->redirect_from_type ?>_<?php echo $redirect->enabled ?>"></span><?php if($redirect->redirect_from_type == 'Page' || $redirect->redirect_from_type == 'Folder' ){ ?><a href="<?php echo $request->make_absolute_url($redirect->redirect_from);?>" target="_blank"><span class="fa fa-external-link-square"></span></a> <?php }?>
                    <?php if($redirect->redirect_from_type == 'Replace'){echo '<b>Replacing </b> "' . $redirect->redirect_from .'" <b>With </b>"'.$redirect->redirect_to . '"'; }elseif($redirect->redirect_from_type == 'Regex'){ echo SR_redirect_manager::regex_strip_slashes($redirect->redirect_from); }else{ echo $redirect->redirect_from;}?>
					
                    <?php if($redirect_manager_tab != 'redirects_rules'){?>
                    <br/>
                     <span class="<?php echo $redirect->redirect_to_type ?>_<?php echo $redirect->enabled ?>"></span><?php if($redirect->redirect_to_type == 'Page' || $redirect->redirect_to_type == 'Folder' ){ ?><a href="<?php echo $request->make_absolute_url($redirect->redirect_to);?>" target="_blank"><span class="fa fa-external-link-square"></span></a> <?php }?>
                     
                       <?php
                        echo $redirect->redirect_to;
                        ?>
                     
                    <?php }?>
                    </td>
                
                    <td style="font-size:14px; vertical-align: middle; text-align: center;"><?php echo $redirect->redirect_type;?></td>
                    <td style="width: 80px; vertical-align: middle; text-align: center; color:green; font-size:14px"><?php echo $redirect->hits; ?></td>
                    <td style="width: 170px; vertical-align: middle; text-align: center">
                    <?php  if($redirect->access_date !=0) echo $redirect->access_date ; ?></td>
                </tr>
                <?php } if($i==0){ ?>
                <tr><td colspan="7" style="text-align: center"> <?php _e('No data available!','wsr');?></td></tr>
                <?php } ?>

                </tbody>
            </table>
<!-- ***** Table  *******************************************************  !-->                
            </div>
            <?php $pagination->run();?>
        </div>
         <div style="text-align: right"><?php _e(' * Need Help?','wsr'); ?>  <a target="_blank" href="http://www.clogica.com/kb/adding-redirects.htm"><?php _e('click here to see how to add redirects','wsr'); ?></a></div>

</form>

<?php
}

$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts();
$SR_jforms->run();
