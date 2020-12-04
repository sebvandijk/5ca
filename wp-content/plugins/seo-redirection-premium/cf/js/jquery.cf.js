/**
 * Created by Fakhri Alsadi on 2/27/2015.
 */
jQuery(document).ready(function($){

    //File chooser
    $('.file_chooser').click(function(e) {
        var custom_uploader;
        var input_field;
        var title;
        var type;
        e.preventDefault();
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
        input_field = $(this).attr('data-input-field');
        title = 'Choose ' + $(this).attr('data-type');
        type = $(this).attr('data-type');
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: title,
            button: {
                text: title
            },
            multiple: false
        });
        custom_uploader.on('select', function() {
            attachment = custom_uploader.state().get('selection').first().toJSON();
            update_chooser(input_field, attachment.url, type );
        });
        custom_uploader.open();
    });

    $('.file_chooser_empty').click(function(e) {
        var input_field;
        var type;
        type = $(this).attr('data-type');
        input_field = $(this).attr('data-input-field');
        update_chooser(input_field,"", type);
    });

    function update_chooser(id, file_url, type)
    {
        if(type == 'image')
        {
            $('#' + id).css('display','none')
            $('#' + id).val(file_url);
            $('#' + id + '_preview').attr('src', "");
            $('#' + id + '_preview').attr('src', file_url);
            if(file_url !=""){
                $('#' + id + '_preview').css('display','block');
            }else{
                $('#' + id + '_preview').css('display','none');
            }

        }else
        {
            $('#' + id).css('display','block')
            $('#' + id).val(file_url);
            $('#' + id + '_preview').css('display','none');
        }
    }

    //bchecknox option
    jQuery(document).ready(function($){

        $('.bcheckbox').bind("click", function () {
            var selector= '#'+ $(this).attr("data-id");
            var group_selector = '#'+ $(this).attr("data-group");
            if($( selector + "_label").hasClass('active')){
                $(selector).removeAttr('checked');
                if(!$(this).hasClass('bcheckbox_check_unckeck_all')){
                    $(group_selector + '_bcheckbox_check_unckeck_all_label').removeClass('active');
                    $(group_selector + '_bcheckbox_check_unckeck_all').removeAttr('checked');
                }
            }else {
                $(selector).attr('checked', 'checked');
            }
        });

        $('.bcheckbox_check_unckeck_all').bind("click", function () {
            var selector_text= $(this).attr("data-group");
            if($('#' + selector_text + '_bcheckbox_check_unckeck_all_label').hasClass('active'))
            {
                $('.' + selector_text).removeClass('active');
                $("[name='" + selector_text + "[]']").removeAttr('checked');
            }else{
                $('.' + selector_text).addClass('active');
                $("[name='" + selector_text + "[]']").attr('checked', 'checked');
            }
        });

        $('.bcheckbox_check_all').bind("click", function () {
            var selector_text= $(this).attr("data-group");
            $('.' + selector_text).addClass('active');
            $("[name='" + selector_text + "[]']").attr('checked', 'checked');
        });

        $('.bcheckbox_unckeck_all').bind("click", function () {
            var selector_text= $(this).attr("data-group");
            $('.' + selector_text).removeClass('active');
            $("[name='" + selector_text + "[]']").removeAttr('checked');
        });

    });

});
