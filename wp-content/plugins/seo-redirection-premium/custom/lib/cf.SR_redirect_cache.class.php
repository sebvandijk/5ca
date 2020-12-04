<?php

if(!class_exists('clogica_SR_redirect_cache')){
    class clogica_SR_redirect_cache {

        public static $show_notifications=true;

        /*- Add Redirect ----------------------------------------*/
        public function add_redirect($post_id,$is_redirected,$redirect_from,$redirect_to,$redirect_type=301)
        {
           if(self::cache_enabled()){
            global $wpdb;
            $table_name = SR_database::WP_SEO_Cache();
            $blog= get_current_blog_id(); 
            $sql = $wpdb->prepare("insert IGNORE into $table_name(ID,is_redirected,redirect_from,redirect_to,redirect_type,blog) values(%d,%d,'%s','%s',%d,%d)", $post_id, $is_redirected,$redirect_from,$redirect_to,$redirect_type, $blog);	
             $wpdb->query($sql);
           }
        }

        /*- Fetch Redirect ----------------------------------------*/
        public function fetch_redirect($post_id)
        {
            global $wpdb;
            $check_status = buddy_press_check_locking();
            if($check_status )
            {
                return;
            }
            $table_name = SR_database::WP_SEO_Cache();
            return $wpdb->get_row($wpdb->prepare("select *  from  $table_name where blog='%d' and ID='%d'; ",get_current_blog_id(),$post_id));
        }
        /*- Redirect Cache ----------------------------------------*/
        public function redirect_cached($post_id)
        {
            $redirect = $this->fetch_redirect($post_id);
            if($redirect != null && self::cache_enabled())
            {
                if($redirect->is_redirected==1)
                {
                    if($redirect->redirect_type==301)
                    {
                        header ('HTTP/1.1 301 Moved Permanently');
                        header ("Location: " . $redirect->redirect_to);
                        exit();
                    }
                    else if($redirect->redirect_type==307)
                    {
                        header ('HTTP/1.0 307 Temporary Redirect');
                        header ("Location: " . $redirect->redirect_to);
                        exit();
                    }
                    else if($redirect->redirect_type==302)
                    {
                        header ("Location: " . $redirect->redirect_to);
                        exit();
                    }
                }
                return 'not_redirected';
            }
            return 'not_found';
        }

        /*- Delete Redirect ----------------------------------------*/
        public function del_redirect($post_id)
        {
            global $wpdb;
            $table_name = SR_database::WP_SEO_Cache();
            return $wpdb->get_var("delete from  $table_name where blog='" . get_current_blog_id() . "' and ID='$post_id'; ");
        }

        /*- Free Cache ----------------------------------------*/
        public static function free_cache($force=0)
        {
            if(self::cache_enabled() || $force==1){
            global $wpdb;
            $table_name = SR_database::WP_SEO_Cache();
            $wpdb->query(" delete from $table_name where blog='" . get_current_blog_id() . "' ");
            if(self::show_notifications() && $force!=2)
            SRP_PLUGIN::get_app()->echo_message("<b>".__("All cached redirects are deleted!",'wsr')."</b>");
            }
        }

        /*- Free Cache ----------------------------------------*/
        public static function free_cache_without_notification(){
            self::free_cache(2);
        }

        /*- Cache Count ----------------------------------------*/
        public function count_cache()
        {
            global $wpdb;
            $table_name = SR_database::WP_SEO_Cache();
            return $wpdb->get_var("select count(*) as cnt from  $table_name where blog='" . get_current_blog_id() . "';  ");
        }


        /* ----------------------------------------------- */
         public static function show_notifications()
         {
             return self::$show_notifications;
         }

         /* ----------------------------------------------- */
         public static function cache_enabled()
         {
             return (intval(SRP_PLUGIN::get_options()->read_option_value("cache_enable"))!=0);
         }



    }}