<?php
$SR_jforms = new jforms();

$request = SRP_PLUGIN::get_request();
$app = SRP_PLUGIN::get_app();

$cat="Plugin Usage";
$name="";
$email="";
$subject="";
$details="";
$order="";

if($request->post("btn_send")!=''){

    $cat=$request->post('cat');
    $name=$request->post('name');
    $email=$request->post('email');
    $subject=$request->post('subject');
    $details=$request->post('details');
    $order=$request->post('order');

    if($request->post('name')!='' && $request->post('email')!='' && $request->post('subject')!='' && $request->post('details')!='' )
    {
        $headers = "From: $name <$email>" . "\r\n";
        wp_mail( 'support@clogica.com', $cat . ': ' . $order .': ' . $subject , $details , $headers );
        $app->echo_message(__("You message is received and queued, we will reply you at your email.",'wsr'));

        $cat="Plugin Usage";
        $name="";
        $email="";
        $subject="";
        $details="";
        $order="";

    }else
    {
        $app->echo_message(__("Please fill all the following fields before sending!",'wsr'),"danger");
    }
}




?>

<style>
    #g404from .form-group{ margin-bottom: 5px;}
    #g404from p{ margin: 5px 0 5px 0;}
</style>
<h4><?php _e('Help Center','wsr');?></h4><hr/>

<form id="g404from" action="<?php echo $request->get_current_parameters(array("add","edit","del"));?>" method="post" class="form-horizontal" role="form" data-toggle="validator">
    <div class="row">
        <div class="col-sm-12">
            <p><?php _e('We recommend before contacting us and wait for reply, to explore the product page in our knowledge base, it contains many articles about how to use the plugin','wsr');?><br/>
                <b><?php _e('To go to the','wsr');?> <a target="_blank" href="http://www.clogica.com/kb/topics/seo-redirection-premium"><?php _e('knowledge base click here','wsr');?></a></b></p>
            <br/><br/>
            <h2 style="display: inline; color: #636465; font-size:24px"><b><?php _e('Open a new ticket?','wsr');?></b></h2>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-10 help_icon">
 <div class="form-group">
                <div class="col-sm-offset-1 col-sm-7">
				<p><?php _e('Please provide as much details as possible so we can best assist you. To update a previously submitted ticket, Please login.','wsr');?></p>
				<br/>
				<br/>
                    <button class="btn btn-success" type="button" name="new_ticket" target="newwindow" onclick="window.open('http://www.clogica.com/support-center','_blank')" value="btn_send"><?php _e('Open a new ticket now','wsr');?></button>
                </div>
            </div>

        </div>
    </div>
</form>
<?php
$SR_jforms->hide_alerts();
$SR_jforms->run();