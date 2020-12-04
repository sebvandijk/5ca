<?php
global $wpdb;

$request = SRP_PLUGIN::get_request();
$misc = SRP_PLUGIN::get_misc();
$app = SRP_PLUGIN::get_app();
$options = SRP_PLUGIN::get_options();

$SR_jforms = new jforms();
$SR_redirect_cache = new clogica_SR_redirect_cache();
$current_link=$request->get_current_parameters(array('del','search','page_num','add','edit'));


//- Add Update Code  ------------------------------------
if($request->post('add')!='' || $request->post('edit','int')>0)
{

    $redirect_from= $request->make_relative_url($request->post('redirect_from')) ;
    $redirect_to=$request->make_relative_url($request->post('redirect_to'));
    $redirect_type=$request->post('redirect_type');
    $redirect_from_type=$request->post('redirect_from_type');
    $enabled=$request->post('enabled');

    if(($request->post('edit','int')>0 && $wpdb->get_var("select ID from " . SR_database::WP_SEO_Redirection() . " where redirect_from='$redirect_from' and cat='404rule' and blog='" . get_current_blog_id() . "'")==$request->post('edit','int') ) || ($redirect_from=='' && $wpdb->get_var("select redirect_from_type from " . SR_database::WP_SEO_Redirection() . " where redirect_from_type='$redirect_from_type' and cat='404rule' and blog='" . get_current_blog_id() . "' ")!=$redirect_from_type ) || ( $redirect_from!='' && $wpdb->get_var("select redirect_from from " . SR_database::WP_SEO_Redirection() . " where redirect_from='$redirect_from' and cat='404rule' and blog='" . get_current_blog_id() . "'")!=$redirect_from) )
    {
        if($redirect_from_type == 'CMS'){
            $redirect_from = "/";
        } else if($redirect_from_type == 'CSE') {
            $redirect_from = 'SearchBot';
        }

        $regex="";
        if($redirect_from_type =='CSS' || $redirect_from_type =='CMS' || $redirect_from_type =='CSF' )
        {
            $regex= '^' . $misc->regex_prepare($redirect_from) . '.*$';
        } elseif($redirect_from_type =='Folder')
        {
            $regex= '^' . $misc->regex_prepare($redirect_from) . '.*$';

        } elseif($redirect_from_type =='Regex')
        {
            $regex= $redirect_from;
        } else if($redirect_from_type =='Contain')
        {
            $regex= '^.*' . $misc->regex_prepare($redirect_from) . '.*$';
        } else if($redirect_from_type =='StartWith')
        {
            $regex= '^' . $misc->regex_prepare($redirect_from) . '.*$';
        } else if($redirect_from_type =='EndWith')
        {
            $regex= '^.*' . $misc->regex_prepare($redirect_from) . '$';
        } else if($redirect_from_type =='Filetype')
        {
            $regex= '^.*' . $misc->regex_prepare( '.' . $redirect_from) . '$';
        }

        if($request->post('edit')!='')
        {
            $ID = $request->post('edit','int');
            $wpdb->query(" update " .  SR_database::WP_SEO_Redirection() . " set redirect_from='$redirect_from',redirect_to='$redirect_to',redirect_type='$redirect_type',redirect_from_type='$redirect_from_type' , regex='$regex',enabled='$enabled'  where blog='" . get_current_blog_id() . "' and ID=" . $ID);
            SR_redirect_manager::clear_fixed_404($redirect_from, $regex);
            $app->echo_message("<b>".__("The 404 rule is updated successfully!",'wsr')."</b>",'success') ;
            $SR_redirect_cache->free_cache();

        }else
        {
            $cat='404rule';
            $wpdb->query(" insert into " . SR_database::WP_SEO_Redirection() . "(redirect_from,redirect_to,redirect_type,cat,redirect_to_type,redirect_from_type,regex,enabled,blog) values('$redirect_from','$redirect_to','$redirect_type','404rule','Page','$redirect_from_type','$regex','$enabled','" . get_current_blog_id() . "') ");
            SR_redirect_manager::clear_fixed_404($redirect_from, $regex);
            
            $app->echo_message("<b>".__("New redirect is added successfully!",'wsr')."</b>",'success') ;
            $SR_redirect_cache->free_cache();
        }

    }else
    {
        
        $app->echo_message("<b>".__("This 404 rule is already exists!",'wsr')."</b>",'danger') ;

    }

}

//- Delete Code  ------------------------------------


