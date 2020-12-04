<?php

if(!class_exists('SR_plugin_menus')){
class SR_plugin_menus
{

    private static $tabs;
    
    /*----------------------------------------------------------*/
    public static function init()
    {
        self::$tabs = SRP_PLUGIN::get_tabs();
        self::$tabs->init();
        self::$tabs->set_parameter("SR_tab");
    }

    /*----------------------------------------------------------*/
    public static function side_menu()
    {
        add_options_page(SR_PLUGIN_NAME, SR_PLUGIN_NAME, 'manage_options', SRP_PLUGIN::get_app()->get_plugin_slug(), array('SR_plugin_menus', 'side_menu_content'));
    }

    /*----------------------------------------------------------*/
    public static function side_menu_content()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.','wsr'));
        }
        echo '<div class="wrap" style="direction: ltr"><div class="title_logo">'.SR_PLUGIN_NAME.'</div><br/>';
        if(SRP_PLUGIN::get_app()->search_plugins('redirection','seo-redirection-premium')!='')
        {
            SRP_PLUGIN::get_app()->echo_message("<b>".__('There is another redirect plugin','wsr')." (" . SRP_PLUGIN::get_app()->search_plugins('redirection','seo-redirection-premium') . ") ".__('installed, please deactivate it to avoid conflict','wsr')."</b>",'error',1);
        }

        if(SRP_PLUGIN::get_app()->search_plugins('redirect-to-homepage')!='')
        {
            SRP_PLUGIN::get_app()->echo_message("<b>".__('There is another redirect plugin','wsr')." (" .  SRP_PLUGIN::get_app()->search_plugins('redirect-to-homepage'). ") ".__('installed, please deactivate it to avoid conflict','wsr')."</b>",'error',1);
        }


        if(SRP_PLUGIN::get_options()->read_option_value('plugin_status')=='0')
        {
            SRP_PLUGIN::get_app()->echo_message("<b>".__("The plugin is currently disabled, go to the options tab and enable it!",'wsr')."</b>",'warning',1);
        }
		
		
        self::$tabs->set_ignore_parameter(array('del', 'search', 'page_num', 'add', 'edit', 'page404','grpID','shown','sort','link_type','link','return','post_operation','post_operation_id','country','rsrc','404_manager_tab','history_manager_tab','redirect_manager_tab'));
        self::$tabs->add_file_tab('redirect_manager', __('Redirect Manager','wsr'), 'option_redirect_manager.php', 'file');
        self::$tabs->add_file_tab('404_manager', __('404 Manager','wsr'), 'option_404_manager.php', 'file');
        self::$tabs->add_file_tab('redirect_history', __('Redirect History','wsr'), 'option_history_manager.php', 'file');
        self::$tabs->add_file_tab('export_import', __('Export/Import','wsr'), 'option_export_import.php', 'file');
        self::$tabs->add_file_tab('general_options', __('General Options','wsr'), 'option_general.php', 'file');
       // if(is_main_site())
        self::$tabs->add_file_tab('help_center', __('Help Center','wsr'), 'option_help_center.php', 'file');
        self::$tabs->run();
        echo '</div>';
    }


    /*----------------------------------------------------------*/
    public static function hook_menus()
    {
        add_action('admin_menu', array('SR_plugin_menus', 'side_menu'));
        add_action( 'add_meta_boxes', array('SR_plugin_menus','redirect_meta_boxes'), 10, 3 );
    }
    

    /*----------------------------------------------------------*/
    public static function redirect_meta_boxes()
    {
        if (SRP_PLUGIN::get_options()->read_option_value('show_redirect_box') == '1') {

            $post_types = explode(',',SRP_PLUGIN::get_options()->read_option_value('post_types'));
            foreach ( $post_types as $post_type ) {

                add_meta_box(
                    'seo_redirection_box',
                    __(SR_PLUGIN_NAME),
                    array('SR_plugin_menus','select_box_menu'),
                    $post_type,
                    'side'
                );
            }

        }
    }

    /*----------------------------------------------------------*/
    public static function select_box_menu($post)
    {
        global $wpdb;
        if(get_post_status()!='auto-draft')
        {

            $permalink="";
            if (in_array($post->post_status, array('draft', 'pending'))) {
                list( $permalink, $postname ) = get_sample_permalink( $post->ID);
                $permalink = str_replace( '%postname%', $postname, $permalink );
                $permalink = str_replace( '%pagename%', $postname, $permalink );

            } else {
                $permalink = get_permalink($post->ID);
            }

            $permalink = SRP_PLUGIN::get_request()->make_relative_url($permalink);
            $redirect = $wpdb->get_row($wpdb->prepare(" select id,redirect_from,redirect_to from " . SR_database::WP_SEO_Redirection() ." where redirect_from='%s' and cat='link' and blog='%d' ",$permalink,get_current_blog_id()));

            if($wpdb->num_rows>0)
            {
                self::redirected_menu($permalink,$redirect);
            }
            else
            {
                self::setup_redirect_menu($permalink);
            }

        }else
        {
         esc_html_e("You can not setup a redirect for this new " . get_post_type()  . " before saving it.",'wsr');
        }


    }

    /*----------------------------------------------------------*/
    public static function setup_redirect_menu($permalink,$outcome='echo')
    {
        self::boxes_style($outcome);
        $link= SRP_PLUGIN::get_security()->encode_url($permalink);
        $return=  SRP_PLUGIN::get_security()->encode_url(SRP_PLUGIN::get_request()->get_current_URL());
        $grp=SR_option_manager::get_group_id('Redirected Posts');
        $grpwhere = "&grpID=$grp";
        $html="
        <div style=\"color: #23527c\">
          $permalink  <input type=\"hidden\" name=\"seo_premalink\" value=\"$permalink\">
        </div>
        <br>
        <div style=\"text-align:center;\">
              <input onclick=\"window.location='options-general.php?page=" . SRP_PLUGIN::get_app()->get_plugin_slug() . "&SR_tab=redirect_manager&add=1$grpwhere&link=$link&return=$return'\" type=\"button\" value=\"Redirect\" class=\"peter-river-flat-button\" style=\"width:90%; margin-bottom:10px; margin-right:10px \" id=\"publish\" name=\"publish\">
              <input onclick=\"window.location='options-general.php?page=" . SRP_PLUGIN::get_app()->get_plugin_slug() . "&SR_tab=redirect_manager&add=1$grpwhere&link=$link&return=$return&post_operation=draft&post_operation_id=". get_the_ID() ."'\" type=\"button\" value=\"Redirect & Draft\" class=\"orange-flat-button\" style=\"width:90%; margin-bottom:10px; margin-right:10px \" id=\"publish\" name=\"publish\">
              <input onclick=\"window.location='options-general.php?page=" . SRP_PLUGIN::get_app()->get_plugin_slug() . "&SR_tab=redirect_manager&add=1$grpwhere&link=$link&return=$return&post_operation=trash&post_operation_id=". get_the_ID() ."'\" type=\"button\" value=\"Redirect & Trash\" class=\"pomegranate-flat-button\" style=\"width:90%; margin-bottom:10px; margin-right:10px \" id=\"publish\" name=\"publish\">
        </div>
        ";

        if($outcome=='echo')
        {
            echo $html;
        }else
        {
            return $html;
        }
    }


    /*----------------------------------------------------------*/
    public static function redirected_menu($permalink, $redirect, $outcome='echo')
    {
        self::boxes_style($outcome);
        $link= SRP_PLUGIN::get_security()->encode_url($permalink);
        $return=SRP_PLUGIN::get_security()->encode_url(SRP_PLUGIN::get_request()->get_current_URL());
        $rid=$redirect->id;
        $html="
            <div style=\"color: #23527c;\">
              $permalink
            </div>
              <div>
                <b>".__("Redirected to:","wsr")."</b>
            </div>
              <div style=\"color: #23527c;\">
              {$redirect->redirect_to}
                </div>
              <br/>
              <div style=\"text-align:center;\">
              <input onclick=\"window.location='options-general.php?page=" . SRP_PLUGIN::get_app()->get_plugin_slug() . "&SR_tab=redirect_manager&edit=$rid&return=$return'\" type=\"button\" value=\"Update\" class=\"peter-river-flat-button\" style=\"width:40%; margin-bottom:10px; margin-right:10px \" id=\"publish\" name=\"publish\">
                 <input onclick=\"window.location='options-general.php?page=" . SRP_PLUGIN::get_app()->get_plugin_slug() . "&SR_tab=redirect_manager&redirect_manager_tab=redirects&del=$rid&return=$return'\" type=\"button\" value=\"Delete\" class=\"pomegranate-flat-button\" style=\"width:40%; margin-bottom:10px\" id=\"publish\" name=\"publish\">
              </div>
        ";

        if($outcome=='echo')
        {
            echo $html;
        }else
        {
            return $html;
        }
    }


    /*---------------------------------------------------------------*/
    public static function boxes_style($outcome='echo')
    {

        $css="
<style>
.peter-river-flat-button {
  position: relative;
  vertical-align: top;
  width: 80%;
  height: 35px;
  padding: 0;
  font-size: 14px;
  color: white;
  text-align: center;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
  background: #3498db;
  border: 0;
  border-bottom: 2px solid #2a8bcc;
  cursor: pointer;
  -webkit-box-shadow: inset 0 -2px #2a8bcc;
  box-shadow: inset 0 -2px #2a8bcc;
}
.peter-river-flat-button:active {
  top: 1px;
  outline: none;
  -webkit-box-shadow: none;
  box-shadow: none;
}

.orange-flat-button {
  position: relative;
  vertical-align: top;
  width: 80%;
  height: 35px;
  padding: 0;
  font-size: 14px;
  color: white;
  text-align: center;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
  background: #f39c12;
  border: 0;
  border-bottom: 2px solid #e8930c;
  cursor: pointer;
  -webkit-box-shadow: inset 0 -2px #e8930c;
  box-shadow: inset 0 -2px #e8930c;
}
.orange-flat-button:active {
  top: 1px;
  outline: none;
  -webkit-box-shadow: none;
  box-shadow: none;
}


.pomegranate-flat-button {
  position: relative;
  vertical-align: top;
  width: 80%;
  height: 35px;
  padding: 0;
  font-size: 14px;
  color: white;
  text-align: center;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
  background: #c0392b;
  border: 0;
  border-bottom: 2px solid #b53224;
  cursor: pointer;
  -webkit-box-shadow: inset 0 -2px #b53224;
  box-shadow: inset 0 -2px #b53224;
}
.pomegranate-flat-button:active {
  top: 1px;
  outline: none;
  -webkit-box-shadow: none;
  box-shadow: none;
}

        </style>
        ";

        if($outcome=='echo')
        {
            echo $css;
        }else
        {
            return $css;
        }
    }

}}