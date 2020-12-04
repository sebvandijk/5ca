<?php
global $wpdb;

$request = SRP_PLUGIN::get_request();

$table_name = SR_database::WP_SEO_Groups();
$SR_jforms = new jforms();
?>
<h4><?php _e('System Groups','wsr');?></h4><hr/>
<div class="form-group">
    <div class="col-sm-8">
        <table class="table table-bordered table-hover table-striped" >
            <thead>
            <tr>
                <th class="btn btn-default table_header"><?php _e('Group Name','wsr');?></th>
                <th class="btn btn-default table_header" width="100"><?php _e('Redirects','wsr');?></th>
            </tr>
            </thead>
            <?php

            $system_groups = $wpdb->get_results("select * from `$table_name` where blog='" . get_current_blog_id() . "' and group_type=1;");
            foreach ( $system_groups as $group ) {
            ?>
            <tr>
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

        <br/>
    </div>
    <br/>
</div>
<?
$SR_jforms->hide_alerts();
$SR_jforms->run();