<?php

/**
 * By: Fakhri Alsadi
 * Date: 2/2/2015
 * Time: 12:45 PM
 * 
 * Dependencies:
 * In: app
 * Out: request 
 */
if(!class_exists('cf_options_1')){
    class cf_options_1 {
        
        private $option_group_name='clogica_option_group';
        private $cf;
        private $request;
 
        
        /* Set the object's parent cf to access all objects ------------------  */        
        public function set_cf($cf)
        {
            $this->cf=$cf;
            $this->request= call_user_func(array($cf, 'get_request'));
            //$this->request = $cf::get_request();
        }
        
        /* initialization --------------------------------------------------  */
        public function init($option_group_name)
        {
            $this->option_group_name=$option_group_name;
        }
        
        /* set_option_group --------------------------------------------------  */
        public function set_option_group($option_group_name)
        {
            $this->option_group_name=$option_group_name;
        }

        /* get_option_group --------------------------------------------------  */
        public function get_option_group()
        {
            return $this->option_group_name;
        }

            /* update_my_options -------------------------------------------------  */
        public function update_my_options($options,$blog=0)
        {
            if(intval($blog)<=0)
            {
                if(is_multisite())
                    update_blog_option($blog,$this->get_option_group(),$options);
                else
                    update_site_option($this->get_option_group(),$options);
                
            }else
            {
                update_blog_option($blog, $this->get_option_group(), $options);
            }

        }

        /* get_my_options ----------------------------------------------------  */
        public function get_my_options($blog=0)
        {
            if(intval($blog)<=0)
            {
                
                if(is_multisite())
                 $options=get_blog_option($blog,$this->get_option_group());
                else
                 $options=get_site_option($this->get_option_group());
                if(!is_array($options))
                {
                    $options= array();

                    if(is_multisite())
                        add_blog_option($blog,$this->get_option_group(),$options);
                    else
                        add_site_option($this->get_option_group(),$options);                        
                }
                return $options;
            }else
            {
                $options=get_blog_option($blog, $this->get_option_group());
                if(!is_array($options))
                {
                    $options= array();
                    add_blog_option($blog, $this->get_option_group(),$options);
                }
                return $options;
            }
        }

        /* read_option_value -------------------------------------------------  */
        public function read_option_value($key,$default='',$blog=0)
        {
            $options=$this->get_my_options($blog);
            if(array_key_exists($key,$options))
            {
                return $options[$key];
            }else
            {
                $this->save_option_value($key,$default,$blog);
                return $default;
            }
        }

        /* save_option_value -------------------------------------------------  */
        public function save_option_value($key,$value,$blog=0)
        {
            $options=$this->get_my_options($blog);
            $options[$key]=$value;
            $this->update_my_options($options,$blog);
        }

        /* save_post_option_value -------------------------------------------------  */
        public function save_post_option_value($key,$type='text',$blog=0)
        {
            $options=$this->get_my_options($blog);
            $options[$key]=$this->request->post($key,$type);
            $this->update_my_options($options,$blog);
        }

        /* save_get_option_value --------------------------------------------------  */
        public function save_get_option_value($key,$type='text',$blog=0)
        {
            $options=$this->get_my_options($blog);
            $options[$key]=$this->request->get($key,$type);
            $this->update_my_options($options,$blog);
        }

        /* delete_my_options -----------------------------------------------------  */
        public function delete_my_options($blog)
        {
            if(intval($blog)<=0)
            {
                delete_site_option($this->get_option_group());
            }else
            {
                delete_blog_option($blog,$this->get_option_group());
            }
        }


        
    }
}
