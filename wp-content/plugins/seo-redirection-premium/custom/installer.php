<?php
/**
 * Author: Fakhri Alsadi
 * Date: 2/8/2015
 * Time: 9:06 PM
 *
 *  $installer = new seo_redirection_installer();
    $installer->hook_installer();
 */
if(!class_exists('seo_redirection_installer')){
    class seo_redirection_installer {

        private static $version;

        public static function set_version($version)
        {
            self::$version = $version;
        }


        /* Install function -------------------------------------------- */
        public static function install($networkwide)
        {
            global $wpdb;

            self::install_options();
            SR_redirect_manager::check_default_permalink();
			
            /* Redirection Table ------------------------------------------*/
            $table_name = SR_database::WP_SEO_Redirection();
							
            
            if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                $sql = "
                CREATE TABLE IF NOT EXISTS `$table_name` (
                `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `enabled` int(1) NOT NULL DEFAULT 1,
                `redirect_from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `redirect_from_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Page',
                `redirect_from_folder_settings` int(1) NOT NULL,
                `redirect_from_subfolders` int(1) NOT NULL DEFAULT 1,
                `redirect_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `redirect_to_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Page',
                `redirect_to_folder_settings` int(1) NOT NULL DEFAULT 1,
                `regex` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `redirect_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `url_type` int(2) NOT NULL DEFAULT '1',
                `postID` int(11) unsigned DEFAULT NULL,
                `cat` VARCHAR(20) NOT NULL DEFAULT 'link',
                `grpID` int(11) unsigned DEFAULT 1,
                `hits` int(11) unsigned NOT NULL DEFAULT 0,
                `access_date` datetime DEFAULT NULL,
                `blog` int(11) unsigned NOT NULL DEFAULT 1,
                PRIMARY KEY (`ID`),
                UNIQUE KEY `redirect_from` (`redirect_from`,`cat`,`blog`)
                )ENGINE = MyISAM ;";
                $wpdb->query($sql);
            }else
            {
			
                /* Redirection Table upgrade ---------------------------------------------- */
   
                //check if Innodb convert it to myisam.                
                $status = $wpdb->get_row($wpdb->prepare("SHOW TABLE STATUS WHERE Name = '%s'",$table_name));
                if($status->Engine == 'InnoDB')
                {
                    $wpdb->query("alter table $table_name engine = MyISAM;");
                }

                //other checks
                
                $redirects = $wpdb->get_results(" select redirect_from,redirect_to,ID from $table_name; ");
                foreach ($redirects as $redirect)
                {
                    $redirect_from=  SRP_PLUGIN::get_request()->make_relative_url($redirect->redirect_from);
                    $redirect_to=SRP_PLUGIN::get_request()->make_relative_url($redirect->redirect_to);
                    $ID=$redirect->ID;
                    $wpdb->query(" update $table_name set  redirect_from='$redirect_from',redirect_to='$redirect_to'  where ID=$ID ");
                }

                $wpdb->query("update ". SR_database::WP_SEO_Redirection() ." set redirect_from_type='Page' where redirect_from_type=''  ");
                $wpdb->query("update ". SR_database::WP_SEO_Redirection() ." set redirect_to_type='Page' where redirect_to_type=''  ");


                // Add the new fields cat and grpID cat is for (link or 404rule), while grpID is for Groups
                if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_NAME = '$table_name'
			AND table_schema = DATABASE()
                        AND COLUMN_NAME = 'cat' ") == '0') {

                    $sql="
                    ALTER TABLE $table_name
                    ADD COLUMN cat VARCHAR(20) NOT NULL DEFAULT 'link',
                    ADD COLUMN grpID int(11) unsigned NOT NULL DEFAULT 1,
                    ADD COLUMN blog int(11) unsigned NOT NULL DEFAULT 1,
                    DROP INDEX redirect_from,
                    ADD CONSTRAINT redirect_from UNIQUE (redirect_from,cat,blog);
                ";
                    $wpdb->query($sql);

                    //fix group ID for current redirects using url_type field
                    $grpID=SR_option_manager::get_group_id('Redirected Posts');
                    if(intval($grpID)>0)
                    {
                        $wpdb->query(" update ". SR_database::WP_SEO_Redirection() ." set grpID='$grpID' where  blog='". get_current_blog_id() ."' and url_type=2 and cat='link'");
                        $wpdb->query(" update ". SR_database::WP_SEO_Redirection() ." set url_type=1 where  blog='". get_current_blog_id() ."' and cat='link'");
                    }

                }
                
                // Fix grpID for pro version from codecanyon.net
                if($wpdb->get_var(" SELECT count(*) as cnt from $table_name where grpID IS NULL ") > 0)
                {
                    $sql="
                  ALTER TABLE $table_name
                  DROP COLUMN grpID,
                  ADD COLUMN grpID int(11) unsigned NOT NULL DEFAULT 1;
                  ";
                    $wpdb->query($sql);

                    //fix group ID for current redirects using url_type field
                    $grpID=SR_option_manager::get_group_id('Redirected Posts');
                    if(intval($grpID)>0)
                    {
                        $wpdb->query(" update ". SR_database::WP_SEO_Redirection() ." set grpID='$grpID' where  blog='". get_current_blog_id() ."' and url_type=2 and cat='link' ");
                        $wpdb->query(" update ". SR_database::WP_SEO_Redirection() ." set url_type=1 where  blog='". get_current_blog_id() ."' and cat='link' ");
                    }
                }
                // Fix add blog field if not exist.
                if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_NAME = '$table_name'
                            AND table_schema = DATABASE()
                            AND COLUMN_NAME = 'blog' ") == '0') {

                    $sql="
                    ALTER TABLE $table_name
                    ADD COLUMN blog int(11) unsigned NOT NULL DEFAULT 1,
                    DROP INDEX redirect_from,
                    ADD CONSTRAINT redirect_from UNIQUE (redirect_from,cat,blog);
                ";

                    $wpdb->query($sql);
                }
                // Fix add blog field if not exist.
                if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_NAME = '$table_name'
                            AND table_schema = DATABASE()
                            AND COLUMN_NAME = 'hits' ") == '0') {

                    $sql="
                    ALTER TABLE $table_name
                    ADD COLUMN `hits` int(11) unsigned NOT NULL DEFAULT 0,
                    ADD COLUMN `access_date` datetime DEFAULT NULL;
                ";

                    $wpdb->query($sql);
                }
                
                //Fix Replace rule.
                $sql="update $table_name set cat='special', redirect_to_type='Replace' where redirect_from_type='Replace'";
                $wpdb->query($sql);
            }

            /* 404 Links Table ------------------------------------------*/
            $table_name = SR_database::WP_SEO_404_links();
            if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                $sql = "
			CREATE TABLE IF NOT EXISTS `$table_name` (
              `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `ctime` datetime NOT NULL,
              `counter` int(11) unsigned DEFAULT 1,
              `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `link_type` int(11) unsigned DEFAULT 1,
              `referrer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `ip` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              `country` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              `os` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              `browser` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              `blog` int(11) unsigned NOT NULL DEFAULT 1,
              PRIMARY KEY (`ID`),
              UNIQUE KEY `link` (`link`,`blog`)
            ) ENGINE = MyISAM ;
			";
                $wpdb->query($sql);
            }else
            {
                //check if Innodb convert it to myisam.                
                $status = $wpdb->get_row($wpdb->prepare("SHOW TABLE STATUS WHERE Name = '%s'",$table_name));
                if($status->Engine == 'InnoDB')
                {
                    $wpdb->query("alter table $table_name engine = MyISAM;");
                }
            }
            /* Redirection Table Upgrade -----------------------------------*/
            if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_NAME = '$table_name'
			AND table_schema = DATABASE()
                        AND COLUMN_NAME = 'counter' ") == '0') {

                $sql="
            ALTER TABLE $table_name
            ADD COLUMN link_type int(11) unsigned DEFAULT 1,
            ADD COLUMN counter int(11) unsigned DEFAULT 1;
            ";
                $wpdb->query($sql);
            }

            // Fix add blog field if not exist.
            if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_NAME = '$table_name'
			AND table_schema = DATABASE()
                        AND COLUMN_NAME = 'blog' ") == '0') {

                $sql="
                ALTER TABLE $table_name
                ADD COLUMN blog int(11) unsigned NOT NULL DEFAULT 1;
            ";
                $wpdb->query($sql);
            }


            /* Logs Table --------------------------------------------------*/
            $table_name = SR_database::WP_SEO_Redirection_LOG();
            if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                $sql = "
    		CREATE TABLE IF NOT EXISTS `$table_name` (
              `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `rID` int(11) unsigned DEFAULT NULL,
              `ctime` datetime NOT NULL,
              `rfrom` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `rto` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `rtype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `rsrc` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              `referrer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `ip` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              `country` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
              `os` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              `browser` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
              `blog` int(11) unsigned NOT NULL DEFAULT 1,
              PRIMARY KEY (`ID`)
            ) ENGINE = MyISAM ;
			";
                $wpdb->query($sql);
            }else
            {
                
                /* Logs Table Upgrade ------------------------------------------*/
                //check if Innodb convert it to myisam.                
                $status = $wpdb->get_row($wpdb->prepare("SHOW TABLE STATUS WHERE Name = '%s'",$table_name));
                if($status->Engine == 'InnoDB')
                {
                    $wpdb->query("alter table $table_name engine = MyISAM;");
                }

                // Fix add blog field if not exist.
                if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_NAME = '$table_name'
                            AND table_schema = DATABASE()
                            AND COLUMN_NAME = 'blog' ") == '0') {

                    $sql="
                    ALTER TABLE $table_name
                    ADD COLUMN blog int(11) unsigned NOT NULL DEFAULT 1;
                ";
                    $wpdb->query($sql);
                }

            }
            

            /* Cache Table ------------------------------------------*/
            $table_name = SR_database::WP_SEO_Cache();
            if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                $sql = "
    		CREATE TABLE IF NOT EXISTS `$table_name` (
              `ID` int(11) unsigned NOT NULL,
              `is_redirected` int(1) unsigned NOT NULL,
              `redirect_from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `redirect_to` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `redirect_type` int(3) unsigned NOT NULL DEFAULT 301,
              `blog` int(11) unsigned NOT NULL DEFAULT 1,
              PRIMARY KEY (`ID`)
            ) ENGINE = MyISAM ;
			";
                $wpdb->query($sql);
            }  else {
                
                //check if Innodb convert it to myisam.                
                $status = $wpdb->get_row($wpdb->prepare("SHOW TABLE STATUS WHERE Name = '%s'",$table_name));
                if($status->Engine == 'InnoDB')
                {
                    $wpdb->query("alter table $table_name engine = MyISAM;");
                }
                
                /* Cache Table Upgrade -----------------------------------------*/
                // Fix add blog field if not exist.
                if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_NAME = '$table_name'
                            AND table_schema = DATABASE()
                            AND COLUMN_NAME = 'blog' ") == '0') {

                    $sql="
                    ALTER TABLE $table_name
                    ADD COLUMN blog int(11) unsigned NOT NULL DEFAULT 1;
                ";
                    $wpdb->query($sql);
                }

                if($wpdb->get_var(" SELECT count(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS
                       WHERE TABLE_NAME = '$table_name'
                            AND table_schema = DATABASE()
                            AND COLUMN_NAME = 'redirect_from' ") == '0') {

                    $sql="
                    ALTER TABLE $table_name
                    ADD COLUMN `redirect_from` varchar(255) COLLATE utf8_unicode_ci NOT NULL;
                ";
                    $wpdb->query($sql);
                }

                
            }
            

            /* Groups Table ------------------------------------------*/
            $table_name = SR_database::WP_SEO_Groups();
            if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
                $sql = "
                CREATE TABLE IF NOT EXISTS `$table_name` (
              `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `group_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `group_type` int(1) unsigned DEFAULT 0,
              `blog` int(11) unsigned NOT NULL DEFAULT 1,
              PRIMARY KEY (`ID`)
            ) ENGINE = MyISAM ;
            ";
            $wpdb->query($sql);
            $wpdb->query("insert into `$table_name`(`group_title`, `group_type`,`blog`) values('Default',1,'" . get_current_blog_id() . "');");
            $wpdb->query("insert into `$table_name`(`group_title`, `group_type`,`blog`) values('Redirected Posts',1,'" . get_current_blog_id() . "');");
            $wpdb->query("insert into `$table_name`(`group_title`, `group_type`,`blog`) values('Modified Posts',1,'" . get_current_blog_id() . "');");
            
            }else
            {
                //check if Innodb convert it to myisam.                
                $status = $wpdb->get_row($wpdb->prepare("SHOW TABLE STATUS WHERE Name = '%s'",$table_name));
                if($status->Engine == 'InnoDB')
                {
                    $wpdb->query("alter table $table_name engine = MyISAM;");
                }
            }

            /* check for multisite -------------------------------------------- */
            if (function_exists('is_multisite') && is_multisite()) {
                if ($networkwide) {

                    $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                    foreach ($blogids as $blog_id) {
                        if($wpdb->get_var("select count(*) as cnt from '$table_name' where blog='$blog_id' ")<3){
                            $wpdb->query("insert into `$table_name`(`group_title`, `group_type`,`blog`) values('Default',1,'" . $blog_id . "');");
                            $wpdb->query("insert into `$table_name`(`group_title`, `group_type`,`blog`) values('Redirected Posts',1,'" . $blog_id . "');");
                            $wpdb->query("insert into `$table_name`(`group_title`, `group_type`,`blog`) values('Modified Posts',1,'" . $blog_id . "');");
                        }
                    }
                }
            } 


        }

        /* Install Options Function  ---------------------------------------------- */
        public static function install_options($blog=0)
        {
            $options = SRP_PLUGIN::get_options()->get_my_options($blog);

            if(!array_key_exists('plugin_status',$options))
                $options['plugin_status']= '1';
			
			if(!array_key_exists('ip_logging_status',$options))
                $options['ip_logging_status']= '1';
			
			if(!array_key_exists('pagination_limit',$options))
                $options['pagination_limit']= '10';

            if(!array_key_exists('show_redirect_box',$options))
                $options['show_redirect_box']= '1';

            if(!array_key_exists('add_auto_redirect',$options))
                $options['add_auto_redirect']= '1';

            if(!array_key_exists('reflect_modifications',$options))
                $options['reflect_modifications']= '1';
            
            if(!array_key_exists('cache_enable',$options))
                $options['cache_enable']= '1';

            if(!array_key_exists('history_status',$options))
                $options['history_status']= '1';

            if(!array_key_exists('history_limit',$options))
                $options['history_limit']= '30';

            if(!array_key_exists('p404_discovery_status',$options))
                $options['p404_discovery_status']= '1';

            if(!array_key_exists('p404_rules',$options))
                $options['p404_rules']= '1';

            if(!array_key_exists('keep_data',$options))
                $options['keep_data']= '1';

            if(!array_key_exists('post_types',$options))
                $options['post_types']= 'page,post';

            if(!array_key_exists('pages_status',$options))
            {
                if(array_key_exists('p404_status',$options) && $options['p404_status']=='1')
                {
                    $options['pages_status']= 'on';
                }else
                {
                    $options['pages_status']= '';
                }
            }

            if(!array_key_exists('redirect_pages_to',$options))
            {
                if(array_key_exists('p404_redirect_to',$options) && $options['p404_redirect_to']!='')
                {
                    $options['redirect_pages_to']= $options['p404_redirect_to'];
                }else
                {
                    $options['redirect_pages_to']= '';
                }
            }

            if(!array_key_exists('images_status',$options))
                $options['images_status']= '';

            if(!array_key_exists('redirect_images_to',$options))
                $options['redirect_images_to']= '';

            if(!array_key_exists('scripts_status',$options))
                $options['scripts_status']= '';

            if(!array_key_exists('redirect_scripts_to',$options))
                $options['redirect_scripts_to']= '';

            if(!array_key_exists('otherfiles_status',$options))
                $options['otherfiles_status']= '';

            if(!array_key_exists('redirect_otherfiles_to',$options))
                $options['redirect_otherfiles_to']= '';

            SRP_PLUGIN::get_options()->update_my_options($options,$blog);
        }


        /* Create a New Blog function ------------------------------------------------------ */
        public static function create_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
            global $wpdb;
            $wpdb->query("insert into `" . SR_database::WP_SEO_Groups() . "`(`group_title`, `group_type`,`blog`) values('Default',1,'" . $blog_id . "');");
            $wpdb->query("insert into `" . SR_database::WP_SEO_Groups() . "`(`group_title`, `group_type`,`blog`) values('Redirected Posts',1,'" . $blog_id . "');");
            $wpdb->query("insert into `" . SR_database::WP_SEO_Groups() . "`(`group_title`, `group_type`,`blog`) values('Modified Posts',1,'" . $blog_id . "');");

            self::install_options($blog_id);
        }

        /* Delete Existing Blog function -------------------------------------------------- */
        public static function delete_blog( $blog_id, $drop ) {
            global $wpdb;
            $wpdb->query("delete from `" . SR_database::WP_SEO_Redirection() . " where blog='" . $blog_id . "';");
            $wpdb->query("delete from `" . SR_database::WP_SEO_Groups() . " where blog='" . $blog_id . "';");
            $wpdb->query("delete from `" . SR_database::WP_SEO_404_links() . " where blog='" . $blog_id . "';");
            $wpdb->query("delete from `" . SR_database::WP_SEO_Redirection_LOG() . " where blog='" . $blog_id . "';");
            $wpdb->query("delete from `" . SR_database::WP_SEO_Cache() . " where blog='" . $blog_id . "';");
        }


        /* Uninstall function --------------------------------------------------------------- */
        public static function uninstall()
        {
            global $wpdb ;

            if(SRP_PLUGIN::get_options()->read_option_value('keep_data')!='1'){

                $table_name = SR_database::WP_SEO_Redirection();
                $wpdb->query(" DROP TABLE `$table_name`  ");

                $table_name = SR_database::WP_SEO_404_links();
                $wpdb->query(" DROP TABLE `$table_name`  ");

                $table_name = SR_database::WP_SEO_Redirection_LOG();
                $wpdb->query(" DROP TABLE `$table_name`  ");

                $table_name = SR_database::WP_SEO_Cache();
                $wpdb->query(" DROP TABLE `$table_name`  ");

                $table_name = SR_database::WP_SEO_Groups();
                $wpdb->query(" DROP TABLE `$table_name`  ");

               // SRP_PLUGIN::get_optionst()->delete_my_options();
            }

        }

        /* Upgrade function --------------------------------------------------------------- */
        public static function upgrade()
        {
            if(SRP_PLUGIN::get_options()->read_option_value('upgrade_version')!= self::$version)
            {
                self::install(null);
                SRP_PLUGIN::get_options()->save_option_value('upgrade_version', self::$version);
            }
        }

        /* Hook functions --------------------------------------------------------------- */
        public static function hook_installer()
        {
            register_activation_hook( SRP_PLUGIN::get_app()->get_plugin_file() , array( 'seo_redirection_installer', 'install' ) );
            register_uninstall_hook( SRP_PLUGIN::get_app()->get_plugin_file() , array( 'seo_redirection_installer', 'uninstall' ) );
            add_action( 'plugins_loaded', array( 'seo_redirection_installer', 'upgrade' ) );
            add_action( 'wpmu_new_blog', array( 'seo_redirection_installer', 'create_new_blog' ), 10, 6);
            add_action( 'delete_blog', array( 'seo_redirection_installer', 'delete_blog' ), 10, 2 );
        }
    }
}