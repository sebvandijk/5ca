<?php

/**
* 
*/

class SR_test_regex
{
	
	function __construct()
	{
		add_action('init',array($this,'WP_SEO_test_init'));
		add_action('admin_enqueue_scripts',array($this,'WP_SEO_test_enqueue_files'));
		add_action('wp_ajax_wp_seo_test_ajax',array( $this ,'WP_SEO_ajax_request_handle_test') );
	}
	public function WP_SEO_ajax_request_handle_test()
	{
	
		if(isset($_POST['url']) && isset($_POST['redirect_from']) && isset($_POST['redirect_to']))
		{
			$status = true;
			$arr_input_fld = array();
			$arr_input_fld['input'] = array();
			$arr_input_fld['message'] = array();

			if(empty($_POST['url']) )
			{
				$arr_input_fld['input'][] = 'test_url_input';
				$arr_input_fld['message'][] = __('Url field is empty','wsr');
				$status = false;
			} 
			if(empty($_POST['redirect_from']) )
			{
				$arr_input_fld['input'][] = 'redirect_from';
				$arr_input_fld['message'][] = __('Redirect from field is empty','wsr');
				$status = false;
			}
			
			if(empty($_POST['redirect_to']) )
			{
				$arr_input_fld['input'][] = 'redirect_to';
				$arr_input_fld['message'][] = __('Redirect to field is empty','wsr');
				$status = false;
			}

			if($status == true)
			{
				$input = $_POST['url'];
				$pattern = "/" . stripcslashes($_POST['redirect_from']) ."/";
			    error_reporting(E_ALL & ~E_WARNING);
			    $redirect_to_reg  = preg_replace( $pattern, $_POST['redirect_to'], $input );

		        if(!preg_match($pattern, $input))
		        {
		        	$error = __("invalid Redirect from pattern / Url",'wsr');
		        	echo json_encode(array('status'=>$status,'regex_output_error' => $error) ); 
					die;
		        }	
		       
	            if($redirect_to_reg[0] == '/')
	            {
	                $redirect_to_reg = site_url( $redirect_to_reg );    
	            }else{
				
	                if( !SRP_PLUGIN::get_request()->is_valid_url($redirect_to_reg) )
	                {
	                	if($redirect_to_reg)
	                	{
	                		$redirect_to_reg = 'http://'.$redirect_to_reg;	
	                	}
	                    
	                }
	            }
        

				echo json_encode(array('status'=>$status,'regex_output' => $redirect_to_reg) ); 
				die;
			}
			
	        echo json_encode(array('status'=>$status,'regex_output' => $arr_input_fld) ); 
		}
		
		die;
	}	
	public function WP_SEO_test_init()
	{
		
	}
	public function WP_SEO_test_enqueue_files(){

		wp_enqueue_script('WP_SEO_CUSTOM_JS', SR_PLUGINS_URL . '/cf/js/seo_custom.js', array( 'jquery' ) );
		wp_localize_script( 'WP_SEO_CUSTOM_JS', 'seo_ajax', array( 'ajax_url' => admin_url('admin-ajax.php')) );
	}
}
new SR_test_regex();
?>