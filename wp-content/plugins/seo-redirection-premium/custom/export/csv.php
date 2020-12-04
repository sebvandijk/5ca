<?php
global $wpdb;

$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

function arr_csv($results, $name = NULL)
{
    if( ! $name)
    {
        $name = md5(uniqid() . microtime(TRUE) . mt_rand()). '.csv';
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename='. $name);
    header('Pragma: no-cache');
    header("Expires: 0");
    $outstream = fopen("php://output", "w");
    foreach($results as $result)
    {
        fputcsv($outstream, $result);
    }
    fclose($outstream);
}

if(current_user_can("manage_options")){


    $group="";
    if(array_key_exists('grpID',$_POST) && $_POST['grpID']!='' )
    {
        $group = " and grpID=" . intval($_POST['grpID']);
    }

    if(array_key_exists('blog',$_POST) && $_POST['blog']!='' )
    {
        $blog = " and blog='" . get_current_blog_id() . "' ";
    }else
    {
        echo __("Invalid parameters ....",'wsr');
        exit(0);
    }

    $table_name= SR_database::WP_SEO_Redirection();
    $results = $wpdb->get_results("select redirect_from,redirect_to,redirect_type,redirect_from_type,redirect_from_folder_settings,redirect_from_subfolders,redirect_to_type,redirect_to_folder_settings,regex from $table_name where cat='link' $blog $group");

    $cvar[0][0]='redirect_from';
    $cvar[0][1]='redirect_to';
    $cvar[0][2]='redirect_type';
    $cvar[0][3]='redirect_from_type';
    $cvar[0][4]='redirect_from_folder_settings';
    $cvar[0][5]='redirect_from_subfolders';
    $cvar[0][6]='redirect_to_type';
    $cvar[0][7]='redirect_to_folder_settings';
    $cvar[0][8]='regex';

    $i=0;
    foreach($results as $result)
    {
        $i++;
        $cvar[$i][0]=$result->redirect_from;
        $cvar[$i][1]=$result->redirect_to;
        $cvar[$i][2]=$result->redirect_type;
        $cvar[$i][3]=$result->redirect_from_type;
        $cvar[$i][4]=$result->redirect_from_folder_settings;
        $cvar[$i][5]=$result->redirect_from_subfolders;
        $cvar[$i][6]=$result->redirect_to_type;
        $cvar[$i][7]=$result->redirect_to_folder_settings;
        $cvar[$i][8]=$result->regex;
    }

    arr_csv($cvar, 'redirects.csv');
}else{
    echo __("You must login to export!!",'wsr');
}