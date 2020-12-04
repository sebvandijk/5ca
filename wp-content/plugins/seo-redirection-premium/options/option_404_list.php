<?php

global $wpdb;

$request = SRP_PLUGIN::get_request();
$options = SRP_PLUGIN::get_options();
$app = SRP_PLUGIN::get_app();
$security = SRP_PLUGIN::get_security();


$table_name = SR_database::WP_SEO_404_links();
$SR_jforms = new jforms();


$current_link=$request->get_current_parameters(array('del','search','page_num','add','edit'));
$no_link_type_current_link=$request->get_current_parameters(array('del','search','page_num','add','edit','link_type'));
$no_sort_current_link=$request->get_current_parameters(array('del','search','page_num','add','edit','sort'));
$no_shown_current_link=$request->get_current_parameters(array('del','search','page_num','add','edit','shown'));
$no_tabs_link = $request->get_current_parameters(array('del','search','page_num','add','edit','shown','sort','link_type','SR_tab'));
$no_country_current_link=$request->get_current_parameters(array('del','search','page_num','add','edit','country'));

 if($request->post('sel_items')!='')
    {
        $IDs=$request->post('sel_items');
        $wpdb->query("delete from $table_name where blog='" . get_current_blog_id() . "' and ID in ($IDs)");
        $count = count(explode(',',$IDs));
        if($count>1)
        {
            $app->echo_message("<b>$count".__(" Links are deleted successfully!",'wsr')."</b>",'success') ;
        }else
        {
            $app->echo_message("<b>$count".__(" Link is deleted successfully!",'wsr')."</b>",'success') ;

        }
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

        function go_link_type(){
            var sword = document.getElementById('link_type').value;
            window.location = "<?php echo $no_link_type_current_link?>&link_type=" + sword ;
        }

        function go_sort(){
            var sword = document.getElementById('sort').value;
            window.location = "<?php echo $no_sort_current_link?>&sort=" + sword ;
        }

        function go_shown(){
            var sword = document.getElementById('shown').value;
            window.location = "<?php echo $no_shown_current_link?>&shown=" + sword ;
        }

        function go_country(){
            var sword = document.getElementById('country').value;
            window.location = "<?php echo $no_country_current_link?>&country=" + sword ;
        }

// -- POPUP -------------------------------------------------------



    </script>
    <br/>
    <form id="myform" action="" method="post" class="form-horizontal" role="form">

        <div class="form-group">

            <div class="col-sm-7"> 
                <button onclick="show_popup(0)" type="button"class="btn btn-primary btn-sm"><span class="glyphicon glyphicon-share-alt"></span> Redirect</button>
                <?php
                $drop = new dropdown_list('sort');
                $drop->onchange("go_sort()");
                $drop->add(__('Order by Date','wsr'),'');
                $drop->add(__('Order by Views','wsr'),'views');
                $drop->add(__('Order by Type','wsr'),'type');
                $drop->run($SR_jforms);
                $drop->select($request->get('sort'));

                $drop = new dropdown_list('shown');
                $drop->onchange("go_shown()");
                $drop->add(__('Seen by Visitors','wsr'),'');
                $drop->add(__('Seen by Bots','wsr'),'bots');
                $drop->add(__('Show All','wsr'),'all');
                $drop->run($SR_jforms);
                $drop->select($request->get('shown'));
                ?>
                <a href="#" onclick="if(confirm('<?php _e('Are you sure you want to delete the selected links?','wsr') ?> ')) document.getElementById('myform').submit();" class="btn btn-danger btn-sm"><span class="glyphicon glyphicon-remove"></span> <?php _e('Delete','wsr');?></a>

            </div>

            <div style="text-align: right; vertical-align: middle" class="col-sm-5">
                <?php
                $drop = new dropdown_list('link_type');
                $drop->onchange("go_link_type()");
                $drop->add(__('All Types','wsr'),'');
                $drop->add(__('Links','wsr'),'1');
                $drop->add(__('Images','wsr'),'2');
                $drop->add(__('CSS/JS','wsr'),'3');
                $drop->add(__('Other Files','wsr'),'4');
                $drop->run($SR_jforms);
                $drop->select($request->get('link_type'));
                ?>
                <input onkeyup="if (event.keyCode == 13) go_search();" value="<?php echo htmlentities($request->get('search')); ?>" type="text" style="width: 120px; height: 30px;" class="small_text_box" id="search" name="search" placeholder="<?php _e('Search Keywords','wsr') ?>" >
                <a style="height: 31px; " href="javascript:go_search();" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-search"></span>"<?php _e('Search','wsr') ?></a>
                <a id="show_all" style="height: 31px; " href="<?php echo $request->get_current_parameters(array('search','shown','sort','link_type'));?>" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th-list"></span> <?php _e('All','wsr') ?></a>
            </div>
        </div>


        <div class="form-group">
            <div class="col-sm-12">
                <table class="table table-bordered table-hover table-striped data_grid">
                    <thead>
                    <tr>
                        <th class="btn btn-default table_header toolcell"><?php
                        $check = new bcheckbox_option();
                        $check->set_group("sel_items");
                        $check->set_primary_style();
                        $check->create_check_all_option();
                        ?></th>
                        <th class="btn btn-default table_header toolcell"><span class="btn btn-default btn-xs"><span aria-hidden="true" class="glyphicon glyphicon-share-alt"></span></span></th>
                        <th class="btn btn-default table_header" style="width: 140px"><?php _e('Discovered','wsr'); ?></th>
                        <th class="btn btn-default table_header"><?php _e('Link','wsr'); ?></th>
                        <th class="btn btn-default table_header toolcell"><span class="btn btn-default btn-xs"><span aria-hidden="true" class="glyphicon glyphicon-eye-open"></span></span></th>
                        <th class="btn btn-default table_header toolcell"><?php _e('Ref','wsr'); ?></th>
                        <th class="btn btn-default table_header" style="text-align: center"><?php _e('IP','wsr'); ?></th>
                        <th class="btn btn-default table_header" style="text-align: center"><?php _e('OS','wsr'); ?></th>
                        <th class="btn btn-default table_header" style="text-align: center"><?php _e('Browser','wsr'); ?></th>

                    </tr>
                    </thead>
                    <tbody>
                    <?php

                    $where="where blog='" . get_current_blog_id() . "' ";

                    if($request->get('search')!='')
                    {
                        $search = urldecode($request->get('search'));
                        if($where=="")
                        {
                            $where = " where (ctime like '%%$search%%' or link like '%%$search%%' or referrer like '%%$search%%' or ip like '%%$search%%' or country like '%%$search%%' or  os like '%%$search%%' or  browser like '%%$search%%' ) ";
                        }else
                        {
                            $where = $where . " and (ctime like '%%$search%%' or link like '%%$search%%' or referrer like '%%$search%%' or ip like '%%$search%%' or country like '%%$search%%' or  os like '%%$search%%' or  browser like '%%$search%%' ) ";
                        }
                    }

                    if($request->get('link_type')!='')
                    {
                        $link_type=$request->get('link_type');
                        if($where=="")
                        {
                            $where = " where link_type='$link_type' ";
                        }else
                        {
                            $where = $where . " and link_type='$link_type' ";
                        }
                    }


                    if($request->get('shown')!='all')
                    {
                        $shown=$request->get('shown');
                        $sql="(browser<>'GoogleBot' and browser<>'SearchBot' and os<>'GoogleBot' and os<>'SearchBot')";
                        if($request->get('shown')=='bots')
                        {
                            $sql="(browser='GoogleBot' or browser='SearchBot' or os='GoogleBot' or os='SearchBot')";
                        }

                        if($where=="")
                        {
                            $where = " where $sql ";
                        }else
                        {
                            $where = $where . " and $sql ";
                        }
                    }


                    $order = "order by ctime desc ";
                    if($request->get('sort')!='')
                    {
                        $sort = $request->get('sort');
                        if($sort == 'views')
                        {
                            $order=" order by counter desc ";
                        }
                        else if($sort == 'type')
                        {
                            $order=" order by link_type asc ";
                        }
                    }

                    $pagination= new clogica_pagination($table_name,$where);		
                    $rows_per_page = ($options->read_option_value('pagination_limit') >0) ? $options->read_option_value('pagination_limit') : 10;
					
                    $pagination->set_rows($rows_per_page);
                    $limit = $pagination->get_sql_limit();
                    $links_404 = $wpdb->get_results("select * from " . SR_database::WP_SEO_404_links() . " $where $order $limit ");
                    $i=0;
                    foreach($links_404 as $link){
                        $i++;
                        ?>
                        <tr id="tr_<?php echo $link->ID;?>">
                            <td scope="row" class="toolcell"><?php $check->create_grouped_option($link->ID)?></td>
                            <td class="toolcell">
                                <button onclick="show_popup(<?php echo $link->ID;?>)" type="button" class="btn btn-success btn-xs tool" title="<?php _e("Redirect this link",'wsr') ?>"><span aria-hidden="true" class="glyphicon glyphicon-share-alt"></span></button>
                            </td>
                            <td style="width: 140px"><?php echo $link->ctime; ?></td>
                            <td><a href="<?php echo $request->make_absolute_url($link->link);?>" target="_blank"><span class="fa fa-external-link-square"></span></a> <span id="link_<?php echo $link->ID;?>"><?php echo $link->link;?></span></td>
                            <td class="toolcell"><?php echo $link->counter;?></td>
                            <td class="toolcell">
                                <?php if($link->referrer!=''){?>
                                <a target="_blank" class="btn btn-primary btn-xs tool" href="<?php echo $link->referrer; ?>" title="Referrer: <?php echo $link->referrer; ?>"><span aria-hidden="true" class="glyphicon glyphicon-link"></span></a>
                                <?php }else{ ?>
                                    <label class="btn btn-default btn-xs tool disabled"><span aria-hidden="true" class="glyphicon glyphicon-link"></span></label>
                                <?php } ?>

                            </td>
                            <td style="width: 110px; text-align: center"><?php



                            if($options->read_option_value('ip_logging_status') == 0)
                            {
                                    $new_ipaddress = ''; 
                            }else if($options->read_option_value('ip_logging_status') == 2) // mask last 3 digits
                            {
                                    $new_ipaddress = preg_replace('/([0-9]+\\.[0-9]+\\.[0-9]+)\\.[0-9]+/', '\\1.***', $link->ip);

                            }else{
                                    $new_ipaddress = '<a target="_blank" href="https://tools.keycdn.com/geo?host='.$link->ip.'">'.$link->ip.'</a>';


                            }


                            echo $new_ipaddress;?></td>
                  
                            <td style="width: 90px; text-align: center"><?php echo $link->os;?></td>
                            <td style="width: 120px; text-align: center"><?php echo $link->browser;?></td>
                        </tr>
                    <?php } if($i==0){ ?>
                        <tr><td colspan="9" style="text-align: center"><?php _e('No data available!','wsr');?></td></tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php $pagination->run();?>

        </div>
        <div style="text-align: right"><?php _e('* Too many 404 errors?','wsr'); ?> <a target="_blank" href="http://www.clogica.com/kb/too-many-404-errors.htm"><?php _e('click here to see why?','wsr'); ?></a></div>

    </form>
        
    
<!-- Modal -->
<div class="modal fade" id="add_redirect" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle"><b>Redirect 404 Links</b></h5>
      </div>
      <div class="modal-body">
          
          <form id="redirect_404_form">
            <div class="form-group">
                <label for="redirect_url">URL: <span class="btn btn-default btn-sm" id="redirect_url"></span></label>
              <input type="hidden" class="form-control" name="selected_ids" id="selected_ids">
            </div>
            <div class="form-group">
                <label for="redirect_to">Redirect to</label>
                <input type="text" class="form-control" id="redirect_to" name="redirect_to" placeholder="Redirect to">
                <p id="redirect_to_msg" style="color: red; font-weight: bold; display: none"></p>
            </div>
            <div class="form-group">
                <label for="search_posts">Search Posts</label>
                <div class="input-group">
                    <input type="text" onkeyup="search_key_up(event.keyCode);" class="form-control" id="search_posts" placeholder="Search keywords ..."><a type="button" style="cursor: pointer" onclick="search_posts()" class="input-group-addon"><i class="glyphicon glyphicon-search"></i></a> 
                </div>    
            </div>
              <div id="datafetch">                
                  
              </div>              
                     <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo $nonce = wp_create_nonce('seoredirectionpremium'); ?>" /> 
          </form>
      </div>
      <div class="modal-footer">
        <input type="hidden" name="ajax_url" id="ajax_url" value="<?php echo admin_url('admin-ajax.php');?>"/>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button id="save_button" onclick="save_values()" type="button" class="btn btn-primary">Save Redirect</button>
      </div>
    </div>
  </div>
</div>


<?php
$SR_jforms->add_script("
$('#search').focusin(function() {
$('#show_all').hide();
$('#search').animate({width: '180px'});
});

$('#search').focusout(function() {
$('#show_all').show();
$('#search').animate({width: '130px'});
});

");
$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts();
$SR_jforms->run();
