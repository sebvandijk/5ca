<?php
global $wpdb ;

$request = SRP_PLUGIN::get_request();
$app = SRP_PLUGIN::get_app();

$SR_jforms = new jforms();
$SR_redirect_cache = new clogica_SR_redirect_cache();

function csv_arr($file_name)
{
    $arrResult = array();
    $handle = fopen($file_name, "r");
    if( $handle ) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $arrResult[] = $data;
        }
        fclose($handle);
    }
    return $arrResult;
}

function add_csv_mime_upload_mimes( $existing_mimes ){
    $existing_mimes['csv'] = 'application/octet-stream'; //allow CSV files
    return $existing_mimes;
}

if($request->post('btn_import')!='')
{
    add_filter('upload_mimes', 'add_csv_mime_upload_mimes');

    if(array_key_exists('import_file',$_FILES) && $_FILES['import_file']['name']!='')
    {
        $filename = $_FILES['import_file']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(strtolower($ext)=='csv')
        {
            if ( ! function_exists( 'wp_handle_upload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
            }

            $uploadedfile=$_FILES['import_file'];
            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload($uploadedfile,$upload_overrides);
            if ( $movefile && !isset( $movefile['error'] ) ) {
                $app->echo_message(__("File is valid, and was successfully uploaded.",'wsr'));
                $results=csv_arr($movefile['file']);

                // start add to database ----------------------------------

                $index=0;
                if($request->post('col_names','int')!=0) $index++;
                $errors=0;
                $exist=0;
                $new=0;
                $grpID=$request->post('grpID','int');

                for($i=$index;$i<count($results);$i++)
                {
                    $sql="";
                    $redirect_from_type='Page';
                    $redirect_to_type='Page';
                    $redirect_from_folder_settings = '1';
                    $redirect_from_subfolders='0';
                    $redirect_to_folder_settings='1';
                    $redirect_type='301';
                    $regex='';
                    $redirect_from='';
                    $redirect_to='';

                    if(count($results[$i])>0)
                        $redirect_from=$results[$i][0];

                    if(count($results[$i])>1)
                        $redirect_to=$results[$i][1];

                    if(count($results[$i])>2)
                        $redirect_type=$results[$i][2];

                    if(count($results[$i])>3)
                        $redirect_from_type=$results[$i][3];

                    if(count($results[$i])>4)
                        $redirect_from_folder_settings=$results[$i][4];

                    if(count($results[$i])>5)
                        $redirect_from_subfolders=$results[$i][5];

                    if(count($results[$i])>6)
                        $redirect_to_type=$results[$i][6];

                    if(count($results[$i])>7)
                        $redirect_to_folder_settings=$results[$i][7];

                    if(count($results[$i])>8)
                        $regex=$results[$i][8];

                    if($redirect_from!='' && $redirect_to!='' && intval($redirect_type)!=0)
                    {
                        $exec=0;
                        if($wpdb->get_var(" select redirect_from from ". SR_database::WP_SEO_Redirection() ." where redirect_from='$redirect_from' and cat='link' and blog='" . get_current_blog_id() . "' "))
                        {
                            $exist++;
                            if($request->post('rule')=='replace')
                            {
                                $wpdb->get_var(" delete from ". SR_database::WP_SEO_Redirection() ." where redirect_from='$redirect_from' and cat='link' and blog='" . get_current_blog_id() . "' ");
                                $exec=1;
                            }
                        }else
                        {
                            $exec=1;
                            $new++;
                        }
                        
                        if($exec==1){                            
                            $wpdb->insert(SR_database::WP_SEO_Redirection(), array(
                                "redirect_from" => $redirect_from ,
                                "redirect_to" => $redirect_to ,
                                "redirect_type" => $redirect_type ,
                                "redirect_from_type" => $redirect_from_type ,
                                "redirect_from_folder_settings" => $redirect_from_folder_settings ,
                                "redirect_from_subfolders" => $redirect_from_subfolders ,
                                "redirect_to_type" => $redirect_to_type ,
                                "redirect_to_folder_settings" => $redirect_to_folder_settings,
                                "regex" => $regex ,
                                "cat" => 'link' ,
                                "grpID" => $grpID ,
                                "blog" => get_current_blog_id()
                            ));
                        }
                        
                    }else
                    {
                        $errors++;
                    }

                }

                $report= intval($errors+$exist+$new) . " redirects are imported with $errors errors,$new new redirects and $exist are ";
                if($request->post('rule')=='replace')
                {
                    $report= $report . 'replaced!';
                }else
                {
                    $report= $report . 'skipped!';
                }

                $app->echo_message($report);

                // end the entrance to database ---------------------------


                unlink($movefile['file']);
                $app->echo_message(__("File is deleted!",'wsr'));
                $SR_redirect_cache->free_cache();

            } else {
                echo $movefile['error'];
            }

        }else
        {
            $app->echo_message(__("Please choose a CSV file",'wsr'),'danger');
        }

    }else
    {
        $app->echo_message(__("You need to select a file to upload it!",'wsr'),'danger');
    }
}


?>

<h4><?php _e("Export Redirects",'wsr')?></h4><hr/>


    <form id="export" target="_blank" action="<?php echo $app->get_plugin_url();?>custom/export/csv.php" method="post" class="form-horizontal" role="form" data-toggle="validator">

                <div class="form-group">
                    <label class="control-label col-sm-2" for="export_file_type"><?php _e("Output Type:",'wsr')?></label>
                    <div class="col-sm-5">
                        <?php
                        $drop = new dropdown_list('export_file_type');
                        $drop->add(__('CSV','wsr'), 'csv');
                        $drop->run($SR_jforms);
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label col-sm-2" for="grpID"><?php _e("Redirects:",'wsr')?></label>
                    <div class="col-sm-5">
                        <?php
                        $drop = new dropdown_list('grpID');
                        $drop->add(__('All Groups','wsr'),'');
                        $groups = $wpdb->get_results("select * from `" . SR_database::WP_SEO_Groups() . "` where blog='" . get_current_blog_id() . "'  order by group_type desc;");
                        foreach ( $groups as $group ) {

                            $count= $wpdb->get_var("select count(*) as cnt from `" . SR_database::WP_SEO_Redirection() . "` where cat='link' and blog='" . get_current_blog_id() . "'  and grpID=" . $group->ID);
                            $drop->add($group->group_title . ' (' . $count . ')' ,$group->ID);
                        }
                        $drop->run($SR_jforms);
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-12">
                        <input type="hidden" name="blog" value="<?php echo get_current_blog_id()?>"/>
                        <button type="submit" class="btn btn-primary btn-sm" name="btn_export" value="btn_export"><span class="glyphicon glyphicon-export"></span><?php _e("Export",'wsr'); ?></button>
                    </div>
                </div>
    </form>

<h4><?php _e("Import Redirects",'wsr'); ?></h4><hr/>

    <form id="import" name="import" enctype='multipart/form-data' action="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
        <input type="hidden" name="MAX_FILE_SIZE" value="999000000" />
        <div class="form-group">
            <label class="control-label col-sm-2" for="import_file_type"><?php _e("File Type:",'wsr') ?></label>
            <div class="col-sm-5">
                <?php
                $drop = new dropdown_list('import_file_type');
                $drop->add(__('CSV','wsr'), 'csv');
                $drop->run($SR_jforms);
                ?>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="file"><?php _e("Choose File:",'wsr');?></label>
            <div class="col-sm-3">
               <input class="btn btn-default btn-sm" type="file" accept="text/csv" id="import_file" name="import_file" required/>
            </div>
			<div class="col-sm-3">
               <a target="_blank" href="http://www.clogica.com/kb/topics/seo-redirection-premium/export-import"><?php _e('Export/Import Redirects Tutorial ','wsr');?></a>
            </div>
        </div>


        <div class="form-group">
            <label class="control-label col-sm-2" for="grpID"><?php _e("Save to:",'wsr'); ?></label>
            <div class="col-sm-5">
                <?php
                $drop = new dropdown_list('grpID');
                $groups = $wpdb->get_results("select * from `" . SR_database::WP_SEO_Groups() . "` where blog='" . get_current_blog_id() . "'  order by group_type desc;");
                foreach ( $groups as $group ) {

                    $count= $wpdb->get_var("select count(*) as cnt from `" . SR_database::WP_SEO_Redirection() . "` where cat='link' and grpID=" . $group->ID);
                    $drop->add($group->group_title . ' (' . $count . ')' ,$group->ID);
                }
                $drop->run($SR_jforms);
                ?>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="Rule"><?php _e("Column Titles:",'wsr')?></label>
            <div class="col-sm-5">
                <?php
                    $check = new bcheckbox_option();
                    $check->create_single_option('col_names',1);
                    echo __(" Skip the first row of the file (if there is a table header)",'wsr');
                ?>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-sm-2" for="Rule"><?php _e("Import Rule:",'wsr'); ?></label>
            <div class="col-sm-5">
                <?php
                $drop = new dropdown_list('rule');
                $drop->add(__('Skip the existing redirects with the same source','wsr'), 'skip');
                $drop->add(__('Replace the existing redirects with the same source','wsr'), 'replace');
                $drop->run($SR_jforms);
                ?>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-12">
                <button class="btn btn-primary btn-sm" type="submit" name="btn_import" value="btn_import"><span class="glyphicon glyphicon-import"></span><?php _e("Import",'wsr') ?></button>
            </div>
        </div>
        <br/>
        <div style="text-align: right"><?php _e("* Need Help?",'wsr');?> <a target="_blank" href="http://www.clogica.com/kb/topics/seo-redirection-premium/export-import"><?php _e("click here to see info about import and export","wsr"); ?></a></div>
        <br/>
    </form>

<?php
$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts(12000);
$SR_jforms->run();
