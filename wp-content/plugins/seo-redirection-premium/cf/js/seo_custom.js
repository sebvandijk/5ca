jQuery(document).on('change','#redirect_from_type',function(){
	if(jQuery(this).val() == "Regex")
	{
		jQuery(".test_inputs_div").show();	
	}else{
		jQuery(".test_inputs_div").hide();	
	}
});

jQuery(document).on('click','#test_regx_btn',function(e){
	var url = jQuery("#test_url_input").val();
	var redirect_from = jQuery('#redirect_from').val();
	var redirect_to = jQuery('#redirect_to').val();


    jQuery('.test_output_div').html('');
    jQuery('.test_inputs_div .test_spinner').show();

	 post = {
                'action':"wp_seo_test_ajax",
                'url' :url,
                'redirect_from' : redirect_from,
				'redirect_to':redirect_to,                
            }
    jQuery.ajax({
        'type':'post',
         url: seo_ajax.ajax_url,
        data: post,
        success:function(responce){
            var mydata = jQuery.parseJSON(responce);
            jQuery('.wp_seo_validate_test').html('');
            if(mydata.status == true)
            {	

                if(mydata.regex_output_error)
                {
                    jQuery('.test_output_div').show();
                    jQuery('.test_output_div').html('');
                    jQuery('.test_output_div').html("<span class='glyphicon glyphicon glyphicon-remove' style='color:red;'></span><span>"+mydata.regex_output_error+"</span>");    
                }

                if(mydata.regex_output)
                {
                    jQuery('.test_output_div').show();
                    jQuery('.test_output_div').html('');
                    jQuery('.test_output_div').html("<span class='glyphicon  glyphicon glyphicon-ok'></span><span>"+mydata.regex_output+"</span>");    
                }

                
            }else{
                jQuery('.test_output_div').hide();
	            for (var i = 0; i < mydata.regex_output.input.length; i++) {
                    
                    if(jQuery("#"+mydata.regex_output.input[i]).next('.validate_txt'))
                    {
                        jQuery("#"+mydata.regex_output.input[i]).next('.validate_txt').remove();
                        jQuery("#"+mydata.regex_output.input[i]).after("<span class='validate_txt'>" +mydata.regex_output.message[i] + ".</span>");
                    }else{
                        jQuery("#"+mydata.regex_output.input[i]).after("<span class='validate_txt'>" +mydata.regex_output.message[i] + ".</span>");    
                    }
                    
	             	//jQuery('.wp_seo_validate_test').append("");
	            };
            }
            jQuery('#redirect_from').focus(function(){
                jQuery(this).next('.validate_txt').remove();
            });
            jQuery('#redirect_to').focus(function(){
                jQuery(this).next('.validate_txt').remove();
            });
            jQuery('#test_url_input').focus(function(){
                jQuery(this).next('.validate_txt').remove();
            });
            jQuery('.test_inputs_div .test_spinner').hide();
        }
    });

});