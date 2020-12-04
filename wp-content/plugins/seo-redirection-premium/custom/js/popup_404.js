var $ = jQuery;

function show_popup(source)
{
    clear_form();
    if(source===0){
        var sel_ids = $("input[name='sel_items[]']:checked")
                  .map(function(){
                      return $(this).val();
                    }).get();

        if (sel_ids.length < 1)
        {
            alert("No links selected!");
            return 0;
        }
        $('#redirect_url').html('<span aria-hidden="true" class="glyphicon glyphicon-th-list"></span> ' + sel_ids.length + " Links Selected");
        $('#selected_ids').val(sel_ids.toString());
    }else if(source >0)
    {
        var source_link = $('#link_' + source).html();
        if(source_link.length > 80)
        {
           source_link = source_link.substr(0, 30) + " ... " + source_link.substr(source_link.length-30, 30); 
        }
        
        $('#redirect_url').html('<span aria-hidden="true" class="glyphicon glyphicon-link"></span> ' + source_link );
        $('#selected_ids').val(source);
    }
    
    $('#add_redirect').modal('show'); // show bootstrap modal
    $('#add_redirect').appendTo("body");
    
}


function clear_form()
{
    $('#redirect_url').html('');  
    $('#selected_ids').val('');  
    $('#redirect_to').val('');
    $('#redirect_to_msg').css("display", "none");
    clean_save_state();
    clean_search_posts_state();
    
}

function clean_save_state()
{
    $('#save_button').prop('disabled', false);
    $('#save_button').html('Save Redirect');
}

function clean_search_posts_state()
{
    $('#datafetch').html('');
    $("#search_posts").val('');
}

function animate_redirect_to_field_bg()
{
    $('#redirect_to').css("background-color", "#e2f6dc");
    setTimeout(function(){ $('#redirect_to').css("background-color", "#fff"); }, 500);
}

function search_key_up(key)
{
    if(key==13)
    {
      search_posts();  
    }
}


function search_posts()
{
    var keyword = $("#search_posts").val();
    var ajax_url = $("#ajax_url").val();
    $('#datafetch').html('<br><p style="text-align: center; font-size: 14px;"><i style="font-size: 16px;" class="fa fa-spinner fa-spin"></i> Searching posts ...</p>');
    $.post(ajax_url,{'action':'search_posts_action','keyword': keyword},
    function(response){
        $('#datafetch').html('');
        $('#datafetch').append(response);
        console.log(result);
    });
}


function use_link(link)
{
   $('#redirect_to').val(decodeURIComponent(link));
   clean_search_posts_state();
   animate_redirect_to_field_bg();
}

function check_input()
{
    var redirect_to = $('#redirect_to').val();
    $('#redirect_to_msg').css("display", "none");
    if(redirect_to == "")
    {
        $('#redirect_to_msg').css("display", "block");
        $('#redirect_to_msg').html("Please input the 'redirect to' URL!");
        alert("Please input the 'redirect to' URL!");
        setTimeout(function() { $('#redirect_to').focus(); }, 200);
        return false;
        
    }else if(!(redirect_to.indexOf('://')!=-1 || redirect_to.substr(0,1)=='/'))
    {
        $('#redirect_to_msg').css("display", "block");
        $('#redirect_to_msg').html("Invalid 'redirect to' URL value!");
        alert("Invalid 'redirect to' URL value!");
        setTimeout(function() { $('#redirect_to').focus(); }, 200);
        return false;
    }
    
    
    return true;
}

function hide_rows()
{
    var ids= $('#selected_ids').val().split(",");
    for(i=0; i<ids.length ; i++)
    {
        $('#tr_' + ids[i]).remove();
    }
}


function save_values()
{
    if(check_input()){
        var ajax_url = $("#ajax_url").val();
        $('#save_button').prop('disabled', true);
        $('#save_button').html('<i style="font-size: 16px;" class="fa fa-spinner fa-spin"></i> Saving ...');
        var formData = $('#redirect_404_form').serialize();
        $.ajax({
        url: ajax_url,
        type: "POST",
        data: {
            'action': 'redirect_404_form',
            'formData': formData,
        },
        success: function (data) {
            
            if(data == "")
            {
                alert("No data received!");
                clean_save_state();
                return;
            }
            data = $.parseJSON(data);
            if (!data.error)
            {
              
                $('#add_redirect').modal('hide');
                hide_rows();
            }else
            {
                alert(data.error_msg);
                clean_save_state();
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR, textStatus, errorThrown);
            alert(errorThrown);
            $('#add_redirect').modal('hide');
        }
    
        });
        
        // 
    }
}