if($request->post('sel_items')!='')
{
    $IDs=$request->post('sel_items');
    $wpdb->query("delete from " . SR_database::WP_SEO_Redirection() . " where blog='" . get_current_blog_id() . "' and ID in ($IDs)");
    $count = count(explode(',',$IDs));
    if($count>1)
    {
        $app->echo_message("<b>$count ".__('Rules are deleted successfully!')."</b>",'success') ;
    }else
    {
        $app->echo_message("<b>$count ".__('Rule is deleted successfully!')."</b>",'success') ;
    }
}
//- Add Delete Forms  ------------------------------------
if($request->get('add')!='' || $request->get('edit')!='' ) {
    require "option_404_rules_add_edit.php";
}else {
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
    </script>
    <br/>
    <form id="myform" action="" method="post" class="form-horizontal" role="form">
        <div class="form-group">

            <div class="col-sm-5">
                <a href="<?php echo $current_link;?>&add=1" class="btn btn-default btn-sm"><span
                        class="glyphicon glyphicon-plus"></span> <?php _e('Add New','wsr') ?></a>
                <a href="#"
                   onclick="if(confirm('<?php _e('Are you sure you want to delete the selected redirects?','wsr') ?> ')) document.getElementById('myform').submit();"
                   class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span> Delete</a>
            </div>

            <div style="text-align: right; vertical-align: middle" class="col-sm-7">
                <input onkeyup="if (event.keyCode == 13) go_search();" value="<?php echo htmlentities($request->get('search')); ?>"
                       type="text" style="max-width: 200px; height: 30px;" class="small_text_box" id="search"
                       name="search" placeholder="<?php _e('Search Keywords','wsr') ?>">
                <a style="height: 31px; " href="javascript:go_search();" class="btn btn-default btn-sm"><span
                        class="glyphicon glyphicon-search"></span><?php _e('Search','wsr') ?> </a>
                <a style="height: 31px; " href="<?php echo $request->get_current_parameters(array('search'));?>"
                   class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th-list"></span> <?php _e('All','wsr') ?></a>
            </div>

        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                    <tr>
                        <th class="btn btn-default table_header toolcell"><?php
                            $check = new bcheckbox_option();
                            $check->set_group("sel_items");
                            $check->set_primary_style();
                            $check->create_check_all_option();
                            ?></th>
                        <th class="btn btn-default table_header toolcell"><?php _e('Edit','wsr')?></th>
                        <th class="btn btn-default table_header"><?php _e('Rule','wsr')?></th>
                        <th class="btn btn-default table_header"><?php _e('Redirect to','wsr')?></th>
                        <th style="width: 85px; text-align: center" class="btn btn-default table_header "><?php _e('Type','wsr')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    $where = "where cat='404rule' and blog='" . get_current_blog_id() . "'";
                    if ($request->get('search') != '') {
                        $search = urldecode($request->get('search'));
                        $where = $where . " and (redirect_from like '%%$search%%' or redirect_to like '%%$search%%' or redirect_type like '%%$search%%'  )";
                    }


                    $where = $where . " order by ID desc";
                    $pagination = new clogica_pagination(SR_database::WP_SEO_Redirection(), $where);
					$rows_per_page = ($options->read_option_value('pagination_limit') >0) ? $options->read_option_value('pagination_limit') : 10;
					
                    $pagination->set_rows($rows_per_page);
                    $limit = $pagination->get_sql_limit();
                    $redirects = $wpdb->get_results("select * from " . SR_database::WP_SEO_Redirection() . " $where $limit ");
                    $i = 0;
                    foreach ($redirects as $redirect) {
                        $i++;
                        ?>
                        <tr>
                            <td scope="row"
                                class="toolcell"><?php $check->create_grouped_option($redirect->ID)?></td>
                            <td class="toolcell">
                                <a class="btn btn-primary btn-xs tool"
                                   href="<?php echo $request->get_current_parameters(array('edit'));?>&edit=<?php echo $redirect->ID; ?>"><span
                                        aria-hidden="true" class="fa fa-pencil"></span></a>
                            </td>
                            <td><span
                                    class="<?php echo $redirect->redirect_from_type ?>_<?php echo $redirect->enabled ?>"></span><?php if ($redirect->redirect_from_type == 'Page' || $redirect->redirect_from_type == 'Folder') { ?>
                                    <a href="<?php echo $request->make_absolute_url($redirect->redirect_from); ?>"
                                       target="_blank"><span class="fa fa-external-link-square"></span>
                                    </a> <?php }?><?php echo $redirect->redirect_from; ?></td>
                            <td><span
                                    class="<?php echo $redirect->redirect_to_type ?>_<?php echo $redirect->enabled ?>"></span><?php if ($redirect->redirect_to_type == 'Page' || $redirect->redirect_to_type == 'Folder') { ?>
                                    <a href="<?php echo $request->make_absolute_url($redirect->redirect_to); ?>"
                                       target="_blank"><span class="fa fa-external-link-square"></span>
                                    </a> <?php }?><?php echo $redirect->redirect_to; ?></td>
                            <td style="width: 85px; text-align: center"><?php echo $redirect->redirect_type; ?></td>
                        </tr>
                    <?php }
                    if ($i == 0) { ?>
                        <tr>
                            <td colspan="5" style="text-align: center"> <?php _e('No data available!','wsr'); ?></td>
                        </tr>
                    <?php } ?>

                    </tbody>
                </table>
            </div>
            <?php $pagination->run();?>
        </div>
    </form>

<?php
}
$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts();
$SR_jforms->run();

