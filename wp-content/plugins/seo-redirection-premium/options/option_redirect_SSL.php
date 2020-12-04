<?php
$request = SRP_PLUGIN::get_request();
$htaccess = SRP_PLUGIN::get_htaccess();
$htaccess->init();

if($request->post('https_add')!='')
{
    if(!$htaccess->is_saved_rule('SEO_REDIRECTION_HTTPS'))
    {
        $htaccess->add_rule('SEO_REDIRECTION_HTTPS', 
"RewriteEngine on
RewriteCond     %{SERVER_PORT} ^80$
RewriteRule     ^(.*)$ https://%{SERVER_NAME}%{REQUEST_URI} [L,R]"
);
        $htaccess->update_htaccess();
    }
}else if($request->post('https_remove')!='')
{
    if($htaccess->is_saved_rule('SEO_REDIRECTION_HTTPS'))
    {
        $htaccess->delete_saved_rule('SEO_REDIRECTION_HTTPS');
        $htaccess->update_htaccess();
    }
}
        
$htaccess->init();
$status=0;
if($htaccess->is_saved_rule('SEO_REDIRECTION_HTTPS'))
{
  $status=1;  
}


$SR_jforms = new jforms();
?>
<h4><?php _e('Redirect HTTP to HTTPS','wsr');?></h4><hr/>
<?php if(!is_ssl()){?><p><div class="alert alert-danger" role="alert"><?php _e('You must use HTTPS conntection to change this option!','wsr');?></div><?php }?>
<form action="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
    <div class="container">
        <p><?php _e('If you do not know what you do here, please leave this tab','wsr');?></p>
    </div>
<?php if($status==0){?>
    <div class="row">
        <div class="col-sm-10 ssl_icon">
            <p><?php _e('There is no detected redirect from HTTP to HTTPS.','wsr');?></p><br/>
            <?php if(is_ssl()){?>
            <button type="submit" name="https_add" value="https_add" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-ok"></span> <?php _e('Enable Redirect from HTTP to HTTPs','wsr');?></button>
            <?php }else{?>
            <button type="submit" name="https_disabled" value="https_disabled" class="btn btn-normal btn-sm" disabled><span class="glyphicon glyphicon-warning-sign"></span>  <?php _e('Enable Redirect from HTTP to HTTPs','wsr');?></button>
            <?php }?>
        </div>
    </div>
<?php }else{?>
     <div class="row">
        <div class="col-sm-10 ssl_icon">
            <p> <?php _e('There is currently a redirect from HTTP to HTTPS.','wsr');?></p><br/>
            <?php if(is_ssl()){?>
            <button type="submit" name="https_remove" value="https_remove" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-ok"></span><?php _e(' Remove the Redirect from HTTP to HTTPs','wsr');?></button>
            <?php }else{?>
            <button type="submit" name="https_disabled" value="https_disabled" class="btn btn-normal btn-sm" disabled><span class="glyphicon glyphicon-warning-sign"></span> <?php _e('Enable Redirect from HTTP to HTTPs','wsr');?></button>
            <?php }?>
        </div>
    </div>       
<?php }?>    

</form>

<?php
$SR_jforms->set_small_select_pickers();
$SR_jforms->hide_alerts();
$SR_jforms->run();