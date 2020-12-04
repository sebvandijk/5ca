<?php

/**
 * By: Fakhri Alsadi
 * Date: 2/2/2015
 * Time: 12:45 PM
 * 
 * Dependencies:
 * In: request,options,tabs
 * Out: htaccess
 */
if(!class_exists('cf_app_2')){
    class cf_app_2 {
        
        private $slug;
        private $plugin_file;
        private $plugin_path;
        private $plugin_url;
        private $cf;
        private $request;
        private $options;




        /* ------------------------------------------------------------------- */
        public function init ($plugin_file='')
        {
            $this->plugin_file = $plugin_file;
            $this->slug =  basename($plugin_file);
            $this->plugin_path = dirname($plugin_file) . '/';
            $this->plugin_url =SR_PLUGINS_URL; //plugin_dir_url($plugin_file);

            $this->hook_general_functions();

            if($this->get_plugin_slug()=='' || $this->in_option_page())
            {
                $this->hook_styles_and_scripts();
            }
        }
        
        /* Set the object's parent cf to access all objects ------------- */        
        public function set_cf($cf)
        {
            $this->cf=$cf;
            $this->request= call_user_func(array($cf, 'get_request'));
            $this->options= call_user_func(array($cf, 'get_options'));
            //$this->request = $cf::get_request();
            //$this->options = $cf::get_options();
           
        }
    
        /* there_is_cache ---------------------------------------------  */
        public function there_is_cache()
        {
            $plugins=get_site_option( 'active_plugins' );
            if(is_array($plugins)){
                foreach($plugins as $the_plugin)
                {
                    if (stripos($the_plugin,'cache')!==false)
                    {
                        return $the_plugin;
                    }
                }
            }
            return '';
        }

        /* search_plugins ---------------------------------------------  */
        public function search_plugins($keyword,$skip='')
        {
            $plugins=get_site_option( 'active_plugins' );
            if(is_array($plugins)){
            foreach($plugins as $the_plugin)
            {
                $phpfile = substr( $the_plugin, strrpos( $the_plugin, '/' )+1 );
                $phpfile = explode(".", $phpfile);
                $phpfile = $phpfile[0];
                if (stripos($phpfile,$keyword)!==false && $phpfile!=$skip)
                {
                    return $phpfile;
                }
            }}
            return '';
        }

        /* there_is_plugin ---------------------------------------------  */
        public function there_is_plugin($plugin)
        {
            $plugins=get_site_option( 'active_plugins' );
            if(is_array($plugins)){
                foreach($plugins as $the_plugin){
                    $phpfile = substr( $the_plugin, strrpos( $the_plugin, '/' )+1 );
                    $phpfile = explode(".", $phpfile);
                    $plugin_name = $phpfile[0];
                    if ($plugin_name==$plugin)
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        /* get_plugin_path ---------------------------------------------  */
        public function get_plugin_path()
        {
           return $this->plugin_path;
        }

        /* get plugin slug -------------------------------------------- */
        public function get_plugin_slug()
        {
            return $this->slug;
        }

        /* in_option_page -------------------------------------------- */
        public function in_option_page()
        {
            return ($this->get_plugin_slug()!='' && $this->request->get('page')==$this->get_plugin_slug());
        }

        /* get plugin slug -------------------------------------------- */
        public function get_plugin_file()
        {
            return $this->plugin_file;
        }

        /* get_plugin_url ---------------------------------------------  */
        public function get_plugin_url()
        {
            return $this->plugin_url;
        }

        /* get home path ---------------------------------------------- */
        public function get_home_path() {
            $home    = set_url_scheme( get_site_option( 'home' ), 'http' );
            $siteurl = set_url_scheme( get_site_option( 'siteurl' ), 'http' );
            if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
                $wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
                $pos = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), trailingslashit( $wp_path_rel_to_home ) );
                $home_path = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
                $home_path = trailingslashit( $home_path );
            } else {
                $home_path = ABSPATH;
            }
            return str_replace( '\\', '/', $home_path );
        }
        
        /* show_message ------------------------------------------------------  */
        public function hook_message($msgtxt,$type='')
        {
            $msg = $msgtxt;
                if($type=='error' || $type=='danger')
                {
                    $msg = '<div id="message" class="error"><p>' . $msgtxt . '</p></div>';
                }
                elseif($type=='updated')
                {
                    $msg = '<div id="message" class="updated"><p>' . $msgtxt . '</p></div>';
                }
                elseif($type=='warning')
                {
                    $msg = '<div id="message" class="error"><p>' . $msgtxt . '</p></div>';
                }
                elseif($type=='success')
                {
                    $msg = '<div id="message" class="updated"><p> ' . $msgtxt . '</p></div>';
                }else
                {
                    $msg = '<div id="message" class="updated"><p>' . $msgtxt . '</p></div>';
                }
            $this->push_msg($msg);
        }

        /* echo message -----------------------------------------------------  */
        public function echo_message($msgtxt,$type='success',$fixed=0)
        {
            $css = $type;
            $icon="";
            if ($type == 'updated' || $type == 'success')
            {
                $css = 'success';
                $icon="<span style='font-size: 14px' class=\"glyphicon glyphicon-ok\"></span>";
            }else if($type == 'error' || $type == 'danger'  )
            {
                $css = 'danger';
                $icon="<span style='font-size: 14px' class=\"glyphicon glyphicon glyphicon-exclamation-sign\"></span>";
            }else if($type == 'warning')
            {
               $css = 'warning';
                $icon="<span style='font-size: 14px' class=\"glyphicon glyphicon glyphicon-exclamation-sign\"></span>"; 
            }

             echo '<div class="f_'. $fixed .' alert alert-' . $css . '" role="alert">' . $icon . ' ' . $msgtxt . '</div><script>setTimeout(function(){ jQuery(".f_0").slideUp("slow"); }, 3000);</script>';
        }

        /* push_msg ---------------------------------------------------------  */
        private function push_msg($msg)
        {

            $msgs=$this->options->read_option_value('admin_notices');

            if(is_array($msgs))
            {
                $msgs[count($msgs)]=$msg;
            }else
            {
                $msgs = array();
                $msgs[0]=$msg;
            }
            $this->options->save_option_value('admin_notices',$msgs);

        }

        /* pop_msgs ------------------------------------------------------  */
        public function pop_msgs()
        {
            $msgs=$this->options->read_option_value('admin_notices');
            if(is_array($msgs))
            {
                for($i=0;$i<count($msgs);$i++)
                {
                    echo $msgs[$i];
                }

            }
            $this->options->save_option_value('admin_notices','');
        }

        /* hook notifications ------------------------------------------------------  */
        public function hook_general_functions()
        {
            add_action( 'admin_notices', array( &$this, 'pop_msgs' ) );
        }

        /* hook styles & java scripts -----------------------------------------------  */
        public function hook_styles_and_scripts()
        {
            add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_styles_scripts' ) );
        }

        /* hook style -------------------------------------------------------------  */
        function enqueue_styles_scripts()
        {
            
            wp_enqueue_style( 'bootstrap', $this->get_plugin_url() . 'cf/css/bootstrap.min.css' );
            wp_enqueue_style( 'bootstrap_theme', $this->get_plugin_url() . 'custom/css/bootstrap-custom-theme.min.css' );
            wp_enqueue_style( 'font_awesome', $this->get_plugin_url() . 'cf/css/font-awesome.min.css' );
            wp_enqueue_style( 'bootstrap-switch', $this->get_plugin_url() . 'cf/css/bootstrap-switch.min.css' );
            wp_enqueue_style( 'jquery_minicolors', $this->get_plugin_url() . 'cf/css/jquery.minicolors.css' );
            wp_enqueue_style( 'bootstrap_select_box_it', $this->get_plugin_url() . 'cf/css/bootstrap_select_picker.css' );
            wp_enqueue_style( 'clogica_common_style', $this->get_plugin_url() . 'cf/css/' . "style.css?&t=".time() );
            wp_enqueue_script('jquery');
            wp_enqueue_media();
            wp_enqueue_script('bootstrap_js', $this->get_plugin_url() . 'cf/js/bootstrap.min.js', array('jquery'), '', true );
            wp_enqueue_script('bootstrap-switch_js', $this->get_plugin_url() . 'cf/js/bootstrap-switch.min.js', array('jquery'), '', true );
            wp_enqueue_script('jquery_minicolors_js', $this->get_plugin_url() . 'cf/js/jquery.minicolors.min.js', array('jquery'), '', true );
            wp_enqueue_script('bootstrap_select_picker_js', $this->get_plugin_url() . 'cf/js/bootstrap_select_picker.js', array('jquery'), '', true );
            wp_enqueue_script('cf_js', $this->get_plugin_url() . 'cf/js/jquery.cf.js', array('jquery'), '', true );

        }
    }
}
