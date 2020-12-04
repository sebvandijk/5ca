                
        <?php if(0) { ?>      
            <table class="table table-bordered table-hover table-striped">
                <thead>
                <tr>
                    <th class="btn btn-default table_header toolcell"><?php
                        $check->set_group("sel_items");
                        $check->set_primary_style();
                        $check->create_check_all_option();
                        ?></th>
                    <th class="btn btn-default table_header toolcell"><?php _e('Edit','wsr');?></th>
                    <th class="btn btn-default table_header"><?php _e('Redirect from/to','wsr');?></th>
                    
				
                    
                    <th style="width: 50px; text-align: center" class="btn btn-default table_header "><?php _e('Type','wsr');?></th>
                    <th style="width: 80px; text-align: center" class="btn btn-default table_header "><?php _e('Hits','wsr');?></th>
                    <th style="width: 170px; text-align: center" class="btn btn-default table_header "><?php _e('Last Access','wsr');?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                    if($redirect_manager_tab == 'redirects_rules')
                    {
                            $where_cond = " and redirect_from_type = 'Replace'";
                    }else{
                            $where_cond = " and redirect_from_type <> 'Replace'";
                    }
                    $where=" where cat='link' and blog='" . get_current_blog_id() . "' ".$where_cond;
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
                    $redirects = $wpdb->get_results("select ID, redirect_from, redirect_from_type, enabled, redirect_to,  redirect_to_type, redirect_type,  (SELECT count(ID) FROM " . SR_database::WP_SEO_Redirection_LOG() . " where `rfrom` = redirect_from and `rto` = redirect_to ) as redirect_hits, (SELECT `ctime`  FROM " . SR_database::WP_SEO_Redirection_LOG() . " where `rfrom` = redirect_from and `rto` = redirect_to  order by `ctime` desc limit 1) as redirect_last_access from $table_name $where $limit ");

                    $i=0;
                    foreach($redirects as $redirect){
                        $hits_cnt=$wpdb->get_var("SELECT count(ID) FROM ". SR_database::WP_SEO_Redirection_LOG() ." where rID=".$redirect->ID);
                       $last_access_date = $wpdb->get_var("SELECT `ctime` FROM ". SR_database::WP_SEO_Redirection_LOG() ." where rID=".$redirect->ID." order by `ctime` desc limit 1");
                        $i++;
                ?>
                <tr>
                    <td scope="row" class="toolcell"><?php $check->create_grouped_option($redirect->ID)?></td>
                    <td class="toolcell">
                        <a class="btn btn-primary btn-xs tool" href="<?php echo $request->get_current_parameters(array('edit'));?>&edit=<?php echo $redirect->ID; ?>"><span aria-hidden="true" class="fa fa-pencil"></span></a>
                    </td>
                    <td style="vertical-align: middle;">
                    <span class="<?php echo $redirect->redirect_from_type ?>_<?php echo $redirect->enabled ?>"></span><?php if($redirect->redirect_from_type == 'Page' || $redirect->redirect_from_type == 'Folder' ){ ?><a href="<?php echo $request->make_absolute_url($redirect->redirect_from);?>" target="_blank"><span class="fa fa-external-link-square"></span></a> <?php }?>
                    <?php if($redirect->redirect_from_type == 'Replace'){echo '<b>Replacing </b> "' . $redirect->redirect_from .'" <b>With </b>"'.$redirect->redirect_to . '"'; }else{ echo $redirect->redirect_from;}?>
					
                    <?php if($redirect_manager_tab != 'redirects_rules'){?>
                    <br/>
                     <span class="<?php echo $redirect->redirect_to_type ?>_<?php echo $redirect->enabled ?>"></span><?php if($redirect->redirect_to_type == 'Page' || $redirect->redirect_to_type == 'Folder' ){ ?><a href="<?php echo $request->make_absolute_url($redirect->redirect_to);?>" target="_blank"><span class="fa fa-external-link-square"></span></a> <?php }?>
                     
                       <?php
                        echo $redirect->redirect_to;
                        ?>
                     
                    <?php }?>
                    </td>
                
                    <td style="font-size:14px; vertical-align: middle; text-align: center;"><?php echo $redirect->redirect_type;?></td>
                    <td style="width: 80px; vertical-align: middle; text-align: center; color:green; font-size:14px"><?php print_r($hits_cnt); ?></td>
                    <td style="width: 170px; vertical-align: middle; text-align: center">
                    <?php echo $last_access_date; ?></td>
                </tr>
                <?php } if($i==0){ ?>
                <tr><td colspan="7" style="text-align: center"> <?php _e('No data available!','wsr');?></td></tr>
                <?php } ?>

                </tbody>
            </table>
        <?php } ?> 




<table class="table table-bordered table-hover table-striped">
                <thead>
                <tr>
                    <th class="btn btn-default table_header toolcell"><?php
                        $check->set_group("sel_items");
                        $check->set_primary_style();
                        $check->create_check_all_option();
                        ?></th>
                    <th class="btn btn-default table_header toolcell">Edit</th>
                    <th class="btn btn-default table_header">Redirect from</th>
                    <th class="btn btn-default table_header">Redirect to</th>
                    <th style="width: 85px; text-align: center" class="btn btn-default table_header ">Type</th>
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
                    $pagination->set_rows(10);
                    $limit = $pagination->get_sql_limit();
                    $redirects = $wpdb->get_results("select * from $table_name $where $limit ");
                    $i=0;
                    foreach($redirects as $redirect){
                        $i++;
                ?>
                <tr>
                    <td scope="row" class="toolcell"><?php $check->create_grouped_option($redirect->ID)?></td>
                    <td class="toolcell">
                        <a class="btn btn-primary btn-xs tool" href="<?php echo $request->get_current_parameters(array('edit'));?>&edit=<?php echo $redirect->ID; ?>"><span aria-hidden="true" class="fa fa-pencil"></span></a>
                    </td>
                    <td><span class="<?php echo $redirect->redirect_from_type ?>_<?php echo $redirect->enabled ?>"></span><?php if($redirect->redirect_from_type == 'Page' || $redirect->redirect_from_type == 'Folder' ){ ?><a href="<?php echo $request->make_absolute_url($redirect->redirect_from);?>" target="_blank"><span class="fa fa-external-link-square"></span></a> <?php }?><?php if($redirect->redirect_from_type == "Replace") echo "Search for: "; ?><?php echo $redirect->redirect_from; ?></td>
                    <td><span class="<?php echo $redirect->redirect_to_type ?>_<?php echo $redirect->enabled ?>"></span><?php if($redirect->redirect_to_type == 'Page' || $redirect->redirect_to_type == 'Folder' ){ ?><a href="<?php echo $request->make_absolute_url($redirect->redirect_to);?>" target="_blank"><span class="fa fa-external-link-square"></span></a> <?php }?><?php if($redirect->redirect_from_type == "Replace") echo "Replace with: "; ?><?php echo $redirect->redirect_to; ?></td>
                    <td style="width: 85px; text-align: center"><?php echo $redirect->redirect_type; ?></td>
                </tr>
                <?php } if($i==0){ ?>
                <tr><td colspan="5" style="text-align: center"> No data available!</td></tr>
                <?php } ?>
                </tbody>
            </table>